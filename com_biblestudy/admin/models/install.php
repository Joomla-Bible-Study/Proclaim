<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;
// Include the JLog class.
jimport('joomla.log.log');
JLog::addLogger(
	array(
		'text_file' => 'com_biblestudy.errors.php'
	),
	JLog::ALL,
	'com_biblestudy'
);
JLoader::register('Com_BiblestudyInstallerScript', JPATH_ADMINISTRATOR . '/components/com_biblestudy/biblestudy.script.php');
JLoader::register('JBSMFreshInstall', JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/biblestudy.install.special.php');

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_biblestudy/api.php';

if (file_exists($api))
{
	require_once $api;
}

/**
 * class Migration model
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */
class BibleStudyModelInstall extends JModelLegacy
{
	/** @var int Total numbers of Versions */
	public $totalSteps = 0;

	/** @var string Running Now */
	public $running = null;

	/** @var int Numbers of Versions already processed */
	public $doneSteps = 0;

	/** @var int Total numbers of Versions */
	public $totalStepsSub = 0;

	/** @var string Running Now */
	public $runningSub = null;

	/** @var int Numbers of Versions already processed */
	public $doneStepsSub = 0;

	/** @var array Call stack for the Visioning System. */
	public $callstack = array();

	/** @var string Path to Mysql files */
	protected $filePath = '/components/com_biblestudy/install/sql/updates/mysql';

	/** @var string Path to PHP Version files */
	protected $phpPath = '/components/com_biblestudy/install/updates/';

	/** @var float The time the process started */
	private $_startTime = null;

	/** @var array The pre versions to process */
	private $_versionStack = array();

	/** @var array The pre versions sub sql array to process */
	private $_allupdates = array();

	/** @var string Version of BibleStudy */
	private $_versionSwitch = null;

	/** @var int Id of Extinction Table */
	private $_biblestudyEid = 0;

	/** @var array Array of Finish Task */
	private $_finish = array();

	/** @var array Array of Install Task */
	private $_install = array();

	/** @var int If was inported */
	private $_isimport = 0;

	/** @type string Type of process */
	private $type = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		if (JFactory::getApplication()->input->get('task') == 'browse')
		{
			$this->browse();
		}

		$this->name = 'install';
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
		$this->getSteps();
		$this->postinstallclenup();

		if (empty($this->_versionStack))
		{
			$this->_versionStack = array();
		}
		ksort($this->_versionStack);

		$this->saveStack();

		if (!$this->haveEnoughTime())
		{
			return true;
		}
		else
		{
			return $this->run(false);
		}
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
	 * Get migrate versions of DB after import/copy has finished.
	 *
	 * @return boolean
	 */
	private function getSteps()
	{
		$olderversiontype = 0;
		$app              = JFactory::getApplication();
		$check            = JBSMDbHelper::getInstallState();

		// Set Finishing Steps
		$this->_finish = array('updateversion', 'fixassets', 'fixmenus', 'fixemptyaccess', 'fixemptylanguage', 'rmoldurl', 'setupdateurl', 'finish');
		$this->totalSteps += count($this->_finish);

		// Check to see if this is not a migration before proceding.
		if ($this->type != 'migration' && $check)
		{
			$this->type     = 'install';
			$this->_install = array(0 => 'installdb');
			$this->totalSteps += count($this->_install);

			return true;

		}
		elseif ($check !== true)
		{
			$this->type = 'migration';
		}

		// First we check to see if there is a current version database installed. This will have a #__bsms_version table so we check for it's existence.
		// Check to be sure a really early version is not installed $versiontype: 1 = current version type 2 = older version type 3 = no version

		$tables         = $this->_db->getTableList();
		$prefix         = $this->_db->getPrefix();
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
				$query = $this->_db->getQuery(true);
				$query->select('*')
					->from('#__bsms_update')
					->order($this->_db->qn('version') . ' desc');
				$this->_db->setQuery($query);
				$updates              = $this->_db->loadObject();
				$version              = $updates->version;
				$this->_versionSwitch = $version;

				$this->callstack['subversiontype_version'] = $version;
				break;
			case 2:
				// This is a current database version so we check to see which version. We query to get the highest build in the version table
				$query = $this->_db->getQuery(true);
				$query->select('*')
					->from('#__bsms_version')
					->order('build desc');
				$this->_db->setQuery($query);
				$this->_db->execute();
				$version = $this->_db->loadObject();

				$this->_versionSwitch = implode('.', preg_split('//', $version->build, -1, PREG_SPLIT_NO_EMPTY));

				$this->callstack['subversiontype_version'] = $version->build;
				break;

			case 3:
				$query = $this->_db->getQuery(true);

				// This is an older version of the software so we check it's version
				if ($olderversiontype == 1)
				{
					$query->select('schemaVersion')->from('#__bsms_schemaVersion');
				}
				else
				{
					$query->select('schemaVersion')->from('#__bsms_schemaversion');
				}
				$this->_db->setQuery($query);
				$schema = $this->_db->loadResult();

				$this->_versionSwitch = implode('.', preg_split('//', $schema, -1, PREG_SPLIT_NO_EMPTY));

				$this->callstack['subversiontype_version'] = $schema;
				break;

			case 4:
				$this->callstack['subversiontype_version'] = JText::_('JBS_IBM_VERSION_TOO_OLD');

				// There is a version installed, but it is older than 6.0.8 and we can't upgrade it
				$this->setState('scanerror', JText::_('JBS_IBM_VERSION_TOO_OLD'));

				return false;
				break;
		}

		if ($this->callstack['subversiontype_version'] > 000)
		{

			// Start of Building the All state build.
			jimport('joomla.filesystem.folder');
			jimport('joomla.filesystem.file');

			$files = str_replace('.sql', '', JFolder::files(JPATH_ADMINISTRATOR . $this->filePath, '\.sql$'));
			$php   = str_replace('.php', '', JFolder::files(JPATH_ADMINISTRATOR . $this->phpPath, '\.php$'));
			usort($files, 'version_compare');
			usort($php, 'version_compare');

			/* Find Extension ID of component */
			$query = $this->_db->getQuery(true);
			$query
				->select('extension_id')
				->from('#__extensions')
				->where('`name` = "com_biblestudy"');
			$this->_db->setQuery($query);
			$eid                  = $this->_db->loadResult();
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
					$this->totalSteps    += count($files);
					$this->_versionStack = $files;
				}
				else
				{
					$app->enqueueMessage(JText::_('JBS_INS_NO_UPDATE_SQL_FILES'), 'warning');

					return false;
				}
			}

			foreach ($php as $i => $value)
			{
				if (version_compare($value, $this->_versionSwitch) <= 0)
				{
					unset($php[$i]);
				}
			}

			$this->totalSteps += count($php);
		}
		$this->_isimport = JFactory::getApplication()->input->getInt('jbsmalt', 0);
		$this->totalSteps += 1;

		return true;
	}

	/**
	 * Correct problem in are update table under 7.0.2 systems
	 *
	 * @return boolean
	 */
	private function correctVersions()
	{
		/* Find Last updated Version in Update table */
		$query = $this->_db->getQuery(true);
		$query->select('*')
			->from('#__bsms_update');
		$this->_db->setQuery($query);
		$updates = $this->_db->loadObjectlist();

		foreach ($updates AS $value)
		{
			/* Check to see if Bad version is in key 3 */

			if (($value->id === '3') && ($value->version !== '7.0.1.1'))
			{
				/* Find Last updated Version in Update table */
				$query = "INSERT INTO `#__bsms_update` (id,version) VALUES (3,'7.0.1.1')
                            ON DUPLICATE KEY UPDATE version= '7.0.1.1';";
				$this->_db->setQuery($query);

				if (!$this->_db->execute())
				{
					JFactory::getApplication()->enqueueMessage(JText::_('JBS_CMN_OPERATION_FAILED'), 'error');

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Set the schema version for an extension by looking at its latest update
	 *
	 * @param   string   $version  Version number
	 * @param   integer  $eid      Extension ID
	 *
	 * @return  bool
	 *
	 * @since   7.1.0
	 */
	private function setSchemaVersion($version, $eid)
	{
		$app = JFactory::getApplication();

		if ($version && $eid)
		{
			// Update the database
			$query = $this->_db->getQuery(true);
			$query
				->delete()
				->from('#__schemas')
				->where('extension_id = ' . $eid);
			$this->_db->setQuery($query);

			if ($this->_db->execute())
			{
				$query->clear();
				$query->insert($this->_db->quoteName('#__schemas'));
				$query->columns(array($this->_db->quoteName('extension_id'), $this->_db->quoteName('version_id')));
				$query->values($eid . ', ' . $this->_db->quote($version));
				$this->_db->setQuery($query);
				$this->_db->execute();

				return true;
			}
			else
			{
				$app->enqueueMessage('Could not locate extension id in schemas table');

				return false;
			}
		}

		return false;
	}

	/**
	 *  Run the Migration will there is time.
	 *
	 * @param   bool  $resetTimer  If the time must be reset
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
	 * Browse
	 *
	 * @return void
	 */
	public function browse()
	{
		$this->startScanning();
		return;
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
			'finish'  => $this->_finish,
			'install' => $this->_install,
			'total'   => $this->totalSteps,
			'done'    => $this->doneSteps,
			'run'     => $this->running,
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
		$this->_finish       = array();
		$this->_install      = array();
		$this->totalSteps    = 0;
		$this->doneSteps     = 0;
		$this->runningSub    = null;
		$this->running       = JText::_('JBS_MIG_STARTING');
	}

	/**
	 * Loads the Versions/SQL/After stack from the session
	 *
	 * @return bool
	 */
	private function loadStack()
	{
		$session = JFactory::getSession();
		$stack   = $session->get('migration_stack', '', 'biblestudy');

		if (empty($stack))
		{
			$this->_versionStack = array();
			$this->_finish       = array();
			$this->_install      = array();
			$this->totalSteps    = 0;
			$this->doneSteps     = 0;
			$this->running       = JText::_('JBS_MIG_STARTING');

			return false;
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
		$this->_finish       = $stack['finish'];
		$this->_install      = $stack['install'];
		$this->totalSteps    = $stack['total'];
		$this->doneSteps     = $stack['done'];
		$this->running       = $stack['run'];

		return true;

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

		return $elapsed < 2;
	}

	/**
	 * Start the Run through the Pre Versions then SQL files then After PHP functions.
	 *
	 * @return bool
	 */
	private function RealRun()
	{
		$app = JFactory::getApplication();
		$run = true;

		if (!empty($this->_install) && $this->haveEnoughTime())
		{
			while (!empty($this->_install))
			{
				asort($this->_install);
				$install       = array_pop($this->_install);
				$this->running = 'Install step: ' . $install;
				$this->install($install);
				$this->doneSteps++;
			}
		}

		if ($this->_isimport && $this->haveEnoughTime())
		{
			$this->fiximport();
			$this->running   = 'Fixing Imported Params';
			$this->_isimport = 0;
			$this->doneSteps++;
		}

		if (!empty($this->_versionStack) && $this->haveEnoughTime())
		{
			krsort($this->_versionStack);
			while (!empty($this->_versionStack))
			{
				$version = array_pop($this->_versionStack);
				$this->runningSub     = $version;
				$this->_allupdates    = array();
				$this->running .= ', ' . $version;
				$this->doneSteps++;
				$this->allUpdate($version);
			}
		}

		if (!empty($this->_allupdates) && $this->haveEnoughTime())
		{
			$percent = 100;
			krsort($this->_allupdates);
			while (!empty($this->_allupdates) && $run)
			{
				if ($this->totalSteps > 0)
				{
					$percent = round($this->doneSteps / $this->totalSteps * 100);
				}
				$version = array_pop($this->_allupdates);
				$this->running = $this->runningSub . ', ' . $percent . '%';
				$this->doneSteps++;
				$run = $this->runUpdates($version);
				if ($run)
				{
					$run = $this->updatePHP($version);
				}

				if ($run == false)
				{
					JBSMDbHelper::resetdb();
					$this->resetStack();
					$app->enqueueMessage(JText::_('JBS_CMN_DATABASE_NOT_MIGRATED'), 'warning');

					return false;
				}
			}
		}

		if (!empty($this->_finish) && empty($this->_versionStack) && $this->haveEnoughTime())
		{
			while (!empty($this->_finish))
			{
				$finish = array_pop($this->_finish);
				$this->doneSteps++;
				$this->running = $finish;
				$this->finish($finish);
			}
		}

		if (empty($this->_install) && empty($this->_versionStack) && empty($this->_allupdates) && empty($this->_finish))
		{
			// Just finished
			$this->resetStack();
			$this->running = JText::_('JBS_MIG_FINISHED');

			return false;
		}

		// If we have more Versions or SQL files, continue in the next step
		return true;
	}

	/**
	 * Install step system
	 *
	 * @param   string  $step  Step to perform next on install
	 *
	 * @return bool
	 */
	private function install($step)
	{

		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$path = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components/com_biblestudy/install/sql';

		// Get the two install sql files
		$files = array('install','install-defaults');

		foreach ($files as $value)
		{
			// Get file contents
			$buffer = file_get_contents($path . '/' . $value . '.sql');
			$this->runningSub = $value . '.sql';

			// Graceful exit and rollback if read not successful
			if ($buffer === false)
			{
				$app->enqueueMessage(JText::_('JBS_INS_ERROR_SQL_READBUFFER'), 'error');

				return false;
			}

			// Create an array of queries from the sql file
			$queries = $db->splitSql($buffer);

			if (count($queries) == 0)
			{
				// No queries to process
				return 0;
			}
			$this->totalSteps += count($queries);
			$this->_allupdates = array_merge($this->_allupdates, $queries);

		}

		return true;
	}

	/**
	 * Uninstall of JBSM
	 *
	 * @return bool
	 */
	public function uninstall()
	{
		// Check if JBSM can be found from the database
		$table = $this->_db->getPrefix() . 'bsms_admin';
		$this->_db->setQuery("SHOW TABLES LIKE {$this->_db->quote($table)}");
		$drop_result = '';
		if ($this->_db->loadResult())
		{
			$query = $this->_db->getQuery(true);
			$query->select('*')
				->from('#__bsms_admin')
				->where('id = 1');
			$this->_db->setQuery($query);
			$adminsettings = $this->_db->loadObject();
			$drop_tables   = $adminsettings->drop_tables;
			if ($drop_tables > 0)
			{
				// We must remove the assets manually each time
				$query = $this->_db->getQuery(true);
				$query->select('id')
					->from('#__assets')
					->where('name = ' . $this->_db->q(BIBLESTUDY_COMPONENT_NAME));
				$this->_db->setQuery($query);
				$parent_id = $this->_db->loadResult();
				$query     = $this->_db->getQuery(true);
				if ($parent_id != '0')
				{
					$query->delete()
						->from('#__assets')
						->where('parent_id = ' . $this->_db->q($parent_id))
						->where('name != ' . $this->_db->q('root.1'));
					$this->_db->setQuery($query);
					$this->_db->execute();
				}
				$query = $this->_db->getQuery(true);
				$query->delete()
					->from('#__assets')
					->where('name LIKE ' . $this->_db->q(BIBLESTUDY_COMPONENT_NAME))
					->where('name != ' . $this->_db->q('root.1'));
				$this->_db->setQuery($query);
				$this->_db->execute();
				$buffer = file_get_contents(JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/sql/uninstall-dbtables.sql');

				// Graceful exit and rollback if read not successful
				if ($buffer === false)
				{
					die('no uninstall-dbtables.sql');
				}
				$queries = $this->_db->splitSql($buffer);
				foreach ($queries as $querie)
				{
					$querie = trim($querie);
					if ($querie != '' && $querie{0} != '#' && $querie != '`')
					{
						$this->_db->setQuery($querie);
						$this->_db->execute();
					}
				}
			}
		}
		else
		{
			$drop_result = '<h3>' . JText::_('JBS_INS_NO_DATABASE_REMOVED') . '</h3>';
		}

		// Post Install Messages Cleanup for Component
		$query = $this->_db->getQuery(true);
		$query->delete('#__postinstall_messages')
			->where($this->_db->qn('language_extension') . ' = ' . $this->_db->q('com_biblestudy'));
		$this->_db->setQuery($query);
		$this->_db->execute();
		echo '<h2>' . JText::_('JBS_INS_UNINSTALLED') . ' ' . BIBLESTUDY_VERSION . '</h2> <div>' . $drop_result . '</div>';

		return true;
	}

	/**
	 * Finish the system
	 *
	 * @param   string  $step  Step to prossess
	 *
	 * @return boolean
	 */
	private function finish($step)
	{
		$app = JFactory::getApplication();
		$run = false;

		switch ($step)
		{
			case 'updateversion':
				$update = $this->getUpdateVersion();
				/* Set new Schema Version */
				$run           = $this->setSchemaVersion($update, $this->_biblestudyEid);
				$this->running = 'Update Version';
				break;
			case 'fixassets':
				// Final step is to fix assets
				$assets        = new JBSMAssets;
				$run           = $assets->fixAssets();
				$this->running = 'Fix Assets';
				break;
			case 'fixmenus':
				$run           = $this->fixMenus();
				$this->running = 'Fix Menus';
				break;
			case 'fixemptyaccess':
				$run           = $this->fixemptyaccess();
				$this->running = 'Fix Empty Access';
				break;
			case 'fixemptylanguage':
				$run           = $this->fixemptylanguage();
				$this->running = 'Fix Empty Language';
				break;
			case 'rmoldurl':
				// Removes all other update urls except package url.
				$conditions = $this->rmoldurl();
				$query = $this->_db->getQuery(true);

				$query->delete($this->_db->qn('#__update_sites'));
				$query->where($conditions, 'OR');

				$this->_db->setQuery($query);

				$this->_db->execute();
				$this->running = 'Remove Old Update URL\'s';
				break;
			case 'setupdateurl':
				$updateurl = new stdClass;
				$updateurl->name = 'Joomla Bible Study Package';
				$updateurl->type = 'collection';
				$updateurl->location = 'http://www.joomlabiblestudy.org/index.php?option=com_ars&amp;view=update&amp;task=' .
					'stream&amp;format=xml&amp;id=1&dummy=extension.xml';
				$updateurl->enabled = '1';
				$this->_db->insertObject('#__update_sites', $updateurl);
				$this->running = 'Set New Update URL';
				break;
			case 'deleteUnexistingFiles':
				// @todo this deleteUnexistingFiles should be moved to migration path and not trigerd unless.
				$this->deleteUnexistingFiles();
				$this->running = 'Cleanup Old files';
				break;
			default:
				$app->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . JText::_('JBS_IBM_REVIEW_ADMIN_TEMPLATE'), 'message');
				break;
		}

		return $run;
	}

	/**
	 * Update messages
	 *
	 * @param   object  $message  Install object
	 *
	 * @return void
	 */
	public function postinstall_messages($message)
	{
		/* Find Extension ID of component */
		$query = $this->_db->getQuery(true);
		$query
			->select('extension_id')
			->from('#__extensions')
			->where('`name` = "com_biblestudy"');
		$this->_db->setQuery($query);
		$eid                   = $this->_db->loadResult();
		$this->_biblestudyEid  = $eid;
		$message->extension_id = $this->_biblestudyEid;
		$this->_db->insertObject('#__postinstall_messages', $message);
	}

	/**
	 * Returns Update Version form Table
	 *
	 * @return string Returns the Last Version in the #_bsms_update table
	 */
	private function getUpdateVersion()
	{
		/* Find Last updated Version in Update table */
		$query = $this->_db->getQuery(true);
		$query
			->select('version')
			->from('#__bsms_update');
		$this->_db->setQuery($query);
		$updates = $this->_db->loadObjectList();
		$update  = end($updates);

		return $update->version;
	}

	/**
	 * Fix Import problem
	 *
	 * @return bool True if fix complete, False if failure
	 */
	private function fiximport()
	{
		$tables = JBSMDbHelper::getObjects();
		$set    = false;
		foreach ($tables as $table)
		{
			if (strpos($table['name'], '_bsms_timeset') == false)
			{

				$query = $this->_db->getQuery(true);
				$query->select('*')->from($table);
				$this->_db->setQuery($query);
				$data = $this->_db->loadObjectList();
				foreach ($data as $row)
				{
					if (isset($row->params))
					{
						$row->params = stripslashes($row->params);
						$set         = true;
					}
					if (isset($row->metadata))
					{
						$row->metadata = stripslashes($row->metadata);
						$set           = true;
					}
					if (isset($row->stylecode))
					{
						$row->stylecode = stripslashes($row->stylecode);
						$set            = true;
					}
					if ($set)
					{
						$this->_db->updateObject($table['name'], $row, 'id');
					}
				}
			}
		}
	}

	/**
	 * Function to update using the version number for sql files
	 *
	 * @param   string  $value  The File name.
	 *
	 * @return boolean
	 *
	 * @since 7.0.4
	 */
	private function allUpdate($value)
	{
		$buffer = file_get_contents(JPATH_ADMINISTRATOR . $this->filePath . '/' . $value . '.sql');

		// Graceful exit and rollback if read not successful
		if ($buffer === false)
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('JLIB_INSTALLER_ERROR_SQL_READBUFFER'), 'WARNING');

			return false;
		}

		// Create an array of queries from the sql file
		$queries = JDatabaseDriver::splitSql($buffer);

		if (count($queries) == 0)
		{
			// No queries to process
			return false;
		}
		$this->totalSteps += count($queries);

		$this->_allupdates = $queries;

		return true;
	}

	/**
	 * Run updates SQL array
	 *
	 * @param   array  $query  Array of SQL to proses.
	 *
	 * @return bool
	 */
	private function runUpdates($query)
	{
		$app = JFactory::getApplication();

		// Process each query in the $queries array (split out of sql file).
		$query = trim($query);

		if ($query != '' && $query{0} != '#')
		{
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				$app->enqueueMessage($this->_db->stderr(true), 'warning');

				return false;
			}
			else
			{
				$queryString = (string) $query;
				$queryString = str_replace(array("\r", "\n"), array('', ' '), substr($queryString, 0, 80));
				JLog::add(JText::sprintf('JLIB_INSTALLER_UPDATE_LOG_QUERY', $this->runningSub, $queryString), JLog::INFO, 'com_biblestudy');

			}
		}

		return true;
	}

	/**
	 * Function to update db using the version number on php files.
	 *
	 * @param   string  $value  The File name.
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	private function updatePHP($value)
	{
		// Check for corresponding PHP file and run migration
		$migration_file = JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/updates/' . $value . '.php';
		if (JFile::exists($migration_file))
		{
			require_once $migration_file;
			$migrationClass = "Migration" . str_ireplace(".", '', $value);
			$migration      = new $migrationClass;
			if (!$migration->up($this->_db))
			{
				JLog::add(JText::sprintf('Data Migration failed'), JLog::WARNING, 'com_biblestudy');

				return false;
			}

			return true;
		}

		return true;
	}

	/**
	 * Cleanup postinstall before migration
	 *
	 * @return void
	 */
	private function postinstallclenup()
	{
		// Post Install Messages Cleanup for Component
		$query = $this->_db->getQuery(true);
		$query->delete('#__postinstall_messages')
			->where($this->_db->qn('language_extension') . ' = ' . $this->_db->q('com_biblestudy'));
		$this->_db->setQuery($query);
		$this->_db->execute();
	}

	/**
	 * Remove Old Files and Folders
	 *
	 * @since      7.1.0
	 *
	 * @return   void
	 */
	protected function deleteUnexistingFiles()
	{
		$files = array(
			'/media/com_biblestudy/css/biblestudy.css.dist',
			'/images/textfile24.png',
			'/components/com_biblestudy/biblestudy.css',
			'/components/com_biblestudy/class.biblestudydownload.php',
			'/components/language/en-GB/en-GB.com_biblestudy.ini',
			'/administrator/language/en-GB/en-GB.com_biblestudy.ini',
			'/administrator/language/en-GB/en-GB.com_biblestudy.sys.ini',
			'/administrator/components/com_biblestudy/Snoopy.class.php',
			'/administrator/components/com_biblestudy/admin.biblestudy.php',
			'/components/com_biblestudy/helpers/updatesef.php',
			'/components/com_biblestudy/helpers/image.php',
			'/components/com_biblestudy/helpers/helper.php',
			'/components/com_biblestudy/views/messages/tmpl/modal16.php',
			'/components/com_biblestudy/controllers/teacherlist.php',
			'/components/com_biblestudy/controllers/teacherdisplay.php',
			'/components/com_biblestudy/controllers/studydetails.php',
			'/components/com_biblestudy/controllers/studieslist.php',
			'/components/com_biblestudy/controllers/serieslist.php',
			'/components/com_biblestudy/controllers/seriesdetail.php',
			'/components/com_biblestudy/models/teacherlist.php',
			'/components/com_biblestudy/models/teacherdisplay.php',
			'/components/com_biblestudy/models/studydetails.php',
			'/components/com_biblestudy/models/studieslist.php',
			'/components/com_biblestudy/models/seriesdetail.php',
			'/components/com_biblestudy/models/serieslist.php',
			'/components/com_biblestudy/views/mediafile/tmpl/form.php',
			'/components/com_biblestudy/views/mediafile/tmpl/form.xml',
			'/language/en-GB/en-GB.com_biblestudy.ini',
			'/language/cs-CZ/cs-CZ.com_biblestudy.ini',
			'/language/de-DE/de-DE.com_biblestudy.ini',
			'/language/es-ES/es-ES.com_biblestudy.ini',
			'/language/hu-HU/hu-HU.com_biblestudy.ini',
			'/language/nl-NL/nl-NL.com_biblestudy.ini',
			'/language/no-NO/no-NO.com_biblestudy.ini',
			'/language/en-GB/en-GB.mod_biblestudy.ini',
			'/language/en-GB/en-GB.mod_biblestudy.sys.ini',
			'/administrator/components/com_biblestudy/install/biblestudy.assets.php',
			'/administrator/components/com_biblestudy/install/sql/jbs7.0.0.sql',
			'/administrator/components/com_biblestudy/install/sql/updates/mysql/20100101.sql',
			'/administrator/components/com_biblestudy/lib/biblestudy.podcast.class.php',
			'/administrator/components/com_biblestudy/controllers/commentsedit.php',
			'/administrator/components/com_biblestudy/controllers/commentslist.php',
			'/administrator/components/com_biblestudy/controllers/cssedit.php',
			'/administrator/components/com_biblestudy/controllers/folderslist.php',
			'/administrator/components/com_biblestudy/controllers/foldersedit.php',
			'/administrator/components/com_biblestudy/controllers/folder.php',
			'/administrator/components/com_biblestudy/controllers/folders.php',
			'/administrator/components/com_biblestudy/controllers/locationslist.php',
			'/administrator/components/com_biblestudy/controllers/locationsedit.php',
			'/administrator/components/com_biblestudy/controllers/mediaedit.php',
			'/administrator/components/com_biblestudy/controllers/mediafilesedit.php',
			'/administrator/components/com_biblestudy/controllers/mediafileslist.php',
			'/administrator/components/com_biblestudy/controllers/mediaimage.php',
			'/administrator/components/com_biblestudy/controllers/mediaimages.php',
			'/administrator/components/com_biblestudy/controllers/medialist.php',
			'/administrator/components/com_biblestudy/controllers/messagetypelist.php',
			'/administrator/components/com_biblestudy/controllers/messagetypeedit.php',
			'/administrator/components/com_biblestudy/controllers/mimetypelist.php',
			'/administrator/components/com_biblestudy/controllers/mimetypeedit.php',
			'/administrator/components/com_biblestudy/controllers/mimetype.php',
			'/administrator/components/com_biblestudy/controllers/mimetypes.php',
			'/administrator/components/com_biblestudy/controllers/podcastlist.php',
			'/administrator/components/com_biblestudy/controllers/podcastedit.php',
			'/administrator/components/com_biblestudy/controllers/serieslist.php',
			'/administrator/components/com_biblestudy/controllers/seriesedit.php',
			'/administrator/components/com_biblestudy/controllers/serverslist.php',
			'/administrator/components/com_biblestudy/controllers/serversedit.php',
			'/administrator/components/com_biblestudy/controllers/sharelist.php',
			'/administrator/components/com_biblestudy/controllers/shareedit.php',
			'/administrator/components/com_biblestudy/controllers/studieslist.php',
			'/administrator/components/com_biblestudy/controllers/studiesedit.php',
			'/administrator/components/com_biblestudy/controllers/teacherlist.php',
			'/administrator/components/com_biblestudy/controllers/teacheredit.php',
			'/administrator/components/com_biblestudy/controllers/templateedit.php',
			'/administrator/components/com_biblestudy/controllers/templateslist.php',
			'/administrator/components/com_biblestudy/controllers/topicslist.php',
			'/administrator/components/com_biblestudy/controllers/topicsedit.php',
			'/administrator/components/com_biblestudy/models/forms/commentsedit.xml',
			'/administrator/components/com_biblestudy/models/forms/foldersedit.xml',
			'/administrator/components/com_biblestudy/models/forms/folder.xml',
			'/administrator/components/com_biblestudy/models/forms/locationsedit.xml',
			'/administrator/components/com_biblestudy/models/forms/mediaedit.xml',
			'/administrator/components/com_biblestudy/models/forms/mediafilesedit.xml',
			'/administrator/components/com_biblestudy/models/forms/mediaimage.xml',
			'/administrator/components/com_biblestudy/models/forms/messagetypeedit.xml',
			'/administrator/components/com_biblestudy/models/forms/mimetypeedit.xml',
			'/administrator/components/com_biblestudy/models/forms/mimetype.xml',
			'/administrator/components/com_biblestudy/models/forms/podcastedit.xml',
			'/administrator/components/com_biblestudy/models/forms/seriesedit.xml',
			'/administrator/components/com_biblestudy/models/forms/serversedit.xml',
			'/administrator/components/com_biblestudy/models/forms/shareedit.xml',
			'/administrator/components/com_biblestudy/models/forms/studiesedit.xml',
			'/administrator/components/com_biblestudy/models/forms/teacheredit.xml',
			'/administrator/components/com_biblestudy/models/forms/templateedit.xml',
			'/administrator/components/com_biblestudy/models/forms/topicsedit.xml',
			'/administrator/components/com_biblestudy/models/episodelist.php',
			'/administrator/components/com_biblestudy/models/commentsedit.php',
			'/administrator/components/com_biblestudy/models/commentslist.php',
			'/administrator/components/com_biblestudy/models/cssedit.php',
			'/administrator/components/com_biblestudy/models/folderslist.php',
			'/administrator/components/com_biblestudy/models/foldersedit.php',
			'/administrator/components/com_biblestudy/models/folder.php',
			'/administrator/components/com_biblestudy/models/folders.php',
			'/administrator/components/com_biblestudy/models/locationslist.php',
			'/administrator/components/com_biblestudy/models/locationsedit.php',
			'/administrator/components/com_biblestudy/models/mediaedit.php',
			'/administrator/components/com_biblestudy/models/mediafilesedit.php',
			'/administrator/components/com_biblestudy/models/mediafileslist.php',
			'/administrator/components/com_biblestudy/models/mediaimage.php',
			'/administrator/components/com_biblestudy/models/mediaimages.php',
			'/administrator/components/com_biblestudy/models/medialist.php',
			'/administrator/components/com_biblestudy/models/messagetypelist.php',
			'/administrator/components/com_biblestudy/models/messagetypeedit.php',
			'/administrator/components/com_biblestudy/models/mimetypelist.php',
			'/administrator/components/com_biblestudy/models/mimetypeedit.php',
			'/administrator/components/com_biblestudy/models/mimetype.php',
			'/administrator/components/com_biblestudy/models/mimetypes.php',
			'/administrator/components/com_biblestudy/models/podcastlist.php',
			'/administrator/components/com_biblestudy/models/podcastedit.php',
			'/administrator/components/com_biblestudy/models/serieslist.php',
			'/administrator/components/com_biblestudy/models/seriesedit.php',
			'/administrator/components/com_biblestudy/models/serverslist.php',
			'/administrator/components/com_biblestudy/models/serversedit.php',
			'/administrator/components/com_biblestudy/models/sharelist.php',
			'/administrator/components/com_biblestudy/models/shareedit.php',
			'/administrator/components/com_biblestudy/models/studieslist.php',
			'/administrator/components/com_biblestudy/models/studiesedit.php',
			'/administrator/components/com_biblestudy/models/teacherlist.php',
			'/administrator/components/com_biblestudy/models/teacheredit.php',
			'/administrator/components/com_biblestudy/models/templateedit.php',
			'/administrator/components/com_biblestudy/models/templateslist.php',
			'/administrator/components/com_biblestudy/models/topicslist.php',
			'/administrator/components/com_biblestudy/models/topicsedit.php',
			'/administrator/components/com_biblestudy/tables/biblestudy.php',
			'/administrator/components/com_biblestudy/tables/booksedit.php',
			'/administrator/components/com_biblestudy/tables/commentsedit.php',
			'/administrator/components/com_biblestudy/tables/foldersedit.php',
			'/administrator/components/com_biblestudy/tables/locationsedit.php',
			'/administrator/components/com_biblestudy/tables/mediaedit.php',
			'/administrator/components/com_biblestudy/tables/mediafilesedit.php',
			'/administrator/components/com_biblestudy/tables/messagetypeedit.php',
			'/administrator/components/com_biblestudy/tables/mimetypeedit.php',
			'/administrator/components/com_biblestudy/tables/podcastedit.php',
			'/administrator/components/com_biblestudy/tables/seriesedit.php',
			'/administrator/components/com_biblestudy/tables/serversedit.php',
			'/administrator/components/com_biblestudy/tables/shareedit.php',
			'/administrator/components/com_biblestudy/tables/studiesedit.php',
			'/administrator/components/com_biblestudy/tables/teacheredit.php',
			'/administrator/components/com_biblestudy/tables/topicsedit.php',
			'/administrator/components/com_biblestudy/tables/templateedit.php',
			'/administrator/components/com_biblestudy/helpers/version.php',
			'/administrator/language/en-GB/en-GB.com_biblestudy.ini',
			'/administrator/language/en-GB/en-GB.com_biblestudy.sys.ini',
			'/administrator/language/cs-CZ/cs-CZ.com_biblestudy.ini',
			'/administrator/language/cs-CZ/cs-CZ.com_biblestudy.sys.ini',
			'/administrator/language/de-DE/de-DE.com_biblestudy.ini',
			'/administrator/language/de-DE/de-DE.com_biblestudy.sys.ini',
			'/administrator/language/es-ES/es-ES.com_biblestudy.ini',
			'/administrator/language/es-ES/es-ES.com_biblestudy.sys.ini',
			'/administrator/language/hu-HU/hu-HU.com_biblestudy.ini',
			'/administrator/language/hu-HU/hu-HU.com_biblestudy.sys.ini',
			'/administrator/language/nl-NL/nl-NL.com_biblestudy.ini',
			'/administrator/language/nl-NL/no-NO.com_biblestudy.ini',
			'/administrator/language/no-NO/no-NO.com_biblestudy.sys.ini',
			// JBSM 8.0.0
			// Site:
			'/components/com_biblestudy/controllers/commentsedit.php',
			'/components/com_biblestudy/controllers/commentslist.php',
			'/components/com_biblestudy/controllers/mediafile.php',
			'/components/com_biblestudy/controllers/mediafiles.php',
			'/components/com_biblestudy/controllers/message.php',
			'/components/com_biblestudy/controllers/messages.php',
			'/components/com_biblestudy/helpers/book.php',
			'/components/com_biblestudy/helpers/date.php',
			'/components/com_biblestudy/helpers/duration.php',
			'/components/com_biblestudy/helpers/editlink.php',
			'/components/com_biblestudy/helpers/editlisting.php',
			'/components/com_biblestudy/helpers/filepath.php',
			'/components/com_biblestudy/helpers/filesize.php',
			'/components/com_biblestudy/helpers/header.php',
			'/components/com_biblestudy/helpers/listing.php',
			'/components/com_biblestudy/helpers/location.php',
			'/components/com_biblestudy/helpers/mediatable.php',
			'/components/com_biblestudy/helpers/messagetype.php',
			'/components/com_biblestudy/helpers/params.php',
			'/components/com_biblestudy/helpers/passage.php',
			'/components/com_biblestudy/helpers/scripture.php',
			'/components/com_biblestudy/helpers/share.php',
			'/components/com_biblestudy/helpers/store.php',
			'/components/com_biblestudy/helpers/textlink.php',
			'/components/com_biblestudy/helpers/title.php',
			'/components/com_biblestudy/helpers/toolbar.php',
			'/components/com_biblestudy/helpers/topics.php',
			'/components/com_biblestudy/helpers/year.php',
			'/components/com_biblestudy/lib/biblestudy.admin.class.php',
			'/components/com_biblestudy/lib/biblestudy.defines.php',
			'/components/com_biblestudy/lib/biblestudy.stats.class.php',
			'/components/com_biblestudy/models/forms/commentsedit.xml',
			'/components/com_biblestudy/models/commentsedit.php',
			'/components/com_biblestudy/models/commentslist.php',
			'/components/com_biblestudy/models/mediafile.php',
			'/components/com_biblestudy/models/mediafiles.php',
			'/components/com_biblestudy/models/message.php',
			'/components/com_biblestudy/models/messages.php',
			// Admin:
			'/administrator/components/com_biblestudy/controllers/ajax.php',
			'/administrator/components/com_biblestudy/helpers/cleanurl.php',
			'/administrator/components/com_biblestudy/helpers/toolbar.php',
			'/administrator/components/com_biblestudy/lib/biblestudy.admin.class.php',
			'/administrator/components/com_biblestudy/lib/biblestudy.migrate.php',
			'/administrator/components/com_biblestudy/migration/biblestudy.611.upgrade.php',
			'/administrator/components/com_biblestudy/migration/biblestudy.612.upgrade.php',
			'/administrator/components/com_biblestudy/migration/biblestudy.613.upgrade.php',
			'/administrator/components/com_biblestudy/migration/biblestudy.614.upgrade.php',
			'/administrator/components/com_biblestudy/migration/biblestudy.622.upgrade.php',
			'/administrator/components/com_biblestudy/migration/biblestudy.623.upgrade.php',
			'/administrator/components/com_biblestudy/migration/biblestudy.700.upgrade.php',
			'/administrator/components/com_biblestudy/migration/update701.php',
			'/administrator/components/com_biblestudy/models/fields/locationordering.php',
			'/administrator/components/com_biblestudy/models/fields/mediaordering.php',
			'/administrator/components/com_biblestudy/models/fields/messagetypeordering.php',
			'/administrator/components/com_biblestudy/models/fields/shareordering.php',
			'/administrator/components/com_biblestudy/models/fields/teacherordering.php',
			'/administrator/components/com_biblestudy/views/admin/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/admin/tmpl/form_assets.php',
			'/administrator/components/com_biblestudy/views/admin/tmpl/form_backup.php',
			'/administrator/components/com_biblestudy/views/admin/tmpl/form_database.php',
			'/administrator/components/com_biblestudy/views/admin/tmpl/form_migrate.php',
			'/administrator/components/com_biblestudy/views/comment/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/folder/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/folders/tmpl/index.html',
			'/administrator/components/com_biblestudy/views/location/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/mediaimage/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/message/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/messagetype/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/mimetype/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/podcast/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/serie/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/server/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/server/tmpl/index.html',
			'/administrator/components/com_biblestudy/views/share/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/teacher/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/template/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/templatecode/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/topic/tmpl/form.php',
		);

		$folders = array(
			'/components/com_biblestudy/assets',
			'/components/com_biblestudy/images',
			'/components/com_biblestudy/views/teacherlist',
			'/components/com_biblestudy/views/teacherdisplay',
			'/components/com_biblestudy/views/studieslist',
			'/components/com_biblestudy/views/studydetails',
			'/components/com_biblestudy/views/serieslist',
			'/components/com_biblestudy/views/seriesdetail',
			'/administrator/media',
			'/administrator/components/com_biblestudy/migration',
			'/administrator/components/com_biblestudy/assets',
			'/administrator/components/com_biblestudy/images',
			'/administrator/components/com_biblestudy/css',
			'/administrator/components/com_biblestudy/js',
			'/administrator/components/com_biblestudy/views/commentsedit',
			'/administrator/components/com_biblestudy/views/commentslist',
			'/administrator/components/com_biblestudy/views/cssedit',
			'/administrator/components/com_biblestudy/views/folderslist',
			'/administrator/components/com_biblestudy/views/foldersedit',
			'/administrator/components/com_biblestudy/views/folder',
			'/administrator/components/com_biblestudy/views/folders',
			'/administrator/components/com_biblestudy/views/locationslist',
			'/administrator/components/com_biblestudy/views/locationsedit',
			'/administrator/components/com_biblestudy/views/mediaedit',
			'/administrator/components/com_biblestudy/views/mediafilesedit',
			'/administrator/components/com_biblestudy/views/mediafileslist',
			'/administrator/components/com_biblestudy/views/mediaimage',
			'/administrator/components/com_biblestudy/views/mediaimages',
			'/administrator/components/com_biblestudy/views/medialist',
			'/administrator/components/com_biblestudy/views/messagetypelist',
			'/administrator/components/com_biblestudy/views/messagetypeedit',
			'/administrator/components/com_biblestudy/views/mimetypelist',
			'/administrator/components/com_biblestudy/views/mimetypeedit',
			'/administrator/components/com_biblestudy/views/mimetype',
			'/administrator/components/com_biblestudy/views/mimetypes',
			'/administrator/components/com_biblestudy/views/podcastlist',
			'/administrator/components/com_biblestudy/views/podcastedit',
			'/administrator/components/com_biblestudy/views/serieslist',
			'/administrator/components/com_biblestudy/views/seriesedit',
			'/administrator/components/com_biblestudy/views/serverslist',
			'/administrator/components/com_biblestudy/views/serversedit',
			'/administrator/components/com_biblestudy/views/sharelist',
			'/administrator/components/com_biblestudy/views/shareedit',
			'/administrator/components/com_biblestudy/views/studieslist',
			'/administrator/components/com_biblestudy/views/studiesedit',
			'/administrator/components/com_biblestudy/views/teacherlist',
			'/administrator/components/com_biblestudy/views/teacheredit',
			'/administrator/components/com_biblestudy/views/templateedit',
			'/administrator/components/com_biblestudy/views/templateslist',
			'/administrator/components/com_biblestudy/views/topicslist',
			'/administrator/components/com_biblestudy/views/topicsedit',
			// JBSM 8.0.0
			'/components/com_biblestudy/views/messages',
			'/components/com_biblestudy/views/message',
			'/components/com_biblestudy/views/mediafiles',
			'/components/com_biblestudy/views/mediafile',
			'/components/com_biblestudy/views/commentslist',
			'/components/com_biblestudy/views/commentsedit',
			// JBS 9.0.0
			'/administrator/componets/com_biblestudy/views/share',
			'/administrator/componets/com_biblestudy/views/shares',
			'/administrator/componets/com_biblestudy/models/forms/share.xml',
			'/administrator/componets/com_biblestudy/models/share.php',
			'/administrator/componets/com_biblestudy/models/shares.php',
			'/administrator/componets/com_biblestudy/tables/share.php',
			'/administrator/componets/com_biblestudy/controllers/shares.php',
			'/administrator/componets/com_biblestudy/controllers/share.php'
		);

		foreach ($files as $file)
		{
			if (JFile::exists(JPATH_ROOT . $file) && !JFile::delete(JPATH_ROOT . $file))
			{
				echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file) . '<br />';
			}
		}

		foreach ($folders as $folder)
		{
			if (JFolder::exists(JPATH_ROOT . $folder) && !JFolder::delete(JPATH_ROOT . $folder))
			{
				echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder) . '<br />';
			}
		}
	}

	/**
	 * Fix Menus
	 *
	 * @since 7.1.0
	 *
	 * @return   bool
	 */
	public function fixMenus()
	{
		$query = $this->_db->getQuery(true);
		$query->select('*')
			->from('#__menu')
			->where($this->_db->qn('menutype') . ' != ' . $this->_db->q('main'))
			->where($this->_db->qn('link') . ' LIKE ' . $this->_db->q('%com_biblestudy%'));
		$this->_db->setQuery($query);
		$menus = $this->_db->loadObjectList();

		foreach ($menus AS $menu)
		{
			$menu->link = str_replace('teacherlist', 'teachers', $menu->link);
			$menu->link = str_replace('teacherdisplay', 'teacher', $menu->link);
			$menu->link = str_replace('studydetails', 'sermon', $menu->link);
			$menu->link = str_replace('serieslist', 'seriesdisplays', $menu->link);
			$menu->link = str_replace('seriesdetail', 'seriesdisplay', $menu->link);
			$menu->link = str_replace('studieslist', 'sermons', $menu->link);
			$query      = $this->_db->getQuery(true);
			$query->update('#__menu')
				->set("link = " . $this->_db->q($menu->link))
				->where('id = ' . $this->_db->q($menu->id));
			$this->_db->setQuery($query);
			$this->_db->execute();
		}

		return true;
	}

	/**
	 * Function to find empty language field and set them to "*"
	 *
	 * @since 7.1.0
	 *
	 * @return   bool
	 */
	public function fixemptylanguage()
	{
		// Tables to fix
		$tables = array(
			array('table' => '#__bsms_comments'),
			array('table' => '#__bsms_mediafiles'),
			array('table' => '#__bsms_series'),
			array('table' => '#__bsms_studies'),
			array('table' => '#__bsms_teachers'),
		);

		// Correct blank records
		foreach ($tables as $table)
		{
			$query = $this->_db->getQuery(true);
			$query->update($table['table'])
				->set('language = ' . $this->_db->q('*'))
				->where('language = ' . $this->_db->q(''));
			$this->_db->setQuery($query);
			$this->_db->execute();
		}

		return true;
	}

	/**
	 * Function to Find empty access in the db and set them to Public
	 *
	 * @since 7.1.0
	 *
	 * @return   bool
	 */
	public function fixemptyaccess()
	{
		// Tables to fix
		$tables = array(
			array('table' => '#__bsms_admin'),
			array('table' => '#__bsms_mediafiles'),
			array('table' => '#__bsms_message_type'),
			array('table' => '#__bsms_podcast'),
			array('table' => '#__bsms_series'),
			array('table' => '#__bsms_servers'),
			array('table' => '#__bsms_studies'),
			array('table' => '#__bsms_studytopics'),
			array('table' => '#__bsms_teachers'),
			array('table' => '#__bsms_templates'),
			array('table' => '#__bsms_topics'),
		);

		// Get Public id
		$id = JFactory::getConfig()->get('access', 1);

		// Correct blank or not set records
		foreach ($tables as $table)
		{
			$query = $this->_db->getQuery(true);
			$query->update($table['table'])
				->set('access = ' . $id)
				->where("access = " . $this->_db->q('0'), 'OR')->where("access = " . $this->_db->q(' '));
			$this->_db->setQuery($query);
			$this->_db->execute();
		}

		return true;
	}

	/**
	 * Old Update URL's
	 *
	 * @return array
	 */
	public function rmoldurl()
	{
		$urls = array(
			$this->_db->qn('location') . ' = ' .
			$this->_db->q('http://www.joomlabiblestudy.org/index.php?option=com_ars&view=update&task=stream&format=xml&id=3&dummy=extension.xml'),
			$this->_db->qn('location') . ' = ' .
			$this->_db->q('http://www.joomlabiblestudy.org/index.php?option=com_ars&view=update&task=stream&format=xml&id=14&dummy=extension.xml'),
			$this->_db->qn('location') . ' = ' .
			$this->_db->q('http://www.joomlabiblestudy.org/index.php?option=com_ars&view=update&task=stream&format=xml&id=13&dummy=extension.xml'),
			$this->_db->qn('location') . ' = ' .
			$this->_db->q('http://www.joomlabiblestudy.org/index.php?option=com_ars&view=update&task=stream&format=xml&id=4&dummy=extension.xml'),
			$this->_db->qn('location') . ' = ' .
			$this->_db->q('http://www.joomlabiblestudy.org/index.php?option=com_ars&view=update&task=stream&format=xml&id=8&dummy=extension.xml'),
			$this->_db->qn('location') . ' = ' .
			$this->_db->q('http://www.joomlabiblestudy.org/index.php?option=com_ars&view=update&task=stream&format=xml&id=5&dummy=extension.xml'));

		return $urls;
	}
}
