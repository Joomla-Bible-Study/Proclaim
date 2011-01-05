<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

//Joomla 1.6 <-> 1.5 Branch
try {
	jimport('joomla.application.component.modeladmin');
	abstract class modelClass extends JModelAdmin{}
}catch(Exception $e){
	jimport('joomla.application.component.model');
	abstract class modelClass extends JModel{}
}

class biblestudyModeladmin extends modelClass {
//class biblestudyModeladmin extends JModelAdmin
//{
	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access	public
	 * @return	void
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	
	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}


	
	function &getData()
	{
		// Load the data
		$query = ' SELECT * FROM #__bsms_admin '.
					'  WHERE id = 1';
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		return $this->_data;
	}

	/**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store()
	{
		$row =& $this->getTable();

		$data = JRequest::get( 'post' );
		//dump ($data, 'post: ');
		// Bind the form fields to the hello table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the record is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		// Store the web link table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
//			$this->setError( $row->getErrorMsg() );
			return false;
		}

		return true;
	}

        /**
         *
         * @param <boolean> $data
         * @param <array> $loadData
         * @return <type>
         * @since   7.0
         */
	public function getForm($data = array(), $loadData = true)
	{
		$app	= JFactory::getApplication();
        // Get the form.
		$form = $this->loadForm('com_biblestudy.admin', 'admin', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}
    
    /**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_biblestudy.edit.admin.data', array());

		if (empty($data)) {
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('admin.id') == 0) {
				$app = JFactory::getApplication();
			//	$data->set('catid', JRequest::getInt('catid', $app->getUserState('com_biblestudy.admin.filter.category_id')));
			}
		}

		return $data;
	}
    
    /**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {
			// Convert the params field to an array.
			$registry = new JRegistry;
			$registry->loadJSON($item->params);
			$item->params = $registry->toArray();
		}

		return $item;
	}	
}
?>
