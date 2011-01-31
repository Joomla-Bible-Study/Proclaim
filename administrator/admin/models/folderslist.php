<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.modellist');

    abstract class modelClass extends JModelList {

    }

class biblestudyModelfolderslist extends modelClass {

    /**
     *
     * @var array
     */
    var $_data;
    var $_allow_deletes = null;

    /**
     * Returns the query
     * @return string The query to be used to retrieve the rows from the database
     */
    function _buildQuery() {
        $query = ' SELECT * '
                . ' FROM #__bsms_folders '
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
            $this->_data = $this->_getList($query);
        }

        return $this->_data;
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
    protected function  populateState() {
        $state = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $state);

        parent::populateState('folder.foldername', 'DESC');
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
                        'folder.id, folder.foldername, folder.folderpath, folder.published'));
        $query->from('#__bsms_folders AS folder');

        //Filter by state
        $state = $this->getState('filter.state');
        if(empty($state))
            $query->where('folder.published = 0 OR folder.published = 1');
        else
            $query->where('folder.published = ' . (int) $state);

        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));
        return $query;
    }

}

?>