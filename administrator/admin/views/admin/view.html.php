<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewadmin extends JView
{
	
	function display($tpl = null)
	{
		
		$admin		=& $this->get('Data');
		$isNew		= ($admin->id < 1);
		//$lists = array();
		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Administration' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		
		//if ($isNew)  {
			//JToolBarHelper::cancel();
		//} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		//}
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy.admin', true );
		//$lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $locationsedit->published);
		//$this->assignRef('lists', $lists);
		//$this->assignRef('locationsedit',		$locationsedit);

		parent::display($tpl);
	}
}
?>