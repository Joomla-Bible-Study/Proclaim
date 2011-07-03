<?php

/**
 * @version     $Id
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'biblestudy.php');
jimport( 'joomla.application.component.view' );


class biblestudyViewcssedit extends JView
{
	
	function display($tpl = null)
	{
		 if (!JFactory::getUser()->authorize('core.manage','com_biblestudy'))
        {
            JError::raiseError(404,JText::_('JBS_CMN_NOT_AUTHORIZED'));
            return false;
        } 
		
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
        $lists		=& $this->get('Data');
		$text = JText::_( 'JBS_CSS_CSS_EDIT' );
		
		
		$this->assignRef('lists',		$lists);
        $this->addToolbar();
		parent::display($tpl);
	}
 protected function addToolbar() {
    
        JToolBarHelper::title(JText::_( 'JBS_CSS_CSS_EDIT' ), 'css.png' );
        JToolBarHelper::apply('cssedit.save');
        JToolBarHelper::cancel('cssedit.cancel', 'JTOOLBAR_CANCEL');
		JToolBarHelper::divider();
		JToolBarHelper::custom('cssedit.backup','archive','Backup CSS', 'JBS_CSS_BACKUP_CSS',false, false);
		JToolBarHelper::custom( 'cssedit.resetcss', 'save', 'Reset CSS', 'JBS_CSS_RESET_CSS', false, false );
		JToolBarHelper::divider();
		JToolBarHelper::help('biblestudy', true );
    }
}