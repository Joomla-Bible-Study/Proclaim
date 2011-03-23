<?php

/**
 * @version     $Id: medialist.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.modellist');

    abstract class modelClass extends JModelList {

    }

class biblestudyModelmedialist extends modelClass {

    /**
     *
     * @var array
     */
    var $_data;
    var $_admin;

    function getAdmin() {
        if (empty($this->_admin)) {
            $query = 'SELECT params'
                    . ' FROM #__bsms_admin'
                    . ' WHERE id = 1';
            $this->_admin = $this->_getList($query);
        }
        return $this->_admin;
    }

    /**
     * Returns the query
     * @return string The query to be used to retrieve the rows from the database
     */
    function _buildQuery() {
        $query = ' SELECT * '
                . ' FROM #__bsms_media '
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

    /*
     * @since   7.0
     */

    protected function populateState() {
        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
        $this->setState('filter.state', $state);
        
        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
        
        parent::populateState('media.media_image_name', 'ASC');
    }

    /**
     * Build and SQL query to load the list data
     * @return  JDatabaseQuery
     * @since   7.0
     */
    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select',
                        'media.id, media.published, media.media_image_name, media.path2, media.media_image_path'));
        $query->from('#__bsms_media AS media');

        // Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('media.published = ' . (int) $published);
		}
		else if ($published === '') {
			$query->where('(media.published = 0 OR media.published = 1)');
		}

        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));
        return $query;
    }

}

?>