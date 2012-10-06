<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewmedialist extends JView
{
	/**
	 * Medialist view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option'); 
		$params = &JComponentHelper::getParams($option);
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		JToolBarHelper::title(   JText::_( 'Media Manager' ), 'mediaimages.png' );
		//This checks the Preferences file to see if the admin is allowing rows to be deleted 
		
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		//JToolBarHelper::preferences('com_biblestudy', '550');
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy', true );

		// Get data from the model
		$items		= & $this->get( 'Data');
		$this->assignRef('items',		$items);
		//Get the admin params
		$admin=& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		$this->assignRef('admin_params', $admin_params);
		$this->assignRef('admin', $admin);
		

		parent::display($tpl);
	}
}
?>