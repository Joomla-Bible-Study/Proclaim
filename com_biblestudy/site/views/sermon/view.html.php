<?php

/**
 * Sermon JView
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;


require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.pagebuilder.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'podcastsubscribe.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'related.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblegateway.php');
$uri = JFactory::getURI();

/**
 * View class for Sermon
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class BiblestudyViewSermon extends JViewLegacy {

    protected $item;
    protected $params;
    protected $print;
    protected $state;
    protected $user;

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
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $userId = $user->get('id');

        $study = $this->get('Item');
        $this->item = $study;
        $this->print = $app->input->getBool('print');
        $this->state = $this->get('State');
        $this->user = $user;
        $relatedstudies = new relatedStudies();

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseWarning(500, implode("\n", $errors));

            return false;
        }

        $template = $this->get('template');

        $registry = new JRegistry();
        $registry->loadJSON($template[0]->params);
        $params = $registry;
        $a_params = $this->get('Admin');
        $this->related = $relatedstudies->getRelated($study, $params);
        // Convert parameter fields to objects.
        $registry = new JRegistry();
        $registry->loadJSON($a_params[0]->params);
        $this->admin_params = $registry;
        //@todo need to move to module bad way to code this.
        // Convert item paremeters into objects
        $registry = new JRegistry;
        $registry->loadJSON($study->params);
        $this->item->params = $registry;
        $adminrows = new JBSAdmin();
        $document = JFactory::getDocument();
        $document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
        $document->addScript('http://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/jquery.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/noconflict.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
        $css = $params->get('css');
        if ($css <= "-1"):
            $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
        else:
            $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
        endif;
        $pathway = $app->getPathWay();
        $contentConfig = JComponentHelper::getParams('com_biblestudy');
        $dispatcher = JDispatcher::getInstance();

        //Adjust the slug if there is no alias in the row
        //Set the slug
        $study->slug = $study->alias ? ($study->id . ':' . $study->alias) : str_replace(' ', '-', htmlspecialchars_decode($study->studytitle, ENT_QUOTES)) . ':' . $study->id;
        $pagebuilder = new JBSPagebuilder();
        $pelements = $pagebuilder->buildPage($study, $params, $this->admin_params);
        $study->scripture1 = $pelements->scripture1;
        $study->scripture2 = $pelements->scripture2;
        $study->media = $pelements->media;
        $study->duration = $pelements->duration;
        $study->studydate = $pelements->studydate;
        if (isset($pelements->topics)):
            $study->topics = $pelements->topics;
        else:
            $study->topics = '';
        endif;
        if (isset($pelements->study_thumbnail)):
            $study->study_thumbnail = $pelements->study_thumbnail;
        else:
            $study->study_thumbnail = null;
        endif;
        if (isset($pelements->series_thumbnail)):
            $study->series_thumbnail = $pelements->series_thumbnail;
        else:
            $study->series_thumbnail = null;
        endif;
        $study->detailslink = $pelements->detailslink;
        if (isset($pelements->teacherimage)):
            $study->teacherimage = $pelements->teacherimage;
        else:
            $study->teacherimage = null;
        endif;
        $article = new stdClass();
        $article->text = $study->scripture1;
        $results = $dispatcher->trigger('onContentPrepare', array('com_biblestudy.sermons', & $article, & $params, $limitstart = null));
        $study->scripture1 = $article->text;
        $article->text = $study->scripture2;
        $results = $dispatcher->trigger('onContentPrepare', array('com_biblestudy.sermons', & $article, & $params, $limitstart = null));
        $study->scripture2 = $article->text;
        $article->text = $study->studyintro;
        $results = $dispatcher->trigger('onContentPrepare', array('com_biblestudy.sermons', & $article, & $params, $limitstart = null));
        $study->studyintro = $article->text;
        $article->text = $study->secondary_reference;
        $results = $dispatcher->trigger('onContentPrepare', array('com_biblestudy.sermons', & $article, & $params, $limitstart = null));
        $study->secondary_reference = $article->text;
        $this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers');
        $this->loadHelper('params');

        //get the podcast subscription
        $podcast = new podcastSubscribe();
        $this->subscribe = $podcast->buildSubscribeTable($params->get('subscribeintro', 'Our Podcasts'));
        //check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();

        //   $count = count($items);
        if ($study->access > 1) {
            if (!in_array($study->access, $groups)) {
                return JError::raiseError('403', JText::_('JBS_CMN_ACCESS_FORBIDDEN'));
            }
        }

        //Passage link to BibleGateway
        $plugin = JPluginHelper::getPlugin('content', 'scripturelinks');
        if ($plugin) {
            $plugin = JPluginHelper::getPlugin('content', 'scripturelinks');
            // Convert parameter fields to objects.
            $registry = new JRegistry;
            $registry->loadJSON($plugin->params);
            $st_params = $registry;
            $version = $st_params->get('bible_version');
            $windowopen = "window.open(this.href,this.target,'width=800,height=500,scrollbars=1');return false;";
        }

        //Added database queries from the default template - moved here instead
        $database = JFactory::getDBO();
        $query = "SELECT id"
                . "\nFROM #__menu"
                . "\nWHERE link ='index.php?option=com_biblestudy&view=sermons' and published = 1";
        $database->setQuery($query);
        $menuid = $database->loadResult();
        $this->assignRef('menuid', $menuid);


        if ($this->getLayout() == 'pagebreak') {
            $this->_displayPagebreak($tpl);
            return;
        }
        $print = JRequest::getBool('print');
        // build the html select list for ordering

        /*
         * Process the prepare content plugins
         */
        $article->text = $study->studytext;
        $linkit = $params->get('show_scripture_link');
        if ($linkit) {
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
            $limitstart = JRequest::getVar('limitstart', 'int');
            $results = $dispatcher->trigger('onContentPrepare', array('com_biblestudy.sermon', & $article, & $params, $limitstart));
            $article->studytext = $article->text;
            $study->studytext = $article->text;
        } //end if $linkit
        $Biblepassage = new showScripture();
        $this->passage = $Biblepassage->buildPassage($study, $params);

        //Prepares a link string for use in social networking
        $u = JURI::getInstance();
        $detailslink = htmlspecialchars($u->toString());
        $detailslink = JRoute::_($detailslink);
        $this->assignRef('detailslink', $detailslink);
        JView::loadHelper('share');
        $this->page = new stdClass();
        $this->page->social = getShare($detailslink, $study, $params, $this->admin_params);
        // XXX not sure why we are adding the helper path???
        JHtml::addIncludePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers');
        $this->page->print = JHtml::_('icon.print_popup', $params);
        //End social networking
        // End process prepare content plugins
        $this->assignRef('template', $template);
        $this->assignRef('print', $print);
        $this->assignRef('params', $params);
        $this->assignRef('study', $study);
        $this->assignRef('article', $article);
        $this->assignRef('passage_link', $passage_link);

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Display PageBrack
     * @param string $tpl
     */
    function _displayPagebreak($tpl) {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('JBS_CMN_READ_MORE'));
        parent::display($tpl);
    }

    /**
     * Prepares the document
     */
    protected function _prepareDocument() {
        $app = JFactory::getApplication();
        $menus = $app->getMenu();
        $pathway = $app->getPathway();
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::_('JBS_CMN_SERMON'));
        }

        $title = $this->params->get('page_title', '');

        $id = (int) @$menu->query['id'];

        // if the menu item does not concern this article
        if ($menu && ($menu->query['option'] != 'com_biblestudy' || $menu->query['view'] != 'sermon' || $id != $this->item->id)) {
            // If this is not a single article menu item, set the page title to the article title
            if ($this->item->studytitle) {
                $title = $this->item->studytitle;
            }
            $path = array(array('title' => $this->item->studytitle, 'link' => ''));
            $path = array_reverse($path);
            foreach ($path as $item) {
                $pathway->addItem($item['title'], $item['link']);
            }
        }

        // Check for empty title and add site name if param is set
        if (empty($title)) {
            $title = $app->getCfg('sitename');
        } elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        } elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title . ':' . $this->item->studytitle, $app->getCfg('sitename'));
        }
        if (empty($title)) {
            $title = $this->item->studytitle;
        }
        $this->document->setTitle($title);


        //Prepare meta information (under development)
        if ($this->item->params->get('metakey')) {
            $this->document->setMetadata('keywords', $this->item->params->get('metakey'));
        } elseif (!$this->item->params->get('metakey') && $this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->item->params->get('metadesc')) {
            $this->document->setDescription($this->item->params->get('metadesc'));
        } elseif (!$this->item->params->get('metadesc') && $this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

        //Not ready for j2.5 will work in latter.
//        $mdata = $this->item->params->get('metadesc')->toArray();
//        foreach ($mdata as $k => $v) {
//            if ($v) {
//                $this->document->setMetadata($k, $v);
//            }
//        }

        // If there is a pagebreak heading or title, add it to the page title
        if (!empty($this->item->page_title)) {
            $this->item->studytitle = $this->item->studytitle . ' - ' . $this->item->page_title;
            $this->document->setTitle($this->item->page_title . ' - ' . JText::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $this->state->get('list.offset') + 1));
        }

        if ($this->print) {
            $this->document->setMetaData('robots', 'noindex, nofollow');
        }
    }

}