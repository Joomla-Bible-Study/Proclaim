<?php
defined('_JEXEC') or die();

jimport ('joomla.application.component.view');
jimport ('joomla.application.component.helper');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
class biblestudyViewmediafilesedit extends JView {

	function display($tpl = null) {
		$admin =& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		//Make sure the user is allowed to access the edit form
        $permission = new JBSAdmin();
        $allow = $permission->getPermission();
        if (!$allow)
        {
            return JError::raiseError('403', JText::_('JBS_CMN_ACCESS_FORBIDDEN')); 
        }
		
		
		if (JPluginHelper::importPlugin('system', 'avreloaded')) {
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_avreloaded'.DS.'elements'.DS.'insertbutton.php');
			$mbutton = JElementInsertButton::fetchElementImplicit('mediacode',JText::_('JBS_MED_AVR_MEDIA'));
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
			//Here we check to see if docMan or VirtueMart are there by looking at their data tables so we don't error out
		$vmenabled = NULL;
		$dmenabled = NULL;
		$db = JFactory::getDBO();
        
       //First we check for the Joomla version and branch according to whether 1.5 or 1.6 because components are held in different tables
       
       $version = JVERSION;
      // echo $version.'<br>';
       $is15 = substr_count($version,'1.5');
       if ($is15)
       {
            $db->setQuery('SELECT name, enabled FROM #__components where enabled = 1');
       }
	   else
       {
            $db->setQuery('SELECT name, enabled FROM #__extensions where enabled = 1');
       }	
		$db->query();
		$components = $db->loadObjectList(); 
		
		foreach ($components as $component)
		{
		  
			if ($component->name == 'VirtueMart')
			{
				$vmenabled = 1;
			}
			if ($component->name == 'DOCman')
			{
				$dmenabled = 1;
			}
		} 
//		$vmenabled = JComponentHelper::getComponent('com_virtuemart',TRUE);
//		$dmenabled = JComponentHelper::getComponent('com_docman',TRUE);
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
		
		require_once( JPATH_COMPONENT_SITE.DS.'helpers'.DS.'toolbar.php' );
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
					array_unshift($docManCategories, JHTML::_('select.option', null, '- '.JTEXT::_('JBS_MED_SELECT_CATEGORY').' -', 'id', 'title'));
				}
		}
	if ($articlesSections)
    {	
		array_unshift($articlesSections, JHTML::_('select.option', null, '- '.JTEXT::_('JBS_MED_SELECT_SECTION').' -', 'id', 'title'));
	}	
		//Run only if Virtuemart enabled
		if ($vmenabled)
		{
			if ($virtueMartCategories)
			{
				array_unshift($virtueMartCategories, JHTML::_('select.option', null, '- '.JTEXT::_('JBS_MED_SELECT_CATEGORY').' -', 'id', 'title'));
			}
		}
		$isNew		= ($mediafilesedit->id < 1);
		
		//Retrieve any Docman items or articles that may exist
		$model = $this->getModel();
		
		//Add the params from the model
		$paramsdata = $mediafilesedit->params;
		$paramsdefs = JPATH_COMPONENT_SITE.DS.'models'.DS.'mediafilesedit.xml';
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
			$studies[] = JHTML::_('select.option', '0', '- '. JText::_( 'JBS_CMN_SELECT_STUDY' ) .' -' );
			$studies = array_merge($studies, $studiesList);
			$studies = JHTML::_('select.genericlist', $studies, 'study_id', 'class="inputbox" size="1" ', 'value', 'text', $mediafilesedit->study_id);
			$this->assignRef('studies', $studies);
		}

		$lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $mediafilesedit->published, 'JBS_CMN_YES', 'JBS_CMN_NO');
		$lists['link_type'] = JHTML::_('select.booleanlist','link_type', 'class="inputbox"', $mediafilesedit->link_type, 'JBS_CMN_YES', 'JBS_CMN_NO');

		$types5[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_SERVER' ) .' -' );
		$types5 			= array_merge( $types5, $serversList);
		$lists['server'] = JHTML::_('select.genericlist', $types5, 'server', 'class="inputbox" size="1" ', 'value', 'text',  $mediafilesedit->server );


		$types6[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_MED_SELECT_SERVER_FOLDER' ) .' -' );
		$types6 			= array_merge( $types6, $foldersList);
		$lists['path'] = JHTML::_('select.genericlist', $types6, 'path', 'class="inputbox" size="1" ', 'value', 'text',  $mediafilesedit->path );



		$podcast[] = JHTML::_('select.option', '0', '- '. JText::_('JBS_CMN_SELECT_PODCAST').' -');
		$podcast = array_merge($podcast, $podcastsList);
		$lists['podcast'] 	= JHTML::_('select.genericlist',	$podcast, 'podcast_id', 'class="inputbox" size="1" ', 'value', 'text', $mediafilesedit->podcast_id);

		;
		$types7[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_MED_SELECT_MEDIA_TYPE' ) .' -' );
		$types7 			= array_merge( $types7, $mediaImages);
		$lists['image'] = JHTML::_('select.genericlist', $types7, 'media_image', 'class="inputbox" size="1" ', 'value', 'text',  $mediafilesedit->media_image );

		$mimeselect[] = JHTML::_('select.option', '0', '- '. JText::_( 'JBS_CMN_SELECT_MIMETYPE' ) .' -' );
		$mime = array_merge($mimeselect, $mimeTypes);
		$lists['mime_type'] = JHTML::_('select.genericlist', $mime, 'mime_type', 'class="inputbox" size="1" ', 'value', 'text', $mediafilesedit->mime_type);

		// build the html select list for ordering
		$lists['ordering'] = JHTML::_('list.specificordering',  $mediafilesedit, $mediafilesedit->id, $ordering, 1 );

 //Get user groups and put into select list Since 1.6
        if (JOOMLA_VERSION == '6')
        {
            
            $query = "SELECT id AS value, title AS text FROM #__usergroups ORDER BY title ASC";
            $db->setQuery($query);
            $db->query();
            $groups = $db->loadObjectList();
           
            $studiesedit->show_level = explode(",",$studiesedit->show_level);
          
            JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
            $lists['show_level'] = JHTML::_('select.genericlist', $groups, 'show_level[]', 'class="inputbox" multiple="multiple" ', 'value', 'text',  $studiesedit->show_level);
           
        }
//TF added this to make studies work in mediafiles
		//$query = "SELECT id AS value, CONCAT(studytitle,' - ', date_format(studydate, '%a %b %e %Y'), ' - ', studynumber) AS text FROM #__bsms_studies WHERE published = 1 ORDER BY studydate DESC";
		//$database->setQuery($query);
		//$studies = $database->loadObjectList();
		//$studies[] = JHTML::_('select.option', '0', '- '. JText::_( 'JBS_CMN_SELECT_STUDY' ) .' -' );
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