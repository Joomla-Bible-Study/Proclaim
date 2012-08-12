<?php

/**
 * Comments Edit
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Model class for CommentsEdit
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyModelcommentsedit extends JModelAdmin {

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState() {
        $app = JFactory::getApplication('site');
        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }
        // Load state from the request. We use a_id to avoid collisions with the router
        $pks = JRequest::getInt('a_id');
        $this->pks = $pks;
        $this->setState('comment.id', $pks);
        $option = JRequest::getCmd('option');
        $app = JFactory::getApplication();
        $app->setUserState($option . 'comment.id', $pks);
    }

    /**
     * Method to get article data.
     *
     * @param	integer	The id of the article.
     *
     * @return	mixed	Content item data object on success, false on failure.
     */
    public function getItem($itemId = null) {
        // Initialise variables.
        $itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('comment.id');

        // Get a row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        $return = $table->load($itemId);

        // Check for a table object error.
        if ($return === false && $table->getError()) {
            $this->setError($table->getError());
            return false;
        }
        $properties = $table->getProperties(1);
        $value = JArrayHelper::toObject($properties, 'JObject');
        return $value;
    }

    /**
     * Overrides the JModelAdmin save routine to save the topics(tags)
     * @param object $data
     * @since 7.0.1
     * @todo This may need to be optimized
     */
    public function save($data) {
        $pks = JRequest::getInt('a_id');
        $option = JRequest::getCmd('option');
        $app = JFactory::getApplication();
        $pks = $app->getUserState($option . 'comment.id');
        if ($pks) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->clear();
            $query->update('#__bsms_comments');
            $query->set(' study_id = ' . $db->Quote($data['study_id']));
            $query->set(' user_id = ' . $db->Quote($data['user_id']));
            $query->set(' full_name = ' . $db->Quote($data['full_name']));
            $query->set(' user_email = ' . $db->Quote($data['user_email']));
            $query->set(' comment_date = ' . $db->Quote($data['comment_date']));
            $query->set(' comment_text = ' . $db->Quote($data['comment_text']));
            $query->set(' published = ' . $db->Quote($data['published']));
            $query->set(' asset_id = ' . $db->Quote($data['asset_id']));
            $query->set(' access = ' . $db->Quote($data['access']));
            $query->set(' language = ' . $db->Quote($data['language']));
            $query->where(' id =' . (int) $pks . ' LIMIT 1');
            $db->setQuery((string) $query);
            if (!$db->query()) {
                JError::raiseError(500, $db->getErrorMsg());
                return false;
            } else {
                return true;
            }
        }
        return parent::save($data);
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
     * @param array $data
     * @param boolean $loadData
     * @return string
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
     * Load Form Data
     * @return string
     * @since   7.0
     */
    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.commentsedit.data', array());
        if (empty($data))
            $data = $this->getItem();

        return $data;
    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param	string	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     * @return	JTable	A database object
     * @since	1.6
     */
    public function getTable($type = 'commentsedit', $prefix = 'Table', $config = array()) {
        JTable::addIncludePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'tables');
        return JTable::getInstance($type, $prefix, $config);
    }

}