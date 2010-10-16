<?php
/**
 * Mime Type Model for Bible Study Component
 
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );


class biblestudyModelmimetypelist extends JModel
{
	/**
	 * mime Type data array
	 *
	 * @var array
	 */
	var $_data;
	var $_total = null;
	var $_pagination = null;
	var $allow_deletes = null;

function __construct()
	{
		parent::__construct();

		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');;

		// Get the pagination request variables
		$limit	   = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 0);
		//$limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0 );
		$limitstart = $mainframe->getUserStateFromRequest( 'com_biblestudy&view=mimetypelist.limitstart', 'limitstart', 0, 'int' );

		
		//$this->setState('limit', $limit);
		//$this->setState('limitstart', $limitstart);
	}
	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function _buildQuery()
	{
		$query = ' SELECT * '
			. ' FROM #__bsms_mimetype '
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
			$this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit') );
		}
		/*$testview 	= JRequest::getVar( 'view' );
			if ($testview != 'mimetypelist') 
				{
					$limitstart = 0;
				}*/
			//$this->setState('limitstart', $limitstart);
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
function getDeletes()
	{
		if (empty($this->_deletes)) {
			$query = 'SELECT allow_deletes'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$this->_deletes = $this->_getList($query);
		}
		return $this->_deletes;
	}
}
?>