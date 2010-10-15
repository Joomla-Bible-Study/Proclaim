<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');


class biblestudyModelteacherdisplay extends JModel
{
	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access	public
	 * @return	void
	 */
	 var $_data;
	 var $_template;
	 var $_admin;
	function __construct()
	{
		parent::__construct();
		//added for single study view off of menu
		$menu	=& JSite::getMenu();
		$item    = $menu->getActive();
		if ($item) 
		{
			$params	=& $menu->getParams($item->id);
			$id = $params->get('id', 0);
		}
		else
			{
				$id = JRequest::getVar('id','GET','INT'); //dump($id, 'id: ');
                
			}
            
		$this->_id = $id;
		//end added from single view off of menu
		$array = JRequest::getVar('id',  0, '', 'array');
		$mainframe =& JFactory::getApplication();, $option;
		$params 			=& $mainframe->getPageParameters();
		$templatemenuid = $params->get('templatemenuid');
		if (!$templatemenuid){$templatemenuid = 1;}
		JRequest::setVar( 'templatemenuid', $templatemenuid, 'get');
		//JRequest::setVar( 'templatemenuid', $params->get('templatemenuid'), 'get');
		$template = $this->getTemplate();
		$params = new JParameter($template[0]->params);
		$this->setId((int)$array[0]);
	}

	
	function setId($id)
	{
		// Set id and wipe data
        if (!$id ){$id = JRequest::getInt('returnid','get');}
		$this->_id		= $id;
		$this->_data	= null;
	}


	
	function &getData()
	{
		// Load the data
		if (empty( $this->_data )) {
		$query = 'SELECT t.* FROM #__bsms_teachers AS t WHERE t.published = 1 AND t.id = '.$this->_id.' ORDER BY t.teachername ASC';
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		return $this->_data;
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
			$query = 'SELECT params'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$this->_admin = $this->_getList($query);
		}
		return $this->_admin;
	}
	
//end class
}
?>