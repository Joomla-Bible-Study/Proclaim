<?php
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );

class biblestudyViewstudieslist extends JView {
	
	/**
	 * studieslist view display method
	 * @return void
	 **/
	function display($tpl = null) {
		global $mainframe, $option;
		
		$this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers');
		$document =& JFactory::getDocument();
		$model =& $this->getModel();
		
		$params 			=& $mainframe->getPageParameters();
		//dump ($params, 'params: ');
		$templatemenuid = $params->get('templatemenuid');
		if (!$templatemenuid){$templatemenuid = 1;}
		JRequest::setVar( 'templatemenuid', $templatemenuid, 'get');
		$template = $this->get('Template');
		$params = new JParameter($template[0]->params);
		
		$document =& JFactory::getDocument();
		$document->addScript(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.js');
		$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.css');
		$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css');
		
		//Import Scripts
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/biblestudy.js');
		$document->addScript(JURI::base().'components/com_biblestudy/tooltip.js');
		
		//Import Stylesheets
		$document->addStylesheet(JURI::base().'administrator/components/com_biblestudy/css/general.css');
		
		$url = $params->get('stylesheet');
		if ($url) {$document->addStyleSheet($url);}
		//Initialize templating class
		//$tmplEninge = $this->loadHelper('templates.helper');
		//$tmplEngine =& bibleStudyTemplate::getInstance();

		
		//$params->merge($template[0]->params);
		//$templateparams = $template[0]->params;
		//$params->merge($templateparams);
		//dump ($templateparams, 'templateparams: ');
		//
		
		//dump ($params, 'params: ');
		$uri				=& JFactory::getURI();
		$filter_topic		= $mainframe->getUserStateFromRequest( $option.'filter_topic', 'filter_topic',0,'int' );
		$filter_book		= $mainframe->getUserStateFromRequest( $option.'filter_book', 'filter_book',0,'int' );
		$filter_teacher		= $mainframe->getUserStateFromRequest( $option.'filter_teacher','filter_teacher',0,'int' );
		$filter_series		= $mainframe->getUserStateFromRequest( $option.'filter_series',	'filter_series',0,'int' );
		$filter_messagetype	= $mainframe->getUserStateFromRequest( $option.'filter_messagetype','filter_messagetype',0,'int' );
		$filter_year		= $mainframe->getUserStateFromRequest( $option.'filter_year','filter_year',0,'int' );
		$filter_location	= $mainframe->getuserStateFromRequest( $option.'filter_location','filter_location',0,'int');
		$filter_orders		= $mainframe->getUserStateFromRequest( $option.'filter_orders','filter_orders','DESC','word' );
		$search				= JString::strtolower($mainframe->getUserStateFromRequest( $option.'search','search','','string'));
/*
		//Retrieve Parameters
		$tmplStudiesList = $params->get('tmplStudiesList');
		$tmplSingleStudyList = $params->get('tmplSingleStudyList');

		//Retrieve the tags that are used in the current template
		$tmplStudiesList = $tmplEngine->loadTagList(null, $tmplStudiesList);
		$tmplSingleStudyList = $tmplEngine->loadTagList(null, $tmplSingleStudyList, true);

		//@todo Find a way to assign the Return fo the buildSqlSelect to the Model Var
		$model->_select = $tmplEngine->buildSqlSELECT($tmplSingleStudyList);
*/		
		$items = $this->get('Data');
		$total = $this->get('Total');
		
		$pagination = $this->get('Pagination');
		$teachers = $this->get('Teachers');
		$series = $this->get('Series');
		$messageTypes = $this->get('MessageTypes');
		$studyYears = $this->get('StudyYears');
		$locations = $this->get('Locations');
		$topics = $this->get('Topics');
		$orders = $this->get('Orders');
		$books = $this->get('Books');
		
        //This is the helper for scripture formatting
        $scripture_call = Jview::loadHelper('scripture');
		//end scripture helper
		$translated_call = JView::loadHelper('translated');
		$topics = getTranslated($topics);
		$orders = getTranslated($orders);
		$book = getTranslated($books);
		//$this->assignRef('books', $books);
		$this->assignRef('template', $template);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('order', $orders);
		$this->assignRef('topic', $topics);
		$menu =& JSite::getMenu();
		$item =& $menu->getActive();

		
		//Build Teachers
		$types[]		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Teacher' ) .' -' );
		$types 			= array_merge( $types, $teachers );
		$lists['teacher_id']	= JHTML::_('select.genericlist',   $types, 'filter_teacher', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_teacher" );
		
		//Build Series List for drop down menu
		$types3[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Series' ) .' -' );
		$types3 			= array_merge( $types3, $series );
		$lists['seriesid']	= JHTML::_('select.genericlist',   $types3, 'filter_series', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_series" );

		//Build message types
		$types4[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Message Type' ) .' -' );
		$types4 			= array_merge( $types4, $messageTypes );
		$lists['messagetypeid']	= JHTML::_('select.genericlist',   $types4, 'filter_messagetype', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_messagetype" );

		//buld study years
		$years[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Year' ) .' -' );
		$years 			= array_merge( $years, $studyYears );
		$lists['studyyear']	= JHTML::_('select.genericlist',   $years, 'filter_year', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_year" );
		
		//build orders
		$ord[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select an Order' ) .' -' );
		$orders 			= array_merge( $ord, $orders );
		$lists['sorting']	= JHTML::_('select.genericlist',   $orders, 'filter_orders', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_orders" );


		$loc[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Location' ) .' -' );
		$loc 			= array_merge( $loc, $locations );
		$lists['locations']	= JHTML::_('select.genericlist',   $loc, 'filter_location', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_location" );


		//Build Topics

		$top[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Topic' ) .' -' );
		$top 			= array_merge( $top, $topics );
		$lists['topics']	= JHTML::_('select.genericlist',   $top, 'filter_topic', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_topic" );


		//Build Books
		$boo[]		= JHTML::_('select.option', '0', '- '. JTEXT::_('Select a Book') . ' -');
		$boo		= array_merge($boo, $book);
		$lists['books'] = JHTML::_('select.genericlist', $boo, 'filter_book', 'class="inputbox" size="1" oncchange="this.form.submit()"', 'value', 'text', "filter_book");

		//Build order
		$ord[]		= JHTML::_('select.option', '0', '- '. JTEXT::_('Select an Order') . ' -');
		$ord		= array_merge($ord, $orders);
		$lists['orders'] = JHTML::_('select.genericlist', $ord, 'filter_orders', 'class="inputbox" size="1" oncchange="this.form.submit()"', 'value', 'text', "filter_orders");
		
		$lists['search']= $search;
		
		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$items);

		$this->assignRef('request_url',	$uri->toString());
		$this->assignRef('params', $params);
		parent::display($tpl);
	}
}
?>