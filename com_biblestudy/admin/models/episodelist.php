<?php

/**
 * @version     $Id: episodelist.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.modellist');

    abstract class modelClass extends JModelList {

    }

class biblestudyModelepisodelist extends modelClass
{
	var $_data;
	var $_total = null;
	var $_pagination = null;
	function __construct()
	{
		parent::__construct();

		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');

		// Get the pagination request variables
		$limit	   = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 0);
		//$limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0 );
		$limitstart = $mainframe->getUserStateFromRequest( 'com_biblestudy&view=episodelist.limitstart', 'limitstart', 0, 'int' );

$testview 	= JRequest::getVar( 'view' );
			if ($testview != 'episodelist') 
				{
					$limitstart = 0;
				}
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}


	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function _buildQuery()
	{
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();
		$query = 'SELECT mf.*, s.id AS sid, p.id AS pid, p.title AS ptitle, b.bookname AS bname, b.booknumber AS bnumber, m.id AS mid, m.mimetext AS mtext, s.studytitle AS stitle,'
		. ' s.chapter_begin, s.verse_begin, s.chapter_end, s.verse_end, s.studydate'
		. ' FROM #__bsms_mediafiles AS mf'
		. ' LEFT JOIN #__bsms_studies AS s ON (mf.study_id = s.id)'
		. ' LEFT JOIN #__bsms_podcast AS p ON (mf.podcast_id = p.id)'
		. ' LEFT JOIN #__bsms_mimetype AS m ON (mf.mime_type = m.id)'
		. ' LEFT JOIN #__bsms_books AS b ON (s.booknumber = b.booknumber)'
		. $where
		. $orderby
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
		
			//$this->setState('limitstart', $limitstart);
		return $this->_data;
	}
/**
	 * Method to get the total number of episode items
	 *
	 * @access public
	 * @return integer
	 */
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
	 * Method to get a pagination object for the episode
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

		$filter_podcast		= $mainframe->getUserStateFromRequest( $option.'filter_podcast',		'filter_podcast',		0,				'int' );
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'DESC',				'word' );
		$filter_study		= $mainframe->getUserStateFromRequest( $option.'filter_study', 'filter_study', 'DESC', 'int' );
		$where = array();

	if ($filter_study > 0) {
			$where[] = ' mf.study_id = '.(int) $filter_study;
			}
			else {
			$where[] = ' mf.study_id > 0';
			}
			
	if ($filter_podcast > 0) {
			$where[] = ' mf.podcast_id = '.(int) $filter_podcast;
			
		}
	else {
			$where[] = ' (mf.podcast_id >0 AND mf.published = 1)';
			
		} 

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		
		return $where;
	}
function _buildContentOrderBy()
	{
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'DESC',	'word' );
		if (!$filter_order){
			$orderby	= ' ORDER BY mf.createdate DESC, mf.ordering ASC '; }
		if ($filter_order == 'ASC'){
			$orderby 	= ' ORDER BY mf.createdate ASC, mf.ordering ASC ';
		} else {
			$orderby 	= ' ORDER BY mf.createdate DESC, mf.ordering ASC ';
		}
		//$orderby 	= ' ORDER BY p.title ASC ';
		return $orderby;
	}
}
?>