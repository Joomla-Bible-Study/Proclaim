<?php
/**
 * Controller MediaFile
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller class for MediaFile
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyControllerMediafileform extends JControllerForm
{
	/**
	 * View item
	 *
	 * @since    1.6
	 */
	protected $view_item = 'mediafileform';

	/**
	 * View list
	 *
	 * @since    1.6
	 */
	protected $view_list = 'mediafilelist';

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_BIBLESTUDY';

	/**
	 * Handles XHR requests (i.e. File uploads)
	 *
	 * @return void
	 *
	 * @throws  Exception
	 * @since   9.0.0
	 */
	public function xhr()
	{
		JSession::checkToken('get') or die('Invalid Token');

		$addonType = $this->input->get('type', 'Legacy', 'string');
		$handler   = $this->input->get('handler');

		// Load the addon
		$addon = JBSMAddon::getInstance($addonType);

		if (method_exists($addon, $handler))
		{
			echo json_encode($addon->$handler($this->input));

			$app = JFactory::getApplication();
			$app->close();
		}
		else
		{
			throw new Exception(JText::sprintf('Handler: "' . $handler . '" does not exist!'), 404);
		}
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
		$app = JFactory::getApplication();

		if (!parent::add())
		{
			$app->setUserState('com_biblestudy.edit.mediafile.createdate', null);
			$app->setUserState('com_biblestudy.edit.mediafile.study_id', null);
			$app->setUserState('com_biblestudy.edit.mediafile.server_id', null);

			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());
		}

		return false;
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
		$return = JFactory::getApplication()->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return)))
		{
			return JUri::base() . 'index.php?option=com_biblestudy&view=mediafilelist';
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

		if ($this->input->getCmd('return') && parent::cancel($key))
		{
			$this->setRedirect(base64_decode($this->input->getCmd('return')));

			return true;
		}

		return false;
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
	public function edit($key = null, $urlVar = 'a_id')
	{
		$app    = JFactory::getApplication();
		$result = parent::edit($key, $urlVar);

		if ($result)
		{
			$app->setUserState('com_biblestudy.edit.mediafile.createdate', null);
			$app->setUserState('com_biblestudy.edit.mediafile.study_id', null);
			$app->setUserState('com_biblestudy.edit.mediafile.server_id', null);
		}

		return $result;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   12.2
	 */
	public function getModel(
		$name = 'Mediafileform',
		$prefix = 'BiblestudyModel',
		$config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
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
		$input = JFactory::getApplication()->input;
		$input->set('a_id', $input->get('id'));
		$result = parent::save($key, $urlVar);

		return $result;
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return    void
	 *
	 * @since    3.1
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		$return = $this->input->getCmd('return');
		$task   = $this->input->get('task');

		if ($return && $task != 'apply')
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JBS_MED_SAVE'), 'message');
			$this->setRedirect(base64_decode($return));
		}

		return;
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

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowEdit($data = array(), $key = 'a_id')
	{
		return true;
	}

	/**
	 * Sets the server for this media record
	 *
	 * @return  void
	 *
	 * @since   9.0.0
	 */
	public function setServer()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		$data = $input->get('jform', array(), 'post');
		$cdate = $data['createdate'];
		$study_id = $data['study_id'];
		$server_id = $data['server_id'];

		// Save server in the session
		$app->setUserState('com_biblestudy.edit.mediafile.createdate', $cdate);
		$app->setUserState('com_biblestudy.edit.mediafile.study_id', $study_id);
		$app->setUserState('com_biblestudy.edit.mediafile.server_id', $server_id);

		$redirect = $this->getRedirectToItemAppend($data['id']);
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $redirect, false));
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   12.2
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'a_id')
	{
		$tmpl    = $this->input->get('tmpl');
		$layout  = $this->input->get('layout', 'edit', 'string');
		$options = $this->input->get('options');
		$return  = $this->input->getCmd('return');
		$append  = '';

		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		if ($layout)
		{
			$append .= '&layout=' . $layout;
		}

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		if ($return)
		{
			$append .= '&return=' . $return;
		}

		if ($options)
		{
			$append .= '&options=' . $options;
		}

		return $append;
	}
}
