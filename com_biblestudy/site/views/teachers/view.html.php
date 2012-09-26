<?php

/**
 * Teachers JView
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');

jimport('joomla.application.component.view');

/**
 * View class for Teachers
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class BiblestudyViewTeachers extends JView {

    /**
     * Items
     * @var type
     */
    protected $items;

    /**
     * Pagination
     * @var type
     */
    protected $pagination;

    /**
     * State
     * @var type
     */
    protected $state;

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

        //Load the Admin settings and params from the template
        JView::loadHelper('params');
        $this->admin = BsmHelper::getAdmin(true);
        JView::loadHelper('image');
        $template = $this->get('template');
        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($template->params);
        $params = $registry;
        $t = $params->get('teachertemplateid');
        if (!$t) {
            $t = JRequest::getVar('t', 1, 'get', 'int');
        }
        $a_params = $this->get('Admin');
        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($a_params[0]->params);
        $this->admin_params = $registry;
        $mainframe = JFactory::getApplication();
        $document = JFactory::getDocument();
        $itemparams = $mainframe->getPageParameters();
        $uri = JFactory::getURI();
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
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/jquery.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/noconflict.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/tooltip.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');

        //Import Stylesheets
        $document->addStylesheet(JURI::base() . 'media/com_biblestudy/css/general.css');
        $document->addStylesheet(JURI::base() . 'media/com_biblestudy/css/studieslist.css');
        $css = $params->get('css');
        if ($css <= "-1"):
            $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
        else:
            $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
        endif;
        $images = new jbsImages();
        // Get data from the model
        $items = $this->get('Items');

        foreach ($items as $i => $item) {
            $image = $images->getTeacherThumbnail($item->teacher_thumbnail, $item->thumb);
            $items[$i]->image = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . ' alt="' . $item->teachername . '">';
            $items[$i]->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':' . str_replace(' ', '-', htmlspecialchars_decode($item->teachername, ENT_QUOTES));
            $items[$i]->teacherlink = JRoute::_('index.php?option=com_biblestudy&view=teacher&id=' . $item->slug . '&t=' . $t);
        }
        $app = JFactory::getApplication();
        $menu = $app->getMenu();

        $pagination = $this->get('Pagination');
        $this->page = new stdClass();
        $this->page->pagelinks = $pagination->getPagesLinks();
        $this->page->counter = $pagination->getPagesCounter();
        $this->assignRef('pagination', $pagination);
        $this->assignRef('items', $items);
        $stringuri = $uri->toString();
        $this->assignRef('request_url', $stringuri);
        $this->assignRef('params', $params);

        parent::display($tpl);
    }

}