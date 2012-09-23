<?php

/**
 * SeriesDisplay JView
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.pagebuilder.class.php');
jimport('joomla.application.component.view');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
include_once (JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'translated.php');

/**
 * View class for SeriesDisplay
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class BiblestudyViewSeriesdisplay extends JView {

    /**
     * State
     * @var array
     */
    protected $state = null;

    /**
     * Item
     * @var array
     */
    protected $item = null;

    /**
     * Items
     * @var array
     */
    protected $items = null;

    /**
     * Pagination
     * @var array
     */
    protected $pagination = null;

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
        $option = JRequest::getCmd('option');
        $document = JFactory::getDocument();
        $document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
        $pathway = $mainframe->getPathWay();
        $contentConfig = JComponentHelper::getParams('com_biblestudy');
        $dispatcher = JDispatcher::getInstance();
        // Get the menu item object
        //Load the Admin settings and params from the template
        $this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers');
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin(true);
        $items = $this->get('Item');
        //Get the series image
        $images = new jbsImages();
        $image = $images->getSeriesThumbnail($items->series_thumbnail);
        $items->image = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . '">';
        $teacherimage = $images->getTeacherThumbnail($items->thumb, $image2 = null);
        $items->teacherimage = '<img src="' . $teacherimage->path . '" height="' . $teacherimage->height . '" width="' . $teacherimage->width . '">';
        $t = JRequest::getInt('t', 'get', 1);
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
        $css = $params->get('css');
        if (!$css):
            $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
        else:
            $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
        endif;

        $items->slug = $items->alias ? ($items->id . ':' . $items->alias) : str_replace(' ', '-', htmlspecialchars_decode($items->series_text, ENT_QUOTES)) . ':' . $items->id;
        $itemparams = $mainframe->getPageParameters();

        //get studies associated with the series
        $pagebuilder = new JBSPagebuilder();
        $whereitem = $items->id;
        $wherefield = 'study.series_id';
        //  $wherefield = 'study.teacher_id';
        $limit = $params->get('series_detail_limit', 10);
        $seriesorder = $params->get('series_detail_order', 'DESC');
        $results = $pagebuilder->studyBuilder($whereitem, $wherefield, $params, $this->admin_params, $limit, $seriesorder);
        foreach ($results AS $study) {
            $topic_text = getTopicItemTranslated($study);
            $study->topic_text = $topic_text;
        }
        $this->seriesstudies = $results;
        $this->page = $items;
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

        //check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        if (!in_array($items->access, $groups) && $items->access) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

        $studies = $items;

        JRequest::setVar('returnid', $items->id, 'get', true);
        //Passage link to BibleGateway
        $plugin = JPluginHelper::getPlugin('content', 'scripturelinks');
        if ($plugin) {
            // Convert parameter fields to objects.
            $registry = new JRegistry;
            $registry->loadJSON($plugin->params);
            $st_params = $registry;
            $version = $st_params->get('bible_version');
        }
        $windowopen = "window.open(this.href,this.target,'width=800,height=500,scrollbars=1');return false;";


        $limitstart = 0;
        $article = new stdClass();
        $article->text = $items->description;
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
            $results = $dispatcher->trigger('onPrepareContent', array(& $article, & $params, $limitstart));
            $items->description = $article->text;
        } //end if $linkit
        // End process prepare content plugins
        $this->assignRef('template', $template);
        $this->assignRef('params', $params);
        $this->assignRef('items', $items);
        $this->assignRef('article', $article);
        $this->assignRef('passage_link', $passage_link);
        $this->assignRef('studies', $studies);
        $uri = JFactory::getURI();
        $stringuri = $uri->toString();
        $this->assignRef('request_url', $stringuri);
        parent::display($tpl);
    }

}