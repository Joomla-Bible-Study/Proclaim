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
		$text = JText::_( 'Edit CSS' );
		
		
		$this->assignRef('lists',		$lists);
        $this->addToolbar();
		parent::display($tpl);
	}
 protected function addToolbar() {
    
    JToolBarHelper::title(   JText::_( 'CSS Edit' ).': <small><small>[ ' . $text.' ]</small></small>', 'css.png' );
        JToolBarHelper::save('cssedit.save');
	//	JToolBarHelper::save();
        JToolBarHelper::cancel('cssedit.cancel');
		JToolBarHelper::custom('cssedit.backup','archive','Backup CSS', 'Backup CSS',false, false);
		JToolBarHelper::custom( 'cssedit.resetcss', 'save', 'Reset CSS', 'Reset CSS', false, false );
		JToolBarHelper::help('biblestudy', true );
    }
}
?>