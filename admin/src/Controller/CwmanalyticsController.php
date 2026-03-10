<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CwmanalyticsModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Analytics dashboard controller.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class CwmanalyticsController extends BaseController
{
    /**
     * Seed the monthly aggregates table with legacy hit/play/download counts
     * from existing study and media-file records.
     *
     * Safe to run multiple times (idempotent). Redirects back to analytics view
     * with a success/info message.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public function seedLegacy(): void
    {
        $app = Factory::getApplication();

        if (!Session::checkToken()) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN_NOTICE'), 'error');
            $this->setRedirect('index.php?option=com_proclaim&view=cwmanalytics');

            return;
        }

        /** @var CwmanalyticsModel $model */
        $model  = $this->getModel('Cwmanalytics', 'Administrator');
        $result = $model->seedFromLegacy();

        $app->enqueueMessage(
            \sprintf(
                '%d study rows and %d media rows imported from legacy counters.',
                $result['studies'],
                $result['media']
            ),
            'success'
        );

        $this->setRedirect('index.php?option=com_proclaim&view=cwmanalytics');
    }

    /**
     * Export filtered analytics data as a CSV file.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public function exportCsv(): void
    {
        $app = Factory::getApplication();

        if (!Session::checkToken('get') && !Session::checkToken()) {
            $app->enqueueMessage('Invalid token', 'error');
            $app->redirect('index.php?option=com_proclaim&view=cwmanalytics');

            return;
        }

        $start      = $app->getInput()->getString('date_start', date('Y-m-d', strtotime('-29 days')));
        $end        = $app->getInput()->getString('date_end', date('Y-m-d'));
        $locationId = $app->getInput()->getInt('location_id', 0);

        /** @var CwmanalyticsModel $model */
        $model    = $this->getModel('Cwmanalytics', 'Administrator');
        $csv      = $model->exportCsvString($start, $end, $locationId);
        $filename = 'proclaim-analytics-' . $start . '-to-' . $end . '.csv';

        // Match Cwmdownload.php pattern: disable all buffering layers
        // (including zlib compression) before sending file headers.
        ini_set('output_buffering', '0');
        ini_set('zlib.output_compression', '0');

        while (ob_get_level()) {
            @ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . \strlen($csv));
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        flush();

        echo $csv;
        exit;
    }

    /**
     * Return quick-stats analytics for a single message as JSON.
     *
     * Called via AJAX from the Messages and Media Files list views.
     * Returns KPI totals, per-media breakdown, and platform stats.
     *
     * @return  never
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public function getStudyAnalyticsXHR(): never
    {
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->sendJsonResponse(false, Text::_('JINVALID_TOKEN_NOTICE'));
        }

        $app     = Factory::getApplication();
        $studyId = $app->getInput()->getInt('study_id', 0);

        if ($studyId <= 0) {
            $this->sendJsonResponse(false, 'Missing study_id');
        }

        // Access control: verify the user can see this study
        $user = $app->getIdentity();
        $db   = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select([$db->quoteName('access'), $db->quoteName('location_id')])
            ->from($db->quoteName('#__bsms_studies'))
            ->where($db->quoteName('id') . ' = ' . (int) $studyId);
        $db->setQuery($query);
        $study = $db->loadObject();

        if (!$study) {
            $this->sendJsonResponse(false, 'Study not found');
        }

        if (!$user->authorise('core.admin')) {
            $viewLevels = $user->getAuthorisedViewLevels();

            if (!\in_array((int) $study->access, $viewLevels, true)) {
                $this->sendJsonResponse(false, 'Access denied');
            }
        }

        // Default to last 30 days
        $start = date('Y-m-d', strtotime('-29 days'));
        $end   = date('Y-m-d');

        /** @var CwmanalyticsModel $model */
        $model = $this->getModel('Cwmanalytics', 'Administrator');

        $info          = $model->getStudyInfo($studyId);
        $kpi           = $model->getStudyKpi($studyId, $start, $end);
        $mediaFiles    = $model->getStudyMediaFiles($studyId, $start, $end);
        $platformStats = $model->getPlatformStatsForStudy($studyId);

        // Parse media_params to extract button labels
        foreach ($mediaFiles as &$mf) {
            $label = '';

            if (!empty($mf['media_params'])) {
                try {
                    $params = new Registry($mf['media_params']);
                    $label  = $params->get('media_button_text', '');
                } catch (\Exception $e) {
                    // Ignore malformed params
                }
            }

            $mf['label'] = $label;
            unset($mf['media_params']);
        }

        unset($mf);

        $this->sendJsonResponse(true, '', [
            'info'          => $info,
            'kpi'           => $kpi,
            'media'         => $mediaFiles,
            'platformStats' => $platformStats,
            'periodStart'   => $start,
            'periodEnd'     => $end,
        ]);
    }

    /**
     * Send a JSON response and terminate execution.
     *
     * @param   bool    $success  Whether the request succeeded.
     * @param   string  $message  Optional message.
     * @param   array   $data     Optional data payload.
     *
     * @return  never
     *
     * @since   10.1.0
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
