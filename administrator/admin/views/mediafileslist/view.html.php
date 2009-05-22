<?php
/**
 * Podcast list View for Bible Study Component
 *
 * @license		GNU/GPL
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.view');
jimport('joomla.i18n.help');
class biblestudyViewmediafileslist extends JView {

	function display($tpl = null) {
		global $mainframe, $option;

		//Handle References
		$params = &JComponentHelper::getParams($option);
		$db =& JFactory::getDBO();
		$uri =& JFactory::getURI();

		//Handle Toolbar
		JToolBarHelper::title(   JText::_( 'Media Files Manager' ), 'generic.png' );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		//Checks to see if the admin allows rows to be deleted
		if ($params->get('allow_deletes') > 0 ):
		JToolBarHelper::deleteList();
		endif;
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::preferences('com_biblestudy', '550');
		JToolBarHelper::help( 'biblestudy.mediafileslist', true );

		$filter_year		= $mainframe->getUserStateFromRequest( $option.'filter_year','filter_year',0,'int' );
		//$filter_order		= $mainframe->getUserStateFromRequest( $option.'filter_order','filter_order','DESC','word' );
		$filter_order	= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'ordering',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'ASC' );
		$filter_studyid		= $mainframe->getUserStateFromRequest( $option.'filter_studyid',		'filter_studyid',		0,				'int' );

		// table order
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		$query5 = " SELECT DISTINCT date_format(createdate, '%Y') AS value, date_format(createdate, '%Y') AS text "
		. ' FROM #__bsms_mediafiles '
		. ' ORDER BY value DESC';
		$db->setQuery( $query5 );
		$studyyear = $db->loadObjectList();
		$years[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Year' ) .' -' );
		$years 			= array_merge( $years, $db->loadObjectList() );
		$lists['years']	= JHTML::_('select.genericlist',   $years, 'filter_year', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_year" );

		$query6 = ' SELECT * FROM #__bsms_order '
		. ' ORDER BY id ';
		$db->setQuery( $query6 );
		$orders[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select an Order' ) .' -' );
		$orders 			= array_merge( $orders, $db->loadObjectList() );
		$lists['sorting']	= JHTML::_('select.genericlist',   $orders, 'filter_order', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_order" );

		$query = "SELECT id AS value, CONCAT(studytitle,' - ', studydate, ' - ', studynumber) AS text FROM #__bsms_studies WHERE published = 1 ORDER BY studydate DESC";
		$db->setQuery($query);
		$studies[] = JHTML::_('select.option', '0', '- '. JText::_( 'Select a Study' ) .' -' );
		$studies = array_merge($studies,$db->loadObjectList() );
		$lists['studyid'] = JHTML::_('select.genericlist', $studies, 'study_id', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_studyid");
		// Get data from the model
		$items		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );

		$javascript 	= 'onchange="document.adminForm.submit();"';
		//$lists['studyid'] = JHTML::_('list.category',  'filter_studyid', $option, intval( $filter_studyid ), $javascript );
		// build list of categories

		$this->assignRef('lists', $lists);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('request_url',	$uri->toString());

		parent::display($tpl);
	}
}
?>