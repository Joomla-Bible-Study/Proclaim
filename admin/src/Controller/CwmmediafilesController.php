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
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * MediaFiles list controller class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmediafilesController extends AdminController
{
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
