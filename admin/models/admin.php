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

// Import library dependencies for database
JLoader::register('InstallerModel', JPATH_ADMINISTRATOR . '/components/com_installer/models/extension.php');
JLoader::register('Com_BiblestudyInstallerScript', JPATH_ADMINISTRATOR . '/components/com_biblestudy/biblestudy.script.php');
JLoader::register('JoomlaInstallerScript', JPATH_ADMINISTRATOR . '/components/com_admin/script.php');

use Joomla\Registry\Registry;

/**
 * Admin admin model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyModelAdmin extends JModelAdmin
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_BIBLESTUDY';

	protected $changeSet = null;

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since    1.6
	 */
	public function getTable($name = 'Admin', $prefix = 'Table', $options = array())
	{
		return JTable::getInstance($name, $prefix, $options);
	}

	/**
	 * Gets the form from the XML file.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since 7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		if (empty($data))
		{
			$this->getItem();
		}

		// Get the form.
		$form = $this->loadForm('com_biblestudy.admin', 'admin', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return    boolean    True on success.
	 *
	 * @since    1.6
	 */
	public function save($data)
	{
		if (parent::save($data))
		{
			return true;
		}

		return false;
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
	 * Get Media Files
	 *
	 * @return mixed
	 *
	 * @since 7.0
	 *
	 * @todo  not sure if this should be here.
	 */
	public function getMediaFiles()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__bsms_mediafiles');
		$db->setQuery($query->__toString());
		$mediafiles = $db->loadObjectList();

		foreach ($mediafiles as $i => $mediafile)
		{
			$reg = new Registry;
			$reg->loadString($mediafile->params);
			$mediafiles[$i]->params = $reg;
		}

		return $mediafiles;
	}

	/**
	 * Fixes database problems
	 *
	 * @return boolean
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function fix()
	{
		if (!$changeSet = $this->getItems())
		{
			return false;
		}

		$changeSet->fix();
		$this->fixSchemaVersion($changeSet);
		$this->fixUpdateVersion();
		$this->fixUpdateJBSMVersion();
		$installer = new BiblestudyModelInstall;
		$installer->fixMenus();
		$installer->fixemptyaccess();
		$installer->fixemptylanguage();
		$this->fixDefaultTextFilters();

		/*
		 * Finally, if the schema updates succeeded, make sure the database is
		 * converted to utf8mb4 or, if not suported by the server, compatible to it.
		 */
		$installerJoomla = new JoomlaInstallerScript;
		$statusArray     = $changeSet->getStatus();

		if (count($statusArray['error']) === 0)
		{
			$installerJoomla->convertTablesToUtf8mb4(false);
		}

		return true;
	}

	/**
	 * Gets the ChangeSet object
	 *
	 * @return string JSchema  ChangeSet
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getItems()
	{
		$folder = JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/sql/updates/';

		if ($this->changeSet !== null)
		{
			return $this->changeSet;
		}

		try
		{
			$this->changeSet = JSchemaChangeset::getInstance(JFactory::getDbo(), $folder);
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

			return false;
		}

		return $this->changeSet;
	}

	/**
	 * Fix schema version if wrong.
	 *
	 * @param   JSchemaChangeSet  $changeSet  Schema change set.
	 *
	 * @return   mixed  string schema version if success, false if fail
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function fixSchemaVersion($changeSet)
	{
		// Get correct schema version -- last file in array.
		$schema          = $changeSet->getSchema();
		$extensionresult = $this->getExtentionId();

		if ($schema == $this->getSchemaVersion())
		{
			return $schema;
		}

		// Delete old row
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__schemas'))
			->where($db->qn('extension_id') . ' = ' . $db->q($extensionresult));
		$db->setQuery($query);
		$db->execute();

		// Add new row
		$query->clear()
			->insert($db->qn('#__schemas'))
			->columns($db->quoteName('extension_id') . ',' . $db->quoteName('version_id'))
			->values($db->quote($extensionresult) . ', ' . $db->quote($schema));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			return false;
		}

		return $schema;
	}

	/**
	 * To retrieve component version
	 *
	 * @return string Version of component
	 *
	 * @since 1.7.3
	 */
	public function getCompVersion()
	{
		$jversion = null;
		$file     = JPATH_COMPONENT_ADMINISTRATOR . '/biblestudy.xml';
		$xml      = simplexml_load_string(file_get_contents($file), 'JXMLElement');

		if ($xml)
		{
			$jversion = (string) $xml->version;
		}

		return $jversion;
	}

	/**
	 * To retrieve component extension_id
	 *
	 * @return string extension_id
	 *
	 * @throws Exception
	 * @since 7.1.0
	 */
	public function getExtentionId()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id')->from($db->qn('#__extensions'))
			->where('element = ' . $db->q('com_biblestudy'));
		$db->setQuery($query);
		$result = $db->loadResult();

		if (!$result)
		{
			throw new Exception('Database error - getExtentionId');
		}

		return $result;
	}

	/**
	 * Get version from #__schemas table
	 *
	 * @return  mixed  the return value from the query, or null if the query fails
	 *
	 * @throws Exception
	 * @since 7.0
	 */
	public function getSchemaVersion()
	{
		$db              = JFactory::getDbo();
		$query           = $db->getQuery(true);
		$extensionresult = $this->getExtentionId();
		$query->select('version_id')->from($db->qn('#__schemas'))
			->where('extension_id = ' . $db->q($extensionresult));
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Fix Joomla version in #__extensions table if wrong (doesn't equal JVersion short version)
	 *
	 * @return   mixed  string update version if success, false if fail
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function fixUpdateVersion()
	{
		/** @type JTableExtension $table */
		$table = JTable::getInstance('Extension');
		$table->load($this->getExtentionId());
		$cache         = new Registry($table->manifest_cache);
		$updateVersion = $cache->get('version');

		if ($updateVersion === $this->getCompVersion())
		{
			return $updateVersion;
		}

		$cache->set('version', $this->getCompVersion());
		$table->manifest_cache = $cache->toString();

		if ($table->store())
		{
			return $this->getCompVersion();
		}

		return false;
	}

	/**
	 * Get current version from #__bsms_update table.
	 *
	 * @return  mixed   version if successful, false if fail.
	 *
	 * @since 9.0.14
	 */
	public function getUpdateJBSMVersion()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('version')
			->from('#__bsms_update')
			->order('id DESC');
		$db->setQuery($query, 0, 1);

		return $db->loadResult();
	}

	/**
	 * Fix Joomla version in #__bsms_updae table if wrong (doesn't equal JVersion short version).
	 *
	 * @return   mixed  string update version if success, false if fail.
	 *
	 * @since 9.0.14
	 */
	public function fixUpdateJBSMVersion()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('id, version')
			->from('#__bsms_update')
			->order('id DESC');
		$db->setQuery($query, 0, 1);

		$results = $db->loadObject();

		if ($results->version === $this->getCompVersion())
		{
			return $results->version;
		}

		$newid = $results->id + 1;

		$query->clear()
			->insert($db->qn('#__bsms_update'))
			->columns($db->qn('id') . ',' . $db->qn('version'))
			->values($db->q($newid) . ', ' . $db->q($this->getCompVersion()));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			return false;
		}

		return $results->version;
	}

	/**
	 * Check if com_biblestudy parameters are blank. If so, populate with com_content text filters.
	 *
	 * @return  mixed  boolean true if params are updated, null otherwise
	 *
	 * @since 7.0
	 */
	public function fixDefaultTextFilters()
	{
		/** @type JTableExtension $table */
		$table = JTable::getInstance('Extension');
		$table->load($table->find(array('name' => 'com_biblestudy')));

		// Check for empty $config and non-empty content filters
		if (!$table->params)
		{
			// Get filters from com_content and store if you find them
			$contentParams = JComponentHelper::getParams('com_biblestudy');

			if ($contentParams->get('filters'))
			{
				$newParams = new Registry;
				$newParams->set('filters', $contentParams->get('filters'));
				$table->params = (string) $newParams;
				$table->store();

				return true;
			}
		}

		return false;
	}

	/**
	 * Get Pagination state but is hard coded to be true right now.
	 *
	 * @return boolean
	 *
	 * @since 7.0
	 */
	public function getPagination()
	{
		return true;
	}

	/**
	 * Get current version from #__extensions table
	 *
	 * @return  mixed   version if successful, false if fail
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getUpdateVersion()
	{
		/** @type JTableExtension $table */
		$table = JTable::getInstance('Extension');
		$table->load($this->getExtentionId());
		$cache = new Registry($table->manifest_cache);

		return $cache->get('version');
	}

	/**
	 * Check if com_biblestudy parameters are blank.
	 *
	 * @return  string  default text filters (if any)
	 *
	 * @since 7.0
	 */
	public function getDefaultTextFilters()
	{
		$table = JTable::getInstance('Extension');
		$table->load($table->find(array('name' => 'com_biblestudy')));

		/** @type TableAdmin $table */

		return $table->params;
	}

	/**
	 * Check for SermonSpeaker and PreachIt
	 *
	 * @return object
	 *
	 * @since 7.0
	 */
	public function getSSorPI()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id, name, element')->from('#__extensions');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Change Player based off MimeType or Extension of File Name
	 *
	 * @return string
	 *
	 * @since 9.0.12
	 */
	public function playerByMediaType()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$db   = JFactory::getDbo();
		$msg  = JText::_('JBS_CMN_OPERATION_SUCCESSFUL');
		$post = $_POST['jform'];
		$reg  = new Registry;
		$reg->loadArray($post['params']);
		$from    = $reg->get('mtFrom', 'x');
		$to      = $reg->get('mtTo', 'x');
		$account = 0;
		$count   = 0;

		$MediaHelper = new JBSMMedia;
		$mimetypes   = $MediaHelper->getMimetypes();

		if ($from !== 'x')
		{
			$key = array_search($from, $mimetypes);
		}
		else
		{
			return 'No Selection Made';
		}

		$query = $db->getQuery(true);
		$query->select('id, params')
			->from('#__bsms_mediafiles')
			->where('published = ' . $db->q('1'));
		$db->setQuery($query);

		foreach ($db->loadObjectList() as $media)
		{
			$count++;
			$search = false;
			$isfrom = '';
			$reg    = new Registry;
			$reg->loadString($media->params);
			$filename  = $reg->get('filename', '');
			$mediacode = $reg->get('mediacode');

			$extension = substr($filename, strrpos($filename, '.') + 1);

			if (strpos($filename, 'http') !== false && $from == 'http')
			{
				$reg->set('mime_type', ' ');
				$isfrom = 'http';
				$search = true;
			}

			if (!empty($mediacode) && $from == 'mediacode')
			{
				$reg->set('mime_type', ' ');
				$isfrom = 'mediacode';
				$search = true;
			}

			if (strpos($key, $extension) !== false || $reg->get('mime_type', 0) == $from)
			{
				$reg->set('mime_type', $from);
				$isfrom = 'Extenstion';
				$search = true;
			}

			if ($search && !empty($isfrom))
			{
				$account++;

				if (JBSMDEBUG)
				{
					$msg .= ' From: ' . $isfrom . '<br />';

					if ($reg->get('mime_type', 0) == $from)
					{
						$msg .= ' MimeType: ' . $reg->get('mime_type') . '<br />';
					}

					$msg .= ' Search found FileName: ' . $filename . '<br />';
				}

				$reg->set('player', $to);

				$query = $db->getQuery(true);
				$query->update('#__bsms_mediafiles')
					->set('params = ' . $db->q($reg->toString()))
					->where('id = ' . (int) $media->id);
				$db->setQuery($query);

				if (!$db->execute())
				{
					return JText::_('JBS_ADM_ERROR_OCCURED');
				}
			}
		}

		return $msg . ' ' . $account;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   ?
	 * @param   string  $direction  ?
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since    1.7.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$this->setState('message', $app->getUserState('com_biblestudy.message'));
		$this->setState('extension_message', $app->getUserState('com_biblestudy.extension_message'));
		$app->setUserState('com_biblestudy.message', '');
		$app->setUserState('com_biblestudy.extension_message', '');
		parent::populateState();
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   TableAdmin  $table  A JTable object.
	 *
	 * @return   void
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		// Reorder the articles within the category so the new article is first
		if (empty($table->id))
		{
			$table->id = 1;
		}
	}

	/**
	 * Load Form Date
	 *
	 * @return object
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_biblestudy.edit.admin.data', array());

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
