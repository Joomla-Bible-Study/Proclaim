<?php

/**
 * @version     $Id: teacheredit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.modeladmin');
 require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'biblestudy.php';
    abstract class modelClass extends JModelAdmin {
        
    }

class biblestudyModelteacheredit extends modelClass {

    /**
     * Constructor that retrieves the ID from the request
     *
     * @access	public
     * @return	void
     */
    var $_admin;

    function __construct() {
        parent::__construct();
        $admin = $this->getAdmin();
        $array = JRequest::getVar('cid', 0, '', 'array');
        $this->setId((int) $array[0]);
        //$admin = $this->getAdmin();
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
                return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.teacheredit.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
        }

    function setId($id) {
        // Set id and wipe data

        $this->_id = $id;
        $this->_data = null;
        $this->_admin = null;
    }

    function &getData() {
        // Load the data
        $admin = $this->getAdmin();
        if (empty($this->_data)) {
            $query = ' SELECT * FROM #__bsms_teachers ' .
                    '  WHERE id = ' . $this->_id;
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
        }
        if (!$this->_data) {
            $this->_data = new stdClass();
            $this->_data->id = 0;
            $this->_data->teachername = null;
            $this->_data->title = null;
            $this->_data->phone = null;
            $this->_data->email = null;
            $this->_data->website = null;
            $this->_data->information = null;
            $this->_data->image = null;
            $this->_data->imageh = null;
            $this->_data->imagew = null;
            $this->_data->thumb = null;
            $this->_data->thumbh = null;
            $this->_data->thumbw = null;
            $this->_data->short = null;
            $this->_data->ordering = null;
            $this->_data->catid = null;
            $this->_data->list_show = 1;
            $this->_data->teacher_thumbnail = ($admin[0]->teacher ? $admin[0]->teacher : null);
            $this->_data->teacher_image = null;
            //TF added this
            $this->_data->published = 1;
        }
        return $this->_data;
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
        //Allows HTML content to come through to the database row
        $data['information'] = JRequest::getVar('information', '', 'post', 'string', JREQUEST_ALLOWRAW);
        // Bind the form fields to the hello table
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
    function delete() {
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

            $query = 'UPDATE #__bsms_teachers'
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

    function move($direction) {
        $row = & $this->getTable();
        if (!$row->load($this->_id)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        if (!$row->move($direction, ' catid = ' . (int) $row->catid . ' AND published >= 0 ')) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return true;
    }

    /**
     * Method to move a mediafile listing
     *
     * @access	public
     * @return	boolean	True on success
     * @since	1.5
     */
    function saveorder($cid = array(), $order) {
        $row = & $this->getTable();
        $groupings = array();

        // update ordering values
        for ($i = 0; $i < count($cid); $i++) {
            $row->load((int) $cid[$i]);
            // track categories
            $groupings[] = $row->catid;

            if ($row->ordering != $order[$i]) {
                $row->ordering = $order[$i];
                if (!$row->store()) {
                    $this->setError($this->_db->getErrorMsg());
                    return false;
                }
            }
        }

        // execute updateOrder for each parent group
        $groupings = array_unique($groupings);
        foreach ($groupings as $group) {
            $row->reorder('catid = ' . (int) $group);
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
        $form = $this->loadForm('com_biblestudy.teacheredit', 'teacheredit', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.teacheredit.data', array());
        if (empty($data))
            $data = $this->getItem();

        return $data;
    }

}

?>
