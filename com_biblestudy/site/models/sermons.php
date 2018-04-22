<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Model class for Sermons
 *
 * @property array _Topics
 * @property mixed _total
 * @property mixed _data
 * @property null  _files
 * @property mixed _Locations
 * @property int   landing
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyModelSermons extends JModelList
{
	public $input;

	/** @var string Needed for context for Populate State
	 * @since 9.0.14 */
	public $context = 'com_biblestudy.sermons.list';

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
				'seriesid', 'study.series_id',
				'hits', 'study.hits',
				'access', 'series.access', 'access_level',
				'location', 'location.location_text',
				'bookname2', 'book.bookname2',
				'language', 'study.language'
			);
		}

		$this->input = new JInput;

		parent::__construct($config);
	}

	/**
	 * Translate item entries: books, topics
	 *
	 * @param   array  $items  Books
	 *
	 * @return array
	 *
	 * @since 7.0
	 */
	public function getTranslated($items = array())
	{
		foreach ($items as $item)
		{
			$item->bookname   = JText::_($item->bookname);
			$item->topic_text = JBSMTranslated::getTopicItemTranslated($item);
			$item->bookname2   = JText::_($item->bookname2);
			$item->topic_text = JBSMTranslated::getTopicItemTranslated($item);
		}

		return $items;
	}

	/**
	 * Get Downloads
	 *
	 * @param   int  $id  ID of Download
	 *
	 * @return string
	 *
	 * @since 7.0
	 * @todo Need to see if we can use this out of a helper to reduce code.
	 */
	public function getDownloads($id)
	{
		$query = $this->_db->getQuery(true);
		$query->select('SUM(downloads) AS totalDownloads')->from('#__bsms_mediafiles')->where('study_id = ' . $id)->group('study_id');
		$result = $this->_getList($query);

		if (!$result)
		{
			$result = '0';

			return $result;
		}

		return $result[0]->totalDownloads;
	}

	/**
	 * Creates and executes a new query that retrieves the medifile information from the mediafiles table.
	 * It then adds to the dataObject the mediafiles associated with the sermon.
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	public function getFiles()
	{
		$mediaFiles = null;
		$db         = JFactory::getDbo();
		$i          = 0;

		foreach ($this->_data as $sermon)
		{
			$i++;
			$sermon_id = $sermon->id;
			$query     = $db->getQuery(true);
			$query->select('study_id, filename, #__bsms_servers.server_path')
				->from('#__bsms_mediafiles')
				->leftJoin('#__bsms_servers ON (#__bsms_mediafiles.server = #__bsms_servers.id)')
				->where('study_id` = ' . $sermon_id);
			$db->setQuery($query);
			$mediaFiles[$sermon->id] = $db->loadAssocList();
		}

		$this->_files = $mediaFiles;

		return $this->_files;
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
	 * @throws  Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		/** @type JApplicationSite $app */
		$app = JFactory::getApplication();

		// Load the parameters.
		$params   = $app->getParams();

		$template = JBSMParams::getTemplateparams();
		$admin    = JBSMParams::getAdmin();

		$template->params->merge($params);
		$template->params->merge($admin->params);
		$params = $template->params;
		$this->setState('params', $params);
		$t = $params->get('sermonsid');

		if (!$t)
		{
			$t     = $this->input->get('t', 1, 'int');
		}

		$landing = 0;
		$this->landing = 0;
		$landingcheck = $this->input->get->get('sendingview');

		if ($landingcheck == 'landing')
		{
			$landing = 1;
			$this->landing = 1;
			$this->setState('sendingview', '');
			$this->input->set('sendingview', '');
		}
		else
		{
			$this->setState('filter.book', 0);
			$this->setState('filter.teacher', 0);
			$this->setState('filter.series', 0);
			$this->setState('filter.messageType', 0);
			$this->setState('filter.year', 0);
			$this->setState('filter.topic', 0);
			$this->setState('filter.location', 0);
			$this->setState('filter.landingbook', 0);
			$this->setState('filter.landingteacher', 0);
			$this->setState('filter.landingseries', 0);
			$this->setState('filter.landingmessageType', 0);
			$this->setState('filter.landingyear', 0);
			$this->setState('filter.landingtopic', 0);
			$this->setState('filter.landinglocation', 0);
			$this->landing = 0;
		}

		$template->id = $t;
		$this->setState('template', $template);
		$this->setState('admin', $admin);

		$this->setState('filter.language', JLanguageMultilang::isEnabled());

		$studytitle = $this->getUserStateFromRequest($this->context . '.filter.studytitle', 'filter_studytitle');
		$this->setState('filter.studytitle', $studytitle);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$books = $book = $this->getUserStateFromRequest($this->context . '.filter.book', 'filter_book');

		if ($landing == 1 && $books !== 0)
		{
			$book = $this->getUserStateFromRequest($this->context . '.filter.landingbook', 'filter_book_landing');
		}

		$this->setState('filter.book', $book);
		$this->setState('filter.landingbook', $book);

		$teacher = $this->getUserStateFromRequest($this->context . '.filter.teacher', 'filter_teacher');

		if ($landing == 1 && $teacher !== 0)
		{
			$teacher = $this->getUserStateFromRequest($this->context . '.filter.landingteacher', 'filter_teacher_landing');
		}

		$this->setState('filter.teacher', $teacher);
		$this->setState('filter.landingteacher', $teacher);

		$series = $this->getUserStateFromRequest($this->context . '.filter.series', 'filter_series');

		if ($landing == 1 && $series !== 0)
		{
			$series = $this->getUserStateFromRequest($this->context . '.filter.landingseries', 'filter_series_landing');
		}

		$this->setState('filter.series', $series);
		$this->setState('filter.landingseries', $series);

		$messageType = $this->getUserStateFromRequest($this->context . '.filter.messageType', 'filter_messagetype');

		if ($landing == 1 && $messageType !== 0)
		{
			$messageType = $this->getUserStateFromRequest($this->context . '.filter.landingmessagetype', 'filter_messagetype_landing');
		}

		$this->setState('filter.messageType', $messageType);
		$this->setState('filter.landingmessagetype', $messageType);

		$year = $this->getUserStateFromRequest($this->context . '.filter.year', 'filter_year');

		if ($landing == 1 && $year !== 0)
		{
			$year = $this->getUserStateFromRequest($this->context . '.filter.landingyear', 'filter_year_landing');
		}

		$this->setState('filter.year', $year);
		$this->setState('filter.landingyear', $year);

		$topic = $this->getUserStateFromRequest($this->context . '.filter.topic', 'filter_topic');

		if ($landing == 1 && $topic !== 0)
		{
			$topic = $this->getUserStateFromRequest($this->context . '.filter.landingtopic', 'filter_topic_landing');
		}

		$this->setState('filter.topic', $topic);
		$this->setState('filter.landingtopic', $topic);

		$location = $this->getUserStateFromRequest($this->context . '.filter.location', 'filter_location');

		if ($landing == 1 && $location !== 0)
		{
			$location = $this->getUserStateFromRequest($this->context . '.filter.landinglocation', 'filter_location_landing');
		}

		$orderCol = $app->input->get('filter_order');

		if (!in_array($orderCol, $this->filter_fields) && !empty($orderCol))
		{
			$orderCol = 'study.studydate';
		}

		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->input->get('filter_order_Dir');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')) && !empty($listOrder))
		{
			$listOrder = 'DESC';
		}

		$this->setState('list.direction', $listOrder);

		$this->setState('list.direction', $listOrder);

		$this->setState('filter.location', $location);
		$this->setState('filter.landinglocation', $location);

		parent::populateState($ordering, $direction);
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
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.published'));
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
	 * Build an SQL query to load the list data
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   7.0
	 * @throws  Exception
	 */
	protected function getListQuery()
	{
		$user            = JFactory::getUser();
		$groups          = implode(',', $user->getAuthorisedViewLevels());
		$db              = $this->getDbo();
		$query           = parent::getListQuery();
		$query->select(
			$this->getState(
				'list.select', 'study.id, study.published, study.studydate, study.studytitle, study.booknumber, study.chapter_begin,
		                study.verse_begin, study.chapter_end, study.verse_end, study.hits, study.alias, study.studyintro,
		                study.teacher_id, study.secondary_reference, study.booknumber2, study.location_id, ' .
				// Use created if modified is 0
				'CASE WHEN study.modified = ' . $db->quote($db->getNullDate()) . ' THEN study.studydate ELSE study.modified END as modified, ' .
				'study.modified_by, uam.name as modified_by_name,' .
				// Use created if publish_up is 0
				'CASE WHEN study.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN study.studydate ELSE study.publish_up END as publish_up,' .
				'study.publish_down,
		                study.series_id, study.download_id, study.thumbnailm, study.thumbhm, study.thumbwm,
		                study.access, study.user_name, study.user_id, study.studynumber, study.chapter_begin2, study.chapter_end2,
		                study.verse_end2, study.verse_begin2, ' . ' ' . $query->length('study.studytext') . ' AS readmore') . ','
			. ' CASE WHEN CHAR_LENGTH(study.alias) THEN CONCAT_WS(\':\', study.id, study.alias) ELSE study.id END as slug ');
		$query->from('#__bsms_studies AS study');

		// Join over Message Types
		$query->select('messageType.message_type AS message_type');
		$query->join('LEFT', '#__bsms_message_type AS messageType ON messageType.id = study.messagetype');

		// Join over Teachers
		$query->select('teacher.teachername AS teachername, teacher.title as title, teacher.teacher_thumbnail as thumb,
			teacher.thumbh, teacher.thumbw');
		$query->join('LEFT', '#__bsms_teachers AS teacher ON teacher.id = study.teacher_id');

		// Join over Series
		$query->select('series.series_text, series.series_thumbnail, series.description as sdescription, series.access as series_access');
		$query->join('LEFT', '#__bsms_series AS series ON series.id = study.series_id');

		// Join over Books
		$query->select('book.bookname');
		$query->join('LEFT', '#__bsms_books AS book ON book.booknumber = study.booknumber');

		$query->select('book2.bookname as bookname2');
		$query->join('LEFT', '#__bsms_books AS book2 ON book2.booknumber = study.booknumber2');

		// Join over Plays/Downloads
		$query->select('GROUP_CONCAT(DISTINCT mediafile.id) as mids, SUM(mediafile.plays) AS totalplays,' .
			'SUM(mediafile.downloads) as totaldownloads, mediafile.study_id');
		$query->join('LEFT', '#__bsms_mediafiles AS mediafile ON mediafile.study_id = study.id');

		// Join over Locations
		$query->select('locations.location_text');
		$query->join('LEFT', '#__bsms_locations AS locations ON study.location_id = locations.id');

		// Join over topics
		$query->select('GROUP_CONCAT(DISTINCT st.topic_id)');
		$query->join('LEFT', '#__bsms_studytopics AS st ON study.id = st.study_id');
		$query->select('GROUP_CONCAT(DISTINCT t.id), GROUP_CONCAT(DISTINCT t.topic_text) as topics_text, GROUP_CONCAT(DISTINCT t.params)');
		$query->join('LEFT', '#__bsms_topics AS t ON t.id = st.topic_id');

		// Join over the users for the author and modified_by names.
		$query->select("CASE WHEN study.user_name > ' ' THEN study.user_name ELSE users.name END AS submitted")
			->select("users.email AS author_email")
			->join('LEFT', '#__users AS users ON study.user_id = users.id')
			->join('LEFT', '#__users AS uam ON uam.id = study.modified_by');

		$query->group('study.id');

		// Filter only for authorized view
		$query->where('(series.access IN (' . $groups . ') or study.series_id <= 0)');
		$query->where('study.access IN (' . $groups . ')');

		// Select only published studies
		$query->where('study.published = 1');
		$query->where('(series.published = 1 or study.series_id <= 0)');

		// Define null and now dates
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(JFactory::getDate()->toSql());

		// Filter by start and end dates.
		if ((!$user->authorise('core.edit.state', 'com_biblestudy')) && (!$user->authorise('core.edit', 'com_biblestudy')))
		{
			$query->where('(study.publish_up = ' . $nullDate . ' OR study.publish_up <= ' . $nowDate . ')')
				->where('(study.publish_down = ' . $nullDate . ' OR study.publish_down >= ' . $nowDate . ')');
		}

		// Begin the filters for menu items
		$params      = $this->getState('params');

		$books       = null;
		$teacher     = null;
		$locations   = null;
		$messagetype = null;
		$topics      = null;
		$series      = null;
		$years       = null;

		// See if we are getting itemid
		$itemid      = $this->input->get('Itemid', '', 'int');
		$application = JFactory::getApplication();
		$menu        = $application->getMenu();
		$item        = $menu->getItem($itemid);

		// Only do this if item id is available
		if ($item != null)
		{
			$teacher     = $params->get('mteacher_id');
			$locations   = $params->get('mlocations');
			$books       = $params->get('mbooknumber');
			$series      = $params->get('mseries_id');
			$topics      = $params->get('mtopic_id');
			$messagetype = $params->get('mmessagetype');
			$years       = $params->get('years');

			// Filter over teachers
			$filters = $teacher;

			if ($filters)
			{
				if (count($filters) > 1)
				{
					$where2   = array();
					$subquery = '(';

					foreach ($filters as $filter)
					{
						$where2[] = 'study.teacher_id = ' . (int) $filter;
					}

					$subquery .= implode(' OR ', $where2);
					$subquery .= ')';

					$query->where($subquery);
				}
				else
				{
					foreach ($filters as $filter)
					{
						if ($filter >= 1)
						{
							$query->where('study.teacher_id = ' . (int) $filter);
						}
					}
				}
			}

			// Filter locations
			$filters = $locations;

			if ($filters)
			{
				if (count($filters) > 1)
				{
					$where2   = array();
					$subquery = '(';

					foreach ($filters as $filter)
					{
						$where2[] = 'study.location_id = ' . (int) $filter;
					}

					$subquery .= implode(' OR ', $where2);
					$subquery .= ')';

					$query->where($subquery);
				}
				else
				{
					foreach ($filters AS $filter)
					{
						if ($filter >= 1)
						{
							$query->where('study.location_id = ' . (int) $filter);
						}
					}
				}
			}

			// Filter over books
			$filters = $books;

			if ($filters)
			{
				if (count($filters) > 1)
				{
					$where2   = array();
					$subquery = '(';

					foreach ($filters as $filter)
					{
						$where2[] = 'study.booknumber = ' . (int) $filter;
					}

					$subquery .= implode(' OR ', $where2);
					$subquery .= ')';

					$query->where($subquery);
				}
				else
				{
					foreach ($filters AS $filter)
					{
						if ($filter >= 1)
						{
							$query->where('study.booknumber = ' . (int) $filter);
						}
					}
				}
			}

			$filters = $series;

			if ($filters)
			{
				if (count($filters) > 1)
				{
					$where2   = array();
					$subquery = '(';

					foreach ($filters as $filter)
					{
						$where2[] = 'study.series_id = ' . (int) $filter;
					}

					$subquery .= implode(' OR ', $where2);
					$subquery .= ')';

					$query->where($subquery);
				}
				else
				{
					foreach ($filters AS $filter)
					{
						if ($filter >= 1)
						{
							$query->where('study.series_id = ' . (int) $filter);
						}
					}
				}
			}

			$filters = $topics;

			if ($filters)
			{
				if (count($filters) > 1)
				{
					$where2   = array();
					$subquery = '(';

					foreach ($filters as $filter)
					{
						$where2[] = 'st.topic_id = ' . (int) $filter;
					}

					$subquery .= implode(' OR ', $where2);
					$subquery .= ')';

					$query->where($subquery);
				}
				else
				{
					foreach ($filters AS $filter)
					{
						if ($filter >= 1)
						{
							$query->where('st.topic_id = ' . (int) $filter);
						}
					}
				}
			}

			$filters = $messagetype;

			if ($filters)
			{
				if (count($filters) > 1)
				{
					$where2   = array();
					$subquery = '(';

					foreach ($filters as $filter)
					{
						$where2[] = 'study.messagetype = ' . (int) $filter;
					}

					$subquery .= implode(' OR ', $where2);
					$subquery .= ')';

					$query->where($subquery);
				}
				else
				{
					foreach ($filters AS $filter)
					{
						if ($filter >= 1)
						{
							$query->where('study.messagetype = ' . (int) $filter);
						}
					}
				}
			}

			$filters = $years;

			if ($filters)
			{
				if (count($filters) > 1)
				{
					$where2   = array();
					$subquery = '(';

					foreach ($filters as $filter)
					{
						$where2[] = 'YEAR(study.studydate) = ' . (int) $filter;
					}

					$subquery .= implode(' OR ', $where2);
					$subquery .= ')';

					$query->where($subquery);
				}
				else
				{
					foreach ($filters AS $filter)
					{
						if ($filter >= 1)
						{
							$query->where('YEAR(study.studydate) = ' . (int) $filter);
						}
					}
				}
			}
		}

		// Filter by studytitle
		$studytitle = $this->getState('filter.studytitle');

		if (!empty($studytitle))
		{
			$query->where('study.studytitle LIKE "' . $studytitle . '%"');
		}

		// Filter by book
		$book = $this->getState('filter.book');

		if ($this->landing == 1)
		{
			$book = $this->getState('filter.landingbook');
			$this->landing = 0;
		}

		if (!empty($book))
		{
			$chb   = $this->input->get('minChapt', '', 'int');
			$che   = $this->input->get('maxChapt', '', 'int');

			// Set the secondary order
			$this->setState('secondaryorderstate', 1);

			if ($chb && $che)
			{
				$query->where('(study.booknumber = ' . (int) $book .
					' AND study.chapter_begin >= ' . (int) $chb .
					' AND study.chapter_end <= ' . (int) $che . ')' .
					'OR study.booknumber2 = ' . (int) $book
				);
			}
			else
			{
				if ($chb)
				{
					$query->where('(study.booknumber = ' . (int) $book . ' AND study.chapter_begin > = ' . (int) $chb . ') OR study.booknumber2 = ' . (int) $book);
				}
				else
				{
					if ($che)
					{
						$query->where('(study.booknumber = ' . (int) $book . ' AND study.chapter_end <= ' . $che . ') OR study.booknumber2 = ' . (int) $book);
					}
					else
					{
						$query->where('(study.booknumber = ' . (int) $book . ' OR study.booknumber2 = ' . (int) $book . ')');
					}
				}
			}
		}

		// Filter by teacher
		$teacher = $this->getState('filter.teacher');

		if ($this->landing == 1)
		{
			$teacher = $this->getState('filter.landingteacher');
			$this->landing = 0;
		}

		if ($teacher >= 1)
		{
			$query->where('study.teacher_id = ' . (int) $teacher);

			// Set the secondary order
			$this->setState('secondaryorderstate', 1);
		}

		// Filter by series
		$series = $this->getState('filter.series');

		if ($this->landing == 1)
		{
			$series = $this->getState('filter.landingseries');
			$this->landing = 0;
		}

		if ($series >= 1)
		{
			$query->where('study.series_id = ' . (int) $series);

			// Set the secondary order
			$this->setState('secondaryorderstate', 1);
		}

		// Filter by message type
		$messageType = $this->getState('filter.messageType');

		if ($this->landing == 1)
		{
			$messageType = $this->getState('filter.landingmessagetype');
			$this->landing = 0;
		}

		if ($messageType >= 1)
		{
			$query->where('study.messageType = ' . (int) $messageType);

			// Set the secondary order
			$this->setState('secondaryorderstate', 1);
		}

		// Filter by Year
		$year = $this->getState('filter.year');

		if ($this->landing == 1)
		{
			$year = $this->getState('filter.landingyear');
			$this->landing = 0;
		}

		if ($year >= 1)
		{
			$query->where('YEAR(study.studydate) = ' . (int) $year);

			// Set the secondary order
			$this->setState('secondaryorderstate', 1);
		}

		// Filter by topic
		$topic = $this->getState('filter.topic');

		if ($this->landing == 1)
		{
			$topic = $this->getState('filter.landingtopic');
			$this->landing = 0;
		}

		if (!empty($topic))
		{
			$query->where('st.topic_id LIKE "%' . $topic . '%"');

			// Set the secondary order
			$this->setState('secondaryorderstate', 1);
		}

		// Filter by location
		$location = $this->getState('filter.location');

		if ($this->landing == 1)
		{
			$location = $this->getState('filter.landinglocation');
			$this->landing = 0;
		}

		if (is_numeric($location))
		{
			$query->where('study.location_id = ' . (int) $location);

			// Set the secondary order
			$this->setState('secondaryorderstate', 1);
		}

		// Filter by language
		$language = $params->get('language', '*');

		if ($this->getState('filter.languages'))
		{
			$query->where('study.language in (' . $db->quote($this->getState('filter.languages')) . ',' . $db->quote('*') . ')');
		}
		elseif ($this->getState('filter.language') || $language != '*')
		{
			$query->where('study.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.fullordering');
		$orderDirn = '';

		if (empty($orderCol) || $orderCol == " ")
		{
			$orderCol  = $this->state->get('list.ordering', 'study.studydate');
			$orderDirn = $this->state->get('list.direction', 'DESC');

			$secondaryorderstate = $this->getState('secondaryorderstate');

			if (!empty($secondaryorderstate))
			{
				$orderDirn = $params->get('default_order_secondary');
			}
		}

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return  integer  The starting number of items available in the data set.
	 *
	 * @since   12.2
	 */
	public function getStart()
	{
		return $this->getState('list.start');
	}
}
