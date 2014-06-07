<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC')
or die;

jimport('joomla.application.component.modeladmin');

/**
 * MediaFile model class
 *
 * @property mixed _id
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelMediafile extends JModelAdmin
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_BIBLESTUDY';
	/**
	 * Admin
	 *
	 * @var string
	 */
	private $_admin;

    /**
     * Data
     *
     * @var
     * @since   8.1.0
     */
    private $data;

	/**
	 * Method to move a mediafile listing
	 *
	 * @param   string $direction  ?
	 *
	 * @access    public
	 * @return    boolean    True on success
	 *
	 * @since     1.5
	 */
	public function move($direction)
	{
		$row = $this->getTable();

		if (!$row->load($this->_id))
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		if (!$row->move($direction, ' study_id = ' . (int) $row->study_id . ' AND published >= 0 '))
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		return true;
	}

    /**
     * Method to get media item
     *
     * @param   null        $pk
     * @return  mixed|void
     *
     * @since   8.1.0
     */
    public function getItem($pk = null) {
        if(!empty($this->data))
            return $this->data;

        $this->data = parent::getItem($pk);

        if(!empty($this->data)) {
            // Convert metadata field to array
            $registry = new JRegistry($this->data->metadata);
            $this->data->metadata = $registry->toArray();

            // Set the server_id from session if available or fall back on the db value
            $server_id = $this->getState('mediafile.server_id');
            $this->data->server_id = empty($server_id) ? $this->data->server_id : $server_id;

        }

        return $this->data;
    }

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string $type    The table type to instantiate
	 * @param   string $prefix  A prefix for the table class name. Optional.
	 * @param   array  $config  Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 */
	public function getTable($type = 'Mediafile', $prefix = 'Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Overrides the JModelAdmin save routine in order to implode the podcast_id
	 *
	 * @param   array $data  The form data.
	 *
	 * @return  boolean True on successfully save
	 *
	 * @since   7.0
	 */
	public function save($data)
	{
		if (parent::save($data))
		{
			// Implode only if they selected at least one podcast. Otherwise just clear the podcast_id field
			$data['podcast_id'] = empty($data['podcast_id']) ? '' : implode(',', $data['podcast_id']);

			/* This code could be uncommented and would remove spaces from filename
			// $data['filename'] = str_replace(' ','_',$data['filename']);
			// Remove starting and trailing spaces */
			$data['filename'] = trim($data['filename']);

			return true;
		}

		return false;
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param   integer $pk  The numeric id of the primary key.
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
     * Get the media form
     *
     * @return bool|mixed
     * @throws Exception
     *
     * @since   8.1.0
     */
    public function getMediaForm() {
        // If user hasn't selected a server yet, just return an empty form
        $server_id = $this->data->server_id;
        if(empty($server_id)) {
            //@TODO This may not be optimal, seems like a hack
            return new JForm("No-op");
        }
        // Reverse lookup server_id to server type
        $model = JModelLegacy::getInstance('server', 'BibleStudyModel');
        $server_type = $model->getType($server_id);

        $path = JPath::clean(JPATH_ADMINISTRATOR . '/components/com_biblestudy/addons/servers/'.$server_type);

        JForm::addFormPath($path);
        JForm::addFieldPath($path.'/fields');

        // Add language files
        $lang = JFactory::getLanguage();
        if(!$lang->load('jbs_addon_legacy', JPATH_ADMINISTRATOR.'/components/com_biblestudy/addons/servers/'.$server_type))
            throw new Exception(JText::_('JBS_ERR_ADDON_LANGUAGE_NOT_LOADED'));

        $form = $this->loadForm('com_biblestudy.mediafile.media', "media", array('control' => 'jform', 'load_data' => true), true, "/media");

        if (empty($form)) {
            return false;
        }

        return $form;
    }
	/**
	 * Get the form data
	 *
	 * @param   array   $data      Data for the form.
	 * @param   boolean $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return boolean|object
	 *
	 * @since 7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
        if (empty($data)) {
            $this->getItem();
        }

		// Get the form.
        // @TODO Rename the form to "media" instead of mediafile
		$form  = $this->loadForm('com_biblestudy.mediafile', 'mediafile', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

        // @TODO Maybe this needs to be removed from here? (Separation of concerns)
		$jinput = JFactory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0);

		} // The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
		}

		$user = JFactory::getUser();

		// Check for existing article.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_biblestudy.mediafile.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_biblestudy'))
		)
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an article you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');

		}

		return $form;
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @param   array $commands  An array of commands to perform.
	 * @param   array $pks       An array of item ids.
	 * @param   array $contexts  An array of item contexts.
	 *
	 * @return    boolean     Returns true on success, false on failure.
	 *
	 * @since    2.5
	 */
	public function batch($commands, $pks, $contexts)
	{
		// Sanitize user ids.
		$pks = array_unique($pks);
		JArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks))
		{
			$this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));

			return false;
		}

		$done = false;

		if (strlen($commands['player']) > 0)
		{
			if (!$this->batchPlayer($commands['player'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}
		if (strlen($commands['link_type']) > 0)
		{
			if (!$this->batchlink_type($commands['link_type'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}
		if (strlen($commands['mimetype']) > 0)
		{
			if (!$this->batchMimetype($commands['mimetype'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (strlen($commands['mediatype']) > 0)
		{
			if (!$this->batchMediatype($commands['mediatype'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}
		if (strlen($commands['popup']) > 0)
		{
			if (!$this->batchPopup($commands['popup'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (!$done)
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch Player changes for a group of mediafiles.
	 *
	 * @param   string $value     The new value matching a player.
	 * @param   array  $pks       An array of row IDs.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	protected function batchPlayer($value, $pks, $contexts)
	{
		// Set the variables
		$user  = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($user->authorise('core.edit', $contexts[$pk]))
			{
				$table->reset();
				$table->load($pk);
				$table->player = (int) $value;

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Custom clean the cache of com_biblestudy and biblestudy modules
	 *
	 * @param   string  $group      The cache group
	 * @param   integer $client_id  The ID of the client
	 *
	 * @return  void
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_biblestudy');
		parent::cleanCache('mod_biblestudy');
	}

	/**
	 * Batch popup changes for a group of media files.
	 *
	 * @param   string $value     The new value matching a client.
	 * @param   array  $pks       An array of row IDs.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	protected function batchlink_type($value, $pks, $contexts)
	{
		// Set the variables
		$user  = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($user->authorise('core.edit', $contexts[$pk]))
			{
				$table->reset();
				$table->load($pk);
				$table->link_type = (int) $value;

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch popup changes for a group of media files.
	 *
	 * @param   string $value     The new value matching a client.
	 * @param   array  $pks       An array of row IDs.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	protected function batchMimetype($value, $pks, $contexts)
	{
		// Set the variables
		$user  = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($user->authorise('core.edit', $contexts[$pk]))
			{
				$table->reset();
				$table->load($pk);
				$table->mime_type = (int) $value;

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch popup changes for a group of media files.
	 *
	 * @param   string $value     The new value matching a client.
	 * @param   array  $pks       An array of row IDs.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	protected function batchMediatype($value, $pks, $contexts)
	{
		// Set the variables
		$user  = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($user->authorise('core.edit', $contexts[$pk]))
			{
				$table->reset();
				$table->load($pk);
				$table->media_image = (int) $value;

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch popup changes for a group of media files.
	 *
	 * @param   string $value     The new value matching a client.
	 * @param   array  $pks       An array of row IDs.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	protected function batchPopup($value, $pks, $contexts)
	{
		// Set the variables
		$user  = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($user->authorise('core.edit', $contexts[$pk]))
			{
				$table->reset();
				$table->load($pk);
				$table->popup = (int) $value;

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record  A record object.
	 *
	 * @return    boolean    True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since    1.6
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			if ($record->state != -2)
			{
				return false;
			}
			$user = JFactory::getUser();

            return $user->authorise('core.delete', 'com_biblestudy.mediafile.' . (int) $record->id);
        }

        return false;
    }

	/**
	 * Load Form Data
	 *
	 * @return array
	 *
	 * @since   7.0
	 */
	protected function loadFormData()
	{
		$session = JFactory::getApplication()->getUserState('com_biblestudy.mediafile.edit.data', array());

        $data = empty($session) ? $this->data : $session;

		return $data;
	}

    /**
     * Auto-populate the model state
     *
     * @return  void
     *
     * @since   8.1.0
     */
    protected function populateState() {
        $app = JFactory::getApplication('administrator');
        $input = $app->input;

        $pk = $input->get('id', null, 'INTEGER');
        $this->setState('mediafile.id', $pk);

        $server_id = $app->getUserState('com_biblestudy.edit.mediafile.server_id');

        $this->setState('mediafile.server_id', $server_id);
    }

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   object $table  A record object.
	 *
	 * @return    array    An array of conditions to add to add to ordering queries.
	 *
	 * @since    1.6
	 */
	protected function getReorderConditions($table)
	{
		$condition   = array();
		$condition[] = 'study_id = ' . (int) $table->study_id;

		return $condition;
	}
}
