<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die();

/**
 * Class script to convert SermonSpeaker 3.4 to Joomla Bible Study
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */
class JBSMSSConvert
{

	/**
	 * function to convert SermonSpeaker
	 *
	 * @return string Table for resultes
	 */
	public function convertSS()
	{
		$result_table = '<table><tr><td><strong>' . JText::_('JBS_IBM_NOTE_ERRORS') . '</strong></td></tr>';
		$db           = JFactory::getDBO();

		// Make a server record
		$base              = JURI::base();
		$site              = str_replace('/administrator/', '', $base);
		$data              = new stdClass;
		$data->server_name = $site;
		$data->server_path = $site;

		if (!$db->insertObject('#__bsms_servers', $data, 'id'))
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
		$server   = $db->loadAssoc();
		$serverid = $server['id'];

		// Series Records
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__sermon_series')
			->where('state = 1');
		$db->setQuery($query);
		$num_rows = $db->getNumRows();

		if ($num_rows < 1)
		{
			$result_table .= '<tr><td><span style="font-color: red;">' . JText::_('JBS_IBM_NO_SERIES_FOUND_SS') . '</span>';
		}
		else
		{
			$series = $db->loadObjectList();
			$addse  = 0;

			foreach ($series AS $single)
			{
				$id          = $single->id;
				$series_text = $single->series_title;
				$description = $single->series_description;
				$published   = $single->state;

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
				$data->teacher          = $id;
				$data->alias            = $single->alias;

				if (!$db->insertObject('#__bsms_series', $data, 'id'))
				{
					$result_table .= '<tr><td>' . JText::_('JBS_IBM_ERROR_OCCURED_SS_SERIES') . '</td></tr>';
				}
				else
				{
					$updatedse = $db->getAffectedRows();
					$addse     = $addse + $updatedse;
				}

			} // End foreach $series as $single
			$result_table .= '<tr><td>' . $addse . ' ' . JText::_('JBS_IBM_SERIES_CONVERTED') . '</td></tr>';
		}

		// Teacher Records
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__sermon_speakers');
		$db->setQuery($query);
		$numrows = $db->getNumRows();

		if ($numrows < 1)
		{
			$result_table .= '<tr><td><span style="font-color: red;">' . JText::_('JBS_IBM_NO_TEACHERS_FOUND_SS') . '</span></td></tr>';
		}

		$teachers = $db->loadObjectList();

		// Loop through each record and map fields then insert into JBS
		$add = 0;

		foreach ($teachers AS $teacher)
		{
			$data              = new stdClass;
			$data->id          = $teacher->id;
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

			if (!$db->insertObject('#__bsms_teachers', $data, 'id'))
			{
				$result_table .= '<tr><td>' . JText::_('JBS_IBM_ERROR_OCCURED_CREATING_TEACHERS') . '</td></tr>';
			}
			else
			{
				$updated = $db->getAffectedRows();
				$add     = $add + $updated;
			}

			$query = $db->getQuery(true);
			$query->select('s.*, se.id AS sid, se.teacher AS teacher')
				->from('#__sermon_sermons AS s')
				->leftJoin('#__bsms_series AS se ON (s.series_id = se.teacher)')
				->where('s.speaker_id = ' . (int) $db->q($teacher->id));
			$db->setQuery($query);
			$num_rows = $db->getNumRows();

			if ($num_rows < 1)
			{
				$result_table .= '<tr><td><span style="font-color: red;">' . JText::_('JBS_IBM_NO_SERMONS_FOUND_SS') . '</span>';
			}
			else
			{
				$sermons = $db->loadObjectList();
				$adds    = 0;
				$add2    = 0;

				foreach ($sermons AS $sermon)
				{
					$data     = new stdClass;
					$data->id = $sermon->id;

					// $data->teacher_id = $sermon->speaker_id;
					// $data->series_id = $sermon->series_id;
					$data->series_id   = $sermon->sid;
					$data->studytitle  = $db->escape($sermon->sermon_title);
					$data->studynumber = $sermon->sermon_number;

					$scripture           = $this->getVerses($sermon->sermon_scripture);
					$data->booknumber    = $scripture->booknumber;
					$data->chapter_begin = $scripture->chapter_begin;
					$data->chapter_end   = $scripture->chapter_end;
					$data->verse_begin   = $scripture->verse_begin;
					$data->verse_end     = $scripture->verse_end;

					$studydate       = $sermon->sermon_date;
					$data->studydate = $studydate;

					$time                = $this->getTime($sermon->sermon_time);
					$data->media_hours   = $time->media_hours;
					$data->media_minutes = $time->media_minutes;
					$data->media_seconds = $time->media_seconds;
					$data->studytext     = $db->escape($sermon->notes);
					$data->user_id       = $sermon->created_by;
					$data->hits          = $sermon->hits;

					if (!$data->hits)
					{
						$data->hits = 0;
					}
					$data->published = $sermon->state;
					$data->alias     = $sermon->alias;

					if (!$db->insertObject('#__bsms_studies', $data, 'id'))
					{
						$result_table .= '<tr><td>' . JText::_('JBS_IBM_ERROR_OCCURED_CREATING_SERMONS_SS') . '</td></tr>';
					}
					else
					{
						$updateds = $db->getAffectedRows();
						$adds     = $adds + $updateds;
					}
					$query = $db->getQuery(true);
					$query->select('id')->from('#__bsms_studies')->where('published = ' . 1)->where('id desc');
					$db->setQuery($query, 0, 1);
					$study              = $db->loadAssoc();
					$data1              = new stdClass;
					$data1->study_id    = $study['id'];
					$data1->server      = $serverid;
					$data1->filename    = $sermon->audiofile;
					$data1->published   = 1;
					$data1->createdate  = $studydate;
					$data1->media_image = 1;
					$data1->downloads   = 1;
					$data1->plays       = 0;

					// @todo we need to check and see if this work with $db->getAffectedRows(); TOM
					if (!$db->insertObject('#__bsms_mediafiles', $data1, 'id'))
					{
						$result_table .= '<tr><td>' . JText::_('JBS_IBM_ERROR_CREATING_MEDIAFILES_SS') . '</td></tr>';
					}
					else
					{
						$updated2 = $db->getAffectedRows();
						$add2     = $add2 + $updated2;
					}
					if ($sermon->videofile)
					{
						$data2              = new stdClass;
						$data2->study_id    = $study['id'];
						$data2->server      = $serverid;
						$data2->filename    = $sermon->videofile;
						$data2->published   = 1;
						$data2->createdate  = $studydate;
						$data2->media_image = 5;
						$data2->downloads   = 0;
						$data2->plays       = 0;

						// @todo we need to check and see if this work with $db->getAffectedRows();  TOM
						if (!$db->insertObject('#__bsms_mediafiles', $data2, 'id'))
						{
							$result_table .= '<tr><td>' . JText::_('JBS_IBM_ERROR_CREATING_MEDIAFILES_SS') . '</td></tr>';
						}
						else
						{
							$updated2 = $db->getAffectedRows();
							$add2     = $add2 + $updated2;
						}

					}

				} // End foreach sermon
				$result_table .= '<tr><td>' . $adds . ' ' . JText::_('JBS_IBM_SERMONS_CREATED_FOR') . ' ' . $teachername . '</td></tr>';
				$result_table .= '<tr><td>' . $add2 . ' ' . JText::_('JBS_IBM_MEDIAFILES_CREATED') . ' ' . $teachername . '</td></tr>';

			} // End of foreach $teachers as $teacher
		}

		$result_table .= '<tr><td>' . $add . ' ' . JText::_('JBS_IBM_TEACHERS_CREATED') . '</td></tr>';
		$result_table .= '</table>';

		return $result_table;
	}

	/**
	 * Get Version of Booknumber
	 *
	 * @param   string $sermon  Book of the Bible.
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
	 * @param   string $time  ?
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
