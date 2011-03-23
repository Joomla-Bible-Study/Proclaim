<?php

/**
 * @version     $Id: sharelist.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.modellist');

    abstract class modelClass extends JModelList {

    }

class biblestudyModelsharelist extends modelClass
{
	/**
	 * teacherlist data array
	 *
	 * @var array
	 */
	var $_data;
	var $_total = null;
	var $_pagination = null;
	var $allow_deletes = null;
	
	function __construct()
	{
		parent::__construct();

		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		//$limitstart	= $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( 'com_biblestudy&view=sharelist.limitstart', 'limitstart', 0, 'int' );


		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}
	

	function _buildQuery()
	{
		$where		= $this->_buildContentWhere();
		$query = ' SELECT * '
		. ' FROM #__bsms_share AS s'
		. $where
		;
		
		return $query;
	}

	/**
	 * Retrieves the data
	 * @return array Array of objects containing the data from the database
	 */
	function getData()
	{
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data ))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit') );
		}
			//$this->setState('limitstart', $limitstart);
		return $this->_data;
	}
function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
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
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}
function _buildContentWhere()
	{
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		$where = array();
	$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		
	return $where;
	}

function getDeletes()
	{
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
    protected function  populateState() {
        $state = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $state);
        
         $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

        parent::populateState('share.name', 'ASC');
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
                        'share.id, share.name, share.params, share.published'));
        $query->from('#__bsms_share AS share');

        // Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('share.published = ' . (int) $published);
		}
		else if ($published === '') {
			$query->where('(share.published = 0 OR share.published = 1)');
		}

        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));
        return $query;
    }
}
?>