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
     * Prevents Joomla's pluralization mechanism from altering the view name.
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
     * @since  7.0.0
     */
    protected $default_view = 'cwmassets';

    /**
     * Route tasks to allowed methods or fall back to checkassets.
     *
     * @param   string  $task  The task to execute.
     *
     * @return mixed
     *
     * @throws \Exception
     * @since 7.0.0
     */
    public function execute($task): mixed
    {
        // Allow XHR tasks through without redirect
        if (str_ends_with($task, 'XHR')) {
            return parent::execute($task);
        }

        if ($task !== 'checkassets' && $task !== 'clear') {
            $task = 'checkassets';
        }

        return parent::execute($task);
    }

    /**
     * Check assets and display the asset fix view.
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

        /** @var \CWM\Component\Proclaim\Administrator\Model\CwmassetsModel $model */
        $model      = $this->getModel('Cwmassets');
        $checklists = $model->checkAssets();
        $session    = Factory::getApplication()->getSession();
        $session->set('asset_stack', '', 'CWM');
        $session->set('checklists', $checklists, 'CWM');
        $this->getInput()->set('view', 'Cwmassets');

        $this->display(false);
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
        $this->getInput()->set('view', 'Cwmassets');
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
        }

        /** @var \CWM\Component\Proclaim\Administrator\Model\CwmassetsModel $model */
        $model  = $this->getModel('Cwmassets');
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
        }

        $db          = Factory::getContainer()->get('DatabaseDriver');
        $assetTables = Cwmassets::getAssetObjects();

        // Ensure parent asset exists
        $parentId = Cwmassets::ensureParentAsset();

        if (!$parentId) {
            $this->sendJsonResponse(false, 'Could not find or create parent asset');
        }

        $tables       = [];
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
        }

        $app       = Factory::getApplication();
        $input     = $app->getInput();
        $tableName = $input->get('table', '', 'string');
        $assetName = $input->get('assetname', '', 'string');
        $offset    = $input->get('offset', 0, 'int');
        $batchSize = $input->get('batchSize', 100, 'int');

        if (empty($tableName) || empty($assetName)) {
            $this->sendJsonResponse(false, 'Missing table or assetname parameter');
        }

        $db       = Factory::getContainer()->get('DatabaseDriver');
        $parentId = Cwmassets::ensureParentAsset();

        if (!$parentId) {
            $this->sendJsonResponse(false, 'Could not find or create parent asset');
        }

        // Load a batch of records
        $query = $db->getQuery(true);
        $query->select(
            $db->qn('j.id') . ', ' . $db->qn('j.asset_id') . ', '
            . $db->qn('a.id', 'aid') . ', ' . $db->qn('a.parent_id') . ', ' . $db->qn('a.rules')
        )
            ->from($db->qn($tableName, 'j'))
            ->leftJoin($db->qn('#__assets', 'a') . ' ON (' . $db->qn('a.id') . ' = ' . $db->qn('j.asset_id') . ')')
            ->setLimit($batchSize, $offset);
        $db->setQuery($query);
        $results = $db->loadObjectList();

        $processed = 0;
        $fixed     = 0;

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
        $defaultRules  = '{"core.delete":[],"core.edit":[],"core.edit.state":[]}';

        // Check if asset actually exists (aid comes from LEFT JOIN)
        $assetExists = !empty($item->aid);

        // Case 1: No asset_id OR asset_id points to a non-existent asset
        if (empty($item->asset_id) || $item->asset_id == 0 || !$assetExists) {
            // Check if asset already exists by name
            $query = $db->getQuery(true);
            $query->select($db->qn('id'))
                ->from($db->qn('#__assets'))
                ->where($db->qn('name') . ' = ' . $db->quote($assetFullName));
            $db->setQuery($query);
            $existingAssetId = $db->loadResult();

            if ($existingAssetId) {
                // Asset exists, just update the record's asset_id
                $query = $db->getQuery(true);
                $query->update($db->qn($tableName))
                    ->set($db->qn('asset_id') . ' = ' . (int) $existingAssetId)
                    ->where($db->qn('id') . ' = ' . (int) $item->id);
                $db->setQuery($query);
                $db->execute();
            } else {
                // Create a new asset
                $query = $db->getQuery(true);
                $query->insert($db->qn('#__assets'))
                    ->columns($db->qn(['parent_id', 'level', 'name', 'title', 'rules']))
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
                    ->set($db->qn('asset_id') . ' = ' . (int) $newAssetId)
                    ->where($db->qn('id') . ' = ' . (int) $item->id);
                $db->setQuery($query);
                $db->execute();
            }

            return true;
        }

        // Case 2: Has valid asset_id with existing asset but parent_id mismatch or empty rules
        if ($item->parent_id != $parentId || empty($item->rules)) {
            $query = $db->getQuery(true);
            $query->update($db->qn('#__assets'))
                ->set($db->qn('parent_id') . ' = ' . (int) $parentId)
                ->set($db->qn('name') . ' = ' . $db->quote($assetFullName));

            if (empty($item->rules)) {
                $query->set($db->qn('rules') . ' = ' . $db->quote($defaultRules));
            }

            $query->where($db->qn('id') . ' = ' . (int) $item->asset_id);
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
        }

        try {
            $db         = Factory::getContainer()->get('DatabaseDriver');
            $assetTable = new \Joomla\CMS\Table\Asset($db);
            $assetTable->rebuild();

            Log::add('Asset tree rebuilt successfully', Log::INFO, 'com_proclaim');

            $this->sendJsonResponse(true, Text::_('JBS_CMN_OPERATION_SUCCESSFUL'));
        } catch (\Exception $e) {
            $this->sendJsonResponse(false, 'Failed to rebuild asset tree: ' . $e->getMessage());
        }
    }

    /**
     * Send JSON response and close the application.
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
        $app->setHeader('Content-Type', 'application/json; charset=utf-8');
        $app->setHeader('Cache-Control', 'no-cache, must-revalidate');

        $response = [
            'success' => $success,
            'message' => $message,
            'data'    => $data,
        ];

        echo json_encode($response, JSON_THROW_ON_ERROR);
        $app->close();
    }
}
