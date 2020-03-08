<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Template model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyModelTemplate extends JModelAdmin
{
	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		// Make sure we cannot unpublished default template.
		if ($data['id'] == '1' && $data['published'] != '1')
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JBS_TPL_DEFAULT_ERROR'), 'error');

			return false;
		}

		return parent::save($data);
	}

	/**
	 * Copy Template
	 *
	 * @param   array  $cid  ID of template
	 *
	 * @return boolean
	 *
	 * @since 7.0
	 */
	public function copy($cid)
	{
		foreach ($cid as $id)
		{
			/** @type TableTemplate $tmplCurr */
			$tmplCurr = JTable::getInstance('Template', 'Table');

			$tmplCurr->load($id);
			$tmplCurr->id = null;
			$tmplCurr->title .= " - copy";

			if (!$tmplCurr->store())
			{
				JFactory::getApplication()->enqueueMessage($tmplCurr->getError(), 'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   12.2
	 */
	public function publish(&$pks, $value = 1)
	{
		foreach ($pks as $i => $pk)
		{
			if ($pk == 1 && $value != 1)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('JBS_TPL_DEFAULT_ERROR'), 'error');
				unset($pks[$i]);
			}
		}

		return parent::publish($pks, $value);
	}

	/**
	 * Get the form data
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since  7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_biblestudy.template', 'template', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
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
	public function checkout($pk = null)
	{
		return $pk;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since       1.6
	 */
	public function getTable($name = 'template', $prefix = 'Table', $options = array())
	{
		return JTable::getInstance($name, $prefix, $options);
	}

	/**
	 * Load Form Date
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   7.0
	 */
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_biblestudy.edit.template.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Custom clean the cache of com_biblestudy and biblestudy modules
	 *
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_biblestudy');
		parent::cleanCache('mod_biblestudy');
	}
}
