<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Site\Controller;

use CWM\Component\Proclaim\Site\Helper\CwmscriptureLookupHelper;
use CWM\Library\Scripture\Helper\ScriptureParamsHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Scripture AJAX controller.
 *
 * Provides an endpoint for frontend Bible version switching.
 *
 * @since  10.1.0
 */
class CwmscriptureController extends BaseController
{
    /**
     * Fetch a passage via AJAX. Returns JSON with passage text.
     *
     * URL: index.php?option=com_proclaim&task=cwmscripture.getPassageXHR&reference=...&version=...&token=1
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public function getPassageXHR(): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        // Clean any prior output, then start a fresh buffer to capture stray
        // PHP warnings/notices that may occur during provider execution.
        while (ob_get_level()) {
            ob_end_clean();
        }

        ob_start();

        if (!Session::checkToken('get')) {
            ob_end_clean();
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store');
            try {
                echo json_encode(
                    ['success' => false, 'message' => Text::_('COM_PROCLAIM_SCRIPTURE_AJAX_INVALID_TOKEN')],
                    JSON_THROW_ON_ERROR
                );
            } catch (\JsonException) {
                echo '{"success":false,"message":'
                    . json_encode(Text::_('COM_PROCLAIM_SCRIPTURE_AJAX_JSON_ENCODE_ERROR'))
                    . '}';
            }
            $app->close();

            return;
        }

        $reference = $input->getString('reference', '');
        $version   = $input->getString('version', 'kjv');

        if (empty($reference)) {
            $this->sendJson($app, [
                'success' => false,
                'message' => Text::_('COM_PROCLAIM_SCRIPTURE_AJAX_NO_REFERENCE'),
            ]);

            return;
        }

        try {
            $scriptureParams = ScriptureParamsHelper::getParams();
        } catch (\Exception $e) {
            $scriptureParams = new Registry();
        }

        $this->sendJson($app, CwmscriptureLookupHelper::lookupPassage($reference, $version, $scriptureParams));
    }

    /**
     * Discard any buffered PHP output and send a clean JSON response.
     *
     * @param   CMSApplicationInterface  $app   Application instance
     * @param   array                    $data  Response payload
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function sendJson(CMSApplicationInterface $app, array $data): void
    {
        // Discard any stray PHP warnings/notices captured during execution
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        try {
            echo json_encode($data, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            echo '{"success":false,"message":'
                . json_encode(Text::_('COM_PROCLAIM_SCRIPTURE_AJAX_JSON_ENCODE_ERROR'))
                . '}';
        }
        $app->close();
    }
}
