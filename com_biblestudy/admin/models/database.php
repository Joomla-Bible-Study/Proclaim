<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * @since      9.0.13
 * */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

// Import library dependencies
JLoader::register('InstallerModel', JPATH_ADMINISTRATOR . '/components/com_installer/models/extension.php');
JLoader::register('BiblestudyInstallerScript', JPATH_COMPONENT_ADMINISTRATOR . 'biblestudy.script.php');

/**
 * Database Manage Model
 *
 * @package  BibleStudy.Admin
 * @since    9.0.13
 */
class BiblestudyModelDatabase extends InstallerModel
{
	/**
	 * Context of model
	 *
	 * @var string
	 * @since    9.0.13
	 */
	protected $context = 'com_biblestudy.discover';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Ordering
	 * @param   string  $direction  Direction of the list
	 *
	 * @since    9.0.13
	 * @return void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$this->setState('message', $app->getUserState('com_biblestudy.message'));
		$this->setState('extension_message', $app->getUserState('com_biblestudy.extension_message'));
		$app->setUserState('com_biblestudy.message', '');
		$app->setUserState('com_biblestudy.extension_message', '');
		parent::populateState('name', 'asc');
	}

	/**
	 * Fixes database problems
	 *
	 * @return void
	 *
	 * @since    9.0.13
	 */
	public function fix()
	{
		$changeSet = $this->getItems();
		$changeSet->fix();
		$this->fixSchemaVersion($changeSet);
		$this->fixUpdateVersion();
		$this->fixDefaultTextFilters();
	}

	/**
	 * Gets the changeset object
	 *
	 * @return  JSchemaChangeset
	 *
	 * @since    9.0.13
	 */
	public function getItems()
	{
		$folder    = JPATH_COMPONENT_ADMINISTRATOR . '/sql/updates/';
		$changeSet = JSchemaChangeset::getInstance(JFactory::getDbo(), $folder);

		return $changeSet;
	}

	/**
	 * Overrides Pagination
	 *
	 * @return boolean
	 *
	 * @since    9.0.13
	 */
	public function getPagination()
	{
		return true;
	}

	/**
	 * Get version from #__schemas table
	 *
	 * @return  mixed  the return value from the query, or null if the query fails
	 *
	 * @throws Exception
	 * @since    9.0.13
	 */
	public function getSchemaVersion()
	{
		$db              = JFactory::getDbo();
		$query           = $db->getQuery(true);
		$extensionresult = $this->getExtentionId();
		$query->select('version_id')->from($db->qn('#__schemas'))
			->where('extension_id = "' . $extensionresult . '"');
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Fix schema version if wrong
	 *
	 * @param   JSchemaChangeSet  $changeSet  ??
	 *
	 * @return   mixed  string schema version if success, false if fail
	 *
	 * @since    9.0.13
	 */
	public function fixSchemaVersion($changeSet)
	{
		// Get correct schema version -- last file in array
		$schema          = $changeSet->getSchema();
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
			$query->where($db->qn('extension_id') . ' = "' . $extensionresult . '"');
			$db->setQuery($query);
			$db->execute();

			// Add new row
			$query = $db->getQuery(true);
			$query->insert($db->qn('#__schemas'));
			$query->set($db->qn('extension_id') . '= "' . $extensionresult . '"');
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
	 * Get current version from #__extensions table
	 *
	 * @return  mixed   version if successful, false if fail
	 *
	 * @since    9.0.13
	 */
	public function getUpdateVersion()
	{
		/** @var TableAdmin $table */
		$table = JTable::getInstance('Extension');
		$table->load($this->getExtentionId());
		$cache = new Registry($table->manifest_cache);

		return $cache->get('version');
	}

	/**
	 * Fix Joomla version in #__extensions table if wrong (doesn't equal JVersion short version)
	 *
	 * @return   mixed  string update version if success, false if fail
	 *
	 * @since    9.0.13
	 */
	public function fixUpdateVersion()
	{
		/** @var TableAdmin $table */
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
	 * Check if com_churchdirectory parameters are blank.
	 *
	 * @return  string  default text filters (if any)
	 *
	 * @since    1.7.0
	 */
	public function getDefaultTextFilters()
	{
		/** @var TableAdmin $table */
		$table = JTable::getInstance('Extension');
		$table->load($table->find(['name' => 'com_biblestudy']));

		return $table->params;
	}

	/**
	 * Check if com_churchdirectory parameters are blank. If so, populate with com_content text filters.
	 *
	 * @return  mixed  boolean true if params are updated, null otherwise
	 *
	 * @since    1.7.0
	 */
	public function fixDefaultTextFilters()
	{
		/** @var TableAdmin $table */
		$table = JTable::getInstance('Extension');
		$table->load($table->find(['name' => 'com_biblestudy']));

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
	 * To retreave component extention_id
	 *
	 * @return int
	 *
	 * @since 7.1.0
	 * @throws Exception
	 */
	public function getExtentionId()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id')->from($db->qn('#__extensions'))
			->where('element = "com_biblestudy"');
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * To retreave component version
	 *
	 * @return string Version of component
	 *
	 * @since 1.7.3
	 */
	public function getCompVersion()
	{
		$file     = JPATH_COMPONENT_ADMINISTRATOR . '/biblestudy.xml';
		/** @var object $xml */
		$xml      = simplexml_load_file($file, 'JXMLElement');
		$jversion = (string) $xml->version;

		return $jversion;
	}
}
