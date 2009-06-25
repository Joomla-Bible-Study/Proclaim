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
		
		// Get data from the model
		$items		= & $this->get( 'Data');
		$menu =& JSite::getMenu();
		$item =& $menu->getActive();


		
		$this->assignRef('items',		$items);
		
		$this->assignRef('request_url',	$uri->toString());
		$this->assignRef('params', $params);

		parent::display($tpl);
	}
}
?>