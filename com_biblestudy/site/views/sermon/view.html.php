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

jimport('joomla.application.component.view');
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
class BiblestudyViewSermon extends JView {

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
        $study = $this->get('Item');
        $relatedstudies = new relatedStudies();

        $app = JFactory::getApplication();
        $menu = $app->getMenu();
        $item = $menu->getActive();


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
        $itemparams = $registry;
        $adminrows = new JBSAdmin();
        $document = JFactory::getDocument();
        $document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
        $document->addScript('http://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/jquery.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/noconflict.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
        $url = $params->get('css', 'biblestudy.css');
        if ($url) {
            $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $url);
        }
        $pathway = $mainframe->getPathWay();
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
        $study->topics = $pelements->topics;
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

        //Prepare meta information (under development)
        if ($itemparams->get('metakey')) {
            $document->setMetadata('keywords', $itemparams->get('metakey'));
        } elseif (!$itemparams->get('metakey')) {
            $document->setMetadata('keywords', $study->topic_text . ',' . $study->studytitle);
        }

        if ($itemparams->get('metadesc')) {
            $document->setDescription($itemparams->get('metadesc'));
        } elseif (!$itemparams->get('metadesc')) {
            $document->setDescription($study->studyintro);
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
        $share = JView::loadHelper('share');
        $this->page = new stdClass();
        $this->page->social = getShare($detailslink, $study, $params, $this->admin_params);
        JHtml::addIncludePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers');
        JHTML::_('behavior.tooltip');
        $this->page->print = JHtml::_('icon.print_popup', $params);
        //End social networking
        // End process prepare content plugins
        $this->assignRef('template', $template);
        $this->assignRef('print', $print);
        $this->assignRef('params', $params);
        $this->assignRef('study', $study);
        $this->assignRef('article', $article);
        $this->assignRef('passage_link', $passage_link);

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

}