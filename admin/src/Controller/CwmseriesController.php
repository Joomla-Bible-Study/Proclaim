<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CwmseriesModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Utilities\ArrayHelper;

/**
 * Series list controller class.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmseriesController extends AdminController
{
    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return    void
     *
     * @throws \Exception
     * @since   3.0
     */
    public function saveOrderAjax(): void
    {
        $pks   = $this->input->post->get('cid', array(), 'array');
        $order = $this->input->post->get('ordering', array(), 'array');

        // Sanitize the input
        ArrayHelper::toInteger($pks);
        ArrayHelper::toInteger($order);

        // Get the model
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
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
     *
     * @since   1.6
     */
    public function getModel($name = 'Cwmserie', $prefix = 'Administrator', $config = ['ignore_request' => true]): \Joomla\CMS\MVC\Model\BaseDatabaseModel
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to get the JSON-encoded number of published Series
     *
     * @return  void
     *
     * @since   10.0.0
     */
    public function getQuickIconSeries(): void
    {
        /** @var CwmseriesModel $model */
        $model = $this->getModel('cwmseries');

        $model->setState('filter.published', 1);

        $amount = (int) $model->getTotal();

        $result = [];

        $result['amount'] = $amount;
        $result['sronly'] = Text::plural('COM_PROCLAIM_N_QUICKICON_SERIES_SRONLY', $amount);
        $result['name']   = Text::plural('COM_PROCLAIM_N_QUICKICON_SERIES', $amount);

        echo new JsonResponse($result);
    }
}
