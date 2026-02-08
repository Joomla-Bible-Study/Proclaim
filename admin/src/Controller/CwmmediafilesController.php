<?php

/**
 * Part of Proclaim Package
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
use CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

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
     * @since   7.0.0
     */
    public function checkin(): bool
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmmediafiles', Text::_('JINVALID_TOKEN'), 'error');

            return false;
        }

        $ids = Factory::getApplication()->getInput()->post->get('cid', [], 'array');

        /** @var CwmmediafileModel $model */
        $model  = $this->getModel($name = 'Cwmmediafile', $prefix = 'Administrator', $config = ['ignore_request' => true]);
        $return = $model->checkin($ids);

        if ($return === false) {
            // Checkin failed.
            $message = Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', '');
            $this->setRedirect(
                Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false),
                $message,
                'error'
            );

            return false;
        }

        // Checkin succeeded.
        $message = Text::plural($this->text_prefix . '_N_ITEMS_CHECKED_IN', \count($ids));
        $this->setRedirect(
            Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false),
            $message
        );

        return true;
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
    public function getModel($name = 'Cwmmediafile', $prefix = 'Administrator', $config = ['ignore_request' => true]): BaseDatabaseModel
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to get the JSON-encoded counts for Media Files
     *
     * @return  void
     *
     * @since   10.0.0
     */
    public function getQuickIconMediaFiles(): void
    {
        CwmcountHelper::sendQuickIconResponse('#__bsms_mediafiles', 'COM_PROCLAIM_N_QUICKICON_MEDIAFILES');
    }
}
