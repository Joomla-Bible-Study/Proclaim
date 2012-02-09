<?php
/**
 * @version     $Id: view.html.php 1330 2011-01-06 08:01:38Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die;

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
        $template = $this->get('template');
         // Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadJSON($template[0]->params);
                $params = $registry;
        $a_params = $this->get('Admin');
         // Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadJSON($a_params[0]->params);
                $this->admin_params = $registry;
        $mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');


		$db		=& JFactory::getDBO();
		$uri	=& JFactory::getURI();

		$document =& JFactory::getDocument();

         $itemparams =& $mainframe->getPageParameters();

    	//Prepare meta information (under development)
    	if ($itemparams->get('metakey'))
		{
			$document->setMetadata('keywords', $itemparams->get('metakey'));
		}
		elseif (!$itemparams->get('metakey'))
		{
			$document->setMetadata('keywords', $this->admin_params->get('metakey'));
		}

 	  if ($itemparams->get('metadesc'))
		{
			$document->setDescription($itemparams->get('metadesc'));
		}
		elseif (!$itemparams->get('metadesc'))
		{
			$document->setDescription($this->admin_params->get('metadesc'));
		}
		$document->addScript(JURI::base().'components/com_biblestudy/assets/js/jquery.js');
		$document->addScript(JURI::base().'components/com_biblestudy/assets/js/biblestudy.js');
		$document->addScript(JURI::base().'components/com_biblestudy/assets/js/tooltip.js');
		$document->addScript(JURI::base().'components/com_biblestudy/assets/player/jwplayer.js');

		//Import Stylesheets
		$document->addStylesheet(JURI::base().'components/com_biblestudy/assets/css/general.css');
		$document->addStylesheet(JURI::base().'components/com_biblestudy/assets/css/studieslist.css');
		$document->addStylesheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
		$url = $params->get('stylesheet');
		if ($url) {$document->addStyleSheet($url);}

		// Get data from the model
		$items		= & $this->get( 'Data');

        //Adjust the slug if there is no alias in the row

        foreach ($items AS $item)
        {
            $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':' . str_replace(' ','-',htmlspecialchars_decode($item->teachername, ENT_QUOTES)) ;
        }
		$menu =& JSite::getMenu();
	//	$item =& $menu->getActive();

		$pagination = $this->get('Pagination');
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('items',		$items);

		$this->assignRef('request_url',	$uri->toString());
		$this->assignRef('params', $params);

		parent::display($tpl);
	}
}