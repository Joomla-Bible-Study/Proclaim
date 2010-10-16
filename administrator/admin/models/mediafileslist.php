<?php

 // mediafileslist Model for Bible Study Component
 

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );


 
class biblestudyModelmediafileslist extends JModel
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

		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');

		// Get the pagination request variables
		$limit	   = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest( 'com_biblestudy&view=mediafileslist.limitstart', 'limitstart', 0, 'int' );
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

	}
	
function _buildQuery()
	{
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();
		$query = ' SELECT m.*, s.id AS sid, s.studytitle, md.media_image_name, md.id AS mid'
        . ' FROM #__bsms_mediafiles AS m'
		. ' LEFT JOIN #__bsms_studies AS s ON (s.id = m.study_id)'
		. ' LEFT JOIN #__bsms_media AS md ON (md.id = m.media_image)'
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
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
	$where = array();
	$filter_studyid		= $mainframe->getUserStateFromRequest( $option.'filter_studyid',		'filter_studyid',		0,				'int' );
	if ($filter_studyid > 0) {
			$where[] = 'm.study_id = '.(int) $filter_studyid;
		}
	$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		
	return $where;
	}
function _buildContentOrderBy()
	{
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		$orders = array('id', 'published', 'studytitle', 'ordering','media_image_name', 'createdate', 'filename');
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'ordering',	'cmd' );
		$filter_order_Dir	= strtoupper($mainframe->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'ASC' ));
		if ($filter_order_Dir != 'ASC' && $filter_order_Dir != 'DESC') {$filter_order_Dir = 'ASC';}
		if (!in_array($filter_order, $orders)) { $filter_order = 'ordering';}

			if ($filter_order == 'ordering'){
			$orderby 	= ' ORDER BY study_id, ordering '.$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , study_id, ordering ';
		}
		return $orderby;
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