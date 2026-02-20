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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

/**
 * Location Setup Wizard controller
 *
 * Handles the multi-step location system configuration wizard.
 * Non-AJAX tasks render the wizard page; AJAX tasks return JSON.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class CwmlocationwizardController extends BaseController
{
    /**
     * Prevents Joomla's pluralization mechanism from altering the view name.
     *
     * @var    string
     * @since  10.1.0
     */
    protected string $view_list = 'cwmlocationwizard';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  10.1.0
     */
    protected $default_view = 'cwmlocationwizard';

    /**
     * Route tasks — allow only known tasks.
     *
     * @param   string  $task  Task name.
     *
     * @return  mixed
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function execute($task): mixed
    {
        $allowed = ['display', 'apply', 'dismiss', 'getStepData'];

        if (!\in_array($task, $allowed, true)) {
            $task = 'display';
        }

        return parent::execute($task);
    }

    /**
     * Display the wizard page.
     *
     * @param   bool    $cachable   Whether the view output can be cached.
     * @param   array   $urlparams  URL parameters.
     *
     * @return  static
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function display($cachable = false, $urlparams = []): static
    {
        Factory::getApplication()->getInput()->set('view', 'cwmlocationwizard');

        return parent::display($cachable, $urlparams);
    }

    /**
     * Apply the wizard configuration (AJAX).
     *
     * Expects POST data:
     *   - mapping: JSON string with { locationId: [groupId, ...], ... }
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function apply(): void
    {
        if (!Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));
        }

        $app   = Factory::getApplication();
        $input = $app->getInput();

        if (!$app->getIdentity()->authorise('core.admin', 'com_proclaim')) {
            $this->sendJsonResponse(false, Text::_('JERROR_ALERTNOAUTHOR'));
        }

        $rawMapping     = $input->post->get('mapping', '{}', 'raw');
        $rawPermissions = $input->post->get('permissions', '{}', 'raw');
        $mapping        = [];
        $permissions    = [];

        if (\is_string($rawMapping)) {
            $decoded = json_decode($rawMapping, true);

            if (\is_array($decoded)) {
                $mapping = $decoded;
            }
        }

        if (\is_string($rawPermissions)) {
            $decoded = json_decode($rawPermissions, true);

            if (\is_array($decoded)) {
                $permissions = $decoded;
            }
        }

        // Sanitise mapping: keys must be int-string location IDs, values must be int arrays
        $sanitised = [];

        foreach ($mapping as $locationId => $groupIds) {
            $locId = (int) $locationId;

            if ($locId <= 0) {
                continue;
            }

            $sanitised[(string) $locId] = array_map('intval', (array) $groupIds);
        }

        // Sanitise permissions: keys must be int group IDs, values must be valid presets
        $validPresets         = ['full', 'editor', 'none'];
        $sanitisedPermissions = [];

        foreach ($permissions as $groupId => $preset) {
            $gid = (int) $groupId;

            if ($gid <= 0 || !\in_array($preset, $validPresets, true)) {
                continue;
            }

            $sanitisedPermissions[$gid] = $preset;
        }

        /** @var \CWM\Component\Proclaim\Administrator\Model\CwmlocationwizardModel $model */
        $model = $this->getModel('Cwmlocationwizard');

        if ($model->applyWizard($sanitised, $sanitisedPermissions)) {
            $this->sendJsonResponse(true, Text::_('JBS_WIZARD_APPLY_SUCCESS'), [
                'redirect' => 'index.php?option=com_proclaim&view=cwmlocations',
            ]);
        } else {
            $this->sendJsonResponse(false, $model->getError() ?: Text::_('JBS_WIZARD_APPLY_ERROR'));
        }
    }

    /**
     * Dismiss the wizard without applying changes (AJAX).
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function dismiss(): void
    {
        if (!Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));
        }

        if (!Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_proclaim')) {
            $this->sendJsonResponse(false, Text::_('JERROR_ALERTNOAUTHOR'));
        }

        /** @var \CWM\Component\Proclaim\Administrator\Model\CwmlocationwizardModel $model */
        $model = $this->getModel('Cwmlocationwizard');

        if ($model->dismiss()) {
            $this->sendJsonResponse(true, Text::_('JBS_WIZARD_DISMISSED'));
        } else {
            $this->sendJsonResponse(false, $model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        }
    }

    /**
     * Return step data as JSON for client-side rendering (AJAX).
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function getStepData(): void
    {
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));
        }

        $step = (int) Factory::getApplication()->getInput()->get('step', 1, 'int');

        /** @var \CWM\Component\Proclaim\Administrator\Model\CwmlocationwizardModel $model */
        $model = $this->getModel('Cwmlocationwizard');
        $data  = [];

        switch ($step) {
            case 1:
                $data = $model->getDetectionInfo();
                break;

            case 3:
                $data = [
                    'locations' => $model->getLocations(),
                    'groups'    => $model->getGroups(),
                    'mapping'   => $model->getCurrentMapping(),
                ];
                break;

            case 5:
                $rawMapping = Factory::getApplication()->getInput()->get('mapping', '{}', 'raw');
                $mapping    = \is_string($rawMapping) ? (json_decode($rawMapping, true) ?? []) : [];
                $data       = $model->getPreviewData($mapping);
                break;
        }

        $this->sendJsonResponse(true, '', $data);
    }

    /**
     * Send a JSON response and terminate execution.
     *
     * @param   bool    $success  Whether the operation succeeded.
     * @param   string  $message  Human-readable message.
     * @param   array   $data     Extra data payload.
     *
     * @return  never
     *
     * @since   10.1.0
     */
    private function sendJsonResponse(bool $success, string $message = '', array $data = []): never
    {
        // Clean any stray output
        while (ob_get_level()) {
            ob_end_clean();
        }

        $response = json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data,
        ], JSON_THROW_ON_ERROR);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo $response;

        Factory::getApplication()->close();
    }
}
