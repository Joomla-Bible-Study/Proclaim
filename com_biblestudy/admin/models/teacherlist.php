<?php

/**
 * @version     $Id: teacherlist.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.modellist');

    abstract class modelClass extends JModelList {

    }

class biblestudyModelteacherlist extends modelClass {

    /**
     * teacherlist data array
     *
     * @var array
     */
    var $_data;
    var $_total = null;
    var $_pagination = null;
    var $allow_deletes = null;

    function __construct() {
        parent::__construct();

        $mainframe = & JFactory::getApplication();
        $option = JRequest::getCmd('option');

        // Get the pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        //$limitstart	= $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
        $limitstart = $mainframe->getUserStateFromRequest('com_biblestudy&view=teacherlist.limitstart', 'limitstart', 0, 'int');


        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    function _buildQuery() {
        $where = $this->_buildContentWhere();
        $orderby = $this->_buildContentOrderBy();
        $query = ' SELECT * '
                . ' FROM #__bsms_teachers AS t'
                . $where
                . $orderby
        ;

        return $query;
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
        //$this->setState('limitstart', $limitstart);
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

    /**
     * Method to get a pagination object for the studies
     *
     * @access public
     * @return integer
     */
    function getPagination() {
        // Lets load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_pagination;
    }

    function _buildContentWhere() {
        $mainframe = & JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $where = array();
        $where = ( count($where) ? ' WHERE ' . implode(' AND ', $where) : '' );

        return $where;
    }

    function _buildContentOrderBy() {
        $mainframe = & JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $orders = array('t.catid', 'ordering');
        $filter_order = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'ordering', 'cmd');
        $filter_order_Dir = strtoupper($mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'ASC'));
        if ($filter_order_Dir != 'ASC' && $filter_order_Dir != 'DESC') {
            $filter_order_Dir = 'ASC';
        }
        if (!in_array($filter_order, $orders)) {
            $filter_order = 'ordering';
        }
        if ($filter_order == 'ordering') {
            $orderby = ' ORDER BY t.catid, ordering ' . $filter_order_Dir;
        } else {
            $orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , t.catid, ordering ';
            //$orderby 	= ' ORDER BY t.catid, ordering ';
        }
        return $orderby;
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

    /*
     * @since   7.0
     */

    protected function populateState() {
        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
        $this->setState('filter.state', $state);
        
        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
        
        parent::populateState('teacher.teachername', 'ASC');
    }

    /*
     * @since   7.0
     */

    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select',
                        'teacher.id, teacher.published, teacher.ordering, teacher.teachername'));
        $query->from('#__bsms_teachers AS teacher');

        // Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('teacher.published = ' . (int) $published);
		}
		else if ($published === '') {
			$query->where('(teacher.published = 0 OR teacher.published = 1)');
		}

        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));
        return $query;
    }

}

?>