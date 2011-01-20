<?php
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.images.class.php');
class biblestudyViewserieslist extends JView {
	
	/**
	 * studieslist view display method
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
		$params 			=& $mainframe->getPageParameters();
		//dump ($params, 'params: ');
		$t = $params->get('t');
		if (!$t){$t = 1;}
		JRequest::setVar( 't', $t, 'get');
		$template = $this->get('Template');
		$params = new JParameter($template[0]->params);
		//dump ($template, 'template: ');
		$document =& JFactory::getDocument();
		$document->addScript(JURI::base().'components/com_biblestudy/tooltip.js');
		//$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.css');
		$document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
		
		//Import Scripts
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base().'administrator/components/com_biblestudy/js/biblestudy.js');
		$document->addScript(JURI::base().'components/com_biblestudy/tooltip.js');
		$document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
		//Import Stylesheets
		$document->addStylesheet(JURI::base().'administrator/components/com_biblestudy/css/general.css');
		
		$url = $params->get('stylesheet');
		if ($url) {$document->addStyleSheet($url);}
		//Initialize templating class
		//$tmplEninge = $this->loadHelper('templates.helper');
		//$tmplEngine =& bibleStudyTemplate::getInstance();
	
		//dump ($params, 'params: ');
		$uri				=& JFactory::getURI();
		$filter_series		= $mainframe->getUserStateFromRequest( $option.'filter_series',	'filter_series',0,'int' );
		$filter_year		= $mainframe->getUserStateFromRequest( $option.'filter_year','filter_year',0,'int' );
		$filter_orders		= $mainframe->getUserStateFromRequest( $option.'filter_orders','filter_orders','DESC','word' );
		//$search				= JString::strtolower($mainframe->getUserStateFromRequest( $option.'search','search','','string'));
/*
		//Retrieve Parameters
		$tmplStudiesList = $params->get('tmplStudiesList');
		$tmplSingleStudyList = $params->get('tmplSingleStudyList');

		//Retrieve the tags that are used in the current template
		$tmplStudiesList = $tmplEngine->loadTagList(null, $tmplStudiesList);
		$tmplSingleStudyList = $tmplEngine->loadTagList(null, $tmplSingleStudyList, true);

		//@todo Find a way to assign the Return fo the buildSqlSelect to the Model Var
		$model->_select = $tmplEngine->buildSqlSELECT($tmplSingleStudyList);
*/		//dump ($template, 'template: ');
		$items = $this->get('Data');
      //  dump ($items, 'items: ');
		$total = $this->get('Total');
		//dump ($items, 'items: ');
		$pagination = $this->get('Pagination');
		//$teachers = $this->get('Teachers');
		$series = $this->get('Series');
		$orders = $this->get('Orders');
		
        //This is the helper for scripture formatting
        $scripture_call = Jview::loadHelper('scripture');
		//end scripture helper
		$this->assignRef('template', $template);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('order', $orders);
		//$this->assignRef('topic', $topics);
		$menu =& JSite::getMenu();
		$item =& $menu->getActive();
//dump ($admin[0]->main, 'main: ');
		//Get the main study list image
		$images = new jbsImages();
		$main = $images->mainStudyImage();
//		if ($admin[0]->main == '- JBS_CMN_DEFAULT_IMAGE -'){$i_path = 'components/com_biblestudy/images/openbible.png'; $main = getImage($i_path);}  // 2010-11-12 santon: need to be changed
//		else 
//		{
//				if ($admin[0]->main && !$admin_params->get('media_imagefolder')) { $i_path = 'components/com_biblestudy/images/'.$admin[0]->main; }
//				if ($admin[0]->main && $admin_params->get('media_imagefolder')) { $i_path = 'images/'.$admin_params->get('media_imagefolder').'/'.$admin[0]->main;}
//		$main = getImage($i_path);
//		}
//		if ($admin[0]->main == '- JBS_CMN_DEFAULT_IMAGE -' && $admin_params->get('media_imagefolder'))   // 2010-11-12 santon: need to be changed
//		{
//			$i_path = 'components/com_biblestudy/images/openbible.png'; $main = getImage($i_path);
//		}
	  	$this->assignRef('main', $main);
	  	
		//Build Series List for drop down menu
		$types3[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_SERIES' ) .' -' );
		$types3 			= array_merge( $types3, $series );
		$lists['seriesid']	= JHTML::_('select.genericlist',   $types3, 'filter_series', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_series" );

		//build orders
		$ord[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_ORDER' ) .' -' );
		$orders 			= array_merge( $ord, $orders );
		$lists['sorting']	= JHTML::_('select.genericlist',   $orders, 'filter_orders', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_orders" );


		//Build order
		$ord[]		= JHTML::_('select.option', '0', '- '. JTEXT::_('JBS_CMN_SELECT_ORDER') . ' -');
		$ord		= array_merge($ord, $orders);
		$lists['orders'] = JHTML::_('select.genericlist', $ord, 'filter_orders', 'class="inputbox" size="1" oncchange="this.form.submit()"', 'value', 'text', "filter_orders");
		
		//$lists['search']= $search;
		
		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$items);

		$this->assignRef('request_url',	$uri->toString());
		$this->assignRef('params', $params);
		parent::display($tpl);
	}
}
?>