<?php
/**
 * Controller MessageForm
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Base this model on the backend version.
JLoader::register('BiblestudyControllerMessage', JPATH_ADMINISTRATOR . '/components/com_biblestudy/controllers/message.php');

/**
 * Controller class for MessageForm
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyControllerMessageform extends BiblestudyControllerMessage
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
	protected $text_prefix = 'COM_BIBLESTUDY';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerForm
	 * @since   12.2
	 * @throws  Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

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
		$return = JFactory::getApplication()->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return)))
		{
			return JUri::base() . 'index.php?option=com_biblestudy&view=messagelist';
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
	 * @return JModel
	 *
	 * @since 7.0
	 */
	public function &getModel($name = 'MessageForm', $prefix = 'BiblestudyModel', $config = array('ignore_request' => true))
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
	public function edit($key = null, $urlVar = 'a_id')
	{
		$result = parent::edit($key, $urlVar);

		return $result;
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
}
