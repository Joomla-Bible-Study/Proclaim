<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.view' );
jimport('joomla.i18n.help' );

class biblestudyViewbooksedit extends JView {
	
	function display($tpl = null) {
		$booksedit =& $this->get('Data');
		$isNew = $booksedit->id < 1;

		$titleCaption = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		$cancelCaption = $isNew ? JText::_('Cancel') : JText::_('Close');
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		JToolBarHelper::title(   JText::_( 'Books Edit' ).': <small><small>[ ' . $titleCaption.' ]</small></small>', 'biblebooks' );
		JToolBarHelper::save();
		JToolBarHelper::cancel('cancel', $cancelCaption);	
		JToolBarHelper::help( 'biblestudy.books', true );
		
		$this->assignRef('booksedit', $booksedit);

		parent::display($tpl);
	}
}
?>