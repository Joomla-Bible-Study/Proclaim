<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewshareedit extends JView
{
	
	function display($tpl = null)
	{
		
		$shareedit		=& $this->get('Data');
		$admin=& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		$lists = array();
		$paramsdata = $shareedit->params;
		$paramsdefs = JPATH_COMPONENT.DS.'models'.DS.'shareedit.xml';
		$params = new JParameter($paramsdata, $paramsdefs);
		$this->assignRef('params', $params);
		
		$isNew		= ($shareedit->id < 1);
		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Social Network Edit' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		jimport( 'joomla.i18n.help' );
		//JToolBarHelper::help( 'biblestudy.teachers', true );
		
		$lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $shareedit->published);
		$this->assignRef('lists', $lists);
		$this->assignRef('shareedit',		$shareedit);
		$this->assignRef('admin_params', $admin_params);
		
		parent::display($tpl);
	}
}
?>