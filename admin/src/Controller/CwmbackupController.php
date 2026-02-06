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

        $app    = Factory::getApplication();
        $config = $app->getConfig();

        // Create a unique export ID for this session
        $exportId = md5(uniqid((string) mt_rand(), true));
        $tmpPath  = $config->get('tmp_path') . '/proclaim_export_' . $exportId . '.sql';

        // Initialize empty file
        file_put_contents($tmpPath, '');

        $tables     = CwmdbHelper::getObjects();
        $tableNames = array_column($tables, 'name');

        // Return the export ID so it can be passed to subsequent calls
        $this->sendJsonResponse(true, '', ['tables' => $tableNames, 'exportId' => $exportId]);
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

        $app      = Factory::getApplication();
        $input    = $app->getInput();
        $config   = $app->getConfig();
        $table    = $input->get('table', '', 'string');
        $exportId = $input->get('exportId', '', 'string');

        if (empty($table)) {
            $this->sendJsonResponse(false, 'No table specified');

            return;
        }

        if (empty($exportId)) {
            $this->sendJsonResponse(false, 'No export ID specified');

            return;
        }

        // Build the temp file path from the export ID
        $tmpPath = $config->get('tmp_path') . '/proclaim_export_' . $exportId . '.sql';

        if (!is_file($tmpPath)) {
            $this->sendJsonResponse(false, 'Export file not found. Please start again.');

            return;
        }

        // Export the table and append directly to file
        $backup    = new Cwmbackup();
        $tableData = $backup->getExportTableData($table);

        // Append to temp file
        if (file_put_contents($tmpPath, $tableData, FILE_APPEND) === false) {
            $this->sendJsonResponse(false, 'Failed to write export data');

            return;
        }

        $this->sendJsonResponse(true, '', ['table' => $table, 'size' => \strlen($tableData)]);
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
        // Register shutdown handler to catch fatal errors
        register_shutdown_function(function () {
            $error = error_get_last();

            if ($error !== null && \in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'PHP Fatal: ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line'],
                    'data'    => [],
                ]);
            }
        });

        try {
            // Check for request forgeries
            if (!Session::checkToken('get') && !Session::checkToken()) {
                $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));

                return;
            }

            $app      = Factory::getApplication();
            $input    = $app->getInput();
            $mode     = $input->get('mode', 'download', 'string');
            $exportId = $input->get('exportId', '', 'string');
            $session  = $app->getSession();
            $config   = $app->getConfig();

            if (empty($exportId)) {
                $this->sendJsonResponse(false, 'No export ID specified');

                return;
            }

            // Build the temp file path from the export ID
            $tmpExportPath = $config->get('tmp_path') . '/proclaim_export_' . $exportId . '.sql';

            if (!is_file($tmpExportPath)) {
                $this->sendJsonResponse(false, 'Export file not found. Please start again.');

                return;
            }

            // Generate filename using standardized method
            $filename = Cwmbackup::generateBackupFilename();

            if ($mode === 'save') {
                // Save to backup folder
                $backupDir = JPATH_SITE . '/media/com_proclaim/backup/';

                if (!is_dir($backupDir)) {
                    Folder::create($backupDir);
                }

                $path = $backupDir . $filename;

                // Move the temp file to backup location
                if (!rename($tmpExportPath, $path)) {
                    // Fallback to copy + delete
                    if (!copy($tmpExportPath, $path)) {
                        $this->sendJsonResponse(false, 'Failed to write backup file');

                        return;
                    }
                    File::delete($tmpExportPath);
                }

                // Clean up old backups
                $backup = new Cwmbackup();
                $backup->updatefiles(Cwmparams::getAdmin()->params);

                $this->sendJsonResponse(true, '', ['filename' => $filename, 'path' => $path]);
            } else {
                // Rename temp file for download
                $downloadPath = $config->get('tmp_path') . '/' . $filename;

                if (!rename($tmpExportPath, $downloadPath)) {
                    // Fallback to copy + delete
                    if (!copy($tmpExportPath, $downloadPath)) {
                        $this->sendJsonResponse(false, 'Failed to prepare download file');

                        return;
                    }
                    File::delete($tmpExportPath);
                }

                // Store path in session for download (needed for the download redirect)
                $session->set('proclaim_download_file', $downloadPath, 'CWM');

                // Return URL for download
                $downloadUrl = Route::_('index.php?option=com_proclaim&task=cwmbackup.downloadExportXHR&' . Session::getFormToken() . '=1', false);

                $this->sendJsonResponse(true, '', ['filename' => $filename, 'downloadUrl' => $downloadUrl]);
            }
        } catch (\Exception $e) {
            $this->sendJsonResponse(false, 'Export error: ' . $e->getMessage());
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
        // Suppress error display and capture any output
        $previousErrorReporting = error_reporting(E_ALL);
        $previousDisplayErrors = ini_get('display_errors');
        ini_set('display_errors', '0');
        ob_start();

        // Register shutdown handler to catch fatal errors
        register_shutdown_function(function () {
            $error = error_get_last();

            if ($error !== null && \in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'PHP Fatal: ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line'],
                    'data'    => [],
                ]);
            }
        });

        try {
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

            // Get Proclaim extension ID and fix database
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->select('extension_id')
                ->from('#__extensions')
                ->where($db->quoteName('element') . ' = ' . $db->q('com_proclaim'));
            $db->setQuery($query);
            $cid = (int) $db->loadResult();

            // Fix database schema
            try {
                $DatabaseModel = new DatabaseModel();
                $DatabaseModel->fix([$cid]);
            } catch (\Exception $e) {
                Log::add('Database fix error: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
            }

            // Fix assets
            try {
                $fix     = new Cwmassets();
                $results = $fix->build();

                if (isset($results->query) && \is_array($results->query)) {
                    foreach ($results->query as $key => $items) {
                        foreach ($items as $item) {
                            Cwmassets::fixAssets($key, $item);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::add('Asset fix error: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
            }

            // Fix ownership
            try {
                Cwmrestore::fixOwnershipPublic();
            } catch (\Exception $e) {
                Log::add('Ownership fix error: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
            }

            // Purge update cache
            try {
                $updateModel = $app->bootComponent('com_joomlaupdate')
                    ->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);
                $updateModel->purge();
            } catch (\Exception $e) {
                Log::add('Update cache purge error: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
            }

            // Flush assets
            try {
                $app->flushAssets();
            } catch (\Exception $e) {
                Log::add('Flush assets error: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
            }

            $this->sendJsonResponse(true, Text::_('JBS_CMN_OPERATION_SUCCESSFUL'));
        } catch (\Exception $e) {
            $this->sendJsonResponse(false, 'Import finalize error: ' . $e->getMessage());
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
        $app = Factory::getApplication();

        // Capture and log any stray output (PHP errors, warnings, etc.)
        $strayOutput = '';

        while (ob_get_level()) {
            $strayOutput .= ob_get_clean();
        }

        if (!empty($strayOutput)) {
            Log::add('Stray output captured: ' . substr($strayOutput, 0, 500), Log::WARNING, 'com_proclaim');
        }

        // Set JSON headers directly (only if headers not already sent)
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
        }

        $response = [
            'success' => $success,
            'message' => $message,
            'data'    => $data,
        ];

        echo json_encode($response, JSON_THROW_ON_ERROR);

        $app->close();
    }
}
