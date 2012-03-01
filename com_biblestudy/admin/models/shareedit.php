<?php
/**
 * @version     $Id: shareedit.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */

//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');
abstract class modelClass extends JModelAdmin{}


class biblestudyModelshareedit extends modelClass
{
	
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
		return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.shareedit.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}

	function setId($id)
	{
		// Set id and wipe data

		$this->_id		= $id;
		$this->_data	= null;
		$this->_admin	= null;
	}


	/**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store()
	{
		$row =& $this->getTable();

		$data = JRequest::get( 'post' );

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
	 * Get the form data
	 *
	 * @param <Array> $data
	 * @param <Boolean> $loadData
	 * @return <type>
	 * @since 7.0
	 */
	public function getForm($data = array(), $loadData = true) {
		// Get the form.
		$form = $this->loadForm('com_biblestudy.shareedit', 'shareedit', array('control' => 'jform', 'load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState('com_biblestudy.edit.shareedit.data', array());
		if (empty($data))
		$data = $this->getItem();

		return $data;
	}

}