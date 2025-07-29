<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

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
     * @return BaseDatabaseModel|bool
     *
     * @since 7.0
     */
    public function &getModel($name = 'Cwmseriesdisplays', $prefix = 'Model', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
}
