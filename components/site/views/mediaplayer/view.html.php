<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class biblestudyViewmediaplayer extends JView
{
	
	function display($tpl = null) 
	{
		global $mainframe, $option;
		$db		=& JFactory::getDBO();
		//$mediaplayer		=& $this->get('Data');
		$query = "SELECT id"
			. "\nFROM #__menu"
			. "\nWHERE link ='index.php?option=com_biblestudy&view=studieslist'";
		$db->setQuery($query);
		$menuid = $db->loadResult();
		$this->assignRef('menuid',$menuid);
		$params =& $mainframe->getPageParameters();
		//$params = &JComponentHelper::getParams($option);
		//$params = &JComponentHelper::getParams( 'com_biblestudy' );
		$id = JRequest::getVar('id', 0,'GET','INT');
		$query = ' SELECT mf.id AS mfid, mf.study_id, mf.server, mf.path, mf.filename, mf.size, mf.mime_type, mf.podcast_id, mf.published AS mfpub, mf.createdate, mf.mediacode,'
			. ' s.id AS sid, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.verse_begin, s.chapter_end, s.verse_end, s.studytitle, s.studyintro, s.published AS spub,'
			. ' s.media_hours, s.studytext, s.media_minutes, s.media_seconds, s.studynumber, st.id AS stid, st.series_text AS stext,'
			. ' sr.id AS srid, sr.server_path,'
			. ' f.id AS fid, f.folderpath,'
			. ' t.id AS tid, t.teachername,'
			. ' b.id AS bid, b.booknumber AS bnumber, b.bookname, s.booknumber2, s.chapter_begin2, s.chapter_end2, s.verse_begin2, s.verse_end2,'
			. ' mt.id AS mtid, mt.mimetype'
			. ' FROM #__bsms_mediafiles AS mf'
			. ' LEFT JOIN #__bsms_studies AS s ON (s.id = mf.study_id)'
			. ' LEFT JOIN #__bsms_servers AS sr ON (sr.id = mf.server)'
			. ' LEFT JOIN #__bsms_folders AS f ON (f.id = mf.path)'
			. ' LEFT JOIN #__bsms_books AS b ON (b.booknumber = s.booknumber)'
			. ' LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id)'
			. ' LEFT JOIN #__bsms_mimetype AS mt ON (mt.id = mf.mime_type)'
			. ' LEFT JOIN #__bsms_series AS st ON (st.id = s.series_id)'
			. ' WHERE mf.id = '.$id;
			$db->setQuery( $query );
			$mediaplayer = $db->loadObject();
		$studyfile = $mediaplayer->server_path.$mediaplayer->folderpath.$mediaplayer->filename;
		$mediacode = $mediaplayer->mediacode;
		$mediacode = str_replace("'",'"',$mediacode);
		$this->assignRef('mediaplayer',		$mediaplayer);
		//from avreloaded
		//$plcode = preg_replace('#\s+#', ' ', $params->get('mediacode', ''));
          //      $res = $app->triggerEvent('onAvReloadedGetVideo', array($plcode));
		$mediacode = str_replace('-',$studyfile,$mediacode);
		$output = '';
		//$mediacode will be from the mediacode field and should consist of two codes {code} and{/code}. we the append {code1}the file name and path {/code1}
		if (JPluginHelper::importPlugin('content', 'avreloaded')) {
			$app = &JFactory::getApplication();
			$res = $app->triggerEvent('onAvReloadedGetVideo', array($mediacode));
			if (is_array($res) && (count($res) == 1)) {
				$output = $res[0];
			}
		}
		$query = 'SELECT c.* FROM #__bsms_comments AS c WHERE c.published = 1'
		.' AND c.study_id = '.$this->mediaplayer->sid.' ORDER BY c.comment_date ASC';
		$db->setQuery($query);
		$comments = $db->loadObjectList();
		$this->assignRef('comments', $comments);
		//$this->assignRef('studyfile', $studyfile);
		$this->assignRef('params', $params);
		$this->assignRef('mediacode', $mediacode);
		$this->assignRef('output', $output);
		
		parent::display($tpl);
	}
}
?>