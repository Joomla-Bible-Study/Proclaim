<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();


//Joomla 1.6 <-> 1.5 Branch
try {
    jimport('joomla.application.component.modellist');

    abstract class modelClass extends JModelList {

    }

} catch (Exception $e) {
    jimport('joomla.application.component.model');

    abstract class modelClass extends JModel {

    }

}
class biblestudyModelmimetypelist extends modelClass
{
	/**
	 * mime Type data array
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
		$limit	   = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 0);
		//$limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0 );
		$limitstart = $mainframe->getUserStateFromRequest( 'com_biblestudy&view=mimetypelist.limitstart', 'limitstart', 0, 'int' );

		
		//$this->setState('limit', $limit);
		//$this->setState('limitstart', $limitstart);
	}
	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function _buildQuery()
	{
		$query = ' SELECT * '
			. ' FROM #__bsms_mimetype '
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
		/*$testview 	= JRequest::getVar( 'view' );
			if ($testview != 'mimetypelist') 
				{
					$limitstart = 0;
				}*/
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
    protected function populateState() {
        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
        $this->setState('filter.state', $state);

        parent::populateState('mimetype.mimetext', 'ASC');
    }

    protected function getListQuery() {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                'list.select',
                'mimetype.id, mimetype.mimetype, mimetype.mimetext, mimetype.published'));
        $query->from('`#__bsms_mimetype` AS mimetype');

        //Filter by state
        $state = $this->getState('mimetype.state');
        if (empty($state))
            $query->where('mimetype.published = 0 OR mimetype.published = 1');
        else
            $query->where('mimetype.published = ' . (int) $state);

        return $query;
    }
}
?>