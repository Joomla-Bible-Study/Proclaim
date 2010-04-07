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
		
				$id = JRequest::getVar('id','GET','INT');
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
        if (!$id ){$id = JRequest::getInt('returnid','get');} //dump ($id, 'id: ');
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
