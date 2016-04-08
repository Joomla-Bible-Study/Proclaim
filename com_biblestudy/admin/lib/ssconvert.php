<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die();

/**
 * Class script to convert SermonSpeaker 5.2 to Joomla Bible Study
 *
 * @package  BibleStudy.Admin
 * @since    9.0.0
 */
class JBSMSSConvert
{

	private $serverid;
	/**
	 * function to convert SermonSpeaker
	 *
	 * @return string Table for results
	 */
	public function convertSS()
	{
		$db = JFactory::getDbo();
		$query       = $db->getQuery(true);
		$query->select('*')
			->from('#__sermon_sermons');
		$db->setQuery($query);
		$sermons = $db->loadObjectList();

		// Get the old sermons, teachers, series

		// Get a unique list of teacher ids
		$query = $db->getQuery(true);
		$query->select('speaker_id, id, series_id')->from('#__sermon_sermons')->group('series_id');
		$db->setQuery($query);
		$seriesspeakers = $db->loadObjectList();

		$result_table = '<table><tr><td><strong>' . JText::_('JBS_IBM_NOTE_ERRORS') . '</strong></td></tr>';

		// Make a server record
		$base              = JUri::base();
		$site              = str_replace('/administrator/', '', $base);
		$data              = new stdClass;
		$data->server_name = $site;
		$data->server_path = $site;

		if (!$db->insertObject('#__bsms_servers', $data))
		{
			$result_table .= '<tr><td>' . JText::_('JBS_IBM_ERROR_OCCURED_SERVER') . '</td></tr>';
		}
		else
		{
			$result_table .= '<tr><td>' . JText::_('JBS_IBM_SERVER_RECORD_ADDED') . '</td></tr>';
		}
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__bsms_servers')
			->where('published = 1')
			->order($db->qn('id') . ' desc');
		$db->setQuery($query, 0, 1);
		$server         = $db->loadAssoc();
		$this->serverid = $server['id'];

		// Make the teachers
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__sermon_speakers');
		$db->setQuery($query);

		$teachers = $db->loadObjectList();
		if (!$teachers)
		{
			$result_table .= '<tr><td><span style="color: red;">' . JText::_('JBS_IBM_NO_TEACHERS_FOUND_SS') . '</span></td></tr>';
		}

		foreach ($teachers AS $teacher)
		{

			$data = new stdClass;
			$teachername       = $teacher->name;
			$data->teachername = $teachername;
			$data->website     = $teacher->website;
			$data->information = $db->escape($teacher->bio);
			$data->image       = $teacher->pic;
			$data->thumb       = $teacher->pic;
			$data->published   = $teacher->state;
			$data->ordering    = $teacher->ordering;
			$data->catid       = $teacher->catid;
			$data->short       = $db->escape($teacher->bio);
			$data->alias       = $teacher->alias;

			if (!$db->insertObject('#__bsms_teachers', $data))
			{
				$result_table .= '<tr><td>' . JText::_('JBS_IBM_ERROR_OCCURED_CREATING_TEACHERS') . '</td></tr>';
			}

			// Get the last teacherid
			$query = $db->getQuery(true);
			$query->select('id')->from('#__bsms_teachers')->order('id');
			$db->setQuery($query, 0, 1);
			$lastteacher = $db->loadResult();

			// Add the new teacher id to the object for cross walk of series and teachers
			foreach ($seriesspeakers as $speakers)
			{
				if ($speakers->speaker_id == $teacher->id)
				{
					$speakers->newid = $lastteacher;
				}
			}

		} // End teachers foreach

		// Series Records
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__sermon_series');
		$db->setQuery($query);
		$series = $db->loadObjectList();
		if (!$series)
		{
			$result_table .= '<tr><td><span style="color: red;">' . JText::_('JBS_IBM_NO_SERIES_FOUND_SS') . '</span>';
		}
		else
		{
			foreach ($series AS $single)
			{
				$id          = $single->id;
				$series_text = $single->series_title;
				$description = $single->series_description;
				$published   = $single->state;
				if (!$published)
				{
					$published = $single->published;
				}
				if (!$published)
				{
					$published = 1;
				}
				$series_thumbnail       = $single->avatar;
				$data                   = new stdClass;
				$data->series_text      = $series_text;
				$data->description      = $description;
				$data->published        = $published;
				$data->series_thumbnail = $series_thumbnail;
				foreach ($seriesspeakers as $speakers)
				{
					if ($speakers->series_id == $single->id)
					{
						$data->teacher = $speakers->newid;
					}
					else
					{
						$data->teacher = $id;
					}
				}

				$data->alias    = $single->alias;
				$data->ordering = $single->ordering;
				if (!$db->insertObject('#__bsms_series', $data))
				{
					$result_table .= '<tr><td>' . JText::_('JBS_IBM_ERROR_OCCURED_SS_SERIES') . '</td></tr>';
				}
				else
				{
					$query = $db->getQuery(true);
					$query->select('id')->from('#__bsms_studies')->order('id DESC');
					$db->setQuery($query, 0, 1);
					$lastseries = $db->loadResult();

					// Add the new teacher id to the object for cross walk of series and teachers
					foreach ($seriesspeakers as $speakers)
					{
						if ($speakers->series_id == $single->id)
						{
							$speakers->newseriesid = $lastseries;
						}
					}
				}

			} // End foreach $series as $single

		}
		// Get all the sermons and loop through them, creating new ones in JBS

		foreach ($sermons as $sermon)
		{
			$this->newStudies($sermon, $seriesspeakers);
		}

		// Count the new numbers and report
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')->from('#__bsms_studies');
		$db->setQuery($query);
		$newstudies = $db->loadResult();

		$query = $db->getQuery(true);
		$query->select('COUNT(*)')->from('#__bsms_teachers');
		$db->setQuery($query);
		$newteachers = $db->loadResult();

		$query = $db->getQuery(true);
		$query->select('COUNT(*)')->from('#__bsms_series');
		$db->setQuery($query);
		$newseries = $db->loadResult();

		$query = $db->getQuery(true);
		$query->select('COUNT(*)')->from('#__bsms_mediafiles');
		$db->setQuery($query);
		$newmediafiles = $db->loadResult();

		$result_table .= '<tr><td>' . $newteachers . ' ' . JText::_('JBS_IBM_TEACHERS_CREATED') . '</td></tr>';
		$result_table .= '<tr><td>' . $newstudies . ' ' . JText::_('JBS_IBM_SERMONS_CREATED_FOR') . '</td></tr>';
		$result_table .= '<tr><td>' . $newmediafiles . ' ' . JText::_('JBS_IBM_MEDIAFILES_CREATED') . '</td></tr>';
		$result_table .= '<tr><td>' . $newseries . ' ' . JText::_('JBS_IBM_SERIES_CONVERTED') . '</td></tr>';
		$result_table .= '</table>';

		return $result_table;

	}

	/**
	 * New Studies Carnation
	 *
	 * @param   object  $sermon          Test of Sermons
	 * @param   array   $seriesspeakers  Array of Series
	 *
	 * @return void
	 */
	public function newStudies($sermon, $seriesspeakers)
	{
		$db   = JFactory::getDbo();
		$data = new stdClass;
		foreach ($seriesspeakers as $speakers)
		{
			if ($speakers->speaker_id == $sermon->speaker_id)
			{
				$data->teacher_id = $speakers->newid;
			}
			if ($speakers->series_id == $sermon->series_id)
			{
				$data->series_id = $speakers->newseriesid;
			}
		}

		$data->studytitle  = $sermon->sermon_title;
		$data->studynumber = $sermon->sermon_number;

		$scripture           = $this->getVerses($sermon->sermon_scripture);
		$data->booknumber    = $scripture->booknumber;
		$data->chapter_begin = $scripture->chapter_begin;
		$data->chapter_end   = $scripture->chapter_end;
		$data->verse_begin   = $scripture->verse_begin;
		$data->verse_end     = $scripture->verse_end;

		$data->studydate = $sermon->sermon_date;

		$time                = $this->getTime($sermon->sermon_time);
		$data->media_hours   = $time->media_hours;
		$data->media_minutes = $time->media_minutes;
		$data->media_seconds = $time->media_seconds;
		$data->studytext     = $sermon->notes;
		$data->user_id       = $sermon->created_by;
		$data->hits          = $sermon->hits;

		if (!$data->hits)
		{
			$data->hits = 0;
		}
		$data->published = $sermon->state;
		$data->alias     = $sermon->alias;

		$db->insertObject('#__bsms_studies', $data);
		$query = $db->getQuery(true);
		$query->select('id')->from('#__bsms_studies')->order('id DESC');

		$db->setQuery($query, 0, 1);
		$study              = $db->loadAssoc();
		$data1              = new stdClass;
		$data1->study_id    = $study['id'];
		$data1->server_id   = $this->serverid;
		$data1->filename    = $sermon->audiofile;
		$data1->published   = 1;
		$data1->createdate  = $data->studydate;
		$data1->media_image = 1;
		$data1->downloads   = 1;
		$data1->plays       = 0;

		$db->insertObject('#__bsms_mediafiles', $data1, 'id');

		if ($sermon->videofile)
		{
			$data2              = new stdClass;
			$data2->study_id    = $study['id'];
			$data2->server      = $this->serverid;
			$data2->filename    = $sermon->videofile;
			$data2->published   = 1;
			$data2->createdate  = $data->studydate;
			$data2->media_image = 5;
			$data2->downloads   = 0;
			$data2->plays       = 0;

			$db->insertObject('#__bsms_mediafiles', $data2, 'id');

		}

	}

	/**
	 * Get Version of Book Number
	 *
	 * @param   string  $sermon  Book of the Bible.
	 *
	 * @return object Version of the Books
	 */
	public function getVerses($sermon)
	{
		$sermonscripture             = new stdClass;
		$sermonscripture->booknumber = '101';
		$secondcolon                 = 0;

		if (!$sermon)
		{
			$sermonscripture->chapter_begin = '';
			$sermonscripture->chapter_end   = '';
			$sermonscripture->verse_begin   = '';
			$sermonscripture->verse_end     = '';
		}
		$bookname = substr_count(strtolower($sermon), 'genesis');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '101';
		}
		$bookname = substr_count(strtolower($sermon), 'exodus');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '102';
		}
		$bookname = substr_count(strtolower($sermon), 'leviticus');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '103';
		}
		$bookname = substr_count(strtolower($sermon), 'numbers');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '104';
		}
		$bookname = substr_count(strtolower($sermon), 'deuteronomy');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '105';
		}
		$bookname = substr_count(strtolower($sermon), 'joshua');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '106';
		}
		$bookname = substr_count(strtolower($sermon), 'judges');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '107';
		}
		$bookname = substr_count(strtolower($sermon), 'ruth');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '108';
		}
		$bookname = substr_count(strtolower($sermon), '1 samuel');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '109';
		}
		$bookname = substr_count(strtolower($sermon), '2 samuel');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '110';
		}
		$bookname = substr_count(strtolower($sermon), '1 kings');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '111';
		}
		$bookname = substr_count(strtolower($sermon), '2 kings');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '112';
		}
		$bookname = substr_count(strtolower($sermon), '1 chronicles');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '113';
		}
		$bookname = substr_count(strtolower($sermon), '2 chronicles');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '114';
		}
		$bookname = substr_count(strtolower($sermon), 'ezra');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '115';
		}
		$bookname = substr_count(strtolower($sermon), 'nehemiah');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '116';
		}
		$bookname = substr_count(strtolower($sermon), 'esther');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '117';
		}
		$bookname = substr_count(strtolower($sermon), 'job');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '118';
		}
		$bookname = substr_count(strtolower($sermon), 'psalm');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '119';
		}
		$bookname = substr_count(strtolower($sermon), 'proverbs');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '120';
		}
		$bookname = substr_count(strtolower($sermon), 'ecclesiastes');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '121';
		}
		$bookname = substr_count(strtolower($sermon), 'song of solomon');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '122';
		}
		$bookname = substr_count(strtolower($sermon), 'isaiah');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '123';
		}
		$bookname = substr_count(strtolower($sermon), 'jeremiah');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '124';
		}
		$bookname = substr_count(strtolower($sermon), 'lamentations');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '125';
		}
		$bookname = substr_count(strtolower($sermon), 'ezekiel');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '126';
		}
		$bookname = substr_count(strtolower($sermon), 'daniel');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '127';
		}
		$bookname = substr_count(strtolower($sermon), 'hosea');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '128';
		}
		$bookname = substr_count(strtolower($sermon), 'joel');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '129';
		}
		$bookname = substr_count(strtolower($sermon), 'amos');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '130';
		}
		$bookname = substr_count(strtolower($sermon), 'obadiah');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '131';
		}
		$bookname = substr_count(strtolower($sermon), 'jonah');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '132';
		}
		$bookname = substr_count(strtolower($sermon), 'micah');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '133';
		}
		$bookname = substr_count(strtolower($sermon), 'nahum');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '134';
		}
		$bookname = substr_count(strtolower($sermon), 'habakkuk');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '135';
		}
		$bookname = substr_count(strtolower($sermon), 'zephaniah');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '136';
		}
		$bookname = substr_count(strtolower($sermon), 'haggai');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '137';
		}
		$bookname = substr_count(strtolower($sermon), 'zechariah');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '138';
		}
		$bookname = substr_count(strtolower($sermon), 'malachi');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '139';
		}
		$bookname = substr_count(strtolower($sermon), 'matthew');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '140';
		}
		$bookname = substr_count(strtolower($sermon), 'mark');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '141';
		}
		$bookname = substr_count(strtolower($sermon), 'luke');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '142';
		}
		$bookname = substr_count(strtolower($sermon), 'john');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '143';
		}
		$bookname = substr_count(strtolower($sermon), 'acts');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '144';
		}
		$bookname = substr_count(strtolower($sermon), 'romans');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '145';
		}
		$bookname = substr_count(strtolower($sermon), '1 corinthians');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '146';
		}
		$bookname = substr_count(strtolower($sermon), '2 corinthians');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '147';
		}
		$bookname = substr_count(strtolower($sermon), 'galatians');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '148';
		}
		$bookname = substr_count(strtolower($sermon), 'ephesians');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '149';
		}
		$bookname = substr_count(strtolower($sermon), 'philippians');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '150';
		}
		$bookname = substr_count(strtolower($sermon), 'colossians');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '151';
		}
		$bookname = substr_count(strtolower($sermon), '1 thessalonians');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '152';
		}
		$bookname = substr_count(strtolower($sermon), '2 thessalonians');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '153';
		}
		$bookname = substr_count(strtolower($sermon), '1 timothy');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '154';
		}
		$bookname = substr_count(strtolower($sermon), '2 timothy');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '155';
		}
		$bookname = substr_count(strtolower($sermon), 'titus');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '156';
		}
		$bookname = substr_count(strtolower($sermon), 'philemon');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '157';
		}
		$bookname = substr_count(strtolower($sermon), 'hebrews');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '158';
		}
		$bookname = substr_count(strtolower($sermon), 'james');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '159';
		}
		$bookname = substr_count(strtolower($sermon), '1 peter');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '160';
		}
		$bookname = substr_count(strtolower($sermon), '2 peter');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '161';
		}
		$bookname = substr_count(strtolower($sermon), '1 john');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '162';
		}
		$bookname = substr_count(strtolower($sermon), '2 john');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '163';
		}
		$bookname = substr_count(strtolower($sermon), '3 john');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '164';
		}
		$bookname = substr_count(strtolower($sermon), 'jude');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '165';
		}
		$bookname = substr_count(strtolower($sermon), 'revelation');

		if ($bookname > 0)
		{
			$sermonscripture->booknumber = '166';
		}

		$firstspace = strpos($sermon, ' ', 2);
		$firstcolon = strpos($sermon, ':');
		$firstdash  = strpos($sermon, '-');

		$issecondcolon = substr_count($sermon, ':', $firstcolon + 1);

		if ($issecondcolon)
		{
			$secondcolon = strpos($sermon, ':', $firstcolon + 1);
		}
		$sermonscripture->chapter_begin = substr($sermon, $firstspace + 1, ($firstcolon - $firstspace) - 1);

		if (!$firstdash)
		{
			$sermonscripture->verse_begin = substr($sermon, $firstcolon + 1);
		}
		else
		{
			$sermonscripture->verse_begin = substr($sermon, $firstcolon + 1, $firstdash - ($firstcolon + 1));
		}
		if (!$issecondcolon)
		{
			$sermonscripture->chapter_end = '';
		}
		else
		{
			$sermonscripture->chapter_end = substr($sermon, $firstdash + 1, ($secondcolon - $firstdash) - 1);
			$sermonscripture->verse_end   = substr($sermon, $secondcolon + 1);
		}
		if (!$issecondcolon && $firstdash)
		{
			$sermonscripture->verse_end = substr($sermon, $firstdash + 1);
		}

		return $sermonscripture;
	}

	/**
	 * Get Time
	 *
	 * @param   string  $time  Time to be formatted out.
	 *
	 * @return object
	 */
	public function getTime($time)
	{

		$firstcolon  = strpos($time, ':');
		$secondcolon = strpos($time, ':', $firstcolon + 1);
		$sermontime  = new stdClass;

		if ($secondcolon)
		{
			$sermontime->media_hours   = substr($time, 0, $firstcolon);
			$sermontime->media_seconds = substr($time, $secondcolon + 1, 2);
			$sermontime->media_minutes = substr($time, $firstcolon + 1, 2);
		}
		else
		{
			if ($firstcolon == 1)
			{
				$minuteslength = '1';
			}
			else
			{
				$minuteslength = '2';
			}
			$sermontime->media_seconds = substr($time, $firstcolon + 1, 2);
			$sermontime->media_minutes = substr($time, 0, $minuteslength);
		}

		return $sermontime;
	}

}
