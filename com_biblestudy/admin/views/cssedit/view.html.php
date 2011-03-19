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
		$text = JText::_( 'JBS_CSS_CSS_EDIT' );
		
		
		$this->assignRef('lists',		$lists);
        $this->addToolbar();
		parent::display($tpl);
	}
 protected function addToolbar() {
    
    JToolBarHelper::title(   JText::_( 'JBS_CSS_CSS_EDIT' ).': <small><small>[ ' . $text.' ]</small></small>', 'css.png' );
        JToolBarHelper::save('cssedit.save');
		JToolBarHelper::divider();
	//	JToolBarHelper::save();
		if ($isNew)
			JToolBarHelper::cancel('cssedit.cancel', 'JTOOLBAR_CANCEL');
		else {
			JToolBarHelper::cancel('cssedit.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolBarHelper::divider();
		JToolBarHelper::custom('cssedit.backup','archive','Backup CSS', 'JBS_CSS_BACKUP_CSS',false, false);
		JToolBarHelper::custom( 'cssedit.resetcss', 'save', 'Reset CSS', 'JBS_CSS_RESET_CSS', false, false );
		JToolBarHelper::divider();
		JToolBarHelper::help('biblestudy', true );
    }
}
?>