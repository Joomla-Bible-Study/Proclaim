<?php

/**
 * @version     $Id: mimetypelist.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

abstract class modelClass extends JModelList {

}

class biblestudyModelmimetypelist extends modelClass {

   
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
    protected function populateState($ordering = null, $direction = null) {
        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        parent::populateState('mimetype.mimetext', 'ASC');
    }

    protected function getListQuery() {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select', 'mimetype.id, mimetype.mimetype, mimetype.mimetext, mimetype.published'));
        $query->from('`#__bsms_mimetype` AS mimetype');

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('mimetype.published = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(mimetype.published = 0 OR mimetype.published = 1)');
        }

        return $query;
    }

}