<?php

/**
 * MessageTypes model
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * MessageType model class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyModelMessagetypes extends JModelList {

    /**
     * Message Type data array
     *
     * @var array
     */
    var $_data;

    /**
     * Allow Deletes
     * @var string
     */
    var $_allow_deletes = null;

    /**
     * Mesaggetype construct
     *
     * @param string $config
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'messagetype.id',
                'published', 'messagetype.published',
                'mesage_type', 'messagetype.message_type,',
                'ordering', 'messagetype.ordering',
                'access', 'messagetype.access',
            );
        }

        parent::__construct($config);
    }

    /**
     * Get Deletes
     * @return object
     */
    public function getDeletes() {
        if (empty($this->_deletes)) {
            $query = 'SELECT allow_deletes'
                    . ' FROM #__bsms_admin'
                    . ' WHERE id = 1';
            $this->_deletes = $this->_getList($query);
        }
        return $this->_deletes;
    }

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     * @since   7.0
     */
    protected function populateState($ordering = null, $direction = null) {
        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        parent::populateState('messagetype.message_type', 'ASC');
    }

    /**
     * Get List Query
     * @since   7.0
     */
    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select', 'messagetype.id, messagetype.published, messagetype.message_type,' .
                        'messagetype.ordering, messagetype.access, messagetype.alias'));

        $query->from('#__bsms_message_type AS messagetype');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = messagetype.access');

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('messagetype.published = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(messagetype.published = 0 OR messagetype.published = 1)');
        }

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));

        return $query;
    }

}