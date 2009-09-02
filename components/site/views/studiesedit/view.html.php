<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
jimport('joomla.application.component.helper');


class biblestudyViewstudiesedit extends JView
{
	
	function display($tpl = null)
	{
		
		global $mainframe, $option;
		$studiesedit		=& $this->get('Data');
		$isNew		= ($studiesedit->id < 1);
		$editor =& JFactory::getEditor();
		$this->assignRef( 'editor', $editor );
		$lists = array();
		$document =& JFactory::getDocument();
		$document->addScript(JURI::base().'components/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/noconflict.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/biblestudy.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/plugins/jquery.selectboxes.js');
		//$document->addStylesheet(JURI::base().'components/com_biblestudy/assets/css/icon.css');
		$document->addStylesheet(JURI::base().'administrator/templates/system/css/system.css');
		$document->addStylesheet(JURI::base().'media/system/css/modal.css');
		$document->addStylesheet(JURI::base().'administrator/templates/khepri/css/rounded.css');
		$document->addStylesheet(JURI::base().'administrator/templates/khepri/css/template.css');
		$document->addStylesheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
		$config =& JComponentHelper::getParams( 'com_biblestudy' );
		$admin=& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		$params =& $mainframe->getPageParameters();
		//$templatemenuid = $params->get('templatemenuid', 1);
		//dump ($admin_params, 'template: ');
		//JRequest::setVar( 'templatemenuid', $templatemenuid, 'get');
		//$template = $this->get('Template');
		//$params = new JParameter($template[0]->params);
		$user =& JFactory::getUser();
		$entry_user = $user->get('gid');
		$entry_access = $admin_params->get('entry_access', 24) ;
		$allow_entry = $admin_params->get('allow_entry_study', 0);
		if ($allow_entry < 1) {return JError::raiseError('403', JText::_('Access Forbidden')); }
		if (!$entry_user) { $entry_user = 0; }
		if ($entry_user < $entry_access ){return JError::raiseError('403', JText::_('Access Forbidden'));}
		
		
		$studiesedit =& $this->get('Data');
		$books =& $this->get('books');
		
		//Add the params from the model
		/*
		$paramsdata = $studiesedit->params;
		$paramsdefs = JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'studiesedit.xml';
		$params = new JParameter($paramsdata, $paramsdefs);
		$this->assignRef('params', $params);
		*/
		//Manipulate Data
		/*
		$scriptures = explode(';', $studiesedit->scripture);
		foreach($scriptures as $scripture){
			$split = explode(' ', $scripture);
			$scriptureBlocks[$scripture]['bookId'] =  $split[0];
			$scriptureBlocks[$scripture]['text'] = $split[1];
		}
		array_unshift($books, JHTML::_('select.option', '0', JText::_('- Select a Book -')));
		*/
		
		require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'toolbar.php' );
		$toolbar = biblestudyHelperToolbar::getToolbar();
		$this->assignRef('toolbar', $toolbar);
		
		$javascript			= 'onchange="changeDisplayImage();"';
		$directory = DS.'images'.DS.$admin_params->get('study_images', 'stories');
		$studypath = JPATH_SITE.DS.'images'.DS.$admin_params->get('study_images', 'stories');
		$fileList 	= JFolder::files($studypath);
		foreach($fileList as $key=>$value)
		{
			$folderfinal1 = new JObject();
			$folderfinal1->value = $value;
			$folderfinal1->id = $key;
			$folderfinal2[] = $folderfinal1;
		}
		array_unshift($folderfinal2, JHTML::_('select.option', '0', '- '.JText::_('No Image').' -', 'value', 'value'));
		//$lists['thumb'] = JHTML::_('select.genericlist',  $folderfinal2, 'thumbnailm', 'class="inputbox" size="1"', 'value', 'value', $studiesedit->thumbnailm );
		
		$lists['thumbnailm']	= JHTML::_('list.images',  'thumbnailm', $studiesedit->thumbnailm, $javascript, $directory, "bmp|gif|jpg|png|swf"  );
		// build the html select list for ordering
		$database	= & JFactory::getDBO();
		$query = "SELECT id"
			. "\nFROM #__menu"
			. "\nWHERE link ='index.php?option=com_biblestudy&view=studieslist' and published = 1";
		$database->setQuery($query);
		$menuid = $database->loadResult();
		$this->assignRef('menuid',$menuid);
			$query = 'SELECT id AS value, teachername AS text, published'
			. ' FROM #__bsms_teachers'
			. ' WHERE published = 1'
			. ' ORDER BY teachername';
		$database->setQuery( $query );
		$teachers = $database->loadObjectList();
		$types[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Teacher' ) .' -' );
		$types 			= array_merge( $types, $database->loadObjectList() );
		$lists['teacher_id'] = JHTML::_('select.genericlist', $types, 'teacher_id', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->teacher_id );
		$lists['published'] = JHTML::_('select.booleanlist', 'published', '', $studiesedit->published);
		$lists['comments'] = JHTML::_('select.booleanlist', 'comments', 'class="inputbox"', $studiesedit->comments);
		
			$query2 = 'SELECT booknumber AS value, bookname AS text, published'
			. ' FROM #__bsms_books'
			. ' WHERE published = 1'
			. ' ORDER BY booknumber';
		$database->setQuery( $query2 );
		$books = $database->loadObjectList();
		$types2[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Book' ) .' -' );
		$types2 			= array_merge( $types2, $database->loadObjectList() );
		$lists['booknumber'] = JHTML::_('select.genericlist', $types2, 'booknumber', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->booknumber );
		$lists['booknumber2'] = JHTML::_('select.genericlist',$types2, 'booknumber2', 'class="inputbox" size="1" ', 'value', 'text', $studiesedit->booknumber2 );
			
			$query3 = 'SELECT id AS value, series_text AS text, published'
			. ' FROM #__bsms_series'
			. ' WHERE published = 1'
			. ' ORDER BY id';
		$database->setQuery( $query3 );
		$series_id = $database->loadObjectList();
		$types3[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Series' ) .' -' );
		$types3 			= array_merge( $types3, $database->loadObjectList() );
		$lists['series_id'] = JHTML::_('select.genericlist', $types3, 'series_id', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->series_id );
		
			$query4 = 'SELECT id AS value, message_type AS text, published'
			. ' FROM #__bsms_message_type'
			. ' WHERE published = 1'
			. ' ORDER BY message_type';
		$database->setQuery( $query4 );
		$messagetype = $database->loadObjectList();
		$types4[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Message Type' ) .' -' );
		$types4 			= array_merge( $types4, $database->loadObjectList() );
		$lists['messagetype'] = JHTML::_('select.genericlist', $types4, 'messagetype', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->messagetype );
		
		$query8 = 'SELECT id AS value, topic_text AS text, published'
			. ' FROM #__bsms_topics'
			. ' WHERE published = 1'
			. ' ORDER BY topic_text';
		$database->setQuery( $query8 );
		$topics = $database->loadObjectList();
		$topics_id[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Topic' ) .' -' );
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
		
		$query4 = 'SELECT id AS value, location_text AS text, published'
			. ' FROM #__bsms_locations'
			. ' WHERE published = 1'
			. ' ORDER BY id';
		$database->setQuery( $query4 );
		//$location_id = $database->loadObjectList();
		$types10[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Location' ) .' -' );
		$types10 			= array_merge( $types10, $database->loadObjectList() );
		$lists['location_id'] = JHTML::_('select.genericlist', $types10, 'location_id', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->location_id );
		
		$query7 = 'SELECT id AS value, media_image_name AS text, published'
			. ' FROM #__bsms_media'
			. ' WHERE published = 1'
			. ' ORDER BY media_image_name';
		$database->setQuery( $query7 );
		$types7[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a CD Image' ) .' -' );
		$types7 			= array_merge( $types7, $database->loadObjectList() );
		$lists['image_cd'] = JHTML::_('select.genericlist', $types7, 'image_cd', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->image_cd );
		
		$query7 = 'SELECT id AS value, media_image_name AS text, published'
			. ' FROM #__bsms_media'
			. ' WHERE published = 1'
			. ' ORDER BY media_image_name';
		$database->setQuery( $query7 );
		$types8[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a DVD Image' ) .' -' );
		$types8 			= array_merge( $types8, $database->loadObjectList() );
		$lists['image_dvd'] = JHTML::_('select.genericlist', $types8, 'image_dvd', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->image_dvd );
		
		$query5 = 'SELECT id AS value, server_path AS text, published'
			. ' FROM #__bsms_servers'
			. ' WHERE published = 1'
			. ' ORDER BY server_path';
		$database->setQuery( $query5 );
		$types5[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select DVD Store' ) .' -' );
		$types5 			= array_merge( $types5, $database->loadObjectList() );
		$lists['server_dvd'] = JHTML::_('select.genericlist', $types5, 'server_dvd', 'class="inputbox" size="1" ', 'value', 'text',  $studiesedit->server_dvd );
		
		$query5 = 'SELECT id AS value, server_path AS text, published'
			. ' FROM #__bsms_servers'
			. ' WHERE published = 1'
			. ' ORDER BY server_path';
		$database->setQuery( $query5 );
		$types6[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a CD Store' ) .' -' );
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