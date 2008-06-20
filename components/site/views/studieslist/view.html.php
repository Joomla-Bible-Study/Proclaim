<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewstudieslist extends JView
{
	/**
	 * studieslist view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		global $mainframe, $option;
		//$params = &JComponentHelper::getParams($option);
		$params =& $mainframe->getPageParameters();
		//$model	  = &$this->getModel();
		//JToolBarHelper::addNewX();
		$db		=& JFactory::getDBO();
		$uri	=& JFactory::getURI();
		$filter_topic		= $mainframe->getUserStateFromRequest( $option.'filter_topic', 'filter_topic',0,'int' );
		$filter_book		= $mainframe->getUserStateFromRequest( $option.'filter_book', 'filter_book',0,'int' );
		$filter_teacher		= $mainframe->getUserStateFromRequest( $option.'filter_teacher','filter_teacher',0,'int' );
		$filter_series		= $mainframe->getUserStateFromRequest( $option.'filter_series',	'filter_series',0,'int' );
		$filter_messagetype	= $mainframe->getUserStateFromRequest( $option.'filter_messagetype','filter_messagetype',0,'int' );
		$filter_year		= $mainframe->getUserStateFromRequest( $option.'filter_year','filter_year',0,'int' );
		$filter_orders		= $mainframe->getUserStateFromRequest( $option.'filter_orders','filter_orders','DESC','word' );
		$search				= $mainframe->getUserStateFromRequest( $option.'search','search','','string' );
		$search				= JString::strtolower( $search );
		//$filter_searchby	= $mainframe->getUserStateFromRequest( $option.'filter_searchby','filter_searchby','studytext','word' );
		//$limit				= JRequest::getVar('limit',				$mainframe->getCfg('list_limit'),	'', 'int');
		//$limitstart			= JRequest::getVar('limitstart',		0,				'', 'int');
		//$options['limit']		= $limit;
		//$options['limitstart']	= $limitstart;
	//$javascript 	= 'onchange="document.adminForm.submit();"';
		// Get data from the model
		
		//this is an experiment from com_content
		//$limit		= $mainframe->getUserStateFromRequest('com_biblestudy.studieslist'.'.limit', 'limit', 'int');
		//$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		//jimport('joomla.html.pagination');
		//$pagination = new JPagination($total, $limitstart, $limit);
		
		//end of com_content experiment
		
		$items		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );
		//jimport('joomla.html.pagination');
		//$pagination = new JPagination($total, $limitstart, $limit);
		//$this->assignRef('request_url',	$uri->toString());
		$document =& JFactory::getDocument();
		$this->assignRef('pagination',	$pagination);
		$menu =& JSite::getMenu();
		$item =& $menu->getActive();
		//$params->def('page_title', $item->name);
		//$document->setTitle($params->get('page_title'));
//Build Teacher List for drop down menu


	$database	= & JFactory::getDBO();
			$query = 'SELECT id AS value, teachername AS text, published'
			. ' FROM #__bsms_teachers'
			. ' WHERE published = 1'
			. ' ORDER BY id';
		$database->setQuery( $query );
		$teacher_id = $database->loadObjectList();
		$types[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Teacher' ) .' -' );
		$types 			= array_merge( $types, $db->loadObjectList() );
		$lists['teacher_id']	= JHTML::_('select.genericlist',   $types, 'filter_teacher', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_teacher" );
		
// Build Books list for drop down menu

	
		/*$query2 = 'SELECT booknumber AS value, bookname AS text, published'
			. ' FROM #__bsms_books'
			. ' WHERE published = 1'
			. ' ORDER BY booknumber';
		$database->setQuery( $query2 );
		$bookid = $database->loadObjectList();
		//$this->assignRef('bookid', $bookid);
		//$bookid =& $database->loadAssocList();
		//foreach ($bookid as $v) {
			//$v[]['text'] = 'JText::_('.$v[]['text'].')';
		//}
		//$rows1=count($bookid);
							//for($i=0;$i<$rows1;$i++)
							//{
							//$bookid[$i]['text'] = 'JText::_('.$bookid[0]['text'].')';
							//} 
		//foreach ($bookid as $c1) { 
		//			$text = 'JText::_('.$c1['text'].')';
		//}
		//$this->assignRef('lists2',$text);
		//$booksid->text = JText::_($booksid->text);
		$types2[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Book' ) .' -' );
		//$types2[]		= JHTML::_('select.option','0',JText::printf($bookid));
		$types2 			= array_merge( $types2, $db->loadObjectList() );
		//$types2 			= array_merge( $types2, $bookid );
		$lists['bookid']	= JHTML::_('select.genericlist',  $types2, 'filter_book', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_book" );
		*/
//Build Series List for drop down menu

$query3 = 'SELECT id AS value, series_text AS text, published'
			. ' FROM #__bsms_series'
			. ' WHERE published = 1'
			. ' ORDER BY id';
		$database->setQuery( $query3 );
		$seriesid = $database->loadObjectList();
		$types3[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Series' ) .' -' );
		$types3 			= array_merge( $types3, $db->loadObjectList() );
		$lists['seriesid']	= JHTML::_('select.genericlist',   $types3, 'filter_series', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_series" );

//Build the Message Type List for the drop down menu

$query4 = 'SELECT id AS value, message_type AS text, published'
			. ' FROM #__bsms_message_type'
			. ' WHERE published = 1'
			. ' ORDER BY message_type';
		$database->setQuery( $query4 );
		$messagetypeid = $database->loadObjectList();
		$types4[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Message Type' ) .' -' );
		$types4 			= array_merge( $types4, $db->loadObjectList() );
		$lists['messagetypeid']	= JHTML::_('select.genericlist',   $types4, 'filter_messagetype', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_messagetype" );
				
$query5 = " SELECT DISTINCT date_format(studydate, '%Y') AS value, date_format(studydate, '%Y') AS text "
			. ' FROM #__bsms_studies '
			. ' ORDER BY value DESC';
		$database->setQuery( $query5 );
		$studyyear = $database->loadObjectList();
		$years[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Year' ) .' -' );
		$years 			= array_merge( $years, $db->loadObjectList() );
		$lists['studyyear']	= JHTML::_('select.genericlist',   $years, 'filter_year', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_year" );

$query6 = ' SELECT * FROM #__bsms_order '
		. ' ORDER BY id ';
		$database->setQuery( $query6 );
		$sortorder = $database->loadObjectList();
		$orders[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select an Order' ) .' -' );
		$orders 			= array_merge( $orders, $db->loadObjectList() );
		$lists['sorting']	= JHTML::_('select.genericlist',   $orders, 'filter_orders', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_orders" );
		


$query8 = 'SELECT DISTINCT #__bsms_studies.topics_id AS value, #__bsms_topics.topic_text AS text'
			. ' FROM #__bsms_studies'
			. ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studies.topics_id)'
			. ' WHERE #__bsms_topics.published = 1'
			. ' ORDER BY #__bsms_topics.topic_text ASC';
		$database->setQuery( $query8 );
		$topicsid = $database->loadObjectList();
		$topics[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Topic' ) .' -' );
		$topics 			= array_merge( $topics, $db->loadObjectList() );
		$lists['topics']	= JHTML::_('select.genericlist',   $topics, 'filter_topic', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_topic" );


		
		
		$lists['search']= $search;
		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$items);
		
		$this->assignRef('request_url',	$uri->toString());
		$this->assignRef('params', $params);

		parent::display($tpl);
	}
}
?>