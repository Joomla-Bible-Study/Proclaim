<?php

/**
 * Controller for Locations
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

use CWM\Component\Proclaim\Administrator\Helper\CwmcountHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Utilities\ArrayHelper;

/**
 * Locations list controller class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmlocationsController extends AdminController
{
    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return    void
     *
     * @throws \Exception
     * @since   7.0.0
     */
    public function saveOrderAjax(): void
    {
        // Get the input
        $pks   = $this->getInput()->post->get('cid', [], 'array');
        $order = $this->getInput()->post->get('order', [], 'array');

        // Sanitize the input
        ArrayHelper::toInteger($pks);
        ArrayHelper::toInteger($order);

        $model = $this->getModel();

        // Save the ordering
        $return = $model->saveorder($pks, $order);

        if ($return) {
            echo "1";
        }

        // Close the application
        Factory::getApplication()->close();
    }

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  The array of possible config values. Optional.
     *
     * @return  BaseDatabaseModel
     *
     * @since   1.6
     */
    public function getModel($name = 'Cwmlocation', $prefix = 'Administrator', $config = ['ignore_request' => true]): BaseDatabaseModel
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to get the JSON-encoded counts for Locations
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function getQuickIconLocations(): void
    {
        CwmcountHelper::sendQuickIconResponse('#__bsms_locations', 'COM_PROCLAIM_N_QUICKICON_LOCATIONS');
    }
}
