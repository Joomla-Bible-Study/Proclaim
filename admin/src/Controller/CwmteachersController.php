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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;

/**
 * Teachers list controller class.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmteachersController extends AdminController
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
        $order = $this->input->post->get('order', array(), 'array');

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
}
