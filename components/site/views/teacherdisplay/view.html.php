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
		global $mainframe, $option;
		
		$document =& JFactory::getDocument();
		$pathway	   =& $mainframe->getPathWay();
		$contentConfig = &JComponentHelper::getParams( 'com_biblestudy' );
		// Get the menu item object
		//$menus = &JMenu::getInstance();
		$menu =& JSite::getMenu();
		$item =& $menu->getActive();
		$params = &$mainframe->getPageParameters();
		JRequest::setVar( 'templatemenuid', $params->get('templatemenuid'), 'get');
		$template = $this->get('Template');
		$params = new JParameter($template[0]->params);
		//$params = &JComponentHelper::getParams($option);
		//end TF added
		$teacher		=& $this->get('Data');
		$this->assignRef('teacher',		$teacher);
		//We pick up the variable to show media in view - this is only used in the view.pdf.php. Here we simply pass the variable to the default template
		$show_media = $contentConfig->get('show_media_view');
		$this->assignRef('show_media', $show_media);
		$studies_param = $params->get('itemslimit');
		//$studies_param = $contentConfig->get('studies');
			if ($studies_param > 0) {
				$limit = ' LIMIT '.$studies_param;
				}
				else {
				$limit = '';
				}
		$database	= & JFactory::getDBO();
		$query = "SELECT id"
			. "\nFROM #__menu"
			. "\nWHERE link ='index.php?option=com_biblestudy&view=teacherlist'";
		$database->setQuery($query);
		$menuid = $database->loadResult();
		$this->assignRef('menuid',$menuid);
		$query = 'SELECT s.id as sid, s.studytitle, s.chapter_begin, s.studydate, s.teacher_id, s.booknumber,'
		. ' t.id AS tid, b.id AS bid, b.booknumber AS bnumber, b.bookname'
		. ' FROM #__bsms_studies AS s'
		. ' LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id)'
		. ' LEFT JOIN #__bsms_books AS b ON (b.booknumber = s.booknumber)'
		. ' WHERE s.teacher_id = '.$teacher->id.' ORDER BY s.studydate DESC'.$limit;
		$database->setQuery( $query );
		$studies = $database->loadObjectList();
		
		if($this->getLayout() == 'pagebreak') {
			$this->_displayPagebreak($tpl);
			return;
		}
		$print = JRequest::getBool('print');
		// build the html select list for ordering
		
		$this->assignRef('studies', $studies);
		$this->assignRef('print', $print);
		$this->assignRef('params' , $params);	
		
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