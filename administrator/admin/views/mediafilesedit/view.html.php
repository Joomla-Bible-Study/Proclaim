<?php
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
jimport ('joomla.application.component.helper');

class biblestudyViewmediafilesedit extends JView {

	function display($tpl = null) {
		
		if (JPluginHelper::importPlugin('system', 'avreloaded')) {
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_avreloaded'.DS.'elements'.DS.'insertbutton.php');
			$mbutton = JElementInsertButton::fetchElementImplicit('mediacode',JText::_('AVR Media'));
			$this->assignRef('mbutton', $mbutton);
		}

		//Check to see if Docman and/or VirtueMart installed
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
//		$vmenabled = JComponentHelper::getComponent('com_virtuemart',TRUE);
//		$dmenabled = JComponentHelper::getComponent('com_docman',TRUE);
	
		//dump ($vmenabled->enabled, 'vm');
		//dump ($dmenabled->enabled, 'dm');
		
		//Get Admin params
		$admin=& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		$this->assignRef('admin_params', $admin_params);
		$this->assignRef('admin', $admin);
		
		//To do - implement this from the site side.
		//$studiesList =& $this->get('Studies');
		//$serversList =& $this->get('Servers');
		//$foldersList =& $this->get('Folders');
		//$podcastsList =& $this->get('Podcasts');
		//$mediaImages =& $this->get('MediaImages');
		//$mimeTypes =& $this->get('MimeTypes');
		//$ordering =& $this->get('Ordering');
		
				//Get the js and css files

		$document =& JFactory::getDocument();
		$document->addStyleSheet(JURI::base().'components/com_biblestudy/css/mediafilesedit.css');
		$document->addScript(JURI::base().'components/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/noconflict.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/plugins/jquery.selectboxes.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/views/mediafilesedit.js');
		
		//Here we check to see if docMan or VirtueMart are there by looking at their data tables so we don't error out
		$vmenabled = NULL;
		$dmenabled = NULL;
		$db = JFactory::getDBO();
		$db->setQuery('SELECT name, enabled FROM #__components where enabled = 1');
		$db->query();
		$components = $db->loadObjectList();
		//dump ($tables, 'tables: ');
		foreach ($components as $component)
		{
			if ($component == 'VirtueMart')
			{
				$vmenabled = 1;
			}
			if ($component == 'DOCMan')
			{
				$dmenabled = 1;
			}
		} 
		//Get Data
		$mediafilesedit	=& $this->get('Data');
		
		$articlesSections =& $this->get('ArticlesSections');
		

		//Manipulate Data
		//Run only if Docman is enabled
		if ($dmenabled > 0)
		{
			$docManCategories =& $this->get('docManCategories');
			if ($docManCategories)
				{
					array_unshift($docManCategories, JHTML::_('select.option', null, '- Select a Category -', 'id', 'title'));
				}
		}
		
		//articles
		array_unshift($articlesSections, JHTML::_('select.option', null, '- Select a Section -', 'id', 'title'));
		
		//Run only if Virtuemart enabled
		if ($vmenabled > 0)
		{
			$virtueMartCategories =& $this->get('virtueMartCategories');
			if ($virtueMartCategories)
			{
				array_unshift($virtueMartCategories, JHTML::_('select.option', null, '- Select a Category -', 'id', 'title'));
			}
		}
		$isNew		= ($mediafilesedit->id < 1);
		
		//Retrieve any Docman items or articles that may exist
		$model = $this->getModel();
		
		//Add the params from the model
		$paramsdata = $mediafilesedit->params;
		$paramsdefs = JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'mediafilesedit.xml';
		$params = new JParameter($paramsdata, $paramsdefs);
		$this->assignRef('params', $params);
		
		//dump($mediafilesedit);
		
		//if ($dmenabled)
		//{
			if($mediafilesedit->docMan_id != 0 && !$isNew) {
				$this->assignRef('docManItem', $model->getDocManItem($mediafilesedit->docMan_id));
				$this->assign('docManStyle', 'display: none');
			}
			$this->assignRef('docManCategories', $docManCategories);
		//}
		
		if($mediafilesedit->article_id != 0 && !$isNew){
			$this->assignRef('articleItem', $model->getArticleItem($mediafilesedit->article_id));
			$this->assign('articleStyle', 'display: none');
		}
		
		//if ($vmenabled)
		//{
			if($mediafilesedit->virtueMart_id != 0 && !$isNew){
				$this->assignRef('virtueMartItem', $model->getVirtueMartItem($mediafilesedit->virtueMart_id));
				$this->assign('virtueMartStyle', 'display: none');
			}
			$this->assignRef('virtueMartCategories', $virtueMartCategories);
		//}
		
		//$editor =& JFactory::getEditor();
		//this->assignRef( 'editor', $editor );
		$lists = array();
		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Edit Media' ).': <small><small>[ ' . $text.' ]</small></small>', 'mp3.png' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		
			// initialise new record
			//$studiesedit->teacher_id 	= JRequest::getVar( 'teacher_id', 0, 'post', 'int' );

		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		JToolBarHelper::custom( 'resetDownloads', 'download.png', 'Reset Download Hits', 'Reset Download Hits', false, false );
		}
		//JToolBarHelper::media_manager( '/' );
		// Add an upload button and view a popup screen width 550 and height 400
		$alt = "Upload";
		$bar=& JToolBar::getInstance( 'toolbar' );
		//$bar->appendButton( 'Popup', 'upload', $alt, 'index.php', 650, 500 );
		$bar->appendButton( 'Popup', 'upload', $alt, "index.php?option=com_media&tmpl=component&task=popupUpload&directory=", 650, 400 );
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy.mediafilesedit', true );
		// build the html select list for ordering

		$database	= & JFactory::getDBO();
			
		$lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $mediafilesedit->published);
		
		$lists['link_type'] = JHTML::_('select.booleanlist','link_type', 'class="inputbox"', $mediafilesedit->link_type);

		$lists['internal_viewer'] = JHTML::_('select.booleanlist', 'internal_viewer', 'class="inputbox"', $mediafilesedit->internal_viewer);
		
		if ($admin_params->get('show_location_media') > 0)
			{
				$query = "SELECT s.id AS value, CONCAT(s.studytitle,' - ', date_format(s.studydate, '%a %b %e %Y'), ' - ', s.studynumber, ' - ',l.location_text) AS text FROM #__bsms_studies AS s LEFT JOIN #__bsms_locations AS l ON (s.location_id = l.id) WHERE s.published = 1 ORDER BY s.studydate DESC";
			}
		else 
			{
				$query = "SELECT id AS value, CONCAT(studytitle,' - ', date_format(studydate, '%a %b %e %Y'), ' - ', studynumber) AS text FROM #__bsms_studies WHERE published = 1 ORDER BY studydate DESC";
			}
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
		$lists['podcast'] 	= JHTML::_('select.genericlist',	$podcast, 'podcast_id', 'class="inputbox" size="5" multiple', 'value', 'text', $mediafilesedit->podcast_id);
		
		
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
		$this->assignRef('vmenabled', $vmenabled);
		$this->assignRef('dmenabled', $dmenabled);
		$this->assignRef('articlesSections', $articlesSections);
		
		parent::display($tpl);
	}
}
?>