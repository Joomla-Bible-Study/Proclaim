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

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use CWM\Component\Proclaim\Site\Helper\CwmseriesdisplaysHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Session\Session;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for Series Displays
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmseriesdisplaysController extends BaseController
{
    /**
     * Proxy for getModel
     *
     * @param   string  $name    The name of the model
     * @param   string  $prefix  The prefix for the PHP class name
     * @param   array   $config  Set ignore request
     *
     * @return  BaseDatabaseModel
     *
     * @since 7.0
     */
    public function getModel($name = 'Cwmseriesdisplays', $prefix = 'Model', $config = ['ignore_request' => true]): BaseDatabaseModel
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * AJAX endpoint for series list pagination.
     *
     * Returns rendered HTML fragment and pagination data for Load More / Infinite Scroll.
     *
     * URL: index.php?option=com_proclaim&task=cwmseriesdisplays.paginateAjax&format=raw&{token}=1
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function paginateAjax(): void
    {
        $app = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => 'Invalid token'], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            /** @var \CWM\Component\Proclaim\Site\Model\CwmseriesdisplaysModel $model */
            $model = $this->getModel('Cwmseriesdisplays', 'Site');

            $state    = $model->getState();
            $template = $state->get('template');
            $params   = $state->get('params');

            $items      = $model->getItems();
            $pagination = $model->getPagination();

            // Prepare items the same way the HtmlView does
            $items = CwmseriesdisplaysHelper::prepareItems($items, $params, $template);

            // Render using the same listing helper
            $listing = new Cwmlisting();
            $html    = '';

            if ($items) {
                $html = $listing->getFluidListing($items, $params, $template, 'seriesdisplays');
            }

            echo json_encode([
                'success'    => true,
                'html'       => $html,
                'total'      => $pagination->total,
                'pagesTotal' => $pagination->pagesTotal,
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to load results',
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }
}
