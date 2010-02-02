<?php

 // studieslist Model for Bible Study Component
 

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );


 
class biblestudyModelstudieslist extends JModel
{
	/**
	 *
	 * @var array
	 */
	var $_data;
	var $_total = null;
	var $_pagination = null;
	var $_files = null;
	
function __construct()
	{
		parent::__construct();

		global $mainframe, $option;

		// Get the pagination request variables
		$limit	   = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		//$limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0);
		$limitstart = $mainframe->getUserStateFromRequest( 'com_biblestudy&view=studieslist.limitstart', 'limitstart', 0, 'int' );

		//$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
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
        //dump ($this->test());
  		/*
		$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername,'
			. ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_message_type.id AS mid,'
			. ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
			. ' #__bsms_topics.id AS tp_id, #__bsms_topics.topic_text'
			. ' FROM #__bsms_studies'
			. ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
			. ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
			. ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
			. ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
			. ' LEFT JOIN #__bsms_topics ON (#__bsms_studies.topics_id = #__bsms_topics.id)'
			. $where
			. $orderby
			;
	    */
	    $query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername,'
			  . ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_message_type.id AS mid,'
			  . ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
			  . ' group_concat(#__bsms_topics.id separator ", ") AS tp_id, group_concat(#__bsms_topics.topic_text separator ", ") as topic_text'
			  . ' FROM #__bsms_studies'
			  . ' left join #__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)'
			  . ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
			  . ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
			  . ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
			  . ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
			  . ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)'
			  . $where
			  . ' GROUP BY #__bsms_studies.id'
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
	* Creates and executes a new query that retrieves the medifile information from the mediafiles table. 
	* It then adds to the dataObject the mediafiles associated with the sermon.
	* @return unknown_type
	*/
	/* Tom commented this out because it caused the query to fail - needs work. */
	function getFiles() {
		$mediaFiles = null;
		$db =& JFactory::getDBO();
		$i=0;
		foreach($this->_data as $sermon) {
			$i++;
			$sermon_id = $sermon->id;
			$query = 'SELECT study_id, filename, #__bsms_folders.folderpath, #__bsms_servers.server_path'
				. ' FROM #__bsms_mediafiles'
				. ' LEFT JOIN #__bsms_servers ON (#__bsms_mediafiles.server = #__bsms_servers.id)'
				. ' LEFT JOIN #__bsms_folders ON (#__bsms_mediafiles.path = #__bsms_folders.id)'
				. ' WHERE `study_id` ='
				.$sermon_id
			;
			$db->setQuery($query);
			//var_dump ($db->loadAssocList()).'<hr>';
			//$files = array($i => $db->loadAssocList($sermon->id));
			//var_dump ($files);
			//var_dump ($db->loadAssocList());
			$mediaFiles[$sermon->id] = $db->loadAssocList();		
		}
		$this->_files = $mediaFiles;
		return $this->_files;
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
		}

		return $this->_pagination;
	}
function _buildContentWhere()
	{
		global $mainframe, $option;

		$filter_topic		= $mainframe->getUserStateFromRequest( $option.'filter_topic',		'filter_topic',		0,				'int' );
		$filter_book		= $mainframe->getUserStateFromRequest( $option.'filter_book',		'filter_book',		0,				'int' );
		$filter_teacher		= $mainframe->getUserStateFromRequest( $option.'filter_teacher',	'filter_teacher',		0,				'int' );
		$filter_series		= $mainframe->getUserStateFromRequest( $option.'filter_series',		'filter_series',		0,				'int' );
		$filter_messagetype	= $mainframe->getUserStateFromRequest( $option.'filter_messagetype','filter_messagetype',		0,				'int' );
		$filter_year		= $mainframe->getUserStateFromRequest( $option.'filter_year',		'filter_year',		0,				'int' );
		$filter_orders		= $mainframe->getUserStateFromRequest( $option.'filter_orders',		'filter_orders',		'DESC',				'word' );
		//$search				= $mainframe->getUserStateFromRequest( $option.'search',			'search',			'',				'string' );
		//$search				= JString::strtolower( $search );
		//$filter_searchby	= $mainframe->getUserStateFromRequest( $option.'filter_searchby','filter_searchby','','word' );



		$where = array();

	if ($filter_topic > 0) {
			$where[] = ' #__bsms_studytopics.topic_id = '.(int) $filter_topic;
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
		
		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		
		return $where;
	}
function _buildContentOrderBy()
	{
		global $mainframe, $option;
		
		$orders = array('id','published','studydate','messagetype','teacher_id','studytitle','series_id','topics_id','hits');
		$filter_order = $mainframe->getUserStateFromRequest($option.'filter_order','filter_order','ordering','cmd' );
		$filter_order_Dir = strtoupper($mainframe->getUserStateFromRequest($option.'filter_order_Dir','filter_order_Dir','ASC'));
		//$filter_orders = $mainframe->getUserStateFromRequest($option.'filter_orders','filter_orders','DESC','word');
		if($filter_order_Dir != 'ASC' && $filter_order_Dir != 'DESC'){$filter_order_Dir = 'ASC';}
		if(!in_array($filter_order,$orders)){$filter_order = 'studydate';}
		
		$orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , id';
		
		return $orderby;
	}

}
?>