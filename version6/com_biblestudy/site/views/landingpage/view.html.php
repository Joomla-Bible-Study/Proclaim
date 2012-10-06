<?php
/**
 * @version     $Id: view.html.php 1330 2011-01-06 08:01:38Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
defined('_JEXEC') or die();
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.images.class.php');
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
		$templatemenuid = $params->get('templatemenuid');
		$test = $params->get('bookslabel');
		//echo $test;
		if (!$templatemenuid){$templatemenuid = 1;}
		JRequest::setVar( 'templatemenuid', $templatemenuid, 'get');
		$template = $this->get('Template');
		$params = new JParameter($template[0]->params);
		$document =& JFactory::getDocument();
		$document->addScript(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.js');
        $stylesheet = JURI::base().'components/com_biblestudy/assets/css/biblestudy.css';
        $document->addStyleSheet($stylesheet);
		
		//Import Scripts
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/biblestudy.js');
		$document->addScript(JURI::base().'components/com_biblestudy/assets/js/jwplayer.js');
		//Styles from tooltip.css moved to assets/css/biblestudy.css
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
		$book = getTranslated($books);
		$this->assignRef('template', $template);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('order', $orders);
		$this->assignRef('topic', $topics);
		$menu =& JSite::getMenu();
		$item =& $menu->getActive();
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