<?php

/**
 * @desc script to convert SermonSpeaker 3.4 to Joomla Bible Study
 * @author Joomla Bible Study
 * @copyright 2010
 */
defined('_JEXEC') or die();

class JBSConvert
{

	function convertSS()
	{
		$result_table = '<table><tr><td><strong>'.JText::_('Please note any error messages below').'</strong></td></tr>';
		$db = JFactory::getDBO();
		//Make a server record
		$base = JURI::base();
		$site = str_replace('/administrator/','',$base);
		$query = 'INSERT INTO #__bsms_servers SET `server_name` = "'.$site.'", `server_path` = "'.$site.'"';
		$db->setQuery($query);
		$db->query();
		if ($db->getErrorNum() > 0)
		{
			$error = $db->getErrorMsg();
			$result_table .= '<tr><td>'.JText::_('An error occured while creating server record').': '.$error.'</td></tr>';
		}
		else
		{
			$result_table .= '<tr><td>'.JText::_('Server record added').'</td></tr>';
		}
		$query = 'SELECT * FROM #__bsms_servers ORDER BY `id` DESC LIMIT 1';
		$db->setQuery($query);
		$db->query();
		$server = $db->loadAssoc();
		$serverid = $server['id'];


		//Series Records
		$query = 'SELECT * FROM #__sermon_series';
		$db->setQuery($query);
		$db->query();
		$num_rows = $db->getNumRows();if ($num_rows < 1)
		{
			$result_table .= '<tr><td><span style="font-color: red;">'.JText::_('No series found in Sermon Speaker database').'</span>';
		}
		else
		{
			$series = $db->loadObjectList();
			$addse = 0;
			foreach ($series AS $single)
			{
				$id = $single->id;
				$series_text = $single->series_title;
				$description = $single->series_description;
				$published = $single->published;
				$series_thumbnail = $single->avatar;

				$query = 'INSERT INTO #__bsms_series SET `series_text` = "'.$series_text.'", `description` = "'.$description.'", `published` = '.$published.
            ', `series_thumbnail` = "'.$series_thumbnail.'", `teacher` = '.$id;
				$db->setQuery($query);
				$db->query();
				if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
					$result_table .= '<tr><td>'.JText::_('An error occured while creating series records').': '.$error.'</td></tr>';
				}
				else
				{
					$updatedse = 0;
					$updatedse = $db->getAffectedRows(); //echo 'affected: '.$updated;
					$addse = $addse + $updatedse;
				}
			} //end foreach $series as $single
			$result_table .= '<tr><td>'.$addse.' '.JText::_('Series converted').'</td></tr>';
		}

		//Teacher Records
		$query = 'SELECT * FROM #__sermon_speakers';
		$db->setQuery($query);
		$db->query();
		$numrows = $db->getNumRows();
		if ($numrows < 1)
		{
			$result_table .= '<tr><td><span style="font-color: red;">'.JText::_('No teachers found in Sermon Speaker component').'</span></td></tr>';

		}

		$teachers = $db->loadObjectList();

		//Loop through each record and map fields then insert into JBS
		$add = 0;
		foreach ($teachers AS $teacher)
		{
			$id = $teacher->id;
			$teachername = $teacher->name;
			$website = $teacher->website;
			$information = $teacher->bio;
			$image = $teacher->pic;
			$thumb = $teacher->pic;
			$published = $teacher->published;
			$ordering = $teacher->ordering;
			$catid = $teacher->catid;
			$short = $teacher->bio;

			$query = 'INSERT INTO #__bsms_teachers SET `teachername` = "'.$teachername.'", `website` = "'.$website.'", `information` = "'.$information.
        '", `image` = "'.$image.'", `thumb` = "'.$thumb.'", `published` = "'.$published.'", `ordering` = "'.$ordering.'", `catid` = "'.$catid.
        '", `list_show` = "1", `short` = "'.$short.'"';
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$result_table .= '<tr><td>'.JText::_('An error occured while creating teacher records').': '.$error.'</td></tr>';
			}
			else
			{
				$updated = 0;
				$updated = $db->getAffectedRows(); //echo 'affected: '.$updated;
				$add = $add + $updated;
			}
			$query = 'SELECT `id` FROM #__bsms_teachers ORDER BY `id` DESC LIMIT 1';
			$db->setQuery($query);
			$db->query();
			$lastteacher = $db->loadAssoc();
			$teacher_id = $lastteacher['id'];
			 

			 
			$query = 'SELECT s.*, se.id AS sid, se.teacher AS teacher
   FROM #__sermon_sermons AS s
   LEFT JOIN #__bsms_series AS se ON (s.series_id = se.teacher) 
   WHERE s.speaker_id = '.$teacher->id;
			$db->setQuery($query);
			$db->query();
			$num_rows = $db->getNumRows();
			if ($num_rows < 1)
			{
				$result_table .= '<tr><td><span style="font-color: red;">'.JText::_('No sermons found in Sermon Speaker database').'</span>';
			}
			else
			{
				$sermons = $db->loadObjectList();
				$adds = 0;
				$add2 = 0;
				foreach ($sermons AS $sermon)
				{
					$id = $sermon->id;
					// $teacher_id = $sermon->speaker_id;
					// $series_id = $sermon->series_id;
					$series_id = $sermon->sid;
					$studytitle = $sermon->sermon_title;
					$studynumber = $sermon->sermon_number;

					$scripture = $this->getVerses($sermon->sermon_scripture);
					$booknumber = $scripture->booknumber;
					$chapter_begin = $scripture->chapter_begin;
					$chapter_end = $scripture->chapter_end;
					$verse_begin = $scripture->verse_begin;
					$verse_end = $scripture->verse_end;

					$studydate = $sermon->sermon_date;

					$time = $this->getTime($sermon->sermon_time);
					$media_hours = $time->media_hours;
					$media_minutes = $time->media_minutes;
					$media_seconds = $time->media_seconds;
					$studytext = $sermon->notes;
					$user_id = $sermon->created_by;
					$hits = $sermon->hits; if (!$hits){
						$hits = 0;
					}
					$published = $sermon->published;

					//Study record
					$query = 'INSERT INTO #__bsms_studies SET `studydate` = "'.$studydate.'", `teacher_id` = "'.$teacher_id.'", `studynumber` = "'.$id.'", `booknumber` = "'
					.$booknumber.'", `chapter_begin` = "'.$chapter_begin.'", `chapter_end` = "'.$chapter_end.'", `verse_begin` = "'.$verse_begin.'", `verse_end` = "'.$verse_end
					.'", `hits` = '.$hits.', `user_id` = "'.$user_id.'", `studytitle` = "'.$studytitle.'", `media_hours` = "'.$media_hours.'", `media_minutes` = "'.$media_minutes
					.'", `media_seconds` = "'.$media_seconds.'", `series_id` = "'.$series_id.'",`studytext` = "'.$studytext.'", `published` = '.$published;
					$db->setQuery($query);
					$db->query();

					if ($db->getErrorNum() > 0)
					{
						$error = $db->getErrorMsg();
						$result_table .= '<tr><td>'.JText::_('An error occured while creating sermon records').': '.$error.'</td></tr>';
					}
					else
					{
						$updateds = 0;
						$updateds = $db->getAffectedRows(); //echo 'affected: '.$updated;
						$adds = $adds + $updateds;
					}

					$query = 'SELECT id from #__bsms_studies ORDER BY id DESC LIMIT 1';
					$db->setQuery($query);
					$db->query();
					$study = $db->loadAssoc();
					$laststudy = $study['id'];
					$query = 'INSERT INTO #__bsms_mediafiles SET `study_id` = '.$laststudy.', `server` = '.$serverid.', `filename` = "'.$sermon->sermon_path.
            '", `published` = 1, `createdate` = "'.$studydate.'", `media_image` = 2, `downloads` = 0, `plays` = 0';
					$db->setQuery($query);
					$db->query();
					if ($db->getErrorNum() > 0)
					{
						$error = $db->getErrorMsg();
						$result_table .= '<tr><td>'.JText::_('An error occured while creating mediafile records').': '.$error.'</td></tr>';
					}
					else
					{
						$updated2 = 0;
						$updated2 = $db->getAffectedRows(); //echo 'affected: '.$updated;
						$add2 = $add2 + $updated2;
					}
				} // end foreach sermon
				$result_table .= '<tr><td>'.$adds.' '.JText::_('Sermons converted for ').$teachername.'</td></tr>';
				$result_table .= '<tr><td>'.$add2.' '.JText::_('Mediafiles converted for ').$teachername.'</td></tr>';


				 
			} //End of foreach $teachers as $teacher





		}

		$result_table .= '<tr><td>'.$add.' '.JText::_('Teachers converted').'</td></tr>';
		$result_table .= '</table>';
		return $result_table;
	}

	function getVerses ($sermon)
	{
		$sermonscripture->booknumber = '101';
		$bookname = substr_count(strtolower($sermon),'genesis'); if ($bookname > 0){
			$sermonscripture->booknumber = '101';
		}
		$bookname = substr_count(strtolower($sermon),'exodus'); if ($bookname > 0){
			$sermonscripture->booknumber = '102';
		}
		$bookname = substr_count(strtolower($sermon),'leviticus'); if ($bookname > 0){
			$sermonscripture->booknumber = '103';
		}
		$bookname = substr_count(strtolower($sermon),'numbers'); if ($bookname > 0){
			$sermonscripture->booknumber = '104';
		}
		$bookname = substr_count(strtolower($sermon),'deuteronomy'); if ($bookname > 0){
			$sermonscripture->booknumber = '105';
		}
		$bookname = substr_count(strtolower($sermon),'joshua'); if ($bookname > 0){
			$sermonscripture->booknumber = '106';
		}
		$bookname = substr_count(strtolower($sermon),'judges'); if ($bookname > 0){
			$sermonscripture->booknumber = '107';
		}
		$bookname = substr_count(strtolower($sermon),'ruth'); if ($bookname > 0){
			$sermonscripture->booknumber = '108';
		}
		$bookname = substr_count(strtolower($sermon),'1 samuel'); if ($bookname > 0){
			$sermonscripture->booknumber = '109';
		}
		$bookname = substr_count(strtolower($sermon),'2 samuel'); if ($bookname > 0){
			$sermonscripture->booknumber = '110';
		}
		$bookname = substr_count(strtolower($sermon),'1 kings'); if ($bookname > 0){
			$sermonscripture->booknumber = '111';
		}
		$bookname = substr_count(strtolower($sermon),'2 kings'); if ($bookname > 0){
			$sermonscripture->booknumber = '112';
		}
		$bookname = substr_count(strtolower($sermon),'1 chronicles'); if ($bookname > 0){
			$sermonscripture->booknumber = '113';
		}
		$bookname = substr_count(strtolower($sermon),'2 chronicles'); if ($bookname > 0){
			$sermonscripture->booknumber = '114';
		}
		$bookname = substr_count(strtolower($sermon),'ezra'); if ($bookname > 0){
			$sermonscripture->booknumber = '115';
		}
		$bookname = substr_count(strtolower($sermon),'nehemiah'); if ($bookname > 0){
			$sermonscripture->booknumber = '116';
		}
		$bookname = substr_count(strtolower($sermon),'esther'); if ($bookname > 0){
			$sermonscripture->booknumber = '117';
		}
		$bookname = substr_count(strtolower($sermon),'job'); if ($bookname > 0){
			$sermonscripture->booknumber = '118';
		}
		$bookname = substr_count(strtolower($sermon),'psalm'); if ($bookname > 0){
			$sermonscripture->booknumber = '119';
		}
		$bookname = substr_count(strtolower($sermon),'proverbs'); if ($bookname > 0){
			$sermonscripture->booknumber = '120';
		}
		$bookname = substr_count(strtolower($sermon),'ecclesiastes'); if ($bookname > 0){
			$sermonscripture->booknumber = '121';
		}
		$bookname = substr_count(strtolower($sermon),'song of solomon'); if ($bookname > 0){
			$sermonscripture->booknumber = '122';
		}
		$bookname = substr_count(strtolower($sermon),'isaiah'); if ($bookname > 0){
			$sermonscripture->booknumber = '123';
		}
		$bookname = substr_count(strtolower($sermon),'jeremiah'); if ($bookname > 0){
			$sermonscripture->booknumber = '124';
		}
		$bookname = substr_count(strtolower($sermon),'lamentations'); if ($bookname > 0){
			$sermonscripture->booknumber = '125';
		}
		$bookname = substr_count(strtolower($sermon),'ezekiel'); if ($bookname > 0){
			$sermonscripture->booknumber = '126';
		}
		$bookname = substr_count(strtolower($sermon),'daniel'); if ($bookname > 0){
			$sermonscripture->booknumber = '127';
		}
		$bookname = substr_count(strtolower($sermon),'hosea'); if ($bookname > 0){
			$sermonscripture->booknumber = '128';
		}
		$bookname = substr_count(strtolower($sermon),'joel'); if ($bookname > 0){
			$sermonscripture->booknumber = '129';
		}
		$bookname = substr_count(strtolower($sermon),'amos'); if ($bookname > 0){
			$sermonscripture->booknumber = '130';
		}
		$bookname = substr_count(strtolower($sermon),'obadiah'); if ($bookname > 0){
			$sermonscripture->booknumber = '131';
		}
		$bookname = substr_count(strtolower($sermon),'jonah'); if ($bookname > 0){
			$sermonscripture->booknumber = '132';
		}
		$bookname = substr_count(strtolower($sermon),'micah'); if ($bookname > 0){
			$sermonscripture->booknumber = '133';
		}
		$bookname = substr_count(strtolower($sermon),'nahum'); if ($bookname > 0){
			$sermonscripture->booknumber = '134';
		}
		$bookname = substr_count(strtolower($sermon),'habakkuk'); if ($bookname > 0){
			$sermonscripture->booknumber = '135';
		}
		$bookname = substr_count(strtolower($sermon),'zephaniah'); if ($bookname > 0){
			$sermonscripture->booknumber = '136';
		}
		$bookname = substr_count(strtolower($sermon),'haggai'); if ($bookname > 0){
			$sermonscripture->booknumber = '137';
		}
		$bookname = substr_count(strtolower($sermon),'zechariah'); if ($bookname > 0){
			$sermonscripture->booknumber = '138';
		}
		$bookname = substr_count(strtolower($sermon),'malachi'); if ($bookname > 0){
			$sermonscripture->booknumber = '139';
		}
		$bookname = substr_count(strtolower($sermon),'matthew'); if ($bookname > 0){
			$sermonscripture->booknumber = '140';
		}
		$bookname = substr_count(strtolower($sermon),'mark'); if ($bookname > 0){
			$sermonscripture->booknumber = '141';
		}
		$bookname = substr_count(strtolower($sermon),'luke'); if ($bookname > 0){
			$sermonscripture->booknumber = '142';
		}
		$bookname = substr_count(strtolower($sermon),'john'); if ($bookname > 0){
			$sermonscripture->booknumber = '143';
		}
		$bookname = substr_count(strtolower($sermon),'acts'); if ($bookname > 0){
			$sermonscripture->booknumber = '144';
		}
		$bookname = substr_count(strtolower($sermon),'romans'); if ($bookname > 0){
			$sermonscripture->booknumber = '145';
		}
		$bookname = substr_count(strtolower($sermon),'1 corinthians'); if ($bookname > 0){
			$sermonscripture->booknumber = '146';
		}
		$bookname = substr_count(strtolower($sermon),'2 corinthians'); if ($bookname > 0){
			$sermonscripture->booknumber = '147';
		}
		$bookname = substr_count(strtolower($sermon),'galatians'); if ($bookname > 0){
			$sermonscripture->booknumber = '148';
		}
		$bookname = substr_count(strtolower($sermon),'ephesians'); if ($bookname > 0){
			$sermonscripture->booknumber = '149';
		}
		$bookname = substr_count(strtolower($sermon),'philippians'); if ($bookname > 0){
			$sermonscripture->booknumber = '150';
		}
		$bookname = substr_count(strtolower($sermon),'colossians'); if ($bookname > 0){
			$sermonscripture->booknumber = '151';
		}
		$bookname = substr_count(strtolower($sermon),'1 thessalonians'); if ($bookname > 0){
			$sermonscripture->booknumber = '152';
		}
		$bookname = substr_count(strtolower($sermon),'2 thessalonians'); if ($bookname > 0){
			$sermonscripture->booknumber = '153';
		}
		$bookname = substr_count(strtolower($sermon),'1 timothy'); if ($bookname > 0){
			$sermonscripture->booknumber = '154';
		}
		$bookname = substr_count(strtolower($sermon),'2 timothy'); if ($bookname > 0){
			$sermonscripture->booknumber = '155';
		}
		$bookname = substr_count(strtolower($sermon),'titus'); if ($bookname > 0){
			$sermonscripture->booknumber = '156';
		}
		$bookname = substr_count(strtolower($sermon),'philemon'); if ($bookname > 0){
			$sermonscripture->booknumber = '157';
		}
		$bookname = substr_count(strtolower($sermon),'hebrews'); if ($bookname > 0){
			$sermonscripture->booknumber = '158';
		}
		$bookname = substr_count(strtolower($sermon),'james'); if ($bookname > 0){
			$sermonscripture->booknumber = '159';
		}
		$bookname = substr_count(strtolower($sermon),'1 peter'); if ($bookname > 0){
			$sermonscripture->booknumber = '160';
		}
		$bookname = substr_count(strtolower($sermon),'2 peter'); if ($bookname > 0){
			$sermonscripture->booknumber = '161';
		}
		$bookname = substr_count(strtolower($sermon),'1 john'); if ($bookname > 0){
			$sermonscripture->booknumber = '162';
		}
		$bookname = substr_count(strtolower($sermon),'2 john'); if ($bookname > 0){
			$sermonscripture->booknumber = '163';
		}
		$bookname = substr_count(strtolower($sermon),'3 john'); if ($bookname > 0){
			$sermonscripture->booknumber = '164';
		}
		$bookname = substr_count(strtolower($sermon),'jude'); if ($bookname > 0){
			$sermonscripture->booknumber = '165';
		}
		$bookname = substr_count(strtolower($sermon),'revelation'); if ($bookname > 0){
			$sermonscripture->booknumber = '166';
		}

		$firstspace = strpos($sermon,' ',2);
		$firstcolon = strpos($sermon,':');
		$firstdash = strpos($sermon,'-');

		$issecondcolon = substr_count($sermon,':',$firstcolon + 1);
		if ($issecondcolon) {
			$secondcolon = strpos($sermon, ':', $firstcolon + 1);
		}
		$sermonscripture->chapter_begin = substr($sermon,$firstspace + 1,($firstcolon - $firstspace) - 1);

		if (!$firstdash) {
			$sermonscripture->verse_begin = substr($sermon,$firstcolon + 1);
		}
		else {$sermonscripture->verse_begin = substr($sermon,$firstcolon + 1,$firstdash - ($firstcolon + 1));
		}
		if (!$issecondcolon) {
			$sermonscripture->chapter_end = '';
		}
		else
		{
			$sermonscripture->chapter_end = substr($sermon, $firstdash + 1,($secondcolon - $firstdash) - 1);
			$sermonscripture->verse_end = substr($sermon,$secondcolon + 1);
		}
		if (!$issecondcolon && $firstdash)
		{
			$sermonscripture->verse_end = substr($sermon, $firstdash + 1);
		}
		return $sermonscripture;
	}

	function getTime($time)
	{
		 
		$firstcolon = strpos($time, ':');
		$secondcolon = strpos($time, ':', $firstcolon + 1);
		if ($secondcolon)
		{
			$sermontime->media_hours = substr($time,0,$firstcolon);
			$sermontime->media_seconds = substr($time, $secondcolon + 1, 2);
			$sermontime->media_minutes = substr($time, $firstcolon + 1, 2);
		}
		else
		{
			if ($firstcolon == 1){
				$minuteslength = '1';
			} else {$minuteslength = '2';
			}
			$sermontime->media_seconds = substr($time, $firstcolon + 1, 2);
			$sermontime->media_minutes = substr($time, 0,$minuteslength);
		}
		 
		 
		return $sermontime;
	}
}
?>