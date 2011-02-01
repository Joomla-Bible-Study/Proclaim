<?php

/**
 * @version     $Id: mediaedit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.modeladmin');

    abstract class modelClass extends JModelAdmin {
        
    }

class biblestudyModelmediaedit extends modelClass {

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
    }

    function setId($id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_data = null;
    }

    function &getData() {
        // Load the data
        if (empty($this->_data)) {
            $query = ' SELECT * FROM #__bsms_media ' .
                    '  WHERE id = ' . $this->_id;
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
        }
        if (!$this->_data) {
            $this->_data = new stdClass();
            $this->_data->id = 0;
            //TF added these
            $this->_data->published = 1;
            $this->_data->media_text = null;
            $this->_data->media_image_name = null;
            $this->_data->media_extension = null;
            $this->_data->media_image_path = null;
            $this->_data->media_alttext = null;
            $this->_data->path2 = null;
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

    function publish($cid = array(), $publish = 1) {

        if (count($cid)) {
            $cids = implode(',', $cid);

            $query = 'UPDATE #__bsms_media'
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
     * Get the form data
     *
     * @param <Array> $data
     * @param <Boolean> $loadData
     * @return <type>
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_biblestudy.mediaedit', 'mediaedit', array('control' => 'jform', 'load_data' => $loadData));

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
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.mediafileedit.data', array());
        if (empty($data)) {
            $data = $this->getItem();
            $data->podcast_id = explode(',', $data->podcast_id);
        }


        return $data;
    }

}

?>
