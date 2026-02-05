<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;
use CWM\Component\Proclaim\Administrator\Lib\Cwmrestore;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Installer\Administrator\Model\DatabaseModel;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

/**
 * Controller for Backup/Restore operations
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmbackupController extends FormController
{
    // =========================================================================
    // AJAX Export Methods
    // =========================================================================

    /**
     * Get list of tables for AJAX export
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function getExportTablesXHR(): void
    {
        // Check for request forgeries
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));

            return;
        }

        $tables = CwmdbHelper::getObjects();
        $tableNames = array_column($tables, 'name');

        $this->sendJsonResponse(true, '', ['tables' => $tableNames]);
    }

    /**
     * Export a single table via AJAX
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function exportTableXHR(): void
    {
        // Check for request forgeries
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));

            return;
        }

        $app   = Factory::getApplication();
        $input = $app->getInput();
        $table = $input->get('table', '', 'string');

        if (empty($table)) {
            $this->sendJsonResponse(false, 'No table specified');

            return;
        }

        $session = $app->getSession();

        // Get or create export data cache
        $exportData = $session->get('proclaim_export_data', '', 'CWM');

        // Export the table
        $backup = new Cwmbackup();
        $tableData = $backup->getExportTableData($table);

        // Append to session cache
        $exportData .= $tableData;
        $session->set('proclaim_export_data', $exportData, 'CWM');

        $this->sendJsonResponse(true, '', ['table' => $table, 'size' => strlen($tableData)]);
    }

    /**
     * Finalize export and create download file
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function finalizeExportXHR(): void
    {
        // Check for request forgeries
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));

            return;
        }

        $app     = Factory::getApplication();
        $input   = $app->getInput();
        $mode    = $input->get('mode', 'download', 'string');
        $session = $app->getSession();

        // Get export data from session
        $exportData = $session->get('proclaim_export_data', '', 'CWM');

        if (empty($exportData)) {
            $this->sendJsonResponse(false, 'No export data found');

            return;
        }

        // Generate filename
        $date     = date('Y_F_j');
        $site     = Uri::root();
        $filename = strtolower(trim(preg_replace('#\W+#', '_', $site), '_')) . '_jbs-db-backup_' . $date . '_' . time() . '.sql';

        $config = $app->getConfig();

        if ($mode === 'save') {
            // Save to backup folder
            $backupDir = JPATH_SITE . '/media/com_proclaim/backup/';

            if (!is_dir($backupDir)) {
                Folder::create($backupDir);
            }

            $path = $backupDir . $filename;

            if (!file_put_contents($path, $exportData)) {
                $this->sendJsonResponse(false, 'Failed to write backup file');

                return;
            }

            // Clean up old backups
            $backup = new Cwmbackup();
            $backup->updatefiles(Cwmparams::getAdmin()->params);

            // Clear session data
            $session->set('proclaim_export_data', '', 'CWM');

            $this->sendJsonResponse(true, '', ['filename' => $filename, 'path' => $path]);
        } else {
            // Save to tmp for download
            $tmpPath = $config->get('tmp_path') . '/' . $filename;

            if (!file_put_contents($tmpPath, $exportData)) {
                $this->sendJsonResponse(false, 'Failed to write temp file');

                return;
            }

            // Store path in session for download
            $session->set('proclaim_download_file', $tmpPath, 'CWM');
            $session->set('proclaim_export_data', '', 'CWM');

            // Return URL for download
            $downloadUrl = Route::_('index.php?option=com_proclaim&task=cwmbackup.downloadExportXHR&' . Session::getFormToken() . '=1', false);

            $this->sendJsonResponse(true, '', ['filename' => $filename, 'downloadUrl' => $downloadUrl]);
        }
    }

    /**
     * Download the exported file
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function downloadExportXHR(): void
    {
        // Check for request forgeries
        if (!Session::checkToken('get') && !Session::checkToken()) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'));
        }

        $app     = Factory::getApplication();
        $session = $app->getSession();

        $filePath = $session->get('proclaim_download_file', '', 'CWM');

        if (empty($filePath) || !is_file($filePath)) {
            throw new \RuntimeException('Download file not found');
        }

        // Clear session
        $session->set('proclaim_download_file', '', 'CWM');

        // Output file
        $backup = new Cwmbackup();
        $backup->outputFile($filePath, basename($filePath), 'text/x-sql');

        // Clean up
        File::delete($filePath);

        $app->close();
    }

    // =========================================================================
    // AJAX Import Methods
    // =========================================================================

    /**
     * Upload import file via AJAX
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function uploadImportFileXHR(): void
    {
        // Check for request forgeries
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));

            return;
        }

        $app      = Factory::getApplication();
        $input    = $app->getInput();
        $userfile = $input->files->get('importdb', null, 'raw');

        if (!\is_array($userfile) || $userfile['error'] || $userfile['size'] < 1) {
            $this->sendJsonResponse(false, Text::_('JBS_IBM_ERROR_UPLOAD_FAILED'));

            return;
        }

        // Move uploaded file to tmp
        $config   = $app->getConfig();
        $tmpPath  = $config->get('tmp_path') . '/' . $userfile['name'];

        if (!File::upload($userfile['tmp_name'], $tmpPath, false, true)) {
            $this->sendJsonResponse(false, 'Failed to move uploaded file');

            return;
        }

        // Handle ZIP files
        if (str_ends_with(strtolower($tmpPath), '.zip')) {
            $package = InstallerHelper::unpack($tmpPath, true);

            if (!isset($package['dir'])) {
                $this->sendJsonResponse(false, 'Failed to extract ZIP file');

                return;
            }

            // Find SQL file in extracted directory
            $files = Folder::files($package['dir'], '\.sql$', true, true);

            if (empty($files)) {
                $this->sendJsonResponse(false, 'No SQL file found in archive');

                return;
            }

            $tmpPath = $files[0];
        }

        // Generate session ID and store file path
        $sessionId = md5(uniqid((string) mt_rand(), true));
        $session   = $app->getSession();
        $session->set('proclaim_import_' . $sessionId, $tmpPath, 'CWM');

        $this->sendJsonResponse(true, '', ['sessionId' => $sessionId]);
    }

    /**
     * Prepare import from backup folder or tmp folder
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function prepareImportXHR(): void
    {
        // Check for request forgeries
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));

            return;
        }

        $app    = Factory::getApplication();
        $input  = $app->getInput();
        $type   = $input->get('type', '', 'string');
        $source = $input->get('source', '', 'string');

        if (empty($type) || empty($source)) {
            $this->sendJsonResponse(false, 'Missing type or source parameter');

            return;
        }

        $filePath = '';

        if ($type === 'backup') {
            // From backup folder
            $filePath = JPATH_SITE . '/media/com_proclaim/backup/' . basename($source);
        } elseif ($type === 'folder') {
            // From tmp folder
            $filePath = Path::clean($source);
        }

        if (!is_file($filePath)) {
            $this->sendJsonResponse(false, Text::_('JBS_IBM_NOT_DB'));

            return;
        }

        // Generate session ID and store file path
        $sessionId = md5(uniqid((string) mt_rand(), true));
        $session   = $app->getSession();
        $session->set('proclaim_import_' . $sessionId, $filePath, 'CWM');

        $this->sendJsonResponse(true, '', ['sessionId' => $sessionId]);
    }

    /**
     * Get import file info (for progress tracking)
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function getImportInfoXHR(): void
    {
        // Check for request forgeries
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));

            return;
        }

        $app       = Factory::getApplication();
        $input     = $app->getInput();
        $sessionId = $input->get('sessionId', '', 'string');
        $session   = $app->getSession();

        $filePath = $session->get('proclaim_import_' . $sessionId, '', 'CWM');

        if (empty($filePath) || !is_file($filePath)) {
            $this->sendJsonResponse(false, 'Import file not found');

            return;
        }

        // Read and validate file
        $content = file_get_contents($filePath);

        if ($content === false) {
            $this->sendJsonResponse(false, Text::_('JBS_INS_ERROR_SQL_READBUFFER'));

            return;
        }

        // Validate it's a Proclaim backup
        $isnot = substr_count($content, '#__bsms_studies');

        if ($isnot === 0) {
            $this->sendJsonResponse(false, Text::_('JBS_IBM_NOT_DB'));

            return;
        }

        // Split into queries
        $db      = Factory::getContainer()->get('DatabaseDriver');
        $queries = $db->splitSql($content);

        // Store queries in session for batch processing
        $session->set('proclaim_import_queries_' . $sessionId, $queries, 'CWM');

        // Calculate batches (50 queries per batch)
        $batchSize    = 50;
        $totalBatches = (int) ceil(count($queries) / $batchSize);

        $this->sendJsonResponse(true, '', [
            'totalQueries' => count($queries),
            'totalBatches' => $totalBatches,
            'batchSize'    => $batchSize,
        ]);
    }

    /**
     * Import a batch of SQL queries
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function importBatchXHR(): void
    {
        // Check for request forgeries
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));

            return;
        }

        $app       = Factory::getApplication();
        $input     = $app->getInput();
        $sessionId = $input->get('sessionId', '', 'string');
        $batch     = $input->get('batch', 0, 'int');
        $session   = $app->getSession();

        $queries = $session->get('proclaim_import_queries_' . $sessionId, [], 'CWM');

        if (empty($queries)) {
            $this->sendJsonResponse(false, 'No queries found in session');

            return;
        }

        $batchSize = 50;
        $start     = $batch * $batchSize;
        $batchQueries = array_slice($queries, $start, $batchSize);

        $db = Factory::getContainer()->get('DatabaseDriver');

        // For first batch, drop existing tables
        if ($batch === 0) {
            $objects = CwmdbHelper::getObjects();

            foreach ($objects as $object) {
                $dropper = 'DROP TABLE IF EXISTS ' . $object['name'] . ';';
                $db->setQuery($dropper);
                $db->execute();
            }
        }

        // Execute batch queries
        foreach ($batchQueries as $query) {
            $query = trim($query);

            if ($query !== '' && $query[0] !== '#') {
                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (\Exception $e) {
                    // Log error but continue
                    Log::add('Import query error: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
                }
            }
        }

        $this->sendJsonResponse(true, '', ['batch' => $batch, 'processed' => count($batchQueries)]);
    }

    /**
     * Finalize import - fix assets and ownership
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function finalizeImportXHR(): void
    {
        // Check for request forgeries
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));

            return;
        }

        $app       = Factory::getApplication();
        $input     = $app->getInput();
        $sessionId = $input->get('sessionId', '', 'string');
        $session   = $app->getSession();

        // Clean up session data
        $session->set('proclaim_import_' . $sessionId, '', 'CWM');
        $session->set('proclaim_import_queries_' . $sessionId, '', 'CWM');

        try {
            // Get Proclaim extension ID and fix database
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->select('extension_id')
                ->from('#__extensions')
                ->where($db->quoteName('element') . ' = ' . $db->q('com_proclaim'));
            $db->setQuery($query);
            $cid = (int) $db->loadResult();

            $DatabaseModel = new DatabaseModel();
            $DatabaseModel->fix([$cid]);

            // Fix assets
            $fix     = new Cwmassets();
            $results = $fix->build();

            foreach ($results->query as $key => $items) {
                foreach ($items as $item) {
                    Cwmassets::fixAssets($key, $item);
                }
            }

            // Fix ownership
            Cwmrestore::fixOwnershipPublic();

            // Purge update cache
            $updateModel = $app->bootComponent('com_joomlaupdate')
                ->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);
            $updateModel->purge();

            // Flush assets
            $app->flushAssets();

            $this->sendJsonResponse(true, Text::_('JBS_CMN_OPERATION_SUCCESSFUL'));
        } catch (\Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Send JSON response helper
     *
     * @param   bool    $success  Success status
     * @param   string  $message  Message
     * @param   array   $data     Additional data
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    private function sendJsonResponse(bool $success, string $message = '', array $data = []): void
    {
        $app      = Factory::getApplication();
        $document = $app->getDocument();

        $document->setMimeEncoding('application/json');

        $response = [
            'success' => $success,
            'message' => $message,
            'data'    => $data,
        ];

        echo json_encode($response, JSON_THROW_ON_ERROR);

        $app->close();
    }
}
