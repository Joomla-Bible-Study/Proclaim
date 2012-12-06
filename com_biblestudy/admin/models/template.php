<?php

/**
 * Template model
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');
require_once JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php';

/**
 * Template model class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyModelTemplate extends JModelAdmin {

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

    /**
     * Store record
     * @param type $data
     * @param type $tmpl
     * @return boolean
     */
    public function store($data = null, $tmpl = null) {
        $row = $this->getTable();
        //@todo Clean this up
        if (!isset($data)) {
            $input = new JInput;
            $data = $input->post;
            //$data = JRequest::get('post');
        }
        $data['tmpl'] = $input->get('tmpl', '', 'string');

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

    /**
     * Copy Template
     * @param type $cid
     * @return boolean
     */
    public function copy($cid) {
        foreach ($cid as $id) {
            $tmplCurr = JTable::getInstance('template', 'Table');

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

    /**
     * Get Items
     * @param type $pk
     * @return type
     */
    public function getItem($pk = null) {
        return parent::getItem($pk);
    }

    /**
     * Load Forme DateÏ
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

    /**
     * Custom clean the cache of com_biblestudy and biblestudy modules
     * @param   string   $group      The cache group
     * @param   integer  $client_id  The ID of the client
     *
     * @return  void
     * @since	1.6
     */
    protected function cleanCache($group = null, $client_id = 0) {
        parent::cleanCache('com_biblestudy');
        parent::cleanCache('mod_biblestudy');
    }

}