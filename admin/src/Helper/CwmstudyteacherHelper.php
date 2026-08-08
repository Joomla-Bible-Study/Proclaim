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
use Joomla\Database\DatabaseInterface;

/**
 * Helper for managing the many-to-many relationship between studies and teachers
 * via the `#__bsms_study_teachers` junction table.
 *
 * Follows the same pattern as CwmscriptureHelper.
 *
 * @since  10.1.0
 */
class CwmstudyteacherHelper
{
    /**
     * Static cache of teachers keyed by study_id.
     *
     * @var array<int, object[]>
     *
     * @since 10.1.0
     */
    private static array $teacherCache = [];

    /**
     * Load teachers for a single study.
     *
     * @param   int  $studyId  Study primary key
     *
     * @return  object[]  Array of junction row objects (teacher_id, ordering, teachername, title, teacher_thumbnail, teacher_image)
     *
     * @since  10.1.0
     */
    public static function getTeachersForStudy(int $studyId): array
    {
        if ($studyId <= 0) {
            return [];
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select([
                $db->quoteName('st.teacher_id'),
                $db->quoteName('st.ordering'),
                $db->quoteName('t.teachername'),
                $db->quoteName('t.title'),
                $db->quoteName('t.teacher_thumbnail'),
                $db->quoteName('t.image', 'teacher_image'),
            ])
            ->from($db->quoteName('#__bsms_study_teachers', 'st'))
            ->join(
                'LEFT',
                $db->quoteName('#__bsms_teachers', 't') . ' ON '
                . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.teacher_id')
            )
            ->where($db->quoteName('st.study_id') . ' = ' . $studyId)
            ->order($db->quoteName('st.ordering') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Batch-load teachers for multiple studies with caching.
     *
     * @param   int[]  $studyIds  Array of study primary keys
     *
     * @return  array<int, object[]>  Keyed by study_id
     *
     * @since  10.1.0
     */
    public static function getTeachersForStudies(array $studyIds): array
    {
        $studyIds = array_values(array_filter(array_map('intval', $studyIds), static fn (int $id) => $id > 0));

        if (empty($studyIds)) {
            return [];
        }

        // Determine which IDs are not yet cached
        $uncached = array_values(array_filter($studyIds, static fn (int $id) => !isset(self::$teacherCache[$id])));

        if (!empty($uncached)) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('st.study_id'),
                    $db->quoteName('st.teacher_id'),
                    $db->quoteName('st.ordering'),
                    $db->quoteName('t.teachername'),
                    $db->quoteName('t.title'),
                    $db->quoteName('t.teacher_thumbnail'),
                    $db->quoteName('t.image', 'teacher_image'),
                ])
                ->from($db->quoteName('#__bsms_study_teachers', 'st'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__bsms_teachers', 't') . ' ON '
                    . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.teacher_id')
                )
                ->whereIn($db->quoteName('st.study_id'), $uncached)
                ->order($db->quoteName('st.study_id') . ' ASC, ' . $db->quoteName('st.ordering') . ' ASC');
            $db->setQuery($query);
            $rows = $db->loadObjectList();

            // Pre-fill cache entries for all requested IDs (some may have zero teachers)
            foreach ($uncached as $id) {
                self::$teacherCache[$id] = [];
            }

            foreach ($rows as $row) {
                $sid                        = (int) $row->study_id;
                self::$teacherCache[$sid][] = $row;
            }
        }

        // Build result from cache
        $result = [];

        foreach ($studyIds as $id) {
            $result[$id] = self::$teacherCache[$id] ?? [];
        }

        return $result;
    }

    /**
     * Clear the static teacher cache, optionally for a single study.
     *
     * @param   int|null  $studyId  Specific study to evict, or null to clear all
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function resetCache(?int $studyId = null): void
    {
        if ($studyId !== null) {
            unset(self::$teacherCache[$studyId]);
        } else {
            self::$teacherCache = [];
        }
    }

    /**
     * Save teachers for a study (delete + insert).
     *
     * @param   int      $studyId   Study primary key
     * @param   array    $teachers  Array of ['teacher_id' => int] entries
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public static function saveTeachers(int $studyId, array $teachers): void
    {
        self::resetCache($studyId);

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // The delete and the inserts are one unit. CwmmessageModel::save()
        // commits the study row before calling this, so a failure partway
        // through the loop left the study saved but its teacher list
        // truncated, with the administrator seeing a fatal as though nothing
        // had been written.
        //
        // Savepoint-aware: MysqliDriver::transactionStart() calls
        // begin_transaction() unconditionally otherwise, and MySQL implicitly
        // commits any transaction already open when it does.
        $db->transactionStart(true);

        try {
            self::writeTeachers($db, $studyId, $teachers);
            $db->transactionCommit(true);
        } catch (\Throwable $e) {
            try {
                $db->transactionRollback(true);
            } catch (\Throwable) {
                // Nothing to roll back.
            }

            self::resetCache($studyId);

            throw $e;
        }
    }

    /**
     * Save the junction rows and the legacy teacher_id column as one unit.
     *
     * #__bsms_studies.teacher_id holds the primary teacher, duplicated from the
     * junction table. Writing the two separately lets them diverge when the
     * second write never happens, and nothing reconciles that afterwards.
     *
     * @param   int      $studyId   Study primary key
     * @param   array[]  $teachers  Teacher entries to save
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public static function saveTeachersAndSync(int $studyId, array $teachers): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $db->transactionStart(true);

        try {
            self::resetCache($studyId);
            self::writeTeachers($db, $studyId, $teachers);
            self::syncLegacyColumn($studyId, $teachers);
            $db->transactionCommit(true);
        } catch (\Throwable $e) {
            try {
                $db->transactionRollback(true);
            } catch (\Throwable) {
                // Nothing to roll back.
            }

            self::resetCache($studyId);

            throw $e;
        }
    }

    /**
     * Replace a study's teacher rows. Caller owns the transaction.
     *
     * @param   DatabaseInterface  $db        Database driver
     * @param   int                $studyId   Study primary key
     * @param   array[]            $teachers  Teacher entries to write
     *
     * @return  void
     *
     * @since   10.5.6
     */
    private static function writeTeachers(DatabaseInterface $db, int $studyId, array $teachers): void
    {
        $query = $db->createQuery()
            ->delete($db->quoteName('#__bsms_study_teachers'))
            ->where($db->quoteName('study_id') . ' = ' . $studyId);
        $db->setQuery($query);
        $db->execute();

        // Insert new (skip duplicates and empty entries)
        $seen = [];

        foreach ($teachers as $entry) {
            $teacherId = (int) ($entry['teacher_id'] ?? 0);

            if ($teacherId <= 0 || isset($seen[$teacherId])) {
                continue;
            }

            $seen[$teacherId] = true;

            $columns = ['study_id', 'teacher_id', 'ordering'];
            $values  = [
                $studyId,
                $teacherId,
                \count($seen) - 1,
            ];

            $insert = $db->createQuery()
                ->insert($db->quoteName('#__bsms_study_teachers'))
                ->columns($db->quoteName($columns))
                ->values(implode(', ', $values));
            $db->setQuery($insert);
            $db->execute();
        }
    }

    /**
     * Sync the primary teacher (ordering=0) back to studies.teacher_id for backwards compatibility.
     *
     * @param   int    $studyId   Study primary key
     * @param   array  $teachers  Array of ['teacher_id' => int] entries
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public static function syncLegacyColumn(int $studyId, array $teachers): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // First teacher in the list is the primary
        $primaryId = 0;

        foreach ($teachers as $entry) {
            $tid = (int) ($entry['teacher_id'] ?? 0);

            if ($tid > 0) {
                $primaryId = $tid;
                break;
            }
        }

        $query = $db->createQuery()
            ->update($db->quoteName('#__bsms_studies'))
            ->set($db->quoteName('teacher_id') . ' = ' . $primaryId)
            ->where($db->quoteName('id') . ' = ' . $studyId);
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Delete all teacher associations for a study.
     *
     * @param   int  $studyId  Study primary key
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public static function deleteTeachers(int $studyId): void
    {
        self::resetCache($studyId);

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->delete($db->quoteName('#__bsms_study_teachers'))
            ->where($db->quoteName('study_id') . ' = ' . $studyId);
        $db->setQuery($query);
        $db->execute();
    }
}
