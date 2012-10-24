<?php

/**
 * Sermons Model
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

include_once (JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'translated.php');

jimport('joomla.application.component.modellist');

/**
 * Model class for Sermons
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class BiblestudyModelSermons extends JModelList {

    /**
     * Files
     * @var array
     */
    var $_files = null;

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
                'study.published',
                'study.studydate',
                'study.studytitle',
                'book.bookname',
                'teacher.teachername',
                'messageType.message_type',
                'series.series_text',
                'study.hits',
                'mediafile.plays',
                'mediafile.downloads',
                'study.chapter_begin',
                'study.language',
                'study.location_id',
                'study.chapter_end'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   11.1
     */
    protected function populateState($ordering = 'study.studydate', $direction = 'DESC') {
        $app = JFactory::getApplication();

        $this->setState('filter.language', $app->getLanguageFilter());

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

        $messageType = $this->getUserStateFromRequest($this->context . '.filter.messageType', 'filter_messagetype');
        $this->setState('filter.messageType', $messageType);

        $year = $this->getUserStateFromRequest($this->context . '.filter.year', 'filter_year');
        $this->setState('filter.year', $year);

        $order = $this->getUserStateFromRequest($this->context . '.filter.orders', 'filter_orders');
        $this->setState('filter.orders', $order);

        $topic = $this->getUserStateFromRequest($this->context . '.filter.topic', 'filter_topic');
        $this->setState('filter.topic', $topic);

        $location = $this->getUserStateFromRequest($this->context . '.filter.location', 'filter_location');
        $this->setState('filter.location', $location);

        $languages = $this->getUserStateFromRequest($this->context . '.filter.languages', 'filter_languages');
        $this->setState('filter.languages', $languages);

        /**
         * @todo We need to figure out how to properly use the populate state so that limitstart works with and without SEF
         */
        parent::populateState('study.studydate', 'DESC');

        $limitstart = JRequest::getInt('limitstart');
        $value = JRequest::getUInt('start');
        $this->setState('list.start', $value);
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
     * @since	1.6
     */
    protected function getStoreId($id = '') {
        // Compile the store id.
        $id .= ':' . serialize($this->getState('filter.published'));
        $id .= ':' . $this->getState('filter.studytitle');
        $id .= ':' . $this->getState('filter.book');
        $id .= ':' . $this->getState('filter.teacher');
        $id .= ':' . $this->getState('filter.series');
        $id .= ':' . $this->getState('filter.messageType');
        $id .= ':' . $this->getState('filter.year');
        $id .= ':' . $this->getState('filter.order');
        $id .= ':' . $this->getState('filter.topic');
        $id .= ':' . $this->getState('filter.location');
        $id .= ':' . $this->getState('list.start');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data
     *
     * @return  JDatabaseQuery
     * @since   7.0
     */
    protected function getListQuery() {
        $user = JFactory::getUser();
        $groups = implode(',', $user->getAuthorisedViewLevels());
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $template_params = $this->getTemplate();
        $registry = new JRegistry;
        $registry->loadJSON($template_params->params);
        $t_params = $registry;
        $query->select(
                $this->getState(
                        'list.select', 'study.id, study.published, study.studydate, study.studytitle, study.booknumber, study.chapter_begin,
		                study.verse_begin, study.chapter_end, study.verse_end, study.hits, study.alias, study.topics_id, study.studyintro,
		                study.teacher_id, study.secondary_reference, study.booknumber2, study.location_id, study.media_hours, study.media_minutes,
		                study.media_seconds, study.series_id, study.thumbnailm, study.thumbhm, study.thumbwm, study.access, study.user_name,
		                study.user_id, study.studynumber, study.chapter_begin2, study.chapter_end2, study.verse_end2, study.verse_begin2 ') . ','
                . ' CASE WHEN CHAR_LENGTH(study.alias) THEN CONCAT_WS(\':\', study.id, study.alias) ELSE study.id END as slug ');
        $query->from('#__bsms_studies AS study');

        //Join over Message Types
        $query->select('messageType.message_type AS message_type');
        $query->join('LEFT', '#__bsms_message_type AS messageType ON messageType.id = study.messagetype');

        //Join over Teachers
        $query->select('teacher.teachername AS teachername, teacher.title as teachertitle, teacher.thumb, teacher.thumbh, teacher.thumbw');
        $query->join('LEFT', '#__bsms_teachers AS teacher ON teacher.id = study.teacher_id');

        //Join over Series
        $query->select('series.series_text, series.series_thumbnail, series.description as sdescription, series.access as series_access');
        $query->join('LEFT', '#__bsms_series AS series ON series.id = study.series_id');

        //Join over Books
        $query->select('book.bookname');
        $query->join('LEFT', '#__bsms_books AS book ON book.booknumber = study.booknumber');

        //Join over Plays/Downloads
        $query->select('SUM(mediafile.plays) AS totalplays, SUM(mediafile.downloads) as totaldownloads, mediafile.study_id');
        $query->join('LEFT', '#__bsms_mediafiles AS mediafile ON mediafile.study_id = study.id');

        //Join over Locations
        $query->select('locations.location_text');
        $query->join('LEFT', '#__bsms_locations AS locations ON study.location_id = locations.id');

        //Join over topics
        $query->select('GROUP_CONCAT(DISTINCT st.topic_id)');
        $query->join('LEFT', '#__bsms_studytopics AS st ON study.id = st.study_id');
        $query->select('GROUP_CONCAT(DISTINCT t.id), GROUP_CONCAT(DISTINCT t.topic_text) as topics_text, GROUP_CONCAT(DISTINCT t.params)');
        $query->join('LEFT', '#__bsms_topics AS t ON t.id = st.topic_id');

        //Join over users
        $query->select('users.name as submitted');
        $query->join('LEFT', '#__users as users on study.user_id = users.id');

        $query->group('study.id');

        $query->select('GROUP_CONCAT(DISTINCT m.id) as mids');
        $query->join('LEFT', '#__bsms_mediafiles as m ON study.id = m.study_id');

        //filter only for authorized view
        $query->where('(series.access IN (' . $groups . ') or study.series_id <= 0)');
        $query->where('study.access IN (' . $groups . ')');

        //select only published studies
        $query->where('study.published = 1');

        //Begin the filters for menu items
        //These params are the filters set by the menu item, not in the JBS template
        $app = JFactory::getApplication('site');


        // Load the parameters. Merge Global and Menu Item params into new object
        $params = $app->getParams();
        $menuparams = new JRegistry;

        if ($menu = $app->getMenu()->getActive()) {
            $menuparams->loadString($menu->params);
        }
        $books = null;
        $teacher = null;
        $locations = null;
        $messagetype = null;
        $topics = null;
        $series = null;
        $years = null;

        // See if we are getting itemid
        $itemid = JRequest::getVar('Itemid');
        $application = JFactory::getApplication();
        $menu = $application->getMenu();
        $item = $menu->getItem($itemid);
        // only do this if item id is avalible
        if ($item != null) {
            $teacher = $menuparams->get('mteacher_id');
            $locations = $menuparams->get('mlocations');
            $books = $menuparams->get('mbooknumber');
            $series = $menuparams->get('mseries_id');
            $topics = $menuparams->get('mtopic_id');
            $messagetype = $menuparams->get('mmessagetype');
            $years = $menuparams->get('years');

            //filter over teachers
            $filters = $teacher;
            if ($filters) {
                if (count($filters) > 1) {
                    $where2 = array();
                    $subquery = '(';
                    foreach ($filters as $filter) {
                        $where2[] = 'study.teacher_id = ' . (int) $filter;
                    }
                    $subquery .= implode(' OR ', $where2);
                    $subquery .= ')';

                    $query->where($subquery);
                } else {
                    foreach ($filters as $filter) {
                        if ($filter >= 1) {
                            $query->where('study.teacher_id = ' . (int) $filter);
                        }
                    }
                }
            }
            //filter locations
            $filters = $locations;
            if ($filters) {
                if (count($filters) > 1) {
                    $where2 = array();
                    $subquery = '(';
                    foreach ($filters as $filter) {
                        $where2[] = 'study.location_id = ' . (int) $filter;
                    }
                    $subquery .= implode(' OR ', $where2);
                    $subquery .= ')';

                    $query->where($subquery);
                } else {
                    foreach ($filters AS $filter) {
                        if ($filter >= 1) {
                            $query->where('study.location_id = ' . (int) $filter);
                        }
                    }
                }
            }
            //filter over books
            $filters = $books;
            if ($filters) {
                if (count($filters) > 1) {
                    $where2 = array();
                    $subquery = '(';
                    foreach ($filters as $filter) {
                        $where2[] = 'study.booknumber = ' . (int) $filter;
                    }
                    $subquery .= implode(' OR ', $where2);
                    $subquery .= ')';

                    $query->where($subquery);
                } else {
                    foreach ($filters AS $filter) {
                        if ($filter >= 1) {
                            $query->where('study.booknumber = ' . (int) $filter);
                        }
                    }
                }
            }
            $filters = $series;
            if ($filters) {
                if (count($filters) > 1) {
                    $where2 = array();
                    $subquery = '(';
                    foreach ($filters as $filter) {
                        $where2[] = 'study.series_id = ' . (int) $filter;
                    }
                    $subquery .= implode(' OR ', $where2);
                    $subquery .= ')';

                    $query->where($subquery);
                } else {
                    foreach ($filters AS $filter) {
                        if ($filter >= 1) {
                            $query->where('study.series_id = ' . (int) $filter);
                        }
                    }
                }
            }
            $filters = $topics;
            if ($filters) {
                if (count($filters) > 1) {
                    $where2 = array();
                    $subquery = '(';
                    foreach ($filters as $filter) {
                        $where2[] = 'study.topics_id = ' . (int) $filter;
                    }
                    $subquery .= implode(' OR ', $where2);
                    $subquery .= ')';

                    $query->where($subquery);
                } else {
                    foreach ($filters AS $filter) {
                        if ($filter >= 1) {
                            $query->where('study.topics_id = ' . (int) $filter);
                        }
                    }
                }
            }
            $filters = $messagetype;
            if ($filters) {
                if (count($filters) > 1) {
                    $where2 = array();
                    $subquery = '(';
                    foreach ($filters as $filter) {
                        $where2[] = 'study.messagetype = ' . (int) $filter;
                    }
                    $subquery .= implode(' OR ', $where2);
                    $subquery .= ')';

                    $query->where($subquery);
                } else {
                    foreach ($filters AS $filter) {
                        if ($filter >= 1) {
                            $query->where('study.messagetype = ' . (int) $filter);
                        }
                    }
                }
            }
            $filters = $years;
            if ($filters) {
                if (count($filters) > 1) {
                    $where2 = array();
                    $subquery = '(';
                    foreach ($filters as $filter) {
                        $where2[] = 'YEAR(study.studydate) = ' . (int) $filter;
                    }
                    $subquery .= implode(' OR ', $where2);
                    $subquery .= ')';

                    $query->where($subquery);
                } else {
                    foreach ($filters AS $filter) {
                        if ($filter >= 1) {
                            $query->where('YEAR(study.studydate) = ' . (int) $filter);
                        }
                    }
                }
            }
        }

        //Filter by studytitle
        $studytitle = $this->getState('filter.studytitle');
        if (!empty($studytitle))
            $query->where('study.studytitle LIKE "' . $studytitle . '%"');

        //Filter by book
        $book = $this->getState('filter.book');
        if (!empty($book)) {
            $chb = JRequest::getInt('minChapt', '', 'post');
            $che = JRequest::getInt('maxChapt', '', 'post');
            if ($chb && $che) {
                $query->where('(study.booknumber = ' . (int) $book . ' AND study.chapter_begin >= ' . $chb . ' AND study.chapter_end <= ' . $che . ') OR study.booknumber2 = ' . (int) $book);
            } else if ($chb) {
                $query->where('(study.booknumber = ' . (int) $book . ' AND study.chapter_begin > = ' . $chb . ') OR study.booknumber2 = ' . (int) $book);
            } else if ($che) {
                $query->where('(study.booknumber = ' . (int) $book . ' AND study.chapter_end <= ' . $che . ') OR study.booknumber2 = ' . (int) $book);
            } else {
                $query->where('(study.booknumber = ' . (int) $book . ' OR study.booknumber2 = ' . (int) $book . ')');
            }
        }

        //Filter by teacher
        $teacher = $this->getState('filter.teacher');
        if ($teacher >= 1)
            $query->where('study.teacher_id = ' . (int) $teacher);

        //Filter by series
        $series = $this->getState('filter.series');
        if ($series >= 1)
            $query->where('study.series_id = ' . (int) $series);

        //Filter by message type
        $messageType = $this->getState('filter.messageType');
        if ($messageType >= 1)
            $query->where('study.messageType = ' . (int) $messageType);

        //Filter by Year
        $year = $this->getState('filter.year');
        if ($year >= 1)
            $query->where('YEAR(study.studydate) = ' . (int) $year);

        //Filter by topic
        $topic = $this->getState('filter.topic');
        if (!empty($topic))
            $query->where('st.topic_id LIKE "%' . $topic . '%"');

        //Filter by location
        $location = $this->getState('filter.location');
        if ($location >= 1)
            $query->where('study.location_id = ' . (int) $location);

        // Filter by language
        $language = $params->get('language', '*');
        if ($this->getState('filter.languages')) {
            $query->where('study.language in (' . $db->Quote($this->getState('filter.languages')) . ',' . $db->Quote('*') . ')');
        } elseif ($this->getState('filter.language') || $language != '*') {
            $query->where('study.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
        }

        //Order by order filter
        $orderparam = $params->get('default_order'); //print_r($t_params);
        if (empty($orderparam)) {
            $orderparam = $t_params->get('default_order', '1');
        }
        if ($orderparam == 2) {
            $order = "ASC";
        } else {
            $order = "DESC";
        }
        $orderstate = $this->getState('filter.orders');
        if (!empty($orderstate))
            $order = $orderstate;

        $query->order('studydate ' . $order);
        return $query;
    }

    /**
     * Translate item entries: books, topics
     * @param array $items
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
     * Returns the topics
     * @return Array
     * @since 7.0.2
     */
    public function getTopics() {
        if (empty($this->_Topics)) {
            $db = $this->getDBO();
            $query = $db->getQuery(true);
            $query->select('DISTINCT #__bsms_topics.id, #__bsms_topics.topic_text, #__bsms_topics.params AS topic_params');
            $query->from('#__bsms_studies');
            $db->select('#__bsms_studytopics');
            $query->join('LEFT', '#__bsms_studytopics ON #__bsms_studies.id = #__bsms_studytopics.study_id');
            $db->select('#__bsms_topics');
            $query->join('LEFT', '#__bsms_topics ON #__bsms_topics.id = #__bsms_studytopics.topic_id');
            $query->where('#__bsms_topics.published = 1');
            $query->order('#__bsms_topics.topic_text ASC');
            $db->setQuery($query->__toString());
            $db_result = $db->loadObjectList();

            $output = array();
            foreach ($db_result as $i => $value) {
                $value->text = getTopicItemTranslated($value);

                $value->value = $value->id;
                $output[] = $value;
            }

            $this->_Topics = $output;
        }
        return $this->_Topics;
    }

    /**
     * Get a list of all used books
     * @since 7.0
     */
    public function getBooks() {

        $template = $this->getTemplate();
        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($template->params);
        $params = $registry;

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('book.booknumber AS value, book.bookname AS text, book.id');
        $query->from('#__bsms_books AS book');
        if ($params->get('booklist') == 1):
            $query->join('INNER', '#__bsms_studies AS study ON study.booknumber = book.booknumber');
        endif;
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
     * Get a list of all used teachers
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
     * Get a list of all used series
     * @since 7.0
     */
    public function getSeries() {
        $db = $this->getDbo();
        $user = JFactory::getUser();
        $groups = implode(',', $user->getAuthorisedViewLevels());

        $query = $db->getQuery(true);

        $query->select('series.id AS value, series.series_text AS text, series.access');
        $query->from('#__bsms_series AS series');
        $query->join('INNER', '#__bsms_studies AS study ON study.series_id = series.id');
        $query->group('series.id');

        //filter only for authorized view
        $query->where('series.access IN (' . $groups . ')');
        $query->order('series.series_text');

        $db->setQuery($query->__toString());
        return $db->loadObjectList();
    }

    /**
     * Get a list of all used message types
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
     * Get a list of all used years
     * @since 7.0
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

    /**
     * Get the number of plays of this study
     * @param int $id
     * @since 7.0
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

    /**
     * Returns the Template to display the list
     * @return Array
     * @since 7.0.2
     */
    public function getTemplate() {
        if (empty($this->_template)) {
            $templateid = JRequest::getVar('t', 1, 'get', 'int');
            $db = $this->getDBO();
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__bsms_templates');
            $query->where('published = 1 AND id = ' . $templateid);
            $db->setQuery($query->__toString());
            $this->_template = $db->loadObject();
        }
        return $this->_template;
    }

    /**
     * Returns the Admin settings to display the list
     * @return Array
     * @since 7.0.2
     */
    public function getAdmin() {
        if (empty($this->_admin)) {
            $db = $this->getDBO();
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__bsms_admin');
            $query->where('id = 1');
            $db->setQuery($query->__toString());
            $this->_admin = $db->loadObject();
        }
        return $this->_admin;
    }

    /**
     * Returns the locations
     * @return Array
     * @since 7.0.2
     */
    public function getLocations() {
        if (empty($this->_Locations)) {
            $db = $this->getDBO();
            $query = $db->getQuery(true);
            $query->select('id AS value, location_text as text, published');
            $query->from('#__bsms_locations');
            $query->where('published = 1');
            $query->order('location_text ASC');
            $db->setQuery($query->__toString());
            $this->_Locations = $db->loadObjectList();
        }
        return $this->_Locations;
    }

    /**
     * Get Start 2
     * @return string
     */
    public function getStart2() {
        return $this->getState('list.start');
    }

    /**
     * Get Downloads
     * @todo Need to see if we can use this out of a helper to reduce code.
     * @param int $id
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
     * Creates and executes a new query that retrieves the medifile information from the mediafiles table.
     * It then adds to the dataObject the mediafiles associated with the sermon.
     * @return unknown_type
     */
    public function getFiles() {
        /* @todo Tom commented this out because it caused the query to fail - needs work. */
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
     * Method to get the total number of studies items
     *
     * @access public
     * @return integer
     */
    public function getTotal() {
        // Lets load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_getListQuery();
            $this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }

}
