<?php

/**
 * Style model
 * @package BibleStudy.Admin
 * @since 7.1.0
 * @Copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * Style Model class
 * @package BibleStudy.Admin
 * @since 7.1.0
 */
class BiblestudyModelStyle extends JModelAdmin {

    /**
     * Protect prefix
     * @var		string	The prefix to use with controller messages.
     * @since	1.6
     */
    protected $text_prefix = 'COM_BIBLESTUDY';

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
        return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.style.' . ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     * @return	JTable	A database object
     * @since	2.5
     */
    public function getTable($type = 'Style', $prefix = 'Table', $config = array()) {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param	array	$data		Data for the form.
     * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
     * @return	mixed	A JForm object on success, false on failure
     * @since	2.5
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_biblestudy.style', 'style', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return	mixed	The data for the form.
     * @since	2.5
     */
    protected function loadFormData() {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.style.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param	integer	The id of the primary key.
     *
     * @return	mixed	Object on success, false on failure.
     * @since	1.6
     */
    public function getItem($pk = null) {
        if ($item = parent::getItem($pk)) {

        }

        return $item;
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