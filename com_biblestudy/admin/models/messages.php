<?php

/**
 * Messages model
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

include_once (JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'translated.php');

jimport('joomla.application.component.modellist');

/**
 * Message model class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyModelMessages extends JModelList {

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   11.1
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'study.id',
                'published', 'study.published',
                'studydate', 'study.studydate',
                'studytitle', 'study.studytitle',
                'bookname', 'book.bookname',
                'teachername', 'teacher.teachername',
                'message_type', 'messageType.message_type',
                'series_text', 'series.series_text',
                'hits', 'study.hits',
                'plays', 'mediafile.plays',
                'access', 'series.access', 'access_level',
                'downloads', 'mediafile.downloads'
            );
        }

        parent::__construct($config);
    }

    /**
     * Get Downloads
     * @param type $id
     * @return string
     */
    public function getDownloads($id) {
        $query = ' SELECT SUM(downloads) AS totalDownloads FROM #__bsms_mediafiles WHERE study_id = ' . $id . ' GROUP BY study_id';
        $result = $this->_getList($query);
        if (!$result) {
            $result = '0';
            return $result;
        }
        return $result[0]->totalDownloads;
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param	string		$id	A prefix for the store id.
     *
     * @return	string		A store id.
     * @since	7.1.0
     */
    protected function getStoreId($id = '') {

        // Compile the store id.
        $id .= ':' . $this->getState('filter.studytitle');
        $id .= ':' . $this->getState('filter.book');
        $id .= ':' . $this->getState('filter.teacher');
        $id .= ':' . $this->getState('filter.series');
        $id .= ':' . $this->getState('filter.messageType');
        $id .= ':' . $this->getState('filter.year');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /* Tom commented this out because it caused the query to fail - needs work. */

    /**
     * Creates and executes a new query that retrieves the medifile information from the mediafiles table.
     * It then adds to the dataObject the mediafiles associated with the sermon.
     * @return unknown_type
     */
    public function getFiles() {
        $mediaFiles = null;
        $db = JFactory::getDBO();
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
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param type $ordering
     * @param type $direction
     *
     * @return	void
     * @since 7.1.0
     */
    protected function populateState($ordering = null, $direction = null) {

        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }
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

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

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
        $user = JFactory::getUser();

        $query->select(
                $this->getState(
                        'list.select', 'study.id, study.published, study.studydate, study.studytitle, study.booknumber, study.chapter_begin,
                        study.verse_begin, study.chapter_end, study.verse_end, study.hits, study.alias, study.language, study.access'));
        $query->from('#__bsms_studies AS study');

        // Join over the language
        $query->select('l.title AS language_title');
        $query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = study.language');

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

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('study.access IN (' . $groups . ')');
        }

        //Filter by teacher
        $teacher = $this->getState('filter.teacher');
        if (is_numeric($teacher)) {
            $query->where('study.teacher_id = ' . (int) $teacher);
        }

        //Filter by series
        $series = $this->getState('filter.series');
        if (is_numeric($series)) {
            $query->where('study.series_id = ' . (int) $series);
        }

        //Filter by message type
        $messageType = $this->getState('filter.messageType');
        if (is_numeric($messageType)) {
            $query->where('study.messageType = ' . (int) $messageType);
        }

        //Filter by Year
        $year = $this->getState('filter.year');
        if (!empty($year)) {
            $query->where('YEAR(study.studydate) = ' . (int) $year);
        }

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('study.published = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(study.published = 0 OR study.published = 1)');
        }

        //Filter by studytitle
        $studytitle = $this->getState('filter.studytitle');
        if (!empty($studytitle)) {
            if (stripos($studytitle, 'id:') === 0) {
                $query->where('study.id = ' . (int) substr($studytitle, 3));
            } else {
                $studytitle = $db->Quote('%' . $db->escape($studytitle, true) . '%');
                $query->where('(study.studytitle LIKE ' . $studytitle . ' OR study.alias LIKE ' . $studytitle . ')');
            }
        }

        //Filter by book
        $book = $this->getState('filter.book');
        if (is_numeric($book)) {
            $query->where('(study.booknumber = ' . (int) $book . ' OR study.booknumber2 = ' . (int) $book . ')');
        }

        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));

        return $query;
    }

    /**
     * translate item entries: books, topics
     * @param array $items Items for entris
     * @since 7.0
     */
    public function getTranslated($items = array()) {
        foreach ($items as $item) {
            $item->bookname = JText::_($item->bookname);
            $item->topic_text = getTopicItemTranslated($item);
        }
        return $items;
    }

    /**
     * get a list of all used books
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

    /**
     * get a list of all used teachers
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

    /**
     * get a list of all used series
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

    /**
     * get a list of all used message types
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

    /**
     * get a list of all used years
     * @since 7.0
     */
    public function getYears() {
        // Create a new query object.
        $db = $this->getDBO();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('DISTINCT YEAR(studydate) as value, YEAR(studydate) as text');
        $query->from('#__bsms_studies');
        $query->order('value');

        // Setup the query
        $db->setQuery($query->__toString());

        // Return the result
        return $db->loadObjectList();
    }

    /**
     * get the number of plays of this study
     * @param int $id
     * @since 7.0
     */
    public function getPlays($id) {
        // Create a new query object.
        $db = $this->getDBO();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('SUM(plays) AS totalPlays');
        $query->from('#__bsms_mediafiles');
        $query->group('study_id');
        $query->where('study_id = ' . $id);

        // Setup the query
        $db->setQuery($query->__toString());

        // Return the result
        return $db->loadResult();
    }

    /**
     * Method to get a list of articles.
     * Overridden to add a check for access levels.
     *
     * @return	mixed	An array of data items on success, false on failure.
     * @since	1.6.1
     */
    public function getItems() {
        $items = parent::getItems();
        $app = JFactory::getApplication();
        if ($app->isSite()) {
            $user = JFactory::getUser();
            $groups = $user->getAuthorisedViewLevels();

            for ($x = 0, $count = count($items); $x < $count; $x++) {
                //Check the access level. Remove articles the user shouldn't see
                if (!in_array($items[$x]->access, $groups)) {
                    unset($items[$x]);
                }
            }
        }
        return $items;
    }

}
