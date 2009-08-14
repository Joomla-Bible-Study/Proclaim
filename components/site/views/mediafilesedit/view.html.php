<?php
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class biblestudyViewmediafilesedit extends JView {

	function display($tpl = null) {
		if (JPluginHelper::importPlugin('system', 'avreloaded')) {
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_avreloaded'.DS.'elements'.DS.'insertbutton.php');
			$mbutton = JElementInsertButton::fetchElementImplicit('mediacode',JText::_('AVR Media'));
			$this->assignRef('mbutton', $mbutton);
		}
		$mediafilesedit =& $this->get('Data');
		$studiesList =& $this->get('Studies');
		$serversList =& $this->get('Servers');
		$foldersList =& $this->get('Folders');
		$podcastsList =& $this->get('Podcasts');
		$mediaImages =& $this->get('MediaImages');
		$mimeTypes =& $this->get('MimeTypes');
		$ordering =& $this->get('Ordering');
		$document =& JFactory::getDocument();
		//$document->addStylesheet(JURI::base().'components/com_biblestudy/assets/css/icon.css');
		$document->addStylesheet(JURI::base().'administrator/templates/system/css/system.css');
		$document->addStylesheet(JURI::base().'media/system/css/modal.css');
		$document->addStylesheet(JURI::base().'administrator/templates/khepri/css/rounded.css');
		$document->addStylesheet(JURI::base().'administrator/templates/khepri/css/template.css');

		require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'toolbar.php' );
		$toolbar = biblestudyHelperToolbar::getToolbar();
		$this->assignRef('toolbar', $toolbar);
		$isNew		= ($mediafilesedit->id < 1);

		$lists = array();

		//Special tasks to perform depending on whether its a new study or not
		$newStudy = JRequest::getVar('new', null, 'GET', 'int');
		if(isset($newStudy)){
			$study = $this->get('Study');
			//@todo Bad practice to embed html here
			$this->assign('studies', '<strong>'.$study->studytitle.' - '.$study->studydate.'</strong>');
		}else {
			$studies[] = JHTML::_('select.option', '0', '- '. JText::_( 'Select a Study' ) .' -' );
			$studies = array_merge($studies, $studiesList);
			$studies = JHTML::_('select.genericlist', $studies, 'study_id', 'class="inputbox" size="1" ', 'value', 'text', $mediafilesedit->study_id);
			$this->assignRef('studies', $studies);
		}

		$lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $mediafilesedit->published);
		$lists['link_type'] = JHTML::_('select.booleanlist','link_type', 'class="inputbox"', $mediafilesedit->link_type);
		$lists['internal_viewer'] = JHTML::_('select.booleanlist', 'internal_viewer', 'class="inputbox"', $mediafilesedit->internal_viewer);

		$types5[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Server' ) .' -' );
		$types5 			= array_merge( $types5, $serversList);
		$lists['server'] = JHTML::_('select.genericlist', $types5, 'server', 'class="inputbox" size="1" ', 'value', 'text',  $mediafilesedit->server );


		$types6[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Server Folder' ) .' -' );
		$types6 			= array_merge( $types6, $foldersList);
		$lists['path'] = JHTML::_('select.genericlist', $types6, 'path', 'class="inputbox" size="1" ', 'value', 'text',  $mediafilesedit->path );



		$podcast[] = JHTML::_('select.option', '0', '- '. JText::_('Select a Podcast').' -');
		$podcast = array_merge($podcast, $podcastsList);
		$lists['podcast'] 	= JHTML::_('select.genericlist',	$podcast, 'podcast_id', 'class="inputbox" size="1" ', 'value', 'text', $mediafilesedit->podcast_id);

		;
		$types7[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Media Type' ) .' -' );
		$types7 			= array_merge( $types7, $mediaImages);
		$lists['image'] = JHTML::_('select.genericlist', $types7, 'media_image', 'class="inputbox" size="1" ', 'value', 'text',  $mediafilesedit->media_image );

		$mimeselect[] = JHTML::_('select.option', '0', '- '. JText::_( 'Select a Mime Type' ) .' -' );
		$mime = array_merge($mimeselect, $mimeTypes);
		$lists['mime_type'] = JHTML::_('select.genericlist', $mime, 'mime_type', 'class="inputbox" size="1" ', 'value', 'text', $mediafilesedit->mime_type);

		// build the html select list for ordering
		$lists['ordering'] = JHTML::_('list.specificordering',  $mediafilesedit, $mediafilesedit->id, $ordering, 1 );

		$this->assignRef('lists', $lists);
		$this->assignRef('mediafilesedit', 	$mediafilesedit);


		/**
		 * @desc The following AddScript methods are for future validation and form helpers via
		 * jquery framework.
		 */
		$document = JFactory::getDocument();
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/noconflict.js');

		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/plugins/jquery.validate.js');
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/validation/validateMedia.js');




		parent::display($tpl);
	}
}
?>