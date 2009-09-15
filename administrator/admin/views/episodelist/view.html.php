<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewepisodelist extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;
		
		JToolBarHelper::title(   JText::_( 'Podcast Episodes Manager' ), 'generic.png' );
		
		//JToolBarHelper::preferences('com_biblestudy', '550');
		
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy.episodelist', true );
		
		$db		=& JFactory::getDBO();
		$uri	=& JFactory::getURI();
		$filter_podcast		= $mainframe->getUserStateFromRequest( $option.'filter_podcast', 'filter_podcast',0,'int' );
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'DESC',				'word' );
		$filter_study		= $mainframe->getUserStateFromRequest( $option.'filter_study', 'filter_study', 'DESC', 'int' );
	$javascript 	= 'onchange="document.adminForm.submit();"';
		// Get data from the model
		$rows		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );
		
$database	= & JFactory::getDBO();

$query6 = ' SELECT * FROM #__bsms_order '
		. ' ORDER BY id ';
		$database->setQuery( $query6 );
		$orders[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Order By MediaFile Date' ) .' -' );
		$orders 			= array_merge( $orders, $database->loadObjectList() );
		$lists['sorting']	= JHTML::_('select.genericlist',   $orders, 'filter_order', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_order" );		
//Build Podcast List for drop down menu

$query = ' SELECT DISTINCT s.id AS value, s.studytitle AS text FROM #__bsms_studies AS s WHERE s.published = 1'
		.' ORDER BY s.studydate DESC';
		$database->setQuery($query);
		$studies[] = JHTML::_('select.option', '0', '- '. JText::_( 'Select a Study' ) .' -' );
		$studies = array_merge($studies, $database->loadObjectList() );
		$lists['studies'] = JHTML::_('select.genericlist', $studies, 'filter_study', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_study" );
	
$query1 = 'SELECT id AS value, title AS text, published'
			. ' FROM #__bsms_podcast'
			. ' WHERE published = 1'
			. ' ORDER BY title ASC';
		$database->setQuery( $query1 );
		$types[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Podcast' ) .' -' );
		$types 			= array_merge( $types, $database->loadObjectList() );
		$lists['podcast_id']	= JHTML::_('select.genericlist',   $types, 'filter_podcast', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_podcast" );
		
				


		//$lists['search']= $search;
		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('lists',		$lists);
		$this->assignRef('rows', 		$rows);
		//$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('request_url',	$uri->toString());

		parent::display($tpl);
	}
}
?>