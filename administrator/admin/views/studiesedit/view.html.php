<?php
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class biblestudyViewstudiesedit extends JView {
	
	function display($tpl = null) {
		$mainframe =& JFactory::getApplication();

		$document =& JFactory::getDocument();
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		$document->addScript(JURI::base().'components/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/noconflict.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/biblestudy.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/plugins/jquery.selectboxes.js');
		
		$config =& JComponentHelper::getParams( 'com_biblestudy' );
		$admin=& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		
		//Get Data
		$studiesedit =& $this->get('Data');
		$books =& $this->get('books');
		$isNew		= ($studiesedit->id < 1);
		$editor =& JFactory::getEditor();
		$this->assignRef( 'editor', $editor );
		$lists = array();
		$text = $isNew ? JText::_( 'JBS_CMN_NEW' ) : JText::_( 'JBS_CMN_EDIT' );
		JToolBarHelper::title(   JText::_( 'JBS_STY_EDIT_STUDY' ).': <small><small>[ ' . $text.' ]</small></small>', 'studies.png' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();

		} else {
			JToolBarHelper::apply();
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		JToolBarHelper::custom( 'resetHits', 'reset.png', 'Reset Hits', 'JBS_STY_RESET_HITS', false, false );
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy', true );
		
		// build the html select list for ordering

		//Build the select list for the study image
		$imagesPath = JPATH_SITE.DS.'images'.DS.$admin_params->get('study_images', 'stories');
		$imageList 	= JFolder::files($imagesPath, null, null, null, array('index.html'));

		array_unshift($imageList, '- '.JText::_('JBS_CMN_NO_IMAGE').' -');
		
		foreach($imageList as $key=>$value) {
			$imageOptions[] = JHTML::_('select.option', $value, $value);
		}
		$imageOptions[0]->value = 0; //Set the value of the "- JBS_CMN_NO_IMAGE -" to 0. Makes it easier for jquery   // 2010-11-12 santon: need to be changed

		$lists['thumbnailm'] = JHTML::_('select.genericlist',  $imageOptions, 'thumbnailm', 'class="imgChoose" size="1"', 'value', 'text', $studiesedit->thumbnailm );

		$database	= & JFactory::getDBO();
		$query = 'SELECT id AS value, teachername AS text, published'
		. ' FROM #__bsms_teachers'
		. ' WHERE published = 1'
		. ' ORDER BY teachername';
		$database->setQuery( $query );
		$teachers = $database->loadObjectList();
		$types[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_TEACHER' ) .' -' );
		$types 			= array_merge( $types, $database->loadObjectList() );
		$lists['teacher_id'] = JHTML::_('select.genericlist', $types, 'teacher_id', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->teacher_id );
		$lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $studiesedit->published);
		$lists['comments'] = JHTML::_('select.booleanlist', 'comments', 'class="inputbox"', $studiesedit->comments);

		$query3 = 'SELECT id AS value, series_text AS text, published'
		. ' FROM #__bsms_series'
		. ' WHERE published = 1'
		. ' ORDER BY id';
		$database->setQuery( $query3 );
		$series_id = $database->loadObjectList();
		$types3[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_SERIE' ) .' -' );
		$types3 			= array_merge( $types3, $database->loadObjectList() );
		$lists['series_id'] = JHTML::_('select.genericlist', $types3, 'series_id', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->series_id );

		$query4 = 'SELECT id AS value, location_text AS text, published'
		. ' FROM #__bsms_locations'
		. ' WHERE published = 1'
		. ' ORDER BY id';
		$database->setQuery( $query4 );
		$location_id = $database->loadObjectList();
		$types10[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_LOCATION' ) .' -' );
		$types10 			= array_merge( $types10, $database->loadObjectList() );
		$lists['location_id'] = JHTML::_('select.genericlist', $types10, 'location_id', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->location_id );

		$query4 = 'SELECT id AS value, message_type AS text, published'
		. ' FROM #__bsms_message_type'
		. ' WHERE published = 1'
		. ' ORDER BY message_type';
		$database->setQuery( $query4 );
		$messagetype = $database->loadObjectList();
		$types4[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_MESSAGE_TYPE' ) .' -' );
		$types4 			= array_merge( $types4, $database->loadObjectList() );
		$lists['messagetype'] = JHTML::_('select.genericlist', $types4, 'messagetype', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->messagetype );

		$query8 = 'SELECT id AS value, topic_text AS text, published'
		. ' FROM #__bsms_topics'
		. ' WHERE published = 1'
		. ' ORDER BY topic_text';
		$database->setQuery( $query8 );
		$topics = $database->loadObjectList();
		$topics_id[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_TOPIC' ) .' -' );
		$topics_id 			= array_merge( $topics_id, $database->loadObjectList() );
		$lists['topics_id'] = JHTML::_('select.genericlist', $topics_id, 'topics_id', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->topics_id );

		$query = ' SELECT mf.id AS mfid, mf.study_id, mf.server, mf.path, mf.filename, mf.size, mf.mime_type, mf.podcast_id, mf.published AS mfpub, mf.createdate, mf.mediacode,'
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
		. ' WHERE mf.study_id = '.$studiesedit->id;
		$database->setQuery( $query );
		$mediafiles = $database->loadObjectList();

		$query7 = 'SELECT id AS value, media_image_name AS text, published'
		. ' FROM #__bsms_media'
		. ' WHERE published = 1'
		. ' ORDER BY media_image_name';
		$database->setQuery( $query7 );
		$types7[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_STY_SELECT_CD_IMAGE' ) .' -' );
		$types7 			= array_merge( $types7, $database->loadObjectList() );
		$lists['image_cd'] = JHTML::_('select.genericlist', $types7, 'image_cd', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->image_cd );

		$query7 = 'SELECT id AS value, media_image_name AS text, published'
		. ' FROM #__bsms_media'
		. ' WHERE published = 1'
		. ' ORDER BY media_image_name';
		$database->setQuery( $query7 );
		$types8[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_STY_SELECT_DVD_IMAGE' ) .' -' );
		$types8 			= array_merge( $types8, $database->loadObjectList() );
		$lists['image_dvd'] = JHTML::_('select.genericlist', $types8, 'image_dvd', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->image_dvd );

		$query5 = 'SELECT id AS value, server_path AS text, published'
		. ' FROM #__bsms_servers'
		. ' WHERE published = 1'
		. ' ORDER BY server_path';
		$database->setQuery( $query5 );
		$types5[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_STY_SELECT_DVD_STORE' ) .' -' );
		$types5 			= array_merge( $types5, $database->loadObjectList() );
		$lists['server_dvd'] = JHTML::_('select.genericlist', $types5, 'server_dvd', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->server_dvd );

		$query5 = 'SELECT id AS value, server_path AS text, published'
		. ' FROM #__bsms_servers'
		. ' WHERE published = 1'
		. ' ORDER BY server_path';
		$database->setQuery( $query5 );
		$types6[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_STY_SELECT_CD_STORE' ) .' -' );
		$types6 			= array_merge( $types6, $database->loadObjectList() );
		$lists['server_cd'] = JHTML::_('select.genericlist', $types6, 'server_cd', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->server_cd );

		$this->assignRef('admin_params', $admin_params);
		$this->assignRef('mediafiles', $mediafiles);
		$this->assignRef('lists',		$lists);
		$this->assignRef('studiesedit',		$studiesedit);
		$this->assignRef('books', $books);
		$this->assignRef('scriptures', $scriptureBlocks);
		
		parent::display($tpl);
	}
}
?>