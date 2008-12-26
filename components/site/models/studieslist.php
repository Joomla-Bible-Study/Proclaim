<?php
defined('_JEXEC') or die();
global $mainframe, $option;
jimport( 'joomla.application.component.model' );

$params = &JComponentHelper::getParams($option);
$default_order = $params->get('default_order');

class biblestudyModelstudieslist extends JModel
{

	var $_total = null;
	var $_pagination = null;
	
	/**
	 * @desc From database
	 */
	var $_data;
	var $_teachers;
	var $_series;
	var $_messageTypes;
	var $_studyYears;
	var $_locations;
	var $_topics;
	var $_orders;

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


	}

	/**
	 * @desc Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function _buildQuery()
	{
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();
		$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, #__bsms_teachers.title AS teachertitle,'
		. ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_message_type.id AS mid,'
		. ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
		. ' #__bsms_topics.id AS tp_id, #__bsms_topics.topic_text, #__bsms_locations.id AS lid, #__bsms_locations.location_text'
		. ' FROM #__bsms_studies'
		. ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
		. ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
		. ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
		. ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
		. '	LEFT JOIN #__bsms_topics ON (#__bsms_studies.topics_id = #__bsms_topics.id)'
		. ' LEFT JOIN #__bsms_locations ON (#__bsms_studies.location_id = #__bsms_locations.id)'
		. $where
		. $orderby
		;
		return $query;
	}

	/**
	 * @desc Returns teachers
	 * @return Array
	 */
	function getTeachers() {
		if (empty($this->_teachers)) {
			$query = 'SELECT id AS value, teachername AS text, published'
			. ' FROM #__bsms_teachers'
			. ' WHERE published = 1'
			. ' ORDER BY teachername ASC';
			$this->_teachers = $this->_getList($query);
		}
		return $this->_teachers;
	}

	/**
	 * @desc Returns teachers
	 * @return Array
	 */
	function getSeries() {
		if(empty($this->_series)) {
			$query = 'SELECT id AS value, series_text AS text, published'
			. ' FROM #__bsms_series'
			. ' WHERE published = 1'
			. ' ORDER BY series_text ASC';
			$this->_series = $this->_getList($query);
		}
		return $this->_series;
	}

	/**
	 * @desc Returns message types
	 * @return Array
	 */
	function getMessageTypes() {
		if (empty($this->_messageTypes)) {
			$query = 'SELECT id AS value, message_type AS text, published'
			. ' FROM #__bsms_message_type'
			. ' WHERE published = 1'
			. ' ORDER BY message_type ASC';
			$this->_messageTypes = $this->_getList($query);
		}
		return $this->_messageTypes;
	}

	/**
	 * @desc Returns years where studies exist
	 * @return Array
	 */
	function getStudyYears() {
		if (empty($this->_StudyYears)) {
			$query = " SELECT DISTINCT date_format(studydate, '%Y') AS value, date_format(studydate, '%Y') AS text "
			. ' FROM #__bsms_studies '
			. ' ORDER BY value DESC';
			$this->_StudyYears = $this->_getList($query);
		}
		return $this->_StudyYears;
	}

	/**
	 * @desc Returns the locations
	 * @return Array
	 */
	function getLocations() {
		if (empty($this->_Locations)) {
			$query = ' SELECT id AS value, location_text AS text, published FROM #__bsms_locations WHERE published = 1'
			. ' ORDER BY location_text ASC ';
			$this->_Locations = $this->_getList($query);
		}
		return $this->_Locations;
	}

	/**
	 * @desc Returns message types
	 * @return Array
	 */
	function getTopics() {
		if (empty($this->_Topics)) {
			$query = 'SELECT DISTINCT #__bsms_studies.topics_id AS value, #__bsms_topics.topic_text AS text'
			. ' FROM #__bsms_studies'
			. ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studies.topics_id)'
			. ' WHERE #__bsms_topics.published = 1'
			. ' ORDER BY #__bsms_topics.topic_text ASC';
			$this->_Topics = $this->_getList($query);
		}
		return $this->_Topics;
	}

	/**
	 * @desc Returns Orders
	 * @return Array
	 */
	function getOrders() {
		if (empty($this->_Orders)) {
			$query = ' SELECT * FROM #__bsms_order '
			. ' ORDER BY id ';
			$this->_Orders = $this->_getList($query);
		}
		return $this->_Orders;
	}

	function getData()
	{
		//dump($data, 'Data from Model');
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
		$filter_location	= $mainframe->getUserStateFromRequest( $option.'filter_location', 	'filter_location', 0, 'int');
		$teacher_menu = $params->get('teacher_id', 1);
		$topic_menu = $params->get('topic_id', 1);
		$book_menu = $params->get('booknumber', 101);
		$series_menu = $params->get('series_id', 1);
		$messagetype_menu = $params->get('messagetype', 1);
		$location_menu = $params->get('locations', 1);
		$filter_orders		= $mainframe->getUserStateFromRequest( $option.'filter_orders',		'filter_orders',		'DESC',				'word' );


		$where = array();
		$rightnow = date('Y-m-d H:i:s');
		$where[] = ' #__bsms_studies.published = 1';
		$where[] = " date_format(#__bsms_studies.studydate, '%Y-%m-%d %T') <= ".(int)$rightnow;

		if ($filter_topic > 0) {
			$where[] = ' #__bsms_studies.topics_id = '.(int) $filter_topic;
		}
		if ($filter_location > 0) {
			$where[] = ' #__bsms_studies.location_id = '.(int) $filter_location;
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
		if ($teacher_menu > 0) {
			$where[] = ' #__bsms_studies.teacher_id = '.(int) $teacher_menu;
		}
		if ($book_menu > 0) {
			$where[] = ' #__bsms_studies.booknumber = '.(int) $book_menu;
		}
		if ($series_menu > 0) {
			$where[] = ' #__bsms_studies.series_id = '.(int) $series_menu;
		}
		if ($topic_menu > 0) {
			$where[] = ' #__bsms_studies.topics_id = '.(int) $topic_menu;
		}
		if ($messagetype_menu > 0) {
			$where[] = ' #__bsms_studies.messagetype = '.(int) $messagetype_menu;
		}
		if ($location_menu > 0) {
			$where[] = ' #__bsms_studies.location_id = '.(int) $location_menu;
		}
		//Added for user level control
		$user =& JFactory::getUser();
		$level_user = $user->get('gid');
		//$level_user = $user->usertype;
		//dump ($level_user, 'Level_user: ');
		$where[] = ' #__bsms_studies.show_level <= '.$level_user;

		//End for user level control

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