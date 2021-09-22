<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\BibleStudy\Administrator\Lib;

// No Direct Access
defined('_JEXEC') or die;

use CWM\Component\BibleStudy\Administrator\Helper\CWMParams;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * Bible Study stats support class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMStats
{
	/** @var int used to store query of messages
	 *
	 * @since 9.0.0 */
	private static $total_messages = 0;

	/** @var string Start Date
	 *
	 * @since 9.0.0 */
	private static $total_messages_start = '';

	/** @var string End Date
	 *
	 * @since 9.0.0 */
	private static $total_messages_end   = '';

	/**
	 * Total plays of media files per study
	 *
	 * @param   int  $id  Id number of study
	 *
	 * @return int Total plays form the media
	 *
	 * @since 9.0.0
	 */
	public static function total_plays($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('sum(m.plays), m.study_id, m.published, s.id FROM #__bsms_mediafiles AS m')
			->leftJoin('#__bsms_studies AS s ON (m.study_id = s.id)')
			->where('m.study_id = ' . $db->q($id));
		$db->setQuery($query);
		$plays = $db->loadResult();

		return (int) $plays;
	}

	/**
	 * Total messages in Bible Study
	 *
	 * @param   string  $start  ?
	 * @param   string  $end    ?
	 *
	 * @return int Total Messages
	 *
	 * @since 9.0.0
	 */
	public static function get_total_messages($start = '', $end = '')
	{
		if ($start != self::$total_messages_start || $end != self::$total_messages_end || !self::$total_messages)
		{
			self::$total_messages_start = $start;
			self::$total_messages_end   = $end;

			$db    = Factory::getDbo();
			$where = array();

			if (!empty($start))
			{
				$where[] = 'time > UNIX_TIMESTAMP(\'' . $start . '\')';
			}

			if (!empty($end))
			{
				$where[] = 'time < UNIX_TIMESTAMP(\'' . $end . '\')';
			}

			$query = $db->getQuery(true);
			$query
				->select('COUNT(*)')
				->from('#__bsms_studies')
				->where('published =' . $db->q('1'));

			if (count($where) > 0)
			{
				$query->where(implode(' AND ', $where));
			}

			$db->setQuery($query);
			self::$total_messages = (int) $db->loadResult();
		}

		return self::$total_messages;
	}

	/**
	 * Total topics in Bible Study
	 *
	 * @param   string  $start  ?
	 * @param   string  $end    ?
	 *
	 * @return int  Total Topics
	 *
	 * @since 9.0.0
	 */
	public static function get_total_topics($start = '', $end = '')
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('COUNT(*)')
			->from('#__bsms_studies')
			->leftJoin('#__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)')
			->leftJoin('#__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)')
			->where('#__bsms_topics.published = ' . $db->q('1'));

		if (!empty($start))
		{
			$query->where('time > UNIX_TIMESTAMP(\'' . $start . '\')');
		}

		if (!empty($end))
		{
			$query->where('time < UNIX_TIMESTAMP(\'' . $end . '\')');
		}

		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Get top studies
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	public static function get_top_studies()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('*')
			->from('#__bsms_studies')
			->where('published = ' . $db->q('1'))
			->where('hits > ' . $db->q('0'))
			->order('hits desc');
		$db->setQuery($query, 0, 1);
		$results     = $db->loadObjectList();
		$top_studies = null;

		foreach ($results as $result)
		{
			$top_studies .= $result->hits . ' ' . Text::_('JBS_CMN_HITS') .
				' - <a href="index.php?option=com_proclaim&amp;task=message.edit&amp;id=' . $result->id . '">' .
				$result->studytitle . '</a> - ' . date('Y-m-d', strtotime($result->studydate)) . '<br>';
		}

		return $top_studies;
	}

	/**
	 * Total media files in Bible Study
	 *
	 * @return int
	 *
	 * @since 9.0.0
	 */
	public static function get_total_categories()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('*')
			->from('#__bsms_mediafiles')
			->where('published = ' . $db->q('1'));
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Get top books
	 *
	 * @return object
	 *
	 * @deprecated Not used as of 8.0.0
	 *
	 * @since      9.0.0
	 */
	public static function get_top_books()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('booknumber, COUNT( hits ) AS totalmsg')
			->from('#__bsms_studies')
			->group('booknumber')
			->order('totalmsg DESC');
		$db->setQuery($query, 0, 5);
		$results = $db->loadObjectList();

		if (count($results) > 0)
		{
			$ids   = implode(',', $results);
			$query = $db->getQuery(true);
			$query
				->select('bookname')
				->from('#__bsms_books')
				->where('booknumber IN (' . $ids . ')')
				->order('booknumber');
			$db->setQuery($query);
			$names = $db->loadResult();
			$i     = 0;

			foreach ($results as $result)
			{
				$result->bookname = $names[$i++];
			}
		}
		else
		{
			$results = new stdClass;
		}

		return $results;
	}

	/**
	 * Total comments
	 *
	 * @return int
	 *
	 * @since 9.0.0
	 */
	public static function get_total_comments()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('COUNT(*)')
			->from('#__bsms_comments')
			->where('published = ' . $db->q('1'));
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Get top thirty days
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	public static function get_top_thirty_days()
	{
		$month      = mktime(0, 0, 0, date("m") - 3, date("d"), date("Y"));
		$last_month = date("Y-m-d 00:00:01", $month);
		$db         = Factory::getDbo();
		$query      = $db->getQuery(true);
		$query
			->select('*')
			->from('#__bsms_studies')
			->where('published = ' . $db->q('1'))
			->where('hits > ' . $db->q('0'))
			->where('UNIX_TIMESTAMP(studydate) > UNIX_TIMESTAMP( ' . $db->q($last_month) . ' )')
			->order('hits desc');
		$db->setQuery($query, 0, 5);
		$results     = $db->loadObjectList();
		$top_studies = null;

		if (!$results)
		{
			$top_studies = Text::_('JBS_CPL_NO_INFORMATION');
		}
		else
		{
			foreach ($results as $result)
			{
				$top_studies .= $result->hits . ' ' . JText::_('JBS_CMN_HITS') .
					' - <a href="index.php?option=com_proclaim&amp;task=message.edit&amp;id=' . $result->id . '">' .
					$result->studytitle . '</a> - ' . date('Y-m-d', strtotime($result->studydate)) . '<br>';
			}
		}

		return $top_studies;
	}

	/**
	 * Get Total Media Files
	 *
	 * @return int Number of Records under MediaFiles that are published.
	 *
	 * @since 9.0.0
	 */
	public static function total_media_files()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('COUNT(*)')
			->from('#__bsms_mediafiles')
			->where('published = ' . 1);
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Get Top Downloads
	 *
	 * @return string List of links to the downloads
	 *
	 * @since 9.0.0
	 */
	public static function get_top_downloads()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select(
				'#__bsms_mediafiles.*, #__bsms_studies.published AS spub, #__bsms_mediafiles.published AS mpublished,' .
				'#__bsms_studies.id AS sid, #__bsms_studies.studytitle AS stitle, #__bsms_studies.studydate AS sdate ')
			->from('#__bsms_mediafiles')
			->leftJoin('#__bsms_studies ON (#__bsms_mediafiles.study_id = #__bsms_studies.id)')
			->where('#__bsms_mediafiles.published = 1 ')
			->where('downloads > 0')
			->order('downloads desc');

		$db->setQuery($query, 0, 5);
		$results     = $db->loadObjectList();
		$top_studies = null;

		foreach ($results as $result)
		{
			$top_studies .=
				$result->downloads . ' - <a href="index.php?option=com_proclaim&amp;task=message.edit&amp;d=' .
				$result->sid . '">' . $result->stitle . '</a> - ' . date('Y-m-d', strtotime($result->sdate)) .
				'<br>';
		}

		return $top_studies;
	}

	/**
	 * Get Downloads ninety
	 *
	 * @return  string list of download links
	 *
	 * @since 9.0.0
	 */
	public static function get_downloads_ninety()
	{
		$month     = mktime(0, 0, 0, date("m") - 3, date("d"), date("Y"));
		$lastmonth = date("Y-m-d 00:00:01", $month);
		$db        = Factory::getDbo();
		$query     = $db->getQuery(true);
		$query
			->select(
				'#__bsms_mediafiles.*, #__bsms_studies.published AS spub, #__bsms_mediafiles.published AS mpublished,' .
				' #__bsms_studies.id AS sid, #__bsms_studies.studytitle AS stitle, #__bsms_studies.studydate AS sdate ')
			->from('#__bsms_mediafiles')
			->leftJoin('#__bsms_studies ON (#__bsms_mediafiles.study_id = #__bsms_studies.id)')
			->where('#__bsms_mediafiles.published = ' . $db->q('1'))
			->where('downloads > ' . (int) $db->q('0'))
			->where('UNIX_TIMESTAMP(createdate) > UNIX_TIMESTAMP( ' . $db->q($lastmonth) . ' )')
			->order('downloads DESC');
		$db->setQuery($query, 0, 5);
		$results     = $db->loadObjectList();
		$top_studies = null;

		if (!$results)
		{
			$top_studies = Text::_('JBS_CPL_NO_INFORMATION');
		}
		else
		{
			foreach ($results as $result)
			{
				$top_studies .= $result->downloads . ' ' . JText::_('JBS_CMN_HITS') .
					' - <a href="index.php?option=com_proclaim&amp;task=message.edit&amp;id=' . $result->sid . '">' .
					$result->stitle . '</a> - ' . date('Y-m-d', strtotime($result->sdate)) . '<br>';
			}
		}

		return $top_studies;
	}

	/**
	 * Total Downloads
	 *
	 * @return  int Number of Mediafiles Published and have downloads
	 *
	 * @since 9.0.0
	 */
	public static function total_downloads()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('SUM(downloads)')
			->from('#__bsms_mediafiles')
			->where('published = ' . 1)
			->where('downloads > ' . 0);
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Top Score ???
	 *
	 * @var   Registry  $admin_params  Admin Prams
	 *
	 * @return int number of scores
	 *
	 * @since 9.0.0
	 */
	public static function top_score()
	{
		$final        = [];
		$admin_params = CWMParams::getAdmin();
		$format       = $admin_params->params->get('format_popular', '0');
		$db           = Factory::getDbo();
		$query        = $db->getQuery(true);
		$query
			->select('study_id, sum(downloads + plays) as added ')
			->from('#__bsms_mediafiles')
			->where('published = ' . 1)
			->group('study_id')
			->order('added DESC');
		$db->setQuery($query);
		$results = $db->loadAssocList();
		array_splice($results, 5);

		foreach ($results as $key => $result)
		{
			$query = $db->getQuery(true);
			$query
				->select('#__bsms_studies.studydate, #__bsms_studies.studytitle, #__bsms_studies.hits,' .
					'#__bsms_studies.id, #__bsms_mediafiles.study_id from #__bsms_studies')
				->leftJoin('#__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id)')
				->where('#__bsms_mediafiles.study_id = ' . (int) $result['study_id']);
			$db->setQuery($query);
			$hits = $db->loadObject();

			if ($hits)
			{
				if ($format < 1)
				{
					$total = $result['added'] + $hits->hits;
				}
				else
				{
					$total = $result->added;
				}

				$link    = ' <a href="index.php?option=com_proclaim&amp;task=message.edit&amp;id=' . $hits->id . '">' .
					$hits->studytitle . '</a> ' . date('Y-m-d', strtotime($hits->studydate)) . '<br>';
				$final2  = array('total' => $total, 'link' => $link);
				$final[] = $final2;
			}
		}

		rsort($final);
		array_splice($final, 5);
		$top_score_table = '';

		foreach ($final as $value)
		{
			foreach ($value as $scores)
			{
				$top_score_table .= $scores;
			}
		}

		return $top_score_table;
	}

	/**
	 * Returns a System of Player
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	public static function players()
	{
		$count_no_player       = 0;
		$count_global_player   = 0;
		$count_internal_player = 0;
		$count_av_player       = 0;
		$count_legacy_player   = 0;
		$count_embed_code      = 0;
		$db                    = Factory::getDbo();
		$query                 = $db->getQuery(true);
		$query
			->select('params')
			->from('#__bsms_mediafiles')
			->where('published = ' . $db->q('1'));
		$db->setQuery($query);
		$params         = $db->loadObjectList();

		$registry = new Registry;
		$media_players = null;

		if ($params)
		{
			$total_players = count($params);

			foreach ($params as $param)
			{
				$registry->loadString($param->params);

				switch ($registry->get('player', 0))
				{
					case 0:
						$count_no_player++;
						break;
					case '100':
						$count_global_player++;
						break;
					case '1':
						$count_internal_player++;
						break;
					case '3':
						$count_av_player++;
						break;
					case '7':
						$count_legacy_player++;
						break;
					case '8':
						$count_embed_code++;
						break;
				}
			}

			$media_players = '<br /><strong>' . Text::_('JBS_CMN_TOTAL_PLAYERS') . ': ' . $total_players . '</strong>' .
				'<br /><strong>' . Text::_('JBS_CMN_INTERNAL_PLAYER') . ': </strong>' . $count_internal_player .
				'<br /><strong><a href="http://extensions.joomla.org/extensions/extension/multimedia/multimedia-players/allvideos" target="blank">' .
				Text::_('JBS_CMN_AVPLUGIN') . '</a>: </strong>' . $count_av_player . '<br /><strong>' .
				Text::_('JBS_CMN_LEGACY_PLAYER') . ': </strong>' . $count_legacy_player . '<br /><strong>' .
				Text::_('JBS_CMN_NO_PLAYER_TREATED_DIRECT') . ': </strong>' . $count_no_player . '<br /><strong>' .
				Text::_('JBS_CMN_GLOBAL_SETTINGS') . ': </strong>' . $count_global_player . '<br /><strong>' .
				Text::_('JBS_CMN_EMBED_CODE') . ': </strong>' . $count_embed_code;
		}

		return $media_players;
	}

	/**
	 * Popups for media files
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	public static function popups()
	{
		$no_player    = 0;
		$pop_count    = 0;
		$sq_count     = 0;
		$inline_count = 0;
		$global_count = 0;
		$db           = Factory::getDbo();
		$query        = $db->getQuery(true);
		$query
			->select('params')
			->from('#__bsms_mediafiles')
			->where('published = ' . 1);
		$db->setQuery($query);
		$popups = $db->loadObjectList();

		if ($popups)
		{
			$total_media_files = count($popups);

			foreach ($popups as $popup)
			{
				$registry = new Registry;
				$registry->loadString($popup->params);
				$popup = $registry->get('popup', null);

				switch ($popup)
				{
					case null:
					case 100:
					case 0:
						$no_player++;
						break;
					case 1:
						$pop_count++;
						break;
					case 2:
						$inline_count++;
						break;
					case 3:
						$sq_count++;
						break;
				}
			}

			$popups = '<br /><strong>' . Text::_('JBS_CMN_TOTAL_MEDIAFILES') . ': ' . $total_media_files . '</strong>' .
				'<br /><strong>' . Text::_('JBS_CMN_INLINE') . ': </strong>' . $inline_count . '<br /><strong>' .
				Text::_('JBS_CMN_POPUP') . ': </strong>' . $pop_count . '<br /><strong>' .
				Text::_('JBS_CMN_SQUEEZEBOX') . ': </strong>' . $sq_count . '<br /><strong>' .
				Text::_('JBS_CMN_NO_OPTION_TREATED_GLOBAL') . ': </strong>' . $no_player;
		}

		return $popups;
	}

	/**
	 * Top Score Site
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	public function top_score_site()
	{
		$input = new Input ;
		$t     = $input->get('t', 1, 'int');

		$admin = CWMParams::getAdmin();
		$limit        = $admin->params->get('popular_limit', '25');
		$top          = '<select onchange="goTo()" id="urlList"><option value="">' .
			Text::_('JBS_CMN_SELECT_POPULAR_STUDY') . '</option>';
		$final        = array();

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('m.study_id, s.access, s.published AS spub, sum(m.downloads + m.plays) as added')
			->from('#__bsms_mediafiles AS m')
			->leftJoin('#__bsms_studies AS s ON (m.study_id = s.id)')
			->where('m.published = 1 GROUP BY m.study_id');
		$db->setQuery($query);
		$format = $admin->params->get('format_popular', '0');

		$items = $db->loadObjectList();

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$user   = Factory::getUser();
		$groups = $user->getAuthorisedViewLevels();
		$count  = count($items);

		for ($i = 0; $i < $count; $i++)
		{
			if ($items[$i]->access > 1)
			{
				if (!in_array($items[$i]->access, $groups))
				{
					unset($items[$i]);
				}
			}
		}

		foreach ($items as $result)
		{
			$query = $db->getQuery(true);
			$query->select('#__bsms_studies.studydate, #__bsms_studies.studytitle,
							#__bsms_studies.hits, #__bsms_studies.id, #__bsms_mediafiles.study_id')
				->from('#__bsms_studies')
				->leftJoin('#__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id)')
				->where('#__bsms_mediafiles.study_id = ' . (int) $result->study_id);
			$db->setQuery($query);
			$hits = $db->loadObject();

			if (!$hits)
			{
				return false;
			}

			if (!$hits->studytitle)
			{
				$name = $hits->id;
			}
			else
			{
				$name = $hits->studytitle;
			}

			if ($format < 1)
			{
				$total = $result->added + $hits->hits;
			}
			else
			{
				$total = $result->added;
			}

			$selectvalue   = Route::_('index.php?option=com_proclaim&view=sermon&id=' . $hits->id . '&t=' . $t);
			$selectdisplay = $name . ' - ' . Text::_('JBS_CMN_SCORE') . ': ' . $total;
			$final2        = array(
				'score'   => $total,
				'select'  => $selectvalue,
				'display' => $selectdisplay
			);
			$final[]       = $final2;
		}

		rsort($final);
		array_splice($final, $limit);

		foreach ($final as $topscore)
		{
			$top .= '<option value="' . $topscore['select'] . '">' . $topscore['display'] . '</option>';
		}

		$top .= '</select>';

		return $top;
	}
}
