<?php

/**
 * @version     $Id: templateedit.php 2025 2011-08-28 04:08:06Z genu $
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

class biblestudyModeltemplateedit extends modelClass {

    var $_id;
    var $_template;

    function __construct() {
        parent::__construct();

        $array = JRequest::getVar('cid', 0, '', 'array');
        $this->setId((int) $array[0]);
    }

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
        return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.templateedit.' . ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
    }

    /**
     * @desc Sets the id, and the _tmpl variable
     * @param $id
     * @return null
     */
    function setId($id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_tmpl = null;
    }

    function getTemplate() {
        if (empty($this->_template)) {

            $query = ' SELECT * FROM #__bsms_templates ' .
                    '  WHERE id = ' . $this->_id;
            $this->_db->setQuery($query);
            $this->_template = $this->_db->loadObject();
        }

        if (!$this->_template) {
            $this->_template = new stdClass();
            $this->_template->id = 0;
            $this->_template->type = null;
            $this->_template->published = 1;
            $this->_template->params = null;
            $this->_template->title = null;
            $this->_template->text = null;
            $this->_template->pdf = null;
        }
        return $this->_template;
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
            $tmplCurr = & JTable::getInstance('templateedit', 'Table');

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
        $form = $this->loadForm('com_biblestudy.templateedit', 'templateedit', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.templateedit.data', array());
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
    public function getTable($type = 'templateedit', $prefix = 'Table', $config = array()) {
        return JTable::getInstance($type, $prefix, $config);
    }

}