<?php
/**
 * Controller MessageForm
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\Controller;
use JLoader;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\Session\Session;
use Joomla\CMS\MVC\Model\ItemModel;
// No Direct Access
defined('_JEXEC') or die;

// Base this model on the backend version.
JLoader::register('CWMMessageController', JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Controller/CWMMessageController.php');

/**
 * Controller class for MessageForm
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class CWMMessageformController extends FormController
{
	/**
	 * View item
	 *
	 * @since    1.6
	 */
	protected $view_item = 'CWMMessageform';

	/**
	 * View list
	 *
	 * @since    1.6
	 */
	protected $view_list = 'CWMMessagelist';

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'com_proclaim';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerForm
	 * @since   12.2
	 * @throws  Exception
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory=null, $app=null, $input=null)
	{
		parent::__construct($config, $factory, $app, $input);


		// Register Extra tasks
		$this->registerTask('add', 'edit');
		$this->registerTask('upload', 'upload');
	}

	/**
	 * Method to add a new record.
	 *
	 * @return    boolean    True if the article can be added, false if not.
	 *
	 * @since    1.6
	 */
	public function add()
	{
		if (!parent::add())
		{
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());
		}
	}

	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return    string    The return URL.
	 *
	 * @since    1.6
	 */
	protected function getReturnPage()
	{
		$return = Factory::getApplication()->input->get('return', null, 'base64');

		if (empty($return) || !Uri::isInternal(base64_decode($return)))
		{
			return Uri::base() . 'index.php?option=com_proclaim&view=cwmmessagelist';
		}
		else
		{
			return base64_decode($return);
		}
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  Boolean    True if access level checks pass, false otherwise.
	 *
	 * @since    1.6
	 */
	public function cancel($key = 'a_id')
	{
		parent::cancel($key);

		// Redirect to the return page.
		$this->setRedirect($this->getReturnPage());
	}

	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The name of the model
	 * @param   string  $prefix  The prefix for the PHP class name
	 * @param   array   $config  Set ignore request
	 *
	 * @return \JModelForm
	 *
	 * @since 7.0
	 */
	public function &getModel($name = 'CWMMessageForm', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return    Boolean    True if access level check and checkout passes, false otherwise.
	 *
	 * @since    1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;

		// Since there is no asset tracking, fallback to the component permissions.
		if (!$recordId)
		{
			return parent::allowEdit($data, $key);
		}

		// Get the item.
		$item = $this->getModel()->getItem($recordId);

		// Since there is no item, return false.
		if (empty($item))
		{
			return false;
		}

		$user = $this->app->getIdentity();

		// Check if can edit own core.edit.own.
		$canEditOwn = $user->authorise('core.edit.own', $this->option . '.message.' ) && $item->created_by == $user->id;

		// Check the category core.edit permissions.
		return $canEditOwn || $user->authorise('core.edit', $this->option . '.message.');
	}
	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return    Boolean    True if successful, false otherwise.
	 *
	 * @since    1.6
	 */
	public function save($key = null, $urlVar = 'a_id')
	{
		$result = parent::save($key, $urlVar);

		return $result;
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowAdd($data = array())
	{
		// In the absence of better information, revert to the component permissions.
		return parent::allowAdd();
	}



}
