<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewfolderslist extends JView
{
	/**
	 * Folderslist view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		global $mainframe, $option; 
		$params = &JComponentHelper::getParams($option);
		JToolBarHelper::title(   JText::_( 'Server Folder Manager' ), 'generic.png' );
		//Checks to see if the admin allows rows to be deleted
		$allow_deletes =& $this->get('Deletes');
		if ($allow_deletes->allow_deletes > 0 ):
		JToolBarHelper::deleteList();
		endif;
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::preferences('com_biblestudy', '550');
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy.folders', true );
		// Get data from the model
		$items		= & $this->get( 'Data');

		$this->assignRef('items',		$items);

		parent::display($tpl);
	}
}
?>