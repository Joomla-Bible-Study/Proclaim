<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Comment model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyModelPopup extends JModelLegacy
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
	protected function populateState()
	{
		/** @type JApplicationSite $app */
		$app = Factory::getApplication('site');

		// Load the parameters.
		/** @var Registry $params */
		$params   = $app->getParams();
		$this->setState('params', $params);
		$template = JBSMParams::getTemplateparams();
		$admin    = JBSMParams::getAdmin();

		$template->params->merge($params);
		$template->params->merge($admin->params);

		$t = $params->get('popupid');

		if (!$t)
		{
			$input = new JInput;
			$t     = $input->get('t', 1, 'int');
		}

		$template->id = $t;

		$this->setState('template', $template);
		$this->setState('administrator', $admin);

		$this->setState('layout', $app->input->get('layout'));
	}
}
