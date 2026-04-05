<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

/**
 * Setup Wizard controller — multi-step first-run configuration.
 *
 * Non-AJAX tasks render the wizard page; AJAX tasks return JSON.
 *
 * @since  10.3.0
 */
class CwmsetupwizardController extends BaseController
{
    /**
     * @var    string
     * @since  10.3.0
     */
    protected string $view_list = 'cwmsetupwizard';

    /**
     * @var    string
     * @since  10.3.0
     */
    protected $default_view = 'cwmsetupwizard';

    /**
     * Route tasks — allow only known tasks.
     *
     * @param   string  $task  Task name.
     *
     * @return  mixed
     *
     * @throws  \Exception
     * @since   10.3.0
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
     * @param   bool   $cachable   Whether the view output can be cached.
     * @param   array  $urlparams  URL parameters.
     *
     * @return  static
     *
     * @throws  \Exception
     * @since   10.3.0
     */
    public function display($cachable = false, $urlparams = []): static
    {
        Factory::getApplication()->getInput()->set('view', 'cwmsetupwizard');

        return parent::display($cachable, $urlparams);
    }

    /**
     * Apply the wizard configuration (AJAX).
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.0
     */
    public function apply(): void
    {
        if (!Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));
        }

        $app = Factory::getApplication();

        if (!$app->getIdentity()->authorise('core.admin', 'com_proclaim')) {
            $this->sendJsonResponse(false, Text::_('JERROR_ALERTNOAUTHOR'));
        }

        $rawData = $app->getInput()->post->get('wizard_data', '{}', 'raw');

        try {
            $data = json_decode($rawData, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));

            return;
        }

        if (!\is_array($data)) {
            $this->sendJsonResponse(false, 'Invalid wizard data.');

            return;
        }

        // Sanitize inputs
        $sanitized = [
            'ministry_style' => \in_array($data['ministry_style'] ?? '', ['simple', 'full_media', 'multi_campus'], true)
                ? $data['ministry_style'] : 'simple',
            'org_name'              => trim((string) ($data['org_name'] ?? '')),
            'default_bible_version' => preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($data['default_bible_version'] ?? 'kjv')),
            'provider_getbible'     => (int) ($data['provider_getbible'] ?? 1),
            'provider_api_bible'    => (int) ($data['provider_api_bible'] ?? 0),
            'uploadpath'            => trim((string) ($data['uploadpath'] ?? '/images/biblestudy/media/')),
            'primary_media'         => \in_array($data['primary_media'] ?? '', ['local', 'youtube', 'vimeo', 'direct'], true)
                ? $data['primary_media'] : 'local',
            'create_sample_content' => !empty($data['create_sample_content']),
            'enable_ai'             => !empty($data['enable_ai']),
            'ai_provider'           => \in_array($data['ai_provider'] ?? '', ['claude', 'openai', 'gemini'], true)
                ? $data['ai_provider'] : 'claude',
            'analytics_enabled' => (int) ($data['analytics_enabled'] ?? 1),
        ];

        /** @var CwmsetupwizardModel $model */
        $model = $this->getModel('Cwmsetupwizard');

        try {
            $summary = $model->applyWizard($sanitized);
            $this->sendJsonResponse(true, Text::_('JBS_WIZARD_SETUP_SUCCESS'), [
                'summary'  => $summary,
                'redirect' => 'index.php?option=com_proclaim&view=cwmcpanel',
            ]);
        } catch (\RuntimeException $e) {
            $this->sendJsonResponse(false, $e->getMessage() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        }
    }

    /**
     * Dismiss the wizard without applying changes (AJAX).
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.0
     */
    public function dismiss(): void
    {
        if (!Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));
        }

        if (!Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_proclaim')) {
            $this->sendJsonResponse(false, Text::_('JERROR_ALERTNOAUTHOR'));
        }

        /** @var CwmsetupwizardModel $model */
        $model = $this->getModel('Cwmsetupwizard');

        try {
            $model->dismiss();
            $this->sendJsonResponse(true, Text::_('JBS_WIZARD_DISMISSED'));
        } catch (\RuntimeException $e) {
            $this->sendJsonResponse(false, $e->getMessage() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        }
    }

    /**
     * Return current wizard state as JSON (AJAX).
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.0
     */
    public function getStepData(): void
    {
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN'));
        }

        /** @var CwmsetupwizardModel $model */
        $model = $this->getModel('Cwmsetupwizard');
        $data  = $model->getCurrentState();

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
     * @since   10.3.0
     */
    private function sendJsonResponse(bool $success, string $message = '', array $data = []): never
    {
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
