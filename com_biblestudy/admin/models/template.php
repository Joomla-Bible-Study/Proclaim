<?php

/**
 * @version     $Id: template.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');
require_once JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php';

abstract class modelClass extends JModelAdmin {

}

class BiblestudyModelTemplate extends modelClass {

   

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param       array   $data   An array of input data.
     * @param       string  $key    The name of the key for the primary key.
     *
     * @return      boolean
     * @since       1.6
     */
    protected function allowEdit($data = array(), $key = 'id') {
        // Check specific edit permission then general edit permission.
        return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.template.' . ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
    }

   
    function store($data = null, $tmpl = null) {
        $row = & $this->getTable();
        //@todo Clean this up
        if (!isset($data)) {
            $data = JRequest::get('post');
        }
        $data['tmpl'] = JRequest::getVar('tmpl', '', 'post', 'string', JREQUEST_ALLOWRAW);

        // Bind the form fields to the table
        if (!$row->bind($data)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        // Make sure the record is valid
        if (!$row->check()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        // Store the table to the database
        if (!$row->store()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        return true;
    }

    function copy($cid) {
        foreach ($cid as $id) {
            $tmplCurr = & JTable::getInstance('template', 'Table');

            $tmplCurr->load($id);
            $tmplCurr->id = null;
            $tmplCurr->title .= " - copy";
            if (!$tmplCurr->store()) {
                $this->setError($curr->getError());
                return false;
            }
        }
        return true;
    }



    /**
     * Get the form data
     *
     * @param <Array> $data
     * @param <Boolean> $loadData
     * @return <type>
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_biblestudy.template', 'template', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    public function getItem($pk = null) {
        return parent::getItem($pk);
    }

    /**
     *
     * @return <type>
     * @since   7.0
     */
    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.template.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param       type    The table type to instantiate
     * @param       string  A prefix for the table class name. Optional.
     * @param       array   Configuration array for model. Optional.
     * @return      JTable  A database object
     * @since       1.6
     */
    public function getTable($type = 'template', $prefix = 'Table', $config = array()) {
        return JTable::getInstance($type, $prefix, $config);
    }

}