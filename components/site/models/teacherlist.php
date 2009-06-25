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
		if (empty( $this->_data ))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList( $query );
		}

		return $this->_data;
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