<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
//jimport ('joomla.application.plugin.helper');
$uri 		=& JFactory::getURI();
//$pathway	=& $mainframe->getPathway();

class biblestudyViewstudydetails extends JView
{
	
	function display($tpl = null)
	{
		//TF added
		global $mainframe, $option;
		//$dispatcher	   =& JDispatcher::getInstance();
		$document =& JFactory::getDocument();
        $document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
		$pathway	   =& $mainframe->getPathWay();
		$contentConfig = &JComponentHelper::getParams( 'com_biblestudy' );
		$dispatcher	=& JDispatcher::getInstance();
		// Get the menu item object
		//$menus = &JMenu::getInstance();
		$menu =& JSite::getMenu();
		$item =& $menu->getActive();
		$params 			=& $mainframe->getPageParameters();
		$templatemenuid = $params->get('templatemenuid',1);
		if (!$templatemenuid){$templatemenuid = 1;}
		JRequest::setVar( 'templatemenuid', $templatemenuid, 'get');
		$template = $this->get('Template');
		$params = new JParameter($template[0]->params);
		//dump ($params);
		$studydetails		=& $this->get('Data');
		//dump ($studydetails, "SD");
		$admin =& $this->get('Admin');
		
		$admin_params = new JParameter($admin[0]->params);
		
		$this->assignRef('admin_params', $admin_params);
		
		
		//Passage link to BibleGateway
		$plugin =& JPluginHelper::getPlugin('content', 'scripturelinks');
		if ($plugin)
			{
 				$plugin =& JPluginHelper::getPlugin('content', 'scripturelinks');
				$st_params 	= new JParameter( $plugin->params );
				$version = $st_params->get('bible_version');
				$windowopen = "window.open(this.href,this.target,'width=800,height=500,scrollbars=1');return false;";
			}
		
		//We pick up the variable to show media in view - this is only used in the view.pdf.php. Here we simply pass the variable to the default template
		$show_media = $contentConfig->get('show_media_view');
		$this->assignRef('show_media', $show_media);
		
		//Added database queries from the default template - moved here instead
		$database	= & JFactory::getDBO();
		$query = "SELECT id"
			. "\nFROM #__menu"
			. "\nWHERE link ='index.php?option=com_biblestudy&view=studieslist' and published = 1";
		$database->setQuery($query);
		$menuid = $database->loadResult();
		$this->assignRef('menuid',$menuid);
		
		
		if($this->getLayout() == 'pagebreak') {
			$this->_displayPagebreak($tpl);
			return;
		}
		$print = JRequest::getBool('print');
		// build the html select list for ordering
		
		/*
		 * Process the prepare content plugins
		 */
		$article->text = $studydetails->studytext;
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
			$limitstart = JRequest::getVar('limitstart','int');
			$results = $dispatcher->trigger('onPrepareContent', array (& $article, & $params, $limitstart));
			$article->studytext = $article->text;
			
			
		} //end if $linkit
		
		
		//Prepares a link string for use in social networking
		$u =& JURI::getInstance();
		$detailslink = htmlspecialchars($u->toString());
		$detailslink = JRoute::_($detailslink); 
		$this->assignRef('detailslink', $detailslink);
		//End social networking
	 	
                // End process prepare content plugins
		$this->assignRef('template', $template);
		$this->assignRef('print', $print);
		$this->assignRef('params' , $params);	
		$this->assignRef('studydetails', $studydetails);
		$this->assignRef('article', $article);
  		$this->assignRef('passage_link', $passage_link);
		//$this->assignRef('scripture', $scripture);
		
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