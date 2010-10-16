<?php

 // Comments Model for Bible Study Component
 

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );


 
class biblestudyModelcommentslist extends JModel
{
	/**
	 *
	 * @var array
	 */
	var $_data;
	var $_total = null;
	var $_pagination = null;
	var $_allow_deletes = null;
	
function __construct()
	{
		parent::__construct();

		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');;

		// Get the pagination request variables
		$limit	   = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest( 'com_biblestudy&view=commentslist.limitstart', 'limitstart', 0, 'int' );
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}
	
function _buildQuery()
	{
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();
		$query = ' SELECT c.*, s.id AS sid, s.studytitle, s.booknumber AS bnumber, b.id AS bid, s.studydate, b.bookname, s.chapter_begin '
			. ' FROM #__bsms_comments AS c'
		. ' LEFT JOIN #__bsms_studies AS s ON (s.id = c.study_id)'
		. ' LEFT JOIN #__bsms_books AS b ON (b.booknumber = s.booknumber)'
		. $where
		. $orderby;
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

	/**
	 * Method to get a pagination object for the studies
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
	function _buildContentWhere()
	{
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');;
	$where = array();
	//$filter_order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'ordering',	'cmd' );
	//$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
	$filter_studyid		= $mainframe->getUserStateFromRequest( $option.'filter_studyid',		'filter_studyid',		0,				'int' );
	if ($filter_studyid > 0) {
			$where[] = 'c.study_id = '.(int) $filter_studyid;
		}
	$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		
	return $where;
	}
function _buildContentOrderBy()
	{
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');;
		$orders = array('c.id', 'published', 's.studytitle', 'c.comment_date');
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'published' );
		$filter_order_Dir	= strtoupper($mainframe->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'ASC') );
		if ($filter_order_Dir != 'ASC' && $filter_order_Dir != 'DESC') {$filter_order_Dir = 'ASC';}
		if (!in_array($filter_order, $orders)) { $filter_order = 'published';}
		return ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
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