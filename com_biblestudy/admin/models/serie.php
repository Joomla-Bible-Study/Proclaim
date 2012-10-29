<?php

/**
 * Serie model
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/biblestudy.php';

/**
 * Serie admin model
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyModelSerie extends JModelAdmin {

    /**
     * @var		string	The prefix to use with controller messages.
     * @since	1.6
     */
    protected $text_prefix = 'COM_BIBLESTUDY';

    /**
     * Batch copy items to a new category or current.
     *
     * @param   integer  $value     The new category.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  mixed  An array of new IDs on success, boolean false on failure.
     *
     * @since	11.1
     */
    protected function batchCopy($value, $pks, $contexts) {
        $categoryId = (int) $value;

        $table = $this->getTable();
        $i = 0;

        // Check that the category exists
        if ($categoryId) {
            $categoryTable = JTable::getInstance('Category');
            if (!$categoryTable->load($categoryId)) {
                if ($error = $categoryTable->getError()) {
                    // Fatal error
                    $this->setError($error);
                    return false;
                } else {
                    $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
                    return false;
                }
            }
        }

        if (empty($categoryId)) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
            return false;
        }

        // Check that the user has create permission for the component
        $extension = JFactory::getApplication()->input->get('option', '');
        $user = JFactory::getUser();
        if (!$user->authorise('core.create', $extension . '.category.' . $categoryId)) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));
            return false;
        }

        // Parent exists so we let's proceed
        while (!empty($pks)) {
            // Pop the first ID off the stack
            $pk = array_shift($pks);

            $table->reset();

            // Check that the row actually exists
            if (!$table->load($pk)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);
                    return false;
                } else {
                    // Not fatal error
                    $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Alter the title & alias
            $data = $this->generateNewTitle($categoryId, $table->alias, $table->title);
            $table->title = $data['0'];
            $table->alias = $data['1'];

            // Reset the ID because we are making a copy
            $table->id = 0;

            // New category ID
            $table->catid = $categoryId;

            // TODO: Deal with ordering?
            //$table->ordering	= 1;
            // Get the featured state
            $featured = $table->featured;

            // Check the row.
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            // Get the new item ID
            $newId = $table->get('id');

            // Add the new ID to the array
            $newIds[$i] = $newId;
            $i++;

            // Check if the article was featured and update the #__content_frontpage table
            if ($featured == 1) {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->insert($db->quoteName('#__content_frontpage'));
                $query->values($newId . ', 0');
                $db->setQuery($query);
                $db->execute();
            }
        }

        // Clean the cache
        $this->cleanCache();

        return $newIds;
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param	object	$record	A record object.
     *
     * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
     * @since	1.6
     */
    protected function canDelete($record) {
        if (!empty($record->id)) {
            if ($record->state != -2) {
                return;
            }
            $user = JFactory::getUser();
            return $user->authorise('core.delete', 'com_biblestudy.serie.' . (int) $record->id);
        }
    }

    /**
     * Method to test whether a record can have its state edited.
     *
     * @param	object	$record	A record object.
     *
     * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
     * @since	1.6
     */
    protected function canEditState($record) {
        $user = JFactory::getUser();

        // Check for existing article.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', 'com_biblestudy.serie.' . (int) $record->id);
        }
        // Default to component settings if serie known.
        return parent::canEditState('com_biblestudy');
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param	JTable	$table
     *
     * @return	void
     * @since	1.6
     */
    protected function prepareTable($table) {
        jimport('joomla.filter.output');
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        $table->series_text = htmlspecialchars_decode($table->series_text, ENT_QUOTES);
        $table->alias = JApplication::stringURLSafe($table->alias);

        if (empty($table->alias)) {
            $table->alias = JApplication::stringURLSafe($table->series_text);
        }

        if (empty($table->id)) {
            // Set the values
            //$table->created	= $date->toMySQL();
            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db = JFactory::getDbo();
                $db->setQuery('SELECT MAX(ordering) FROM #__bsms_series');
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
     * Returns a Table object, always creating it.
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     *
     * @return	JTable	A database object
     */
    public function getTable($type = 'Serie', $prefix = 'Table', $config = array()) {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get a single record.
     *
     * @param	integer	The id of the primary key.
     *
     * @return	mixed	Object on success, false on failure.
     */
    public function getItem($pk = null) {
        if ($item = parent::getItem($pk)) {

        }
        return $item;
    }

    /**
     * Abstract method for getting the form from the model.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A JForm object on success, false on failure
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_biblestudy.serie', 'serie', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Get Teacher data
     * @return abject
     */
    public function getTeacher() {
        if (empty($this->_teacher)) {
            $query = 'SELECT id AS value, teachername AS text'
                    . ' FROM #__bsms_teachers'
                    . ' WHERE published = 1';
            $this->_teacher = $this->_getList($query);
        }
        return $this->_teacher;
    }

    /**
     * Get Admin data
     * @return abject
     */
    public function getAdmin() {
        if (empty($this->_admin)) {
            $query = 'SELECT *'
                    . ' FROM #__bsms_admin'
                    . ' WHERE id = 1';
            $this->_admin = $this->_getList($query);
        }
        return $this->_admin;
    }

    /**
     * Method to store a record
     *
     * @access	public
     * @return	boolean	True on success
     */
    public function store() {
        $row = $this->getTable();

        $data = JRequest::get('post');
        $data['description'] = JRequest::getVar('description', '', 'post', 'string', JREQUEST_ALLOWRAW);
        // Bind the form fields to the series table
        if (!$row->bind($data)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Make sure the hello record is valid
        if (!$row->check()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Store the web link table to the database
        if (!$row->store()) {
            $this->setError($this->_db->getErrorMsg());
            //			$this->setError( $row->getErrorMsg() );
            return false;
        }

        return true;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array    The default data is an empty array.
     *
     * @since   7.0
     */
    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.serie.data', array());
        if (empty($data))
            $data = $this->getItem();

        return $data;
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