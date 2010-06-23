<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.view');
jimport('joomla.i18n.help');

class biblestudyViewbookslist extends JView {

	function display($tpl = null) {
		global $mainframe, $option;
		
		$uri =& JFactory::getURI();
		$params =& JComponentHelper::getParams($option);
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		JToolBarHelper::title(JText::_('Books Manager'), 'biblebooks.png');

		//Checks to see if the admin allows rows to be deleted
		
		JToolBarHelper::deleteList();
		

		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		//JToolBarHelper::preferences('com_biblestudy', '550');
		JToolBarHelper::help('biblestudy', true);


		$db=& JFactory::getDBO();
		

		// Get data from the model
		$items =& $this->get( 'Data');
		$total =& $this->get( 'Total');
		
		$pagination =& $this->get( 'Pagination' );

		$javascript = 'onchange="document.adminForm.submit();"';

		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('request_url',	$uri->toString());

		parent::display($tpl);
	}
}
?>