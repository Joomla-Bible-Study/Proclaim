<?php

/**
 * MessageType model
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * MessageType model class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyModelMessagetype extends JModelAdmin {

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
        return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.messagetype.' . ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
    }

    /**
     * Method to store a record
     *
     * @access	public
     * @return	boolean	True on success
     */
    public function store() {
        $row = & $this->getTable();

        $data = JRequest::get('post');

        // Bind the form fields to the hello table
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
            //			$this->setError( $row->getErrorMsg() );
            return false;
        }

        return true;
    }

    /**
     * Abstract method for getting the form from the model.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_biblestudy.messagetype', 'messagetype', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array    The default data is an empty array.
     * @since   7.0
     */
    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.messagetype.data', array());
        if (empty($data))
            $data = $this->getItem();

        return $data;
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param	JTable	$table
     *
     * @return	void
     * @since	1.6
     */
    protected function prepareTable(&$table) {
        jimport('joomla.filter.output');
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        $table->message_type = htmlspecialchars_decode($table->message_type, ENT_QUOTES);
        $table->alias = JApplication::stringURLSafe($table->alias);

        if (empty($table->alias)) {
            $table->alias = JApplication::stringURLSafe($table->message_type);
        }

        if (empty($table->id)) {
            // Set the values
            //$table->created	= $date->toMySQL();
            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db = JFactory::getDbo();
                $db->setQuery('SELECT MAX(ordering) FROM #__bsms_message_type');
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        } else {
            // Set the values
            //$table->modified	= $date->toMySQL();
            //$table->modified_by	= $user->get('id');
        }
    }

    /**
     * Custom clean the cache of com_biblestudy and biblestudy modules
     * @param string $group
     * @param int $client_id
     *
     * @since	1.6
     */
    protected function cleanCache($group = null, $client_id = 0) {
        parent::cleanCache('com_biblestudy');
        parent::cleanCache('mod_biblestudy');
    }

}