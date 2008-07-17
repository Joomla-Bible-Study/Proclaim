<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class biblestudyViewmediafilesedit extends JView
{
	
	function display($tpl = null)
	{
		
		$mediafilesedit		=& $this->get('Data');
		$isNew		= ($mediafilesedit->id < 1);
		//$editor =& JFactory::getEditor();
		//this->assignRef( 'editor', $editor );
		$lists = array();
		//$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		//JToolBarHelper::title(   JText::_( 'Edit Media' ).': <small><small>[ ' . $text.' ]</small></small>' );
		//JToolBarHelper::save();
		//if ($isNew)  {
			//JToolBarHelper::cancel();
			// initialise new record
			//$studiesedit->teacher_id 	= JRequest::getVar( 'teacher_id', 0, 'post', 'int' );
			
		//} else {
			// for existing items the button is renamed `close`
			//JToolBarHelper::cancel( 'cancel', 'Close' );
		//}
		//jimport( 'joomla.i18n.help' );
		//JToolBarHelper::help( 'biblestudy.mediafilesedit', true );
		// build the html select list for ordering
		
		$database	= & JFactory::getDBO();
		$study = JRequest::getVar('new', '0', 'get', 'int' );
		if ($study == 1) {
			$query = 'SELECT id, studytitle, studydate FROM #__bsms_studies ORDER BY id DESC LIMIT 1';
			$database->setQuery($query);
			$newstudy = $database->loadObject();
			$this->assignRef('newstudy', $newstudy);
		}
		//$this->assignRef('newstudy', '0');
		$lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $mediafilesedit->published);
		
		$lists['link_type'] = JHTML::_('select.booleanlist','link_type', 'class="inputbox"', $mediafilesedit->link_type);
		$lists['internal_viewer'] = JHTML::_('select.booleanlist', 'internal_viewer', 'class="inputbox"', $mediafilesedit->internal_viewer);
		$query = "SELECT id AS value, CONCAT(studytitle,' - ', date_format(studydate, '%a %b %e %Y'), ' - ', studynumber) AS text FROM #__bsms_studies ORDER BY studydate DESC";
		$database->setQuery($query);
		//$studies = $database->loadObjectList();
		$studies[] = JHTML::_('select.option', '0', '- '. JText::_( 'Select a Study' ) .' -' );
		$studies = array_merge($studies,$database->loadObjectList() );
		$lists['studies'] = JHTML::_('select.genericlist', $studies, 'study_id', 'class="inputbox" size="1" ', 'value', 'text', $mediafilesedit->study_id);
		
		$query5 = 'SELECT id AS value, server_path AS text, published'
			. ' FROM #__bsms_servers'
			. ' WHERE published = 1'
			. ' ORDER BY server_path';
		$database->setQuery( $query5 );
		//$servers = $database->loadObjectList();
		$types5[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Server' ) .' -' );
		$types5 			= array_merge( $types5, $database->loadObjectList() );
		$lists['server'] = JHTML::_('select.genericlist', $types5, 'server', 'class="inputbox" size="1" ', 'value', 'text',  $mediafilesedit->server );

			$query6 = 'SELECT id AS value, folderpath AS text, published'
			. ' FROM #__bsms_folders'
			. ' WHERE published = 1'
			. ' ORDER BY folderpath';
		$database->setQuery( $query6 );
		//$folders = $database->loadObjectList();
		$types6[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Server Folder' ) .' -' );
		$types6 			= array_merge( $types6, $database->loadObjectList() );
		$lists['path'] = JHTML::_('select.genericlist', $types6, 'path', 'class="inputbox" size="1" ', 'value', 'text',  $mediafilesedit->path );
		
		$query = 'SELECT id AS value, title AS text FROM #__bsms_podcast WHERE published = 1 ORDER BY title ASC';
		$database->setQuery($query);
		//$podcast = $database->loadObjectList();
		$podcast[] = JHTML::_('select.option', '0', '- '. JText::_('Select a Podcast').' -');
		$podcast = array_merge($podcast, $database->loadObjectList());
		$lists['podcast'] 	= JHTML::_('select.genericlist',	$podcast, 'podcast_id', 'class="inputbox" size="1" ', 'value', 'text', $mediafilesedit->podcast_id);
			$query7 = 'SELECT id AS value, media_image_name AS text, published'
			. ' FROM #__bsms_media'
			. ' WHERE published = 1'
			. ' ORDER BY media_image_name';
		$database->setQuery( $query7 );
		//$extensions = $database->loadObjectList();
		$types7[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Media Type' ) .' -' );
		$types7 			= array_merge( $types7, $database->loadObjectList() );
		$lists['image'] = JHTML::_('select.genericlist', $types7, 'media_image', 'class="inputbox" size="1" ', 'value', 'text',  $mediafilesedit->media_image );
		

		$query = 'SELECT id AS value, mimetext AS text, published FROM #__bsms_mimetype WHERE published = 1 ORDER BY id ASC';
		$database->setQuery($query);
		$mimeselect[] = JHTML::_('select.option', '0', '- '. JText::_( 'Select a Mime Type' ) .' -' );
		$mime = array_merge($mimeselect, $database->loadObjectList() );
		$lists['mime_type'] = JHTML::_('select.genericlist', $mime, 'mime_type', 'class="inputbox" size="1" ', 'value', 'text', $mediafilesedit->mime_type);
		
		// build the html select list for ordering
	$query = 'SELECT ordering AS value, ordering AS text'
	. ' FROM #__bsms_mediafiles'
	. ' WHERE study_id = '. (int) $mediafilesedit->study_id
	. ' ORDER BY ordering'
	;
	
	$lists['ordering'] 			= JHTML::_('list.specificordering',  $mediafilesedit, $mediafilesedit->id, $query, 1 );	
	
		$this->assignRef('lists',		$lists);
		$this->assignRef('mediafilesedit',		$mediafilesedit);
		parent::display($tpl);
	}
}
?>