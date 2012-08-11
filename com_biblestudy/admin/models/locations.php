<?php

/**
 * Locations Model
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Locations model class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyModelLocations extends JModelList {

    /**
     * locations data array
     *
     * @var array
     */
    var $_data;

    /**
     * Pagination
     * @var array
     */
    var $_pagination = null;

    /**
     * Total
     * @var array
     */
    var $_total = null;

    /**
     * Allow Deletes
     * @var string
     */
    var $_allow_deletes = null;

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct($config = array()) {
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

    /**
     * Get Deletes
     * @return type
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

        parent::populateState('location.location_text', 'ASC');
    }

    /**
     * Get List Query
     * @return type
     */
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