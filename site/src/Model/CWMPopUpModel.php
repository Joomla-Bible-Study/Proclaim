<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\Model;
// No Direct Access
defined('_JEXEC') or die;

use JApplicationSite;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use Joomla\Registry\Registry;

/**
 * Comment model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMPopUpModel extends ListModel
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		/** @type JApplicationSite $app */
		$app = Factory::getApplication('site');

		// Load the parameters.
		/** @var Registry $params */
		$params   = $app->getParams();
		$this->setState('params', $params);
		$template = CWMParams::getTemplateparams();
		$admin    = CWMParams::getAdmin();

		$template->params->merge($params);
		$template->params->merge($admin->params);

		$t = $params->get('popupid');

		if (!$t)
		{
			$input = Factory::getApplication()->input;
			$t     = $input->get('t', 1, 'int');
		}

		$template->id = $t;

		$this->setState('template', $template);
		$this->setState('administrator', $admin);

		$this->setState('layout', $app->input->get('layout'));
	}
}
