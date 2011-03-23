<?php

/**
 * @version     $Id: locationslist.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.modellist');

    abstract class modelClass extends JModelList {

    }

class biblestudyModellocationslist extends modelClass {

    /**
     * locationslist data array
     *
     * @var array
     */
    var $_data;
    var $_pagination = null;
    var $_total = null;
    var $_allow_deletes = null;

    function __construct() {
        parent::__construct();

        $mainframe = & JFactory::getApplication();
        $option = JRequest::getCmd('option');

        // Get the pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 0);
        $limitstart = $mainframe->getUserStateFromRequest($option . 'limitstart', 'limitstart', 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    /**
     * Retrieves the data
     * @return array Array of objects containing the data from the database
     */
    function getData() {
        // Lets load the data if it doesn't already exist
        if (empty($this->_data)) {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }


        return $this->_data;
    }

    function getTotal() {
        // Lets load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }

    function getPagination() {
        // Lets load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $total = $this->getTotal();
            $limitstart = $this->getState('limitstart');
            $limit = $this->getState('limit');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_pagination;
    }

    /**
     * Returns the query
     * @return string The query to be used to retrieve the rows from the database
     */
    function _buildQuery() {
        $query = ' SELECT * '
                . ' FROM #__bsms_locations '
        ;

        return $query;
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
    protected function populateState() {
        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
        $this->setState('filter.state', $state);

        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
        
        parent::populateState('location.location_text', 'ASC');
    }

    protected function getListQuery() {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                'list.select',
                'location.id, location.published, location.location_text'));
        $query->from('`#__bsms_locations` AS location');

        // Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('location.published = ' . (int) $published);
		}
		else if ($published === '') {
			$query->where('(location.published = 0 OR location.published = 1)');
		}
        
        return $query;
    }

}

?>