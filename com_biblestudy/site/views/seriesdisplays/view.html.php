<?php

/**
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.pagebuilder.class.php');

class BiblestudyViewSeriesdisplays extends JView {

    /**
     * studieslist view display method
     * @return void
     * */
    function display($tpl = null) {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
        include_once($path1 . 'image.php');

        $document = JFactory::getDocument();

        //  $model = $this->getModel();
        //Load the Admin settings and params from the template
        $this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers');
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin(true);

        $t = JRequest::getInt('t', 'get', 1);
        if (!$t) {
            $t = 1;
        }
        $template = $this->get('template');
        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($template->params);
        $params = $registry;
        $a_params = $this->get('Admin');
        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($a_params[0]->params);
        $this->admin_params = $registry;

        $itemparams = $mainframe->getPageParameters();

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

        $css = $params->get('css', 'biblestudy.css');
        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);


        //Import Scripts
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/jquery.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/tooltip.js');
        $document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/jwplayer.js');
        //Import Stylesheets
        $document->addStylesheet(JURI::base() . 'media/com_biblestudy/css/general.css');

        $url = $params->get('stylesheet');
        if ($url) {
            $document->addStyleSheet($url);
        }

        $uri = JFactory::getURI();
        $filter_series = $mainframe->getUserStateFromRequest($option . 'filter_series', 'filter_series', 0, 'int');

        $items = $this->get('Items');
        $images = new jbsImages();
        //Adjust the slug if there is no alias in the row

        foreach ($items AS $item) {
            $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':' . str_replace(' ', '-', htmlspecialchars_decode($item->series_text, ENT_QUOTES));
            $seriesimage = $images->getSeriesThumbnail($item->series_thumbnail);
            $item->image = '<img src="' . $seriesimage->path . '" height="' . $seriesimage->height . '" width="' . $seriesimage->width . '">';
            $item->serieslink = JRoute::_('index.php?option=com_biblestudy&view=seriesdisplay&id=' . $item->slug . '&t=' . $t);
            $teacherimage = $images->getTeacherImage($item->thumb, $image2 = null);
            $item->teacherimage = '<img src="' . $teacherimage->path . '" height="' . $teacherimage->height . '" width="' . $teacherimage->width . '">';
        }
        //check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $count = count($items);
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {

                if ($items[$i]->access > 1) {
                    if (!in_array($items[$i]->access, $groups)) {
                        unset($items[$i]);
                    }
                }
            }
        }
        $this->items = $items;
        $total = $this->get('Total');
        $pagination = $this->get('Pagination');
        $this->page->pagelinks = $pagination->getPagesLinks();
        $this->page->counter = $pagination->getPagesCounter();
        $series = $this->get('Series');

        //This is the helper for scripture formatting
        $scripture_call = Jview::loadHelper('scripture');
        //end scripture helper
        $this->assignRef('template', $template);
        $this->assignRef('pagination', $pagination);


        //Get the main study list image
        $mainimage = $images->mainStudyImage();
        $this->page->main = '<img src="' . $mainimage->path . '" height="' . $mainimage->height . '" width="' . $mainimage->width . '">';

        $this->assignRef('main', $main);

        //Build Series List for drop down menu
        $types3[] = JHTML::_('select.option', '0', JText::_('JBS_CMN_SELECT_SERIES'));
        $types3 = array_merge($types3, $series);
        $this->page->series = JHTML::_('select.genericlist', $types3, 'filter_series', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_series");


        $this->assignRef('lists', $lists);

        $this->assignRef('request_url', $uri->toString());
        $this->assignRef('params', $params);
        parent::display($tpl);
    }

}