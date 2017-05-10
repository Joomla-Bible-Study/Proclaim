<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Message model class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelMessages extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   11.1
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'study.id',
				'published', 'study.published',
				'studydate', 'study.studydate',
				'studytitle', 'study.studytitle',
				'ordering', 'study.ordering',
				'bookname', 'book.bookname',
				'teachername', 'teacher.teachername',
				'message_type', 'messageType.message_type',
				'series_text', 'series.series_text',
				'study.series_id',
				'hits', 'study.hits',
				'access', 'series.access', 'access_level',
				'locations', 'locations.location_text'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Translate item entries: books, topics
	 *
	 * @param   array  $items  Items for entries
	 *
	 * @since 7.0
	 * @return array
	 */
	public function getTranslated($items = array())
	{
		if ($items)
		{
			foreach ($items as $item)
			{
				$item->bookname = JText::_($item->bookname);
			}
		}

		return $items;
	}

	/**
	 * Method to get a list of articles.
	 * Overridden to add a check for access levels.
	 *
	 * @return    mixed    An array of data items on success, false on failure.
	 *
	 * @since    1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();
		$app   = JFactory::getApplication();

		if ($app->isClient('site'))
		{
			$user   = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();

			for ($x = 0, $count = count($items); $x < $count; $x++)
			{
				// Check the access level. Remove articles the user shouldn't see
				if (!in_array($items[$x]->access, $groups))
				{
					unset($items[$x]);
				}
			}
		}

		return $items;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   7.1.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.year');
		$id .= ':' . $this->getState('filter.book');
		$id .= ':' . $this->getState('filter.teacher');
		$id .= ':' . $this->getState('filter.series');
		$id .= ':' . $this->getState('filter.messagetype');
		$id .= ':' . $this->getState('filter.location');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return    void
	 *
	 * @since 7.1.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');

		// Adjust the context to support modal layouts.
		$layout = $app->input->get('layout');

		if ($layout)
		{
			$this->context .= '.' . $layout;
		}

		// Load the parameters.
		$params = JComponentHelper::getParams('com_biblestudy');
		$this->setState('params', $params);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$book = $this->getUserStateFromRequest($this->context . '.filter.book', 'filter_book');
		$this->setState('filter.book', $book);

		$teacher = $this->getUserStateFromRequest($this->context . '.filter.teacher', 'filter_teacher');
		$this->setState('filter.teacher', $teacher);

		$series = $this->getUserStateFromRequest($this->context . '.filter.series', 'filter_series');
		$this->setState('filter.series', $series);

		$messageType = $this->getUserStateFromRequest($this->context . '.filter.messagetype', 'filter_messagetype');
		$this->setState('filter.messagetype', $messageType);

		$year = $this->getUserStateFromRequest($this->context . '.filter.year', 'filter_year');
		$this->setState('filter.year', $year);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$location = $this->getUserStateFromRequest($this->context . 'filter.location', 'filter_location');
		$this->setState('filter.location', $location);

		parent::populateState('study.studydate', 'desc');

		// Force a language
		$forcedLanguage = $app->input->get('forcedLanguage');

		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}
	}

	/**
	 * Build an SQL query to load the list data
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   7.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$user  = JFactory::getUser();

		$query->select(
			$this->getState(
				'list.select',
				'study.id, study.published, study.studydate, study.studytitle, study.booknumber, study.chapter_begin,
                        study.verse_begin, study.chapter_end, study.verse_end, study.ordering, study.hits, study.alias, study.language, study.access'
			)
		);
		$query->from('#__bsms_studies AS study');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = study.language');

		// Join over Message Types
		$query->select('messageType.message_type AS messageType');
		$query->join('LEFT', '#__bsms_message_type AS messageType ON messageType.id = study.messagetype');

		// Join over Teachers
		$query->select('teacher.teachername AS teachername');
		$query->join('LEFT', '#__bsms_teachers AS teacher ON teacher.id = study.teacher_id');

		// Join over Series
		$query->select('series.series_text, series.id AS series_id');
		$query->join('LEFT', '#__bsms_series AS series ON series.id = study.series_id');

		// Join over Location
		$query->select('locations.location_text');
		$query->join('LEFT', '#__bsms_locations AS locations ON locations.id = study.location_id');

		// Join over Books
		$query->select('book.bookname');
		$query->join('LEFT', '#__bsms_books AS book ON book.booknumber = study.booknumber');

		// Join over Plays/Downloads
		$query->select(
			'SUM(mediafile.plays) AS totalplays, SUM(mediafile.downloads) as totaldownloads, mediafile.study_id'
		);
		$query->join('LEFT', '#__bsms_mediafiles AS mediafile ON mediafile.study_id = study.id');
		$query->group('study.id');

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('study.access = ' . (int) $access);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('study.access IN (' . $groups . ')');
		}

		// Filter by teacher
		$teacher = $this->getState('filter.teacher');

		if (is_numeric($teacher))
		{
			$query->where('study.teacher_id = ' . (int) $teacher);
		}

		// Filter by series
		$series = $this->getState('filter.series');

		if (is_numeric($series))
		{
			$query->where('study.series_id = ' . (int) $series);
		}

		// Filter by message type
		$messageType = $this->getState('filter.messageType');

		if (is_numeric($messageType))
		{
			$query->where('study.messageType = ' . (int) $messageType);
		}

		// Filter by Year
		$year = $this->getState('filter.year');

		if (!empty($year))
		{
			$query->where('YEAR(study.studydate) = ' . (int) $year);
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('study.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(study.published = 0 OR study.published = 1)');
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('study.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(study.studytitle LIKE ' . $search . ' OR study.alias LIKE ' . $search . ')');
			}
		}

		// Filter by book
		$book = $this->getState('filter.book');

		if (is_numeric($book))
		{
			$query->where('(study.booknumber = ' . (int) $book . ' OR study.booknumber2 = ' . (int) $book . ')');
		}

		// Filter by location
		$location = $this->getState('filter.location');

		if (is_numeric($location))
		{
			$query->where('study.location_id = ' . (int) $location);
		}

		// Add the list ordering clause
		$orderCol  = $this->state->get('list.ordering', 'study.studydate');
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}
}
