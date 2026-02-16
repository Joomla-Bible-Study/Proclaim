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
use Joomla\CMS\Log\Log;

/**
 * Migration Helper class
 *
 * @package  Proclaim.Admin
 * @since    10.3.0
 */
class CwmmigrationHelper
{
    /**
     * Fix Menus
     *
     * @return   bool
     * @since 7.1.0
     */
    public static function fixMenus(): bool
    {
        $db           = Factory::getContainer()->get('DatabaseDriver');
        $replacements = [
            'teacherlist'    => 'cwmteachers',
            'teacherdisplay' => 'cwmteacher',
            'studydetails'   => 'cwmsermon',
            'serieslist'     => 'cwmseriesdisplays',
            'seriesdetail'   => 'cwmseriesdisplay',
            'studieslist'    => 'cwmsermons',
        ];

        foreach ($replacements as $old => $new) {
            $query = $db->getQuery(true);
            $query->update($db->qn('#__menu'))
                ->set($db->qn('link') . ' = REPLACE(' . $db->qn('link') . ', ' . $db->q($old) . ', ' . $db->q($new) . ')')
                ->where($db->qn('menutype') . ' != ' . $db->q('main'))
                ->where($db->qn('link') . ' LIKE ' . $db->q('%com_proclaim%'))
                ->where($db->qn('link') . ' LIKE ' . $db->q('%' . $old . '%'));
            $db->setQuery($query);
            $db->execute();
        }

        return true;
    }

    /**
     * Function to find empty access in the DB and set them to Public
     *
     * @return   bool
     * @throws \Exception
     * @since 7.1.0
     */
    public static function fixemptyaccess(): bool
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        // Tables to fix
        $tables = [
            ['table' => '#__bsms_admin'],
            ['table' => '#__bsms_mediafiles'],
            ['table' => '#__bsms_message_type'],
            ['table' => '#__bsms_podcast'],
            ['table' => '#__bsms_series'],
            ['table' => '#__bsms_servers'],
            ['table' => '#__bsms_studies'],
            ['table' => '#__bsms_studytopics'],
            ['table' => '#__bsms_teachers'],
            ['table' => '#__bsms_templates'],
            ['table' => '#__bsms_topics'],
        ];

        // Get Public ID
        $id = Factory::getApplication()->getConfig()->get('access', 1);

        // Correct blank or not set records
        foreach ($tables as $table) {
            $query = $db->getQuery(true);
            $query->update($db->qn($table['table']))
                ->set($db->qn('access') . ' = ' . (int) $id)
                ->where(
                    '(' . $db->qn('access') . ' = ' . $db->q('0') .
                    ' OR ' . $db->qn('access') . ' = ' . $db->q(' ') . ')'
                );
            $db->setQuery($query);
            $db->execute();
        }

        return true;
    }

    /**
     * Function to find empty language fields and set them to "*"
     *
     * @return   bool
     * @since 7.1.0
     */
    public static function fixemptylanguage(): bool
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        // Tables to fix
        $tables = [
            ['table' => '#__bsms_comments'],
            ['table' => '#__bsms_mediafiles'],
            ['table' => '#__bsms_series'],
            ['table' => '#__bsms_studies'],
            ['table' => '#__bsms_teachers'],
        ];

        // Correct blank records
        foreach ($tables as $table) {
            $query = $db->getQuery(true);
            $query->update($db->qn($table['table']))
                ->set($db->qn('language') . ' = ' . $db->q('*'))
                ->where($db->qn('language') . ' = ' . $db->q(''));
            $db->setQuery($query);
            $db->execute();
        }

        return true;
    }

    /**
     * Old Update URLs
     *
     * @return array
     *
     * @since 7.1
     */
    public static function rmoldurl(): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        return [
            $db->qn('name') . ' = ' .
            $db->q('Proclaim Module'),
            $db->qn('name') . ' = ' .
            $db->q('Proclaim Podcast Module'),
            $db->qn('name') . ' = ' .
            $db->q('Proclaim Finder Plg'),
            $db->qn('name') . ' = ' .
            $db->q('Proclaim Backup Plg'),
            $db->qn('name') . ' = ' .
            $db->q('Proclaim Podcast Plg'),
            $db->qn('name') . ' = ' .
            $db->q('Proclaim'),
        ];
    }

    /**
     * Fix an Import problem
     *
     * @return void
     *
     * @since 7.1
     */
    public static function fixImport(): void
    {
        $db     = Factory::getContainer()->get('DatabaseDriver');
        $tables = CwmdbHelper::getObjects();

        foreach ($tables as $table) {
            if (!str_contains($table['name'], '_bsms_timeset')) {
                $query = $db->getQuery(true);
                $query->select('*')->from($db->qn($table['name']));
                $db->setQuery($query);
                $data = $db->loadObjectList();

                foreach ($data as $row) {
                    $set = false;

                    if (isset($row->params)) {
                        $clean = stripslashes($row->params);

                        if ($clean !== $row->params) {
                            $row->params = $clean;
                            $set         = true;
                        }
                    }

                    if (isset($row->metadata)) {
                        $clean = stripslashes($row->metadata);

                        if ($clean !== $row->metadata) {
                            $row->metadata = $clean;
                            $set           = true;
                        }
                    }

                    if (isset($row->stylecode)) {
                        $clean = stripslashes($row->stylecode);

                        if ($clean !== $row->stylecode) {
                            $row->stylecode = $clean;
                            $set            = true;
                        }
                    }

                    if (isset($row->stylecode)) {
                        $clean = stripslashes($row->stylecode);

                        if ($clean !== $row->stylecode) {
                            $row->stylecode = $clean;
                            $set            = true;
                        }
                    }

                    if ($set) {
                        $db->updateObject($table['name'], $row, ['id']);
                    }
                }
            }
        }
    }

    /**
     * Update messages
     *
     * @param   object  $message  Install object
     *                            $message = new \stdClass();
     *                            $message->title_key = ''; // Language string
     *                            $message->description_key = ''; // Language string
     *                            $message->action_key = ''; // (for action only) Language string
     *                            $message->type = ''; // message | action
     *                            $message->action_file = ''; // (for action only) site://path/to/php | admin://path/to/php
     *                            $message->action = ''; // (for action only) = Function to call
     *                            $message->condition_file = ''; // (for action only) = site://path/to/php | admin://path/to/php
     *                            $message->condition_method = ''; // (for action only) = Function to call
     *                            $message->version_introduced = '10.0.0';
     *
     * @return void
     *
     * @since 7.1
     */
    public static function postInstallMessages(object $message): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        // Find Extension ID of a component
        $query = $db->getQuery(true);
        $query
            ->select($db->qn('extension_id'))
            ->from($db->qn('#__extensions'))
            ->where($db->qn('name') . ' = ' . $db->q('com_proclaim'));
        $db->setQuery($query);
        $biblestudyEid               = $db->loadResult();
        $message->extension_id       = $biblestudyEid;
        $message->language_extension = 'com_proclaim';
        $message->language_client_id = 1;

        if ($db->insertObject('#__postinstall_messages', $message) !== true) {
            log::add('Bad error for PostInstall Message', Log::NOTICE, 'com_proclaim');
        }
    }

    /**
     * Migrate deprecated player types to HTML5 Player
     *
     * Converts legacy player types (3=AVPlugin, 7=LegacyAudio) to player 1 (HTML5 Player)
     * Note: Direct Link (0) is NOT deprecated - it offloads playback to the browser.
     * This is a one-time migration that runs on component install/update.
     *
     * @return int Number of records updated
     *
     * @since 10.1.0
     */
    public static function migrateDeprecatedPlayers(): int
    {
        $db           = Factory::getContainer()->get('DatabaseDriver');
        $totalUpdated = 0;

        // Deprecated player values to migrate to HTML5 Player (1)
        // 3 = AV Plugin (no longer supported), 7 = Legacy Audio Player
        // Note: 0 = Direct Link is still valid and NOT deprecated
        $deprecatedPlayers = ['3', '7'];

        foreach ($deprecatedPlayers as $oldPlayer) {
            // Update using REPLACE on the JSON params field
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_mediafiles'))
                ->set($db->quoteName('params') . ' = REPLACE(' . $db->quoteName('params') . ', '
                    . $db->quote('"player":"' . $oldPlayer . '"') . ', '
                    . $db->quote('"player":"1"') . ')')
                ->where($db->quoteName('params') . ' LIKE ' . $db->quote('%"player":"' . $oldPlayer . '"%'));

            $db->setQuery($query);
            $db->execute();
            $totalUpdated += $db->getAffectedRows();
        }

        if ($totalUpdated > 0) {
            Log::add(
                'Migrated ' . $totalUpdated . ' media files from deprecated players to HTML5 Player',
                Log::INFO,
                'com_proclaim'
            );
        }

        return $totalUpdated;
    }

    /**
     * Ensure teacher aliases are populated and unique, then add UNIQUE KEY if missing.
     *
     * Handles data cleanup that Joomla's ChangeSet cannot execute (UPDATE/DELETE/TEMP TABLE),
     * so the ALTER TABLE ADD UNIQUE KEY succeeds regardless of how the migration was triggered.
     *
     * @return  int  Number of teachers cleaned up
     *
     * @since   10.1.0
     */
    public static function fixTeacherAliases(): int
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $fixed = 0;

        // Step 1: Ensure every teacher has an alias
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__bsms_teachers'))
            ->set(
                $db->quoteName('alias') . ' = LOWER(REPLACE(REPLACE(REPLACE(TRIM('
                . $db->quoteName('teachername') . '), ' . $db->quote(' ') . ', ' . $db->quote('-')
                . '), ' . $db->quote("'") . ', ' . $db->quote('')
                . '), ' . $db->quote('"') . ', ' . $db->quote('') . '))'
            )
            ->where([
                $db->quoteName('alias') . ' = ' . $db->quote(''),
                $db->quoteName('alias') . ' IS NULL',
            ], 'OR');
        $db->setQuery($query);
        $db->execute();
        $fixed += $db->getAffectedRows();

        // Step 2: Merge duplicate teachers (same name, keep lowest ID)
        $mergeSql = 'CREATE TEMPORARY TABLE ' . $db->quoteName('#__bsms_teachers_merge') . ' AS '
            . 'SELECT t1.' . $db->quoteName('id') . ' AS dup_id, '
            . '(SELECT MIN(t2.' . $db->quoteName('id') . ') FROM ' . $db->quoteName('#__bsms_teachers') . ' t2 '
            . 'WHERE LOWER(t2.' . $db->quoteName('teachername') . ') = LOWER(t1.' . $db->quoteName('teachername') . ')) AS keeper_id '
            . 'FROM ' . $db->quoteName('#__bsms_teachers') . ' t1 '
            . 'WHERE t1.' . $db->quoteName('id') . ' > ('
            . 'SELECT MIN(t3.' . $db->quoteName('id') . ') FROM ' . $db->quoteName('#__bsms_teachers') . ' t3 '
            . 'WHERE LOWER(t3.' . $db->quoteName('teachername') . ') = LOWER(t1.' . $db->quoteName('teachername') . '))';
        $db->setQuery($mergeSql);
        $db->execute();

        // Check if there are duplicates to merge
        $db->setQuery('SELECT COUNT(*) FROM ' . $db->quoteName('#__bsms_teachers_merge'));
        $dupCount = (int) $db->loadResult();

        if ($dupCount > 0) {
            // Reassign sermons
            $db->setQuery(
                'UPDATE ' . $db->quoteName('#__bsms_studies') . ' s '
                . 'INNER JOIN ' . $db->quoteName('#__bsms_teachers_merge') . ' m ON s.'
                . $db->quoteName('teacher_id') . ' = m.dup_id '
                . 'SET s.' . $db->quoteName('teacher_id') . ' = m.keeper_id'
            );
            $db->execute();

            // Reassign junction table entries
            if (\in_array(str_replace('#__', $db->getPrefix(), '#__bsms_study_teachers'), $db->getTableList(), true)) {
                $db->setQuery(
                    'UPDATE IGNORE ' . $db->quoteName('#__bsms_study_teachers') . ' st '
                    . 'INNER JOIN ' . $db->quoteName('#__bsms_teachers_merge') . ' m ON st.'
                    . $db->quoteName('teacher_id') . ' = m.dup_id '
                    . 'SET st.' . $db->quoteName('teacher_id') . ' = m.keeper_id'
                );
                $db->execute();

                // Delete junction rows that became duplicates after reassignment
                $db->setQuery(
                    'DELETE st1 FROM ' . $db->quoteName('#__bsms_study_teachers') . ' st1 '
                    . 'INNER JOIN ' . $db->quoteName('#__bsms_study_teachers') . ' st2 '
                    . 'ON st1.' . $db->quoteName('study_id') . ' = st2.' . $db->quoteName('study_id')
                    . ' AND st1.' . $db->quoteName('teacher_id') . ' = st2.' . $db->quoteName('teacher_id')
                    . ' AND st1.' . $db->quoteName('id') . ' > st2.' . $db->quoteName('id')
                );
                $db->execute();
            }

            // Reassign series
            $db->setQuery(
                'UPDATE ' . $db->quoteName('#__bsms_series') . ' sr '
                . 'INNER JOIN ' . $db->quoteName('#__bsms_teachers_merge') . ' m ON sr.'
                . $db->quoteName('teacher') . ' = m.dup_id '
                . 'SET sr.' . $db->quoteName('teacher') . ' = m.keeper_id'
            );
            $db->execute();

            // Remove asset entries for duplicates
            $db->setQuery(
                'DELETE FROM ' . $db->quoteName('#__assets')
                . ' WHERE ' . $db->quoteName('name') . ' IN ('
                . 'SELECT CONCAT(' . $db->quote('com_proclaim.teacher.') . ', dup_id) FROM '
                . $db->quoteName('#__bsms_teachers_merge') . ')'
            );
            $db->execute();

            // Delete duplicate teachers
            $db->setQuery(
                'DELETE FROM ' . $db->quoteName('#__bsms_teachers')
                . ' WHERE ' . $db->quoteName('id') . ' IN ('
                . 'SELECT dup_id FROM ' . $db->quoteName('#__bsms_teachers_merge') . ')'
            );
            $db->execute();
            $fixed += $dupCount;
        }

        $db->setQuery('DROP TEMPORARY TABLE IF EXISTS ' . $db->quoteName('#__bsms_teachers_merge'));
        $db->execute();

        // Step 3: Deduplicate aliases by appending -ID to collisions
        $dupAliasSql = 'CREATE TEMPORARY TABLE ' . $db->quoteName('#__bsms_teachers_dup_ids') . ' AS '
            . 'SELECT t2.' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__bsms_teachers') . ' t2 '
            . 'WHERE EXISTS ('
            . 'SELECT 1 FROM ' . $db->quoteName('#__bsms_teachers') . ' t3 '
            . 'WHERE LOWER(t3.' . $db->quoteName('alias') . ') = LOWER(t2.' . $db->quoteName('alias') . ') '
            . 'AND t3.' . $db->quoteName('id') . ' < t2.' . $db->quoteName('id') . ')';
        $db->setQuery($dupAliasSql);
        $db->execute();

        $db->setQuery(
            'UPDATE ' . $db->quoteName('#__bsms_teachers')
            . ' SET ' . $db->quoteName('alias') . ' = CONCAT(' . $db->quoteName('alias') . ', '
            . $db->quote('-') . ', ' . $db->quoteName('id') . ')'
            . ' WHERE ' . $db->quoteName('id') . ' IN ('
            . 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__bsms_teachers_dup_ids') . ')'
        );
        $db->execute();
        $fixed += $db->getAffectedRows();

        $db->setQuery('DROP TEMPORARY TABLE IF EXISTS ' . $db->quoteName('#__bsms_teachers_dup_ids'));
        $db->execute();

        // Step 4: Add UNIQUE KEY if missing
        $db->setQuery(
            'SHOW INDEX FROM ' . $db->quoteName('#__bsms_teachers')
            . ' WHERE ' . $db->quoteName('Key_name') . ' = ' . $db->quote('idx_alias')
        );

        if (!$db->loadResult()) {
            try {
                $db->setQuery(
                    'ALTER TABLE ' . $db->quoteName('#__bsms_teachers')
                    . ' ADD UNIQUE KEY ' . $db->quoteName('idx_alias') . ' (' . $db->quoteName('alias') . ')'
                );
                $db->execute();
            } catch (\Exception $e) {
                Log::add('Could not add idx_alias: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
            }
        }

        if ($fixed > 0) {
            Log::add('Fixed ' . $fixed . ' teacher alias/duplicate issues', Log::INFO, 'com_proclaim');
        }

        return $fixed;
    }

    /**
     * Populate the study_teachers junction table from the legacy teacher_id column.
     *
     * Ensures every study with a teacher_id has a corresponding junction table entry.
     * Safe to run multiple times (uses INSERT IGNORE).
     *
     * @return  int  Number of rows inserted
     *
     * @since   10.1.0
     */
    public static function populateStudyTeachers(): int
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Check if junction table exists
        $junctionTable = str_replace('#__', $db->getPrefix(), '#__bsms_study_teachers');

        if (!\in_array($junctionTable, $db->getTableList(), true)) {
            return 0;
        }

        $db->setQuery(
            'INSERT IGNORE INTO ' . $db->quoteName('#__bsms_study_teachers')
            . ' (' . $db->quoteName('study_id') . ', ' . $db->quoteName('teacher_id') . ', ' . $db->quoteName('ordering') . ')'
            . ' SELECT ' . $db->quoteName('id') . ', ' . $db->quoteName('teacher_id') . ', 0'
            . ' FROM ' . $db->quoteName('#__bsms_studies')
            . ' WHERE ' . $db->quoteName('teacher_id') . ' IS NOT NULL'
            . ' AND ' . $db->quoteName('teacher_id') . ' > 0'
        );
        $db->execute();
        $inserted = $db->getAffectedRows();

        if ($inserted > 0) {
            Log::add('Populated ' . $inserted . ' study-teacher junction records from legacy column', Log::INFO, 'com_proclaim');
        }

        return $inserted;
    }

    /**
     * Ensure bible_translations table is seeded with known translations.
     *
     * After a restore or migration, the seed data (INSERT IGNORE) from SQL
     * update files is NOT executed by Joomla's ChangeSet (it only runs DDL).
     * This method ensures the translation catalogue is populated so the
     * Bible version picker works out-of-the-box.
     *
     * Uses INSERT IGNORE so existing rows (e.g. from backup) are preserved,
     * and already-downloaded translations keep their `installed`/`verse_count` state.
     *
     * @return  int  Number of rows inserted
     *
     * @since   10.1.0
     */
    public static function seedBibleTranslations(): int
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Check if the table exists
        $tableName = str_replace('#__', $db->getPrefix(), '#__bsms_bible_translations');

        if (!\in_array($tableName, $db->getTableList(), true)) {
            return 0;
        }

        // Canonical translation catalogue — must match install.mysql.utf8.sql / 10.1.0-20260209.sql
        $translations = [
            ['kjv', 'King James Version', 'en', 'getbible', 0, 1, 4000000],
            ['akjv', 'American King James Version', 'en', 'getbible', 0, 0, 4000000],
            ['web', 'World English Bible', 'en', 'getbible', 0, 1, 4300000],
            ['asv', 'American Standard Version', 'en', 'getbible', 0, 0, 4100000],
            ['ylt', 'Young\'s Literal Translation', 'en', 'getbible', 0, 0, 4000000],
            ['basicenglish', 'Bible in Basic English', 'en', 'getbible', 0, 0, 3500000],
            ['douayrheims', 'Douay-Rheims Bible', 'en', 'getbible', 0, 0, 4200000],
            ['wb', 'Webster Bible', 'en', 'getbible', 0, 0, 4000000],
            ['darby', 'Darby Translation', 'en', 'getbible', 0, 0, 4000000],
            ['vulgate', 'Vulgata Clementina', 'la', 'getbible', 0, 0, 3800000],
            ['almeida', 'Almeida Atualizada', 'pt', 'getbible', 0, 0, 4000000],
            ['luther1545', 'Luther (1545)', 'de', 'getbible', 0, 0, 4200000],
            ['ls1910', 'Louis Segond 1910', 'fr', 'getbible', 0, 0, 4100000],
            ['synodal', 'Synodal Translation', 'ru', 'getbible', 0, 0, 4500000],
            ['valera', 'Reina Valera (1909)', 'es', 'getbible', 0, 0, 4100000],
            ['karoli', 'Károli Bible', 'hu', 'getbible', 0, 0, 4000000],
            ['giovanni', 'Giovanni Diodati Bible', 'it', 'getbible', 0, 0, 4100000],
            ['cornilescu', 'Cornilescu Bible', 'ro', 'getbible', 0, 0, 3900000],
            ['korean', 'Korean Bible', 'ko', 'getbible', 0, 0, 3800000],
            ['cus', 'Chinese Union Simplified', 'zh', 'getbible', 0, 0, 2500000],
        ];

        $columns = [
            $db->quoteName('abbreviation'),
            $db->quoteName('name'),
            $db->quoteName('language'),
            $db->quoteName('source'),
            $db->quoteName('installed'),
            $db->quoteName('bundled'),
            $db->quoteName('estimated_size'),
        ];

        $inserted = 0;

        foreach ($translations as $row) {
            $values = $db->quote($row[0]) . ', '
                . $db->quote($row[1]) . ', '
                . $db->quote($row[2]) . ', '
                . $db->quote($row[3]) . ', '
                . (int) $row[4] . ', '
                . (int) $row[5] . ', '
                . (int) $row[6];

            $query = 'INSERT IGNORE INTO ' . $db->quoteName('#__bsms_bible_translations')
                . ' (' . implode(', ', $columns) . ')'
                . ' VALUES (' . $values . ')';

            $db->setQuery($query);
            $db->execute();
            $inserted += $db->getAffectedRows();
        }

        // Fix legacy abbreviations that may still exist from older seed data
        $renames = [
            'asvd'       => ['asv', 'American Standard Version'],
            'clementine' => ['vulgate', 'Vulgata Clementina'],
            'luther1912' => ['luther1545', 'Luther (1545)'],
            'rv1909'     => ['valera', 'Reina Valera (1909)'],
            'cuvs'       => ['cus', 'Chinese Union Simplified'],
        ];

        foreach ($renames as $oldAbbr => [$newAbbr, $newName]) {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_bible_translations'))
                ->set($db->quoteName('abbreviation') . ' = ' . $db->quote($newAbbr))
                ->set($db->quoteName('name') . ' = ' . $db->quote($newName))
                ->where($db->quoteName('abbreviation') . ' = ' . $db->quote($oldAbbr));
            $db->setQuery($query);
            $db->execute();
        }

        // Remove deprecated abbreviation
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__bsms_bible_translations'))
            ->where($db->quoteName('abbreviation') . ' = ' . $db->quote('webbe'));
        $db->setQuery($query);
        $db->execute();

        // Reconcile installed/verse_count with actual verse data.
        // After restore the translations table may be freshly seeded (installed=0)
        // while the verses table was preserved and still has downloaded data.
        self::reconcileBibleTranslations();

        if ($inserted > 0) {
            Log::add('Seeded ' . $inserted . ' bible translations', Log::INFO, 'com_proclaim');
        }

        return $inserted;
    }

    /**
     * Reconcile bible_translations installed/verse_count with actual verse data.
     *
     * After a restore, the translations catalogue may be freshly seeded
     * (installed=0, verse_count=0) while the bible_verses table was
     * preserved and still contains downloaded verse data.  This method
     * syncs the two tables so the admin UI correctly shows which
     * translations are already available locally.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function reconcileBibleTranslations(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Check both tables exist
        $prefix     = $db->getPrefix();
        $tableList  = $db->getTableList();
        $transTable = str_replace('#__', $prefix, '#__bsms_bible_translations');
        $verseTable = str_replace('#__', $prefix, '#__bsms_bible_verses');

        if (!\in_array($transTable, $tableList, true) || !\in_array($verseTable, $tableList, true)) {
            return;
        }

        // Get actual verse counts per translation from the verses table
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('translation'),
                'COUNT(*) AS ' . $db->quoteName('cnt'),
            ])
            ->from($db->quoteName('#__bsms_bible_verses'))
            ->group($db->quoteName('translation'));
        $db->setQuery($query);
        $counts = $db->loadObjectList('translation');

        if (empty($counts)) {
            // No verses in DB — mark all as not installed
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_bible_translations'))
                ->set($db->quoteName('installed') . ' = 0')
                ->set($db->quoteName('verse_count') . ' = 0')
                ->where($db->quoteName('installed') . ' = 1');
            $db->setQuery($query);
            $db->execute();

            return;
        }

        // Update translations that have verses
        foreach ($counts as $abbr => $row) {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_bible_translations'))
                ->set($db->quoteName('installed') . ' = 1')
                ->set($db->quoteName('verse_count') . ' = ' . (int) $row->cnt)
                ->where($db->quoteName('abbreviation') . ' = ' . $db->quote($abbr));
            $db->setQuery($query);
            $db->execute();
        }

        // Mark translations with no verses as not installed
        $installedAbbrs = array_keys($counts);

        if (!empty($installedAbbrs)) {
            $quoted = array_map([$db, 'quote'], $installedAbbrs);
            $query  = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_bible_translations'))
                ->set($db->quoteName('installed') . ' = 0')
                ->set($db->quoteName('verse_count') . ' = 0')
                ->where($db->quoteName('abbreviation') . ' NOT IN (' . implode(',', $quoted) . ')')
                ->where($db->quoteName('installed') . ' = 1');
            $db->setQuery($query);
            $db->execute();
        }

        Log::add('Reconciled bible translation install status with verse data', Log::INFO, 'com_proclaim');
    }

    /**
     * Rewrite legacy component media paths inside mediafile params JSON.
     *
     * Converts `media/com_biblestudy/` to `media/com_proclaim/` (component rename).
     * Does NOT touch `images/biblestudy/` paths — that filesystem folder still exists.
     * Safe to run multiple times — only updates rows that still contain the old paths.
     *
     * @return  int  Number of mediafile rows updated
     *
     * @since   10.1.0
     */
    public static function fixMediafileLegacyPaths(): int
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $replacements = [
            // JSON-escaped form: media\/com_biblestudy\/ → media\/com_proclaim\/
            'media\\/com_biblestudy\\/' => 'media\\/com_proclaim\\/',
        ];

        $updated = 0;

        foreach ($replacements as $search => $replace) {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_mediafiles'))
                ->set(
                    $db->quoteName('params') . ' = REPLACE('
                    . $db->quoteName('params') . ', '
                    . $db->quote($search) . ', '
                    . $db->quote($replace) . ')'
                )
                ->where($db->quoteName('params') . ' LIKE ' . $db->quote('%' . $search . '%'));
            $db->setQuery($query);
            $db->execute();
            $updated += $db->getAffectedRows();
        }

        if ($updated > 0) {
            Log::add('Fixed legacy image paths in ' . $updated . ' mediafile rows', Log::INFO, 'com_proclaim');
        }

        return $updated;
    }
}
