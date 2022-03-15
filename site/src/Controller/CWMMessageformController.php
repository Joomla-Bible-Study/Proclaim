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

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use JLoader;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

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
	 * @var string View item
	 *
	 * @since    1.6
	 */
	protected $view_item = 'CWMMessageForm';

	/**
	 * @var string View list
	 *
	 * @since    1.6
	 */
	protected $view_list = 'CWMMessageList';

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'com_proclaim';

	/**
	 * Constructor.
	 *
	 * @param   array                                        $config   An optional associative array of configuration settings.
	 *                                                                 Recognized key values include 'name', 'default_task', 'model_path', and
	 *                                                                 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface|null                     $factory  The factory.
	 * @param   \Joomla\CMS\Application\CMSApplication|null  $app      The Application for the dispatcher
	 * @param   \Joomla\Input\Input|null                     $input    Input
	 *
	 * @throws \Exception
	 * @see     JControllerForm
	 * @since   12.2
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		// This seems to fix all needs for ID usage
		$input->set('id', $input->getInt('a_id'));

		// Register Extra tasks
		$this->registerTask('add', 'edit');
		$this->registerTask('upload', 'upload');
	}

	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return    string    The return URL.
	 *
	 * @throws \Exception
	 * @since    1.6
	 */
	protected function getReturnPage()
	{
		$return = $this->input->get('return', null, 'base64');

		if (empty($return) || !Uri::isInternal(base64_decode($return)))
		{
			return Uri::base() . 'index.php?option=com_proclaim&view=cwmmessagelist';
		}

		return base64_decode($return);
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean    True if access level checks pass, false otherwise.
	 *
	 * @throws \Exception
	 * @since    1.6
	 */
	public function cancel($key = null)
	{
		$result = parent::cancel($key);

		// Redirect to the return page.
		$this->setRedirect(Route::_($this->getReturnPage(), false));

		return $result;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return BaseDatabaseModel The model.
	 *
	 * @since 7.0
	 */
	public function getModel($name = 'CWMMessageForm', $prefix = '', $config = array('ignore_request' => true)): BaseDatabaseModel
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since    1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;

		// Since there is no asset tracking, fallback to the component permissions.
		if (!$recordId)
		{
			return false;
		}

		// Need to do a lookup from the model.
		$record     = $this->getModel()->getItem($recordId);
		$series_Id = (int) $record->series_id;

		if ($series_Id)
		{
			$user = $this->app->getIdentity();

			// The category has been set. Check the category permissions.
			if ($user->authorise('core.edit', $this->option . '.series.' . $series_Id))
			{
				return true;
			}

			// Fallback on edit.own.
			if ($user->authorise('core.edit.own', $this->option . '.series.' . $series_Id))
			{
				return ($record->created_by === $user->id);
			}

			return false;
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}

	/**
	 * Method overrides to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowAdd($data = array()): bool
	{
		if ($study_Id = ArrayHelper::getValue($data, 'study_id', $this->input->getInt('study_id'), 'int'))
		{
			// If the category has been passed in the data or URL check it.
			return $this->app->getIdentity()->authorise('core.create', 'com_contact.study.' . $study_Id);
		}

		// In the absence of better information, revert to the component permissions.
		return parent::allowAdd();
	}
}
