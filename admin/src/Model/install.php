<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

use Symfony\Component\Config\Loader\Loader;

defined('_JEXEC') or die;

Loader::register('Com_BiblestudyInstallerScript', JPATH_ADMINISTRATOR . '/components/com_biblestudy/biblestudy.script.php');
Loader::register('JBSMFreshInstall', JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/biblestudy.install.special.php');

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_biblestudy/api.php';

if (file_exists($api))
{
	require_once $api;
}

/**
 * class Migration model
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class BibleStudyModelInstall extends JModelLegacy
{
	/** @var integer Total numbers of Versions
	 *
	 * @since 7.1
	 */
	public $totalSteps = 0;

	/** @var integer Numbers of Versions already processed
	 *
	 * @since 7.1
	 */
	public $doneSteps = 0;

	/** @var string Running Now
	 *
	 * @since 7.1
	 */
	public $running = null;

	/** @var array Call stack for the Visioning System.
	 *
	 * @since 7.1
	 */
	public $callstack = array();

	/** @var string Path to Mysql files
	 *
	 * @since 7.1
	 */
	protected $filePath = '/components/com_biblestudy/install/sql/updates/mysql';

	/** @var string Path to PHP Version files
	 *
	 * @since 7.1
	 */
	protected $phpPath = '/components/com_biblestudy/install/updates/';

	/** @var float The time the process started
	 *
	 * @since 7.1
	 */
	private $startTime = null;

	/** @var array The pre versions to process
	 *
	 * @since 7.1
	 */
	private $versionStack = array();

	/** @var array The pre versions sub sql array to process
	 *
	 * @since 7.1
	 */
	private $allupdates = array();

	/** @var string Version of BibleStudy
	 *
	 * @since 7.1
	 */
	private $versionSwitch = null;

	/** @var integer Id of Extinction Table
	 *
	 * @since 7.1
	 */
	private $biblestudyEid = 0;

	/** @var array Array of Finish Task
	 *
	 * @since 7.1
	 */
	private $finish = array();

	/** @var string Version number to be running
	 *
	 * @since 7.1
	 */
	private $version = "0.0.0";

	/** @type array PHP file steps for migrations
	 *
	 * @since 7.1
	 */
	private $subSteps = array();

	/** @var array Array of Sub Query from php files queries Task
	 *
	 * @since 7.1
	 */
	private $subQuery = array();

	/** @type array list of php files to work through
	 *
	 * @since 7.1
	 */
	private $subFiles = array();

	/** @var array Array of Install Task
	 *
	 * @since 9.0.14
	 */
	private $start = array();

	/** @var integer If was imported
	 *
	 * @since 7.1
	 */
	private $isimport = 0;

	/** @type array Array of assets to fix
	 *
	 * @since 7.1
	 */
	public $query = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 * @since   7.1
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->name = 'install';
	}

	/**
	 * Start Looking though the Versions
	 *
	 * @return boolean
	 *
	 * @throws  Exception
	 * @since   7.1
	 */
	public function startScanning()
	{
		$this->resetStack();
		$this->resetTimer();
		$this->getSteps();
		$this->postinstallclenup();

		if (empty($this->versionStack))
		{
			$this->versionStack = array();
		}

		asort($this->versionStack);

		$this->saveStack();

		if (!$this->haveEnoughTime())
		{
			return true;
		}

		return $this->run(false);
	}

	/**
	 * Starts or resets the internal timer
	 *
	 * @return void
	 *
	 * @since 7.1
	 */
	private function resetTimer()
	{
		$this->startTime = $this->microtime_float();
	}

	/**
	 * Returns the current timestamps in decimal seconds
	 *
	 * @return string
	 *
	 * @since 7.1
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
	 *
	 * @throws  Exception
	 * @since   7.1
	 */
	private function getSteps()
	{
		$olderversiontype = 0;
		$app              = JFactory::getApplication();

		// Set Finishing Steps
		$this->finish     = array('updateversion', 'fixassets', 'fixmenus', 'fixemptyaccess', 'fixemptylanguage',
			'rmoldurl', 'setupdateurl', 'finish');
		$this->totalSteps += count($this->finish);

		/**
		 * First we check to see if there is a current version database installed. This will have a #__bsms_version
		 * table so we check for it's existence.
		 * Check to be sure a really early version is not installed $versiontype: 1 = current version type 2 = older version type 3 = no version
		 */

		$tables         = $this->_db->getTableList();
		$prefix         = $this->_db->getPrefix();
		$versiontype    = 0;
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
				$this->correctVersions();

				// Find Last updated Version in Update table
				$query = $this->_db->getQuery(true);
				$query->select('version')
					->from('#__bsms_update')
					->order($this->_db->qn('id') . ' DESC');
				$this->_db->setQuery($query, 0, 1);
				$updates             = $this->_db->loadObject();
				$version             = $updates->version;
				$this->versionSwitch = $version;

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

				$this->versionSwitch = implode('.', preg_split('//', $version->build, -1, PREG_SPLIT_NO_EMPTY));

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

				$this->versionSwitch = implode('.', preg_split('//', $schema, -1, PREG_SPLIT_NO_EMPTY));

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

			// Find Extension ID of component
			$query = $this->_db->getQuery(true);
			$query
				->select('extension_id')
				->from('#__extensions')
				->where($this->_db->qn('name') . ' = ' . $this->_db->q('com_biblestudy'));
			$this->_db->setQuery($query);
			$eid                 = $this->_db->loadResult();
			$this->biblestudyEid = $eid;

			foreach ($files as $i => $value)
			{
				$update = $this->versionSwitch;

				if ($update && $eid)
				{
					// Set new Schema Version
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
					$this->totalSteps   += count($files);
					$this->versionStack = $files;
				}
				else
				{
					$app->enqueueMessage(JText::_('JBS_INS_NO_UPDATE_SQL_FILES'), 'warning');

					return false;
				}
			}

			foreach ($php as $i => $value)
			{
				if (version_compare($value, $this->versionSwitch) <= 0)
				{
					unset($php[$i]);
				}
				elseif ($php)
				{
					$this->totalSteps += count($files);
					$this->subFiles   = $php;
				}
			}
		}

		$this->isimport   = JFactory::getApplication()->input->getInt('jbsmalt', 0);
		++$this->totalSteps;

		return true;
	}

	/**
	 * Correct problem in are update table under 7.0.2 systems
	 *
	 * @return boolean
	 *
	 * @throws  Exception
	 * @since   7.1
	 */
	private function correctVersions()
	{
		// Find Last updated Version in Update table
		$query = $this->_db->getQuery(true);
		$query->select('*')
			->from('#__bsms_update');
		$this->_db->setQuery($query);
		$updates = $this->_db->loadObjectList();

		foreach ($updates AS $value)
		{
			// Check to see if Bad version is in key 3

			if (($value->id === '3') && ($value->version !== '7.0.1.1'))
			{
				// Find Last updated Version in Update table
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
	 * @return  boolean
	 *
	 * @throws  Exception
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

				if (!$this->_db->execute())
				{
					$app->enqueueMessage('Error instering ID', 'Error');

					return false;
				}

				return true;
			}

			$app->enqueueMessage('Could not locate extension id in schemas table');

				return false;
		}

		return false;
	}

	/**
	 *  Run the Migration will there is time.
	 *
	 * @param   bool  $resetTimer  If the time must be reset
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since 7.1
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
	 * Saves the Versions/SQL/After stack in the session
	 *
	 * @return void
	 *
	 * @since 7.1
	 */
	private function saveStack()
	{
		$stack = array(
			'aversion'   => $this->version,
			'version'    => $this->versionStack,
			'switch'     => $this->versionSwitch,
			'allupdates' => $this->allupdates,
			'finish'     => $this->finish,
			'start'      => $this->start,
			'subFiles'   => $this->subFiles,
			'subQuery'   => $this->subQuery,
			'subSteps'   => $this->subSteps,
			'isimport'   => $this->isimport,
			'callstack'  => $this->callstack,
			'total'      => $this->totalSteps,
			'done'       => $this->doneSteps,
			'run'        => $this->running,
			'query'      => $this->query,
		);
		$stack = json_encode($stack, JSON_THROW_ON_ERROR);

		if (function_exists('base64_encode') && function_exists('base64_decode'))
		{
			if (function_exists('gzdeflate') && function_exists('gzinflate'))
			{
				$stack = gzdeflate($stack, 9);
			}

			$stack = base64_encode($stack);
		}

		$session = JFactory::getSession();
		$session->set('migration_stack', $stack, 'JBSM');
	}

	/**
	 * Resets the Versions/SQL/After stack saved in the session
	 *
	 * @return void
	 *
	 * @since 7.1
	 */
	private function resetStack()
	{
		$session = JFactory::getSession();
		$session->set('migration_stack', '', 'JBSM');
		$this->version       = '0.0.0';
		$this->versionStack  = array();
		$this->versionSwitch = null;
		$this->allupdates    = array();
		$this->finish        = array();
		$this->start         = array();
		$this->subFiles      = array();
		$this->subQuery      = array();
		$this->subSteps      = array();
		$this->isimport      = 0;
		$this->callstack     = array();
		$this->totalSteps    = 0;
		$this->doneSteps     = 0;
		$this->running       = JText::_('JBS_MIG_STARTING');
		$this->query         = array();
	}

	/**
	 * Loads the Versions/SQL/After stack from the session
	 *
	 * @return boolean
	 *
	 * @since 7.1
	 */
	private function loadStack()
	{
		$session = JFactory::getSession();
		$stack   = $session->get('migration_stack', '', 'JBSM');

		if (empty($stack))
		{
			$this->version       = '0.0.0';
			$this->versionStack  = array();
			$this->versionSwitch = null;
			$this->allupdates    = array();
			$this->finish        = array();
			$this->start         = array();
			$this->subFiles      = array();
			$this->subQuery      = array();
			$this->subSteps      = array();
			$this->isimport      = 0;
			$this->callstack     = array();
			$this->totalSteps    = 0;
			$this->doneSteps     = 0;
			$this->running       = JText::_('JBS_MIG_STARTING');
			$this->query         = array();

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

		$stack = json_decode($stack, true, 512, JSON_THROW_ON_ERROR);

		$this->version       = $stack['aversion'];
		$this->versionStack  = $stack['version'];
		$this->versionSwitch = $stack['switch'];
		$this->allupdates    = $stack['allupdates'];
		$this->finish        = $stack['finish'];
		$this->start         = $stack['start'];
		$this->subFiles      = $stack['subFiles'];
		$this->subQuery      = $stack['subQuery'];
		$this->subSteps      = $stack['subSteps'];
		$this->isimport      = $stack['isimport'];
		$this->callstack     = $stack['callstack'];
		$this->totalSteps    = $stack['total'];
		$this->doneSteps     = $stack['done'];
		$this->running       = $stack['run'];
		$this->query         = $stack['query'];

		return true;
	}

	/**
	 * Makes sure that no more than 5 seconds since the start of the timer have elapsed
	 *
	 * @return boolean
	 *
	 * @since 7.1
	 */
	private function haveEnoughTime()
	{
		$now     = $this->microtime_float();
		$elapsed = abs($now - $this->startTime);

		return $elapsed < 2;
	}

	/**
	 * Start the Run through the Pre Versions then SQL files then After PHP functions.
	 *
	 * @return boolean
	 *
	 * @throws  Exception
	 * @since   7.1
	 */
	private function realRun()
	{
		$app = JFactory::getApplication();
		$run = true;

		if (!empty($this->start))
		{
			$this->running = 'Backup DB';
			$this->doneSteps++;
			$export = new JBSMBackup;
			$export->exportdb(2);
			JLog::add('Backup DB', JLog::INFO, 'com_biblestudy');
			$this->start = array();
		}

		if ($this->isimport)
		{
			$this->fiximport();
			$this->running  = 'Fixing Imported Params';
			$this->isimport = 0;
			JLog::add('Fixing Imported Params', JLog::INFO, 'com_biblestudy');
			$this->doneSteps++;
		}

		if (!empty($this->versionStack))
		{
			krsort($this->versionStack);

			while (!empty($this->versionStack) && $this->haveEnoughTime())
			{
				$version       = array_pop($this->versionStack);
				$this->running = $version;
				$this->doneSteps++;
				$run = $this->allUpdate($version);

				if (!$run)
				{
					JFactory::getApplication()->enqueueMessage('Error Updating Update version ' . (string) $version, 'error');
					JLog::add('Error Updating Update version ' . (string) $version, JLog::ERROR, 'com_biblestudy');
				}
			}
		}

		if ((!empty($this->allupdates) || !empty($this->subFiles)) && empty($this->versionStack))
		{
			ksort($this->allupdates);

			while ((!empty($this->allupdates) || !empty($this->subFiles)) && $this->haveEnoughTime())
			{
				$this->version = key($this->allupdates);

				if (isset($this->allupdates[$this->version]) && @!empty($this->allupdates[$this->version]))
				{
					if (strpos($this->running, $this->version))
					{
						$this->totalSteps += count((array) $this->allupdates[$this->version]);
					}

					// Used for Install array.
					if (!is_array($this->allupdates[$this->version]))
					{
						$this->allupdates[$this->version] = array($this->allupdates[$this->version]);
					}

					$string = array_shift($this->allupdates[$this->version]);

					$this->running = $this->version . ' String: ' . $string;
					$run           = $this->runUpdates($string);
					$this->doneSteps++;
				}
				elseif (in_array($this->version, $this->subFiles, true) && @empty($this->allupdates[$this->version]))
				{
					// Check for corresponding PHP file and run migration
					$migrationfile = JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/updates/' . $this->version . '.php';

					require_once $migrationfile;
					$migrationClass = "Migration" . str_ireplace(".", '', $this->version);
					$migration      = new $migrationClass;

					if (!class_exists($migrationClass))
					{
						JLog::add('Missing Class' . $migrationClass, JLog::WARNING, 'com_biblestudy');

						return true;
					}

					if (!empty($this->subSteps) && !empty($this->subFiles))
					{
						while (!empty($this->subFiles) && $this->haveEnoughTime())
						{
							$query = array();
							$step  = $this->versionSwitch;

							if (!isset($this->subQuery[$this->version][$step]) && !empty($this->subSteps[$this->version]))
							{
								$step = $this->versionSwitch = array_shift($this->subSteps[$this->version]);
								JLog::add('Change step : ' . $step, JLog::INFO, 'com_biblestudy');
							}
							elseif (empty($this->subSteps[$this->version]))
							{
								JLog::add('Unset Last Step : ' . $step, JLog::INFO, 'com_biblestudy');

								$step = $this->versionSwitch = null;
								unset($this->subSteps[$this->version]);
								unset($this->subQuery[$this->version]);
								unset($this->allupdates[$this->version]);

								if (($key = array_search($this->version, $this->subFiles)) !== false)
								{
									unset($this->subFiles[$key]);
								}
							}

							if (isset($this->subQuery[$this->version][$step]) && !empty($this->subQuery[$this->version][$step]))
							{
								$query            = array_shift($this->subQuery[$this->version][$step]);
								$migration->query = $this->subQuery[$this->version];
							}
							elseif (isset($this->subQuery[$this->version][$step]) && empty($this->subQuery[$this->version][$step]))
							{
								unset($this->subQuery[$this->version][$step]);
								$this->versionSwitch = null;
								JLog::add('Uset Sub Query if empty : ' . $step . ' ' . $this->version, JLog::INFO, 'com_biblestudy');
							}

							if (empty($step) && empty($query))
							{
								unset($this->subFiles[$this->version]);
								unset($this->subSteps[$this->version]);
								JLog::add('Uset Version in All updates : ' . $this->version, JLog::INFO, 'com_biblestudy');
							}
							else
							{
								$this->running = 'PHP Sub Process: ' . $this->version . ' - ' . $step;
								$migration->$step(JFactory::getDbo(), $query);

								// Pull back the Query form PHP file if any.
								if (isset($migration->query) && !empty($migration->query))
								{
									$this->subQuery[$this->version] = $migration->query;
								}

								$queryString = null;

								if (!empty($query) && is_array($query))
								{
									$queryString = (string) $query['id'];
									$queryString = str_replace(array("\r", "\n"), array('', ' '), substr($queryString, 0, 80));
									$queryString = ' ID:' . $queryString . ' Query count: ' . count($this->subQuery[$this->version][$step]);
								}

								JLog::add('Doing Step in ' . $migrationClass . ' Step: ' . $step . $queryString, JLog::INFO, 'com_biblestudy');

								$this->doneSteps++;
							}
						}
					}
				}
				else
				{
					unset($this->allupdates[$this->version]);
					JLog::add('Unset Version if no steps : ' . $this->version, JLog::INFO, 'com_biblestudy');
				}

				if ($run === false)
				{
					JBSMDbHelper::resetdb();
					$this->resetStack();
					$app->enqueueMessage(JText::_('JBS_CMN_DATABASE_NOT_MIGRATED'), 'warning');

					return false;
				}
			}
		}

		if (!empty($this->finish) && empty($this->versionStack) && empty($this->allupdates) && empty($this->subFiles))
		{
			while (!empty($this->finish) && $this->haveEnoughTime())
			{
				$finish = array_pop($this->finish);
				$this->doneSteps++;
				$this->running = $finish;
				$this->finish($finish);
			}
		}

		/** We are going to walk thought the assets that need to be fixed that were found form the finish lookup. */
		if (!empty($this->query)
			&& empty($this->finish)
			&& empty($this->versionStack)
			&& empty($this->allupdates)
			&& empty($this->subFiles))
		{
			krsort($this->query);

			while (!empty($this->query) && $this->haveEnoughTime())
			{
				$this->versionSwitch = key($this->query);

				if (isset($this->query[$this->versionSwitch]) && @!empty($this->query[$this->versionSwitch]))
				{
					$version = array_pop($this->query[$this->versionSwitch]);
					$this->doneSteps++;
					$this->running = 'Fixing Assets that are not right';
					JBSMAssets::fixAssets($this->versionSwitch, $version);
				}
				else
				{
					unset($this->query[$this->versionSwitch]);
				}
			}
		}

		if (empty($this->query)
			&& empty($this->finish)
			&& empty($this->versionStack)
			&& empty($this->allupdates)
			&& empty($this->subFiles))
		{
			// Fix any problem with db versions after migration.
			JLoader::register('BiblestudyModelAdmin', JPATH_ADMINISTRATOR . '/components/com_biblestudy/models/AdministratorModel.php');
			$admin = new BiblestudyModelAdmin;
			$admin->fix();

			// Just finished
			$this->resetStack();
			$this->running = JText::_('JBS_MIGFINISED');

			return false;
		}

		// If we have more Versions or SQL files, continue in the next step
		return true;
	}

	/**
	 * Uninstall of JBSM
	 *
	 * @return boolean
	 *
	 * @throws  Exception
	 * @since   7.1
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

				if ($parent_id !== '0')
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

				$queries = JDatabaseDriver::splitSql($buffer);

				foreach ($queries as $querie)
				{
					$querie = trim($querie);

					if ($querie !== '' && $querie[0] !== '#' && $querie !== '`')
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
		JFactory::getApplication()->enqueueMessage('<h2>' . JText::_('JBS_INS_UNINSTALLED') . ' ' .
			BIBLESTUDY_VERSION . '</h2> <div>' . $drop_result . '</div>'
		);

		return true;
	}

	/**
	 * Finish the system
	 *
	 * @param   string  $step  Step to process
	 *
	 * @return boolean
	 *
	 * @throws  Exception
	 * @since   7.1
	 */
	private function finish($step)
	{
		$app = JFactory::getApplication();
		$run = false;

		switch ($step)
		{
			case 'updateversion':
				$update = $this->getUpdateVersion();

				// Set new Schema Version
				$run           = $this->setSchemaVersion($update, $this->biblestudyEid);
				$this->running = 'Update Version';
				break;
			case 'fixassets':
				// Final step is to fix assets by building what need to be fixed.
				$assets = new JBSMAssets;
				$assets->build();
				$this->query      = $assets->query;
				$this->totalSteps += $assets->count;
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
				$query      = $this->_db->getQuery(true);
				$query->delete($this->_db->qn('#__update_sites'));
				$query->where($conditions, $glue = 'OR');
				$this->_db->setQuery($query);
				$this->_db->execute();
				$this->running = 'Remove Old Update URL\'s';
				break;
			case 'setupdateurl':
				// Find Extension ID of component
				$query = $this->_db->getQuery(true);
				$query
					->select('extension_id')
					->from('#__extensions')
					->where($this->_db->qn('name') . ' = ' . $this->_db->q('com_biblestudy'));
				$this->_db->setQuery($query);
				$eid = $this->_db->loadResult();

				$conditions = array(
					$this->_db->qn('name') . ' = ' .
					$this->_db->q('Proclaim Package'),
				);
				$query      = $this->_db->getQuery(true);
				$query->delete($this->_db->qn('#__update_sites'));
				$query->where($conditions, $glue = 'OR');
				$this->_db->setQuery($query);
				$this->_db->execute();

				$conditions = array(
					$this->_db->qn('extension_id') . ' = ' .
					$this->_db->q($eid),
				);
				$query      = $this->_db->getQuery(true);
				$query->delete($this->_db->qn('#__update_sites_extensions'));
				$query->where($conditions, $glue = 'OR');
				$this->_db->setQuery($query);
				$this->_db->execute();

				$updateurl           = new stdClass;
				$updateurl->name     = 'Proclaim Package';
				$updateurl->type     = 'extension';
				$updateurl->location = 'https://www.christianwebministries.org/index.php?option=com_ars&amp;view=update&amp;task=stream&amp;id=2&amp;format=xml';
				$updateurl->enabled  = '1';
				$this->_db->insertObject('#__update_sites', $updateurl);
				$lastid                     = $this->_db->insertid();
				$updateurl1                 = new stdClass;
				$updateurl1->update_site_id = $lastid;
				$updateurl1->extension_id   = $eid;
				$this->_db->insertObject('#__update_sites_extensions', $updateurl1);
				$this->running = 'Set New Update URL';
				break;
			default:
				$app->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_SUCCESSFUL') .
					JText::_('SIMPLEMODEMESSAGE_BODY') .
					JText::_('JBS_IBM_REVIEW_ADMIN_TEMPLATE')
				);
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
	 *
	 * @since 7.1
	 */
	public function postinstall_messages($message)
	{
		// Find Extension ID of component
		$query = $this->_db->getQuery(true);
		$query
			->select('extension_id')
			->from('#__extensions')
			->where($this->_db->qn('name') . ' = ' . $this->_db->q('com_biblestudy'));
		$this->_db->setQuery($query);
		$eid                   = $this->_db->loadResult();
		$this->biblestudyEid   = $eid;
		$message->extension_id = $this->biblestudyEid;

		if ($this->_db->insertObject('#__postinstall_messages', $message) !== true)
		{
			jexit('Bad install');
		}
	}

	/**
	 * Returns Update Version form Table
	 *
	 * @return string Returns the Last Version in the #_bsms_update table
	 *
	 * @since 7.1
	 */
	private function getUpdateVersion()
	{
		// Find Last updated Version in Update table

		$query = $this->_db->getQuery(true);
		$query
			->select('version')
			->from('#__bsms_update');
		$this->_db->setQuery($query);
		$updates = $this->_db->loadObjectList();

		return end($updates)->version;
	}

	/**
	 * Fix Import problem
	 *
	 * @return boolean True if fix complete, False if failure
	 *
	 * @since 7.1
	 */
	private function fiximport()
	{
		$tables = JBSMDbHelper::getObjects();
		$set    = false;

		foreach ($tables as $table)
		{
			if (strpos($table['name'], '_bsms_timeset') === false)
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
						$this->_db->updateObject($table['name'], $row, array('id'));
					}
				}
			}
		}

		return true;
	}

	/**
	 * Function to update using the version number for sql files
	 *
	 * @param   string  $value  The File name.
	 *
	 * @return boolean
	 *
	 * @throws  Exception
	 * @since   7.1.4
	 */
	private function allUpdate($value)
	{
		$buffer = file_get_contents(JPATH_ADMINISTRATOR . $this->filePath . '/' . $value . '.sql');

		// Graceful exit and rollback if read not successful
		if ($buffer === false)
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('JLIBinstallER_ERROR_SQL_READBUFFER'), 'WARNING');
			JLog::add(JText::sprintf('JLIBinstallER_ERROR_SQL_READBUFFER'), JLog::WARNING, 'com_biblestudy');

			return false;
		}

		// Create an array of queries from the sql file
		$queries = JDatabaseDriver::splitSql($buffer);

		if ((int) count($queries) === 0)
		{
			return false;
		}

		$this->totalSteps += count($queries);

		$this->allupdates = array_merge($this->allupdates, array($value => $queries));

		// Build php steps now.
		$migrationFile = JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/updates/' . $value . '.php';

		if (JFile::exists($migrationFile))
		{
			require_once $migrationFile;
			$migrationClass = "Migration" . str_ireplace(".", '', $value);

			if (class_exists($migrationClass))
			{
				$migration = new $migrationClass;

				if (isset($migration->postinstall_messages))
				{
					$steps            = $migration->steps;
					$this->totalSteps += count($steps);

					// If Steps build is mandatory.
					$migration->build($this->_db);

					if (isset($migration->count))
					{
						$this->totalSteps += (int) $migration->count;
					}

					$this->subSteps = array_merge($this->subSteps, array($value => $steps));
					$this->subQuery = array_merge($this->subQuery, array($value => $migration->query));
				}
				else
				{
					$this->subSteps   = array_merge($this->subSteps, array($value => array('up')));
					++$this->totalSteps;
				}
			}
		}

		return true;
	}

	/**
	 * Run updates SQL
	 *
	 * @param   string  $string  String of SQL to process.
	 *
	 * @return boolean
	 *
	 * @throws  Exception
	 * @since   7.1
	 */
	private function runUpdates($string)
	{
		// Process each query in the $queries array (split out of sql file).
		$string = trim($string);

		if ($string !== '' && $string[0] !== '#')
		{
			$this->_db->setQuery($this->_db->convertUtf8mb4QueryToUtf8($string));
			$this->doneSteps++;

			try
			{
				$this->_db->execute();
			}
			catch (RuntimeException $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'com_biblestudy');

				return false;
			}

			$queryString = (string) $string;
			$queryString = str_replace(array("\r", "\n"), array('', ' '), substr($queryString, 0, 80));
			JLog::add(
				JText::sprintf('JLIBINSTALLER_UPDATE_LOG_QUERY', $this->running, $queryString),
				JLog::INFO, 'com_biblestudy'
			);
		}

		return true;
	}

	/**
	 * Cleanup postInstall before migration
	 *
	 * @return void
	 *
	 * @since 7.1
	 */
	private function postinstallclenup()
	{
		// Post Install Messages Cleanup for Component
		$query = $this->_db->getQuery(true);
		$query->delete('#__postinstall_messages')
			->where($this->_db->qn('language_extension') . ' = ' . $this->_db->q('com_biblestudy'));
		$this->_db->setQuery($query);
		$this->_db->execute();
		JLog::add('PostInstallCleanup', JLog::INFO, 'com_biblestudy');
	}

	/**
	 * Fix Menus
	 *
	 * @return   boolean
	 * @since 7.1.0
	 *
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
	 * @return   boolean
	 * @since 7.1.0
	 *
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
	 * @return   boolean
	 * @since 7.1.0
	 *
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
				->where("access = " . $this->_db->q('0'), $glue = 'OR')
				->where("access = " . $this->_db->q(' '));
			$this->_db->setQuery($query);
			$this->_db->execute();
		}

		return true;
	}

	/**
	 * Old Update URL's
	 *
	 * @return array
	 *
	 * @since 7.1
	 */
	public function rmoldurl()
	{
		$urls = array(
			$this->_db->qn('name') . ' = ' .
			$this->_db->q('Proclaim Module'),
			$this->_db->qn('name') . ' = ' .
			$this->_db->q('Proclaim Podcast Module'),
			$this->_db->qn('name') . ' = ' .
			$this->_db->q('Proclaim Finder Plg'),
			$this->_db->qn('name') . ' = ' .
			$this->_db->q('Proclaim Search Plg'),
			$this->_db->qn('name') . ' = ' .
			$this->_db->q('Proclaim Backup Plg'),
			$this->_db->qn('name') . ' = ' .
			$this->_db->q('Proclaim Podcast Plg'),
			$this->_db->qn('name') . ' = ' .
			$this->_db->q('Proclaim'));

		return $urls;
	}
}
