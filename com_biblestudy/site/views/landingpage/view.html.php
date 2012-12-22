<?php

/**
 * LandingPage JViewLegacy
 * @package BibleStudy.Site
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

//require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
JLoader::register('jbsImages', dirname(__FILE__) . '/lib/biblestudy.images.class.php');
//require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
JLoader::register('JBSAdmin', dirname(__FILE__) . '/lib/biblestudy.admin.class.php');
JLoader::register('BiblestudyHelper', JPATH_COMPONENT.'/helpers/images.php');
JLoader::register('JBSMHelper', JPATH_ADMINISTRATOR.'/components/com_biblestudy/helpers/helper.php');

/**
 * Landing page list view class
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyViewLandingpage extends JViewLegacy {

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise a JError object.
     *
     * @see     fetch()
     * @since   11.1
     */
    public function display($tpl = null) {

        $mainframe = JFactory::getApplication();
        $input = new JInput;
        $option = $input->get('option','','cmd');
        JViewLegacy::loadHelper('image');
        //Load the Admin settings and params from the template
        $this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers');
        $document = JFactory::getDocument();
        $mainframe = JFactory::getApplication();
        
        $itemparams = $mainframe->getPageParameters();

        // Convert parameter fields to objects.
        $a_params = $this->get('Admin');
        $registry = new JRegistry;
        $registry->loadString($a_params[0]->params);
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
        //$model = $this->getModel();

        $t = $input->get('t', 1, 'int');
        if (!$t) {
            $t = 1;
        }

        $template = $this->get('template');

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadString($template[0]->params);
        $params = $registry;
        $admin = $this->get('Admin');

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadString($admin[0]->params);
        $this->admin_params = $registry;

        $document = JFactory::getDocument();
        $document->addScript(JURI::base() . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'tooltip.js');
        JViewLegacy::loadHelper('helper');
        $images = new jbsImages();
        $showhide = $images->getShowhide();
        //$document->addScriptDeclaration($showhide);
        
        $css = $params->get('css');
         if (BIBLESTUDY_CHECKREL){JHtml::_('jquery.framework');}
        //Import Scripts
        JHtml::script('media/com_biblestudy/js/biblestudy.js');
        JHtml::script('media/com_biblestudy/js/jquery.js');
        //$document->addScript(JURI::base() . 'media/com_biblestudy/js/jquery.js');
        //$document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');

        //Import Stylesheets
         JHtml::stylesheet('media/com_biblestudy/css/general.css');
        //$document->addStylesheet(JURI::base() . 'media/com_biblestudy/css/general.css');
        if ($css <= "-1"):
             JHtml::stylesheet('media/com_biblestudy/css/biblestudy.css');
            //$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
        else:
            JHtml::stylesheet('media/com_biblestudy/css/site/' . $css);
            //$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
        endif;

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
        $app = JFactory::getApplication();
        $menu = $app->getMenu();
        $item = $menu->getActive();

        //Get the main study list image
        $images = new jbsImages();
        $main = $images->mainStudyImage();
        $Uri_toString = $uri->toString();
        $this->assignRef('request_url', $Uri_toString);
        $this->assignRef('params', $params);
        parent::display($tpl);
    }

}