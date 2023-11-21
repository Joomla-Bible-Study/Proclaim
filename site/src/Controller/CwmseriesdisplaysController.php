<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
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
     * @return BaseDatabaseModel|boolean
     *
     * @since 7.0
     */
    public function &getModel($name = 'Cwmseriesdisplays', $prefix = 'Model', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
}
