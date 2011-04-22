<?php

/**
 * @version     $Id: seriesedit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.modeladmin');
 require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'biblestudy.php';
    abstract class modelClass extends JModelAdmin {
        
    }

class biblestudyModelseriesedit extends modelClass {

    /**
     * Constructor that retrieves the ID from the request
     *
     * @access	public
     * @return	void
     */
    var $_teacher;
    var $_admin;

    //$teacher = $this->getTeacher();
    function __construct() {
        parent::__construct();
        $teacher = $this->getTeacher();
        $admin = $this->getAdmin();
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
        protected function allowEdit($data = array(), $key = 'id')
        {
                // Check specific edit permission then general edit permission.
                return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.seriesedit.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
        }

    function setId($id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_data = null;
    }

    function &getData() {
        // Load the data
        $admin = $this->getAdmin();
        if (empty($this->_data)) {
            $query = ' SELECT * FROM #__bsms_series ' .
                    '  WHERE id = ' . $this->_id;
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
        }
        if (!$this->_data) {
            $this->_data = new stdClass();
            $this->_data->id = 0;
            //TF added these
            $this->_data->published = 0;
            $this->_data->series_text = null;
            $this->_data->series_thumbnail = ($admin[0]->series ? $admin[0]->series : null);
            $this->_data->description = null;
            $this->_data->teacher = null;
        }
        return $this->_data;
    }

    function getTeacher() {
        if (empty($this->_teacher)) {
            $query = 'SELECT id AS value, teachername AS text'
                    . ' FROM #__bsms_teachers'
                    . ' WHERE published = 1';
            $this->_teacher = $this->_getList($query);
        }
        return $this->_teacher;
    }

    function getAdmin() {
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
    function store() {
        $row = & $this->getTable();

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
     * Method to delete record(s)
     *
     * @access	public
     * @return	boolean	True on success
     */
    function legacyDelete() {
        $cids = JRequest::getVar('cid', array(0), 'post', 'array');

        $row = & $this->getTable();

        if (count($cids)) {
            foreach ($cids as $cid) {
                if (!$row->delete($cid)) {
                    $this->setError($row->getErrorMsg());
                    return false;
                }
            }
        }
        return true;
    }

    function legacyPublish($cid = array(), $publish = 1) {

        if (count($cid)) {
            $cids = implode(',', $cid);

            $query = 'UPDATE #__bsms_series'
                    . ' SET published = ' . intval($publish)
                    . ' WHERE id IN ( ' . $cids . ' )'

            ;
            $this->_db->setQuery($query);
            if (!$this->_db->query()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }
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
        $form = $this->loadForm('com_biblestudy.seriesedit', 'seriesedit', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.seriesedit.data', array());
        if (empty($data))
            $data = $this->getItem();

        return $data;
    }

}

?>
