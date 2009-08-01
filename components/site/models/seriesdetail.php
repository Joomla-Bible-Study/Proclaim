<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');


class biblestudyModelseriesdetail extends JModel
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
		
				$id = JRequest::getVar('id', 0,'GET','INT');
		//end added from single view off of menu
		$array = JRequest::getVar('id',  0, '', 'array');
		$this->setId((int)$array[0]);
		
		 ////set the default view search path
        $this->addTablePath(JPATH_COMPONENT.DS.'tables');
        $params 			=& $mainframe->getPageParameters();
		$templatemenuid = $params->get('templatemenuid');
		if (!$templatemenuid){$templatemenuid = 1;}
		JRequest::setVar( 'templatemenuid', $templatemenuid, 'get');
		$this->_id = $id;
		$template = $this->getTemplate();
		$params = new JParameter($template[0]->params);
		
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
	
	function &getData()
	{
		// Load the data
		if (empty( $this->_data )) {
			$id = JRequest::getVar('id', 0,'GET','INT');
		
		$query = 'SELECT se.*, t.id AS tid, t.teachername, t.title AS teachertitle, t.thumb, t.thumbh, t.thumbw, t.teacher_thumbnail'
		. ' FROM #__bsms_series AS se'
		. ' LEFT JOIN #__bsms_teachers AS t ON (se.teacher = t.id)'
		. ' WHERE se.id = '.$id;
		
		/*$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername AS tname, #__bsms_teachers.title, '
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
			. '  WHERE #__bsms_studies.id = '.$id;*/
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
