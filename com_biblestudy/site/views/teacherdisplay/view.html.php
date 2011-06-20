<?php
/**
 * @version     $Id: view.html.php 1330 2011-01-06 08:01:38Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
jimport( 'joomla.application.component.view' );
$uri 		=& JFactory::getURI();

class biblestudyViewteacherdisplay extends JView
{
	
	function display($tpl = null)
	{
		
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		
        
		$document =& JFactory::getDocument();
		$document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
		$document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
        $document->addScript(JURI::base().'components/com_biblestudy/assets/css/biblestudy.js');
		$pathway	   =& $mainframe->getPathWay();
				
        
         //Load the Admin settings and params from the template
        $this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers');
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin(true);
        $this->admin_params = $this->admin;
        
         $t = JRequest::getInt('t','get',1);
        if (!$t) {
            $t = 1;
        }
     //   JRequest::setVar('t', $t, 'get');
        $template = $this->get('template');
      //  $params = new JParameter($template[0]->params);
         // Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadJSON($template[0]->params);
                $params = $registry;
       
		
		$url = $params->get('stylesheet');
		if ($url) {$document->addStyleSheet($url);}
		
		$teacher		=& $this->get('Data');
        $id = JRequest::getInt('id', 'get');
        if ($id) {$teacher->id=$id;}
		$this->assignRef('teacher',		$teacher);
		
		$studies_param = $params->get('studies');
			if ($studies_param > 0) {
				$limit = ' LIMIT '.$studies_param;
				}
				else {
				$limit = '';
				}
		$database	= & JFactory::getDBO();
		

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
 WHERE #__bsms_studies.teacher_id = '.$teacher->id.' AND #__bsms_studies.published = 1 GROUP BY #__bsms_studies.id ORDER BY #__bsms_studies.studydate DESC
'.$limit;
		$database->setQuery( $query );
		$results = $database->loadObjectList();
        
   //check permissions for this view by running through the records and removing those the user doesn't have permission to see
   $items = $results;
  
        $user = JFactory::getUser();
        $groups	= $user->getAuthorisedViewLevels(); 
        $count = count($items);
        
        for ($i = 0; $i < $count; $i++)
        {
            
            if ($items[$i]->access > 1)
            {
               if (!in_array($items[$i]->access,$groups))
               {
                    unset($items[$i]); 
               } 
	        }
        }
        $this->items = $items;
        
    
		if($this->getLayout() == 'pagebreak') {
			$this->_displayPagebreak($tpl);
			return;
		}
		$print = JRequest::getBool('print');
		// build the html select list for ordering
		
	//	$this->assignRef('studies', $studies);
		$this->assignRef('print', $print);
		$this->assignRef('params' , $params);
		$this->assignRef('template', $template);
		parent::display($tpl);
	}
	function _displayPagebreak($tpl)
	{
		$document =& JFactory::getDocument();
		$document->setTitle(JText::_('JBS_CMN_READ_MORE'));
		parent::display($tpl);
	}
}