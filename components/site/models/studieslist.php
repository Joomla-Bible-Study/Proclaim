<?php

 // studieslist Model for Bible Study Component
 

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
global $mainframe, $option;
jimport( 'joomla.application.component.model' );

$params = &JComponentHelper::getParams($option);
$default_order = $params->get('default_order');
 
class biblestudyModelstudieslist extends JModel
{
	var $_data;
	var $_total = null;
	var $_pagination = null;
	//var $_limit = null;
	
	function __construct()
	{
		parent::__construct();

		global $mainframe, $option;
		$config = JFactory::getConfig();
		// Get the pagination request variables
		$this->setState('limit', $mainframe->getUserStateFromRequest('com_biblestudy.limit', 'limit', $config->getValue('config.list_limit'), 'int'));
		$this->setState('limitstart', JRequest::getVar('limitstart', 0, '', 'int'));

		// In case limit has been changed, adjust limitstart accordingly
		$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));
		
		// Get the pagination request variables
		//$limit	   = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		//$limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );
		//$limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0 );
		// In case limit has been changed, adjust it
		//$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
		//$this->setState('limit', $limit);
		//$this->setState('limitstart', $limitstart);
	}

	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function _buildQuery()
	{
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();
		$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, #__bsms_teachers.title AS teachertitle,'
			. ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_message_type.id AS mid,'
			. ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
			. ' #__bsms_topics.id AS tp_id, #__bsms_topics.topic_text'
			. ' FROM #__bsms_studies'
			. ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
			. ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
			. ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
			. ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
			. '	LEFT JOIN #__bsms_topics ON (#__bsms_studies.topics_id = #__bsms_topics.id)'
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

		return $this->_data;
	}
/**
	 * Method to get the total number of studies items
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
			
			/*jimport('joomla.html.pagination');
			$total = $this->getTotal();
			$limitstart = $this->getState('limitstart');
			$limit = $this->getState('limit');
			$this->_pagination = new JPagination( $total, $limitstart, $limit );*/
			
		}

		return $this->_pagination;
	}
function _buildContentWhere()
	{
		global $mainframe, $option;
		$params = &JComponentHelper::getParams($option);
		$default_order = $params->get('default_order');
		$filter_topic		= $mainframe->getUserStateFromRequest( $option.'filter_topic',		'filter_topic',		0,				'int' );
		$filter_book		= $mainframe->getUserStateFromRequest( $option.'filter_book',		'filter_book',		0,				'int' );
		$filter_teacher		= $mainframe->getUserStateFromRequest( $option.'filter_teacher',	'filter_teacher',		0,				'int' );
		$filter_series		= $mainframe->getUserStateFromRequest( $option.'filter_series',		'filter_series',		0,				'int' );
		$filter_messagetype	= $mainframe->getUserStateFromRequest( $option.'filter_messagetype','filter_messagetype',		0,				'int' );
		$filter_year		= $mainframe->getUserStateFromRequest( $option.'filter_year',		'filter_year',		0,				'int' );
		//$filter_order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		$default_order,				'word' );
		//$search				= $mainframe->getUserStateFromRequest( $option.'search',			'search',			'',				'string' );
		//$search				= JString::strtolower( $search );
		//$filter_searchby	= $mainframe->getUserStateFromRequest( $option.'filter_searchby','filter_searchby','','word' );
		//$filter_ordering		= $mainframe->getUserStateFromRequest( $option.'.filter_ordering',					'filter_ordering',		'c.ordering',	'cmd' );
		$filter_orders		= $mainframe->getUserStateFromRequest( $option.'filter_orders',		'filter_orders',		'DESC',				'word' );


		$where = array();
		$rightnow = date('Y-m-d H:i:s');
		$where[] = ' #__bsms_studies.published = 1';
		$where[] = " date_format(#__bsms_studies.studydate, '%Y-%m-%d %T') <= ".(int)$rightnow;
		
		if ($filter_topic > 0) {
			$where[] = ' #__bsms_studies.topics_id = '.(int) $filter_topic;
		}
		if ($filter_book > 0) {
			$where[] = ' #__bsms_studies.booknumber = '.(int) $filter_book;
		}
		if ($filter_teacher > 0) {
			$where[] = ' #__bsms_studies.teacher_id = '.(int) $filter_teacher;
		}
		if ($filter_series > 0) {
			$where[] = ' #__bsms_studies.series_id = '.(int) $filter_series;
		}
		if ($filter_messagetype > 0) {
			$where[] = ' #__bsms_studies.messagetype = '.(int) $filter_messagetype;
		}
		if ($filter_year > 0) {
			$where[] = " date_format(#__bsms_studies.studydate, '%Y')= ".(int) $filter_year;
		}
		//if ($search) {
			//$where[] = 'LOWER(#__bsms_studies.' .$filter_searchby.') LIKE '.$this->_db->Quote('%'.$search.'%');
		//}

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		
		return $where;
	}
function _buildContentOrderBy()
	{
		global $mainframe, $option;

		$filter_orders		= $mainframe->getUserStateFromRequest( $option.'filter_orders',		'filter_orders',		'DESC',	'word' );

		if ($filter_orders == 'ASC'){
			$orderby 	= ' ORDER BY studydate ASC ';
		} else {
			$orderby 	= ' ORDER BY studydate DESC ';
		}

		return $orderby;
	}
}
?>