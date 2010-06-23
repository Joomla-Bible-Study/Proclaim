<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewmimetypeedit extends JView
{
	
	function display($tpl = null)
	{
		
		$mimetypeedit		=& $this->get('Data');
		$isNew		= ($mimetypeedit->id < 1);
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Mime Type Edit' ).': <small><small>[ ' . $text.' ]</small></small>', 'mimetype.png' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy', true );

		$this->assignRef('mimetypeedit',		$mimetypeedit);

		parent::display($tpl);
	}
}
?>