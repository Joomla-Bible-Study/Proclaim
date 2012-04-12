<?php

/**
 * @version     $Id: podcasts.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

abstract class modelClass extends JModelList {

}

class BiblestudyModelPodcasts extends modelClass {

    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'podcast.id',
                'published', 'podcast.published',
                'ordering', 'podcast.ordering',
                'language', 'podcast.language'
            );
        }

        parent::__construct($config);
    }

    /**
     *
     * @param <string> $id   A prefix for the store id
     * @return <string>      A store id
     * @since 7.0
     */
    protected function getStoreId($id = '') {

        // Compile the store id.
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /*
     * @since   7.0
     */

    protected function populateState($ordering = null, $direction = null) {
        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);
        parent::populateState('podcast.title', 'ASC');
    }

    /*
     * @since   7.0
     */

    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select', 'podcast.id, podcast.published, podcast.title, podcast.description, podcast.language'));
        $query->from('#__bsms_podcast AS podcast');

        // Join over the language
        $query->select('l.title AS language_title');
        $query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = podcast.language');

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('podcast.published = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(podcast.published = 0 OR podcast.published = 1)');
        }

        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));
        return $query;
    }

}