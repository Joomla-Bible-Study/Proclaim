<?php
/**
 * Mime Type List View for Bible Study Component
 * 
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

/**
 * mime Type View
 *
 */
class biblestudyViewmimetypelist extends JView
{
	/**
	 * mime Type view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		global $mainframe, $option; 
		$params = &JComponentHelper::getParams($option);
		JToolBarHelper::title(   JText::_( 'Mime Type Manager' ), 'generic.png' );
		//Checks to see if the admin allows rows to be deleted
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::preferences('com_biblestudy', '550');
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy.mimetype', true );
		$uri	=& JFactory::getURI();
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );
		// Get data from the model
		$items		= & $this->get( 'Data');

		$this->assignRef('items',		$items);
		
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('request_url',	$uri->toString());
		parent::display($tpl);
	}
}
?>