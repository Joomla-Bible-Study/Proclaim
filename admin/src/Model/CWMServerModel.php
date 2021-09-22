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

use Joomla\Registry\Registry;

/**
 * Server administrator model
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyModelServer extends JModelAdmin
{
	/**
	 * Data
	 *
	 * @var object
	 * @since   9.0.0
	 */
	private $data;

	/**
	 * @var boolean
	 * @since 9.0.0
	 * @todo need to look into this and see if we need it still.
	 */
	private $event_after_upload;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @throws  Exception
	 * @since   12.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		if (isset($config['event_after_upload']))
		{
			$this->event_after_upload = isset($config['event_after_upload']);
		}
	}

	/**
	 * Reverse look up of id to server_type
	 *
	 * @param   int   $pk   ID to get
	 * @param   bool  $ext  If comfing from externl
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	public function getType($pk, $ext = false)
	{
		return $this->getItem($pk, $ext)->type;
	}

	/**
	 * Method to get a server item.
	 *
	 * @param   null  $pk   An optional id of the object to get
	 * @param   bool  $ext  If comfing from externl
	 *
	 * @return mixed Server Server data object, false on failure
	 *
	 * @since 9.0.0
	 */
	public function getItem($pk = null, $ext = false)
	{
		if (!empty($this->data))
		{
			return $this->data;
		}

		$this->data = parent::getItem($pk);

		if ($this->data)
		{
			// Convert media field to array
			$registry          = new Registry($this->data->media);
			$this->data->media = $registry->toArray();

			// Set the type from session if available or fall back on the db value
			$server_name             = $this->getState('server.server_name');
			$this->data->server_name = empty($server_name) ? $this->data->server_name : $server_name;

			$type = null;

			if (!$ext)
			{
				$type = $this->getState('server.type');
			}

			$this->data->type = empty($type) ? $this->data->type : $type;

			// Load server type configuration if available
			if ($this->data->type)
			{
				$this->data->addon = $this->getConfig($this->data->type);
			}
		}

		return $this->data;
	}

	/**
	 * Return the configuration xml of a server
	 *
	 * @param   string  $addon  Type of server
	 *
	 * @return SimpleXMLElement
	 *
	 * @since   9.0.0
	 */
	public function getConfig($addon)
	{
		$path = JPATH_ADMINISTRATOR . '/components/com_proclaim/addons/servers/' . $addon . '/' . $addon . '.xml';

		return simplexml_load_string(file_get_contents($path));
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		if (strpos($data['server_name'], '"onmouseover="prompt(1)"') !== false)
		{
			$this->setError('"Illegal character use in Server Name field"');

			return false;
		}

		if (isset($data['params']['path']))
		{
			if (strpos($data['params']['path'], '//'))
			{
				$data['params']['path'] = substr($data['params']['path'], strpos($data['params']['path'], '//'));
			}
			elseif (strpos($data['params']['path'], '//') === false)
			{
				$data['params']['path'] = '//' . $data['params']['path'];
			}
		}

		if (parent::save($data))
		{
			return true;
		}

		return false;
	}

	/**
	 * Get the server form
	 *
	 * @return boolean|mixed
	 *
	 * @throws Exception
	 *
	 * @since   9.0.0
	 */
	public function getAddonServerForm()
	{
		// If user hasn't selected a server type yet, just return an empty form
		$type = $this->data->type;

		if (empty($type))
		{
			// @TODO This may not be optimal, seems like a hack
			return new JForm("No-op");
		}

		$path = JPath::clean(JPATH_ADMINISTRATOR . '/components/com_proclaim/addons/servers/' . $type);

		JForm::addFormPath($path);
		JForm::addFieldPath($path . '/fields');

		// Add language files
		$lang = Factory::getLanguage();

		if (!$lang->load('jbs_addon_' . $type, $path))
		{
			throw new Exception(JText::_('JBS_CMN_ERROR_ADDON_LANGUAGE_NOT_LOADED'));
		}

		$form = $this->loadForm('com_proclaim.server.' . $type, $type, array('control' => 'jform', 'load_data' => true), true, "/server");

		if ($form === null)
		{
			return false;
		}

		return $form;
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
		if (empty($data))
		{
			$this->getItem();
		}

		// Get the forms.
		$form = $this->loadForm('com_proclaim.server', 'server', array('control' => 'jform', 'load_data' => $loadData));

		if ($form === null)
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since    1.6
	 */
	protected function canDelete($record)
	{
		$user = Factory::getUser();

		return $user->authorise('core.delete', 'com_proclaim.article.' . (int) $record->id);
	}

	/**
	 * Method to test whether a state can be edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return   boolean  True if allowed to change the state of the record. Defaults to the permission set in the
	 *                    component.
	 *
	 * @since    1.6
	 */
	protected function canEditState($record)
	{
		$user = Factory::getUser();

		// Check for existing article.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_proclaim.server.' . (int) $record->id);
		}

		// Default to component settings if neither article nor category known.
		return parent::canEditState($record);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  object    The default data is an empty array.
	 *
	 * @throws \Exception
	 * @since   9.0.0
	 * @TODO    This gets called twice, because we're loading two forms. (There is a redundancy
	 *          in the bind() because the data is itereted over 2 times, 1 for each form). Possibly,
	 *          figure out a way to iterate over only the relevant data)
	 */
	protected function loadFormData()
	{
		// If current state has data, use it instead of data from db
		$session = Factory::getApplication()->getUserState('com_proclaim.edit.server.data', array());

		return empty($session) ? $this->data : $session;
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
		parent::cleanCache('mod_biblestudy');
	}

	/**
	 * Auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since   9.0.0
	 */
	protected function populateState()
	{
		$app   = Factory::getApplication('administrator');
		$input = $app->input;

		$pk = $input->get('id', null, 'INTEGER');
		$this->setState('server.id', $pk);

		$sname = $app->getUserState('com_proclaim.edit.server.server_name');
		$this->setState('server.server_name', $sname);

		$type = $app->getUserState('com_proclaim.edit.server.type');
		$this->setState('server.type', $type);
	}
}
