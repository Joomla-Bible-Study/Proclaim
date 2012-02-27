<?php

/**
 * @version     $Id: commentsedit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die;

    jimport('joomla.application.component.modeladmin');

    abstract class modelClass extends JModelAdmin {

    }

class biblestudyModelcommentsedit extends modelClass {

    /**
     * Constructor that retrieves the ID from the request
     *
     * @access	public
     * @return	void
     */
    function __construct() {
        parent::__construct();

        $array = JRequest::getVar('cid', 0, '', 'array');
        $this->setId((int) $array[0]);
    }

    function setId($id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_data = null;
    }

    function &getData() {
        // Load the data
        if (empty($this->_data)) {
            $query = ' SELECT * FROM #__bsms_comments ' .
                    '  WHERE id = ' . $this->_id;
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
        }
        if (!$this->_data) {
            $this->_data = new stdClass();
            $this->_data->id = 0;
            //TF added these
            $this->_data->published = 0;
            $this->_data->user_id = 0;
            $this->_data->user_email = null;
            $this->_data->full_name = null;
            $this->_data->comment_date = null;
            $this->_data->comment_text = null;
            $this->_data->study_id = 0;
        }
        return $this->_data;
    }

    /**
     * Method to store a record
     *
     * @access	public
     * @return	boolean	True on success
     */
    function store() {
        $row = & $this->getTable();

        $data = JRequest::get('post');

        // Bind the form fields to the  table
        if (!$row->bind($data)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Make sure the  record is valid
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
     * Get the form data
     *
     * @param <Array> $data
     * @param <Boolean> $loadData
     * @return <type>
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_biblestudy.commentsedit', 'commentsedit', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     *
     * @return <type>
     * @since   7.0
     */
    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.commentsedit.data', array());
        if (empty($data))
            $data = $this->getItem();

        return $data;
    }

}