<?php

/**
 * Topics Model
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Topics model class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyModelTopics extends JModelList {

    /**
     * Constructer
     * @param string $config
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'topic.id',
                'published', 'topic.published',
                'topic_text', 'topic.topic_text',
                'params', 'topic.params',
            );
        }

        parent::__construct($config);
    }

    /**
     * Populate State
     * @param string $ordering
     * @param string $direction
     *
     * @since   7.0
     */
    protected function populateState($ordering = null, $direction = null) {
        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        parent::populateState('topic.topic_text', 'ASC');
    }

    /**
     * Get Stored ID
     * @param string $id   A prefix for the store id
     * @return string      A store id
     * @since 7.0
     */
    protected function getStoreId($id = '') {

        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');

        return parent::getStoreId($id);
    }

    /**
     * Get List Query
     * @since   7.0
     * @return array
     */
    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select', 'topic.id, topic.topic_text, topic.published, topic.params AS topic_params'));
        $query->from('#__bsms_topics AS topic');


        // Filter by search in title.
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('topic.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('(topic.topic_text LIKE ' . $search . ')');
            }
        }
        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('topic.published = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(topic.published = 0 OR topic.published = 1)');
        }

        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));
        return $query;
    }

}