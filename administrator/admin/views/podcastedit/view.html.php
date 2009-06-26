<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewpodcastedit extends JView
{
	
	function display($tpl = null)
	{
		
		$podcastedit		=& $this->get('Data');
		$isNew		= ($podcastedit->id < 1);
		
		$db	= & JFactory::getDBO();
		$query = 'SELECT p.id AS pid,'
			. ' mf.id AS mfid, mf.study_id, mf.server, mf.path, mf.filename, mf.size, mf.mime_type, mf.podcast_id, mf.published AS mfpub, mf.createdate,'
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
			. ' WHERE mf.podcast_id = '.$podcastedit->id.' ORDER BY mf.createdate DESC';
			$db->setQuery( $query );
			$episodes = $db->loadObjectList();
			
		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Podcast Edit' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		//JToolBarHelper::custom('writeXML','save.png','writeXML','Write XML', false, false);
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy.podcasts', true );
		
		
		$template = $this->get('Template');
		$tem[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Template' ) .' -' );
		$tem 			= array_merge( $tem, $template );
		$lists['templates']	= JHTML::_('select.genericlist',   $tem, 'detailstemplateid', 'class="inputbox" size="1" ', 'value', 'text', $podcastedit->detailstemplateid );
		
		
		$this->assignRef('podcastedit',		$podcastedit);
		$lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $podcastedit->published);
		$this->assignRef('lists',		$lists);
		$this->assignRef('episodes', $episodes);
		parent::display($tpl);
	}
}
?>