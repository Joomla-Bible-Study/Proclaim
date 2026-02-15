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

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Installer\Administrator\Model\DatabaseModel;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

/**
 * Restore class
 *
 * @package  Proclaim.Admin
 * @since    7.0.4
 */
class Cwmrestore
{
    /**
     * Alter tables for Blob
     *
     * @return bool
     *
     * @since 7.0.0
     */
    protected static function tablesToBlob(): bool
    {
        $backuptables = self::getObjects();

        $db = Factory::getContainer()->get('DatabaseDriver');

        foreach ($backuptables as $backuptable) {
            if (substr_count($backuptable['name'], 'studies')) {
                $query = 'ALTER TABLE ' . $db->qn($backuptable['name']) . ' MODIFY ' . $db->qn('studytext') . ' BLOB';
                $db->setQuery($query);
                $db->execute();

                $query = 'ALTER TABLE ' . $db->qn($backuptable['name']) . ' MODIFY ' . $db->qn('studytext2') . ' BLOB';
                $db->setQuery($query);
                $db->execute();
            }

            if (substr_count($backuptable['name'], 'podcast')) {
                $query = 'ALTER TABLE ' . $db->qn($backuptable['name']) . ' MODIFY ' . $db->qn('description') . ' BLOB';
                $db->setQuery($query);
                $db->execute();
            }

            if (substr_count($backuptable['name'], 'series')) {
                $query = 'ALTER TABLE ' . $db->qn($backuptable['name']) . ' MODIFY ' . $db->qn('description') . ' BLOB';
                $db->setQuery($query);
                $db->execute();
            }

            if (substr_count($backuptable['name'], 'teachers')) {
                $query = 'ALTER TABLE ' . $db->qn($backuptable['name']) . ' MODIFY ' . $db->qn('information') . ' BLOB';
                $db->setQuery($query);
                $db->execute();
            }
        }

        return true;
    }

    /**
     * Get Objects for tables
     *
     * @return array
     *
     * @since 7.0.0
     */
    protected static function getObjects(): array
    {
        $db        = Factory::getContainer()->get('DatabaseDriver');
        $tables    = $db->getTableList();
        $prefix    = $db->getPrefix();
        $prelength = \strlen($prefix);
        $bsms      = 'bsms_';
        $objects   = [];

        foreach ($tables as $table) {
            if (substr_count($table, $bsms)) {
                $table     = substr_replace($table, '#__', 0, $prelength);
                $objects[] = ['name' => $table];
            }
        }

        return $objects;
    }

    /**
     * Modify tables to Text
     *
     * @return bool
     *
     * @since 9.0.0
     */
    protected static function tablesToText(): bool
    {
        $backuptables = self::getObjects();

        $db = Factory::getContainer()->get('DatabaseDriver');

        foreach ($backuptables as $backuptable) {
            if (substr_count($backuptable['name'], 'studies')) {
                $query = 'ALTER TABLE ' . $db->qn($backuptable['name']) . ' MODIFY ' . $db->qn('studytext') . ' TEXT';
                $db->setQuery($query);
                $db->execute();

                $query = 'ALTER TABLE ' . $db->qn($backuptable['name']) . ' MODIFY ' . $db->qn('studytext2') . ' TEXT';
                $db->setQuery($query);
                $db->execute();
            }

            if (substr_count($backuptable['name'], 'podcast')) {
                $query = 'ALTER TABLE ' . $db->qn($backuptable['name']) . ' MODIFY ' . $db->qn('description') . ' TEXT';
                $db->setQuery($query);
                $db->execute();
            }

            if (substr_count($backuptable['name'], 'series')) {
                $query = 'ALTER TABLE ' . $db->qn($backuptable['name']) . ' MODIFY ' . $db->qn('description') . ' TEXT';
                $db->setQuery($query);
                $db->execute();
            }

            if (substr_count($backuptable['name'], 'teachers')) {
                $query = 'ALTER TABLE ' . $db->qn($backuptable['name']) . ' MODIFY ' . $db->qn('information') . ' TEXT';
                $db->setQuery($query);
                $db->execute();
            }
        }

        return true;
    }

    /**
     * Import DB
     *
     * @param bool $parent Switch to see if it is coming from migration or restore.
     *
     * @return bool|array
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function importdb($parent): bool|array
    {
        $input         = Factory::getApplication()->getInput();
        $installtype   = $input->getPath('install_directory');
        $backuprestore = $input->getWord('backuprestore', '');
        $this->dbo     = Factory::getContainer()->get('DatabaseDriver');

        // Restore form prior backup files located on the server.
        if (substr_count($backuprestore, '.sql')) {
            $restored      = self::restoreDB($backuprestore);

            if ($restored) {
                return true;
            }
            return false;
        }

        // Start finding how to restore files.
        if (
            !empty($installtype) && $installtype !== '/' && $installtype !== Factory::getApplication()->getConfig()->get(
                'tmp_path'
            ) . '/'
        ) {
            $uploadresults = self::getPackageFromFolder();
        } else {
            $uploadresults = $this->getPackageFromUpload();
        }
        $result = $uploadresults;

        if ($result) {
            switch ($result['type']) {
                case 'dir':
                    $src     = Folder::files($result['dir'], '.', true, true);
                    $tmp_src = $src[0];
                    break;
                case 'file':
                    $tmp_src = $result['dir'];
                    break;
                default:
                    throw new \InvalidArgumentException('Unknown Archive Type');
            }

            $result = self::installdb($tmp_src, $parent);

            if ($result) {
                // Get Proclaim extension ID
                $query = $this->dbo->getQuery(true);
                $query->select($this->dbo->qn('extension_id'));
                $query->from($this->dbo->qn('#__extensions'));
                $query->where($this->dbo->qn('element') . ' = ' . $this->dbo->q('com_proclaim'));
                $this->dbo->setQuery($query);
                $cid = (int) $this->dbo->loadResult();

                // Reset #__schemas so DatabaseModel::fix() re-runs all migrations.
                // The restore replaced all bsms_* tables with backup data, but
                // #__schemas (a Joomla core table) still claims the latest version.
                // Without this reset, fix() thinks everything is up-to-date and
                // skips creating tables that were added after the backup was made.
                if ($cid) {
                    self::resetSchemaVersion($cid);
                }

                // Fix the Proclaim Database schema after restore
                $DatabaseModel = new DatabaseModel();
                $DatabaseModel->fix([$cid]);

                // Fix Proclaim assets (ACL permissions)
                self::fixAssetsAfterRestore();

                // Fix object ownership for migrated data
                self::fixOwnershipAfterRestore();

                /** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $updateModel */
                $updateModel = Factory::getApplication()->bootComponent('com_joomlaupdate')
                    ->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);
                $updateModel->purge();

                // Refresh versionable assets cache
                Factory::getApplication()->flushAssets();
            }

            // Clean up the installation files.
            if (!is_file($uploadresults['packagefile'])) {
                $config                       = Factory::getApplication()->getConfig();
                $uploadresults['packagefile'] = $config->get('tmp_path') . '/' . $uploadresults['packagefile'];
            }

            InstallerHelper::cleanupInstall($uploadresults['packagefile'], $uploadresults['extractdir']);
        }

        return $result;
    }

    /**
     * Restore DB for Proclaim
     *
     * @param   string  $backuprestore  file name to restore
     *
     * @return bool See if the restore worked.
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public static function restoreDB($backuprestore): bool
    {
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get('DatabaseDriver');
        /**
         * Attempt to increase the maximum execution time for PHP scripts with a check for safe_mode.
         */
        set_time_limit(3000);

        $query = file_get_contents(JPATH_SITE . '/media/com_proclaim/backup/' . $backuprestore);

        // Check to see if this is a backup from an old DB and not a migration
        $isold   = substr_count($query, '#__bsms_admin_genesis');
        $isnot   = substr_count($query, '#__bsms_studies');
        $iscernt = substr_count($query, BIBLESTUDY_VERSION_UPDATEFILE);

        if ($isold !== 0 && $isnot === 0) {
            $app->enqueueMessage(Text::_('JBS_IBM_OLD_DB'), 'warning');

            return false;
        }

        if ($isnot === 0) {
            $app->enqueueMessage(Text::_('JBS_IBM_NOT_DB'), 'warning');

            return false;
        }

        if (!$iscernt) {
            $app->enqueueMessage(basename($backuprestore), 'warning');
            $app->enqueueMessage(Text::_('JBS_IBM_NOT_CURENT_DB'), 'warning');

            return false;
        }

        $queries = $db->splitSql($query);

        foreach ($queries as $query) {
            $query = trim($query);

            if ($query !== '' && $query[0] !== '#') {
                $db->setQuery($query);
                $db->execute();
            }
        }

        // After restoring, reset the schema version and run DatabaseModel::fix()
        // so that any tables/columns added after the backup was created get applied.
        try {
            $query = $db->getQuery(true);
            $query->select($db->quoteName('extension_id'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_proclaim'));
            $db->setQuery($query);
            $cid = (int) $db->loadResult();

            if ($cid) {
                self::resetSchemaVersion($cid);

                $databaseModel = new DatabaseModel();
                $databaseModel->fix([$cid]);
            }
        } catch (\Exception $e) {
            $app->enqueueMessage('Schema repair notice: ' . $e->getMessage(), 'warning');
        }

        return true;
    }

    /**
     * Get Package from Folder
     *
     * @return array|bool
     *
     * @throws \Exception
     * @since 9.0.0
     */
    private static function getPackageFromFolder(): bool|array
    {
        $input = Factory::getApplication()->getInput();

        // Get the path to the package to install.
        $p_dir = $input->getString('install_directory');
        $p_dir = Path::clean($p_dir);

        // Did you give us a valid directory?
        if (!is_dir($p_dir)) {
            throw new \RuntimeException(Text::_('COM_INSTALLER_MSG_INSTALL_PLEASE_ENTER_A_PACKAGE_DIRECTORY'), 502);
        }

        $package['packagefile'] = null;
        $package['extractdir']  = null;
        $package['dir']         = $p_dir;
        $package['type']        = 'dir';

        return $package;
    }

    /**
     * Get Package form Upload
     *
     * @return bool|array
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function getPackageFromUpload(): bool|array
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        // Get the uploaded file information
        $userfile = $input->files->get('importdb', null, 'raw');

        // Make sure that file uploads are enabled in PHP
        if (!(bool)\ini_get('file_uploads')) {
            $app->enqueueMessage(Text::_('JBS_IBM_ERROR_PHP_UPLOAD_NOT_ENABLED'), 'warning');

            return false;
        }

        // Ensure that zlib is loaded so that the package can be unpacked.
        if (!\extension_loaded('zlib')) {
            $app->enqueueMessage(Text::_('JBS_IBM_ERROR_UPLOAD_FAILED_ZLIB'), 'error');

            return false;
        }

        // If there is no uploaded file, we have a problem...
        if (!\is_array($userfile)) {
            $app->enqueueMessage(Text::_('JBS_CMN_NO_FILE_SELECTED'), 'warning');

            return false;
        }

        // Is the PHP tmp directory missing?
        if ($userfile['error'] && ($userfile['error'] == UPLOAD_ERR_NO_TMP_DIR)) {
            $app->enqueueMessage(
                Text::_('JBS_IBM_ERROR_UPLOAD_FAILED') . '<br />' . Text::_(
                    'JBS_IBM_ERROR_UPLOAD_FAILED_PHPUPLOADNOTSET',
                    'error'
                )
            );

            return false;
        }

        // Is the max upload size too small in php.ini?
        if ($userfile['error'] && ($userfile['error'] == UPLOAD_ERR_INI_SIZE)) {
            $app->enqueueMessage(
                Text::_('JBS_IBM_ERROR_UPLOAD_FAILED') . '<br />' . Text::_(
                    'JBS_IBM_ERROR_UPLOAD_FAILED_SMALLUPLOADSIZE',
                    'error'
                )
            );

            return false;
        }

        // Check if there was a problem uploading the file.
        if ($userfile['error'] || $userfile['size'] < 1) {
            $app->enqueueMessage(Text::_('JBS_IBM_ERROR_UPLOAD_FAILED'), 'warning');

            return false;
        }

        // Build the appropriate paths
        $config   = Factory::getApplication()->getConfig();
        $tmp_dest = $config->get('tmp_path') . '/' . $userfile['name'];
        $tmp_src  = $userfile['tmp_name'];

        // Move an uploaded file.
        File::upload($tmp_src, $tmp_dest, false, true);

        if (!str_ends_with($tmp_dest, 'sql') && str_ends_with($tmp_dest, 'sql.zip')) {
            // Unpack the downloaded package file.
            $package         = InstallerHelper::unpack($tmp_dest, true);
            if (!isset($package['dir'])) {
                throw new \RuntimeException('Compressed file did not extract.', 500);
            }
            $package['type'] = 'dir';
        } else {
            $package['packagefile'] = null;
            $package['extractdir']  = null;
            $package['dir']         = $tmp_dest;
            $package['type']        = 'file';
        }

        return $package;
    }

    /**
     * Install DB
     *
     * @param   string  $tmp_src  Temp info
     * @param   bool    $parent   To tell if coming from migration
     *
     * @return bool if db installed correctly.
     *
     * @throws \Exception
     * @since 9.0.0
     */
    protected static function installdb(string $tmp_src, bool $parent = true): bool
    {
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get('DatabaseDriver');

        $query = file_get_contents($tmp_src);

        // Graceful exit and rollback if read is not successful
        if ($query === false) {
            $app->enqueueMessage(Text::_('JBS_INS_ERROR_SQL_READBUFFER'), 'error');

            return false;
        }
        // Check if sql file is for Joomla! Bible Studies
        $isold   = substr_count($query, '#__bsms_admin_genesis');
        $isnot   = substr_count($query, '#__bsms_studies');
        $iscernt = substr_count($query, BIBLESTUDY_VERSION_UPDATEFILE);

        if ($isold !== 0 && $isnot === 0) {
            $app->enqueueMessage(Text::_('JBS_IBM_OLD_DB'), 'warning');

            return false;
        }

        if ($isnot === 0) {
            $app->enqueueMessage('Extracted file: ' . basename($tmp_src), 'warning');
            $app->enqueueMessage(Text::_('JBS_IBM_NOT_DB'), 'warning');

            return false;
        }

        if (($iscernt === 0) && ($parent !== true)) {
            // Way to check if a file came from a restore and is current.
            $app->enqueueMessage(Text::_('JBS_IBM_NOT_CURENT_DB'), 'warning');

            return false;
        }

        // First, we need to drop the existing JBS tables
        $objects = self::getObjects();

        foreach ($objects as $object) {
            $dropper = 'DROP TABLE IF EXISTS ' . $db->qn($object['name']);
            $db->setQuery($dropper);
            $db->execute();
        }

        // Create an array of queries from the SQL file
        $queries = $db->splitSql($query);

        if (\count($queries) === 0) {
            // No queries to process
            return false;
        }

        // Process each query in the $queries array (split out of the SQL file).
        foreach ($queries as $query) {
            $query = trim($query);

            if ($query !== '' && $query[0] !== '#') {
                $db->setQuery($query);

                if (!$db->execute()) {
                    $app->enqueueMessage(Text::sprintf('JBS_IBM_INSTALLDB_ERRORS', $db->stderr(true)), 'error');

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Reset the #__schemas version for Proclaim so that DatabaseModel::fix()
     * re-runs all SQL update files.
     *
     * After a restore, the bsms_* tables come from the backup but #__schemas
     * (a Joomla core table) still holds the version from before the restore.
     * This mismatch causes DatabaseModel::fix() to skip migrations, leaving
     * tables that were added after the backup was created missing entirely.
     *
     * @param   int  $extensionId  The Proclaim extension ID
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected static function resetSchemaVersion(int $extensionId): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Delete the current schema entry
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__schemas'))
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId);
        $db->setQuery($query);
        $db->execute();

        // Insert a baseline version before all update files so fix() runs everything
        $query = $db->getQuery(true);
        $query->insert($db->quoteName('#__schemas'))
            ->columns([$db->quoteName('extension_id'), $db->quoteName('version_id')])
            ->values($extensionId . ', ' . $db->quote('0.0.0'));
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Fix Proclaim assets after database restore
     *
     * This method rebuilds the asset relationships for all Proclaim tables
     * to ensure ACL permissions work correctly after a restore.
     *
     * @return void
     *
     * @since 10.1.0
     */
    protected static function fixAssetsAfterRestore(): void
    {
        $app = Factory::getApplication();

        try {
            // Build the asset fix queue
            $results = Cwmassets::build();

            // Process all assets synchronously
            foreach ($results->query as $key => $items) {
                foreach ($items as $item) {
                    Cwmassets::fixAssets($key, $item);
                }
            }

            $app->enqueueMessage(Text::_('JBS_IBM_ASSETS_FIXED'), 'info');
        } catch (\Exception $e) {
            // Asset fix failed - user can run it manually from control panel
            $app->enqueueMessage(
                Text::_('JBS_IBM_ASSETS_FIX_MANUAL') . ' ' . $e->getMessage(),
                'warning'
            );
        }
    }

    /**
     * Public wrapper for ownership fix (used by AJAX controller)
     *
     * @return void
     *
     * @since 10.1.0
     */
    public static function fixOwnershipPublic(): void
    {
        self::fixOwnershipAfterRestore();
    }

    /**
     * Fix object ownership after database restore
     *
     * When restoring a database from another site, created_by and modified_by
     * fields may reference user IDs that don't exist. This method updates
     * those fields to use the current user's ID.
     *
     * @return void
     *
     * @since 10.1.0
     */
    protected static function fixOwnershipAfterRestore(): void
    {
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get('DatabaseDriver');

        // Get the current user ID (the person doing the restore)
        $currentUserId = $app->getIdentity()->id;

        if (!$currentUserId) {
            return;
        }

        // Tables with ownership fields
        $tablesWithOwnership = [
            '#__bsms_studies',
            '#__bsms_teachers',
            '#__bsms_series',
            '#__bsms_mediafiles',
            '#__bsms_servers',
            '#__bsms_podcast',
        ];

        $totalFixed = 0;

        foreach ($tablesWithOwnership as $table) {
            try {
                // Fix created_by where user doesn't exist
                $query = $db->getQuery(true);
                $query->update($db->quoteName($table))
                    ->set($db->quoteName('created_by') . ' = ' . (int) $currentUserId)
                    ->where($db->quoteName('created_by') . ' NOT IN (SELECT ' . $db->qn('id') . ' FROM ' . $db->qn('#__users') . ')')
                    ->where($db->quoteName('created_by') . ' != 0');
                $db->setQuery($query);
                $db->execute();
                $totalFixed += $db->getAffectedRows();

                // Fix modified_by where user doesn't exist
                $query = $db->getQuery(true);
                $query->update($db->quoteName($table))
                    ->set($db->quoteName('modified_by') . ' = ' . (int) $currentUserId)
                    ->where($db->quoteName('modified_by') . ' NOT IN (SELECT ' . $db->qn('id') . ' FROM ' . $db->qn('#__users') . ')')
                    ->where($db->quoteName('modified_by') . ' != 0');
                $db->setQuery($query);
                $db->execute();
                $totalFixed += $db->getAffectedRows();
            } catch (\Exception $e) {
                // Table might not have the column, skip it
                continue;
            }
        }

        if ($totalFixed > 0) {
            $app->enqueueMessage(Text::sprintf('JBS_IBM_OWNERSHIP_FIXED', $totalFixed), 'info');
        }
    }
}
