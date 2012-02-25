<?php

/**
 * @version     $Id: view.html.php 1330 2011-01-06 08:01:38Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die;

require_once (JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.defines.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
jimport('joomla.application.component.view');

class biblestudyViewLandingpage extends JView {

    /**
     * Landing Page view display method
     * @return void
     * */
    function display($tpl = null) {

        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
        include_once($path1 . 'image.php');
        //Load the Admin settings and params from the template
        $this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers');
        $document = JFactory::getDocument();
        $document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $itemparams = $mainframe->getPageParameters();

        // Convert parameter fields to objects.
        $a_params = $this->get('Admin');
        $registry = new JRegistry;
        $registry->loadJSON($a_params[0]->params);
        $this->admin_params = $registry;
        //Prepare meta information (under development)
        if ($itemparams->get('metakey')) {
            $document->setMetadata('keywords', $itemparams->get('metakey'));
        } elseif (!$itemparams->get('metakey')) {
            $document->setMetadata('keywords', $this->admin_params->get('metakey'));
        }

        if ($itemparams->get('metadesc')) {
            $document->setDescription($itemparams->get('metadesc'));
        } elseif (!$itemparams->get('metadesc')) {
            $document->setDescription($this->admin_params->get('metadesc'));
        }
        $model = $this->getModel();

        $t = JRequest::getInt('t', 'get', 1);
        if (!$t) {
            $t = 1;
        }

        $template = $this->get('template');

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($template[0]->params);
        $params = $registry;
        $admin = $this->get('Admin');

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($admin[0]->params);
        $this->admin_params = $registry;

        $document = JFactory::getDocument();
        $document->addScript(JURI::base() . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'tooltip.js');
        $css = $params->get('css','biblestudy.css');
        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/'.$css);

        //Import Scripts
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/jquery.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');

        //Import Stylesheets
        $document->addStylesheet(JURI::base() . 'media/com_biblestudy/css/general.css');

        $url = $params->get('stylesheet');
        if ($url) {
            $document->addStyleSheet($url);
        }

        $uri = JFactory::getURI();
        $filter_topic = $mainframe->getUserStateFromRequest($option . 'filter_topic', 'filter_topic', 0, 'int');
        $filter_book = $mainframe->getUserStateFromRequest($option . 'filter_book', 'filter_book', 0, 'int');
        $filter_teacher = $mainframe->getUserStateFromRequest($option . 'filter_teacher', 'filter_teacher', 0, 'int');
        $filter_series = $mainframe->getUserStateFromRequest($option . 'filter_series', 'filter_series', 0, 'int');
        $filter_messagetype = $mainframe->getUserStateFromRequest($option . 'filter_messagetype', 'filter_messagetype', 0, 'int');
        $filter_year = $mainframe->getUserStateFromRequest($option . 'filter_year', 'filter_year', 0, 'int');
        $filter_location = $mainframe->getuserStateFromRequest($option . 'filter_location', 'filter_location', 0, 'int');
        $filter_orders = $mainframe->getUserStateFromRequest($option . 'filter_orders', 'filter_orders', 'DESC', 'word');
        $search = JString::strtolower($mainframe->getUserStateFromRequest($option . 'search', 'search', '', 'string'));

        $adminrows = new JBSAdmin();
        $total = $this->get('Total');

        $pagination = $this->get('Pagination');
        //@todo need to find a Diffente way to do this
        $menu = JSite::getMenu();
        $item = $menu->getActive();

        //Get the main study list image
        $images = new jbsImages();
        $main = $images->mainStudyImage();

        $this->assignRef('request_url', $uri->toString());
        $this->assignRef('params', $params);
        parent::display($tpl);
    }

}