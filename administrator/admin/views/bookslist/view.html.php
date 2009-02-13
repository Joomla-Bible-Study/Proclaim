<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.view');
jimport('joomla.i18n.help');

class biblestudyViewbookslist extends JView {

	function display($tpl = null) {
		global $mainframe, $option;
		
		$uri =& JFactory::getURI();
		$params =& JComponentHelper::getParams($option);
		
		JToolBarHelper::title(JText::_('Books Manager'), 'generic.png');

		//Checks to see if the admin allows rows to be deleted
		if ($params->get('allow_deletes') > 0 ):
		JToolBarHelper::deleteList();
		endif;

		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::preferences('com_biblestudy', '550');
		JToolBarHelper::help('biblestudy.books', true);


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