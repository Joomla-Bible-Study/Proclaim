<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Controller;

use CWM\Component\Proclaim\Site\Helper\Cwmdownload;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller class for Sermons
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmsermonsController extends BaseController
{
    /**
     * Download?
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0
     */
    public function download(): void
    {
        $input = Factory::getApplication()->getInput();
        $task  = $input->get('task');
        $mid   = $input->getInt('id');

        if ($task === 'download') {
            $downloader = new Cwmdownload();
            $downloader->download($mid);
        }
    }

    /**
     * Add hits to the play count. Used in direct link redirects
     *
     * @return  void
     *
     * @throws \Exception
     * @since 7.0
     */
    public function playHit(): void
    {
        $app      = Factory::getApplication();
        $getMedia = new Cwmmedia();
        $getMedia->hitPlay((int)$app->input->get('id', '', 'int'));

        // Now the hit has been updated will redirect to the url.
        $return = $app->input->get('return');
        $return = base64_decode($return);
        $app->redirect($return);
    }

    /**
     * AJAX endpoint to record a play hit without redirect.
     * Used by Fancybox, inline players, and other JS-based media playback.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public function playHitAjax(): void
    {
        $app = Factory::getApplication();
        $id  = $app->input->getInt('id', 0);

        if ($id > 0) {
            $getMedia = new Cwmmedia();
            $getMedia->hitPlay($id);
        }

        $app->getDocument()->setMimeEncoding('application/json');
        echo json_encode(['success' => $id > 0]);
        $app->close();
    }
}
