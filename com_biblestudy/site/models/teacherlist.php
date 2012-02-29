<?php
/**
 * @version $Id: teacherlist.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

//No Direct Access
defined('_JEXEC') or die;

jimport( 'joomla.application.component.modellist' );


class biblestudyModelteacherlist extends JModelList
{

   
/**
     * Build an SQL query to load the list data
     *
     * @return  JDatabaseQuery
     * @since   7.0
     */
    protected function getListQuery() {
        $db = $this->getDbo();
        
        $query = $db->getQuery(true);
        $query->select('teachers.*,CASE WHEN CHAR_LENGTH(teachers.alias) THEN CONCAT_WS(\':\', teachers.id, teachers.alias) ELSE teachers.id END as slug');
        $query->from('#__bsms_teachers as teachers');
        $query->select('s.id as sid');
        $query->join('LEFT','#__bsms_studies as s on teachers.id = s.teacher_id');
        $query->where('teachers.published = 1 AND list_show = 1');
        $query->order('teachername, ordering ASC');
       
        return $query;
        
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
  
function getAdmin()
	{
		if (empty($this->_admin)) {
			$query = 'SELECT params'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$this->_admin = $this->_getList($query);
		}
		return $this->_admin;
	}

}