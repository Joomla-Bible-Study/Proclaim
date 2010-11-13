<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.view' );
jimport('joomla.i18n.help' );

class biblestudyViewbooksedit extends JView {
	
	function display($tpl = null) {
		$booksedit =& $this->get('Data');
		$isNew = $booksedit->id < 1;

		$titleCaption = $isNew ? JText::_( 'JBS_CMN_NEW' ) : JText::_( 'JBS_CMN_EDIT' );
		$cancelCaption = $isNew ? JText::_('JBS_CMN_CANCEL') : JText::_('JBS_CMN_CLOSE');
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		JToolBarHelper::title(   JText::_( 'JBS_BOK_BOOKS_EDIT' ).': <small><small>[ ' . $titleCaption.' ]</small></small>', 'biblebooks' );
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel('cancel', $cancelCaption);	
		JToolBarHelper::help( 'biblestudy', true );
		
		$this->assignRef('booksedit', $booksedit);

		parent::display($tpl);
	}
}
?>