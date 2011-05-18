<?php
/**
 * @version     $Id: templateedit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.modeladmin');
require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'biblestudy.php';
    abstract class modelClass extends JModelAdmin {
        
    }

class biblestudyModeltemplateedit extends modelClass {
	var $_id;
	var $_template;


	function __construct() {
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
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
                return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.templateedit.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
        }
    
	/**
	 * @desc Sets the id, and the _tmpl variable
	 * @param $id
	 * @return null
	 */
	function setId($id) {
		// Set id and wipe data
		$this->_id		= $id;
		$this->_tmpl	= null;

	}

	function getTemplate(){
		if(empty($this->_template)) {

			$query = ' SELECT * FROM #__bsms_templates '.
					'  WHERE id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_template = $this->_db->loadObject();
		}

		if (!$this->_template)
		{
			$this->_template = new stdClass();
			$this->_template->id = 0;
			$this->_template->type = null;
			$this->_template->published = 1;
			$this->_template->params = null;
			$this->_template->title = null;
			$this->_template->text = null;
			$this->_template->pdf = null;
		}
		return $this->_template;
	}

	function store($data = null, $tmpl = null){
		$row =& $this->getTable(); //dump ($row, 'row: ');
		//@todo Clean this up
		if(!isset($data)) {
			$data = JRequest::get('post');
		}
		$data['tmpl'] = JRequest::getVar( 'tmpl', '', 'post', 'string', JREQUEST_ALLOWRAW );
//dump ($data, 'data: ');
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
			return false;
		}
		return true;
	}

	function copy($cid) {
		foreach($cid as $id) {
			$tmplCurr =& JTable::getInstance('templateedit', 'Table');

			$tmplCurr->load($id);
			$tmplCurr->id = null;
			$tmplCurr->title .= " - copy";
			if (!$tmplCurr->store()) {
				$this->setError($curr->getError());
				return false;
			}
		}
		return true;
	}
	/**
	 * @todo Make sure there is at least one template of each type
	 * @return unknown_type
	 */
	function legacyDelete() {
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$row =& $this->getTable();

		if (count( $cids ))
		{
			foreach($cids as $cid) {
				if ($cid == 1)
				{$this->setError('You cannot delete the default template');
				return false;
				}
				if (!$row->delete( $cid )) {
					if($cid == 1)
					{$this->setError('You cannot delete the default template');}
					else {$this->setError( $row->getErrorMsg() );}
					return false;
				}
			}
		}
		return true;
	}
	function legacyPublish($cid = array(), $publish = 1) {
		if (count( $cid )) {
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__bsms_templates'
			. ' SET published = ' . intval($publish)
			. ' WHERE id IN ('.$cids.')'
				
			;
			$this->_db->setQuery( $query );
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
        $form = $this->loadForm('com_biblestudy.templateedit', 'templateedit', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

 public function getItem($pk = null) {
        return parent::getItem($pk);
    }
    /**
     *
     * @return <type>
     * @since   7.0
     */
    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.templateedit.data', array());
        if (empty($data))
            $data = $this->getItem();

        return $data;
    }
    
     /**
         * Returns a reference to the a Table object, always creating it.
         *
         * @param       type    The table type to instantiate
         * @param       string  A prefix for the table class name. Optional.
         * @param       array   Configuration array for model. Optional.
         * @return      JTable  A database object
         * @since       1.6
         */
        public function getTable($type = 'templateedit', $prefix = 'Table', $config = array()) 
        {
                return JTable::getInstance($type, $prefix, $config);
        }
}
?>