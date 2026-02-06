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
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
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
     * Get a list of tables for AJAX export
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
        }

        $app    = Factory::getApplication();
        $config = $app->getConfig();

        // Create a unique export ID for this session
        $exportId = md5(uniqid((string) mt_rand(), true));
        $tmpPath  = $config->get('tmp_path') . '/proclaim_export_' . $exportId . '.sql';

        // Initialize an empty file
        file_put_contents($tmpPath, '');

        $tables     = CwmdbHelper::getObjects();
        $tableNames = array_column($tables, 'name');

        // Return the export ID so it can be passed to later calls
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
        }

        $app      = Factory::getApplication();
        $input    = $app->getInput();
        $config   = $app->getConfig();
        $table    = $input->get('table', '', 'string');
        $exportId = $input->get('exportId', '', 'string');

        if (empty($table)) {
            $this->sendJsonResponse(false, 'No table specified');
        }

        if (empty($exportId)) {
            $this->sendJsonResponse(false, 'No export ID specified');
        }

        // Build the temp file path from the export ID
        $tmpPath = $config->get('tmp_path') . '/proclaim_export_' . $exportId . '.sql';

        if (!is_file($tmpPath)) {
            $this->sendJsonResponse(false, 'Export file not found. Please start again.');
        }

        // Export the table and append directly to file
        $backup    = new Cwmbackup();
        $tableData = $backup->getExportTableData($table);

        // Append to temp file
        if (file_put_contents($tmpPath, $tableData, FILE_APPEND) === false) {
            $this->sendJsonResponse(false, 'Failed to write export data');
        }

        $this->sendJsonResponse(true, '', ['table' => $table, 'size' => \strlen($tableData)]);
    }

    /**
     * Finalize export and create a download file
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function finalizeExportXHR(): void
    {
        // Register a shutdown handler to catch fatal errors
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
                ], JSON_THROW_ON_ERROR);
            }
        });

        try {
            // Check for request forgeries
            if (!Session::checkToken('get') && !Session::checkToken()) {
                $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));
            }

            $app      = Factory::getApplication();
            $input    = $app->getInput();
            $mode     = $input->get('mode', 'download', 'string');
            $exportId = $input->get('exportId', '', 'string');
            $session  = $app->getSession();
            $config   = $app->getConfig();

            if (empty($exportId)) {
                $this->sendJsonResponse(false, 'No export ID specified');
            }

            // Build the temp file path from the export ID
            $tmpExportPath = $config->get('tmp_path') . '/proclaim_export_' . $exportId . '.sql';

            if (!is_file($tmpExportPath)) {
                $this->sendJsonResponse(false, 'Export file not found. Please start again.');
            }

            // Generate filename using standardized method
            $filename = Cwmbackup::generateBackupFilename();

            if ($mode === 'save') {
                // Save to back up folder
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
        }

        $app      = Factory::getApplication();
        $input    = $app->getInput();
        $userfile = $input->files->get('importdb', null, 'raw');

        if (!\is_array($userfile) || $userfile['error'] || $userfile['size'] < 1) {
            $this->sendJsonResponse(false, Text::_('JBS_IBM_ERROR_UPLOAD_FAILED'));
        }

        // Move uploaded file to tmp
        $config   = $app->getConfig();
        $tmpPath  = $config->get('tmp_path') . '/' . $userfile['name'];

        if (!File::upload($userfile['tmp_name'], $tmpPath, false, true)) {
            $this->sendJsonResponse(false, 'Failed to move uploaded file');
        }

        // Handle ZIP files
        if (str_ends_with(strtolower($tmpPath), '.zip')) {
            $package = InstallerHelper::unpack($tmpPath, true);

            if (!isset($package['dir'])) {
                $this->sendJsonResponse(false, 'Failed to extract ZIP file');
            }

            // Find SQL file in extracted directory
            $files = Folder::files($package['dir'], '\.sql$', true, true);

            if (empty($files)) {
                $this->sendJsonResponse(false, 'No SQL file found in archive');
            }

            $tmpPath = $files[0];
        }

        // Generate session ID and store a file path
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
        }

        $app    = Factory::getApplication();
        $input  = $app->getInput();
        $type   = $input->get('type', '', 'string');
        $source = $input->get('source', '', 'string');

        if (empty($type) || empty($source)) {
            $this->sendJsonResponse(false, 'Missing type or source parameter');
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
        }

        // Generate session ID and store a file path
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
        }

        $app       = Factory::getApplication();
        $input     = $app->getInput();
        $sessionId = $input->get('sessionId', '', 'string');
        $session   = $app->getSession();

        $filePath = $session->get('proclaim_import_' . $sessionId, '', 'CWM');

        if (empty($filePath) || !is_file($filePath)) {
            $this->sendJsonResponse(false, 'Import file not found');
        }

        // Read and validate a file
        $content = file_get_contents($filePath);

        if ($content === false) {
            $this->sendJsonResponse(false, Text::_('JBS_INS_ERROR_SQL_READBUFFER'));
        }

        // Validate it's a Proclaim backup
        $isnot = substr_count($content, '#__bsms_studies');

        if ($isnot === 0) {
            $this->sendJsonResponse(false, Text::_('JBS_IBM_NOT_DB'));
        }

        // Split into queries
        $db      = Factory::getContainer()->get('DatabaseDriver');
        $queries = $db->splitSql($content);

        // Store queries in session for batch processing
        $session->set('proclaim_import_queries_' . $sessionId, $queries, 'CWM');

        // Calculate batches (50 queries per batch)
        $batchSize    = 50;
        $totalBatches = (int) ceil(\count($queries) / $batchSize);

        $this->sendJsonResponse(true, '', [
            'totalQueries' => \count($queries),
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
        }

        $app       = Factory::getApplication();
        $input     = $app->getInput();
        $sessionId = $input->get('sessionId', '', 'string');
        $batch     = $input->get('batch', 0, 'int');
        $session   = $app->getSession();

        $queries = $session->get('proclaim_import_queries_' . $sessionId, [], 'CWM');

        if (empty($queries)) {
            $this->sendJsonResponse(false, 'No queries found in session');
        }

        $batchSize    = 50;
        $start        = $batch * $batchSize;
        $batchQueries = \array_slice($queries, $start, $batchSize);

        $db = Factory::getContainer()->get('DatabaseDriver');

        // For the first batch, drop existing tables
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

        $this->sendJsonResponse(true, '', ['batch' => $batch, 'processed' => \count($batchQueries)]);
    }

    /**
     * Finalize import-fix assets and ownership
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function finalizeImportXHR(): void
    {
        // Try to increase the memory limit (best-effort may fail on shared hosting)
        $currentLimit = $this->getMemoryLimitBytes();
        $targetLimit  = 512 * 1024 * 1024; // 512M

        if ($currentLimit > 0 && $currentLimit < $targetLimit) {
            // Try progressive increases - some hosts allow modest increases but not large ones
            foreach (['256M', '384M', '512M'] as $tryLimit) {
                @ini_set('memory_limit', $tryLimit);
            }
        }

        // Suppress error display and capture any output
        $previousErrorReporting = error_reporting(E_ALL);
        $previousDisplayErrors  = \ini_get('display_errors');
        ini_set('display_errors', '0');
        ob_start();

        // Register a shutdown handler to catch fatal errors
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
                ], JSON_THROW_ON_ERROR);
            }
        });

        try {
            // Check for request forgeries
            if (!Session::checkToken('get') && !Session::checkToken()) {
                $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));
            }

            $app          = Factory::getApplication();
            $input        = $app->getInput();
            $sessionId    = $input->get('sessionId', '', 'string');
            $skipAssetFix = $input->get('skipAssetFix', '0', 'string') === '1';
            $session      = $app->getSession();

            // Clean up session data
            $session->set('proclaim_import_' . $sessionId, '', 'CWM');
            $session->set('proclaim_import_queries_' . $sessionId, '', 'CWM');

            // Fix assets using a lightweight direct SQL approach (unless skipped for testing)
            if (!$skipAssetFix) {
                $this->fixAssetsLightweight();
            } else {
                Log::add('Asset fix skipped (testing mode)', Log::INFO, 'com_proclaim');
            }

            // Recreate templatecode PHP files from database records
            $templatecodesCreated = $this->recreateTemplatecodeFiles();

            $this->sendJsonResponse(true, Text::_('JBS_CMN_OPERATION_SUCCESSFUL'), [
                'templatecodes_created' => $templatecodesCreated,
            ]);
        } catch (\Exception $e) {
            $this->sendJsonResponse(false, 'Import finalize error: ' . $e->getMessage());
        }
    }

    /**
     * Fix assets using direct SQL to minimize memory usage
     *
     * Instead of loading full Table objects for each record, this method:
     * 1. Gets the parent asset ID for com_proclaim
     * 2. For each table, processes records in batches
     * 3. Uses direct SQL INSERT/UPDATE instead of Table objects
     *
     * @return void
     *
     * @since 10.1.0
     */
    private function fixAssetsLightweight(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Get or create the com_proclaim parent asset
        $parentId = Cwmassets::ensureParentAsset();

        if (!$parentId) {
            Log::add('Could not find or create com_proclaim parent asset ID', Log::WARNING, 'com_proclaim');

            return;
        }

        Log::add('Using parent asset ID: ' . $parentId, Log::INFO, 'com_proclaim');

        $assetTables = Cwmassets::getAssetObjects();

        // Adjust batch size based on available memory (smaller batches for constrained environments)
        $memoryLimit = $this->getMemoryLimitBytes();
        $batchSize   = 100;

        if ($memoryLimit > 0 && $memoryLimit < 128 * 1024 * 1024) {
            $batchSize = 25; // Very constrained (< 128M)
        } elseif ($memoryLimit > 0 && $memoryLimit < 256 * 1024 * 1024) {
            $batchSize = 50; // Moderately constrained (< 256M)
        }

        foreach ($assetTables as $tableInfo) {
            try {
                // Get total count for this table
                $query = $db->getQuery(true);
                $query->select('COUNT(*)')
                    ->from($db->qn($tableInfo['name']));
                $db->setQuery($query);
                $total = (int) $db->loadResult();

                if ($total === 0) {
                    continue;
                }

                // Process in batches
                $offset = 0;

                while ($offset < $total) {
                    // Load a batch of records
                    $query = $db->getQuery(true);
                    $query->select(
                        $db->qn('j.id') . ', ' . $db->qn('j.asset_id') . ', '
                        . $db->qn('a.id', 'aid') . ', ' . $db->qn('a.parent_id') . ', ' . $db->qn('a.rules')
                    )
                        ->from($db->qn($tableInfo['name'], 'j'))
                        ->leftJoin($db->qn('#__assets', 'a') . ' ON (' . $db->qn('a.id') . ' = ' . $db->qn('j.asset_id') . ')')
                        ->setLimit($batchSize, $offset);
                    $db->setQuery($query);
                    $results = $db->loadObjectList();

                    if (empty($results)) {
                        break;
                    }

                    // Process each record in this batch
                    foreach ($results as $item) {
                        $this->fixSingleAssetDirect($db, $tableInfo, $item, $parentId);
                    }

                    // Free memory and force garbage collection
                    unset($results);
                    gc_collect_cycles();

                    $offset += $batchSize;
                }

                Log::add('Fixed assets for ' . $tableInfo['name'] . ' (' . $total . ' records)', Log::INFO, 'com_proclaim');
            } catch (\Exception $e) {
                Log::add('Asset fix error for ' . $tableInfo['name'] . ': ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
            }
        }
    }

    /**
     * Fix a single asset record using direct SQL
     *
     * @param   object  $db         Database driver
     * @param   array   $tableInfo  Table info from getAssetObjects
     * @param   object  $item       Record with id, asset_id, aid, parent_id, rules
     * @param   int     $parentId   Parent asset ID for com_proclaim
     *
     * @return void
     *
     * @since 10.1.0
     */
    private function fixSingleAssetDirect(object $db, array $tableInfo, object $item, int $parentId): void
    {
        $assetName    = 'com_proclaim.' . $tableInfo['assetname'] . '.' . $item->id;
        $defaultRules = '{"core.delete":[],"core.edit":[],"core.edit.state":[]}';

        // Case 1: No asset_id OR asset_id points to non-existent asset (aid is NULL from LEFT JOIN)
        // This is the common case after restore - records have stale asset_ids
        if (empty($item->asset_id) || $item->asset_id == 0 || empty($item->aid)) {
            // Check if asset already exists by name (in case asset_id just wasn't set)
            $query = $db->getQuery(true);
            $query->select($db->qn('id'))
                ->from($db->qn('#__assets'))
                ->where($db->qn('name') . ' = ' . $db->quote($assetName));
            $db->setQuery($query);
            $existingAssetId = $db->loadResult();

            if ($existingAssetId) {
                // Asset exists, just update the record's asset_id
                $query = $db->getQuery(true);
                $query->update($db->qn($tableInfo['name']))
                    ->set($db->qn('asset_id') . ' = ' . (int) $existingAssetId)
                    ->where($db->qn('id') . ' = ' . (int) $item->id);
                $db->setQuery($query);
                $db->execute();
            } else {
                // Create new asset
                $query = $db->getQuery(true);
                $query->insert($db->qn('#__assets'))
                    ->columns($db->qn(['parent_id', 'level', 'name', 'title', 'rules']))
                    ->values(
                        (int) $parentId . ', 4, ' . $db->quote($assetName) . ', ' .
                        $db->quote($tableInfo['assetname'] . ' ' . $item->id) . ', ' .
                        $db->quote($defaultRules)
                    );
                $db->setQuery($query);
                $db->execute();
                $newAssetId = $db->insertid();

                // Update the record with new asset_id
                $query = $db->getQuery(true);
                $query->update($db->qn($tableInfo['name']))
                    ->set($db->qn('asset_id') . ' = ' . (int) $newAssetId)
                    ->where($db->qn('id') . ' = ' . (int) $item->id);
                $db->setQuery($query);
                $db->execute();
            }

            return;
        }

        // Case 2: Has valid asset_id with existing asset but parent_id mismatch or empty rules
        if ($item->parent_id != $parentId || empty($item->rules)) {
            // Update the existing asset record
            $query = $db->getQuery(true);
            $query->update($db->qn('#__assets'))
                ->set($db->qn('parent_id') . ' = ' . (int) $parentId)
                ->set($db->qn('name') . ' = ' . $db->quote($assetName));

            if (empty($item->rules)) {
                $query->set($db->qn('rules') . ' = ' . $db->quote($defaultRules));
            }

            $query->where($db->qn('id') . ' = ' . (int) $item->asset_id);
            $db->setQuery($query);
            $db->execute();
        }
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Get PHP memory limit in bytes
     *
     * @return int Memory limit in bytes, or -1 if unlimited
     *
     * @since 10.1.0
     */
    private function getMemoryLimitBytes(): int
    {
        $limit = \ini_get('memory_limit');

        if ($limit === '-1') {
            return -1; // Unlimited
        }

        $value = (int) $limit;
        $unit  = strtoupper(substr($limit, -1));

        switch ($unit) {
            case 'G':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'M':
                $value *= 1024 * 1024;
                break;
            case 'K':
                $value *= 1024;
                break;
        }

        return $value;
    }

    /**
     * Recreate templatecode PHP files from database records
     *
     * After import, the templatecode table has the PHP code but the actual
     * template files in the tmpl directories don't exist. This method
     * recreates them from the database records.
     *
     * @return int Number of templatecode files created
     *
     * @since 10.1.0
     */
    private function recreateTemplatecodeFiles(): int
    {
        $db      = Factory::getContainer()->get('DatabaseDriver');
        $created = 0;

        try {
            // Get all templatecode records
            $query = $db->getQuery(true);
            $query->select($db->qn(['id', 'type', 'filename', 'templatecode']))
                ->from($db->qn('#__bsms_templatecode'))
                ->where($db->qn('published') . ' = 1');
            $db->setQuery($query);
            $records = $db->loadObjectList();

            if (empty($records)) {
                Log::add('No templatecode records to recreate', Log::INFO, 'com_proclaim');

                return 0;
            }

            // Type to path mapping (matches CwmtemplatecodeTable::store())
            $typePaths = [
                1 => JPATH_ROOT . '/components/com_proclaim/tmpl/Cwmsermons/',
                2 => JPATH_ROOT . '/components/com_proclaim/tmpl/Cwmsermon/',
                3 => JPATH_ROOT . '/components/com_proclaim/tmpl/Cwmteachers/',
                4 => JPATH_ROOT . '/components/com_proclaim/tmpl/Cwmteacher/',
                5 => JPATH_ROOT . '/components/com_proclaim/tmpl/Cwmseriesdisplays/',
                6 => JPATH_ROOT . '/components/com_proclaim/tmpl/Cwmseriesdisplay/',
                7 => JPATH_ROOT . '/modules/mod_proclaim/tmpl/',
            ];

            foreach ($records as $record) {
                $type = (int) $record->type;

                if (!isset($typePaths[$type])) {
                    Log::add('Unknown templatecode type: ' . $type . ' for ID ' . $record->id, Log::WARNING, 'com_proclaim');
                    continue;
                }

                $directory = $typePaths[$type];
                $filename  = 'default_' . $record->filename . '.php';
                $filepath  = $directory . $filename;

                // Ensure directory exists
                if (!is_dir($directory)) {
                    Log::add('Templatecode directory does not exist: ' . $directory, Log::WARNING, 'com_proclaim');
                    continue;
                }

                // Prepare content - ensure security check is present
                $content       = $record->templatecode;
                $securityCheck = "defined('_JEXEC') or die;";

                if (strpos($content, $securityCheck) === false) {
                    $content = "<?php\n" . $securityCheck . "\n" . $content;
                }

                // Write the file
                if (File::write($filepath, $content)) {
                    $created++;
                    Log::add('Created templatecode file: ' . $filepath, Log::INFO, 'com_proclaim');
                } else {
                    Log::add('Failed to create templatecode file: ' . $filepath, Log::WARNING, 'com_proclaim');
                }
            }

            Log::add('Recreated ' . $created . ' templatecode files', Log::INFO, 'com_proclaim');
        } catch (\Exception $e) {
            Log::add('Error recreating templatecode files: ' . $e->getMessage(), Log::ERROR, 'com_proclaim');
        }

        return $created;
    }

    /**
     * Send JSON response and terminate.
     *
     * @param   bool    $success  Success status
     * @param   string  $message  Message
     * @param   array   $data     Additional data
     *
     * @return never
     *
     * @throws \Exception
     * @since 10.1.0
     */
    private function sendJsonResponse(bool $success, string $message = '', array $data = []): never
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

        // Use exit instead of $app->close() to avoid any shutdown processing issues
        exit(0);
    }
}
