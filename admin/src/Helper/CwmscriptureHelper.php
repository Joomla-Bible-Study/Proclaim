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
     * Save references and refresh the legacy flat columns as one unit.
     *
     * #__bsms_studies carries a flattened copy of the first two references.
     * Writing the junction table and that copy separately lets them diverge if
     * the second write never happens, and the divergence is permanent -- the
     * flat columns keep describing the previous reference set until some later
     * save happens to succeed. Callers that maintain both should use this.
     *
     * @param   int                   $studyId     Study primary key
     * @param   ScriptureReference[]  $scriptures  References to save
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public static function saveScripturesAndSync(int $studyId, array $scriptures): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $db->transactionStart(true);

        try {
            self::resetScriptureCache($studyId);
            self::writeScriptures($db, $studyId, $scriptures);
            self::syncLegacyColumns($studyId, $scriptures);
            $db->transactionCommit(true);
        } catch (\Throwable $e) {
            try {
                $db->transactionRollback(true);
            } catch (\Throwable) {
                // Nothing to roll back.
            }

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
     * Sync the first two scripture references back to the legacy flat columns on #__bsms_studies.
     *
     * @param   int                    $studyId     Study primary key
     * @param   ScriptureReference[]   $scriptures  All references for the study
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public static function syncLegacyColumns(int $studyId, array $scriptures): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $ref1 = $scriptures[0] ?? null;
        $ref2 = $scriptures[1] ?? null;

        $fields = [];

        if ($ref1 !== null) {
            $fields[] = $db->quoteName('booknumber') . ' = ' . $ref1->booknumber;
            $fields[] = $db->quoteName('chapter_begin') . ' = ' . $ref1->chapterBegin;
            $fields[] = $db->quoteName('verse_begin') . ' = ' . $ref1->verseBegin;
            $fields[] = $db->quoteName('chapter_end') . ' = ' . $ref1->chapterEnd;
            $fields[] = $db->quoteName('verse_end') . ' = ' . $ref1->verseEnd;
            $fields[] = $db->quoteName('bible_version') . ' = ' . $db->quote($ref1->bibleVersion);
        } else {
            $fields[] = $db->quoteName('booknumber') . ' = 0';
            $fields[] = $db->quoteName('chapter_begin') . ' = 0';
            $fields[] = $db->quoteName('verse_begin') . ' = 0';
            $fields[] = $db->quoteName('chapter_end') . ' = 0';
            $fields[] = $db->quoteName('verse_end') . ' = 0';
            $fields[] = $db->quoteName('bible_version') . ' = ' . $db->quote('');
        }

        if ($ref2 !== null) {
            $fields[] = $db->quoteName('booknumber2') . ' = ' . $db->quote((string) $ref2->booknumber);
            $fields[] = $db->quoteName('chapter_begin2') . ' = ' . $db->quote((string) $ref2->chapterBegin);
            $fields[] = $db->quoteName('verse_begin2') . ' = ' . $db->quote((string) $ref2->verseBegin);
            $fields[] = $db->quoteName('chapter_end2') . ' = ' . $db->quote((string) $ref2->chapterEnd);
            $fields[] = $db->quoteName('verse_end2') . ' = ' . $db->quote((string) $ref2->verseEnd);
            $fields[] = $db->quoteName('bible_version2') . ' = ' . $db->quote($ref2->bibleVersion);
        } else {
            $fields[] = $db->quoteName('booknumber2') . ' = NULL';
            $fields[] = $db->quoteName('chapter_begin2') . ' = NULL';
            $fields[] = $db->quoteName('verse_begin2') . ' = NULL';
            $fields[] = $db->quoteName('chapter_end2') . ' = NULL';
            $fields[] = $db->quoteName('verse_end2') . ' = NULL';
            $fields[] = $db->quoteName('bible_version2') . ' = NULL';
        }

        $query = $db->createQuery()
            ->update($db->quoteName('#__bsms_studies'))
            ->set($fields)
            ->where($db->quoteName('id') . ' = ' . $studyId);
        $db->setQuery($query);
        $db->execute();
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
     * ⚠️ A position with no reference is left untouched rather than zeroed, so
     * a row that predates the junction keeps whatever its columns held.
     *
     * @param   object[]  $rows      Rows to annotate, modified in place
     * @param   string    $idColumn  Property holding the study's primary key
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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
                    // No junction reference here, so the legacy columns are all
                    // this row has. bookname is still set, because callers gate
                    // on it and an undefined property is a warning.
                    $row->{'bookname' . $suffix} = ScriptureHelper::getBookName(
                        (int) ($row->{'booknumber' . $suffix} ?? 0)
                    );

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
