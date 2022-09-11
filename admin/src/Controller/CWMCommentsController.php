<?php
/**
 * Controller for Comments
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// No Direct Access
use Joomla\CMS\MVC\Controller\AdminController;

defined('_JEXEC') or die;

/**
 * Controller for Comments
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMCommentsController extends AdminController
{
	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return boolean|\Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since 7.0.0
	 */
	public function getModel($name = 'CWMComment', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
