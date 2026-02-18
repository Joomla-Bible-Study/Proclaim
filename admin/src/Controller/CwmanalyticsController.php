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

use CWM\Component\Proclaim\Administrator\Helper\CwmanalyticsHelper;
use Joomla\CMS\Factory;
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
        Session::checkToken() or $app->redirect('index.php?option=com_proclaim&view=cwmanalytics');

        $result = CwmanalyticsHelper::seedFromLegacy();
        $app->enqueueMessage(
            \sprintf(
                '%d study rows and %d media rows imported from legacy counters.',
                $result['studies'],
                $result['media']
            ),
            'success'
        );

        $app->redirect('index.php?option=com_proclaim&view=cwmanalytics');
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

        CwmanalyticsHelper::exportCsv($start, $end, $locationId);
        $app->close();
    }
}
