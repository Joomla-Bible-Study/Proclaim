<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Lib;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Library\Scripture\Helper\ScriptureHelper as CwmscriptureHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

/**
 * Finishes what the schema update started.
 *
 * The flat columns this once read are gone. Moving the rows is
 * now done by the SQL update file, which has to run before the DROP in the same
 * file -- Joomla executes schema updates before postflight, so PHP is too late.
 * What SQL could not do is translate a book name, so the rows it writes carry an
 * empty reference_text and this fills them in.
 *
 * Called from proclaim.script.php postflight, from the Backup and Restore
 * screens, and from the control panel's data fixes. Safe to run repeatedly.
 *
 * @since  10.1.0
 */
class CwmscriptureMigration
{
    /**
     * Rows to fill per query.
     *
     * @var int
     * @since 10.1.0
     */
    private const BATCH_SIZE = 100;

    /**
     * The flat scripture columns #__bsms_studies once carried.
     *
     * @var string[]
     * @since 10.5.8
     */
    private const LEGACY_COLUMNS = [
        'booknumber', 'chapter_begin', 'verse_begin', 'chapter_end', 'verse_end', 'bible_version',
        'booknumber2', 'chapter_begin2', 'verse_begin2', 'chapter_end2', 'verse_end2', 'bible_version2',
    ];

    /**
     * Give every reference that has no display text one.
     *
     * @return  int  Number of references filled in
     *
     * @since  10.1.0
     */
    public static function migrate(): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if (!\in_array($db->getPrefix() . 'bsms_study_scriptures', $db->getTableList(), true)) {
            Log::add('Scripture junction table not found, skipping migration.', Log::INFO, 'com_proclaim');

            return 0;
        }

        $query = $db->createQuery()
            ->select($db->quoteName(['id', 'booknumber', 'chapter_begin', 'verse_begin', 'chapter_end', 'verse_end']))
            ->from($db->quoteName('#__bsms_study_scriptures'))
            ->where($db->quoteName('reference_text') . " = ''")
            ->where($db->quoteName('booknumber') . ' > 0')
            ->setLimit(self::BATCH_SIZE);

        $filled = 0;

        do {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];

            foreach ($rows as $row) {
                $text = CwmscriptureHelper::formatReference(
                    (int) $row->booknumber,
                    (int) $row->chapter_begin,
                    (int) $row->verse_begin,
                    (int) $row->chapter_end,
                    (int) $row->verse_end
                );

                if ($text === '') {
                    continue;
                }

                $update = $db->createQuery()
                    ->update($db->quoteName('#__bsms_study_scriptures'))
                    ->set($db->quoteName('reference_text') . ' = ' . $db->quote($text))
                    ->where($db->quoteName('id') . ' = ' . (int) $row->id);
                $db->setQuery($update);
                $db->execute();

                $filled++;
            }
            // A reference the library cannot name keeps an empty text and would
            // be selected again forever, so stop once a batch fills nothing.
        } while ($rows !== [] && $filled > 0 && \count($rows) === self::BATCH_SIZE);

        if ($filled > 0) {
            Log::add('Scripture references given display text: ' . $filled, Log::INFO, 'com_proclaim');
        }

        return $filled;
    }

    /**
     * Kept so importers and older callers do not fatal.
     *
     * @param   int  $studyId  Study primary key
     *
     * @return  int  Always 0; there are no legacy columns left to migrate
     *
     * @since       10.5.8
     * @deprecated  10.5.8  Write the junction directly with CwmscriptureHelper::saveScriptures().
     */
    public static function migrateStudy(int $studyId): int
    {
        return 0;
    }

    /**
     * Move the legacy flat columns into the junction and drop them.
     *
     * ⚠️ Deliberately not in migration SQL. Joomla's ChangeSet builds a check
     * only for ALTER TABLE, CREATE TABLE and RENAME TABLE; an INSERT has none,
     * so it is skipped and never replayed while the DROP COLUMNs beside it are
     * replayed. Database Maintenance → Fix, and the 9.x upgrade wizard through
     * CwmupgradeHelper::runSchemaMigration(), would then run the drops without
     * the backfill. Here the two cannot come apart.
     *
     * Safe to call whenever: it returns immediately once the columns are gone.
     *
     * @return  int  References moved into the junction
     *
     * @since   10.5.8
     */
    public static function retireLegacyColumns(): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if (!self::hasLegacyColumns($db)) {
            return 0;
        }

        $moved = self::backfillJunction($db);

        // ⚠️ Dropping booknumber does not drop this index, it shrinks it to
        // (published) -- a duplicate of idx_state.
        $db->setQuery(
            'SHOW INDEX FROM ' . $db->quoteName('#__bsms_studies')
            . ' WHERE ' . $db->quoteName('Key_name') . ' = ' . $db->quote('idx_booknumber_published')
        );

        if ($db->loadRow()) {
            $db->setQuery(
                'ALTER TABLE ' . $db->quoteName('#__bsms_studies')
                . ' DROP INDEX ' . $db->quoteName('idx_booknumber_published')
            );
            $db->execute();
        }

        foreach (self::LEGACY_COLUMNS as $column) {
            if (!self::columnExists($db, $column)) {
                continue;
            }

            $db->setQuery(
                'ALTER TABLE ' . $db->quoteName('#__bsms_studies')
                . ' DROP COLUMN ' . $db->quoteName($column)
            );
            $db->execute();
        }

        // A template could have this saved as `booknumber`, and Cwmserieslist
        // puts it straight into ORDER BY.
        $db->setQuery(
            'UPDATE ' . $db->quoteName('#__bsms_templates')
            . ' SET ' . $db->quoteName('params') . ' = REPLACE(' . $db->quoteName('params') . ', '
            . $db->quote('"series_detail_sort":"booknumber"') . ', '
            . $db->quote('"series_detail_sort":"studydate"') . ')'
            . ' WHERE ' . $db->quoteName('params') . ' LIKE '
            . $db->quote('%"series_detail_sort":"booknumber"%')
        );
        $db->execute();

        Log::add('Legacy scripture columns retired; ' . $moved . ' references moved.', Log::INFO, 'com_proclaim');

        return $moved;
    }

    /**
     * Copy the flat pair into the junction, for studies it does not know yet.
     *
     * ⚠️ The secondary columns are VARCHAR and really do hold '' and '-1', so
     * every read of them is REGEXP-guarded: CAST('' AS SIGNED) raises
     * "Truncated incorrect INTEGER value" under STRICT_TRANS_TABLES.
     *
     * ⚠️ The SELECT is wrapped in a derived table so MySQL materialises it
     * before inserting. Without that, the primary insert satisfies the
     * secondary's NOT EXISTS and every second reference is lost.
     *
     * @param   DatabaseInterface  $db  Database driver
     *
     * @return  int  Rows inserted
     *
     * @since   10.5.8
     */
    private static function backfillJunction(DatabaseInterface $db): int
    {
        if (!self::junctionTableExists($db)) {
            return 0;
        }

        $studies = $db->quoteName('#__bsms_studies');
        $junc    = $db->quoteName('#__bsms_study_scriptures');
        $unseen  = 'NOT EXISTS (SELECT 1 FROM ' . $junc . ' AS j WHERE j.study_id = s.id)';
        $numeric = static fn (string $c): string => "CASE WHEN s.`$c` REGEXP '^[0-9]+$' THEN CAST(s.`$c` AS SIGNED) ELSE 0 END";

        $db->setQuery(
            'INSERT INTO ' . $junc . ' (`study_id`, `ordering`, `booknumber`, `chapter_begin`, `verse_begin`,'
            . ' `chapter_end`, `verse_end`, `bible_version`, `reference_text`) SELECT * FROM ('
            . 'SELECT s.`id`, 0, s.`booknumber`, COALESCE(s.`chapter_begin`, 0), COALESCE(s.`verse_begin`, 0),'
            . ' COALESCE(s.`chapter_end`, 0), COALESCE(s.`verse_end`, 0), COALESCE(s.`bible_version`, \'\'), \'\''
            . ' FROM ' . $studies . ' AS s WHERE s.`booknumber` > 0 AND ' . $unseen
            . ' UNION ALL '
            . 'SELECT s.`id`, 1, CAST(s.`booknumber2` AS SIGNED), ' . $numeric('chapter_begin2') . ', '
            . $numeric('verse_begin2') . ', ' . $numeric('chapter_end2') . ', ' . $numeric('verse_end2') . ','
            . ' COALESCE(s.`bible_version2`, \'\'), \'\''
            . ' FROM ' . $studies . ' AS s WHERE s.`booknumber2` REGEXP \'^[0-9]+$\''
            . ' AND CAST(s.`booknumber2` AS SIGNED) > 0 AND ' . $unseen
            . ') AS backfill'
        );
        $db->execute();

        return (int) $db->getAffectedRows();
    }

    /**
     * @param   DatabaseInterface  $db  Database driver
     *
     * @return  bool
     *
     * @since   10.5.8
     */
    private static function hasLegacyColumns(DatabaseInterface $db): bool
    {
        return self::columnExists($db, 'booknumber');
    }

    /**
     * @param   DatabaseInterface  $db      Database driver
     * @param   string             $column  Column name
     *
     * @return  bool
     *
     * @since   10.5.8
     */
    private static function columnExists(DatabaseInterface $db, string $column): bool
    {
        $db->setQuery(
            'SHOW COLUMNS FROM ' . $db->quoteName('#__bsms_studies') . ' LIKE ' . $db->quote($column)
        );

        return $db->loadRow() !== null;
    }

    /**
     * @param   DatabaseInterface  $db  Database driver
     *
     * @return  bool
     *
     * @since   10.5.8
     */
    private static function junctionTableExists(DatabaseInterface $db): bool
    {
        return \in_array($db->getPrefix() . 'bsms_study_scriptures', $db->getTableList(), true);
    }
}
