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
}
