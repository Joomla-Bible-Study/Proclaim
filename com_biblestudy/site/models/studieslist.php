<?php
/**
 * @version $Id$
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die();
$mainframe = & JFactory::getApplication();
$option = JRequest::getCmd('option');
jimport('joomla.application.component.modellist');
include_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'helpers' . DS . 'translated.php');

$params = &JComponentHelper::getParams($option);
$default_order = $params->get('default_order');

//class biblestudyModelstudieslist extends modelClass {
class biblestudyModelstudieslist extends JModelList {

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
	var $_select;
	var $_books;
	var $_template;
	var $_admin;


	function __construct() {
		$config['table_path'] = JPATH_COMPONENT . DS . 'tables';    // use site tables
		parent::__construct($config);
		$mainframe = & JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$params = & $mainframe->getPageParameters();

		$t = JRequest::getInt('t', 'get');
		if (!$t) {
			$t = 1;
		}
		$template = $this->getTemplate();
		jimport('joomla.html.parameter');
		// Convert parameter fields to objects.
		$registry = new JRegistry;
		$registry->loadJSON($template[0]->params);
		$params = $registry;

		$this->_params = $params;
		$config = JFactory::getConfig();
		// Get the pagination request variables

		$this->setState('limit', $params->get('itemslimit'), 'limit', $params->get('itemslimit'), 'int');
		$this->setState('limitstart', JRequest::getVar('limitstart', 0, '', 'int'));

		// In case limit has been changed, adjust limitstart accordingly
		$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));
		// In case we are on more than page 1 of results and the total changes in one of the drop downs to a selection that has fewer in its total, we change limitstart
		if ($this->getTotal() < $this->getState('limitstart')) {
			$this->setState('limitstart', 0, '', 'int');
		}
	}

	/**
	 * @since   7.0
	 */
	protected function populateState() {
		$studytitle = $this->getUserStateFromRequest($this->context . '.filter.studytitle', 'filter_studytitle');
		$this->setState('filter.studytitle', $studytitle);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$book = $this->getUserStateFromRequest($this->context . '.filter.book', 'filter_book');
		$this->setState('filter.book', $book);

		$teacher = $this->getUserStateFromRequest($this->context . '.filter.teacher', 'filter_teacher');
		$this->setState('filter.teacher', $teacher);

		$series = $this->getUserStateFromRequest($this->context . '.filter.series', 'filter_series');
		$this->setState('filter.series', $series);

		$messageType = $this->getUserStateFromRequest($this->context . '.filter.messageType', 'filter_message_type');
		$this->setState('filter.messageType', $messageType);

		$year = $this->getUserStateFromRequest($this->context . '.filter.year', 'filter_year');
		$this->setState('filter.year', $year);

		$topic = $this->getUserStateFromRequest($this->context . '.filter.topic', 'filter_topic');
		$this->setState('filter.topic', $topic);

		$state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
		$this->setState('filter.state', $state);

		parent::populateState('study.studydate', 'DESC');
	}

	/**
	 * Build an SQL query to load the list data
	 *
	 * @return JDatabaseQuery
	 * @since 7.0
	 */
	protected function getListQuery() {
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		/**
		 * @todo This should select only the fields that ere to be displayed (to speedup the query)
		 */
		$query->select(
		$this->getState(
                        'list.select', 'study.*'));
		$query->from('#__bsms_studies AS study');

		//Join over Message Types
		$query->select('messageType.message_type AS messageType');
		$query->join('LEFT', '#__bsms_message_type AS messageType ON messageType.id = study.messagetype');

		//Join over Teachers
		$query->select('teacher.teachername AS teachername');
		$query->join('LEFT', '#__bsms_teachers AS teacher ON teacher.id = study.teacher_id');

		//Join over Series
		$query->select('series.series_text');
		$query->join('LEFT', '#__bsms_series AS series ON series.id = study.series_id');

        //Join over Locations
        $query->select('location.location_text');
        $query->join('LEFT', '#__bsms_locations AS location ON location.id = study.location_id');        
        
        //Join over Topics
        $query->select('topic.topic_text');
        $query->join('LEFT', '#__bsms_topics AS topic ON topic.id = study.topics_id');        
   
        //Join over Books
        $query->select('book.bookname');
        $query->join('LEFT', '#__bsms_books AS book ON book.booknumber = study.booknumber');
        
        //Filter by topic
        $topic = $this->getState('filter.topic');
        if (is_numeric($topic))
            $query->where('study.topics_id = ' . (int) $topic);
        elseif (is_array($topic) && count($topic) > 0) {
            JArrayHelper::toInteger($topic);
            $topic = implode(',', $topic);
            if(!empty($topic))
                $query->where('study.topics_id IN ('.$topic.')');            
        }
        
        //Filter by book
        $book = $this->getState('filter.book');
        if (is_numeric($book))
            $query->where('( study.booknumber = ' . (int) $book . ' OR study.booknumber2 = ' . (int) $book.')');
        elseif (is_array($book) && count($book) > 0) {
            JArrayHelper::toInteger($book);
            $book = implode(',', $book);
            if(!empty($book))
                $query->where('(study.booknumber IN ('.$book.') OR study.booknumber2 IN ('.$book.'))');
            
        }       
        
        
//        //Filter by chapter 
        /**
         * @todo FIX ME -- I don't work!
         */
        $chapter_begin = $this->getState('filter.chapter_begin');
        if (!empty($chapter_begin))
            $query->where('study.teacher_id = ' . (int) $teacher);        
        
        //Filter by teacher
        $teacher = $this->getState('filter.teacher');
        if (is_numeric($teacher))
            $query->where('study.teacher_id = ' . (int) $teacher);
        elseif (is_array($teacher) && count($teacher) > 0) {
            JArrayHelper::toInteger($teacher);
            $teacher = implode(',', $teacher);
            if(!empty($teacher))
                $query->where('study.teacher_id IN ('.$teacher.')');
        }

        //Filter by series
        $series = $this->getState('filter.series');
        if (is_numeric($series))
            $query->where('study.series_id = ' . (int) $series);
        elseif (is_array($series) && count($series) > 0) {
            JArrayHelper::toInteger($series);
            $series = implode(',', $series);
            if(!empty ($series))
                $query->where('study.series_id IN ('.$series.')');
        }

        //Filter by message type
        $messageType = $this->getState('filter.messageType');
        if (is_numeric($messageType))
            $query->where('study.messageType = ' . (int) $messageType);
        elseif(is_array($messageType) && count($messageType) > 0) {
            JArrayHelper::toInteger($messageType);
            $messageType = implode(',', $messageType);
            if(!empty($messageType))
                $query->where('study.messageType IN ('.$messageType.')');
        }

        //Filter by Year
        $year = $this->getState('filter.year');
        if (!empty($year))
            $query->where('YEAR(study.studydate) = ' . (int) $year);        
        
        //Filter by Location
        $location = $this->getState('filter.location');
        if (!is_numeric($messageType))
            $query->where('study.location_id = ' . (int) $location); 
        elseif(is_array($location) && count($location) > 0) {
            JArrayHelper::toInteger ($location);
            $location = implode(',', $location);
            if(!empty($location))
                $query->where('study.location_id IN ('.$location.')');
        }
        
        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));

		return $query;

	}


	function setSelect($string) {

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
		if (empty($this->_series)) {
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
	 * @desc Returns Orders
	 * @return Array
	 */
	function getOrders() {
		if (empty($this->_Orders)) {
			$query = ' SELECT * FROM #__bsms_order '
			. ' ORDER BY id ';
			$db_result = $this->_getList($query);

			$output = array();
			foreach ($db_result as $i => $value) {
				$value->text = JText::_($value->text);
				$output[] = $value;
			}
			$this->_Orders = $output;
		}
		return $this->_Orders;
	}

	function getBooks() {
		if (empty($this->_Books)) {
			//get parameters
			$booklist = $this->_params->get('booklist');
			if ($booklist == 1) {
				$query = 'SELECT DISTINCT s.booknumber AS value, s.published AS spublished, b.id, b.booknumber AS bbooknumber, b.bookname AS text FROM #__bsms_studies AS s LEFT JOIN #__bsms_books AS b ON ( b.booknumber = s.booknumber ) WHERE s.published =1 AND b.id IS NOT NULL ORDER BY bbooknumber';
			} else {
				$query = 'SELECT id, booknumber AS value, bookname AS text, published'
				. ' FROM #__bsms_books'
				. ' WHERE published = 1'
				. ' ORDER BY booknumber';
			}
			$db_result = $this->_getList($query);

			$output = array();
			foreach ($db_result as $i => $value) {
				$value->text = JText::_($value->text);
				$output[] = $value;
			}
			$this->_Books = $output;
		}
		return $this->_Books;
	}

	/**
	 * @desc Returns the Template to display the list
	 * @return Array
	 */
	function getTemplate() {
		if (empty($this->_template)) {
			$templateid = JRequest::getVar('t', 1, 'get', 'int');
			$query = 'SELECT *'
			. ' FROM #__bsms_templates'
			. ' WHERE published = 1 AND id = ' . $templateid;
			$this->_template = $this->_getList($query);
		}
		return $this->_template;
	}

	function getAdmin() {
		if (empty($this->_admin)) {
			$query = 'SELECT *'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$this->_admin = $this->_getList($query);
		}
		return $this->_admin;
	}

	function getData() {
		$mainframe = & JFactory::getApplication();
		// Lets load the data if it doesn't already exist
		if (empty($this->_data)) {
			$query = $this->_buildQuery();
			$result = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
			foreach ($result AS $item) {
				$topic_text = getTopicItemTranslated($item);
				$item->topic_text = $topic_text;
				$item->bookname = JText::_($item->bookname);
			}
			$this->_data = $result;
		}

		return $this->_data;
	}

	/**
	 * Method to get a pagination object for the studies
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination() {
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}
}