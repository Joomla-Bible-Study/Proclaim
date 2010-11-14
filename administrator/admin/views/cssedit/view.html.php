<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewcssedit extends JView
{
	
	function display($tpl = null)
	{
		
		
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
	$lists		=& $this->get('Data');
		$text = JText::_( 'JBS_CMN_EDIT_CSS' );
		JToolBarHelper::title(   JText::_( 'CSS Edit' ).': <small><small>[ ' . $text.' ]</small></small>', 'css.png' );
		JToolBarHelper::save();
        JToolBarHelper::cancel();
		JToolBarHelper::custom('backup','archive','Backup CSS', 'Backup CSS',false, false);
		JToolBarHelper::custom( 'resetcss', 'save', 'Reset CSS', 'Reset CSS', false, false );
		JToolBarHelper::help('biblestudy', true );
		
		$this->assignRef('lists',		$lists);

		parent::display($tpl);
	}
}
?>