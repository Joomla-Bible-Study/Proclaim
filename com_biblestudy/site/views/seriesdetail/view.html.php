<?php
/**
 * @version     $Id: view.html.php 1330 2011-01-06 08:01:38Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

require_once (JPATH_SITE  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
jimport( 'joomla.application.component.view' );
$uri 		=& JFactory::getURI();
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
class biblestudyViewseriesdetail extends JView
{
	
	function display($tpl = null)
	{
		//TF added
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		$document =& JFactory::getDocument();
        $document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
        $document->addScript(JURI::base().'components/com_biblestudy/assets/css/biblestudy.js');
		$pathway	   =& $mainframe->getPathWay();
		$contentConfig = &JComponentHelper::getParams( 'com_biblestudy' );
		$dispatcher	=& JDispatcher::getInstance();
		// Get the menu item object
		//Load the Admin settings and params from the template
        $this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers');
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin(true);
       // $this->admin_params = $this->admin;
        
         $t = JRequest::getInt('t','get',1);
        if (!$t) {
            $t = 1;
        }
        JRequest::setVar('t', $t, 'get');
        $template = $this->get('template');
        $params = new JParameter($template[0]->params);
        $a_params = $this->get('Admin');
        $this->admin_params = new JParameter($a_params[0]->params);
		$items		=& $this->get('Data');		
		
		//Get studies from this series
        $seriesorder = $params->get('series_detail_order','DESC');
       	
		$limit = ' LIMIT '.$params->get('series_detail_limit',10);
		//$limit = ' LIMIT 10';
		$db = JFactory::getDBO();
		$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername,
		 #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_series.description AS sdescription,
		 #__bsms_message_type.id AS mid,
		 #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,
		 #__bsms_locations.id AS lid, #__bsms_locations.location_text,
		 group_concat(#__bsms_topics.id separator ", ") AS tp_id, group_concat(#__bsms_topics.topic_text separator ", ") as topic_text
		 FROM #__bsms_studies
		 left join #__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)
		 LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)
		 LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)
		 LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)
		 LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)
		 LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)
		 LEFT JOIN #__bsms_locations ON (#__bsms_studies.location_id = #__bsms_locations.id)
		 WHERE #__bsms_studies.series_id = '.$items->id.' GROUP BY #__bsms_studies.id ORDER BY #__bsms_studies.studydate '.$seriesorder
		.$limit;
       
		$db->setQuery( $query );
		$results = $db->loadObjectList();
        $items2 = $results;
         //check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user = JFactory::getUser();
        $groups	= $user->getAuthorisedViewLevels(); 
        $count = count($items);
        
        for ($i = 0; $i < $count; $i++)
        {
            
            if ($items2[$i]->access > 1)
            {
               if (!in_array($items2[$i]->access,$groups))
               {
                    unset($items2[$i]); 
               } 
	        }
        }
        $studies = $items2;
        
   //     $adminrows = new JBSAdmin();
   //     $studies = $adminrows->showRows($results);
        JRequest::setVar('returnid',$items->id,'get',true);
		//Passage link to BibleGateway
		$plugin =& JPluginHelper::getPlugin('content', 'scripturelinks');
 		if ($plugin){$st_params 	= new JParameter( $plugin->params );
		$version = $st_params->get('bible_version');}
		$windowopen = "window.open(this.href,this.target,'width=800,height=500,scrollbars=1');return false;";
		
		
			if($this->getLayout() == 'pagebreak') {
			$this->_displayPagebreak($tpl);
			return;
		}
		$print = JRequest::getBool('print');
		// build the html select list for ordering
		
		/*
		 * Process the prepare content plugins
		 */
		$limitstart = 0;
		$article->text = $items->description;
		$linkit = $params->get('show_scripture_link');
		if ($linkit) {
			switch ($linkit) 
			{
			case 0:
				break;
			case 1:
				JPluginHelper::importPlugin('content');
				break;
			case 2:
				JPluginHelper::importPlugin('content', 'scripturelinks');
				break;
			}
			$results = $dispatcher->trigger('onPrepareContent', array (& $article, & $params, $limitstart));
			$items->description = $article->text;
			
		} //end if $linkit
          // End process prepare content plugins
		$this->assignRef('template', $template);
		$this->assignRef('print', $print);
		$this->assignRef('params' , $params);	
	//	$this->assignRef('admin_params', $admin_params);
		$this->assignRef('items', $items);
		$this->assignRef('article', $article);
  		$this->assignRef('passage_link', $passage_link);
  		$this->assignRef('studies', $studies);
		//$this->assignRef('scripture', $scripture);
		parent::display($tpl);
	}

}