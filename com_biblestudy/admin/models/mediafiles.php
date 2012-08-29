<?php

/**
 * MediaFiles model
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * MediaFiles model class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyModelMediafiles extends JModelList {

    /**
     * Constructer
     * @param string $config
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'mediafile.id',
                'published', 'mediafile.published',
                'ordering', 'mediafile.ordering',
                'filenam', 'mediafile.filename',
                'studytitle', 'study.studytitle',
                'media_text', 'mediatype.media_text',
                'createdate', 'mediafile.createdate',
                'plays', 'mediafile.plays',
                'downloads', 'mediafile.downloads',
                'language', 'mediafile.language'
            );
        }

        parent::__construct($config);
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
     * @since   7.0
     */
    protected function populateState($ordering = null, $direction = null) {

        // Initialise variables.
        $app = JFactory::getApplication();
        $session = JFactory::getSession();

        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }

        $filename = $this->getUserStateFromRequest($this->context . '.filter.filename', 'filter_filename');
        $this->setState('filter.filename', $filename);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $study = $this->getUserStateFromRequest($this->context . '.filter.studytitle', 'filter_studytitle');
        $this->setState('filter.studytitle', $study);

        $mediaTypeId = $this->getUserStateFromRequest($this->context . '.filter.mediatype', 'filter_mediatypeId');
        $this->setState('filter.mediatypeId', $mediaTypeId);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

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
     * Get Stored ID
     * @param string $id   A prefix for the store id
     * @since 7.0
     */
    protected function getStoreId($id = '') {

        // Compile the store id.
        $id .= ':' . $this->getState('filter.filename');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.studytitle');
        $id .= ':' . $this->getState('filter.mediatypeId');
        $id .= ':' . $this->getState('filter.language');

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
                        mediafile.createdate, mediafile.plays, mediafile.downloads, mediafile.language'));

        $query->from('`#__bsms_mediafiles` AS mediafile');

        // Join over the language
        $query->select('l.title AS language_title');
        $query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = mediafile.language');

        //Join over the studies
        $query->select('study.studytitle AS studytitle');
        $query->join('LEFT', '#__bsms_studies AS study ON study.id = mediafile.study_id');

        //Join over the mediatypes
        $query->select('mediatype.media_text AS mediaType');
        $query->join('LEFT', '`#__bsms_media` AS mediatype ON mediatype.id = mediafile.media_image');

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('mediafile.published = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(mediafile.published = 0 OR mediafile.published = 1)');
        }

        //Filter by filename
        $filename = $this->getState('filter.filename');
        if (!empty($filename)) {
            $query->where('mediafile.filename LIKE "%' . $filename . '%"');
        }

        //Filter by study title
        $study = $this->getState('filter.studytitle');
        if (!empty($study)) {
            $query->where('study.studytitle LIKE "%' . $study . '%"');
        }

        //Filter by media type
        $mediaType = $this->getState('filter.mediatypeId');
        if (is_numeric($mediaType)) {
            $query->where('mediafile.media_image = ' . (int) $mediaType);
        }
        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering', 'mediafile.filename');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));

        return $query;
    }

}