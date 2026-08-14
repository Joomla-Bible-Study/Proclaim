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

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

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
     * @param   int                $id     Row being updated, excluded from the check
     *
     * @return  string  An alias free for this row
     *
     * @since 10.5.6
     */
    private static function makeUniqueAlias(DatabaseInterface $db, string $table, string $base, int $id): string
    {
        // stringURLSafe() returns '' for titles with no transliterable
        // characters. Writing that back would leave the row looking
        // un-aliased, so it would be picked up again on every future run and
        // never gain a usable alias.
        if ($base === '') {
            $base = 'item-' . $id;
        }

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
