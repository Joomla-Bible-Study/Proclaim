<?php
/**
 * Controller Messages
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\Controller;
use JLoader;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Model\ListModel;
use CWM\Component\Proclaim\Administrator\Model\CWMMessagesModel;

// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller class for Messages
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class CWMMessagelistController extends BaseController
{
	/**
	 * @var string View item
	 *
	 * @since    1.6
	 */
	protected $view_item = 'messageform';

	/**
	 * @var string View List
	 *
	 * @since    1.6
	 */
	protected string $view_list = 'messagelist';

	/**
	 * @var string The prefix to use with controller messages.
	 *
	 * @since    1.6
	 */
	protected string $text_prefix = 'COM_PROCLAIM';

	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The name of the model
	 * @param   string  $prefix  The prefix for the PHP class name
	 * @param   array   $config  Set ignore request
	 *
	 * @return boolean|\Joomla\CMS\MVC\Model\BaseDatabaseModel|\Joomla\CMS\MVC\Model\ListModel
	 *
	 * @since 7.0
	 */
	public function &getModel($name = 'Model', $prefix = 'CWMMessages', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
