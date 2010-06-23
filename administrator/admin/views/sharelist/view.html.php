<?php
/**
 * teacherlist View for Bible Study Component
 * 
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

/**
 * teacherlist View
 *
 */
class biblestudyViewsharelist extends JView
{
	/**
	 * teacherlist view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		global $mainframe, $option; 
		//$params = &JComponentHelper::getParams($option);
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		JToolBarHelper::title(   JText::_( 'Social Network Manager' ), 'social.png' );
		//Checks to see if the admin allows rows to be deleted
		JToolBarHelper::deleteList();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		//JToolBarHelper::preferences('com_biblestudy', '550');
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy', true );
		$uri	=& JFactory::getURI();
		
		// Get data from the model
		$items		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );
		//$paramsdata = $items->params;
		//$paramsdefs = JPATH_COMPONENT.DS.'models'.DS.'shareedit.xml';
		//$params = new JParameter($items[0]->params);
		//dump ($items);
	//$this->assignRef('params', $params);	
	$this->assignRef('items',		$items);
	$this->assignRef('pagination',	$pagination);
// table order
		
		$this->assignRef('lists', $lists);
		$this->assignRef('items',		$items);
		$this->assignRef('request_url',	$uri->toString());
		parent::display($tpl);
	}
}
?>