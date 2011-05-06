<?php

/**
 * @version     $Id: commentslist.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.modellist');

    abstract class modelClass extends JModelList {

    }

class biblestudyModelcommentslist extends modelClass {

    /**
     *
     * @var array
     */
    var $_data;
    var $_total = null;
    var $_pagination = null;

    function __construct() {
        parent::__construct();

        $mainframe = & JFactory::getApplication();
        $option = JRequest::getCmd('option');

        // Get the pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = $mainframe->getUserStateFromRequest('com_biblestudy&view=commentslist.limitstart', 'limitstart', 0, 'int');
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    function _buildQuery() {
        $where = $this->_buildContentWhere();
        $orderby = $this->_buildContentOrderBy();
        $query = ' SELECT c.*, s.id AS sid, s.studytitle, s.booknumber AS bnumber, b.id AS bid, s.studydate, b.bookname, s.chapter_begin '
                . ' FROM #__bsms_comments AS c'
                . ' LEFT JOIN #__bsms_studies AS s ON (s.id = c.study_id)'
                . ' LEFT JOIN #__bsms_books AS b ON (b.booknumber = s.booknumber)'
                . $where
                . $orderby;
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
        //$filter_order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'ordering',	'cmd' );
        //$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
        $filter_studyid = $mainframe->getUserStateFromRequest($option . 'filter_studyid', 'filter_studyid', 0, 'int');
        if ($filter_studyid > 0) {
            $where[] = 'c.study_id = ' . (int) $filter_studyid;
        }
        $where = ( count($where) ? ' WHERE ' . implode(' AND ', $where) : '' );

        return $where;
    }

    function _buildContentOrderBy() {
        $mainframe = & JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $orders = array('c.id', 'published', 's.studytitle', 'c.comment_date');
        $filter_order = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'published');
        $filter_order_Dir = strtoupper($mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'ASC'));
        if ($filter_order_Dir != 'ASC' && $filter_order_Dir != 'DESC') {
            $filter_order_Dir = 'ASC';
        }
        if (!in_array($filter_order, $orders)) {
            $filter_order = 'published';
        }
        return ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;
    }

    /**
     * @since   7.0
     */
    protected function populateState() {
        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
        $this->setState('filter.state', $state);
        
        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
        
        parent::populateState('comment.comment_date', 'DESC');
    }

    /**
     *
     * @since   7.0
     */
    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select',
                        'comment.*'));
        $query->from('#__bsms_comments AS comment');

        // Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('comment.published = ' . (int) $published);
		}
		else if ($published === '') {
			$query->where('(comment.published = 0 OR comment.published = 1)');
		}

        //Join over Studies
        $query->select('study.studytitle AS studytitle, study.chapter_begin, study.studydate');
        $query->join('LEFT', '#__bsms_studies AS study ON study.id = comment.study_id');

        //Join over books
        $query->select('book.bookname as bookname');
        $query->join('LEFT', '#__bsms_books as book ON book.booknumber = study.booknumber');


        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));
        return $query;
    }

}

?>