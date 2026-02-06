<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

declare(strict_types=1);

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use CWM\Component\Proclaim\Administrator\Model\CwmassetsModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

/**
 * Controller for Assets
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmassetsController extends BaseController
{
    /**
     * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanism from kicking in
     *
     * @var  string
     *
     * @since 7.0
     */
    protected string $view_list = 'cwmassets';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'cwmassets';

    /**
     * Constructor.
     *
     * @param   string  $task  An optional associative array of configuration settings.
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.5
     */
    public function execute($task): void
    {
        // Allow XHR tasks through without redirect
        if (str_ends_with($task, 'XHR')) {
            parent::execute($task);

            return;
        }

        if ($task !== 'run' && $task !== 'checkassets' && $task !== 'clear') {
            $task = 'browse';
        }

        parent::execute($task);
    }

    // =========================================================================
    // Legacy Methods (kept for backwards compatibility)
    // =========================================================================

    /**
     * Check Assets (legacy - redirects to view)
     *
     * @return void
     *
     * @throws \Exception
     * @since 8.0.0
     */
    public function checkassets(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $model      = new CwmassetsModel();
        $checklists = $model->checkAssets();
        $session    = Factory::getApplication()->getSession();
        $session->set('asset_stack', '', 'CWM');
        $session->set('checklists', $checklists, 'CWM');
        $this->input->set('view', 'Cwmassets');

        $this->display(false);
    }

    /**
     * Browse - display the asset check view
     *
     * @return void
     *
     * @throws \Exception
     * @since 8.0.0
     */
    public function browse(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        // Just display the view - let AJAX handle the actual fixing
        $this->checkassets();
    }

    /**
     * Run function loop (legacy)
     *
     * @return void
     *
     * @throws \Exception
     * @since 8.0.0
     */
    public function run(): void
    {
        // Redirect to checkassets which will show the modern UI
        $this->checkassets();
    }

    /**
     * Clear session state
     *
     * @return void
     *
     * @throws \Exception
     * @since 9.0.2
     */
    public function clear(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        Cwmhelper::clearCache();
        $session = Factory::getApplication()->getSession();
        $session->set('asset_stack', '', 'CWM');
        $this->input->set('view', 'Cwmassets');
        $this->display(false);
    }

    // =========================================================================
    // AJAX Methods
    // =========================================================================

    /**
     * Check assets status via AJAX
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function checkAssetsXHR(): void
    {
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));

            return;
        }

        $model  = new CwmassetsModel();
        $assets = $model->checkAssets();

        // Translate realname values before sending (JS doesn't have these language strings)
        foreach ($assets as &$asset) {
            $asset['realname'] = Text::_($asset['realname']);
        }

        $this->sendJsonResponse(true, '', ['assets' => $assets]);
    }

    /**
     * Get list of tables and counts for asset fixing
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function getAssetTablesXHR(): void
    {
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));

            return;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $assetTables = Cwmassets::getAssetObjects();

        // Ensure parent asset exists
        $parentId = Cwmassets::ensureParentAsset();

        if (!$parentId) {
            $this->sendJsonResponse(false, 'Could not find or create parent asset');

            return;
        }

        $tables = [];
        $totalRecords = 0;

        foreach ($assetTables as $tableInfo) {
            $query = $db->getQuery(true);
            $query->select('COUNT(*)')
                ->from($db->qn($tableInfo['name']));
            $db->setQuery($query);
            $count = (int) $db->loadResult();

            $tables[] = [
                'name'      => $tableInfo['name'],
                'assetname' => $tableInfo['assetname'],
                'realname'  => Text::_($tableInfo['realname']),
                'count'     => $count,
            ];

            $totalRecords += $count;
        }

        $this->sendJsonResponse(true, '', [
            'tables'       => $tables,
            'totalRecords' => $totalRecords,
            'parentId'     => $parentId,
        ]);
    }

    /**
     * Fix assets for a specific table via AJAX
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function fixAssetBatchXHR(): void
    {
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));

            return;
        }

        $app       = Factory::getApplication();
        $input     = $app->getInput();
        $tableName = $input->get('table', '', 'string');
        $assetName = $input->get('assetname', '', 'string');
        $offset    = $input->get('offset', 0, 'int');
        $batchSize = $input->get('batchSize', 100, 'int');

        if (empty($tableName) || empty($assetName)) {
            $this->sendJsonResponse(false, 'Missing table or assetname parameter');

            return;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $parentId = Cwmassets::ensureParentAsset();

        if (!$parentId) {
            $this->sendJsonResponse(false, 'Could not find or create parent asset');

            return;
        }

        // Load batch of records
        $query = $db->getQuery(true);
        $query->select('j.id, j.asset_id, a.id as aid, a.parent_id, a.rules')
            ->from($db->qn($tableName) . ' as j')
            ->leftJoin('#__assets as a ON (a.id = j.asset_id)')
            ->setLimit($batchSize, $offset);
        $db->setQuery($query);
        $results = $db->loadObjectList();

        $processed = 0;
        $fixed = 0;

        foreach ($results as $item) {
            $processed++;
            $wasFixed = $this->fixSingleAssetDirect($db, $tableName, $assetName, $item, $parentId);

            if ($wasFixed) {
                $fixed++;
            }
        }

        $this->sendJsonResponse(true, '', [
            'table'     => $tableName,
            'offset'    => $offset,
            'processed' => $processed,
            'fixed'     => $fixed,
        ]);
    }

    /**
     * Fix a single asset record using direct SQL
     *
     * @param   object  $db         Database driver
     * @param   string  $tableName  Table name
     * @param   string  $assetName  Asset name (e.g., 'message', 'teacher')
     * @param   object  $item       Record with id, asset_id, aid, parent_id, rules
     * @param   int     $parentId   Parent asset ID for com_proclaim
     *
     * @return bool True if asset was fixed, false if already OK
     *
     * @since 10.1.0
     */
    private function fixSingleAssetDirect(object $db, string $tableName, string $assetName, object $item, int $parentId): bool
    {
        $assetFullName = 'com_proclaim.' . $assetName . '.' . $item->id;
        $defaultRules = '{"core.delete":[],"core.edit":[],"core.edit.state":[]}';

        // Check if asset actually exists (aid comes from LEFT JOIN)
        $assetExists = !empty($item->aid);

        // Case 1: No asset_id OR asset_id points to non-existent asset
        if (empty($item->asset_id) || $item->asset_id == 0 || !$assetExists) {
            // Check if asset already exists by name
            $query = $db->getQuery(true);
            $query->select('id')
                ->from('#__assets')
                ->where('name = ' . $db->quote($assetFullName));
            $db->setQuery($query);
            $existingAssetId = $db->loadResult();

            if ($existingAssetId) {
                // Asset exists, just update the record's asset_id
                $query = $db->getQuery(true);
                $query->update($db->qn($tableName))
                    ->set('asset_id = ' . (int) $existingAssetId)
                    ->where('id = ' . (int) $item->id);
                $db->setQuery($query);
                $db->execute();
            } else {
                // Create new asset
                $query = $db->getQuery(true);
                $query->insert('#__assets')
                    ->columns(['parent_id', 'level', 'name', 'title', 'rules'])
                    ->values(
                        (int) $parentId . ', 4, ' . $db->quote($assetFullName) . ', ' .
                        $db->quote($assetName . ' ' . $item->id) . ', ' .
                        $db->quote($defaultRules)
                    );
                $db->setQuery($query);
                $db->execute();
                $newAssetId = $db->insertid();

                // Update the record with new asset_id
                $query = $db->getQuery(true);
                $query->update($db->qn($tableName))
                    ->set('asset_id = ' . (int) $newAssetId)
                    ->where('id = ' . (int) $item->id);
                $db->setQuery($query);
                $db->execute();
            }

            return true;
        }

        // Case 2: Has valid asset_id with existing asset but parent_id mismatch or empty rules
        if ($item->parent_id != $parentId || empty($item->rules)) {
            $query = $db->getQuery(true);
            $query->update('#__assets')
                ->set('parent_id = ' . (int) $parentId)
                ->set('name = ' . $db->quote($assetFullName));

            if (empty($item->rules)) {
                $query->set('rules = ' . $db->quote($defaultRules));
            }

            $query->where('id = ' . (int) $item->asset_id);
            $db->setQuery($query);
            $db->execute();

            return true;
        }

        // Asset is already OK
        return false;
    }

    /**
     * Rebuild asset tree after fixing
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function rebuildAssetTreeXHR(): void
    {
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));

            return;
        }

        try {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $assetTable = new \Joomla\CMS\Table\Asset($db);
            $assetTable->rebuild();

            Log::add('Asset tree rebuilt successfully', Log::INFO, 'com_proclaim');

            $this->sendJsonResponse(true, Text::_('JBS_CMN_OPERATION_SUCCESSFUL'));
        } catch (\Exception $e) {
            $this->sendJsonResponse(false, 'Failed to rebuild asset tree: ' . $e->getMessage());
        }
    }

    /**
     * Send JSON response helper
     *
     * @param   bool    $success  Success status
     * @param   string  $message  Message
     * @param   array   $data     Additional data
     *
     * @return void
     *
     * @since 10.1.0
     */
    private function sendJsonResponse(bool $success, string $message = '', array $data = []): void
    {
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
        exit(0);
    }
}
