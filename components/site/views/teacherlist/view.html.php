<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewteacherlist extends JView
{
	/**
	 * teacherlist view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		global $mainframe, $option;
		//$params = &JComponentHelper::getParams($option);
		$params =& $mainframe->getPageParameters();
		//$model	  = &$this->getModel();
		JRequest::setVar( 'templatemenuid', $params->get('templatemenuid'), 'get');
		$template = $this->get('Template');
		$params = new JParameter($template[0]->params);
		$db		=& JFactory::getDBO();
		$uri	=& JFactory::getURI();
		$admin=& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		//Import Scripts
		$document =& JFactory::getDocument();
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/biblestudy.js');
		$document->addScript(JURI::base().'components/com_biblestudy/tooltip.js');
		
		//Import Stylesheets
		$document->addStylesheet(JURI::base().'administrator/components/com_biblestudy/css/general.css');
		$document->addStylesheet(JURI::base().'components/com_biblestudy/tooltip.css');
		$document->addStylesheet(JURI::base().'components/com_biblestudy/assets/css/studieslist.css');
		$document->addStylesheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
		$url = $params->get('stylesheet');
		if ($url) {$document->addStyleSheet($url);}
		
		// Get data from the model
		$items		= & $this->get( 'Data');
		$menu =& JSite::getMenu();
		$item =& $menu->getActive();

		$pagination = $this->get('Pagination');
		$this->assignRef('admin_params', $admin_params);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('items',		$items);
		
		$this->assignRef('request_url',	$uri->toString());
		$this->assignRef('params', $params);

		parent::display($tpl);
	}
}
?>