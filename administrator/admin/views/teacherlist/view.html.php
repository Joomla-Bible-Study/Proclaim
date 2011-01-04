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
class biblestudyViewteacherlist extends JView
{
	/**
	 * teacherlist view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option'); 
		$params = &JComponentHelper::getParams($option);
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		JToolBarHelper::title(   JText::_( 'JBS_TCH_TEACHER_MANAGER' ), 'teachers.png' );
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
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'ordering',	'cmd' );
		$filter_order_Dir	= strtoupper($mainframe->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'ASC' ));
		// Get data from the model
		$items		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );
		
	$this->assignRef('items',		$items);
	$this->assignRef('pagination',	$pagination);
// table order
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;
		$this->assignRef('lists', $lists);
		$this->assignRef('items',		$items);
		$this->assignRef('request_url',	$uri->toString());
		parent::display($tpl);
	}
}
?>