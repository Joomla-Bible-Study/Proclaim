<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;

JLoader::register('Com_BiblestudyInstallerScript', JPATH_ADMINISTRATOR . '/components/com_biblestudy/biblestudy.script.php');
JLoader::register('MigrationUpgrade', JPATH_ADMINISTRATOR . '/components/com_biblestudy/migration/updateALL.php');


/**
 * class Migration model
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */
class BibleStudyModelMigration extends JModelLegacy
{
	/**
	 * Set start Time
	 *
	 * @var float The time the process started
	 */
	private $_startTime = null;

	/** @var array The pre versions to process */
	private $_versionStack = array();

	/** @var int Total numbers of Versions */
	public $totalVersions = 0;

	/** @var int Numbers of Versions already processed */
	public $doneVersions = 0;

	/** @var array Call stack for the Visioning System. */
	public $callstack = array();

	/** @var string Version of BibleStudy */
	private $_versionSwitch = null;

	/** @var int Id of Extinction Table */
	private $_biblestudyEid = 0;

	/** @var string Path to Mysql files */
	protected $filePath = '/components/com_biblestudy/install/sql/updates/mysql';

	/** @var array Array of SQL files to parse. */
	private $_filesStack = array();

	/** @var array Array of PHP Function to parse. */
	private $_afterStack = array();

	/**
	 * Returns the current timestamps in decimal seconds
	 *
	 * @return string
	 */
	private function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());

		return ((float) $usec + (float) $sec);
	}

	/**
	 * Starts or resets the internal timer
	 *
	 * @return void
	 */
	private function resetTimer()
	{
		$this->_startTime = $this->microtime_float();
	}

	/**
	 * Makes sure that no more than 3 seconds since the start of the timer have elapsed
	 *
	 * @return bool
	 */
	private function haveEnoughTime()
	{
		$now     = $this->microtime_float();
		$elapsed = abs($now - $this->_startTime);

		return $elapsed < 3;
	}

	/**
	 * Saves the Versions/SQL/After stack in the session
	 *
	 * @return void
	 */
	private function saveStack()
	{
		$stack = array(
			'version' => $this->_versionStack,
			'files'   => $this->_filesStack,
			'after'   => $this->_afterStack,
			'total'   => $this->totalVersions,
			'done'    => $this->doneVersions,
		);
		$stack = json_encode($stack);

		if (function_exists('base64_encode') && function_exists('base64_decode'))
		{
			if (function_exists('gzdeflate') && function_exists('gzinflate'))
			{
				$stack = gzdeflate($stack, 9);
			}
			$stack = base64_encode($stack);
		}
		$session = JFactory::getSession();
		$session->set('migration_stack', $stack, 'biblestudy');
	}

	/**
	 * Resets the Versions/SQL/After stack saved in the session
	 *
	 * @return void
	 */
	private function resetStack()
	{
		$session = JFactory::getSession();
		$session->set('migration_stack', '', 'biblestudy');
		$this->_versionStack = array();
		$this->_filesStack   = array();
		$this->_afterStack   = array();
		$this->totalVersions = 0;
		$this->doneVersions  = 0;
	}

	/**
	 * Loads the Versions/SQL/After stack from the session
	 *
	 * @return void
	 */
	private function loadStack()
	{
		$session = JFactory::getSession();
		$stack   = $session->get('migration_stack', '', 'biblestudy');

		if (empty($stack))
		{
			$this->_versionStack = array();
			$this->_filesStack   = array();
			$this->_afterStack   = array();
			$this->totalVersions = 0;
			$this->doneVersions  = 0;

			return;
		}

		if (function_exists('base64_encode') && function_exists('base64_decode'))
		{
			$stack = base64_decode($stack);

			if (function_exists('gzdeflate') && function_exists('gzinflate'))
			{
				$stack = gzinflate($stack);
			}
		}
		$stack = json_decode($stack, true);

		$this->_versionStack = $stack['version'];
		$this->_filesStack   = $stack['files'];
		$this->_afterStack   = $stack['after'];
		$this->totalVersions = $stack['total'];
		$this->doneVersions  = $stack['done'];

	}

	/**
	 * Start Looking though the Versions
	 *
	 * @return bool
	 */
	public function startScanning()
	{
		$this->resetStack();
		$this->resetTimer();
		$this->getVersions();
		$this->getSqlFiles();
		$this->getAfter();

		if (empty($this->_versionStack))
		{
			$this->_versionStack = array();
		}
		if (empty($this->_filesStack))
		{
			$this->_filesStack = array();
		}
		if (empty($this->_afterStack))
		{
			$this->_afterStack = array();
		}
		ksort($this->_filesStack);
		ksort($this->_versionStack);
		ksort($this->_afterStack);

		$this->saveStack();

		return true;
	}

	/**
	 *  Run the Migration will there is time.
	 *
	 * @param   bool $resetTimer  If the time must be reset
	 *
	 * @return bool
	 */
	public function run($resetTimer = true)
	{
		if ($resetTimer)
		{
			$this->resetTimer();
		}

		$this->loadStack();
		$result = true;

		while ($result && $this->haveEnoughTime())
		{
			$result = $this->RealRun();
		}

		$this->saveStack();

		return $result;
	}

	/**
	 * Start the Run through the Pre Versions then SQL files then After PHP functions.
	 *
	 * @return bool
	 */
	private function RealRun()
	{
		if (!empty($this->_versionStack))
		{
			while (!empty($this->_versionStack) && $this->haveEnoughTime())
			{
				$version = array_pop($this->_versionStack);
				$this->doneVersions++;
				$this->doVersionUpdate($version);
			}
		}

		if (empty($this->_versionStack) && !empty($this->_filesStack))
		{
			while (!empty($this->_filesStack) && $this->haveEnoughTime())
			{
				$files = array_pop($this->_filesStack);
				$this->doneVersions++;
				$this->allUpdate($files);
			}
		}

		if (empty($this->_versionStack) && empty($this->_filesStack) && !empty($this->_afterStack))
		{
			while (!empty($this->_afterStack) && $this->haveEnoughTime())
			{
				$versions = array_pop($this->_afterStack);
				$this->doneVersions++;
				$this->doVersionUpdate($versions);
			}
		}

		if (empty($this->_filesStack) && empty($this->_versionStack) && empty($this->_afterStack))
		{
			// Just finished
			$this->resetStack();
			$this->finish();

			return false;
		}

		// If we have more Versions or SQL files, continue in the next step
		return true;
	}

	/**
	 * Get migrate versions of DB after import/copy has finished.
	 *
	 * @return boolean
	 */
	public function getVersions()
	{
		$db               = JFactory::getDBO();
		$olderversiontype = 0;

		// First we check to see if there is a current version database installed. This will have a #__bsms_version table so we check for it's existence.
		// Check to be sure a really early version is not installed $versiontype: 1 = current version type 2 = older version type 3 = no version

		$tables         = $db->getTableList();
		$prefix         = $db->getPrefix();
		$versiontype    = '';
		$currentversion = false;
		$oldversion     = false;

		// Check to see if version is newer then 7.0.2
		foreach ($tables as $table)
		{
			$studies              = $prefix . 'bsms_update';
			$currentversionexists = substr_count($table, $studies);

			if ($currentversionexists > 0)
			{
				$currentversion = true;
				$versiontype    = 1;
			}
		}
		if ($versiontype !== 1)
		{
			foreach ($tables as $table)
			{
				$studies              = $prefix . 'bsms_version';
				$currentversionexists = substr_count($table, $studies);

				if ($currentversionexists > 0)
				{
					$currentversion = true;
					$versiontype    = 2;
				}
			}
		}

		// Only move forward if a current version type is not found
		if (!$currentversion)
		{
			// Now let's check to see if there is an older database type (prior to 6.2)
			$oldversion = false;

			foreach ($tables as $table)
			{
				$studies          = $prefix . 'bsms_schemaVersion';
				$oldversionexists = substr_count($table, $studies);

				if ($oldversionexists > 0)
				{
					$oldversion       = true;
					$olderversiontype = 1;
					$versiontype      = 3;
				}
			}
			if (!$oldversion)
			{
				foreach ($tables as $table)
				{
					$studies            = $prefix . 'bsms_schemaversion';
					$olderversionexists = substr_count($table, $studies);

					if ($olderversionexists > 0)
					{
						$oldversion       = true;
						$olderversiontype = 2;
						$versiontype      = 3;
					}
				}
			}
		}

		// Finally if both current version and old version are false, we double check to make sure there are no JBS tables in the database.
		if (!$currentversion && !$oldversion)
		{
			foreach ($tables as $table)
			{
				$studies   = $prefix . 'bsms_studies';
				$jbsexists = substr_count($table, $studies);

				if (!$jbsexists)
				{
					$versiontype = 5;
				}
				if ($jbsexists > 0)
				{
					$versiontype = 4;
				}
			}
		}

		$this->callstack['versionttype'] = $versiontype;

		// Now we run a switch case on the VersionType and run an install routine accordingly
		switch ($versiontype)
		{
			case 1:
				self::correctVersions();
				/* Find Last updated Version in Update table */
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__bsms_update')
					->order($db->qn('version') . ' desc');
				$db->setQuery($query);
				$updates              = $db->loadObject();
				$version              = $updates->version;
				$this->_versionSwitch = $version;

				$this->callstack['subversiontype1_version'] = $version;
				break;
			case 2:
				// This is a current database version so we check to see which version. We query to get the highest build in the version table
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__bsms_version')
					->order('build desc');
				$db->setQuery($query);
				$db->query();
				$version = $db->loadObject();

				$this->callstack['subversiontype2_version'] = $version->build;

				switch ($version->build)
				{
					case '700':
						$this->_versionStack = array('upgrade701');
						break;

					case '624':
						$this->_versionStack = array('upgrade700', 'upgrade701');
						break;

					case '623':
						$this->_versionStack = array('upgrade623', 'upgrade700', 'upgrade701');
						break;

					case '622':
						$this->_versionStack = array('upgrade622', 'upgrade623', 'upgrade700', 'upgrade701');
						break;

					case '615':
						$this->_versionStack = array('upgrade622', 'upgrade623', 'upgrade700', 'upgrade701');
						break;

					case '614':
						$this->_versionStack = array('upgrade614', 'upgrade622', 'upgrade623', 'upgrade700', 'upgrade701');
						break;

					case null:
						$this->setState('scanerror', 'Bad DB Upload');

						return false;
						break;
				}
				break;

			case 3:
				$query = $db->getQuery(true);

				// This is an older version of the software so we check it's version
				if ($olderversiontype == 1)
				{
					$query->select('schemaVersion')->from('#__bsms_schemaVersion');
				}
				else
				{
					$query->select('schemaVersion')->from('#__bsms_schemaversion');
				}
				$db->setQuery($query);
				$schema = $db->loadResult();

				$this->callstack['subversiontype3_version'] = $schema;

				switch ($schema)
				{
					case '600':
						$this->setState('scanerror', JText::_('JBS_IBM_VERSION_TOO_OLD'));

						return false;
						break;

					case '608':
						$this->_versionStack = array('upgrade611', 'upgrade613', 'upgrade614', 'upgrade622', 'upgrade623', 'upgrade700', 'upgrade701');
						break;

					case '611':
						$this->_versionStack = array('upgrade613', 'upgrade614', 'upgrade622', 'upgrade623', 'upgrade700', 'upgrade701');
						break;

					case '613':
						$this->_versionStack = array('upgrade614', 'upgrade622', 'upgrade623', 'upgrade700', 'upgrade701');
						break;
				}
				break;

			case 4:

				$this->callstack['subversiontype4_version'] = JText::_('JBS_IBM_VERSION_TOO_OLD');

				// There is a version installed, but it is older than 6.0.8 and we can't upgrade it
				$this->setState('scanerror', JText::_('JBS_IBM_VERSION_TOO_OLD'));

				return false;
				break;
		}

		if (!empty($this->_versionStack))
		{
			$this->totalVersions += count($this->_versionStack);
		}

		return true;
	}

	/**
	 * Get Sql Files Array of file in the update folder
	 *
	 * @return bool
	 */
	private function getSqlFiles()
	{
		$db  = JFactory::getDBO();
		$app = JFactory::getApplication();

		// Start of Building the All state build.
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$files = str_replace('.sql', '', JFolder::files(JPATH_ADMINISTRATOR . $this->filePath, '\.sql$'));
		usort($files, 'version_compare');

		/* Find Extension ID of component */
		$query = $db->getQuery(true);
		$query
			->select('extension_id')
			->from('#__extensions')
			->where('`name` = "com_biblestudy"');
		$db->setQuery($query);
		$eid                  = $db->loadResult();
		$this->_biblestudyEid = $eid;

		foreach ($files as $i => $value)
		{

			$update = $this->_versionSwitch;

			if ($update && $eid)
			{
				/* Set new Schema Version */
				$this->setSchemaVersion($update, $eid);
			}
			else
			{
				$value = '7.0.0';
			}

			if (version_compare($value, $update) <= 0)
			{
				unset($files[$i]);
			}
			elseif ($files)
			{
				$this->totalVersions += count($files);
				$files             = array_reverse($files);
				$this->_filesStack = $files;
			}
			else
			{
				$app->enqueueMessage(JText::_('JBS_INS_NO_UPDATE_SQL_FILES'), 'warning');

				return false;
			}
		}

		return true;
	}

	/**
	 * Get After Array for thing that cannot be don in SQL
	 *
	 * @return void
	 */
	private function getAfter()
	{
		if (empty($this->_afterStack))
		{
			$this->_afterStack = array('0' => 'upgrade710', '1' => 'upgrade800');
			$this->totalVersions += count($this->_afterStack);
		}
	}

	/**
	 * System to Update based on versions
	 *
	 * @param   string $version  Version to update
	 *
	 * @return boolean
	 */
	private function doVersionUpdate($version)
	{
		$migration = new MigrationUpgrade;

		if (call_user_func(array($migration, $version)))
		{
			return true;
		}
		JFactory::getApplication()->enqueueMessage('Version did not update ' . $version, 'error');

		die($version);
	}

	/**
	 * Correct problem in are update table under 7.0.2 systems
	 *
	 * @return boolean
	 */
	public static function correctVersions()
	{
		$db = JFactory::getDBO();
		/* Find Last updated Version in Update table */
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__bsms_update');
		$db->setQuery($query);
		$updates = $db->loadObjectlist();

		foreach ($updates AS $value)
		{
			/* Check to see if Bad version is in key 3 */

			if (($value->id === '3') && ($value->version !== '7.0.1.1'))
			{
				/* Find Last updated Version in Update table */
				$query = "INSERT INTO `#__bsms_update` (id,version) VALUES (3,'7.0.1.1')
                            ON DUPLICATE KEY UPDATE version= '7.0.1.1';";
				$db->setQuery($query);

				if (!$db->execute())
				{
					JFactory::getApplication()->enqueueMessage(JText::_('JBS_CMN_OPERATION_FAILED'), 'error');

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Function to do updates after 7.0.2 using the SQL Stack
	 *
	 * @param   string $value  The File to run sql.
	 *
	 * @return boolean
	 *
	 * @since 7.0.4
	 */
	protected function allUpdate($value)
	{
		$db  = JFactory::getDbo();
		$app = JFactory::getApplication();

		$buffer = file_get_contents(JPATH_ADMINISTRATOR . $this->filePath . '/' . $value . '.sql');

		// Graceful exit and rollback if read not successful
		if ($buffer === false)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_SQL_READBUFFER'), JLog::WARNING, 'jerror');

			return false;
		}

		// Create an array of queries from the sql file
		$queries = $db->splitSql($buffer);

		if (count($queries) == 0)
		{
			// No queries to process
			return 0;
		}

		// Process each query in the $queries array (split out of sql file).
		foreach ($queries as $query)
		{
			$query = trim($query);

			if ($query != '' && $query{0} != '#')
			{
				$db->setQuery($query);

				if (!$db->execute())
				{
					$app->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', ''), 'error');

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Set the schema version for an extension by looking at its latest update
	 *
	 * @param   string  $version  Version number
	 * @param   integer $eid      Extension ID
	 *
	 * @return  void
	 *
	 * @since   7.1.0
	 */

	private function setSchemaVersion($version, $eid)
	{
		$app = JFactory::getApplication();

		if ($version && $eid)
		{
			$db = JFactory::getDBO();

			// Update the database
			$query = $db->getQuery(true);
			$query
				->delete()
				->from('#__schemas')
				->where('extension_id = ' . $eid);
			$db->setQuery($query);

			if ($db->execute())
			{
				$query->clear();
				$query->insert($db->quoteName('#__schemas'));
				$query->columns(array($db->quoteName('extension_id'), $db->quoteName('version_id')));
				$query->values($eid . ', ' . $db->quote($version));
				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				$app->enqueueMessage('Could not locate extension id in schemas table');
			}
		}
	}

	/**
	 * Returns Update Version form Table
	 *
	 * @return string Returns the Last Version in the #_bsms_update table
	 */
	private function getUpdateVersion()
	{
		$db = JFactory::getDbo();

		/* Find Last updated Version in Update table */
		$query = $db->getQuery(true);
		$query
			->select('version')
			->from('#__bsms_update');
		$db->setQuery($query);
		$updates = $db->loadObjectList();
		$update  = end($updates);

		return $update->version;
	}

	/**
	 * Finish the system
	 *
	 * @return boolean
	 */
	private function finish()
	{
		$app    = JFactory::getApplication();
		$update = $this->getUpdateVersion();

		if ($update)
		{
			/* Set new Schema Version */
			$this->setSchemaVersion($update, $this->_biblestudyEid);
			$app->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . JText::_('JBS_IBM_REVIEW_ADMIN_TEMPLATE'), 'message');

			// Final step is to fix assets
			$assets = new JBSMAssets;
			$assets->fixAssets();
			$installer = new Com_BiblestudyInstallerScript;
			$installer->fixMenus();
			$installer->fixImagePaths();
			$installer->fixemptyaccess();
			$installer->fixemptylanguage();

			return true;
		}
		else
		{
			JBSMDbHelper::resetdb();
			$app->enqueueMessage(JText::_('JBS_CMN_DATABASE_NOT_MIGRATED'), 'warning');

			return false;
		}
	}

}
