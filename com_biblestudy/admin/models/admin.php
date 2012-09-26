<?php

/**
 * Admin Model
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

// Import library dependencies for database
JLoader::register('InstallerModel', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_installer' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'extension.php');
JLoader::register('Com_BiblestudyInstallerScript', JPATH_ADMINISTRATOR . '/components/com_biblestudy/biblestudy.script.php');

/**
 * Admin admin model class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyModelAdmin extends JModelAdmin {

    /**
     * Context
     * @var string
     */
    protected $_context = 'com_biblestudy.discover';

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     * @param string $ordering
     * @param string $direction
     *
     * @since	1.7.2
     */
    protected function populateState($ordering = null, $direction = null) {
        $app = JFactory::getApplication();
        $this->setState('message', $app->getUserState('com_biblestudy.message'));
        $this->setState('extension_message', $app->getUserState('com_biblestudy.extension_message'));
        $app->setUserState('com_biblestudy.message', '');
        $app->setUserState('com_biblestudy.extension_message', '');
        parent::populateState('name', 'asc');
    }

    /**
     * Constructor that retrieves the ID from the request
     * @var     Prefix Component decleraion
     * @access	public
     * @return	void
     */
    var $_text_prefix = 'COM_BIBLESTUDY';

    /**
     * Conctruter that retrives the id from the admin section
     *
     * @var Admin section decleration
     */
    var $_admin;

    /**
     * Cuncructer
     */
    public function __construct() {
        parent::__construct();

        $array = JRequest::getVar('cid', 0, '', 'array');
        $this->setId((int) $array[0]);
    }

    /**
     * Set ID of admin
     *
     * @param int $id
     */
    public function setId($id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_data = null;
    }

    /**
     * Get Date
     * @return object
     */
    public function &getData() {
        // Load the data
        $query = ' SELECT * FROM #__bsms_admin ' .
                '  WHERE id = 1';
        $this->_db->setQuery($query);
        $this->_data = $this->_db->loadObject();
        return $this->_data;
    }

    /**
     * Method to store a record
     *
     * @access	public
     * @param boolean $updateNulls
     * @return	boolean	True on success
     */
    public function store($updateNulls = 'false') {
        $row = $this->getTable();


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

        // Store the web link table to the database
        if (!$row->store()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return true;
    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     * @return	JTable	A database object
     * @since	1.6
     */
    public function getTable($type = 'admin', $prefix = 'Table', $config = array()) {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Gets the form from the XML file.
     *
     * @param <Array> $data
     * @param <Boolean> $loadData
     * @return <JForm> Form Object
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_biblestudy.admin', 'admin', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Load Form Date
     * @return object
     */
    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.admin.data', array());
        if (empty($data))
            $data = $this->getItem();

        return $data;
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
        return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.admin.' . ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
    }

    /**
     * Method to check-out a row for editing.
     *
     * @param   integer  $pk  The numeric id of the primary key.
     *
     * @return  boolean  False on failure or error, true otherwise.
     *
     * @since   11.1
     */
    public function checkout($pk = null) {
        return $pk;
    }

    /**
     * Custom clean the cache of com_biblestudy and biblestudy modules
     * @param string $group
     * @param int    $client_id
     * @since	1.6
     */
    protected function cleanCache($group = null, $client_id = 0) {
        parent::cleanCache('com_biblestudy');
        parent::cleanCache('mod_biblestudy');
    }

    /**
     *
     * Fixes database problems
     */
    public function fix() {
        $changeSet = $this->getItems();
        $changeSet->fix();
        $this->fixSchemaVersion();
        $this->fixUpdateVersion();
        $installer = new Com_BiblestudyInstallerScript();
        $installer->deleteUnexistingFiles();
        $installer->fixMenus();
        $installer->fixImagePaths();
        $installer->fixemptyaccess();
        $installer->fixemptylanguage();
        $this->fixDefaultTextFilters();
    }

    /**
     *
     * Gets the changeset object
     *
     * @return  JSchema  Changeset
     */
    public function getItems() {
        $folder = JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/sql/updates/';
        $changeSet = JSchemaChangeset::getInstance(JFactory::getDbo(), $folder);
        return $changeSet;
    }

    /**
     * Get Pagination state but is harde coded to be true right now.
     * @return boolean
     */
    public function getPagination() {
        return true;
    }

    /**
     * Get version from #__schemas table
     *
     * @return  mixed  the return value from the query, or null if the query fails
     * @throws Exception
     */
    public function getSchemaVersion() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $extensionresult = $this->getExtentionId();
        $query->select('version_id')->from($db->qn('#__schemas'))
                ->where('extension_id = "' . $extensionresult . '"');
        $db->setQuery($query);
        $result = $db->loadResult();
        if ($db->getErrorNum()) {
            throw new Exception('Database error - getSchemaVersion');
        }
        return $result;
    }

    /**
     * Fix schema version if wrong
     *
     * @return   mixed  string schema version if success, false if fail
     */
    public function fixSchemaVersion() {
        // Get correct schema version -- last file in array
        $schema = $this->getCompVersion();
        $db = JFactory::getDbo();
        $result = false;
        $extensionresult = $this->getExtentionId();

        // Check value. If ok, don't do update
        $version = $this->getSchemaVersion();
        if ($version == $schema) {
            $result = $version;
        } else {
            // Delete old row
            $query = $db->getQuery(true);
            $query->delete($db->qn('#__schemas'));
            $query->where($db->qn('extension_id') . ' = "' . $extensionresult . '"');
            $db->setQuery($query);
            $db->query();

            // Add new row
            $query = $db->getQuery(true);
            $query->insert($db->qn('#__schemas'));
            $query->set($db->qn('extension_id') . '= "' . $extensionresult . '"');
            $query->set($db->qn('version_id') . '= ' . $db->q($schema));
            $db->setQuery($query);
            if ($db->query()) {
                $result = $schema;
            }
        }
        return $result;
    }

    /**
     * Get current version from #__extensions table
     *
     * @return  mixed   version if successful, false if fail
     */
    public function getUpdateVersion() {
        $table = JTable::getInstance('Extension');
        $table->load($this->getExtentionId());
        $cache = new JRegistry($table->manifest_cache);
        return $cache->get('version');
    }

    /**
     * Fix Joomla version in #__extensions table if wrong (doesn't equal JVersion short version)
     *
     * @return   mixed  string update version if success, false if fail
     */
    public function fixUpdateVersion() {
        $table = JTable::getInstance('Extension');
        $table->load($this->getExtentionId());
        $cache = new JRegistry($table->manifest_cache);
        $updateVersion = $cache->get('version');
        if ($updateVersion == $this->getCompVersion()) {
            return $updateVersion;
        } else {
            $cache->set('version', $this->getCompVersion());
            $table->manifest_cache = $cache->toString(); //print_r($table->manifest_cache);
            if ($table->store()) {
                return $this->getCompVersion();
            } else {
                return false;
            }
        }
    }

    /**
     * Check if com_biblestudy parameters are blank.
     *
     * @return  string  default text filters (if any)
     */
    public function getDefaultTextFilters() {
        $table = JTable::getInstance('Extension');
        $table->load($table->find(array('name' => 'com_biblestudy')));
        return $table->params;
    }

    /**
     * Check if com_biblestudy parameters are blank. If so, populate with com_content text filters.
     *
     * @return  mixed  boolean true if params are updated, null otherwise
     */
    public function fixDefaultTextFilters() {
        $table = JTable::getInstance('Extension');
        $table->load($table->find(array('name' => 'com_biblestudy')));

        // Check for empty $config and non-empty content filters
        if (!$table->params) {
            // Get filters from com_content and store if you find them
            $contentParams = JComponentHelper::getParams('com_biblestudy');
            if ($contentParams->get('filters')) {
                $newParams = new JRegistry();
                $newParams->set('filters', $contentParams->get('filters'));
                $table->params = (string) $newParams;
                $table->store();
                return true;
            }
        }
    }

    /**
     *
     * To retreave component extention_id
     *
     * @return extention_id
     * @since 7.1.0
     * @throws Exception
     */
    public function getExtentionId() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('extension_id')->from($db->qn('#__extensions'))
                ->where('element = "com_biblestudy"');
        $db->setQuery($query);
        $result = $db->loadResult();
        if ($db->getErrorNum()) {
            throw new Exception('Database error - getExtentionId');
        }
        return $result;
    }

    /**
     * To retreave component version
     *
     * @return string Version of component
     * @since 1.7.3
     */
    public function getCompVersion() {
        $jversion = null;
        $xml = null;
        $file = JPATH_COMPONENT_ADMINISTRATOR . '/biblestudy.xml';
        $xml = JFactory::getXML($file);
        if ($xml):
            $jversion = (string) $xml->version;
        endif;
        return $jversion;
    }

}