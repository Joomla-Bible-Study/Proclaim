<?php

/**
 * @version     $Id: mediafilesedit.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class biblestudyModelmediafilesedit extends JModelAdmin {

	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access	public
	 * @return	void
	 */
	var $_admin;
	var $_text_prefix = 'COM_BIBLESTUDY';

	function __construct() {
		parent::__construct();

		/**
		 * @todo J16 has new way of retrieving parameters so we need to implement it here too
		 */
		$admin = $this->getLegacyAdmin();
		$this->_admin_params = new JParameter($admin[0]->params);
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
	protected function allowEdit($data = array(), $key = 'id') {
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.mediafilesedit.' . ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}

	function setId($id) {
		// Set id and wipe data
		$this->_id = $id;
		$this->_data = null;
	}

	function &getData() {
		// Load the data
		if (empty($this->_data)) {
			$query = ' SELECT * FROM #__bsms_mediafiles ' .
                    '  WHERE id = ' . $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_data->id = 0;
			//TF added these
			$today = date("Y-m-d H:i:s");
			$this->_data->published = 1;
			$this->_data->media_image = null;
			$this->_data->server = ($this->_admin_params->get('server') > 0 ? $this->_admin_params->get('server') : null);
			$this->_data->path = ($this->_admin_params->get('path') > 0 ? $this->_admin_params->get('path') : null);
			$this->_data->special = ($this->_admin_params->get('target') != 'No default' ? $this->_admin_params->get('target') : null);
			;
			$this->_data->filename = null;
			$this->_data->size = null;
			$this->_data->podcast_id = ($this->_admin_params->get('podcast') > 0 ? $this->_admin_params->get('podcast') : null);
			$this->_data->internal_viewer = null;
			$this->_data->mediacode = null;
			$this->_data->ordering = null;
			$this->_data->study_id = null;
			$this->_data->createdate = $today;
			$this->_data->link_type = ($this->_admin_params->get('download') > 0 ? $this->_admin_params->get('download') : null);
			$this->_date->hits = null;
			$this->_data->mime_type = ($this->_admin_params->get('mime') > 0 ? $this->_admin_params->get('mime') : null);
			$this->_data->docMan_id = null;
			$this->_data->article_id = null;
			$this->_data->comment = null;
			$this->_data->virtueMart_id = null;
			$this->_data->params = null;
			$this->_data->player = null;
			$this->_data->popup = null;
		}
		return $this->_data;
	}

	/**
	 * Method to move a mediafile listing
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function move($direction) {
		$row = & $this->getTable();
		if (!$row->load($this->_id)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!$row->move($direction, ' study_id = ' . (int) $row->study_id . ' AND published >= 0 ')) {
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
	function saveorder($pks=null, $cid = array(), $order= null) {
		$row = & $this->getTable();
		$groupings = array();

		// update ordering values
		for ($i = 0; $i < count($cid); $i++) {
			$row->load((int) $cid[$i]);
			// track categories
			$groupings[] = $row->study_id;

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
			$row->reorder('study_id = ' . (int) $group);
		}

		return true;
	}

	function getLegacyAdmin() {
		if (empty($this->_admin)) {
			$query = 'SELECT params'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$this->_admin = $this->_getList($query);
		}
		return $this->_admin;
	}

	
	/**
	 * Overrides the JModelAdmin save routine in order to implode the podcast_id
	 *
	 * @param array $data
	 * @return <Boolean> True on sucessfull save
	 * @since   7.0
	 */
	public function save($data) {
		//Implode only if they selected at least one podcast. Otherwise just clear the podcast_id field
		$data['podcast_id'] = empty($data['podcast_id']) ? '' : implode(',', $data['podcast_id']);
		return parent::save($data);
	}

	protected function preprocessForm(JForm $form, $data, $group = 'content') {
		parent::preprocessForm($form, $data, $group);
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
		$form = $this->loadForm('com_biblestudy.mediafilesedit', 'mediafilesedit', array('control' => 'jform', 'load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState('com_biblestudy.edit.mediafilesedit.data', array());
		if (empty($data)) {
			$data = $this->getItem();

			$data->podcast_id = explode(',', $data->podcast_id);
		}


		return $data;
	}

}