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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since    1.6
	 */
	protected function populateState($ordering = null, $direction = null): void
	{
		/** @type JApplicationSite $app */
		$app = Factory::getApplication();

		// Load the parameters
		/** @var Registry $params */
		$params   = $app->getParams();
		$this->setState('params', $params);
		$template = CWMParams::getTemplateparams();
		$admin    = CWMParams::getAdmin();

		$template->params->merge($params);
		$template->params->merge($admin->params);

		$t = (int) $params->get('popupid');

		if (!$t)
		{
			$t     = $app->input->get('t', 1, 'int');
		}

		$template->id = $t;

		$this->setState('template', $template);
		$this->setState('administrator', $admin);

		$this->setState('layout', $app->input->get('layout'));
	}
}
