<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Table\CWMMessageTable;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Input;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;

/**
 * MessageType model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMMessageTypeModel extends AdminModel
{
	/**
	 * Method to store a record
	 *
	 * @access    public
	 * @return    boolean    True on success
	 *
	 * @since     7.0.0
	 */
	public function store(): bool
	{
		$row   = new CWMMessageTable($this->_db);
		$input = new Input;
		$data  = $input->get('post');

		// Bind the form fields to the hello table
		if (!$row->bind($data))
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Make sure the record is valid
		if (!$row->check())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Store the table to the database
		if (!$row->store())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		return true;
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		return $this->loadForm('com_proclaim.messagetype', 'messagetype', array('control' => 'jform', 'load_data' => $loadData));
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
	public function checkout($pk = null): bool
	{
		return true;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed   The default data is an empty array.
	 *
	 * @throws \Exception
	 * @since   7.0
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_proclaim.edit.messagetype.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   CWMMessageTable  $table  A reference to a JTable object.
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table): void
	{
		jimport('joomla.filter.output');

		$table->message_type = htmlspecialchars_decode($table->message_type, ENT_QUOTES);
		$table->alias        = ApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias))
		{
			$table->alias = ApplicationHelper::stringURLSafe($table->message_type);
		}

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = Factory::getContainer()->get('DatabaseDriver');
				$query = $db->getQuery(true);
				$query->select('MAX(ordering)')->from('#__bsms_message_type');
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Custom clean the cache of com_proclaim and biblestudy modules
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
		parent::cleanCache('com_proclaim');
		parent::cleanCache('mod_proclaim');
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function getTable($name = 'CWMMessageType', $prefix = '', $options = array()): Table
	{
		return parent::getTable($name, $prefix, $options);
	}
}
