<?php

/**
 * @version     $Id: studieslist.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die();

include_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'helpers' . DS . 'translated.php');

jimport('joomla.application.component.modellist');

class biblestudyModelstudieslist extends JModelList {

    var $_files = null;

    function __construct($config = array()) {
        if(empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
              'study.published',
              'study.studydate',
              'study.studytitle',
              'book.bookname',
              'teacher.teachername',
              'messageType.message_type',
              'series.series_text',
              'study.hits',
              'mediafile.plays',
              'mediafile.downloads'
            );
        }
        
        parent::__construct($config);
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
            $mediaFiles[$sermon->id] = $db->loadAssocList();
        }
        $this->_files = $mediaFiles;
        return $this->_files;
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
     * get a list of all used books
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
     * get a list of all used teachers
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
     * get a list of all used series
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
     * get a list of all used message types
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
     * get a list of all used years
     */
    public function getYears() {
        $db = $this->getDBO();
        $query = $db->getQuery(true);

        $query->select('DISTINCT YEAR(studydate) as value, YEAR(studydate) as text');
        $query->from('#__bsms_studies');
        $query->order('value');

        $db->setQuery($query->__toString());
        $year = $db->loadObjectList();
        return $year;
    }

    /*
     * @since 7.0
     * get the number of plays of this study
     */
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