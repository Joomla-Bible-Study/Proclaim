<?php

/**
 * @version     $Id: teachers.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class BiblestudyModelTeachers extends JModelList {

    /**
     * teachers data array
     *
     * @var array
     */
    var $_data;
    var $_total = null;
    var $_pagination = null;
    var $allow_deletes = null;

    function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'teacher.published',
                'teacher.ordering',
                'teacher.teachername',
                'teacher.alias',
                'teacher.language'
            );
        }

        parent::__construct($config);
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

    protected function populateState($ordering = null, $direction = null) {
        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        parent::populateState('teacher.teachername', 'ASC');
    }

    /**
     *
     * @param <string> $id   A prefix for the store id
     * @return <string>      A store id
     * @since 7.0
     */
    protected function getStoreId($id = '') {

        // Compile the store id.
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /**
     *
     * @return arrey
     * @since 7.0
     */
    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select', 'teacher.id, teacher.published, teacher.ordering, teacher.teachername, teacher.alias, teacher.language'));
        $query->from('#__bsms_teachers AS teacher');

        // Join over the language
        $query->select('l.title AS language_title');
        $query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = teacher.language');


        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('teacher.published = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(teacher.published = 0 OR teacher.published = 1)');
        }

        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));
        return $query;
    }

}