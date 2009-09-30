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
	 var $_template;
	 var $_admin;
	function __construct()
	{
		parent::__construct();
		global $mainframe;
		//added for single study view off of menu
		//$menu	=& JSite::getMenu();
		//$item    = $menu->getActive();
		//$params	=& $menu->getParams($item->id);
		//$params2 =& $mainframe->getPageParameters();
		//$id = $params2->get('id');
		//$params =& JSiteHelper::getMenuParams();
		//$id = $params->get('id', 0);
		//dump ($id, 'id: ');
		//if (!$id)
		//	{
				$id = JRequest::getVar('id', 0,'GET','INT');
				//dump ($id, 'id: ');
			//}
		
		//end added from single view off of menu
		$array = JRequest::getVar('id',  0, '', 'array');
		$this->setId((int)$array[0]);
		//$params 			=& $mainframe->getPageParameters();
		
		 ////set the default view search path
        $this->addTablePath(JPATH_COMPONENT.DS.'tables');
        $params 			=& $mainframe->getPageParameters();
		$templatemenuid = $params->get('templatemenuid');
		if (!$templatemenuid){$templatemenuid = 1;}
		JRequest::setVar( 'templatemenuid', $templatemenuid, 'get');
		
		//JRequest::setVar( 'templatemenuid', $params->get('templatemenuid'), 'get');
		//JRequest::setVar('id', $params->get('id'), 'get');
		$this->_id = $id;
		$template = $this->getTemplate();
		$params = new JParameter($template[0]->params);
		//dump ($params, 'params: ');
	//if($params2->get('record_hits') == 1){
		$this->hit();
	//}
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
			$id = JRequest::getVar('id', 0,'GET','INT');
		$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername AS teachername, #__bsms_teachers.title, '
			. ' #__bsms_teachers.image, #__bsms_teachers.imagew, #__bsms_teachers.imageh, #__bsms_teachers.thumb, #__bsms_teachers.thumbw, #__bsms_teachers.thumbh,'
			. ' #__bsms_series.id AS sid, #__bsms_series.series_text AS stext, #__bsms_message_type.id AS mid,'
			. ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname AS bname, #__bsms_locations.id as lid, #__bsms_locations.location_text,'
			. ' #__bsms_topics.id AS tpid, #__bsms_topics.topic_text'
			. ' FROM #__bsms_studies'
			. ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
			. ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
			. ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
			. ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
			. ' LEFT JOIN #__bsms_locations ON (#__bsms_studies.location_id = #__bsms_locations.id)'
			. ' LEFT JOIN #__bsms_topics ON (#__bsms_studies.topics_id = #__bsms_topics.id)'
			. '  WHERE #__bsms_studies.id = '.$id;
			//.$this->_id.;
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
		$data['comment_text'] = JRequest::getVar( 'comment_text', '', 'post', 'string', JREQUEST_ALLOWRAW );
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

function getTemplate() {
		if(empty($this->_template)) {
			$templateid = JRequest::getVar('templatemenuid',1,'get', 'int');
			//dump ($templateid, 'templateid: ');
			$query = 'SELECT *'
			. ' FROM #__bsms_templates'
			. ' WHERE published = 1 AND id = '.$templateid;
			$this->_template = $this->_getList($query);
			//dump ($this->_template, 'this->_template');
		}
		return $this->_template;
	}
 function getAdmin()
	{
		if (empty($this->_admin)) {
			$query = 'SELECT *'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$this->_admin = $this->_getList($query);
		}
		return $this->_admin;
	}	
	
//end class
}
?>
