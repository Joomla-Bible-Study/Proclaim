<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Import library dependencies for database
JLoader::register('InstallerModel', JPATH_ADMINISTRATOR . '/components/com_installer/models/extension.php');
JLoader::register('Com_BiblestudyInstallerScript', JPATH_ADMINISTRATOR . '/components/com_biblestudy/biblestudy.script.php');
use \Joomla\Registry\Registry;

/**
 * Admin admin model class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelAdmin extends JModelAdmin
{

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_BIBLESTUDY';

	/**
	 * Context
	 *
	 * @var string
	 */
	protected $_context = 'com_biblestudy.discover';

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
	 */
	public function getForm($data = array(), $loadData = true)
	{
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
	 * Get Media Image
	 *
	 * @todo may not be used
	 *
	 * @return void
	public function getMediaImages()
	{
		$mediafiles = $this->getMediaFiles();

		$images = new stdClass;

		foreach ($mediafiles as $mediafile)
		{
			$reg = new Registry;
			$reg->loadString($mediafile->params);
			$image = $mediafile->params->get('media_image');
			$imagecount = substr_count($image,'png');
		}
	}
	 *
	 */

	/**
	 * Get Media Files
	 *
	 * @return mixed
	 *
	 * @todo not sure if this should be here.
	 */
	public function getMediaFiles()
	{
		$db              = JFactory::getDbo();
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
	 */
	public function fix()
	{
		if (!$changeSet = $this->getItems())
		{
			return false;
		}
		$changeSet->fix();
		$this->fixSchemaVersion();
		$this->fixUpdateVersion();
		$installer = new BiblestudyModelInstall;
		$installer->deleteUnexistingFiles();
		$installer->fixMenus();
		$installer->fixemptyaccess();
		$installer->fixemptylanguage();
		$this->fixDefaultTextFilters();

		return true;
	}

	/**
	 * Gets the changeset object
	 *
	 * @return string JSchema  Changeset
	 */
	public function getItems()
	{
		$folder = JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/sql/updates/';

		try
		{
			$changeSet = JSchemaChangeset::getInstance(JFactory::getDbo(), $folder);
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

			return false;
		}

		return $changeSet;
	}

	/**
	 * Fix schema version if wrong
	 *
	 * @return   mixed  string schema version if success, false if fail
	 */
	public function fixSchemaVersion()
	{
		// Get correct schema version -- last file in array
		$schema          = $this->getCompVersion();
		$db              = JFactory::getDbo();
		$result          = false;
		$extensionresult = $this->getExtentionId();

		// Check value. If ok, don't do update
		$version = $this->getSchemaVersion();

		if ($version == $schema)
		{
			$result = $version;
		}
		else
		{
			// Delete old row
			$query = $db->getQuery(true);
			$query->delete($db->qn('#__schemas'));
			$query->where($db->qn('extension_id') . ' = ' . $db->q($extensionresult));
			$db->setQuery($query);
			$db->execute();

			// Add new row
			$query = $db->getQuery(true);
			$query->insert($db->qn('#__schemas'));
			$query->set($db->qn('extension_id') . '= ' . $db->q($extensionresult));
			$query->set($db->qn('version_id') . '= ' . $db->q($schema));
			$db->setQuery($query);

			if ($db->execute())
			{
				$result = $schema;
			}
		}

		return $result;
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
		$xml      = null;
		$file     = JPATH_COMPONENT_ADMINISTRATOR . '/biblestudy.xml';
		$xml      = simplexml_load_file($file, 'JXMLElement');

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
	 * @since 7.1.0
	 * @throws Exception
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
	 */
	public function fixUpdateVersion()
	{
		$table = JTable::getInstance('Extension');
		$table->load($this->getExtentionId());
		$cache         = new Registry($table->manifest_cache);
		$updateVersion = $cache->get('version');

		if ($updateVersion == $this->getCompVersion())
		{
			return $updateVersion;
		}
		else
		{
			$cache->set('version', $this->getCompVersion());
			$table->manifest_cache = $cache->toString();

			if ($table->store())
			{
				return $this->getCompVersion();
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Check if com_biblestudy parameters are blank. If so, populate with com_content text filters.
	 *
	 * @return  mixed  boolean true if params are updated, null otherwise
	 */
	public function fixDefaultTextFilters()
	{
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
	 */
	public function getPagination()
	{
		return true;
	}

	/**
	 * Get current version from #__extensions table
	 *
	 * @return  mixed   version if successful, false if fail
	 */
	public function getUpdateVersion()
	{
		$table = JTable::getInstance('Extension');
		$table->load($this->getExtentionId());
		$cache = new Registry($table->manifest_cache);

		return $cache->get('version');
	}

	/**
	 * Check if com_biblestudy parameters are blank.
	 *
	 * @return  string  default text filters (if any)
	 */
	public function getDefaultTextFilters()
	{
		$table = JTable::getInstance('Extension');
		$table->load($table->find(array('name' => 'com_biblestudy')));

		return $table->params;
	}

	/**
	 * Check for SermonSpeaker and PreachIt
	 *
	 * @return object
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
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   ?
	 * @param   string  $direction  ?
	 *
	 * @return  void
	 *
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
	 * @param   JTable  $table  A JTable object.
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
