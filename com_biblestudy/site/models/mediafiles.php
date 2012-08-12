<?php

/**
 * MediaFiles Model
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Model class for MediaFiles
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyModelmediafiles extends JModelList {

    /**
     * Data
     * @var array
     */
    var $_data;

    /**
     * Total
     * @var array
     */
    var $_total = null;

    /**
     * Pagination
     * @var array
     */
    var $_pagination = null;

    /**
     * Allow Deletion
     * @var array
     */
    var $_allow_deletes = null;

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   11.1
     */
    public function __construct($config = array()) {
        parent::__construct($config);

        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');

        // Get the pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = $mainframe->getUserStateFromRequest('com_biblestudy&view=mediafiles.limitstart', 'limitstart', 0, 'int');
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    /**
     * Build Queary
     * @return string
     */
    public function _buildQuery() {
        $where = $this->_buildContentWhere();
        $orderby = $this->_buildContentOrderBy();
        $query = ' SELECT m.*, s.id AS sid, s.studytitle, md.media_image_name, md.id AS mid'
                . ' FROM #__bsms_mediafiles AS m'
                . ' LEFT JOIN #__bsms_studies AS s ON (s.id = m.study_id)'
                . ' LEFT JOIN #__bsms_media AS md ON (md.id = m.media_image)'
                . $where
                . $orderby;
        return $query;
    }

    /**
     * Retrieves the data
     * @return array Array of objects containing the data from the database
     */
    public function getData() {
        // Lets load the data if it doesn't already exist
        if (empty($this->_data)) {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }
        return $this->_data;
    }

    /**
     * Method to get the total number of items for the data set.
     *
     * @return  integer  The total number of items available in the data set.
     *
     * @since   11.1
     */
    public function getTotal() {
        // Lets load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }

    /**
     * Method to get a JPagination object for the data set.
     *
     * @return  JPagination  A JPagination object for the data set.
     *
     * @since   11.1
     */
    public function getPagination() {
        // Lets load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_pagination;
    }

    /**
     * Build Content Where
     * @return string
     */
    public function _buildContentWhere() {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $where = array();
        $filter_studyid = $mainframe->getUserStateFromRequest($option . 'filter_studyid', 'filter_studyid', 0, 'int');
        if ($filter_studyid > 0) {
            $where[] = 'm.study_id = ' . (int) $filter_studyid;
        }
        $where = ( count($where) ? ' WHERE ' . implode(' AND ', $where) : '' );

        return $where;
    }

    /**
     * Build Content Order By
     * @return string
     */
    public function _buildContentOrderBy() {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $orders = array('id', 'published', 'studytitle', 'ordering', 'media_image_name', 'createdate', 'filename');
        $filter_order = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'ordering', 'cmd');
        $filter_order_Dir = strtoupper($mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'ASC'));
        if ($filter_order_Dir != 'ASC' && $filter_order_Dir != 'DESC') {
            $filter_order_Dir = 'ASC';
        }
        if (!in_array($filter_order, $orders)) {
            $filter_order = 'ordering';
        }

        if ($filter_order == 'ordering') {
            $orderby = ' ORDER BY study_id, ordering ' . $filter_order_Dir;
        } else {
            $orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , study_id, ordering ';
        }
        return $orderby;
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
     *
     * @since   11.1
     */
    protected function populateState($ordering = null, $direction = null) {
        $filename = $this->getUserStateFromRequest($this->context . '.filter.filename', 'filter_filename');
        $this->setState('filter.filename', $filename);

        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
        $this->setState('filter.state', $state);

        $study = $this->getUserStateFromRequest($this->context . '.filter.studytitle', 'filter_studytitle');
        $this->setState('filter.studytitle', $study);

        $mediaTypeId = $this->getUserStateFromRequest($this->context . '.filter.mediatype', 'filter_mediatypeId');
        $this->setState('filter.mediatypeId', $mediaTypeId);

        parent::populateState('mediafile.createdate', 'DESC');
    }

    /**
     * Builds a list of mediatypes (Used for the filter combo box)
     *
     * @return array Array of Objects
     * @since 7.0
     */
    public function getMediatypes() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('media.id AS value, media.media_text AS text');
        $query->from('#__bsms_media AS media');
        $query->join('INNER', '#__bsms_mediafiles AS mediafile ON mediafile.media_image = media.id');
        $query->group('media.id');
        $query->order('media.media_text');

        $db->setQuery($query->__toString());
        return $db->loadObjectList();
    }

    /**
     * Method to get a store id based on the model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  An identifier string to generate the store id.
     *
     * @return  string  A store id.
     *
     * @since   11.1
     */
    protected function getStoreId($id = '') {
        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data
     *
     * @return  JDatabaseQuery
     * @since   7.0
     */
    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select', 'mediafile.id, mediafile.published, mediafile.ordering, mediafile.filename,
                        mediafile.createdate, mediafile.plays, mediafile.downloads'));

        $query->from('`#__bsms_mediafiles` AS mediafile');

        //Join over the studies
        $query->select('study.studytitle AS studytitle');
        $query->join('LEFT', '#__bsms_studies AS study ON study.id = mediafile.study_id');

        //Join over the mediatypes
        $query->select('mediatype.media_text AS mediaType');
        $query->join('LEFT', '`#__bsms_media` AS mediatype ON mediatype.id = mediafile.media_image');

        //Filter by state
        $state = $this->getState('filter.state');
        if (empty($state))
            $query->where('mediafile.published = 0 OR mediafile.published = 1');
        else
            $query->where('mediafile.published = ' . (int) $state);

        //Filter by filename
        $filename = $this->getState('filter.filename');
        if (!empty($filename))
            $query->where('mediafile.filename LIKE "' . $filename . '%"');

        //Filter by study title
        $study = $this->getState('filter.studytitle');
        if (!empty($study))
            $query->where('study.studytitle LIKE "' . $study . '%"');

        //Filter by media type
        $mediaType = $this->getState('filter.mediatypeId');
        if (!empty($mediaType))
            $query->where('mediafile.media_image = ' . (int) $mediaType);

        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));

        return $query;
    }

}