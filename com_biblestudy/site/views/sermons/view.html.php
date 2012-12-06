<?php

/**
 * Sermons JViewLegacy
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.stats.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.pagebuilder.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'podcastsubscribe.php');

/**
 * View for Sermons class
 *
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class BiblestudyViewSermons extends JViewLegacy {

    /**
     * Items
     * @var array
     */
    protected $items;

    /**
     * Pagination
     * @var array
     */
    protected $pagination;

    /**
     * State
     * @var array
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
        $input = new JInput;
        $limitstart = $input->get('limitstart','','int');
        $input->set('start', $limitstart);
        $state = $this->get('State');
        $this->assignRef('state', $state);
        $document = JFactory::getDocument();

        $items = $this->get('Items');
        $this->limitstart = $input->get('start','' ,'int');
        $pagination = $this->get('Pagination');
        $pagelinks = $pagination->getPagesLinks();
        if ($pagelinks !== ''):
            $this->pagelinks = $pagelinks;
        endif;
        $this->limitbox = '<span class="display-limit">' . JText::_('JGLOBAL_DISPLAY_NUM') . $pagination->getLimitBox() . '</span>';
        $this->assignRef('pagination', $pagination);
        //Load the Admin settings and params from the template
        JViewLegacy::loadHelper('params');
        $this->admin = BsmHelper::getAdmin(true);

        $admin_parameters = $this->get('Admin');
        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadString($admin_parameters->params);
        $this->admin_params = $registry;

        //check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $count = count($items);

        for ($i = 0; $i < $count; $i++) {

            if ($items[$i]->access > 1) {
                if (!in_array($items[$i]->access, $groups)) {
                    unset($items[$i]);
                }
            }
        }
        $template = $this->get('template');
        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadString($template->params);
        $params = $registry;

        $a_params = $this->get('Admin');
        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadString($a_params->params);
        $this->admin_params = $registry;
        foreach ($items AS $item) {
            $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':' . str_replace(' ', '-', htmlspecialchars_decode($item->studytitle, ENT_QUOTES));
        }
        //Build the elements so they can be accessed through the $this->page array in the template
        $dispatcher = JDispatcher::getInstance();
        $linkit = $params->get('show_scripture_link', '0');
        switch ($linkit) {
            case 0:
                break;
            case 1:
                JPluginHelper::importPlugin('content');
                break;
            case 2:
                JPluginHelper::importPlugin('content', 'scripturelinks');
                break;
        }
        $limitstart = $input->get('limitstart','', 'int');


        $studies = $items;
        $pagebuilder = new JBSPagebuilder();
        foreach ($studies as $i => $study) {
            $article = new stdClass();
            $pelements = $pagebuilder->buildPage($study, $params, $this->admin_params);
            $studies[$i]->scripture1 = $pelements->scripture1;
            $studies[$i]->scripture2 = $pelements->scripture2;
            $article->text = $studies[$i]->scripture1;
            $results = $dispatcher->trigger('onContentPrepare', array('com_biblestudy.sermons', & $article, & $params, $limitstart));
            $studies[$i]->scripture1 = $article->text;
            $article->text = $studies[$i]->scripture2;
            $results = $dispatcher->trigger('onContentPrepare', array('com_biblestudy.sermons', & $article, & $params, $limitstart));
            $studies[$i]->scripture2 = $article->text;
            $studies[$i]->media = $pelements->media;
            $studies[$i]->duration = $pelements->duration;
            $studies[$i]->studydate = $pelements->studydate;
            $studies[$i]->topics = $pelements->topics;
            if (isset($pelements->study_thumbnail)):
                $studies[$i]->study_thumbnail = $pelements->study_thumbnail;
            else:
                $studies[$i]->study_thumbnail = null;
            endif;
            if (isset($pelements->series_thumbnail)):
                $studies[$i]->series_thumbnail = $pelements->series_thumbnail;
            else:
                $studies[$i]->series_thumbnail = null;
            endif;
            $studies[$i]->detailslink = $pelements->detailslink;
            $article->text = $studies[$i]->studyintro;
            $results = $dispatcher->trigger('onContentPrepare', array('com_biblestudy.sermons', & $article, & $params, $limitstart));
            $studies[$i]->studyintro = $article->text;
            $article->text = $studies[$i]->secondary_reference;
            $results = $dispatcher->trigger('onContentPrepare', array('com_biblestudy.sermons', & $article, & $params, $limitstart));
            $studies[$i]->secondary_reference = $article->text;
        }
        $this->study = $studies;
        $this->items = $items;
        //get the podcast subscription
        $podcast = new podcastSubscribe();
        $this->subscribe = $podcast->buildSubscribeTable($params->get('subscribeintro', 'Our Podcasts'));
        $mainframe = JFactory::getApplication();
        $option = $input->get('option','','cmd');
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

        JViewLegacy::loadHelper('image');

        if (BIBLESTUDY_CHECKREL)
                    {JHtml::_('behavior.framework');}
                    else {JHTML::_('behavior.mootools');}
        $css = $params->get('css');
        if ($css <= "-1" ):
            $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
        else:
            $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
        endif;
        $document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
        //Errors when using local swfobject.js file.  IE 6 doesn't work
        //Import Scripts
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/jquery.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/noconflict.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/views/studieslist.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/tooltip.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
        //Styles from tooltip.css moved to css/biblestudy.css
        //Import Stylesheets
        $document->addStylesheet(JURI::base() . 'media/com_biblestudy/css/general.css');
        $uri = JFactory::getURI();

        $filter_topic = $this->state->get('filter.topic');
        $filter_book = $this->state->get('filter.book');
        $filter_teacher = $this->state->get('filter.teacher');
        $filter_series = $this->state->get('filter.series');
        $filter_messagetype = $this->state->get('filter.messageType');
        $filter_year = $this->state->get('filter.year');
        $filter_location = $this->state->get('filter.location');
        $filter_orders = $this->state->get('filter.orders');
        $filter_languages = $this->state->get('filter.languages');
        $total = $this->get('Total');
        //Remove the studies the user is not allowed to see

        $this->teachers = $this->get('Teachers');
        $this->series = $this->get('Series');
        $this->messageTypes = $this->get('MessageTypes');
        $this->years = $this->get('Years');
        $this->locations = $this->get('Locations');
        $this->topics = $this->get('Topics');
        $this->orders = $this->get('Orders');
        $this->books = $this->get('Books');

        //This is the helper for scripture formatting
        $scripture_call = JViewLegacy::loadHelper('scripture');
        //end scripture helper
        //Get the data for the drop down boxes
        $this->assignRef('template', $template);
        $this->assignRef('pagination', $pagination);
        $this->assignRef('order', $this->orders);
        $this->assignRef('topic', $this->topics);
        $app = JFactory::getApplication();
        $menu = $app->getMenu();
        $item = $menu->getActive();
        $images = new jbsImages();
        $main = $images->mainStudyImage();
        $this->assignRef('main', $main);

        //Get the Popular stats
        $stats = new jbStats();
        $this->page = new stdClass();
        $this->page->popular = $stats->top_score_site();

        //Get whether "Go" Button is used then turn off onchange if it is
        if ($params->get('use_go_button', 0) == 0) {
            $go = 'onchange="this.form.submit()"';
        } else {
            $go = null;
        }

        //Build go button
        $this->page->gobutton = '<span id="gobutton"><input type="submit" value="' . JText::_('JBS_STY_GO_BUTTON') . '" /></span>';

        //Build language drop down
        $used = JLanguageHelper::getLanguages();
        $langtemp = array();
        $lang = array();
        foreach ($used as $use) {
            $langtemp = array('text' => $use->title_native, 'value' => $use->lang_code);
            $lang[] = $langtemp;
        }
        $langdropdown[] = JHTML::_('select.option', '0', JTEXT::_('JBS_SELECT_LANGUAGE'));
        $langdropdown = array_merge($langdropdown, $lang);
        $this->page->languages = JHTML::_('select.genericlist', $langdropdown, 'filter_languages', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_languages");

        //Build the teacher dropdown
        $types[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_TEACHER'));
        $types = array_merge($types, $this->teachers);
        $this->page->teachers = JHTML::_('select.genericlist', $types, 'filter_teacher', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_teacher");

        //Build Series List for drop down menu
        $types3[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_SERIES'));
        $types3 = array_merge($types3, $this->series);
        $this->page->series = JHTML::_('select.genericlist', $types3, 'filter_series', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_series");

        //Build message types
        $types4[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_MESSAGE_TYPE'));
        $types4 = array_merge($types4, $this->messageTypes);
        $this->page->messagetypes = JHTML::_('select.genericlist', $types4, 'filter_messagetype', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_messagetype");

        //build study years
        $years[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_YEAR'));
        $years = array_merge($years, $this->years);
        $this->page->years = JHTML::_('select.genericlist', $years, 'filter_year', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_year");

        //build locations
        $loc[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_LOCATION'));
        $loc = array_merge($loc, $this->locations);
        $this->page->locations = JHTML::_('select.genericlist', $loc, 'filter_location', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_location");

        //Build Topics
        $top[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_TOPIC'));
        $top = array_merge($top, $this->topics);
        $this->page->topics = JHTML::_('select.genericlist', $top, 'filter_topic', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_topic");

        //Build Books
        $boo[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_BOOK'));
        $boo = array_merge($boo, $this->books);
        $this->page->books = JHTML::_('select.genericlist', $boo, 'filter_book', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_book");

        //Build order
        $ordervalues = array(
            array('value' => "DESC", 'text' => JText::_("JBS_CMN_DESCENDING")),
            array('value' => "ASC", 'text' => JText::_("JBS_CMN_ASCENDING"))
        );
        $ord[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_ORDER'));
        $ord = array_merge($ord, $ordervalues);
        $this->page->order = JHTML::_('select.genericlist', $ord, 'filter_orders', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_orders");

        $this->assignRef('lists', $lists);
        $this->assignRef('items', $items);
        $stringuri = $uri->toString();
        $this->assignRef('request_url', $stringuri);
        $this->assignRef('params', $params);
        parent::display($tpl);
    }

}