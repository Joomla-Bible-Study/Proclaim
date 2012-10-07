<?php

/**
 * @desc This class gets podcast episodes and writes xml files of podcasts
 * @author Tom Fuller
 * @copyright 2010
 */
defined('_JEXEC') or die();
class JBSPodcast
{

	function getPodcast($id)
	{

		return $podcast;
	}

	function getEpisodes($id)
	{
		//here's where we look at each mediafile to see if they are connected to this podcast
		$db = JFactory::getDBO();
		$query = "SELECT id, params, published FROM `#__bsms_mediafiles` WHERE params LIKE '%podcasts%' and published = '1'";
		$db->setQuery($query);
		$results = $db->loadObjectList();
		$where = array();
		foreach ($results as $result)
		{
			$params = new JParameter($result->params);
			//dump ($params, 'params: ');
			$podcasts = $params->get('podcasts');
			 
			switch ($podcasts)
			{
				case is_array($podcasts) :
					foreach ($podcasts as $podcast)
					{
						if ($podids->id == $podcast)
						{
							$where[] = 'mf.id = '.$result->id;
						}
					}
					break;
				case -1 :
					break;
				case 0 :
					break;

				default :
					if ($podcasts == $podids->id)
				{
					$where[] = 'mf.id = '.$result->id;
					break;
				}
			}
		}
		$where 		= ( count( $where ) ? ' '. implode( ' OR ', $where ) : '' );
		if ($where)
		{
			$where = ' WHERE '.$where.' AND ';
		}
		else {return $msg= ' No media files were associated with a podcast. ';
		}
		//dump ($where, 'where: ');
		$query = 'SELECT p.id AS pid, p.podcastlimit,'
		. ' mf.id AS mfid, mf.study_id, mf.server, mf.path, mf.filename, mf.size, mf.mime_type, mf.podcast_id, mf.published AS mfpub, mf.createdate, mf.params,'
		. ' mf.docMan_id, mf.article_id,'
		. ' s.id AS sid, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.verse_begin, s.chapter_end, s.verse_end, s.studytitle, s.studyintro, s.published AS spub,'
		. ' s.media_hours, s.media_minutes, s.media_seconds,'
		. ' sr.id AS srid, sr.server_path,'
		. ' f.id AS fid, f.folderpath,'
		. ' t.id AS tid, t.teachername,'
		. ' b.id AS bid, b.booknumber AS bnumber, b.bookname,'
		. ' mt.id AS mtid, mt.mimetype'
		. ' FROM #__bsms_mediafiles AS mf'
		. ' LEFT JOIN #__bsms_studies AS s ON (s.id = mf.study_id)'
		. ' LEFT JOIN #__bsms_servers AS sr ON (sr.id = mf.server)'
		. ' LEFT JOIN #__bsms_folders AS f ON (f.id = mf.path)'
		. ' LEFT JOIN #__bsms_books AS b ON (b.booknumber = s.booknumber)'
		. ' LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id)'
		. ' LEFT JOIN #__bsms_mimetype AS mt ON (mt.id = mf.mime_type)'
		. ' LEFT JOIN #__bsms_podcast AS p ON (p.id = mf.podcast_id)'
		. $where.'s.published = 1 AND mf.published = 1 AND p.id = '.$id.' ORDER BY createdate DESC '.$limit;

		$db->setQuery( $query );
		$episodes = $db->loadObjectList();
		$episodedetail = '';
		foreach ($episodes as $episode)
		{
			// dump ($episode, 'episode: ');
		}
		//   return $episode;
	}

}

?>