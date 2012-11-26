<?php

/**
 * Teacher model
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Teacher model class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyModelTeacher extends JModelAdmin {

    /**
     * Controller Prefix
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
        return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.teacher.' . ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
    }

    /**
     * Returns a Table object, always creating it
     *
     * @param	type	$type	The table type to instantiate
     * @param	string	$prefix	A prefix for the table class name. Optional.
     * @param	array	$config	Configuration array for model. Optional.
     *
     * @return	JTable	A database object
     * @since	1.7.0
     */
    public function getTable($type = 'Teacher', $prefix = 'Table', $config = array()) {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get a single record.
     *
     * @param	integer	$pk	The id of the primary key.
     *
     * @return	mixed	Object on success, false on failure.
     * @since	1.7.0
     */
    public function getItem($pk = null) {
        if ($item = parent::getItem($pk)) {
            // Convert the params field to an array.
        }

        return $item;
    }

    /**
     * Get the form data
     *
     * @param array $data
     * @param boolean $loadData
     * @return boolean|object
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true) {

        JForm::addFieldPath('JPATH_ADMINISTRATOR/components/com_users/models/fields');

        // Get the form.
        $form = $this->loadForm('com_biblestudy.teacher', 'teacher', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object) $data)) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return	mixed	The data for the form.
     * @since   7.0
     */
    protected function loadFormData() {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.teacher.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param	JTable	$table	A record object.
     *
     * @return	array	An array of conditions to add to add to ordering queries.
     * @since	1.7.0
     */
    protected function getReorderConditions($table) {
        $condition = array();
        $condition[] = 'catid = ' . (int) $table->catid;

        return $condition;
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

        $table->teachername = htmlspecialchars_decode($table->teachername, ENT_QUOTES);
        $table->alias = JApplication::stringURLSafe($table->alias);

        if (empty($table->alias)) {
            $table->alias = JApplication::stringURLSafe($table->teachername);
        }

        if (empty($table->id)) {

            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db = JFactory::getDbo();
                $db->setQuery('SELECT MAX(ordering) FROM #__bsms_teachers');
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        }
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