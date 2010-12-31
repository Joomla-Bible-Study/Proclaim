<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');
jimport('joomla.application.component.modeladmin');

if(class_exists('JModelAdmin')) {
abstract class modelClass extends JModelAdmin{}
}else{
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

	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_biblestudy.admin', 'admin', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}	
}
?>
