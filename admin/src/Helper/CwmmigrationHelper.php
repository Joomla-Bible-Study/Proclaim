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
        $db = Factory::getContainer()->get('DatabaseDriver');
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
        $db = Factory::getContainer()->get('DatabaseDriver');
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
        $biblestudyEid         = $db->loadResult();
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
        $db = Factory::getContainer()->get('DatabaseDriver');
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
}
