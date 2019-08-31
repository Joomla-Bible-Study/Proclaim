<?php

/**
 * Helper for mod_biblestudy.php
 *
 * @package     Proclaim
 * @subpackage  Model.BibleStudy
 * @copyright   2007 - 2019 (C) CWM Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.joomlabiblestudy.org
 * */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * BibleStudy mod helper
 *
 * @package     Proclaim
 * @subpackage  Model.BibleStudy
 * @since       7.1.0
 */
class ModJBSMHelper
{
	/**
	 * Get Latest
	 *
	 * @param   Registry  $params  Item Params
	 *
	 * @return array
	 *
	 * @since 7.1.0
	 */
	public static function getLatest($params)
	{
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		$db = JFactory::getDbo();
		$db->setQuery('SET SQL_BIG_SELECTS=1');
		$db->execute();
		$query            = $db->getQuery(true);
		$teacher          = $params->get('teacher_id');
		$topic            = $params->get('topic_id');
		$book             = $params->get('booknumber');
		$series           = $params->get('series_id');
		$locations        = $params->get('locations');
		$condition        = $params->get('condition');
		$messagetype_menu = $params->get('messagetype');
		$year             = $params->get('year');
		$orderparam       = $params->get('order', '1');
		$language         = $params->get('language', '*');

		if ($orderparam == 2)
		{
			$order = "ASC";
		}
		else
		{
			$order = "DESC";
		}

		if ($condition > 0)
		{
			$condition = ' AND ';
		}
		else
		{
			$condition = ' OR ';
		}

		$query->select('study.id, study.published, study.studydate, study.studytitle, study.booknumber, study.chapter_begin,
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
		                study.verse_end2, study.verse_begin2, ' . $query->length('study.studytext') . ' AS readmore' . ','
			. ' CASE WHEN CHAR_LENGTH(study.alias) THEN CONCAT_WS(\':\', study.id, study.alias) ELSE study.id END as slug ');
		$query->from('#__bsms_studies AS study');

		// Join over Message Types
		$query->select('messageType.message_type AS messageType');
		$query->join('LEFT', '#__bsms_message_type AS messageType ON messageType.id = study.messagetype');

		// Join over Teachers
		$query->select('teacher.teachername AS teachername, teacher.title as title, teacher.teacher_thumbnail as thumb,
			teacher.thumbh, teacher.thumbw');
		$query->join('LEFT', '#__bsms_teachers AS teacher ON teacher.id = study.teacher_id');

		// Join over Series
		$query->select('series.series_text, series.series_thumbnail, series.description as sdescription');
		$query->join('LEFT', '#__bsms_series AS series ON series.id = study.series_id');

		// Join over Books
		$query->select('book.bookname');
		$query->join('LEFT', '#__bsms_books AS book ON book.booknumber = study.booknumber');

		$query->select('book2.bookname as bookname2');
		$query->join('LEFT', '#__bsms_books AS book2 ON book2.booknumber = study.booknumber2');

		// Join over MediaFiles and Plays/Downloads
		$query->select('GROUP_CONCAT(DISTINCT mediafile.id) as mids, SUM(mediafile.plays) AS totalplays,' .
			' SUM(mediafile.downloads) as totaldownloads, mediafile.study_id');
		$query->join('LEFT', '#__bsms_mediafiles AS mediafile ON mediafile.study_id = study.id');
		$query->group('study.id');

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
		$nowDate  = $db->quote(JFactory::getDate()->toSql(true));

		// Filter by start and end dates.
		if ((!$user->authorise('core.edit.state', 'com_biblestudy')) && (!$user->authorise('core.edit', 'com_biblestudy')))
		{
			$query->where('(study.publish_up = ' . $nullDate . ' OR study.publish_up <= ' . $nowDate . ')')
				->where('(study.publish_down = ' . $nullDate . ' OR study.publish_down >= ' . $nowDate . ')');
		}

		// Filter over teachers
		$filters = $teacher;

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
			if (!is_array($filters))
			{
				$filters = array($filters);
			}

			foreach ($filters as $filter)
			{
				if ($filter != -1 && $filter != 0)
				{
					$query->where('study.teacher_id = ' . (int) $filter, $condition);
				}
			}
		}

		// Filter locations
		$filters = $locations;

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
			if (!is_array($filters))
			{
				$filters = array($filters);
			}

			foreach ($filters AS $filter)
			{
				if ($filter != -1 && $filter != 0)
				{
					$query->where('study.location_id = ' . (int) $filter, $condition);
				}
			}
		}

		// Filter over books
		$filters = $book;

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
			if (!is_array($filters))
			{
				$filters = array($filters);
			}

			foreach ($filters AS $filter)
			{
				if ($filter != -1 && $filter != 0)
				{
					$query->where('study.booknumber = ' . (int) $filter, $condition);
				}
			}
		}

		$filters = $series;

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
			if (!is_array($filters))
			{
				$filters = array($filters);
			}

			foreach ($filters AS $filter)
			{
				if ($filter != -1 && $filter != 0)
				{
					$query->where('study.series_id = ' . (int) $filter, $condition);
				}
			}
		}

		$filters = $topic;

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
			if (!is_array($filters))
			{
				$filters = array($filters);
			}

			foreach ($filters AS $filter)
			{
				if ($filter != -1 && $filter != 0)
				{
					$query->where('st.topic_id = ' . (int) $filter, $condition);
				}
			}
		}

		// Filter by language
		$lang = JFactory::getLanguage();

		if ($lang || $language != '*')
		{
			$query->where('study.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		$filters = $messagetype_menu;

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
			if (!is_array($filters))
			{
				$filters = array($filters);
			}

			foreach ($filters AS $filter)
			{
				if ($filter != -1 && $filter != 0)
				{
					$query->where('study.messagetype = ' . (int) $filter, $condition);
				}
			}
		}

		$filters = $year;

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
			if (!is_array($filters))
			{
				$filters = array($filters);
			}

			if ($filters !== null)
			{
				foreach ($filters AS $filter)
				{
					if ($filter != -1 && $filter != 0)
					{
						$query->where('YEAR(study.studydate) = ' . (int) $filter, $condition);
					}
				}
			}
		}

		$query->order('studydate ' . $order);
		$db->setQuery((string) $query, 0, $params->get('moduleitems', '5'));
		$rows = $db->loadObjectList();

		return $rows;
	}
}
