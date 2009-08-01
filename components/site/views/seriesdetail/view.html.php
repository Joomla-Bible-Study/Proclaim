<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
$uri 		=& JFactory::getURI();
//$pathway	=& $mainframe->getPathway();

class biblestudyViewseriesdetail extends JView
{
	
	function display($tpl = null)
	{
		//TF added
		global $mainframe, $option;
		//$dispatcher	   =& JDispatcher::getInstance();
		$document =& JFactory::getDocument();
		$pathway	   =& $mainframe->getPathWay();
		$contentConfig = &JComponentHelper::getParams( 'com_biblestudy' );
		$dispatcher	=& JDispatcher::getInstance();
		// Get the menu item object
		//$menus = &JMenu::getInstance();
		$menu =& JSite::getMenu();
		$item =& $menu->getActive();
		$params 			=& $mainframe->getPageParameters();
		$templatemenuid = $params->get('templatemenuid');
		if (!$templatemenuid){$templatemenuid = 1;}
		JRequest::setVar( 'templatemenuid', $templatemenuid, 'get');
		$template = $this->get('Template');
		$params = new JParameter($template[0]->params);
		//dump ($params, 'params2: ');
		$items		=& $this->get('Data');
		$admin =& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		
		//Passage link to BibleGateway
		$plugin =& JPluginHelper::getPlugin('content', 'scripturelinks');
 		$st_params 	= new JParameter( $plugin->params );
		//$scripture = $book.$b1.$ch_b.$b2.$v_b.$b3.$ch_e.$b2a.$v_e;
		$version = $st_params->get('bible_version');
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
		$this->assignRef('admin_params', $admin_params);
		$this->assignRef('items', $items);
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