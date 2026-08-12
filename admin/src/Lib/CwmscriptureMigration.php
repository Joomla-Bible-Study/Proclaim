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
 * Migrates legacy flat scripture columns from #__bsms_studies into the new
 * #__bsms_study_scriptures junction table.
 *
 * Called from proclaim.script.php postflight on update, from the Backup and
 * Restore screens, and from the control panel's data fixes. Every caller may
 * run it repeatedly: it selects only studies that have legacy values and no
 * junction rows, so migrated studies are skipped and an interrupted run resumes
 * where it stopped.
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
     * Run the migration over every study that still needs it.
     *
     * @return  int  Number of studies migrated
     *
     * @since  10.1.0
     */
    public static function migrate(): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if (!self::junctionTableExists($db)) {
            Log::add('Scripture junction table not found, skipping migration.', Log::INFO, 'com_proclaim');

            return 0;
        }

        $studies = self::loadPending($db);

        if (empty($studies)) {
            Log::add('No studies with unmigrated scripture references found.', Log::INFO, 'com_proclaim');

            return 0;
        }

        $migrated = 0;
        $batch    = [];

        foreach ($studies as $study) {
            $batch = array_merge($batch, self::rowToValues($db, $study));
            $migrated++;

            if (\count($batch) >= self::BATCH_SIZE) {
                self::insertBatch($db, $batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            self::insertBatch($db, $batch);
        }

        Log::add('Scripture migration completed: ' . $migrated . ' studies migrated.', Log::INFO, 'com_proclaim');

        return $migrated;
    }

    /**
     * Migrate one study, for importers that write the legacy columns directly.
     *
     * @param   int  $studyId  Study primary key
     *
     * @return  int  1 if the study was migrated, 0 if it had nothing to migrate
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function migrateStudy(int $studyId): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if (!self::junctionTableExists($db)) {
            return 0;
        }

        $studies = self::loadPending($db, $studyId);

        if (empty($studies)) {
            return 0;
        }

        self::insertBatch($db, self::rowToValues($db, $studies[0]));

        return 1;
    }

    /**
     * Studies holding legacy values that have no junction rows yet.
     *
     * ⚠️ The `NOT EXISTS` is what makes every caller safe to run repeatedly.
     * This used to be a table-wide `COUNT(*) > 0` check, which switched the
     * migration off permanently the first time any study was saved through the
     * editor — leaving studies written by the PreachIT and Sermon Speaker
     * importers in the flat columns with no way to repair them (#1623).
     *
     * @param   DatabaseInterface  $db       Database driver
     * @param   int|null           $studyId  Limit to one study, or null for all
     *
     * @return  object[]
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function loadPending(DatabaseInterface $db, ?int $studyId = null): array
    {
        $bn1 = $db->quoteName('s.booknumber');
        $bn2 = $db->quoteName('s.booknumber2');

        $query = $db->createQuery()
            ->select([
                $db->quoteName('s.id'),
                $bn1,
                $db->quoteName('s.chapter_begin'),
                $db->quoteName('s.verse_begin'),
                $db->quoteName('s.chapter_end'),
                $db->quoteName('s.verse_end'),
                $db->quoteName('s.bible_version'),
                $bn2,
                $db->quoteName('s.chapter_begin2'),
                $db->quoteName('s.verse_begin2'),
                $db->quoteName('s.chapter_end2'),
                $db->quoteName('s.verse_end2'),
                $db->quoteName('s.bible_version2'),
            ])
            ->from($db->quoteName('#__bsms_studies', 's'))
            ->where('(' . $bn1 . ' > 0 OR (' . $bn2 . ' IS NOT NULL AND ' . $bn2 . ' > 0))')
            ->where(
                'NOT EXISTS (SELECT 1 FROM ' . $db->quoteName('#__bsms_study_scriptures', 'j')
                . ' WHERE ' . $db->quoteName('j.study_id') . ' = ' . $db->quoteName('s.id') . ')'
            );

        if ($studyId !== null) {
            $query->where($db->quoteName('s.id') . ' = ' . $studyId);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Turn one study's flat columns into junction row value strings.
     *
     * @param   DatabaseInterface  $db     Database driver
     * @param   object             $study  Row from loadPending()
     *
     * @return  string[]  Zero, one or two value strings
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function rowToValues(DatabaseInterface $db, object $study): array
    {
        $values = [];

        foreach ([['', 0], ['2', 1]] as [$suffix, $ordering]) {
            $book = (int) ($study->{'booknumber' . $suffix} ?? 0);

            if ($book < 1) {
                continue;
            }

            $chapterBegin = (int) ($study->{'chapter_begin' . $suffix} ?? 0);
            $verseBegin   = (int) ($study->{'verse_begin' . $suffix} ?? 0);
            $chapterEnd   = (int) ($study->{'chapter_end' . $suffix} ?? 0);
            $verseEnd     = (int) ($study->{'verse_end' . $suffix} ?? 0);

            $values[] = implode(', ', [
                (int) $study->id,
                $ordering,
                $book,
                $chapterBegin,
                $verseBegin,
                $chapterEnd,
                $verseEnd,
                $db->quote((string) ($study->{'bible_version' . $suffix} ?? '')),
                $db->quote(
                    CwmscriptureHelper::formatReference($book, $chapterBegin, $verseBegin, $chapterEnd, $verseEnd)
                ),
            ]);
        }

        return $values;
    }

    /**
     * @param   DatabaseInterface  $db  Database driver
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function junctionTableExists(DatabaseInterface $db): bool
    {
        return \in_array($db->getPrefix() . 'bsms_study_scriptures', $db->getTableList(), true);
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
        if (empty($batch)) {
            return;
        }

        $columns = $db->quoteName([
            'study_id', 'ordering', 'booknumber', 'chapter_begin', 'verse_begin',
            'chapter_end', 'verse_end', 'bible_version', 'reference_text',
        ]);

        $query = $db->createQuery()
            ->insert($db->quoteName('#__bsms_study_scriptures'))
            ->columns($columns);

        foreach ($batch as $values) {
            $query->values($values);
        }

        $db->setQuery($query);
        $db->execute();
    }
}
