<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');


class biblestudyModelstudydetails extends JModel
{
	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access	public
	 * @return	void
	 */
	function __construct()
	{
		parent::__construct();
		global $mainframe;
		//added for single study view off of menu
		$menu	=& JSite::getMenu();
		$item    = $menu->getActive();
		$params	=& $menu->getParams($item->id);
		$params2 =& $mainframe->getPageParameters();
		//$params =& JSiteHelper::getMenuParams();
		$id = $params->get('id', 0);
		if (!$id)
			{
				$id = JRequest::getVar('id', 0,'GET','INT');
			}
		$this->_id = $id;
		//end added from single view off of menu
		$array = JRequest::getVar('id',  0, '', 'array');
		$this->setId((int)$array[0]);
		
		 //set the default view search path
        $this->addTablePath(JPATH_COMPONENT.DS.'tables');
	if($params2->get('record_hits') == 1){
		$this->hit();
	}
	}
	
	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

/**
	 * Method to increment the hit counter for the study
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function hit()
	{
		/*global $mainframe;
		if ($this->_id)
		{
			$study = $this->getTable();
			$study->hit($this->_id);
			return true;
		}*/
		$db =& JFactory::getDBO();
		$db->setQuery('UPDATE '.$db->nameQuote('#__bsms_studies').'SET '.$db->nameQuote('hits').' = '.$db->nameQuote('hits').' + 1 '.' WHERE id = '.$this->_id);
		$db->query();
		return true;
//		return false;
	}
	function &getData()
	{
		// Load the data
		if (empty( $this->_data )) {
		$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername AS tname, '
			. ' #__bsms_teachers.image, #__bsms_teachers.imagew, #__bsms_teachers.imageh, #__bsms_teachers.thumb, #__bsms_teachers.thumbw, #__bsms_teachers.thumbh,'
			. ' #__bsms_series.id AS sid, #__bsms_series.series_text AS stext, #__bsms_message_type.id AS mid,'
			. ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname AS bname'
			. ' FROM #__bsms_studies'
			. ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
			. ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
			. ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
			. ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
			. '  WHERE #__bsms_studies.id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		return $this->_data;
	}

/*	*
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success*/
	 
	function storecomment()
	{
		$row =& $this->getTable('commentsedit');

		$data = JRequest::get( 'post' );

		// Bind the form fields to the table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the record is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		// Store the table to the database
		if (!$row->store()) {
			$this->setError( $row->getErrorMsg() );
			return false;
		}

		return true;
	}


	
	/**
	 * Method to delete record(s)
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	/*function delete()
	{
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$row =& $this->getTable();

		if (count( $cids ))
		{
			foreach($cids as $cid) {
				if (!$row->delete( $cid )) {
					$this->setError( $row->getErrorMsg() );
					return false;
				}
			}						
		}
		return true;
	}
function publish($cid = array(), $publish = 1)
	{
		
		if (count( $cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__bsms_studies'
				. ' SET published = ' . intval( $publish )
				. ' WHERE id IN ( '.$cids.' )'
				
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}		
	}*/			
//end class
}
?>
