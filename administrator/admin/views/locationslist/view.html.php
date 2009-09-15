<?php
/**
 * books list View for Bible Study Component
 * 
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewlocationslist extends JView
{
	
	function display($tpl = null)
	{
		global $mainframe, $option;
		
		//$document =& JFactory::getDocument();
		//$document->addStyleSheet('http://www.calvarychapelnewberg.net/j15/administrator/components/com_biblestudy/css/general.css');
 		$params = &JComponentHelper::getParams($option);
		JToolBarHelper::title(   JText::_( 'Locations Manager' ), 'generic.png' );
		//Checks to see if the admin allows rows to be deleted
		
		JToolBarHelper::deleteList();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		//JToolBarHelper::preferences('com_biblestudy', '550');
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy.locations', true );
		$db=& JFactory::getDBO();
		$uri	=& JFactory::getURI();
		
	// Get data from the model
	$items		= & $this->get( 'Data');
	$total		= & $this->get( 'Total');
	$pagination = & $this->get( 'Pagination' );
	
	$javascript 	= 'onchange="document.adminForm.submit();"';	

	$this->assignRef('items',		$items);
	$this->assignRef('pagination',	$pagination);
	$this->assignRef('request_url',	$uri->toString());

		parent::display($tpl);
	}
}
?>