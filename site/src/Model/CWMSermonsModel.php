<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Model;

use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use CWM\Component\Proclaim\Administrator\Helper\CWMTranslated;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;
use Joomla\Input\Input;

// No Direct Access
defined('_JEXEC') or die;

/**
 * Model class for Sermons
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class CWMSermonsModel extends ListModel
{
	/**
	 * @var   Input
	 *
	 * @since 7.0.0
	 */
	public $input;

	/** @var string Needed for context for Populate State
	 * @since 9.0.14
	 */
	public $context = 'com_proclaim.sermons.list';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws \Exception
	 * @since   11.1
	 * @see     JController
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

		$this->input = Factory::getApplication();

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
			$item->bookname   = Text::_($item->bookname);
			$item->topic_text = CWMTranslated::getTopicItemTranslated($item);
			$item->bookname2  = Text::_($item->bookname2);
			$item->topic_text = CWMTranslated::getTopicItemTranslated($item);
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
	 * @todo  Need to see if we can use this out of a helper to reduce code.
	 */
	public function getDownloads($id)
	{
		$query = $this->_db->getQuery(true);
		$query->select('SUM(downloads) AS totalDownloads')
			->from('#__bsms_mediafiles')
			->where('study_id = ' . $id)
			->group('study_id');
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
	 * @return array
	 *
	 * @since 7.0
	 */
	public function getFiles()
	{
		$mediaFiles = null;
		$db         = $this->getDbo();
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
	 * @throws  \Exception
	 * @since   11.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication();

		$forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

		// Load the parameters.
		$params = $app->getParams();

		$template = CWMParams::getTemplateparams();
		$admin    = CWMParams::getAdmin();

		$template->params->merge($params);
		$template->params->merge($admin->params);
		$params = $template->params;
		$this->setState('params', $params);
		$t = $params->get('sermonsid');

		if (!$t)
		{
			$t = $this->input->get('t', 1, 'int');
		}

		$landing       = 0;
		$this->landing = 0;
		$landingcheck  = $this->input->get('sendingview');

		if ($landingcheck === 'landing')
		{
			$landing       = 1;
			$this->landing = 1;
			$this->setState('sendingview', '');
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
		$this->setState('administrator', $admin);

		$this->setState('filter.language', Multilanguage::isEnabled());

		$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
		$this->setState('filter.level', $level);

		$book = $this->getUserStateFromRequest($this->context . '.filter.book', 'filter_book');

		if ($landing === 1 && $book !== 0)
		{
			$book = $this->getUserStateFromRequest($this->context . '.filter.landingbook', 'filter_book_landing');
		}

		$this->setState('filter.book', $book);
		$this->setState('filter.landingbook', $book);

		$teacher = $this->getUserStateFromRequest($this->context . '.filter.teacher', 'filter_teacher');

		if ($landing === 1 && $teacher !== 0)
		{
			$teacher = $this->getUserStateFromRequest($this->context . '.filter.landingteacher', 'filter_teacher_landing');
		}

		$this->setState('filter.teacher', $teacher);
		$this->setState('filter.landingteacher', $teacher);

		$series = $this->getUserStateFromRequest($this->context . '.filter.series', 'filter_series');

		if ($landing === 1 && $series !== 0)
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

		if ($landing === 1 && $year !== 0)
		{
			$year = $this->getUserStateFromRequest($this->context . '.filter.landingyear', 'filter_year_landing');
		}

		$this->setState('filter.year', $year);
		$this->setState('filter.landingyear', $year);

		$topic = $this->getUserStateFromRequest($this->context . '.filter.topic', 'filter_topic');

		if ($landing === 1 && $topic !== 0)
		{
			$topic = $this->getUserStateFromRequest($this->context . '.filter.landingtopic', 'filter_topic_landing');
		}

		$this->setState('filter.topic', $topic);
		$this->setState('filter.landingtopic', $topic);

		$location = $this->getUserStateFromRequest($this->context . '.filter.location', 'filter_location');

		if ($landing === 1 && $location !== 0)
		{
			$location = $this->getUserStateFromRequest($this->context . '.filter.landinglocation', 'filter_location_landing');
		}

		$orderCol = $app->input->get('filter_order');

		if (!empty($orderCol) && !in_array($orderCol, $this->filter_fields, true))
		{
			$orderCol = 'study.studydate';
		}

		$this->setState('list.ordering', $orderCol);

		// From landing page filter passing
		$listOrder = $app->input->get('filter_order_Dir');

		if (!empty($listOrder) && !in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$direction = 'DESC';
		}

		$this->setState('list.direction', $direction);

		$this->setState('filter.location', $location);
		$this->setState('filter.landinglocation', $location);

		parent::populateState($ordering, $direction);

		// Force a language
		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}
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
	 * @return  DatabaseQuery
	 *
	 * @throws  \Exception
	 * @since   7.0
	 */
	protected function getListQuery()
	{
		$user   = Factory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$db     = $this->getDbo();
		$query  = parent::getListQuery();
		$query->select(
			$this->getState(
				'list.select', 'study.id, study.published, study.studydate, study.studytitle, study.booknumber, study.chapter_begin,
		                study.verse_begin, study.chapter_end, study.verse_end, study.hits, study.alias, study.studyintro,
		                study.teacher_id, study.secondary_reference, study.booknumber2, study.location_id, study.studytext, ' .
				// Use created if modified is 0
				'CASE WHEN study.modified = ' . $db->quote($db->getNullDate()) . ' THEN study.studydate ELSE study.modified END as modified, ' .
				'study.modified_by, uam.name as modified_by_name,' .
				// Use created if publish_up is 0
				'CASE WHEN study.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN study.studydate ELSE study.publish_up END as publish_up,' .
				'study.publish_down,
		                study.series_id, study.download_id, study.thumbnailm, study.thumbhm, study.thumbwm,
		                study.access, study.user_name, study.user_id, study.studynumber, study.chapter_begin2, study.chapter_end2,
		                study.verse_end2, study.verse_begin2, ' . ' ' . $query->length('study.studytext') . ' AS readmore'
			) . ', CASE WHEN CHAR_LENGTH(study.alias) THEN CONCAT_WS(\':\', study.id, study.alias) ELSE study.id END as slug '
		);
		$query->from('#__bsms_studies AS study');

		// Join over Message Types
		$query->select('messageType.message_type AS message_type');
		$query->join('LEFT', '#__bsms_message_type AS messageType ON messageType.id = study.messagetype');

		// Join over Teachers
		$query->select('teacher.teachername AS teachername, teacher.title as title, teacher.teacher_thumbnail as thumb,
			teacher.thumbh, teacher.thumbw'
		);
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
			'SUM(mediafile.downloads) as totaldownloads, mediafile.study_id'
		);
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
		$nowDate  = $db->quote(Factory::getDate()->toSql());

		// Filter by start and end dates.
		if ((!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise('core.edit', 'com_proclaim')))
		{
			$query->where('(study.publish_up = ' . $nullDate . ' OR study.publish_up <= ' . $nowDate . ')')
				->where('(study.publish_down = ' . $nullDate . ' OR study.publish_down >= ' . $nowDate . ')');
		}

		// Begin the filters for menu items
		$params = $this->getState('params');

		$filters_group = [];

		// Teacher ID
		if (!is_null($params->get('mteacher_id')) && $params->get('mteacher_id')[0] !== '-1' && empty($this->getState('filter.teacher')))
		{
			$filters_group[] = ['study.teacher_id' => $params->get('mteacher_id')];
		}
		elseif (!is_null($params->get('mteacher_id')) && $params->get('mteacher_id')[0] !== '-1' && !empty($this->getState('filter.teacher')))
		{
			$filters_group[] = ['study.teacher_id' => $params->get('mteacher_id')];
			$filters_group[] = ['study.teacher_id' => [$this->getState('filter.teacher')]];
		}
		elseif (!empty($this->getState('filter.teacher')))
		{
			$filters_group[] = ['study.teacher_id' => [$this->getState('filter.teacher')]];
		}

		// Location ID
		if (!is_null($params->get('mlocations')) && $params->get('mlocations')[0] !== '-1' && empty($this->getState('filter.location')))
		{
			$filters_group[] = ['study.location_id' => $params->get('mlocations')];
		}
		elseif (!is_null($params->get('mlocations')) && $params->get('mlocations')[0] !== '-1' && !empty($this->getState('filter.location')))
		{
			$filters_group[] = ['study.location_id' => $params->get('mlocations')];
			$filters_group[] = ['study.location_id' => [$this->getState('filter.location')]];
		}
		elseif (!empty($this->getState('filter.location')))
		{
			$filters_group[] = ['study.location_id' => [$this->getState('filter.location')]];
		}

		// Book Number ID
		if (!is_null($params->get('mbooknumber')) && $params->get('mbooknumber')[0] !== '-1' && empty($this->getState('filter.book')))
		{
			$filters_group[] = ['study.booknumber' => $params->get('mbooknumber')];
		}
		elseif (!is_null($params->get('mbooknumber')) && $params->get('mbooknumber')[0] !== '-1' && !empty($this->getState('filter.book')))
		{
			$filters_group[] = ['study.booknumber' => $params->get('mbooknumber')];
			$filters_group[] = ['study.booknumber' => [$this->getState('filter.book')]];
		}
		elseif (!empty($this->getState('filter.book')))
		{
			$filters_group[] = ['study.booknumber' => [$this->getState('filter.book')]];
		}

		// Series ID
		if (!is_null($params->get('mseries_id')) && $params->get('mseries_id')[0] !== '-1' && empty($this->getState('filter.series')))
		{
			$filters_group[] = ['study.series_id' => $params->get('mseries_id')];
		}
		elseif (!is_null($params->get('mseries_id')) && $params->get('mseries_id')[0] !== '-1' && !empty($this->getState('filter.series')))
		{
			$filters_group[] = ['study.series_id' => $params->get('mseries_id')];
			$filters_group[] = ['study.series_id' => [$this->getState('filter.series')]];
		}
		elseif (!empty($this->getState('filter.series')))
		{
			$filters_group[] = ['study.series_id' => [$this->getState('filter.series')]];
		}

		// Topic ID
		if (!is_null($params->get('mtopic_id')) && $params->get('mtopic_id')[0] !== '-1' && empty($this->getState('filter.topic')))
		{
			$filters_group[] = ['st.topic_id' => $params->get('mtopic_id')];
		}
		elseif (!is_null($params->get('mtopic_id')) && $params->get('mtopic_id')[0] !== '-1' && !empty($this->getState('filter.topic')))
		{
			$filters_group[] = ['st.topic_id' => $params->get('mtopic_id')];
			$filters_group[] = ['st.topic_id' => [$this->getState('filter.topic')]];
		}
		elseif (!empty($this->getState('filter.topic')))
		{
			$filters_group[] = ['st.topic_id' => [$this->getState('filter.topic')]];
		}

		// Message Type ID
		if (!is_null($params->get('mmessagetype')) && $params->get('mmessagetype')[0] !== '-1' && empty($this->getState('filter.messagetype')))
		{
			$filters_group[] = ['study.messagetype' => $params->get('mmessagetype')];
		}
		elseif (!is_null($params->get('mmessagetype'))
			&& $params->get('mmessagetype')[0] !== '-1'
			&& !empty($this->getState('filter.messagetype'))
		)
		{
			$filters_group[] = ['study.messagetype' => $params->get('mmessagetype')];
			$filters_group[] = ['study.messagetype' => [$this->getState('filter.messagetype')]];
		}
		elseif (!empty($this->getState('filter.messagetype')))
		{
			$filters_group[] = ['study.messagetype' => [$this->getState('filter.messagetype')]];
		}

		// Year ID
		if (!is_null($params->get('years')) && $params->get('years')[0] !== '-1' && empty($this->getState('filter.year')))
		{
			$filters_group[] = ['YEAR(study.studydate)' => $params->get('years')];
		}
		elseif (!is_null($params->get('years')) && $params->get('years')[0] !== '-1' && !empty($this->getState('filter.year')))
		{
			$filters_group[] = ['YEAR(study.studydate)' => [$this->getState('filter.year')]];
			$filters_group[] = ['YEAR(study.studydate)' => $params->get('years')];
		}
		elseif (!empty($this->getState('filter.year')))
		{
			$filters_group[] = ['YEAR(study.studydate)' => [$this->getState('filter.year')]];
		}

		// Work through each filter decelerations
		foreach ($filters_group as $filters)
		{
			if (is_array($filters))
			{
				// Work through the menu filters or search filters
				foreach ($filters as $filter => $filtervalue)
				{
					if (count($filtervalue) > 1)
					{
						$where2   = array();
						$subquery = '(';

						foreach ($filtervalue as $filterid)
						{
							$where2[] = $filter . ' = ' . (int) $filterid;
						}

						$subquery .= implode(' OR ', $where2);
						$subquery .= ')';

						$query->where($subquery);
					}
					else
					{
						foreach ($filtervalue as $filterid)
						{
							if ((int) $filterid >= 1 && $filter !== 'study.booknumber')
							{
								if ((int) $this->landing === 1)
								{
									$$filterid = $this->getState($filter);
								}

								$query->where($filter . ' = ' . (int) $filterid);
							}

							if ((int) $filterid >= 1 && $filter === 'study.booknumber')
							{
								$book = $filterid;
								$chb  = $this->input->get('minChapt', '', 'int');
								$che  = $this->input->get('maxChapt', '', 'int');

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
										$query->where('(study.booknumber = ' . (int) $book . ' AND study.chapter_begin > = ' .
											(int) $chb . ') OR study.booknumber2 = ' . (int) $book
										);
									}
									else
									{
										if ($che)
										{
											$query->where('(study.booknumber = ' . (int) $book . ' AND study.chapter_end <= ' .
												$che . ') OR study.booknumber2 = ' . (int) $book
											);
										}
										else
										{
											$query->where('(study.booknumber = ' . (int) $book . ' OR study.booknumber2 = ' . (int) $book . ')');
										}
									}
								}
							}
						}
					}
				}
			}
		}

		// Filter by language
		$language = $params->get('language', '*');

		if ($this->getState('filter.languages'))
		{
			$query->where('study.language in (' . $db->quote($this->getState('filter.languages')) . ',' . $db->quote('*') . ')');
		}
		elseif ($language !== '*' || $this->getState('filter.language'))
		{
			$query->where('study.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		// Adding in search strings
		// Filter: like / search
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('study.studytitle LIKE ' . $like . ' OR study.studytext LIKE ' . $like . ' OR study.studyintro LIKE ' .
				$like . ' OR series.series_text LIKE ' . $like . ' OR series.description LIKE ' . $like . ' OR t.topic_text LIKE ' . $like
			);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.fullordering');
		$orderDirn = '';

		if (empty($orderCol) || $orderCol === " ")
		{
			$orderCol = $this->state->get('list.ordering', 'study.studydate');

			// Set order by menu if set. New Default is blank as of 9.2.5
			if ($this->state->params->get('order') === '2')
			{
				$this->state->set('list.direction', 'ASC');
			}
			elseif ($this->state->params->get('order') === '1')
			{
				$this->state->set('list.direction', 'DESC');
			}

			$orderDirn = $this->state->get('list.direction', 'DESC');
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

	/**
	 * Get a list of teachers associated with series
	 *
	 * @return mixed
	 * @since 9.0.0
	 */
	public function getTeachers()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('t.id AS value, t.teachername AS text');
		$query->from('#__bsms_teachers AS t');
		$query->select('series.access');
		$query->join('INNER', '#__bsms_series AS series ON t.id = series.teacher');
		$query->group('t.id');
		$query->order('t.teachername ASC');

		$db->setQuery($query->__toString());
		$items = $db->loadObjectList();

		return $items;
	}

	/**
	 * Get a list of teachers associated with series
	 *
	 * @return mixed
	 * @since 9.0.0
	 */
	public function getYears()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT YEAR(s.studydate) as value, YEAR(s.studydate) as text');
		$query->from('#__bsms_studies as s');
		$query->select('series.access');
		$query->join('INNER', '#__bsms_series as series on s.series_id = series.id');
		$query->order('value');

		$db->setQuery($query->__toString());

		return $db->loadObjectList();
	}

	/**
	 * Get a list of all used series
	 *
	 * @return object
	 * @since 7.0
	 */
	public function getSeries()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('series.id AS value, series.series_text AS text, series.access');
		$query->from('#__bsms_series AS series');
		$query->join('INNER', '#__bsms_studies AS study ON study.series_id = series.id');
		$query->group('series.id');
		$query->order('series.series_text');

		$db->setQuery($query->__toString());
		$items = $db->loadObjectList();

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$user   = Factory::getUser();
		$groups = $user->getAuthorisedViewLevels();
		$count  = count($items);

		if ($count > 0)
		{
			foreach ($items as $i => $iValue)
			{
				if ($iValue->access > 1)
				{
					if (!in_array($iValue->access, $groups, true))
					{
						unset($items[$i]);
					}
				}
			}
		}

		return $items;
	}

	public function getBooks()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('books.id AS value, books.bookname AS text, books.id as value');
		$query->from('#__bsms_books AS books');
		$query->order('books.booknumber');

		$db->setQuery($query->__toString());
		$books = $db->loadObjectList();

		foreach ($books as $book)
		{
			$book->text = Text::_($book->text);
		}

		return $books;
	}
}
