<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\String\StringHelper;

/**
 * Class for updating the alias in certain tables
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class Cwmalias
{
    /**
     * Extension Name
     *
     * @var string
     *
     * @since 1.5
     */
    public static $extension = 'com_proclaim';

    /**
     * Tables whose aliases are unique, each with the column an alias is built from.
     *
     * The site router resolves these by alias alone under "Remove IDs from URLs".
     * Teachers are not listed: their unique index predates this list.
     *
     * @var array<string, string>
     * @since __DEPLOY_VERSION__
     */
    public const array UNIQUE_ALIAS_TABLES = [
        '#__bsms_studies' => 'studytitle',
        '#__bsms_series'  => 'series_text',
    ];

    /**
     * Name of the unique index on each alias column, as on #__bsms_teachers.
     *
     * @var string
     * @since __DEPLOY_VERSION__
     */
    public const string UNIQUE_ALIAS_INDEX = 'idx_alias';

    /**
     * For each table in UNIQUE_ALIAS_TABLES, the language key family that
     * refuses a taken alias and the task that edits the row holding it.
     *
     * @var array<string, array{0: string, 1: string}>
     * @since __DEPLOY_VERSION__
     */
    private const array TAKEN_ALIAS = [
        '#__bsms_studies' => ['JBS_STY_DUPLICATE_ALIAS', 'cwmmessage.edit'],
        '#__bsms_series'  => ['JBS_SER_DUPLICATE_ALIAS', 'cwmserie.edit'],
    ];

    /**
     * Update Alias
     *
     * @return string
     * @since 7.1.0
     */
    public static function updateAlias(): int
    {
        $done    = 0;
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $objects = self::getObjects();
        $results = [];

        foreach ($objects as $object) {
            $results[] = self::getTableQuery($table = $object['name'], $title = $object['titlefield']);
        }

        foreach ($results as $result) {
            foreach ($result as $r) {
                if (!$r['title']) {
                    // Do nothing
                } else {
                    // Two titles that slugify identically -- reused series
                    // names, teacher name variants -- would otherwise get the
                    // same alias.
                    // On #__bsms_studies/_series/_message_type there is no unique
                    // index, so the duplicate stuck and the SEF router -- which
                    // resolves by alias alone under "Remove IDs from URLs" -- served
                    // whichever row the database returned first, silently rendering
                    // one record's page as another's. On #__bsms_teachers, which
                    // does carry UNIQUE KEY `idx_alias`, the duplicate INSERT threw
                    // instead and aborted the whole backfill mid-run.
                    $alias = self::makeUniqueAlias(
                        $db,
                        $r['table'],
                        OutputFilter::stringURLSafe($r['title']),
                        (int) $r['id']
                    );

                    $query = $db->createQuery();
                    $query->update($db->quoteName($r['table']))
                        ->set($db->quoteName('alias') . ' = ' . $db->q($alias))
                        ->where($db->quoteName('id') . ' = ' . (int) $r['id']);
                    $db->setQuery($query);

                    // One unwritable row must not abandon the rest of the backfill.
                    try {
                        $db->execute();
                        $done++;
                    } catch (\RuntimeException $e) {
                        Log::add(
                            'Alias backfill skipped ' . $r['table'] . ' id ' . (int) $r['id']
                            . ': ' . $e->getMessage(),
                            Log::WARNING,
                            'com_proclaim'
                        );
                    }
                }
            }
        }

        return $done;
    }

    /**
     * Run ensureUniqueAliases() over every table in UNIQUE_ALIAS_TABLES.
     *
     * @param   DatabaseInterface  $db  Database driver
     *
     * @return  array<string, list<array{id: int, from: string, to: string}>>  The aliases changed, by table
     *
     * @throws  \RuntimeException  When the database refuses a rename or an index
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function ensureAllUniqueAliases(DatabaseInterface $db): array
    {
        $changed = [];

        foreach (self::UNIQUE_ALIAS_TABLES as $table => $titleField) {
            $changed[$table] = self::ensureUniqueAliases($db, $table, $titleField);
        }

        return $changed;
    }

    /**
     * Give every row of a table its own alias, then add the unique index that
     * keeps it that way.
     *
     * Where rows share an alias, the published or archived row with the lowest
     * id keeps it, because that is the row the site router already serves at
     * that address. Every other row gets the next free `-2`, `-3`, ... form of
     * it. A row with no alias gets one built from its title.
     *
     * Safe to repeat: once the index exists there is nothing left to do. Runs
     * after every update, and after a backup restore, which recreates the table
     * the way the backup recorded it.
     *
     * @param   DatabaseInterface  $db          Database driver
     * @param   string             $table       Table, with the #__ prefix
     * @param   string             $titleField  Column an empty alias is built from
     *
     * @return  list<array{id: int, from: string, to: string}>  The aliases changed
     *
     * @throws  \RuntimeException  When the database refuses a rename or the index
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function ensureUniqueAliases(DatabaseInterface $db, string $table, string $titleField): array
    {
        $index = self::aliasIndexState($db, $table);

        if ($index === 'unique') {
            return [];
        }

        $changed = [];

        // Grouped by the database, so two aliases count as the same exactly
        // when the index about to be added will say they are.
        $aliases = $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName('alias'))
                ->from($db->quoteName($table))
                ->where($db->quoteName('alias') . ' <> ' . $db->quote(''))
                ->group($db->quoteName('alias'))
                ->having('COUNT(*) > 1')
        )->loadColumn();

        foreach ($aliases as $alias) {
            $rows = $db->setQuery(
                $db->createQuery()
                    ->select($db->quoteName(['id', $titleField], ['id', 'title']))
                    ->from($db->quoteName($table))
                    ->where($db->quoteName('alias') . ' = :alias')
                    ->order('CASE WHEN ' . $db->quoteName('published') . ' IN (1, 2) THEN 0 ELSE 1 END')
                    ->order($db->quoteName('id'))
                    ->bind(':alias', $alias)
            )->loadObjectList();

            array_shift($rows);

            foreach ($rows as $row) {
                $changed[] = self::realias($db, $table, $row, $alias, $alias);
            }
        }

        // One empty alias is not a duplicate yet, but the next would be, and
        // neither can be reached by its address.
        $rows = $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName(['id', $titleField], ['id', 'title']))
                ->from($db->quoteName($table))
                ->where($db->quoteName('alias') . ' = ' . $db->quote(''))
                ->order($db->quoteName('id'))
        )->loadObjectList();

        foreach ($rows as $row) {
            $changed[] = self::realias($db, $table, $row, '', OutputFilter::stringURLSafe((string) $row->title));
        }

        if ($index === 'other') {
            $db->setQuery(
                'ALTER TABLE ' . $db->quoteName($table) . ' DROP INDEX ' . $db->quoteName(self::UNIQUE_ALIAS_INDEX)
            )->execute();
        }

        $db->setQuery(
            'ALTER TABLE ' . $db->quoteName($table)
            . ' ADD UNIQUE KEY ' . $db->quoteName(self::UNIQUE_ALIAS_INDEX) . ' (' . $db->quoteName('alias') . ')'
        )->execute();

        return $changed;
    }

    /**
     * The title and alias a Save as Copy should store.
     *
     * The copy arrives with the original's title and alias. An alias the user
     * typed is kept, for the table's check() to refuse if another row holds
     * it. Otherwise the alias is cleared for the save to build from the title,
     * and an unchanged title is numbered first, as core numbers a copied
     * article.
     *
     * @param   DatabaseInterface  $db             Database driver
     * @param   string             $table          A table in UNIQUE_ALIAS_TABLES
     * @param   string             $title          The submitted title
     * @param   string             $alias          The submitted alias
     * @param   string             $originalTitle  The original's title
     * @param   string             $originalAlias  The original's alias
     *
     * @return  array{0: string, 1: string}  The title and alias to store
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function forCopy(
        DatabaseInterface $db,
        string $table,
        string $title,
        string $alias,
        string $originalTitle,
        string $originalAlias
    ): array {
        if ($alias !== '' && $alias !== $originalAlias) {
            return [$title, $alias];
        }

        return [$title === $originalTitle ? self::nextCopyTitle($db, $table, $title) : $title, ''];
    }

    /**
     * A copy's title: the next (2), (3), ... whose alias no row holds yet, so
     * a second copy of an original becomes (3) rather than another (2), and
     * the alias built from it carries the same number.
     *
     * Core's generateNewTitle() is not used. It looks rows up by catid, which
     * these tables do not have, and it steps the alias's own trailing number,
     * so a copy of "psalm-23" would become "psalm-24".
     *
     * @param   DatabaseInterface  $db     Database driver
     * @param   string             $table  A table in UNIQUE_ALIAS_TABLES
     * @param   string             $title  The original's title
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function nextCopyTitle(DatabaseInterface $db, string $table, string $title): string
    {
        do {
            $title = StringHelper::increment($title);
            $slug  = ApplicationHelper::stringURLSafe($title);
        } while ($slug !== '' && self::aliasInUse($db, $table, $slug, 0));

        return $title;
    }

    /**
     * A free alias for a row about to be inserted without going through its
     * Table, built from the alias it arrived with or else from its title.
     *
     * @param   DatabaseInterface  $db     Database driver
     * @param   string             $table  A table in UNIQUE_ALIAS_TABLES
     * @param   string             $alias  The alias the row arrived with; may be empty
     * @param   string             $title  The row's title
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function aliasForInsert(DatabaseInterface $db, string $table, string $alias, string $title): string
    {
        $base = OutputFilter::stringURLSafe($alias !== '' ? $alias : $title);

        // A title with nothing to slug: core falls back to the date and time.
        if ($base === '') {
            $base = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        return self::makeUniqueAlias($db, $table, $base, 0);
    }

    /**
     * The row that already holds an alias, other than the one being saved.
     *
     * @param   DatabaseInterface  $db         Database driver
     * @param   string             $table      A table in UNIQUE_ALIAS_TABLES
     * @param   string             $alias      The alias being saved
     * @param   int                $excludeId  The row being saved; 0 for a new one
     *
     * @return  ?object  That row's id, title and published state, or null when the alias is free
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function findAliasOwner(DatabaseInterface $db, string $table, string $alias, int $excludeId): ?object
    {
        $query = $db->createQuery()
            ->select($db->quoteName(['id', self::UNIQUE_ALIAS_TABLES[$table], 'published'], ['id', 'title', 'published']))
            ->from($db->quoteName($table))
            ->where($db->quoteName('alias') . ' = :alias')
            ->where($db->quoteName('id') . ' <> :id')
            ->bind(':alias', $alias)
            ->bind(':id', $excludeId, ParameterType::INTEGER)
            ->setLimit(1);

        return $db->setQuery($query)->loadObject() ?: null;
    }

    /**
     * Why a save is refused when another row already holds its alias.
     *
     * The row is named, with a link to it, only in the administrator and only
     * for a user its admin list would already show it to. Anyone else learns
     * just that the alias is taken, and whether by a row in the trash.
     *
     * @param   DatabaseInterface  $db     Database driver
     * @param   string             $table  A table in UNIQUE_ALIAS_TABLES
     * @param   string             $alias  The alias being saved
     * @param   object             $owner  The row holding it, from findAliasOwner()
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function takenAliasMessage(DatabaseInterface $db, string $table, string $alias, object $owner): string
    {
        [$key, $task] = self::TAKEN_ALIAS[$table];

        if ((int) $owner->published === -2) {
            $key .= '_TRASHED';
        }

        if (!self::listShowsRow($db, $table, (int) $owner->id)) {
            return Text::sprintf($key, htmlspecialchars($alias, ENT_QUOTES, 'UTF-8'));
        }

        return Text::sprintf(
            $key . '_LINK',
            htmlspecialchars((string) $owner->title, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($alias, ENT_QUOTES, 'UTF-8'),
            'index.php?option=com_proclaim&task=' . $task . '&id=' . (int) $owner->id
        );
    }

    /**
     * Whether the current user's admin list for a table would show a row: in
     * the administrator, through the location and view-level filter the lists
     * apply.
     *
     * @param   DatabaseInterface  $db     Database driver
     * @param   string             $table  Table, with the #__ prefix
     * @param   int                $id     The row
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function listShowsRow(DatabaseInterface $db, string $table, int $id): bool
    {
        if (!Factory::getApplication()->isClient('administrator')) {
            return false;
        }

        $query = $db->createQuery()
            ->select('1')
            ->from($db->quoteName($table, 'holder'))
            ->where($db->quoteName('holder.id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

        CwmlocationHelper::applySecurityFilter($query, 'holder');

        return (bool) $db->setQuery($query)->loadResult();
    }

    /**
     * Whether a save gives a row an alias it did not already have stored.
     *
     * @param   DatabaseInterface  $db     Database driver
     * @param   string             $table  Table, with the #__ prefix
     * @param   string             $alias  The alias being saved
     * @param   int                $id     The row being saved; 0 for a new one
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function isNewAlias(DatabaseInterface $db, string $table, string $alias, int $id): bool
    {
        if ($id <= 0) {
            return true;
        }

        $stored = $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName('alias'))
                ->from($db->quoteName($table))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER)
        )->loadResult();

        return $stored === null || (string) $stored !== $alias;
    }

    /**
     * Give one row the first free alias built from a base.
     *
     * @param   DatabaseInterface  $db     Database driver
     * @param   string             $table  Table, with the #__ prefix
     * @param   object             $row    The row, with id and title
     * @param   string             $from   Its current alias
     * @param   string             $base   What the new alias is built from
     *
     * @return  array{id: int, from: string, to: string}
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function realias(DatabaseInterface $db, string $table, object $row, string $from, string $base): array
    {
        $id    = (int) $row->id;
        $alias = self::makeUniqueAlias($db, $table, $base, $id);

        $db->setQuery(
            $db->createQuery()
                ->update($db->quoteName($table))
                ->set($db->quoteName('alias') . ' = :alias')
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':alias', $alias)
                ->bind(':id', $id, ParameterType::INTEGER)
        )->execute();

        return ['id' => $id, 'from' => $from, 'to' => $alias];
    }

    /**
     * Whether a table's alias index exists, and whether it is unique.
     *
     * @param   DatabaseInterface  $db     Database driver
     * @param   string             $table  Table, with the #__ prefix
     *
     * @return  string  'unique', 'other' when an index of that name is not unique, or 'none'
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function aliasIndexState(DatabaseInterface $db, string $table): string
    {
        foreach ($db->getTableKeys($table) as $key) {
            if ($key->Key_name === self::UNIQUE_ALIAS_INDEX) {
                return (int) $key->Non_unique === 0 ? 'unique' : 'other';
            }
        }

        return 'none';
    }

    /**
     * Build an alias that no other row in the same table is already using.
     *
     * Appends -2, -3, ... to the slug until it is free, matching the
     * disambiguation Joomla core applies to article and category aliases.
     *
     * Each candidate is checked against the live table rather than against an
     * in-memory set, so it also accounts for rows written earlier in this same
     * backfill run (updateAlias() writes each row before moving to the next).
     *
     * @param   DatabaseInterface  $db     Database driver
     * @param   string             $table  Table being backfilled
     * @param   string             $base   Slugified title (may be empty)
     * @param   int                $id     Row being updated, excluded from the check; 0 for a row
     *                                     not stored yet, which needs a non-empty base because
     *                                     the fallback for an empty one is named after the id
     *
     * @return  string  An alias free for this row
     *
     * @since 10.5.6
     */
    public static function makeUniqueAlias(DatabaseInterface $db, string $table, string $base, int $id): string
    {
        // stringURLSafe() returns '' for titles with no transliterable
        // characters. Writing that back would leave the row looking
        // un-aliased, so it would be picked up again on every future run and
        // never gain a usable alias.
        if ($base === '') {
            $base = 'item-' . $id;
        }

        // Leave room for a suffix within the 255-character column. A longer
        // value would be cut back to the base and collide all over again.
        $base    = mb_substr($base, 0, 240);
        $alias   = $base;
        $counter = 1;

        while (self::aliasInUse($db, $table, $alias, $id)) {
            $counter++;

            // Defensive stop: the row id is unique by definition, so this
            // suffix always terminates the search.
            if ($counter > 100) {
                return $base . '-' . $id;
            }

            $alias = $base . '-' . $counter;
        }

        return $alias;
    }

    /**
     * Whether another row in this table already holds the given alias.
     *
     * @param   DatabaseInterface  $db         Database driver
     * @param   string             $table      Table to search
     * @param   string             $alias      Candidate alias
     * @param   int                $excludeId  Row to ignore (the one being updated)
     *
     * @return  bool
     *
     * @since 10.5.6
     */
    private static function aliasInUse(DatabaseInterface $db, string $table, string $alias, int $excludeId): bool
    {
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName($table))
            ->where($db->quoteName('alias') . ' = ' . $db->q($alias))
            ->where($db->quoteName('id') . ' != ' . $excludeId);
        $db->setQuery($query);

        return (int) $db->loadResult() > 0;
    }

    /**
     * Get Object's for tables
     *
     * @return array
     *
     * @since 1.5
     */
    private static function getObjects(): array
    {
        $objects = [
            ['name' => '#__bsms_series', 'titlefield' => 'series_text'],
            ['name' => '#__bsms_studies', 'titlefield' => 'studytitle'],
            ['name' => '#__bsms_message_type', 'titlefield' => 'message_type'],
            ['name' => '#__bsms_teachers', 'titlefield' => 'teachername'],
        ];

        return $objects;
    }

    /**
     * Get Table fields for updating.
     *
     * @param   string  $table  Table
     * @param   string  $title  Title
     *
     * @return bool|array
     *
     * @since 1.5
     */
    private static function getTableQuery($table, $title): bool|array
    {
        $data = [];

        if (!$table) {
            return false;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery();
        $query->select($db->quoteName('id') . ', ' . $db->quoteName('alias') . ', ' . $db->quoteName($title))
            ->from($db->quoteName($table));
        $db->setQuery($query);
        $results = $db->loadObjectList();

        foreach ($results as $result) {
            if (!$result->alias) {
                $temp   = [
                    'id'    => $result->id,
                    'title' => $result->$title,
                    'alias' => $result->alias,
                    'table' => $table,
                ];
                $data[] = $temp;
            }
        }

        return $data;
    }
}
