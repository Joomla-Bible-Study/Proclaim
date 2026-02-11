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

use CWM\Component\Proclaim\Administrator\Helper\CwmscriptureHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

/**
 * Migrates legacy flat scripture columns from #__bsms_studies into the new
 * #__bsms_study_scriptures junction table.
 *
 * Called once from proclaim.script.php postflight on update.
 *
 * @since  10.1.0
 */
class CwmscriptureMigration
{
    /**
     * Batch size for inserts.
     *
     * @var int
     * @since 10.1.0
     */
    private const BATCH_SIZE = 100;

    /**
     * Run the migration. Idempotent — checks for existing data before inserting.
     *
     * @return  int  Number of studies migrated
     *
     * @since  10.1.0
     */
    public static function migrate(): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Check if table exists
        $tables    = $db->getTableList();
        $prefix    = $db->getPrefix();
        $tableName = $prefix . 'bsms_study_scriptures';

        if (!\in_array($tableName, $tables, true)) {
            Log::add('Scripture junction table not found, skipping migration.', Log::INFO, 'com_proclaim');

            return 0;
        }

        // Check if already migrated (junction table has rows)
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_study_scriptures'));
        $db->setQuery($query);
        $existingCount = (int) $db->loadResult();

        if ($existingCount > 0) {
            Log::add('Scripture migration already completed (' . $existingCount . ' rows exist).', Log::INFO, 'com_proclaim');

            return 0;
        }

        // Query all studies with at least one book reference
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('booknumber'),
                $db->quoteName('chapter_begin'),
                $db->quoteName('verse_begin'),
                $db->quoteName('chapter_end'),
                $db->quoteName('verse_end'),
                $db->quoteName('bible_version'),
                $db->quoteName('booknumber2'),
                $db->quoteName('chapter_begin2'),
                $db->quoteName('verse_begin2'),
                $db->quoteName('chapter_end2'),
                $db->quoteName('verse_end2'),
                $db->quoteName('bible_version2'),
            ])
            ->from($db->quoteName('#__bsms_studies'))
            ->where('(' . $db->quoteName('booknumber') . ' > 0 OR '
                . $db->quoteName('booknumber2') . ' IS NOT NULL AND ' . $db->quoteName('booknumber2') . ' > 0)');
        $db->setQuery($query);
        $studies = $db->loadObjectList();

        if (empty($studies)) {
            Log::add('No studies with scripture references found.', Log::INFO, 'com_proclaim');

            return 0;
        }

        $migrated = 0;
        $batch    = [];

        foreach ($studies as $study) {
            $bn1 = (int) ($study->booknumber ?? 0);
            $bn2 = (int) ($study->booknumber2 ?? 0);

            // Primary reference
            if ($bn1 > 0) {
                $refText = CwmscriptureHelper::formatReference(
                    $bn1,
                    (int) ($study->chapter_begin ?? 0),
                    (int) ($study->verse_begin ?? 0),
                    (int) ($study->chapter_end ?? 0),
                    (int) ($study->verse_end ?? 0)
                );

                $batch[] = implode(', ', [
                    (int) $study->id,
                    0,
                    $bn1,
                    (int) ($study->chapter_begin ?? 0),
                    (int) ($study->verse_begin ?? 0),
                    (int) ($study->chapter_end ?? 0),
                    (int) ($study->verse_end ?? 0),
                    $db->quote((string) ($study->bible_version ?? '')),
                    $db->quote($refText),
                ]);
            }

            // Secondary reference
            if ($bn2 > 0) {
                $refText2 = CwmscriptureHelper::formatReference(
                    $bn2,
                    (int) ($study->chapter_begin2 ?? 0),
                    (int) ($study->verse_begin2 ?? 0),
                    (int) ($study->chapter_end2 ?? 0),
                    (int) ($study->verse_end2 ?? 0)
                );

                $batch[] = implode(', ', [
                    (int) $study->id,
                    1,
                    $bn2,
                    (int) ($study->chapter_begin2 ?? 0),
                    (int) ($study->verse_begin2 ?? 0),
                    (int) ($study->chapter_end2 ?? 0),
                    (int) ($study->verse_end2 ?? 0),
                    $db->quote((string) ($study->bible_version2 ?? '')),
                    $db->quote($refText2),
                ]);
            }

            $migrated++;

            // Flush batch
            if (\count($batch) >= self::BATCH_SIZE) {
                self::insertBatch($db, $batch);
                $batch = [];
            }
        }

        // Flush remaining
        if (!empty($batch)) {
            self::insertBatch($db, $batch);
        }

        Log::add('Scripture migration completed: ' . $migrated . ' studies migrated.', Log::INFO, 'com_proclaim');

        return $migrated;
    }

    /**
     * Insert a batch of rows into the junction table.
     *
     * @param   DatabaseInterface  $db     Database driver
     * @param   array              $batch  Array of value strings
     *
     * @return  void
     *
     * @since  10.1.0
     */
    private static function insertBatch(DatabaseInterface $db, array $batch): void
    {
        $columns = $db->quoteName([
            'study_id', 'ordering', 'booknumber', 'chapter_begin', 'verse_begin',
            'chapter_end', 'verse_end', 'bible_version', 'reference_text',
        ]);

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__bsms_study_scriptures'))
            ->columns($columns);

        foreach ($batch as $values) {
            $query->values($values);
        }

        $db->setQuery($query);
        $db->execute();
    }
}
