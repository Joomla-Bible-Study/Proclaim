<?php
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class biblestudyViewstudieslist extends JView {

	function display($tpl = null) {
	   
       
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		jimport( 'joomla.i18n.help' );
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		JToolBarHelper::title(JText::_('JBS_STY_STUDIES_MANAGER'), 'studies.png' );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		//JToolBarHelper::preferences('com_biblestudy', '550');
		JToolBarHelper::help('biblestudy', true );

		//Include the Jquery Library
		$document =& JFactory::getDocument();
		$document->addScript(JURI::base().'components/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/noconflict.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/biblestudy.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/plugins/jquery.selectboxes.js');

		//Register relative models
		//$booksList = $this->setModel('topicsedit', false, $test);
		
		//dump($this->_models);

		$db		=& JFactory::getDBO();
		$uri	=& JFactory::getURI();
		$filter_topic		= $mainframe->getUserStateFromRequest( $option.'filter_topic', 'filter_topic',0,'int' );
		$filter_book		= $mainframe->getUserStateFromRequest( $option.'filter_book', 'filter_book',0,'int' );
		$filter_teacher		= $mainframe->getUserStateFromRequest( $option.'filter_teacher','filter_teacher',0,'int' );
		$filter_series		= $mainframe->getUserStateFromRequest( $option.'filter_series',	'filter_series',0,'int' );
		$filter_messagetype	= $mainframe->getUserStateFromRequest( $option.'filter_messagetype','filter_messagetype',0,'int' );
		$filter_year		= $mainframe->getUserStateFromRequest( $option.'filter_year','filter_year',0,'int' );
		$search				= $mainframe->getUserStateFromRequest( $option.'search','search','','string' );
		$search				= JString::strtolower( $search );
		$filter_searchby	= $mainframe->getUserStateFromRequest( $option.'filter_searchby','filter_searchby','studytext','word' );

		$filter_year		= $mainframe->getUserStateFromRequest( $option.'filter_year','filter_year',0,'int' );
		$filter_order	 	= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'studydate',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'ASC' );

		$javascript 	= 'onchange="document.adminForm.submit();"';
		// Get data from the model
		$rows		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );
		$mediaFiles		= & $this->get( 'Files');
        
		//Loop through the rows to retrieve plays and downloads
		foreach($rows as $row) {
			$row->totalplays = $this->getModel()->getPlays($row->id);
			$row->totaldownloads = $this->getModel()->getDownloads($row->id);
		}
		//Build Teacher List for drop down menu


		$database	= & JFactory::getDBO();
		$query1 = 'SELECT id AS value, teachername AS text, published'
		. ' FROM #__bsms_teachers'
		. ' WHERE published = 1'
		. ' ORDER BY id';
		$database->setQuery( $query1 );
		$teacher_id = $database->loadObjectList();
		$types[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_TEACHER' ) .' -' );
		$types 			= array_merge( $types, $database->loadObjectList() );
		$lists['teacher_id']	= JHTML::_('select.genericlist',   $types, 'filter_teacher', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_teacher" );

		// Build Books list for drop down menu


		/*$query2 = 'SELECT booknumber AS value, bookname AS text, published'
			. ' FROM #__bsms_books'
			. ' WHERE published = 1'
			. ' ORDER BY booknumber';
			$database->setQuery( $query2 );
			$bookid = $database->loadObjectList();
			$types2[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_BOOK' ) .' -' );
			$types2 			= array_merge( $types2, $database->loadObjectList() );
			$lists['bookid']	= JHTML::_('select.genericlist',   $types2, 'filter_book', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_book" );
			*/
		//Build Series List for drop down menu

		$query3 = 'SELECT id AS value, series_text AS text, published'
		. ' FROM #__bsms_series'
		. ' WHERE published = 1'
		. ' ORDER BY id';
		$database->setQuery( $query3 );
		$seriesid = $database->loadObjectList();
		$types3[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_SERIE' ) .' -' );
		$types3 			= array_merge( $types3, $database->loadObjectList() );
		$lists['seriesid']	= JHTML::_('select.genericlist',   $types3, 'filter_series', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_series" );

		//Build the Message Type List for the drop down menu

		$query4 = 'SELECT id AS value, message_type AS text, published'
		. ' FROM #__bsms_message_type'
		. ' WHERE published = 1'
		. ' ORDER BY message_type';
		$database->setQuery( $query4 );
		$messagetypeid = $database->loadObjectList();
		$types4[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_MESSAGE_TYPE' ) .' -' );
		$types4 			= array_merge( $types4, $database->loadObjectList() );
		$lists['messagetypeid']	= JHTML::_('select.genericlist',   $types4, 'filter_messagetype', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_messagetype" );

		$query5 = " SELECT DISTINCT date_format(studydate, '%Y') AS value, date_format(studydate, '%Y') AS text "
		. ' FROM #__bsms_studies '
		. ' ORDER BY value DESC';
		$database->setQuery( $query5 );
		$studyyear = $database->loadObjectList();
		$years[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_YEAR' ) .' -' );
		$years 			= array_merge( $years, $database->loadObjectList() );
		$lists['studyyear']	= JHTML::_('select.genericlist',   $years, 'filter_year', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_year" );

		/*$query6 = ' SELECT * FROM #__bsms_order '
		 . ' ORDER BY id ';
		 $database->setQuery( $query6 );
		 $sortorder = $database->loadObjectList();
		 $orders[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_ORDER' ) .' -' );
		 $orders 			= array_merge( $orders, $database->loadObjectList() );
		 $lists['sorting']	= JHTML::_('select.genericlist',   $orders, 'filter_orders', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_orders" );

		 $query7 = ' SELECT * FROM #__bsms_search ';
		 $database->setQuery( $query7 );
		 $searchbyquery = $database->loadObjectList();
		 $searchby[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_STY_SELECT_SEARCH_FIELD' ) .' -' );
		 $searchby 			= array_merge( $searchby, $database->loadObjectList() );
		 //$lists['searchby'] = JHTML::_('select.genericlist',  $searchby, 'filter_searchby', 'class="inputbox" size="1"', 'value', 'text', "$filter_searchby");

		 //Build Topics List for drop down menu

		 $query8 = 'SELECT id AS value, topic_text AS text, published'
			. ' FROM #__bsms_topics'
			. ' WHERE published = 1'
			. ' ORDER BY id';
			$database->setQuery( $query8 );
			$topicsid = $database->loadObjectList();
			$topics[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_TOPIC' ) .' -' );
			$topics 			= array_merge( $topics, $database->loadObjectList() );
			$lists['topics']	= JHTML::_('select.genericlist',   $topics, 'filter_topic', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_topic" );
			*/
		$lists['search']= $search;

		// table order
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('lists',		$lists);
		$this->assignRef('rows', 		$rows);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('request_url',	$uri->toString());
	// commented out for now - broken in model (Tom)	$this->assignRef('mediaFiles', $mediaFiles);

		parent::display($tpl);
	}
}
?>