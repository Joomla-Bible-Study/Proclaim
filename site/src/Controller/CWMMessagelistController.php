<?php
/**
 * Controller Messages
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\CWMMessagelistController;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
// No Direct Access
defined('_JEXEC') or die;

// Base this model on the backend version.
JLoader::register('ProclaimControllerMessages', JPATH_ADMINISTRATOR . '/components/com_proclaim/controllers/MessagesController.php');

/**
 * Controller class for Messages
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class MessagelistController extends BaseController
{
	/**
	 * View item
	 *
	 * @since    1.6
	 */
	protected $view_item = 'messageform';

	/**
	 * View list
	 *
	 * @since    1.6
	 */
	protected $view_list = 'messagelist';

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_PROCLAIM';

	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The name of the model
	 * @param   string  $prefix  The prefix for the PHP class name
	 * @param   array   $config  Set ignore request
	 *
	 * @return JModelLegacy
	 *
	 * @since 7.0
	 */
	public function &getModel($name = 'Messagelist', $prefix = 'ProclaimModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
