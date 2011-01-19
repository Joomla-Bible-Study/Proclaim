<?php
defined('_JEXEC') or die();
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.images.class.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
jimport( 'joomla.application.component.view' );

class biblestudyViewLandingpage extends JView {
	
	/**
	 * Landing Page view display method
	 * @return void
	 **/
	function display($tpl = null) {
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
		include_once($path1.'image.php');
		$this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers');
		$document =& JFactory::getDocument();
		$model =& $this->getModel();
		$admin=& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		$this->assignRef('admin_params', $admin_params);
		$this->assignRef('admin', $admin);
		$params =& $mainframe->getPageParameters();
	

 $menuitemid = JRequest::getInt( 'Itemid' );
  if ($menuitemid)
  {
    $menu = JSite::getMenu();
    $menuparams = $menu->getParams( $menuitemid );
    $params->merge( $menuparams );
  }
		

		//dump ($params, 'params: ');
		$t = $params->get('t');
		$test = $params->get('bookslabel');
		//echo $test;
		if (!$t){$t = 1;}
		JRequest::setVar( 't', $t, 'get');
		$template = $this->get('Template');
		$params = new JParameter($template[0]->params);
		//dump ($template, 'template: ');
		$document =& JFactory::getDocument();
		$document->addScript(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.js');
		//$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.css');
        $stylesheet = JURI::base().'components/com_biblestudy/assets/css/biblestudy.css';
        $document->addStyleSheet($stylesheet);
		
		//Import Scripts
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/biblestudy.js');
		
		//Import Stylesheets
		$document->addStylesheet(JURI::base().'administrator/components/com_biblestudy/css/general.css');
		
		$url = $params->get('stylesheet');
		if ($url) {$document->addStyleSheet($url);}
		
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

		$results = $this->get('Data');
        $adminrows = new JBSAdmin();
        $items = $adminrows->showRows($results);
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
		//dump ($topics);
        //This is the helper for scripture formatting
        $scripture_call = Jview::loadHelper('scripture');
		//end scripture helper
		$translated_call = JView::loadHelper('translated');
		//dump ($topics, 'topics: ');
		$topics = getTranslated($topics);
		
		//$orders = getTranslated($orders);
		$book = getTranslated($books);
		//$this->assignRef('books', $books);
		$this->assignRef('template', $template);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('order', $orders);
		$this->assignRef('topic', $topics);
		$menu =& JSite::getMenu();
		$item =& $menu->getActive();
//dump ($admin[0]->main, 'main: ');
		//Get the main study list image
		$images = new jbsImages();
		$main = $images->mainStudyImage();

		
	  	$this->assignRef('main', $main);


		$this->assignRef('items',		$items);

		$this->assignRef('request_url',	$uri->toString());
		$this->assignRef('params', $params);
		parent::display($tpl);
	}
}
?>