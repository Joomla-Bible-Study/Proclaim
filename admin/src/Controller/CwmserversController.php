<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Response\JsonResponse;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Servers list controller class.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmserversController extends AdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Cwmserver', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to get the JSON-encoded amount of published articles
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function getQuickiconContent()
	{
		$model = $this->getModel('cwmservers');

		$model->setState('filter.published', 1);

		$amount = (int) $model->getTotal();

		$result = [];

		$result['amount'] = $amount;
		$result['sronly'] = Text::plural('COM_CONTENT_N_QUICKICON_SRONLY', $amount);
		$result['name'] = Text::plural('COM_CONTENT_N_QUICKICON', $amount);

		echo new JsonResponse($result);
	}
}
