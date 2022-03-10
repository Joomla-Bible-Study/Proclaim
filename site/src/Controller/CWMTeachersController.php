<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\CWMTeachersController;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller for Teachers
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class CWMTeachersController extends BaseController
{
	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The name of the model
	 * @param   string  $prefix  The prefix for the PHP class name
	 * @param   array   $config  Set ignore request
	 *
	 * @return $model
	 *
	 * @since 7.0
	 */
	public function &getModel($name = 'Teacher', $prefix = 'ProclaimModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
