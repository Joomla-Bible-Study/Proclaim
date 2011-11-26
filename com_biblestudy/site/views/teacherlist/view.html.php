<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

require_once (JPATH_SITE  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.defines.php');
jimport( 'joomla.application.component.view' );


class biblestudyViewteacherlist extends JView
{
	/**
	 * teacherlist view display method
	 * @return void
	 **/
	function display($tpl = null)
	{

		//Load the Admin settings and params from the template
		$this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'helpers');
		$this->loadHelper('params');
		$this->admin = BsmHelper::getAdmin(true);

		$t = JRequest::getInt('t','get',1);
		if (!$t) {
			$t = 1;
		}
		//      JRequest::setVar('t', $t, 'get');
		$template = $this->get('template');
		// $params = new JParameter($template[0]->params);
		// Convert parameter fields to objects.
		$registry = new JRegistry;
		$registry->loadJSON($template[0]->params);
		$params = $registry;
		$a_params = $this->get('Admin');
		//   $this->admin_params = new JParameter($a_params[0]->params);
		// Convert parameter fields to objects.
		$registry = new JRegistry;
		$registry->loadJSON($a_params[0]->params);
		$this->admin_params = $registry;
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');


		$db		=& JFactory::getDBO();
		$uri	=& JFactory::getURI();

		$document =& JFactory::getDocument();
		$document->addScript(JURI::base().'components/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/biblestudy.js');
		$document->addScript(JURI::base().'components/com_biblestudy/tooltip.js');

		//Import Stylesheets
		$document->addStylesheet(JURI::base().'components/com_biblestudy/css/general.css');
		$document->addStylesheet(JURI::base().'components/com_biblestudy/assets/css/studieslist.css');
		$document->addStylesheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
		$url = $params->get('stylesheet');
		if ($url) {
			$document->addStyleSheet($url);
		}

		// Get data from the model
		$items		= & $this->get( 'Data');
		$menu =& JSite::getMenu();
		$item =& $menu->getActive();

		$pagination = $this->get('Pagination');
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('items',		$items);

		$this->assignRef('request_url',	$uri->toString());
		$this->assignRef('params', $params);

		parent::display($tpl);
	}
}