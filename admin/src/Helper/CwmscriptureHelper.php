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

use CWM\Library\Scripture\Helper\ScriptureHelper;
use CWM\Library\Scripture\Helper\ScriptureReference;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Proclaim-specific scripture helper.
 *
 * Extends the shared CWM Scripture Library helper with study-specific methods
 * for managing scripture references attached to Proclaim studies.
 *
 * Generic methods (parseReference, formatReference, getBookNumber, getBookName,
 * getAllBooks, getAbbreviations) are inherited from the library.
 *
 * @since  10.1.0
 */
class CwmscriptureHelper extends ScriptureHelper
{
    /**
     * Static cache for batch-loaded scripture references, keyed by study_id.
     *
     * @var array<int, ScriptureReference[]>
     * @since 10.1.0
     */
    private static array $scriptureCache = [];

    /**
     * Load all scripture references for a single study from the junction table.
     *
     * @param   int  $studyId  Study primary key
     *
     * @return  ScriptureReference[]
     *
     * @since  10.1.0
     */
    public static function getScripturesForStudy(int $studyId): array
    {
        if ($studyId <= 0) {
            return [];
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__bsms_study_scriptures'))
            ->where($db->quoteName('study_id') . ' = ' . $studyId)
            ->order($db->quoteName('ordering') . ' ASC');
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        if (empty($rows)) {
            return [];
        }

        $result = [];

        foreach ($rows as $row) {
            $result[] = ScriptureReference::fromRow($row);
        }

        return $result;
    }

    /**
     * Batch-load scripture references for multiple studies.
     *
     * @param   int[]  $studyIds  Array of study primary keys
     *
     * @return  array<int, ScriptureReference[]>  Keyed by study_id
     *
     * @since  10.1.0
     */
    public static function getScripturesForStudies(array $studyIds): array
    {
        $studyIds = array_filter(array_map('intval', $studyIds));

        if (empty($studyIds)) {
            return [];
        }

        $uncached = array_values(array_filter($studyIds, static fn (int $id) => !isset(self::$scriptureCache[$id])));

        if (!empty($uncached)) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->select('*')
                ->from($db->quoteName('#__bsms_study_scriptures'))
                ->whereIn($db->quoteName('study_id'), $uncached)
                ->order($db->quoteName('study_id') . ' ASC, ' . $db->quoteName('ordering') . ' ASC');
            $db->setQuery($query);
            $rows = $db->loadObjectList();

            foreach ($uncached as $id) {
                self::$scriptureCache[$id] = [];
            }

            foreach ($rows as $row) {
                $sid                          = (int) $row->study_id;
                self::$scriptureCache[$sid][] = ScriptureReference::fromRow($row);
            }
        }

        $result = [];

        foreach ($studyIds as $id) {
            $result[$id] = self::$scriptureCache[$id] ?? [];
        }

        return $result;
    }

    /**
     * Clear the static scripture cache, optionally for a single study.
     *
     * @param   int|null  $studyId  Specific study to evict, or null to clear all
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function resetScriptureCache(?int $studyId = null): void
    {
        if ($studyId !== null) {
            unset(self::$scriptureCache[$studyId]);
        } else {
            self::$scriptureCache = [];
        }
    }

    /**
     * Save scripture references for a study (delete + insert).
     *
     * @param   int                    $studyId     Study primary key
     * @param   ScriptureReference[]   $scriptures  References to save
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public static function saveScriptures(int $studyId, array $scriptures): void
    {
        self::resetScriptureCache($studyId);

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // The delete and the inserts are one unit. Without that, an insert
        // failing partway through leaves the study with its old references
        // gone and only some of the new ones written, which nothing later
        // reconciles.
        //
        // Savepoint-aware so this can nest inside a caller's transaction:
        // MysqliDriver::transactionStart() calls begin_transaction()
        // unconditionally otherwise, and MySQL implicitly commits any
        // transaction already open when it does.
        $db->transactionStart(true);

        try {
            self::writeScriptures($db, $studyId, $scriptures);
            $db->transactionCommit(true);
        } catch (\Throwable $e) {
            try {
                $db->transactionRollback(true);
            } catch (\Throwable) {
                // Nothing to roll back.
            }

            // The cache was cleared before the write; clear it again so a
            // reader after the rollback re-reads the restored rows.
            self::resetScriptureCache($studyId);

            throw $e;
        }
    }


    /**
     * Replace a study's reference rows. Caller owns the transaction.
     *
     * @param   DatabaseInterface     $db          Database driver
     * @param   int                   $studyId     Study primary key
     * @param   ScriptureReference[]  $scriptures  References to write
     *
     * @return  void
     *
     * @since   10.5.6
     */
    private static function writeScriptures(DatabaseInterface $db, int $studyId, array $scriptures): void
    {
        $query = $db->createQuery()
            ->delete($db->quoteName('#__bsms_study_scriptures'))
            ->where($db->quoteName('study_id') . ' = ' . $studyId);
        $db->setQuery($query);
        $db->execute();

        foreach ($scriptures as $i => $ref) {
            $columns = [
                'study_id', 'ordering', 'booknumber', 'chapter_begin', 'verse_begin',
                'chapter_end', 'verse_end', 'bible_version', 'reference_text',
            ];
            $values = [
                $studyId,
                $i,
                $ref->booknumber,
                $ref->chapterBegin,
                $ref->verseBegin,
                $ref->chapterEnd,
                $ref->verseEnd,
                $db->quote($ref->bibleVersion),
                $db->quote($ref->referenceText),
            ];

            $insert = $db->createQuery()
                ->insert($db->quoteName('#__bsms_study_scriptures'))
                ->columns($db->quoteName($columns))
                ->values(implode(', ', $values));
            $db->setQuery($insert);
            $db->execute();
        }
    }


    /**
     * Delete all scripture references for a study.
     *
     * @param   int  $studyId  Study primary key
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public static function deleteScriptures(int $studyId): void
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->delete($db->quoteName('#__bsms_study_scriptures'))
            ->where($db->quoteName('study_id') . ' = ' . $studyId);
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Project a study's first two scripture references onto the row properties
     * that templates read: `bookname`, `booknumber`, `chapter_begin`,
     * `verse_begin`, `chapter_end`, `verse_end`, `bible_version`, and the `2`
     * suffixed set for the second reference.
     *
     * These properties are read by template overrides outside this repository
     * and by helpers such as Cwmshowscripture::formReference(), so they stay on
     * the row even though the junction is the source. Rows that already carry
     * `scriptures` cost nothing; the rest are batch-loaded in one query.
     *
     * @param   object[]  $rows      Rows to annotate, modified in place
     * @param   string    $idColumn  Property holding the study's primary key
     *
     * @return  void
     *
     * @since   10.5.8
     */
    public static function applyBookNames(array $rows, string $idColumn = 'id'): void
    {
        $missing = [];

        foreach ($rows as $row) {
            if (!isset($row->scriptures) && !empty($row->{$idColumn})) {
                $missing[] = (int) $row->{$idColumn};
            }
        }

        $loaded = $missing === [] ? [] : self::getScripturesForStudies(array_unique($missing));

        foreach ($rows as $row) {
            $refs = $row->scriptures ?? ($loaded[(int) ($row->{$idColumn} ?? 0)] ?? []);
            $refs = \is_array($refs) ? array_values($refs) : [];

            foreach ([0 => '', 1 => '2'] as $index => $suffix) {
                $ref = $refs[$index] ?? null;

                if (!$ref instanceof ScriptureReference || $ref->booknumber <= 0) {
                    // Still defined, because callers gate on it and an undefined
                    // property is a warning.
                    $row->{'bookname' . $suffix} = '';

                    continue;
                }

                $row->{'bookname' . $suffix}      = ScriptureHelper::getBookName($ref->booknumber);
                $row->{'booknumber' . $suffix}    = $ref->booknumber;
                $row->{'chapter_begin' . $suffix} = $ref->chapterBegin;
                $row->{'verse_begin' . $suffix}   = $ref->verseBegin;
                $row->{'chapter_end' . $suffix}   = $ref->chapterEnd;
                $row->{'verse_end' . $suffix}     = $ref->verseEnd;
                $row->{'bible_version' . $suffix} = $ref->bibleVersion;
            }
        }
    }
}
