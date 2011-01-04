<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
jimport ('joomla.application.component.helper');

class biblestudyViewmediafilesedit extends JView {
	protected $form;
	
	function display($tpl = null) {

		$this->form = $this->get("Form");
		$db = JFactory::getDBO();
        $lists = array();
        //Get Data
		$mediafilesedit	=& $this->get('Data');
        
		if (JPluginHelper::importPlugin('system', 'avreloaded')) {
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_avreloaded'.DS.'elements'.DS.'insertbutton.php');
			$mbutton = JElementInsertButton::fetchElementImplicit('mediacode',JText::_('JBS_MED_AVR_MEDIA'));
			$this->assignRef('mbutton', $mbutton);
		}

		//Import the article ids
     //   require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'articles.php');
    //    $articlesform = new JFormFieldArticles();
     //   $articleids = $articlesform->getInput();
     //   dump ($articleids, 'articles: ');
     
 //Pick up the podcasts and display as a multiselect box
     $query = "SELECT id AS value, title AS text from #__bsms_podcast WHERE published = 1 ORDER BY title ASC";
     $db->setQuery($query);
     $db->query();
     $podcasts = $db->loadObjectList();
     $mediafilesedit->podcast_id = explode(",",$mediafilesedit->podcast_id);
   //  if (is_array($results)){$podcast_values = explode(",",$results->id);}
     $lists['podcasts'] = JHTML::_('select.genericlist',$podcasts,'podcast_id[]','multiple class="inputbox"','value','text',$mediafilesedit->podcast_id);
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		
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
		
        
       //First we check for the Joomla version and branch according to whether 1.5 or 1.6 because components are held in different tables
           
       if (JOOMLA_VERSION == '5')
       {
            $db->setQuery('SELECT name, enabled FROM #__components where enabled = 1');
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
       }
	   else
       {
            $db->setQuery('SELECT element, enabled FROM #__extensions where enabled = 1');
      		$db->query();
            $components = $db->loadObjectList(); 
		
		foreach ($components as $component)
		{
		  
			if ($component->element == 'com_virtuemart')
			{
				$vmenabled = 1;
			}
			if ($component->element == 'com_docman')
			{
				$dmenabled = 1;
			}
		}
       }
		
	
    if (JOOMLA_VERSION == '5')
    {
   	    $articlesSections =& $this->get('ArticlesSections');        
    }
    else
    {
        $articlesCategories =& $this->get('ArticleCategories');
    }



		//Manipulate Data
		//Run only if Docman is enabled
		if ($dmenabled > 0)
		{
			$docManCategories =& $this->get('docManCategories');
			if ($docManCategories)
				{
					array_unshift($docManCategories, JHTML::_('select.option', null, '- '.JTEXT::_('JBS_MED_SELECT_CATEGORY').' -', 'id', 'title'));
				}
		}
		
		//articles
	//	array_unshift($articlesSections, JHTML::_('select.option', null, '- '.JTEXT::_('JBS_MED_SELECT_SECTION').' -', 'id', 'title'));
		
		//Run only if Virtuemart enabled
		if ($vmenabled > 0)
		{
			$virtueMartCategories =& $this->get('virtueMartCategories');
			if ($virtueMartCategories)
			{
				array_unshift($virtueMartCategories, JHTML::_('select.option', null, '- '.JTEXT::_('JBS_MED_SELECT_CATEGORY').' -', 'id', 'title'));
			}
		}
		
		//Retrieve any Docman items or articles that may exist
		$model = $this->getModel();
		
		//Add the params from the model
		$paramsdata = $mediafilesedit->params;
		$paramsdefs = JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'mediafilesedit.xml';
		$params = new JParameter($paramsdata, $paramsdefs);
		$this->assignRef('params', $params);
	//This is for getting the path of the file for determining the file size	
    $query = 'SELECT #__bsms_mediafiles.*, #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,
    #__bsms_folders.id AS fid, 
    #__bsms_folders.folderpath AS fpath, #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath, 
    #__bsms_media.media_image_name AS imname, #__bsms_media.path2 AS path2, s.studyintro, s.media_hours, s.media_minutes, 
    s.media_seconds, s.studytitle, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.chapter_end, s.verse_begin, 
    s.verse_end, t.teachername, t.id as tid, s.id as sid, s.studyintro,  #__bsms_media.media_alttext AS malttext, 
    #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext FROM #__bsms_mediafiles 
    LEFT JOIN #__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image) 
    LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server) 
    LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path) 
    LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type) 
    LEFT JOIN #__bsms_studies AS s ON (s.id = #__bsms_mediafiles.study_id) 
    LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id) 
    WHERE #__bsms_mediafiles.id = '.$mediafilesedit->id;
$db->setQuery( $query );
$db->query();
$thefile = $db->loadObject();
$protocol = $params->get('protocol','http://');
if ($thefile)
    {
        if (substr($thefile->spath,0,4)=='http') 
        {$filepath = $thefile->spath.$thefile->fpath.$thefile->filename;}
        else {$filepath = $protocol.$thefile->spath.$thefile->fpath.$thefile->filename;}
        	
    }
else
{
    $filepath = '';
}			
$this->assignRef('filepath', $filepath);
if($mediafilesedit->docMan_id != 0 && !$isNew) {
				$this->assignRef('docManItem', $model->getDocManItem($mediafilesedit->docMan_id));
				$this->assign('docManStyle', 'display: none');
			}
			$this->assignRef('docManCategories', $docManCategories);
		
//		if($mediafilesedit->article_id != 0 && !$isNew){
//			$this->assignRef('articleItem', $model->getArticleItem($mediafilesedit->article_id));
//			$this->assign('articleStyle', 'display: none');
//		}

			if($mediafilesedit->virtueMart_id != 0 && !$isNew){
				$this->assignRef('virtueMartItem', $model->getVirtueMartItem($mediafilesedit->virtueMart_id));
				$this->assign('virtueMartStyle', 'display: none');
			}
			$this->assignRef('virtueMartCategories', $virtueMartCategories);

		
		$this->addToolbar();
		// build the html select list for ordering

		$database	= & JFactory::getDBO();
			
		$lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $mediafilesedit->published);
		
		$lists['link_type'] = JHTML::_('select.booleanlist','link_type', 'class="inputbox"', $mediafilesedit->link_type);

		$lists['internal_viewer'] = JHTML::_('select.booleanlist', 'internal_viewer', 'class="inputbox"', $mediafilesedit->internal_viewer);
		
		if ($admin_params->get('show_location_media') > 0)
			{
				$query = "SELECT s.id AS value, CONCAT(s.studytitle,' - ', date_format(s.studydate, '%Y-%m-%d'), ' - ', s.studynumber, ' - ',l.location_text) AS text FROM #__bsms_studies AS s LEFT JOIN #__bsms_locations AS l ON (s.location_id = l.id) WHERE s.published = 1 ORDER BY s.studydate DESC";
			}
		else 
			{
				$query = "SELECT id AS value, CONCAT(studytitle,' - ', date_format(studydate, '%Y-%m-%d'), ' - ', studynumber) AS text FROM #__bsms_studies WHERE published = 1 ORDER BY studydate DESC";
			}
		$database->setQuery($query);
		$studies[] = JHTML::_('select.option', '0', '- '. JText::_( 'JBS_CMN_SELECT_STUDY' ) .' -' );
		$studies = array_merge($studies,$database->loadObjectList() );
		$lists['studies'] = JHTML::_('select.genericlist', $studies, 'study_id', 'class="inputbox" size="1" ', 'value', 'text', $mediafilesedit->study_id);

		$query5 = 'SELECT id AS value, server_path AS text, published'
		. ' FROM #__bsms_servers'
		. ' WHERE published = 1'
		. ' ORDER BY server_path';
		$database->setQuery( $query5 );
		$types5[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_SERVER' ) .' -' );
		$types5 			= array_merge( $types5, $database->loadObjectList() );
		$lists['server'] = JHTML::_('select.genericlist', $types5, 'server', 'class="inputbox" size="1" ', 'value', 'text',  $mediafilesedit->server );

		$query6 = 'SELECT id AS value, folderpath AS text, published'
		. ' FROM #__bsms_folders'
		. ' WHERE published = 1'
		. ' ORDER BY folderpath';
		$database->setQuery( $query6 );
		$types6[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_MED_SELECT_SERVER_FOLDER' ) .' -' );
		$types6 			= array_merge( $types6, $database->loadObjectList() );
		$lists['path'] = JHTML::_('select.genericlist', $types6, 'path', 'class="inputbox" size="1" ', 'value', 'text',  $mediafilesedit->path );

		$query = 'SELECT id AS value, title AS text FROM #__bsms_podcast WHERE published = 1 ORDER BY title ASC';
		$database->setQuery($query);
		$podcast[] = JHTML::_('select.option', '0', '- '. JText::_('JBS_CMN_SELECT_PODCAST').' -');
		$podcast = array_merge($podcast, $database->loadObjectList());
		$lists['podcast'] 	= JHTML::_('select.genericlist',	$podcast, 'podcast_id', 'class="inputbox" size="5" multiple', 'value', 'text', $mediafilesedit->podcast_id);
		
		
		$query7 = 'SELECT id AS value, media_image_name AS text, published'
		. ' FROM #__bsms_media'
		. ' WHERE published = 1'
		. ' ORDER BY media_image_name';
		$database->setQuery( $query7 );
		$types7[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_MED_SELECT_MEDIA_TYPE' ) .' -' );
		$types7 			= array_merge( $types7, $database->loadObjectList() );
		$lists['image'] = JHTML::_('select.genericlist', $types7, 'media_image', 'class="inputbox" size="1" ', 'value', 'text',  $mediafilesedit->media_image );


		$query = 'SELECT id AS value, mimetext AS text, published FROM #__bsms_mimetype WHERE published = 1 ORDER BY id ASC';
		$database->setQuery($query);
		$mimeselect[] = JHTML::_('select.option', '0', '- '. JText::_( 'JBS_CMN_SELECT_MIMETYPE' ) .' -' );
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
	//	$this->assignRef('articlesSections', $articlesSections);
		
		parent::display($tpl);
	}
	
	protected function addToolbar() {
		$isNew		= ($mediafilesedit->id < 1);
		$title = $isNew ? JText::_( 'JBS_CMN_NEW' ) : JText::_( 'JBS_CMN_EDIT' );
		JToolBarHelper::title(JText::_( 'JBS_MED_EDIT_MEDIA' ).': <small><small>['. $title.']</small></small>', 'mp3.png' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();

		} else {
			JToolBarHelper::apply();
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		JToolBarHelper::custom( 'resetDownloads', 'download.png', 'Reset Download Hits', 'JBS_MED_RESET_DOWNLOAD_HITS', false, false );
		JToolBarHelper::custom( 'resetPlays', 'play.png', 'Reset Plays', 'JBS_MED_RESET_PLAYS', false, false );
		}
		
		// Add an upload button and view a popup screen width 550 and height 400
		$alt = "Upload";
		$bar=& JToolBar::getInstance( 'toolbar' );
		$bar->appendButton( 'Popup', 'upload', $alt, "index.php?option=com_media&tmpl=component&task=popupUpload&directory=", 650, 400 );
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy', true );		
	}
}
?>