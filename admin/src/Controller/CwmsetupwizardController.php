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
        $allowed = ['display', 'apply', 'dismiss', 'dismissChecklist', 'getStepData'];

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
            'teacher_name'          => trim((string) ($data['teacher_name'] ?? '')),
            'default_bible_version' => preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($data['default_bible_version'] ?? 'kjv')),
            'provider_getbible'     => (int) ($data['provider_getbible'] ?? 1),
            'provider_api_bible'    => (int) ($data['provider_api_bible'] ?? 0),
            'api_bible_api_key'     => preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($data['api_bible_api_key'] ?? '')),
            'uploadpath'            => trim((string) ($data['uploadpath'] ?? '/images/biblestudy/media/')),
            'metadesc'              => trim((string) ($data['metadesc'] ?? '')),
            'primary_media'         => \in_array($data['primary_media'] ?? '', ['local', 'youtube', 'vimeo', 'direct'], true)
                ? $data['primary_media'] : 'local',
            'create_sample_content' => !empty($data['create_sample_content']),
            'use_default_images'    => !empty($data['use_default_images']),
            'enable_ai'             => !empty($data['enable_ai']),
            'ai_provider'           => \in_array($data['ai_provider'] ?? '', ['claude', 'openai', 'gemini'], true)
                ? $data['ai_provider'] : 'claude',
            'enable_podcast'       => !empty($data['enable_podcast']),
            'enable_backup'        => !empty($data['enable_backup']),
            'enable_comments'      => !empty($data['enable_comments']),
            'social_sharing'       => !empty($data['social_sharing']),
            'analytics_enabled'    => (int) ($data['analytics_enabled'] ?? 1),
            'studylistlimit'       => (int) ($data['studylistlimit'] ?? 20),
            'download_button_text' => trim((string) ($data['download_button_text'] ?? 'Listen')),
            'gdpr_mode'            => (int) ($data['gdpr_mode'] ?? 0),
            'ai_voice'             => \in_array($data['ai_voice'] ?? '', ['third_person', 'first_person', 'conversational', 'summary'], true)
                ? $data['ai_voice'] : 'third_person',
            // Podcast details
            'podcast_title'       => trim((string) ($data['podcast_title'] ?? '')),
            'podcast_description' => trim((string) ($data['podcast_description'] ?? '')),
            'podcast_author'      => trim((string) ($data['podcast_author'] ?? '')),
            'podcast_email'       => filter_var(trim((string) ($data['podcast_email'] ?? '')), FILTER_SANITIZE_EMAIL),
            // Simple mode template
            'simple_mode_template' => \in_array($data['simple_mode_template'] ?? '', ['simple_mode1', 'simple_mode2'], true)
                ? $data['simple_mode_template'] : 'simple_mode1',
            'simplegridtextoverlay' => (int) ($data['simplegridtextoverlay'] ?? 1),
            // Platform API keys
            'youtube_api_key'    => preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($data['youtube_api_key'] ?? '')),
            'youtube_channel_id' => preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($data['youtube_channel_id'] ?? '')),
            'vimeo_access_token' => preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($data['vimeo_access_token'] ?? '')),
            // Location details
            'location_address'  => trim((string) ($data['location_address'] ?? '')),
            'location_city'     => trim((string) ($data['location_city'] ?? '')),
            'location_state'    => trim((string) ($data['location_state'] ?? '')),
            'location_postcode' => trim((string) ($data['location_postcode'] ?? '')),
            'location_phone'    => trim((string) ($data['location_phone'] ?? '')),
        ];

        /** @var CwmsetupwizardModel $model */
        $model = $this->getModel('Cwmsetupwizard');

        try {
            $summary = $model->applyWizard($sanitized);

            // Multi-Campus: redirect to Location Access Wizard for group mapping
            $redirect = ($sanitized['ministry_style'] === 'multi_campus')
                ? 'index.php?option=com_proclaim&view=cwmlocationwizard'
                : 'index.php?option=com_proclaim&view=cwmcpanel';

            $message = ($sanitized['ministry_style'] === 'multi_campus')
                ? Text::_('JBS_WIZARD_SETUP_SUCCESS_CAMPUS')
                : Text::_('JBS_WIZARD_SETUP_SUCCESS');

            $this->sendJsonResponse(true, $message, [
                'summary'  => $summary,
                'redirect' => $redirect,
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
     * Dismiss the post-wizard checklist (non-AJAX, redirects to cpanel).
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.0
     */
    public function dismissChecklist(): void
    {
        if (!Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_proclaim')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'));
        }

        /** @var CwmsetupwizardModel $model */
        $model = $this->getModel('Cwmsetupwizard');
        $model->dismissChecklist();

        $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel');
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
