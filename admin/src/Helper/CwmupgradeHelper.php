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
 * Reports whether the database schema is behind the shipped SQL update files.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class CwmupgradeHelper
{
    /**
     * Check whether the database schema is behind the shipped SQL update files.
     *
     * Compares the version_id recorded in #__schemas against the latest
     * update file in admin/sql/updates/mysql/.  Returns an array with
     * the current and expected versions, or null when everything is current.
     *
     * @return  array{current: string, expected: string}|null  Null if up-to-date
     *
     * @since   10.1.0
     */
    public static function isSchemaOutOfDate(): ?array
    {
        try {
            $db  = Factory::getContainer()->get(DatabaseInterface::class);
            $cid = self::getExtensionId();

            if (!$cid) {
                return null;
            }

            // Current schema version from Joomla's tracking table
            $query = $db->createQuery()
                ->select($db->quoteName('version_id'))
                ->from($db->quoteName('#__schemas'))
                ->where($db->quoteName('extension_id') . ' = ' . $cid);
            $db->setQuery($query);
            $currentVersion = (string) $db->loadResult();

            if (!$currentVersion) {
                return null;
            }

            // Find the latest shipped update file
            $updateDir = JPATH_ADMINISTRATOR . '/components/com_proclaim/sql/updates/mysql';

            if (!is_dir($updateDir)) {
                return null;
            }

            $files = glob($updateDir . '/*.sql');

            if (empty($files)) {
                return null;
            }

            // Extract version strings (e.g. "10.1.0-20260220" from filename)
            $versions = [];

            foreach ($files as $file) {
                $versions[] = basename($file, '.sql');
            }

            usort($versions, 'version_compare');
            $latestVersion = end($versions);

            // If the recorded schema is older than the newest file, it's out of date
            if (version_compare($currentVersion, $latestVersion, '<')) {
                return [
                    'current'  => $currentVersion,
                    'expected' => $latestVersion,
                ];
            }
        } catch (\Exception) {
            // Fail silently — this is a non-critical informational check
        }

        return null;
    }

    /**
     * Get the Proclaim extension ID from #__extensions.
     *
     * @return  int  Extension ID, or 0 if not found
     *
     * @since   10.1.0
     */
    private static function getExtensionId(): int
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_proclaim'));
        $db->setQuery($query);

        return (int) $db->loadResult();
    }
}
