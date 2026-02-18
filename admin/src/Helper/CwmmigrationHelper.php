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

use Joomla\CMS\Component\ComponentHelper;
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

    // =========================================================================
    // Phase 4: Location migration (access-level → location_id)
    // =========================================================================

    /**
     * Main orchestrator for migrating access-level campus separation to the
     * location system introduced in Proclaim 10.1.
     *
     * Detects the current installation scenario and runs the appropriate
     * sub-migration.  Safe to re-run — already-migrated data is skipped.
     *
     * @return  array{scenario: string, locations_created: int, messages_updated: int,
     *                groups_mapped: int, teachers_linked: int, teachers_unlinked: int,
     *                timestamp: string}  Migration report.
     *
     * @since   10.1.0
     */
    public static function migrateAccessToLocations(): array
    {
        $scenario = self::detectMigrationScenario();

        $report = [
            'scenario'          => $scenario,
            'locations_created' => 0,
            'messages_updated'  => 0,
            'groups_mapped'     => 0,
            'teachers_linked'   => self::countLinkedTeachers(),
            'teachers_unlinked' => \count(self::getUnlinkedTeachers()),
            'timestamp'         => date('Y-m-d H:i:s'),
        ];

        switch ($scenario) {
            case '2B':
                // Already using locations — validate data integrity and suggest mappings
                $report['valid'] = self::validateExistingLocations() ? 1 : 0;
                break;

            case '2A':
                // Multi-campus via access levels — create locations and map messages
                $accessLevels = self::getDistinctNonPublicAccessLevels();
                $locationMap  = []; // accessLevelId => newLocationId

                foreach ($accessLevels as $accessLevel) {
                    $locationId                          = self::createLocationFromAccess($accessLevel);
                    $locationMap[(int) $accessLevel->id] = $locationId;
                    $report['locations_created']++;
                }

                // Map messages: access level → location_id
                $report['messages_updated'] = self::mapMessagesToLocations($locationMap);

                // Store group→location mappings in component params
                $mappings                 = self::buildGroupLocationMappings($locationMap);
                self::createGroupLocationMappings($mappings);
                $report['groups_mapped']  = \count($mappings);

                // Normalise all message access to Public (1) after location assignment
                self::normalizeAccessLevelsToPublic();
                break;

            case '2C':
            default:
                // Single campus — create a default location and assign all messages
                $locationId                  = self::createDefaultLocation();
                $report['locations_created'] = $locationId > 0 ? 1 : 0;
                $report['messages_updated']  = self::assignAllMessagesToLocation($locationId);
                break;
        }

        Log::add(
            'Location migration complete. Scenario: ' . $scenario
            . ' | created: ' . $report['locations_created']
            . ' | updated: ' . $report['messages_updated'],
            Log::INFO,
            'com_proclaim'
        );

        return $report;
    }

    /**
     * Detect which migration scenario applies to the current installation.
     *
     *   2B — Messages already have location_id values (no data migration needed).
     *   2A — Messages use distinct non-Public access levels for campus separation.
     *   2C — Single-campus install (all messages are Public or one access level).
     *
     * @return  string  '2A', '2B', or '2C'.
     *
     * @since   10.1.0
     */
    public static function detectMigrationScenario(): string
    {
        if (self::getMessagesWithLocations() > 0) {
            return '2B';
        }

        if (\count(self::getDistinctNonPublicAccessLevels()) >= 2) {
            return '2A';
        }

        return '2C';
    }

    /**
     * Count how many published messages already have a location_id assigned.
     *
     * @return  int
     *
     * @since   10.1.0
     */
    public static function getMessagesWithLocations(): int
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_studies'))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('location_id') . ' > 0');
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Return the distinct non-Public Joomla view levels that are currently
     * used on published messages.
     *
     * @return  \stdClass[]  Each object has ->id and ->title.
     *
     * @since   10.1.0
     */
    public static function getDistinctNonPublicAccessLevels(): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Subquery: distinct non-Public access values used by published messages
        $sub = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('access'))
            ->from($db->quoteName('#__bsms_studies'))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('access') . ' > 1'); // 1 = Public

        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('title')])
            ->from($db->quoteName('#__viewlevels'))
            ->where($db->quoteName('id') . ' IN (' . $sub . ')');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Return the count of published locations.
     *
     * @return  int
     *
     * @since   10.1.0
     */
    public static function getPublishedLocationCount(): int
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_locations'))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    // -------------------------------------------------------------------------
    // Scenario 2A helpers
    // -------------------------------------------------------------------------

    /**
     * Create a location record named after the given Joomla view level.
     * If a location with the same name already exists it is reused (idempotent).
     *
     * @param   \stdClass  $accessLevel  Object with ->id and ->title properties.
     *
     * @return  int  The location ID (new or existing).
     *
     * @since   10.1.0
     */
    public static function createLocationFromAccess(\stdClass $accessLevel): int
    {
        $db   = Factory::getContainer()->get('DatabaseDriver');
        $name = trim((string) $accessLevel->title);

        // Check for existing location with this name
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_locations'))
            ->where($db->quoteName('location_text') . ' = ' . $db->quote($name));
        $db->setQuery($query);
        $existing = (int) $db->loadResult();

        if ($existing > 0) {
            return $existing;
        }

        // Insert new location
        $alias              = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe($name);
        $row                = new \stdClass();
        $row->location_text = $name;
        $row->alias         = $alias ?: 'location-' . (int) $accessLevel->id;
        $row->published     = 1;
        $row->access        = 1; // Public
        $db->insertObject('#__bsms_locations', $row);

        $newId = (int) $db->insertid();
        Log::add('Created location "' . $name . '" (ID ' . $newId . ') from access level', Log::INFO, 'com_proclaim');

        return $newId;
    }

    /**
     * Persist the group→location mappings array in component parameters.
     *
     * Merges with existing mappings so repeated calls are safe.
     *
     * @param   array<int, int[]>  $mappings  locationId → [groupId, ...]
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function createGroupLocationMappings(array $mappings): void
    {
        $params   = ComponentHelper::getParams('com_proclaim');
        $existing = $params->get('location_group_mapping', '{}');

        if (\is_string($existing)) {
            $existing = json_decode($existing, true) ?: [];
        }

        // Merge without overwriting manually configured entries
        foreach ($mappings as $locationId => $groupIds) {
            $key = (string) $locationId;

            if (!isset($existing[$key])) {
                $existing[$key] = array_values(array_unique(array_map('intval', $groupIds)));
            }
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($existing, JSON_THROW_ON_ERROR)))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_proclaim'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Set location_id on messages based on their current access level.
     * Only updates messages that do not yet have a location assigned.
     *
     * @param   array<int, int>  $locationMap  accessLevelId → locationId
     *
     * @return  int  Number of messages updated.
     *
     * @since   10.1.0
     */
    public static function mapMessagesToLocations(array $locationMap): int
    {
        if (empty($locationMap)) {
            return 0;
        }

        $db      = Factory::getContainer()->get('DatabaseDriver');
        $updated = 0;

        foreach ($locationMap as $accessId => $locationId) {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_studies'))
                ->set($db->quoteName('location_id') . ' = ' . (int) $locationId)
                ->where($db->quoteName('access') . ' = ' . (int) $accessId)
                ->where('(' . $db->quoteName('location_id') . ' = 0 OR ' . $db->quoteName('location_id') . ' IS NULL)');
            $db->setQuery($query);
            $db->execute();
            $updated += $db->getAffectedRows();
        }

        return $updated;
    }

    /**
     * Reset all message access fields to Public (1) after location assignment.
     * Only touches messages that already have a location_id set.
     *
     * @return  int  Number of messages normalised.
     *
     * @since   10.1.0
     */
    public static function normalizeAccessLevelsToPublic(): int
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__bsms_studies'))
            ->set($db->quoteName('access') . ' = 1')
            ->where($db->quoteName('access') . ' != 1')
            ->where($db->quoteName('location_id') . ' > 0');
        $db->setQuery($query);
        $db->execute();

        return $db->getAffectedRows();
    }

    /**
     * Build a locationId → [groupIds] mapping by inspecting Joomla view level rules.
     *
     * The #__viewlevels.rules column stores a JSON array of group IDs that can
     * see that access level.  We invert the map: accessLevel → groups → locationId.
     *
     * @param   array<int, int>  $locationMap  accessLevelId → locationId
     *
     * @return  array<int, int[]>  locationId → [groupId, ...]
     *
     * @since   10.1.0
     */
    public static function buildGroupLocationMappings(array $locationMap): array
    {
        if (empty($locationMap)) {
            return [];
        }

        $db  = Factory::getContainer()->get('DatabaseDriver');
        $ids = array_map('intval', array_keys($locationMap));

        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('rules')])
            ->from($db->quoteName('#__viewlevels'))
            ->whereIn($db->quoteName('id'), $ids);
        $db->setQuery($query);
        $levels = $db->loadObjectList('id') ?: [];

        $result = [];

        foreach ($locationMap as $accessId => $locationId) {
            if (!isset($levels[$accessId])) {
                continue;
            }

            $rules    = json_decode($levels[$accessId]->rules, true);
            $groupIds = \is_array($rules) ? array_values(array_map('intval', $rules)) : [];

            if (!empty($groupIds)) {
                $result[$locationId] = $groupIds;
            }
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // Scenario 2B helpers
    // -------------------------------------------------------------------------

    /**
     * Verify that all messages with location_id set reference a valid, published
     * location record.
     *
     * @return  bool  True if data is consistent; false if orphaned references exist.
     *
     * @since   10.1.0
     */
    public static function validateExistingLocations(): bool
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $orphanQuery = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_studies', 's'))
            ->where($db->quoteName('s.location_id') . ' > 0')
            ->where(
                'NOT EXISTS ('
                . $db->getQuery(true)
                    ->select('1')
                    ->from($db->quoteName('#__bsms_locations', 'l'))
                    ->where($db->quoteName('l.id') . ' = ' . $db->quoteName('s.location_id'))
                    ->where($db->quoteName('l.published') . ' = 1')
                . ')'
            );
        $db->setQuery($orphanQuery);

        return (int) $db->loadResult() === 0;
    }

    // -------------------------------------------------------------------------
    // Scenario 2C helpers
    // -------------------------------------------------------------------------

    /**
     * Create a "Main Campus" default location if none exists yet.
     *
     * @return  int  The location ID (new or existing).
     *
     * @since   10.1.0
     */
    public static function createDefaultLocation(): int
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Reuse existing location if any are present
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_locations'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('id') . ' ASC');
        $db->setQuery($query, 0, 1);
        $existing = (int) $db->loadResult();

        if ($existing > 0) {
            return $existing;
        }

        $row                = new \stdClass();
        $row->location_text = 'Main Campus';
        $row->alias         = 'main-campus';
        $row->published     = 1;
        $row->access        = 1;
        $db->insertObject('#__bsms_locations', $row);

        $newId = (int) $db->insertid();
        Log::add('Created default "Main Campus" location (ID ' . $newId . ')', Log::INFO, 'com_proclaim');

        return $newId;
    }

    /**
     * Assign all messages that have no location to the given location ID.
     *
     * @param   int  $locationId  The location to assign to.
     *
     * @return  int  Number of messages updated.
     *
     * @since   10.1.0
     */
    public static function assignAllMessagesToLocation(int $locationId): int
    {
        if ($locationId <= 0) {
            return 0;
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__bsms_studies'))
            ->set($db->quoteName('location_id') . ' = ' . $locationId)
            ->where('(' . $db->quoteName('location_id') . ' = 0 OR ' . $db->quoteName('location_id') . ' IS NULL)');
        $db->setQuery($query);
        $db->execute();

        return $db->getAffectedRows();
    }

    // -------------------------------------------------------------------------
    // Teacher linkage helpers
    // -------------------------------------------------------------------------

    /**
     * Count teacher records that have a non-zero user_id (linked to a Joomla user).
     *
     * @return  int
     *
     * @since   10.1.0
     */
    public static function countLinkedTeachers(): int
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // The user_id column may not exist yet — handle gracefully
        $columns = $db->getTableColumns('#__bsms_teachers');

        if (!isset($columns['user_id'])) {
            return 0;
        }

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_teachers'))
            ->where($db->quoteName('user_id') . ' > 0');
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Return teacher records that have no associated Joomla user account.
     *
     * @return  \stdClass[]  Teacher rows with ->id and ->name.
     *
     * @since   10.1.0
     */
    public static function getUnlinkedTeachers(): array
    {
        $db      = Factory::getContainer()->get('DatabaseDriver');
        $columns = $db->getTableColumns('#__bsms_teachers');

        if (!isset($columns['user_id'])) {
            // All teachers are "unlinked" if the column doesn't exist yet
            $query = $db->getQuery(true)
                ->select([$db->quoteName('id'), $db->quoteName('name')])
                ->from($db->quoteName('#__bsms_teachers'))
                ->where($db->quoteName('published') . ' = 1');
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        }

        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('name')])
            ->from($db->quoteName('#__bsms_teachers'))
            ->where($db->quoteName('published') . ' = 1')
            ->where('(' . $db->quoteName('user_id') . ' = 0 OR ' . $db->quoteName('user_id') . ' IS NULL)');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
