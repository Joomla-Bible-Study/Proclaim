<?php
/**
 * teacherlist Model for Bible Study Component
 
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );


class biblestudyModelteacherlist extends JModel
{
	/**
	 * teacherlist data array
	 *
	 * @var array
	 */
	var $_total = null;
	var $_pagination = null;
	var $_data;
	var $_template;

function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$params 			=& $mainframe->getPageParameters();
		JRequest::setVar( 'templatemenuid', $params->get('templatemenuid'), 'get');
		$template = $this->getTemplate();
		$params = new JParameter($template[0]->params);
		
		$this->setState('limit',$params->get('itemslimit'),'limit',$params->get('itemslimit'),'int');
		$this->setState('limitstart', JRequest::getVar('limitstart', 0, '', 'int'));
		// In case limit has been changed, adjust limitstart accordingly
		$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));
		// In case we are on more than page 1 of results and the total changes in one of the drop downs to a selection that has fewer in its total, we change limitstart
		if ($this->getTotal() < $this->getState('limitstart')) {$this->setState('limitstart', 0,'','int');}

	}
	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function _buildQuery()
	{
		$query = ' SELECT * '
			. ' FROM #__bsms_teachers WHERE #__bsms_teachers.published = 1 AND list_show = 1 ORDER BY ordering ASC'
		;

		return $query;
	}

	/**
	 * Retrieves the data
	 * @return array Array of objects containing the data from the database
	 */
	function getData()
	{
		// Lets load the data if it doesn't already exist
		//if (empty( $this->_data ))
		if (empty( $this->_data ))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_data;
	}

function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the teachers
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

function getTemplate() 
		{
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

}
?>