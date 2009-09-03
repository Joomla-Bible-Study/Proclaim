<?php
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
jimport ('joomla.application.component.helper');

class biblestudyViewmediafilesedit extends JView {

	function display($tpl = null) {
		$admin =& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		//dump ($admin);
		$user =& JFactory::getUser();
		$entry_user = $user->get('gid');
		$entry_access = $admin_params->get('entry_access', 24) ;
		$allow_entry = $admin_params->get('allow_entry_study', 0);
		if ($allow_entry < 1) {return JError::raiseError('403', JText::_('Access Forbidden')); }
		if (!$entry_user) { $entry_user = 0; }
		if ($entry_user < $entry_access ){return JError::raiseError('403', JText::_('Access Forbidden'));}
		
		if (JPluginHelper::importPlugin('system', 'avreloaded')) {
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_avreloaded'.DS.'elements'.DS.'insertbutton.php');
			$mbutton = JElementInsertButton::fetchElementImplicit('mediacode',JText::_('AVR Media'));
			$this->assignRef('mbutton', $mbutton);
		}
		
		$document =& JFactory::getDocument();
		$document->addStylesheet(JURI::base().'components/com_biblestudy/assets/css/icon.css');
		$document->addStylesheet(JURI::base().'administrator/templates/system/css/system.css');
		$document->addStylesheet(JURI::base().'media/system/css/modal.css');
		$document->addStylesheet(JURI::base().'administrator/templates/khepri/css/rounded.css');
		$document->addStylesheet(JURI::base().'administrator/templates/khepri/css/template.css');
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/noconflict.js');
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/plugins/jquery.selectboxes.js');
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/views/mediafilesedit.js');
		
		$vmenabled = JComponentHelper::getComponent('com_virtuemart',TRUE);
		$dmenabled = JComponentHelper::getComponent('com_docman',TRUE);
		$this->assignRef('vmenabled', $vmenabled);
		$this->assignRef('dmenabled', $dmenabled);
		
		//Get Data
		$mediafilesedit =& $this->get('Data');
		$studiesList =& $this->get('Studies');
		$serversList =& $this->get('Servers');
		$foldersList =& $this->get('Folders');
		$podcastsList =& $this->get('Podcasts');
		$mediaImages =& $this->get('MediaImages');
		$mimeTypes =& $this->get('MimeTypes');
		$ordering =& $this->get('Ordering');
		$docManCategories =& $this->get('docManCategories');
		$articlesSections =& $this->get('ArticlesSections');
		$virtueMartCategories =& $this->get('virtueMartCategories');
		
		require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'toolbar.php' );
		$toolbar = biblestudyHelperToolbar::getToolbar();
		$this->assignRef('toolbar', $toolbar);
		$isNew		= ($mediafilesedit->id < 1);
		$alt = "Upload";
		$bar=& JToolBar::getInstance( 'toolbar' );
		$bar->appendButton( 'Popup', 'upload', $alt, "index.php?option=com_media&tmpl=component&task=popupUpload&directory=", 800, 700 );
		$docManCategories =& $this->get('docManCategories');
		$articlesSections =& $this->get('ArticlesSections');
		$virtueMartCategories =& $this->get('virtueMartCategories');

		//Manipulate Data
		//Run only if Docman is enabled
		if ($dmenabled)
		{
			if ($docManCategories)
				{
					array_unshift($docManCategories, JHTML::_('select.option', null, '- Select a Category -', 'id', 'title'));
				}
		}
		
		array_unshift($articlesSections, JHTML::_('select.option', null, '- Select a Section -', 'id', 'title'));
		
		//Run only if Virtuemart enabled
		if ($vmenabled)
		{
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

		$lists = array();

		//Special tasks to perform depending on whether its a new study or not
		//dump ($studiesList);
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

//TF added this to make studies work in mediafiles
		//$query = "SELECT id AS value, CONCAT(studytitle,' - ', date_format(studydate, '%a %b %e %Y'), ' - ', studynumber) AS text FROM #__bsms_studies WHERE published = 1 ORDER BY studydate DESC";
		//$database->setQuery($query);
		//$studies = $database->loadObjectList();
		//$studies[] = JHTML::_('select.option', '0', '- '. JText::_( 'Select a Study' ) .' -' );
		//$studies = array_merge($studies,$database->loadObjectList() );
		//$lists['studies'] = JHTML::_('select.genericlist', $studies, 'study_id', 'class="inputbox" size="1" ', 'value', 'text', $mediafilesedit->study_id);
		
		$this->assignRef('lists', $lists);
		$this->assignRef('mediafilesedit', 	$mediafilesedit);
		$this->assignRef('articlesSections', $articlesSections);

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