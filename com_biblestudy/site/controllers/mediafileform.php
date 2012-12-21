<?php

/**
 * Controller MediaFile
 *
 * @package BibleStudy.Site
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Controller class for MediaFile
 *
 * @package BibleStudy.Site
 * @since   7.0.0
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
	 * Method to add a new record.
	 *
	 * @return    boolean    True if the article can be added, false if not.
	 * @since    1.6
	 */
	public function add()
	{
		if (!parent::add()) {
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());
		}
	}

	/**
	 * constructor (registers additional tasks to methods)
	 *
	 * @return void
	 */
	function __construct($config = array())
	{
		$input = new JInput; 
		$input->set('a_id', $input->get('a_id', 0, 'int'));
		parent::__construct($config);

		// Register Extra tasks
		$this->registerTask('add', 'edit');
		$this->registerTask('upload', 'upload');
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
		// In the absense of better information, revert to the component permissions.
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
	 * Method to cancel an edit.
	 *
	 * @param    string    $key    The name of the primary key of the URL variable.
	 *
	 * @return    Boolean    True if access level checks pass, false otherwise.
	 * @since    1.6
	 */
	public function cancel($key = 'a_id')
	{
		parent::cancel($key);

		// Redirect to the return page.
		$this->setRedirect($this->getReturnPage());
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param    string    $key       The name of the primary key of the URL variable.
	 * @param    string    $urlVar    The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return    Boolean    True if access level check and checkout passes, false otherwise.
	 * @since    1.6
	 */
	public function edit($key = null, $urlVar = 'a_id')
	{
		$result = parent::edit($key, $urlVar);

		return $result;
	}

	/**
	 * Get Model
	 *
	 * @param string $name
	 * @param string $prefix
	 *
	 * @return array
	 */
	public function getModel($name = 'Mediafileform', $prefix = 'BiblestudyModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param    int        $recordId      The primary key id for the item.
	 * @param    string     $urlVar        The name of the URL variable for the id.
	 *
	 * @return    string    The arguments to append to the redirect URL.
	 * @since    1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'a_id')
	{
		$this->input = new JInput();

		// Need to override the parent method completely.
		$tmpl   = $this->input->get('tmpl');
		$layout = $this->input->get('layout', 'edit');
		$append = '';

		// Setup redirect info.
		if ($tmpl) {
			$append .= '&tmpl=' . $tmpl;
		}

		$append .= '&layout=edit';

		if ($recordId) {
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		$itemId = $this->input->getInt('Itemid');
		$return = $this->getReturnPage();
		$catId  = $this->input->getInt('catid', null, 'get');

		if ($itemId) {
			$append .= '&Itemid=' . $itemId;
		}

		if ($catId) {
			$append .= '&catid=' . $catId;
		}

		if ($return) {
			$append .= '&return=' . base64_encode($return);
		}

		return $append;
	}

	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return    string    The return URL.
	 * @since    1.6
	 */
	protected function getReturnPage()
	{
		$return = JFactory::getApplication()->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return))) {
			return JURI::base() . 'index.php?option=com_biblestudy&view=mediafilelist';
		} else {
			return base64_decode($return);
		}
	}

	/**
	 * Method to save a record.
	 *
	 * @param    string    $key       The name of the primary key of the URL variable.
	 * @param    string    $urlVar    The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return    Boolean    True if successful, false otherwise.
	 * @since    1.6
	 */
	public function save($key = null, $urlVar = 'a_id')
	{
		// Load the backend helper for filtering.
		//require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/biblestudy.php';
        JLoader::register('JBSMHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/biblestudy.php');
		$result = parent::save($key, $urlVar);

		// If ok, redirect to the return page.
		if ($result) {
			$this->setRedirect($this->getReturnPage());
		}

		return $result;
	}

	/**
	 * Link to Docman Category Items
	 *
	 * @todo This is brocken and not sure if needed.
	 */
	public function docmanCategoryItems()
	{
		//hide errors and warnings
		error_reporting(0);
		$input = new JInput;
		$catId = $input->get('catId', '', 'int');

		$model = $this->getModel('mediafile');
		$items = $model->getdocManCategoryItems($catId);
		echo $items;
	}

	/**
	 * Link to Sections May need to be Removed.
	 *
	 * @todo This is brocken and not sure if needed.
	 */
	public function articlesSectionCategories()
	{
		error_reporting(0);
		$input = new JInput;
		$secId = $input->get('secId', '', 'int');

		$model = & $this->getModel('mediafile');
		$items = & $model->getArticlesSectionCategories($secId);
		echo $items;
	}

	/**
	 * Link to Articals Category Items
	 *
	 * @todo This is brocken and not sure if needed.
	 */
	public function articlesCategoryItems()
	{
		$input = new JInput;
		error_reporting(0);
		$catId = $input->get('catId', '', 'int');

		$model = & $this->getModel('mediafile');
		$items = & $model->getCategoryItems($catId);
		echo $items;
	}

	/**
	 * Link to VertueMart Items
	 *
	 * @todo This is brocken and not sure if needed.
	 */
	public function virtueMartItems()
	{
		$input = new JInput;
		error_reporting(0);
		$catId = $input->get('catId', '', 'int');

		$model = & $this->getModel('mediafile');
		$items = & $model->getVirtueMartItems($catId);
		echo $items;
	}



}
