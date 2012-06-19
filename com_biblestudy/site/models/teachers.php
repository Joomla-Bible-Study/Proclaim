<?php

/**
 * @version $Id: teacher.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class BiblestudyModelTeachers extends JModelList {

    /**
     * Build an SQL query to load the list data
     *
     * @return  JDatabaseQuery
     * @since   7.0
     */
    protected function getListQuery() {
        $db = $this->getDbo();

        //See if this view is being filtered by language in the menu
         $menu = JSite::getMenu();
        $item = $menu->getActive(); 
        $language = $item->language;
        $query = $db->getQuery(true);
        $query->select('teachers.*,CASE WHEN CHAR_LENGTH(teachers.alias) THEN CONCAT_WS(\':\', teachers.id, teachers.alias) ELSE teachers.id END as slug');
        $query->from('#__bsms_teachers as teachers');
        $query->select('s.id as sid');
        $query->join('LEFT', '#__bsms_studies as s on teachers.id = s.teacher_id');
        if ($this->getState('filter.language') || $language != '*') {
            $query->where('teachers.language in (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
        }
        $query->where('teachers.published = 1 AND teachers.list_show = 1');
        $query->order('teachers.teachername, teachers.ordering ASC');
        $query->group('teachers.id');
        return $query;
    }

    protected function populateState($ordering='teachers.ordering', $direction='asc') {
        $app = JFactory::getApplication();
        
        $this->setState('filter.language', $app->getLanguageFilter());
    }
    /**
     * @desc Returns the Template to display the list
     * @return Array
     * @since 7.0.2
     */
    function getTemplate() {
        if (empty($this->_template)) {
            $templateid = JRequest::getVar('t', 1, 'get', 'int');
            $db = $this->getDBO();
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__bsms_templates');
            $query->where('published = 1 AND id = ' . $templateid);
            $db->setQuery($query->__toString());
            $this->_template = $db->loadObject();
        }
        return $this->_template;
    }

    function getAdmin() {
        if (empty($this->_admin)) {
            $query = 'SELECT params'
                    . ' FROM #__bsms_admin'
                    . ' WHERE id = 1';
            $this->_admin = $this->_getList($query);
        }
        return $this->_admin;
    }

}