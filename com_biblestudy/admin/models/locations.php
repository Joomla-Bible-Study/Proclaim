<?php

/**
 * @version     $Id: locations.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

abstract class modelClass extends JModelList {

}

class BiblestudyModelLocations extends modelClass {

    /**
     * locations data array
     *
     * @var array
     */
    var $_data;
    var $_pagination = null;
    var $_total = null;
    var $_allow_deletes = null;

    public function __construct() {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'location.id',
                'published', 'location.published',
                'mesage_type', 'location.message_type,',
                'ordering', 'location.ordering',
                'access', 'location.access',
            );
        }

        parent::__construct();
    }

   

    function getDeletes() {
        if (empty($this->_deletes)) {
            $query = 'SELECT allow_deletes'
                    . ' FROM #__bsms_admin'
                    . ' WHERE id = 1';
            $this->_deletes = $this->_getList($query);
        }
        return $this->_deletes;
    }

    /**
     * @since   7.0
     */
    protected function populateState($ordering = null, $direction = null) {
        
        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        parent::populateState('location.location_text', 'ASC');
    }

    protected function getListQuery() {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select', 'location.id, location.published, location.location_text'));
        $query->from('`#__bsms_locations` AS location');

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('location.published = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(location.published = 0 OR location.published = 1)');
        }

        return $query;
    }

}