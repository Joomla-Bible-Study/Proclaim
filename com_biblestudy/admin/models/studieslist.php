<?php

/**
 * @version     $Id: studieslist.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

include_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'helpers' . DS . 'translated.php');

jimport('joomla.application.component.modellist');

abstract class modelClass extends JModelList {
    
}

class biblestudyModelstudieslist extends modelClass {

    /**
     *
     * @var array
     */
    var $_data;
    var $_total = null;
    var $_pagination = null;
    var $_files = null;

    function __construct() {
        parent::__construct();

        $mainframe = & JFactory::getApplication();
        $option = JRequest::getCmd('option');

        // Get the pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');

        $limitstart = $mainframe->getUserStateFromRequest('com_biblestudy&view=studieslist.limitstart', 'limitstart', 0, 'int');


        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    /**
     * Returns the query
     * @return string The query to be used to retrieve the rows from the database
     */
    function _buildQuery() {
        $where = $this->_buildContentWhere();
        $orderby = $this->_buildContentOrderBy();

        $query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername,'
                . ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_message_type.id AS mid,'
                . ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
                . ' group_concat(#__bsms_topics.id separator ", ") AS tp_id, group_concat(#__bsms_topics.topic_text separator ", ") as topic_text, sum(#__bsms_mediafiles.plays) AS totalplays, sum(#__bsms_mediafiles.downloads) AS totaldownloads, #__bsms_mediafiles.study_id'
                . ' FROM #__bsms_studies'
                . ' left join #__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)'
                . ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
                . ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
                . ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
                . ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
                . ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)'
                . ' LEFT JOIN #__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id)'
                . $where
                . ' GROUP BY #__bsms_studies.id'
                . $orderby
        ;
        return $query;
    }

    function getDownloads($id) {
        $query = ' SELECT SUM(downloads) AS totalDownloads FROM #__bsms_mediafiles WHERE study_id = ' . $id . ' GROUP BY study_id';
        $result = $this->_getList($query);
        if (!$result) {
            $result = '0';
            return $result;
        }
        return $result[0]->totalDownloads;
    }

    /**
     * Retrieves the data
     * @return array Array of objects containing the data from the database
     */
    function getData() {
        die('biblestudyModelstudieslist.getData is no more used');
//        // Lets load the data if it doesn't already exist
//        if (empty($this->_data)) {
//            $query = $this->_buildQuery();
//            $result = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
//            $this->_data = getTopicItemsTranslated($result);
//        }
//        //$this->setState('limitstart', $limitstart);
//        return $this->_data;
    }

    /**
     * Creates and executes a new query that retrieves the medifile information from the mediafiles table. 
     * It then adds to the dataObject the mediafiles associated with the sermon.
     * @return unknown_type
     */
    /* Tom commented this out because it caused the query to fail - needs work. */
    function getFiles() {
        $mediaFiles = null;
        $db = & JFactory::getDBO();
        $i = 0;
        foreach ($this->_data as $sermon) {
            $i++;
            $sermon_id = $sermon->id;
            $query = 'SELECT study_id, filename, #__bsms_folders.folderpath, #__bsms_servers.server_path'
                    . ' FROM #__bsms_mediafiles'
                    . ' LEFT JOIN #__bsms_servers ON (#__bsms_mediafiles.server = #__bsms_servers.id)'
                    . ' LEFT JOIN #__bsms_folders ON (#__bsms_mediafiles.path = #__bsms_folders.id)'
                    . ' WHERE `study_id` ='
                    . $sermon_id
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
    function getTotal() {
        // Lets load the content if it doesn't already exist
        if (empty($this->_total)) {
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
    function legacyGetPagination() {
        // Lets load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_pagination;
    }

    function _buildContentWhere() {
        $mainframe = & JFactory::getApplication();
        $option = JRequest::getCmd('option');

        $filter_topic = $mainframe->getUserStateFromRequest($option . 'filter_topic', 'filter_topic', 0, 'int');
        $filter_book = $mainframe->getUserStateFromRequest($option . 'filter_book', 'filter_book', 0, 'int');
        $filter_teacher = $mainframe->getUserStateFromRequest($option . 'filter_teacher', 'filter_teacher', 0, 'int');
        $filter_series = $mainframe->getUserStateFromRequest($option . 'filter_series', 'filter_series', 0, 'int');
        $filter_messagetype = $mainframe->getUserStateFromRequest($option . 'filter_messagetype', 'filter_messagetype', 0, 'int');
        $filter_year = $mainframe->getUserStateFromRequest($option . 'filter_year', 'filter_year', 0, 'int');
        $filter_orders = $mainframe->getUserStateFromRequest($option . 'filter_orders', 'filter_orders', 'DESC', 'word');
        //$search				= $mainframe->getUserStateFromRequest( $option.'search',			'search',			'',				'string' );
        //$search				= JString::strtolower( $search );
        //$filter_searchby	= $mainframe->getUserStateFromRequest( $option.'filter_searchby','filter_searchby','','word' );



        $where = array();

        if ($filter_topic > 0) {
            $where[] = ' #__bsms_studytopics.topic_id = ' . (int) $filter_topic;
        }
        if ($filter_book > 0) {
            $where[] = ' #__bsms_studies.booknumber = ' . (int) $filter_book;
        }
        if ($filter_teacher > 0) {
            $where[] = ' #__bsms_studies.teacher_id = ' . (int) $filter_teacher;
        }
        if ($filter_series > 0) {
            $where[] = ' #__bsms_studies.series_id = ' . (int) $filter_series;
        }
        if ($filter_messagetype > 0) {
            $where[] = ' #__bsms_studies.messagetype = ' . (int) $filter_messagetype;
        }
        if ($filter_year > 0) {
            $where[] = " date_format(#__bsms_studies.studydate, '%Y')= " . (int) $filter_year;
        }

        $where = ( count($where) ? ' WHERE ' . implode(' AND ', $where) : '' );

        return $where;
    }

    function _buildContentOrderBy() {
        $mainframe = & JFactory::getApplication();
        $option = JRequest::getCmd('option');

        $orders = array('id', 'published', 'studydate', 'messagetype', 'teacher_id', 'studytitle', 'series_id', 'topics_id', 'hits', 'totalplays', 'totaldownloads');
        $filter_order = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'ordering', 'cmd');
        $filter_order_Dir = strtoupper($mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'ASC'));
        //$filter_orders = $mainframe->getUserStateFromRequest($option.'filter_orders','filter_orders','DESC','word');
        if ($filter_order_Dir != 'ASC' && $filter_order_Dir != 'DESC') {
            $filter_order_Dir = 'ASC';
        }
        if (!in_array($filter_order, $orders)) {
            $filter_order = 'studydate';
        }

        $orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , id';

        return $orderby;
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
     * @return  JDatabaseQuery
     * @since   7.0
     */
    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select', 'study.id, study.published, study.studydate, study.studytitle, study.booknumber, study.chapter_begin,
                        study.verse_begin, study.chapter_end, study.verse_end, study.hits'));
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

        //Join over Topics
        $query->select('topic.topic_text, topic.params AS topic_params');
        $query->join('LEFT', '#__bsms_topics AS topic ON topic.id = study.topics_id');

        //Join over Books
        $query->select('book.bookname');
        $query->join('LEFT', '#__bsms_books AS book ON book.booknumber = study.booknumber');

        //Join over Plays/Downloads
        $query->select('SUM(mediafile.plays) AS totalplays, SUM(mediafile.downloads) as totaldownloads, mediafile.study_id');
        $query->join('LEFT', '#__bsms_mediafiles AS mediafile ON mediafile.study_id = study.id');
        $query->group('study.id');



        //Filter by studytitle
        $studytitle = $this->getState('filter.studytitle');
        if (!empty($studytitle))
            $query->where('study.studytitle LIKE "' . $studytitle . '%"');

        //Filter by book
        $book = $this->getState('filter.book');
        if (!empty($book))
            $query->where('study.booknumber = ' . (int) $book . ' OR study.booknumber2 = ' . (int) $book);
        $query->join('LEFT', '#__bsms_books AS books ON books.booknumber = study.booknumber');

        //Filter by teacher
        $teacher = $this->getState('filter.teacher');
        if (!empty($teacher))
            $query->where('study.teacher_id = ' . (int) $teacher);

        //Filter by series
        $series = $this->getState('filter.series');
        if (!empty($series))
            $query->where('study.series_id = ' . (int) $series);

        //Filter by message type
        $messageType = $this->getState('filter.messageType');
        if (!empty($messageType))
            $query->where('study.messageType = ' . (int) $messageType);

        //Filter by Year
        $year = $this->getState('filter.year');
        if (!empty($year))
            $query->where('YEAR(study.studydate) = ' . (int) $year);

        //Filter by topic
        $topic = $this->getState('filter.topic');
        if (!empty($topic))
            $query->where('study.topics_id = ' . (int) $topic);

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('study.published = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(study.published = 0 OR study.published = 1)');
        }

        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));

        return $query;
    }

    /*
     * @since 7.0
     * translate item entries: books, topics
     */

    public function getTranslated($items = array()) {
        foreach ($items as $item) {
            $item->bookname = JText::_($item->bookname);
            $item->topic_text = getTopicItemTranslated($item);
        }
        return $items;
    }

    /*
     * @since 7.0
     */

    public function getBooks() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('book.booknumber AS value, book.bookname AS text, book.id');
        $query->from('#__bsms_books AS book');
        $query->join('INNER', '#__bsms_studies AS study ON study.booknumber = book.booknumber');
        $query->group('book.id');
        $query->order('book.booknumber');

        $db->setQuery($query->__toString());

        $db_result = $db->loadAssocList();
        foreach ($db_result as $i => $value) {
            $db_result[$i]['text'] = JText::_($value['text']);
        }
        return $db_result;
    }

    /*
     * @since 7.0
     */

    public function getTeachers() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('teacher.id AS value, teacher.teachername AS text');
        $query->from('#__bsms_teachers AS teacher');
        $query->join('INNER', '#__bsms_studies AS study ON study.teacher_id = teacher.id');
        $query->group('teacher.id');
        $query->order('teacher.teachername');

        $db->setQuery($query->__toString());
        return $db->loadObjectList();
    }

    /*
     * @since 7.0
     */

    public function getSeries() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('series.id AS value, series.series_text AS text');
        $query->from('#__bsms_series AS series');
        $query->join('INNER', '#__bsms_studies AS study ON study.series_id = series.id');
        $query->group('series.id');
        $query->order('series.series_text');

        $db->setQuery($query->__toString());
        return $db->loadObjectList();
    }

    /*
     * @since 7.0
     */

    public function getMessageTypes() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('messageType.id AS value, messageType.message_type AS text');
        $query->from('#__bsms_message_type AS messageType');
        $query->join('INNER', '#__bsms_studies AS study ON study.messagetype = messageType.id');
        $query->group('messageType.id');
        $query->order('messageType.message_type');

        $db->setQuery($query->__toString());
        return $db->loadObjectList();
    }

    /*
     * @since 7.0
     */

    public function getTopics() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('topic.id AS value, topic.topic_text AS topic_text, topic.params AS topic_params');
        $query->from('#__bsms_topics AS topic');
        $query->join('INNER', '#__bsms_studies AS study ON study.topics_id = topic.id');
        $query->group('topic.id');
        $query->order('topic.topic_text');

        $db->setQuery($query->__toString());
        $db_result = $db->loadObjectList();
        $output = array();
        foreach ($db_result as $i => $value) {
            $value->text = getTopicItemTranslated($value);
            $output[] = $value;
        }
        return $output;
    }

    public function getYears() {
        $db = $this->getDBO();
        $query = $db->getQuery(true);

        $query->select('DISTINCT YEAR(studydate) as value, YEAR(studydate) as text');
        $query->from('#__bsms_studies');
        $query->order('value');

        $db->setQuery($query->__toString());
        $year = $db->loadObjectList();
        //  dump ($year);
        return $year;
    }

    public function getPlays($id) {
        $db = $this->getDBO();
        $query = $db->getQuery(true);

        $query->select('SUM(plays) AS totalPlays');
        $query->from('#__bsms_mediafiles');
        $query->group('study_id');
        $query->where('study_id = ' . $id);
        $db->setQuery($query->__toString());
        $plays = $db->loadResult();
        return $plays;
    }

}

?>