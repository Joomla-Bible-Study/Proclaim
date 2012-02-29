<?php

/**
 * @version     $Id: view.html.php 1330 2011-01-06 08:01:38Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die;

require_once (JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.defines.php');
jimport('joomla.application.component.view');
$uri = JFactory::getURI();
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
include_once (JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'translated.php');

class biblestudyViewseriesdetail extends JView {

    protected $state = null;
    protected $item = null;
    protected $items = null;
    protected $pagination = null;

    /**
     * Display the view
     *
     * @return	mixed	False on error, null otherwise.
     */
    function display($tpl = null) {
        //TF added
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
        $css = $params->get('css','biblestudy.css');
        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/'.$css);
        $items = $this->get('Item');
        $items->slug = $items->alias ? ($items->id . ':' . $items->alias) : str_replace(' ', '-', htmlspecialchars_decode($items->series_text, ENT_QUOTES)) . ':' . $items->id;
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
        //Get studies from this series
        $seriesorder = $params->get('series_detail_order', 'DESC');

        $limit = ' LIMIT ' . $params->get('series_detail_limit', 10);
        $db = JFactory::getDBO();
        $query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername,'
                . ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_series.description AS sdescription,'
                . ' #__bsms_message_type.id AS mid,'
                . ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
                . ' #__bsms_locations.id AS lid, #__bsms_locations.location_text,'
                . ' group_concat(#__bsms_topics.id separator ", ") AS tp_id, group_concat(#__bsms_topics.topic_text separator ", ") as topic_text'
                . ' FROM #__bsms_studies'
                . ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
                . ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
                . ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
                . ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
                . ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studies.topics_id)'
                . ' LEFT JOIN #__bsms_locations ON (#__bsms_studies.location_id = #__bsms_locations.id)'
                . ' WHERE #__bsms_studies.series_id = ' . $items->id . ' GROUP BY #__bsms_studies.id ORDER BY #__bsms_studies.studydate ' . $seriesorder
                . $limit;

        $db->setQuery($query);
        $results = $db->loadObjectList();

        foreach ($results AS $item) {
            $topic_text = getTopicItemTranslated($item);
            $item->topic_text = $topic_text;
        }

        $items2 = $results;
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

        if ($this->getLayout() == 'pagebreak') {
            $this->_displayPagebreak($tpl);
            return;
        }
        $print = JRequest::getBool('print');
        // build the html select list for ordering
        // Process the prepare content plugins
        $limitstart = 0;
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
        $this->assignRef('print', $print);
        $this->assignRef('params', $params);
        $this->assignRef('items', $items);
        $this->assignRef('article', $article);
        $this->assignRef('passage_link', $passage_link);
        $this->assignRef('studies', $studies);
        $uri = JFactory::getURI();
        $this->assignRef('request_url', $uri->toString());
        parent::display($tpl);
    }

}