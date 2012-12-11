<?php

/**
 * Message Model
 *
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
include_once (JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'translated.php');

/**
 * Model class for Messages
 *
 * @package BibleStudy.Site
 * @since   7.0.0
 * @todo    need to redo model to Joomla Standers.
 */
class biblestudyModelMessages extends JModelList
{

	/**
	 * Data
	 *
	 * @var array
	 */
	var $_data;

	/**
	 * Total
	 *
	 * @var array
	 */
	var $_total = null;

	/**
	 * Pagination
	 *
	 * @var array
	 */
	var $_pagination = null;

	/**
	 * Files
	 *
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
	function __construct($config = array())
	{
		parent::__construct($config);

		$mainframe = JFactory::getApplication();
		$input     = new JInput;
		$option    = $input->get('option', '', 'cmd');

		// Get the pagination request variables
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');

		$limitstart = $mainframe->getUserStateFromRequest('com_biblestudy&view=messages.limitstart', 'limitstart', 0, 'int');


		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Get Downloads
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	function getDownloads($id)
	{
		$query  = ' SELECT SUM(downloads) AS totalDownloads FROM #__bsms_mediafiles WHERE study_id = ' . $id . ' GROUP BY study_id';
		$result = $this->_getList($query);
		if (!$result) {
			$result = '0';

			return $result;
		}

		return $result[0]->totalDownloads;
	}

	/**
	 * Get Plays
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	function getPlays($id)
	{
		$query  = ' SELECT SUM(plays) AS totalPlays FROM #__bsms_mediafiles WHERE study_id = ' . $id . ' GROUP BY study_id';
		$result = $this->_getList($query);
		if (!$result) {
			$result = '0';

			return $result;
		}

		return $result[0]->totalPlays;
	}

	/**
	 * Creates and executes a new query that retrieves the medifile information from the mediafiles table.
	 * It then adds to the dataObject the mediafiles associated with the sermon.
	 *
	 * @return unknown_type
	 */
	function getFiles()
	{
		/*@todo Tom commented this out because it caused the query to fail - needs work. */
		$mediaFiles = null;
		$db         = JFactory::getDBO();
		$i          = 0;
		foreach ($this->_data as $sermon) {
			$i++;
			$sermon_id = $sermon->id;
			$query     = 'SELECT study_id, filename, #__bsms_folders.folderpath, #__bsms_servers.server_path'
					. ' FROM #__bsms_mediafiles'
					. ' LEFT JOIN #__bsms_servers ON (#__bsms_mediafiles.server = #__bsms_servers.id)'
					. ' LEFT JOIN #__bsms_folders ON (#__bsms_mediafiles.path = #__bsms_folders.id)'
					. ' WHERE `study_id` ='
					. $sermon_id;
			$db->setQuery($query);
			$mediaFiles[$sermon->id] = $db->loadAssocList();
		}
		$this->_files = $mediaFiles;

		return $this->_files;
	}

	/**
	 * Method to get a pagination object for the studies
	 *
	 * @access public
	 * @return integer
	 */
	function legacyGetPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Build Content Where
	 *
	 * @return string
	 */
	function _buildContentWhere()
	{
		$mainframe = JFactory::getApplication();
		$input     = new JInput;
		$option    = $input->get('option', '', 'cmd');

		$filter_book        = $mainframe->getUserStateFromRequest($option . 'filter_book', 'filter_book', 0, 'int');
		$filter_teacher     = $mainframe->getUserStateFromRequest($option . 'filter_teacher', 'filter_teacher', 0, 'int');
		$filter_series      = $mainframe->getUserStateFromRequest($option . 'filter_series', 'filter_series', 0, 'int');
		$filter_messagetype = $mainframe->getUserStateFromRequest($option . 'filter_messagetype', 'filter_messagetype', 0, 'int');
		$filter_year        = $mainframe->getUserStateFromRequest($option . 'filter_year', 'filter_year', 0, 'int');
		$filter_orders      = $mainframe->getUserStateFromRequest($option . 'filter_orders', 'filter_orders', 'DESC', 'word');


		$where = array();

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
			$where[] = " YEAR(#__bsms_studies.studydate)= " . (int) $filter_year;
		}

		$where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');

		return $where;
	}

	/**
	 * Build Content Order By
	 *
	 * @return string
	 */
	function _buildContentOrderBy()
	{
		$mainframe = JFactory::getApplication();
		$input     = new JInput;
		$option    = $input->get('option', '', 'cmd');

		$orders           = array(
			'id',
			'published',
			'studydate',
			'messagetype',
			'teacher_id',
			'studytitle',
			'series_id'
		);
		$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'ordering', 'cmd');
		$filter_order_Dir = strtoupper($mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'ASC'));
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
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// List state information
		$value = $app->input->get('limit', $app->getCfg('list_limit', 0), 'uint');
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$orderCol = $app->input->get('filter_order', 'a.ordering');
		if (!in_array($orderCol, $this->filter_fields)) {
			$orderCol = 'a.ordering';
		}
		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->input->get('filter_order_Dir', 'ASC');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);

		$params = $app->getParams();
		$this->setState('params', $params);
		$user		= JFactory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_biblestudy')) &&  (!$user->authorise('core.edit', 'com_biblestudy'))){
		// filter on published for those who do not have edit or edit.state rights.
		$this->setState('filter.published', 1);
	}

		$this->setState('filter.language', $app->getLanguageFilter());

		// process show_noauth parameter
		if (!$params->get('show_noauth')) {
			$this->setState('filter.access', true);
		}
		else {
			$this->setState('filter.access', false);
		}

		$this->setState('layout', $app->input->get('layout'));

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
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select', 'study.id, study.published, study.studydate, study.studytitle, study.booknumber, study.chapter_begin,
                        study.verse_begin, study.chapter_end, study.verse_end, study.hits, study.language'));
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

		//Filter by Year?
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

		// Filter by language
		if ($this->getState('filter.language')) {
			$query->where('study.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');
		}

		// Add the list ordering clause.
		$query->order($this->getState('list.ordering', 'study.id').' '.$this->getState('list.direction', 'ASC'));

		return $query;
	}

	/**
	 * Get a list of all used books
	 *
	 * @since 7.0
	 */
	public function getBooks()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('book.booknumber AS value, book.bookname AS text');
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
	 * Get a list of all used teachers
	 *
	 * @since 7.0
	 */
	public function getTeachers()
	{
		$db    = $this->getDbo();
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
	 *
	 * @since 7.0
	 */
	public function getSeries()
	{
		$db    = $this->getDbo();
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
	 * Get a list of all used message types
	 *
	 * @since 7.0
	 */
	public function getMessageTypes()
	{
		$db    = $this->getDbo();
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
	 *
	 * @since 7.0
	 */
	public function getYears()
	{
		$db    = $this->getDBO();
		$query = $db->getQuery(true);

		$query->select('DISTINCT YEAR(studydate) as value, YEAR(studydate) as text');
		$query->from('#__bsms_studies');
		$query->order('value');

		$db->setQuery($query->__toString());
		$year = $db->loadObjectList();

		return $year;
	}

}