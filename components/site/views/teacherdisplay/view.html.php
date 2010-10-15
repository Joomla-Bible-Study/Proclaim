<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
$uri 		=& JFactory::getURI();
//$pathway	=& $mainframe->getPathway();

class biblestudyViewteacherdisplay extends JView
{
	
	function display($tpl = null)
	{
		//TF added
		$mainframe =& JFactory::getApplication();, $option;
		// get the user information
        $userinfo =& JFactory::getUser();
		$user = $userinfo->get('gid');
        
		$document =& JFactory::getDocument();
		$document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
		$document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
		$pathway	   =& $mainframe->getPathWay();
		$contentConfig = &JComponentHelper::getParams( 'com_biblestudy' );
		// Get the menu item object
		//$menus = &JMenu::getInstance();
		$menu =& JSite::getMenu();
		$item =& $menu->getActive();
		$params = &$mainframe->getPageParameters();
		$url = $params->get('stylesheet');
		if ($url) {$document->addStyleSheet($url);}
		$templatemenuid = $params->get('templatemenuid');
		if (!$templatemenuid){$templatemenuid = 1;}
		JRequest::setVar( 'templatemenuid', $templatemenuid, 'get');
		//JRequest::setVar( 'templatemenuid', $params->get('templatemenuid'), 'get');
		$template = $this->get('Template');
		$params = new JParameter($template[0]->params);
		$url = $params->get('stylesheet');
		if ($url) {$document->addStyleSheet($url);}
		$admin=& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		//$params = &JComponentHelper::getParams($option);
		//end TF added
		$teacher		=& $this->get('Data');
        $id = JRequest::getInt('id', 'get');
        if ($id) {$teacher->id=$id;}
		$this->assignRef('teacher',		$teacher);
		//We pick up the variable to show media in view - this is only used in the view.pdf.php. Here we simply pass the variable to the default template
		$show_media = $contentConfig->get('show_media_view');
		$this->assignRef('show_media', $show_media);
		$studies_param = $params->get('studies');
		//$studies_param = $contentConfig->get('studies');
			if ($studies_param > 0) {
				$limit = ' LIMIT '.$studies_param;
				}
				else {
				$limit = '';
				}
		$database	= & JFactory::getDBO();
		
/*		$query = 'SELECT s.id as sid, s.studytitle, s.chapter_begin, s.studydate, s.teacher_id, s.booknumber,'
		. ' t.id AS tid, b.id AS bid, b.booknumber AS bnumber, b.bookname'
		. ' FROM #__bsms_studies AS s'
		. ' LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id)'
		. ' LEFT JOIN #__bsms_books AS b ON (b.booknumber = s.booknumber)'
		. ' WHERE s.teacher_id = '.$teacher->id.' ORDER BY s.studydate DESC'.$limit;
*/		
		$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername,
 #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_series.description AS sdescription,
 #__bsms_message_type.id AS mid,
 #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,
 #__bsms_locations.id AS lid, #__bsms_locations.location_text,
 group_concat(#__bsms_topics.id separator ", ") AS tp_id, group_concat(#__bsms_topics.topic_text separator ", ") as topic_text, sum(#__bsms_mediafiles.plays) AS totalplays, sum(#__bsms_mediafiles.downloads) AS totaldownloads, #__bsms_mediafiles.study_id 
 FROM #__bsms_studies
 left join #__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)
 LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)
 LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)
 LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)
 LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)
 LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)
 LEFT JOIN #__bsms_locations ON (#__bsms_studies.location_id = #__bsms_locations.id) 
 LEFT JOIN #__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id)
 WHERE #__bsms_studies.teacher_id = '.$teacher->id.' AND #__bsms_studies.published = 1 AND #__bsms_studies.show_level <= '.$user.' GROUP BY #__bsms_studies.id ORDER BY #__bsms_studies.studydate DESC
'.$limit;
		$database->setQuery( $query );
		$studies = $database->loadObjectList();
		//dump ($studies, 'studies: ');
		if($this->getLayout() == 'pagebreak') {
			$this->_displayPagebreak($tpl);
			return;
		}
		$print = JRequest::getBool('print');
		// build the html select list for ordering
		
		$this->assignRef('studies', $studies);
		$this->assignRef('print', $print);
		$this->assignRef('params' , $params);	
		$this->assignRef('admin_params', $admin_params);
		$this->assignRef('template', $template);
	//	$this->assignRef('user', $user);
		parent::display($tpl);
	}
	function _displayPagebreak($tpl)
	{
		$document =& JFactory::getDocument();
		$document->setTitle(JText::_('PGB ARTICLE PAGEBRK'));
		parent::display($tpl);
	}
}
?>