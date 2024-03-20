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

use CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel;
use CWM\Component\Proclaim\Administrator\Model\CwmmediafilesModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

/**
 * MediaFiles list controller class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmediafilesController extends AdminController
{
    /**
     * Check in of one or more records.
     *
     * @return  bool  True on success
     *
     * @throws \Exception
     * @since   12.2
     */
    public function checkin(): bool
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $ids = Factory::getApplication()->input->post->get('cid', array(), 'array');

        /** @var CwmmediafileModel $model */
        $model  = $this->getModel($name = 'Cwmmediafile', $prefix = 'Administrator', $config = ['ignore_request' => true]);
        $return = $model->checkin($ids);

        if ($return === false) {
            // Checkin failed.
            $message = Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
            $this->setRedirect(
                Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false),
                $message,
                'error'
            );

            return false;
        }

        // Checkin succeeded.
        $message = Text::plural($this->text_prefix . '_N_ITEMS_CHECKED_IN', count($ids));
        $this->setRedirect(
            Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false),
            $message
        );

        return true;
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return    void
     *
     * @throws \Exception
     * @since   3.0
     */
    public function saveOrderAjax()
    {
        // Get the input
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
