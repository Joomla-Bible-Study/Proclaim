<?php
/**
 * BibleStudy Component
 *
 * @package       BibleStudy.Installer
 *
 * @copyright (C) 2008 - 2015 BibleStudy Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          http://www.joomlabiblestudy.org
 **/
defined('_JEXEC') or die ();

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.path');
jimport('joomla.filesystem.archive');

define('BIBLESTUDY_INSTALLER_PATH', __DIR__);
define('BIBLESTUDY_INSTALLER_ADMINPATH', dirname(BIBLESTUDY_INSTALLER_PATH));
define('BIBLESTUDY_INSTALLER_SITEPATH', JPATH_SITE . '/components/' . basename(BIBLESTUDY_INSTALLER_ADMINPATH));
define('BIBLESTUDY_INSTALLER_MEDIAPATH', JPATH_SITE . '/media/com_biblestudy');

use Joomla\Registry\Registry;

/**
 * Install Model for BibleStudy
 *
 * @since        1.6
 */
class JBSMModelInstall extends JModelLegacy
{
	/**
	 * Flag to indicate model state initialization.
	 *
	 * @var        boolean
	 * @since    1.6
	 */
	protected $__state_set = false;

	protected $_versionprefix = false;
	protected $_installed = array();
	protected $_versions = array();
	protected $_action = false;

	protected $_errormsg = null;

	protected $_versiontablearray = null;
	protected $_versionarray = null;

	public $steps = null;

	/** @type \JDatabaseDriver */
	public $db;

	/**
	 * BibleStudyModelInstall constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->db = JFactory::getDBO();

		if (function_exists('ignore_user_abort'))
		{
			ignore_user_abort(true);
		}

		$this->setState('default_max_time', @ini_get('max_execution_time'));
		@set_time_limit(300);
		$this->setState('max_time', @ini_get('max_execution_time'));

		$this->steps = array(
			array('step' => '', 'menu' => JText::_('COM_BIBLESTUDY_INSTALL_STEP_INSTALL')),
			array('step' => 'Prepare', 'menu' => JText::_('COM_BIBLESTUDY_INSTALL_STEP_PREPARE')),
			array('step' => 'Plugins', 'menu' => JText::_('COM_BIBLESTUDY_INSTALL_STEP_PLUGINS')),
			array('step' => 'Database', 'menu' => JText::_('COM_BIBLESTUDY_INSTALL_STEP_DATABASE')),
			array('step' => 'Finish', 'menu' => JText::_('COM_BIBLESTUDY_INSTALL_STEP_FINISH')),
			array('step' => '', 'menu' => JText::_('COM_BIBLESTUDY_INSTALL_STEP_COMPLETE')));
	}

	/**
	 * Initialise Kunena, run from Joomla installer.
	 */
	public function install()
	{
		// Make sure that we are using the latest English language files
		$this->installLanguage('en-GB');

		$this->setStep(0);
	}

	/**
	 * Uninstall Kunena, run from Joomla installer.
	 *
	 * @return boolean
	 */
	public function uninstall()
	{
		// Put back file that was removed during installation.
		$contents = '';
		JFile::write(BIBLESTUDY_PATH_ADMIN . '/install.php', $contents);

		// Uninstall all plugins.
		$this->uninstallPlugin('kunena', 'alphauserpoints');
		$this->uninstallPlugin('kunena', 'community');
		$this->uninstallPlugin('kunena', 'comprofiler');
		$this->uninstallPlugin('kunena', 'gravatar');
		$this->uninstallPlugin('kunena', 'joomla');
		$this->uninstallPlugin('kunena', 'kunena');
		$this->uninstallPlugin('kunena', 'uddeim');
		$this->uninstallPlugin('finder', 'kunena');
		$this->uninstallPlugin('quickicon', 'kunena');
		$this->uninstallPlugin('content', 'kunena');

		// Uninstall menu module.
		$this->uninstallModule('mod_biblstudymenu');

		// Remove all JBSM related menu items, including aliases
//		if (class_exists('BibleStudyMenuFix'))
//		{
//			$items = BibleStudyMenuFix::getAll();
//			foreach ($items as $item)
//			{
//				BibleStudyMenuFix::delete($item->id);
//			}
//		}

		$this->deleteMenu();

		// Uninstall Kunena library
		// $this->uninstallLibrary();

		// Uninstall Kunena media
		// $this->uninstallMedia();

		// Uninstall Kunena system plugin
		// $this->uninstallPlugin('system', 'kunena');

		return true;
	}

	/**
	 * @return $this
	 */
	public function getModel()
	{
		return $this;
	}

	/**
	 * Overridden method to get model state variables.
	 *
	 * @param    string $property Optional parameter name.
	 * @param    mixed  $default  The default value to use if no state property exists by name.
	 *
	 * @return    object    The property where specified, the state object where omitted.
	 * @since    1.6
	 */
	public function getState($property = null, $default = null)
	{
		// if the model state is uninitialized lets set some values we will need from the request.
		if ($this->__state_set === false)
		{
			$app = JFactory::getApplication();
			$this->setState('action', $step = $app->getUserState('com_biblestudy.install.action', null));
			$this->setState('step', $step = $app->getUserState('com_biblestudy.install.step', 0));
			$this->setState('task', $task = $app->getUserState('com_biblestudy.install.task', 0));
			$this->setState('version', $task = $app->getUserState('com_biblestudy.install.version', null));

			if ($step == 0)
			{
				$app->setUserState('com_biblestudy.install.status', array());
			}

			$this->setState('status', $app->getUserState('com_biblestudy.install.status'));

			$this->__state_set = true;
		}

		$value = parent::getState($property);

		return (is_null($value) ? $default : $value);
	}

	/**
	 * @return object
	 */
	public function getStatus()
	{
		return $this->getState('status', array());
	}

	/**
	 * @return object
	 */
	public function getAction()
	{
		return $this->getState('action', null);
	}

	/**
	 * @return object
	 */
	public function getStep()
	{
		return $this->getState('step', 0);
	}

	/**
	 * @return object
	 */
	public function getTask()
	{
		return $this->getState('task', 0);
	}

	/**
	 * @return object
	 */
	public function getVersion()
	{
		return $this->getState('version', null);
	}

	/**
	 * @param $action
	 *
	 * @throws \Exception
	 */
	public function setAction($action)
	{
		$this->setState('action', $action);
		$app = JFactory::getApplication();
		$app->setUserState('com_biblestudy.install.action', $action);
	}

	/**
	 * @param $step
	 *
	 * @throws \Exception
	 */
	public function setStep($step)
	{
		$this->setState('step', ( int ) $step);
		$app = JFactory::getApplication();
		$app->setUserState('com_biblestudy.install.step', ( int ) $step);
		$this->setTask(0);
	}

	/**
	 * @param $task
	 *
	 * @throws \Exception
	 */
	public function setTask($task)
	{
		$this->setState('task', ( int ) $task);
		$app = JFactory::getApplication();
		$app->setUserState('com_biblestudy.install.task', ( int ) $task);
	}

	/**
	 * @param $version
	 *
	 * @throws \Exception
	 */
	public function setVersion($version)
	{
		$this->setState('version', $version);
		$app = JFactory::getApplication();
		$app->setUserState('com_biblestudy.install.version', $version);
	}

	/**
	 * @param        $task
	 * @param bool   $result
	 * @param string $msg
	 * @param null   $id
	 *
	 * @throws \Exception
	 */
	public function addStatus($task, $result = false, $msg = '', $id = null)
	{
		$status = $this->getState('status');
		$step   = $this->getStep();

		if ($id === null)
		{
			$status [] = array('step' => $step, 'task' => $task, 'success' => $result, 'msg' => $msg);
		}
		else
		{
			unset($status [$id]);
			$status [$id] = array('step' => $step, 'task' => $task, 'success' => $result, 'msg' => $msg);
		}

		$this->setState('status', $status);
		$app = JFactory::getApplication();
		$app->setUserState('com_biblestudy.install.status', $status);
	}

	/**
	 * @return bool|string
	 */
	function getInstallError()
	{
		$status = $this->getState('status', array());
		foreach ($status as $cur)
		{
			$error = !$cur['success'];
			if ($error)
			{
				return $cur['task'] . ' ... ' . ($cur['success'] > 0 ? 'SUCCESS' : 'FAILED');
			}
		}

		return false;
	}

	/**
	 * @return array|null
	 */
	public function getSteps()
	{
		return $this->steps;
	}

	/**
	 * @param      $path
	 * @param      $filename
	 * @param null $dest
	 * @param bool $silent
	 *
	 * @return bool|null
	 */
	public function extract($path, $filename, $dest = null, $silent = false)
	{
		$success = null;

		if (!$dest)
		{
			$dest = $path;
		}

		$file = "{$path}/{$filename}";

		$text = '';

		if (is_file($file))
		{
			$success = true;

			if (!JFolder::exists($dest))
			{
				$success = JFolder::create($dest);
			}

			if ($success)
			{
				$success = JArchive::extract($file, $dest);
			}

			if (!$success)
			{
				$text .= JText::sprintf('COM_BIBLESTUDY_INSTALL_EXTRACT_FAILED', $file);
			}
		}
		else
		{
			$success = true;
			$text .= JText::sprintf('COM_BIBLESTUDY_INSTALL_EXTRACT_MISSING', $file);
		}

		if ($success !== null && !$silent)
		{
			$this->addStatus(JText::sprintf('COM_BIBLESTUDY_INSTALL_EXTRACT_STATUS', $filename), $success, $text);
		}

		return $success;
	}

	/**
	 * @param $group
	 * @param $element
	 *
	 * @return bool|\JTable
	 */
	function loadPlugin($group, $element)
	{
		$plugin = JTable::getInstance('extension');
		$plugin->load(array('type' => 'plugin', 'folder' => $group, 'element' => $element));

		return $plugin;
	}

	/**
	 * @param $path
	 * @param $name
	 *
	 * @return bool|null
	 */
	function installModule($path, $name)
	{
		$success = false;

		$dest = JPATH_ROOT . "/tmp/jbsminstall_mod_{$name}";

		if (is_dir($dest))
		{
			JFolder::delete($dest);
		}

		if (is_dir(BIBLESTUDY_INSTALLER_ADMINPATH . '/' . $path))
		{
			// Copy path
			$success = JFolder::copy(BIBLESTUDY_INSTALLER_ADMINPATH . '/' . $path, $dest);
		}
		elseif (is_file(BIBLESTUDY_INSTALLER_ADMINPATH . '/' . $path))
		{
			// Extract file
			$success = $this->extract(BIBLESTUDY_INSTALLER_ADMINPATH, $path, $dest);
		}

		// Only install module if it can be used in current Joomla version (manifest exists)
		if ($success && is_file("{$dest}/mod_{$name}.xml"))
		{
			$installer = new JInstaller ();
			$success   = $installer->install($dest);
			$this->addStatus(JText::sprintf('COM_BIBLESTUDY_INSTALL_MODULE_STATUS', ucfirst($name)), $success);
		}
		elseif (!$success)
		{
			$this->addStatus(JText::sprintf('COM_BIBLESTUDY_INSTALL_MODULE_STATUS', ucfirst($name)), $success);
		}

		JFolder::delete($dest);

		return $success;
	}

	/**
	 * @param     $path
	 * @param     $group
	 * @param     $name
	 * @param     $publish
	 * @param int $ordering
	 *
	 * @return bool|null
	 */
	function installPlugin($path, $group, $name, $publish, $ordering = 0)
	{
		$success = false;

		$dest = JPATH_ROOT . "/tmp/jbsminstall_plg_{$group}_{$name}";

		if (is_dir($dest))
		{
			JFolder::delete($dest);
		}

		if (is_dir(BIBLESTUDY_INSTALLER_PATH . '/' . $path))
		{
			// Copy path
			$success = JFolder::copy(BIBLESTUDY_INSTALLER_PATH . '/' . $path, $dest);
		}
		elseif (is_file(BIBLESTUDY_INSTALLER_PATH . '/' . $path))
		{
			// Extract file
			$success = $this->extract(BIBLESTUDY_INSTALLER_PATH, $path, $dest);
		}

		// Only install plugin if it can be used in current Joomla version (manifest exists)
		if ($success && is_file("{$dest}/{$name}.xml"))
		{
			$installer = new JInstaller ();
			$success   = $installer->install($dest);

			if ($success)
			{
				// First change plugin ordering
				$plugin = $this->loadPlugin($group, $name);

				if ($ordering && !$plugin->ordering)
				{
					$plugin->ordering = $ordering;
				}

				if ($publish)
				{
					$plugin->enabled = $publish;
				}

				$success = $plugin->store();
			}
			$this->addStatus(JText::sprintf('COM_BIBLESTUDY_INSTALL_PLUGIN_STATUS', ucfirst($group) . ' - ' . ucfirst($name)), $success);
		}
		elseif (!$success)
		{
			$this->addStatus(JText::sprintf('COM_BIBLESTUDY_INSTALL_PLUGIN_STATUS', ucfirst($group) . ' - ' . ucfirst($name)), $success);
		}

		JFolder::delete($dest);

		return $success;
	}

	/**
	 * @param $name
	 */
	function uninstallModule($name)
	{
		$query = "SELECT extension_id FROM #__extensions WHERE type='module' AND element='{$name}'";
		$this->db->setQuery($query);
		$moduleid = $this->db->loadResult();

		if ($moduleid)
		{
			$installer = new JInstaller ();
			$installer->uninstall('module', $moduleid);
		}
	}

	/**
	 * @param $folder
	 * @param $name
	 */
	function uninstallPlugin($folder, $name)
	{
		$query = "SELECT extension_id FROM #__extensions WHERE type='plugin' AND folder='{$folder}' AND element='{$name}'";
		$this->db->setQuery($query);
		$pluginid = $this->db->loadResult();

		if ($pluginid)
		{
			$installer = new JInstaller ();
			$installer->uninstall('plugin', $pluginid);
		}
	}

	/**
	 * Method to uninstall the jbsm library during uninstall process
	 *
	 * @return void
	 */
	public function uninstallLibrary()
	{
		$libraryid = $this->uninstallMediaLibraryQuery('library', 'kunena');

		if ($libraryid)
		{
			$installer = new JInstaller;
			$installer->uninstall('library', $libraryid);
		}
	}

	/**
	 * Method to uninstall the JBSM media during uninstall process
	 *
	 * @return void
	 */
	public function uninstallMedia()
	{
		$mediaid = $this->uninstallMediaLibraryQuery('file', 'bsms_media');

		if ($mediaid)
		{
			$installer = new JInstaller;
			$installer->uninstall('file', $mediaid);
		}
	}

	/**
	 * Method to uninstall the JBSM media during uninstall process
	 *
	 * @param   string $element Name of the package or of the component
	 *
	 * @return int
	 */
	private function uninstallMediaLibraryQuery($type, $element)
	{
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('extension_id'));
		$query->from($this->db->quoteName('#__extensions'));
		$query->where($this->db->quoteName('type') . "='" . $type . "'");
		$query->where($this->db->quoteName('element') . "='" . $element . "'");
		$this->db->setQuery($query);
		$id = $this->db->loadResult();

		return $id;
	}

	/**
	 * @param       $path
	 * @param array $ignore
	 */
	public function deleteFiles($path, $ignore = array())
	{
		$ignore = array_merge($ignore, array('.git', '.svn', 'CVS', '.DS_Store', '__MACOSX'));

		if (JFolder::exists($path))
		{
			foreach (JFolder::files($path, '.', false, true, $ignore) as $file)
			{
				if (JFile::exists($file))
				{
					JFile::delete($file);
				}
			}
		}
	}

	/**
	 * @param       $path
	 * @param array $ignore
	 */
	public function deleteFolders($path, $ignore = array())
	{
		$ignore = array_merge($ignore, array('.git', '.svn', 'CVS', '.DS_Store', '__MACOSX'));

		if (JFolder::exists($path))
		{
			foreach (JFolder::folders($path, '.', false, true, $ignore) as $folder)
			{
				if (JFolder::exists($folder))
				{
					JFolder::delete($folder);
				}
			}
		}
	}

	/**
	 * @param       $path
	 * @param array $ignore
	 */
	public function deleteFolder($path, $ignore = array())
	{
		$this->deleteFiles($path, $ignore);
		$this->deleteFolders($path, $ignore);
	}

	/**
	 * Start up Steps
	 *
	 * @throws \JBSMInstallerException
	 * @throws \KunenaSchemaException
	 */
	public function stepPrepare()
	{
		$results = array();

		$this->setVersion(null);
		$this->addStatus(JText::_('COM_BIBLESTUDY_INSTALL_STEP_PREPARE'), true);

		$action = $this->getAction();

		if ($action == 'install' || $action == 'migrate')
		{
			// Let's start from clean database
			$this->deleteTables('bsms_');
			$this->deleteMenu();
		}

		$installed = $this->getDetectVersions();

		if ($action == 'migrate' && $installed['fb']->component)
		{
			$version    = $installed['fb'];
			$results [] = $this->migrateTable($version->prefix, $version->prefix . 'version', 'bsms_version');
		}
		else
		{
			$version = $installed['kunena'];
		}

		$this->setVersion($version);

		// Always enable the System - Kunena plugin
		$query = $this->db->getQuery(true);
		$query->clear()
			->update($this->db->quoteName('#__extensions'))
			->set($this->db->quoteName('enabled') . ' = 1')
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
			->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('system'))
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote('kunena'));
		$this->db->setQuery($query);
		$this->db->execute();

		require_once BIBLESTUDY_INSTALLER_PATH . '/schema.php';
		$schema    = new BibleStudyModelSchema();
		$results[] = $schema->updateSchemaTable('bsms_version');

		// Insert data from the old version, if it does not exist in the version table
		if ($version->id == 0 && $version->component)
		{
			$this->insertVersionData($version->version, $version->versiondate, $version->versionname, null);
		}

		/*
				foreach ( $results as $result )
					if (!empty($result['action']) && empty($result['success']))
						$this->addStatus ( JText::_('COM_BIBLESTUDY_INSTALL_'.strtoupper($result['action'])) . ' ' . $result ['name'], $result ['success'] );
		*/
		$this->insertVersion('migrateDatabase');

		if (!$this->getInstallError())
		{
			$this->setStep($this->getStep() + 1);
		}

		$this->checkTimeout(true);
	}

	// TODO: remove after making sure that all the old files get deleted (already disabled).
	/**
	 * Step extracting
	 */
	public function stepExtract()
	{
		$path = JPATH_ADMINISTRATOR . '/components/com_biblestudy/archive';
		if (JBSM::isDev() || !is_file("{$path}/fileformat"))
		{
			// Git install
			$dir = JPATH_ADMINISTRATOR . '/components/com_biblestudy/media/kunena';

			if (is_dir($dir))
			{
				JFolder::copy($dir, BIBLESTUDY_INSTALLER_MEDIAPATH, false, true);
			}

			$this->setStep($this->getStep() + 1);

			return;
		}

		$ext = file_get_contents("{$path}/fileformat");

		static $files = array(
			array('name' => 'com_biblestudy-admin', 'dest' => BIBLESTUDY_INSTALLER_ADMINPATH),
			array('name' => 'com_biblestudy-site', 'dest' => BIBLESTUDY_INSTALLER_SITEPATH),
			array('name' => 'com_biblestudy-media', 'dest' => BIBLESTUDY_INSTALLER_MEDIAPATH)
		);
		static $ignore = array(
			BIBLESTUDY_INSTALLER_ADMINPATH => array('index.html', 'jbsm.xml', 'jbsm.j25.xml', 'jbsm.php', 'api.php', 'archive', 'install', 'language'),
			BIBLESTUDY_INSTALLER_SITEPATH  => array('index.html', 'jbsm.php', 'router.php', 'COPYRIGHT.php', 'template', 'language')
		);
		$task = $this->getTask();

		// Extract archive files
		if (isset($files[$task]))
		{
			$file = $files[$task];

			if (is_file("{$path}/{$file['name']}{$ext}"))
			{
				$dest = $file['dest'];
				if (!empty($ignore[$dest]))
				{
					// Delete all files and folders (cleanup)
					$this->deleteFolder($dest, $ignore[$dest]);

					if ($dest == BIBLESTUDY_INSTALLER_SITEPATH)
					{
						$this->deleteFolder("$dest/template/blue_eagle", array('params.ini'));
					}
				}

				// Copy new files into folder
				$this->extract($path, $file['name'] . $ext, $dest, JBSM::isDev());
			}

			$this->setTask($task + 1);
		}
		else
		{
			if (function_exists('apc_clear_cache'))
			{
				@apc_clear_cache('system');
			}

			// Force page reload to avoid MySQL timeouts after extracting
			$this->checkTimeout(true);

			if (!$this->getInstallError())
			{
				$this->setStep($this->getStep() + 1);
			}
		}
	}

	/**
	 * Step through the Plugins
	 */
	public function stepPlugins()
	{
		// TODO: Complete smart search support
		//$this->installPlugin('plugins/plg_finder_kunena', 'finder', 'kunena', false, 1);
		$this->installPlugin('plugins/plg_bsms_alphauserpoints', 'kunena', 'alphauserpoints', false, 1);
		$this->installPlugin('plugins/plg_bsms_community', 'kunena', 'community', false, 2);
		$this->installPlugin('plugins/plg_bsms_comprofiler', 'kunena', 'comprofiler', false, 3);
		$this->installPlugin('plugins/plg_bsms_gravatar', 'kunena', 'gravatar', false, 4);
		$this->installPlugin('plugins/plg_bsms_uddeim', 'kunena', 'uddeim', false, 5);
		$this->installPlugin('plugins/plg_bsms_kunena', 'kunena', 'kunena', true, 6);
		$this->installPlugin('plugins/plg_bsms_joomla', 'kunena', 'joomla', true, 7);

		// TODO: install also menu module
		//$this->installModule('install/modules/mod_biblstudymenu', 'kunenamenu');

		if (function_exists('apc_clear_cache'))
		{
			@apc_clear_cache('system');
		}

		if (!$this->getInstallError())
		{
			$this->setStep($this->getStep() + 1);
		}
	}

	/**
	 * Work through Database update or move to migration
	 *
	 * @throws \JBSMInstallerException
	 */
	public function stepDatabase()
	{
		$task = $this->getTask();
		switch ($task)
		{
			case 0:
				if ($this->migrateDatabase())
				{
					$this->setTask($task + 1);
				}
				break;
			case 1:
				if ($this->installDatabase())
				{
					$this->setTask($task + 1);
				}
				break;
			case 2:
				if ($this->upgradeDatabase())
				{
					$this->setTask($task + 1);
				}
				break;
			case 3:
				if ($this->installSampleData())
				{
					$this->setTask($task + 1);
				}
				break;
			case 4:
				if ($this->recountThankyou())
				{
					$this->setTask($task + 1);
				}
				break;
			default:
				if (!$this->getInstallError())
				{
					$this->setStep($this->getStep() + 1);
				}
		}
	}

	/**
	 * Finish out installation steps
	 *
	 * @throws \JBSMInstallerException
	 */
	public function stepFinish()
	{
		JBSM::setup();

		$lang = JFactory::getLanguage();
		$lang->load('com_biblestudy', JPATH_SITE) || $lang->load('com_biblestudy', BIBLESTUDY_INSTALLER_SITEPATH);

		$this->createMenu(false);

		// Fix broken category aliases (workaround for < 2.0-DEV12 bug)
		JBSMCategoryHelper::fixAliases();

		// Clean cache, just in case
		JBSMMenuHelper::cleanCache();
		/** @var JCache|JCacheController $cache */
		$cache = JFactory::getCache();
		$cache->clean('com_biblestudy');

		// Delete installer file (only if not using GIT build).
		if (!JBSM::isDev())
		{
			JFile::delete(BIBLESTUDY_PATH_ADMIN . '/install.php');
		}

		if (!$this->getInstallError())
		{
			$this->updateVersionState('');
			$this->addStatus(JText::_('COM_BIBLESTUDY_INSTALL_SUCCESS'), true, '');

			$this->setStep($this->getStep() + 1);
		}
	}

	// TODO: move to migration
	/**
	 * @return bool
	 * @throws \Exception
	 * @throws \JBSMInstallerException
	 */
	public function migrateDatabase()
	{
		$version = $this->getVersion();

		if (!empty ($version->prefix))
		{
			// Migrate all tables from old installation

			$app   = JFactory::getApplication();
			$state = $app->getUserState('com_biblestudy.install.dbstate', null);

			// First run: find tables that potentially need migration
			if ($state === null)
			{
				$state = $this->listTables($version->prefix);
			}

			// Handle only first table in the list
			$oldtable = array_shift($state);

			if ($oldtable)
			{
				$newtable = preg_replace('/^' . $version->prefix . '/i', 'bsms_', $oldtable);
				$result   = $this->migrateTable($version->prefix, $oldtable, $newtable);

				if ($result)
				{
					$this->addStatus(ucfirst($result ['action']) . ' ' . $result ['name'], true);
				}

				// Save user state with remaining tables
				$app->setUserState('com_biblestudy.install.dbstate', $state);

				// Database migration continues
				return false;
			}
			else
			{
				// Reset user state
				$this->updateVersionState('installDatabase');
				$app->setUserState('com_biblestudy.install.dbstate', null);
			}
		}

		// Database migration complete
		return true;
	}

	/**
	 * Install DB from PHP
	 *
	 * @return bool
	 * @throws \Exception
	 * @throws \JBSMInstallerException
	 * @throws \KunenaSchemaException
	 */
	public function installDatabase()
	{
		static $schema = null;
		static $create = null;
		static $tables = null;

		if ($schema === null)
		{
			// Run only once: get table creation SQL and existing tables
			require_once BIBLESTUDY_INSTALLER_PATH . '/schema.php';
			$schema = new BibleStudyModelSchema();
			$create = $schema->getCreateSQL();
			$tables = $this->listTables('bsms_', true);
		}

		$app   = JFactory::getApplication();
		$state = $app->getUserState('com_biblestudy.install.dbstate', null);

		// First run: get all tables
		if ($state === null)
		{
			$state = array_keys($create);
		}

		// Handle only first table in the list
		$table = array_shift($state);

		if ($table)
		{
			if (!isset($tables[$table]))
			{
				$result = $schema->updateSchemaTable($table);

				if ($result)
				{
					$this->addStatus(JText::_('COM_BIBLESTUDY_INSTALL_CREATE') . ' ' . $result ['name'], $result ['success']);
				}
			}

			// Save user state with remaining tables
			$app->setUserState('com_biblestudy.install.dbstate', $state);

			// Database install continues
			return false;
		}
		else
		{
			// Reset user state
			$this->updateVersionState('upgradeDatabase');
			$app->setUserState('com_biblestudy.install.dbstate', null);
		}

		// Database install complete
		return true;
	}

	/**
	 * @return bool
	 * @throws \Exception
	 * @throws \JBSMInstallerException
	 */
	public function upgradeDatabase()
	{
		static $xml = null;

		// If there's no version installed, there's nothing to do
		$curversion = $this->getVersion();

		if (!$curversion->component)
		{
			return true;
		}

		if ($xml === null)
		{
			// Run only once: Get migration SQL from our XML file
			$xml = simplexml_load_file(BIBLESTUDY_INSTALLER_PATH . '/jbsm.install.upgrade.xml');
		}

		if ($xml === false)
		{
			$this->addStatus(JText::_('COM_BIBLESTUDY_INSTALL_DB_UPGRADE_FAILED_XML'), false, '', 'upgrade');
		}

		$app   = JFactory::getApplication();
		$state = $app->getUserState('com_biblestudy.install.dbstate', null);

		// First run: initialize state and migrate configuration
		if ($state === null)
		{
			$state = array();
		}

		// Allow queries to fail
		$this->db->setDebug(false);

		foreach ($xml->upgrade[0] as $version)
		{
			// If we have already upgraded to this version, continue to the next one
			$vernum = (string) $version['version'];
			if (!empty($state[$vernum]))
			{
				continue;
			}

			// Update state
			$state[$vernum] = 1;

			if ($version['version'] == '@' . 'jbsmversion' . '@')
			{
				$git    = 1;
				$vernum = JBSM::version();
			}

			if (isset($git) || version_compare(strtolower($version['version']), strtolower($curversion->version), '>'))
			{
				foreach ($version as $action)
				{
					$result = $this->processUpgradeXMLNode($action);

					if ($result)
					{
						$this->addStatus($result ['action'] . ' ' . $result ['name'], $result ['success']);
					}
				}

				$this->addStatus(JText::sprintf('COM_BIBLESTUDY_INSTALL_VERSION_UPGRADED', $vernum), true, '', 'upgrade');

				// Save user state with remaining tables
				$app->setUserState('com_biblestudy.install.dbstate', $state);

				// Database install continues
				return false;
			}
		}
		// Reset user state
		$this->updateVersionState('InstallSampleData');
		$app->setUserState('com_biblestudy.install.dbstate', null);

		// Database install complete
		return true;
	}

	/**
	 * @param   object  $action  Return of xml actions
	 *
	 * @return array|mixed|null
	 */
	function processUpgradeXMLNode($action)
	{
		$result   = null;
		$nodeName = $action->getName();
		$mode     = strtolower((string) $action['mode']);
		$success  = false;
		switch ($nodeName)
		{
			case 'phpfile':
				$filename = $action['name'];
				$include  = BIBLESTUDY_INSTALLER_PATH . "/sql/updates/php/{$filename}.php";
				$function = 'bsms_' . strtr($filename, array('.' => '', '-' => '_'));
				if (is_file($include))
				{
					require($include);

					if (is_callable($function))
					{
						$result = call_user_func($function, $this);

						if (is_array($result))
						{
							$success = $result['success'];
						}
						else
						{
							$success = true;
						}
					}
				}

				if (!$success && !$result)
				{
					$result = array('action' => JText::_('COM_BIBLESTUDY_INSTALL_INCLUDE_STATUS'), 'name' => $filename . '.php', 'success' => $success);
				}

				break;
			case 'query':
				$query = (string) $action;
				$this->db->setQuery($query);

				try
				{
					$this->db->execute();

					if (!$this->db->getErrorNum())
					{
						$success = true;
					}
				}
				catch (Exception $e)
				{
					$success = false;
				}

				if ($action['mode'] == 'silenterror' || !$this->db->getAffectedRows() || $success)
				{
					$result = null;
				}
				else
				{
					$result = array('action' => 'SQL Query: ' . $query, 'name' => '', 'success' => $success);
				}
				break;
			default:
				$result = array('action' => 'fail', 'name' => $nodeName, 'success' => false);
		}

		return $result;
	}

	/**
	 * @return bool
	 * @throws \JBSMInstallerException
	 */
	public function installSampleData()
	{
		require_once(BIBLESTUDY_INSTALLER_PATH . '/sql/install/php/sampledata.php');

		if (installSampleData())
		{
			$this->addStatus(JText::_('COM_BIBLESTUDY_INSTALL_SAMPLEDATA'), true);
		}

		return true;
	}

	/**
	 * Get Version Prefix
	 *
	 * @return bool|null
	 * @throws \JBSMInstallerException
	 */
	public function getVersionPrefix()
	{
		if ($this->_versionprefix !== false)
		{
			return $this->_versionprefix;
		}

		$match = $this->detectTable($this->_versiontablearray);

		if (isset ($match ['prefix']))
		{
			$this->_versionprefix = $match ['prefix'];
		}
		else
		{
			$this->_versionprefix = null;
		}

		return $this->_versionprefix;
	}

	// TODO: move to migration
	/**
	 * @return array
	 * @throws \JBSMInstallerException
	 */
	public function getDetectVersions()
	{
		if (!empty($this->_versions))
		{
			return $this->_versions;
		}

		$jbsm    = $this->getInstalledVersion('bsms_', $this->_kVersions);

		if (!empty($jbsm->state))
		{
			$this->_versions['failed'] = $jbsm;
			$jbsm                    = $this->getInstalledVersion('bsms_', $this->_kVersions, true);

			if (version_compare($jbsm->version, '1.6.0-ALPHA', "<"))
			{
				$jbsm->ignore = true;
			}
		}

		if ($jbsm->component && empty($jbsm->ignore))
		{
			$this->_versions['biblestudy'] = $jbsm;
			$migrate                   = false;
		}

		if (empty($jbsm->component))
		{
			$this->_versions['biblestudy'] = $jbsm;
		}
		else
		{
			$uninstall                    = clone $jbsm;
			$uninstall->action            = 'UNINSTALL';
			$this->_versions['uninstall'] = $uninstall;
		}

		foreach ($this->_versions as $version)
		{
			$version->label       = $this->getActionText($version);
			$version->description = $this->getActionText($version, 'desc');
			$version->hint        = $this->getActionText($version, 'hint');
			$version->warning     = $this->getActionText($version, 'warn');
			$version->link        = JUri::base(true) . '/index.php?option=com_biblestudy&view=install&task=' . strtolower($version->action) . '&' . JSession::getFormToken() . '=1';
		}

		$jbsm->warning = '';

		return $this->_versions;
	}

	/**
	 * Check to see if Migration is possible.
	 *
	 * @param $new
	 * @param $old
	 *
	 * @return bool
	 * @throws \JBSMInstallerException
	 */
	public function isMigration($new, $old)
	{
		// If K1.6 not installed: migrate
		if (!$new->component || !$this->detectTable($new->prefix . 'messages'))
		{
			return true;
		}

		// If old not installed: upgrade
		if (!$old->component || !$this->detectTable($old->prefix . 'messages'))
		{
			return false;
		}

		// User is currently using K1.6: upgrade
		if (strtotime($new->installdate) > strtotime($old->installdate))
		{
			return false;
		}

		// User is currently using K1.0/K1.5: migrate
		if (strtotime($new->installdate) < strtotime($old->installdate))
		{
			return true;
		}
		// Both K1.5 and K1.6 were installed during the same day.. Not going to be easy choice..

		// Let's assume that this could be migration
		return true;
	}

	// TODO: move to migration
	/**
	 * @param      $prefix
	 * @param      $versionlist
	 * @param bool $state
	 *
	 * @return mixed|null|\StdClass
	 * @throws \JBSMInstallerException
	 */
	public function getInstalledVersion($prefix, $versionlist, $state = false)
	{
		if (!$state && isset($this->_installed[$prefix]))
		{
			return $this->_installed[$prefix];
		}

		if ($prefix === null)
		{
			$versionprefix = $this->getVersionPrefix();
		}
		else
		{
			if ($this->detectTable($prefix . 'version'))
			{
				$versionprefix = $prefix;
			}
			else
			{
				$versionprefix = null;
			}
		}

		if ($versionprefix)
		{
			// Version table exists, try to get installed version
			$state = $state ? " WHERE state=''" : "";
			$this->db->setQuery("SELECT * FROM " . $this->db->quoteName($this->db->getPrefix() . $versionprefix . 'version') . $state . " ORDER BY `id` DESC", 0, 1);
			$version = $this->db->loadObject();

			if ($this->db->getErrorNum())
			{
				throw new JBSMInstallerException ($this->db->getErrorMsg(), $this->db->getErrorNum());
			}

			if ($version)
			{
				$version->version = strtolower($version->version);
				$version->prefix  = $versionprefix;
				$version->component = 'biblestudy';
				$version->version = strtoupper($version->version);

				// Version table may contain dummy version.. Ignore it
				if (!$version || version_compare($version->version, '0.1.0', "<"))
				{
					unset ($version);
				}
			}
		}

		if (!isset ($version))
		{
			// No version found -- try to detect version by searching some missing fields
			$match = $this->detectTable($versionlist);

			// Clean install
			if (empty ($match))
			{
				return $this->_installed = null;
			}

			// Create version object
			$version              = new StdClass ();
			$version->id          = 0;
			$version->component   = $match ['component'];
			$version->version     = strtoupper($match ['version']);
			$version->versiondate = $match ['date'];
			$version->installdate = '';
			$version->versionname = '';
			$version->prefix      = $match ['prefix'];
		}

		$version->action = $this->getInstallAction($version);

		return $this->_installed[$prefix] = $version;
	}

	/**
	 * @param string $state
	 *
	 * @throws \JBSMInstallerException
	 */
	protected function insertVersion($state = 'beginInstall')
	{
		// Insert data from the new version
		$this->insertVersionData(JBSM::version(), JBSM::versionDate(), JBSM::versionName(), $state);
	}

	/**
	 * @param $state
	 *
	 * @throws \JBSMInstallerException
	 */
	protected function updateVersionState($state)
	{
		// Insert data from the new version
		$this->db->setQuery("UPDATE " . $this->db->quoteName($this->db->getPrefix() . 'bsms_version') . " SET state = " . $this->db->Quote($state) . " ORDER BY id DESC LIMIT 1");
		$this->db->execute();

		if ($this->db->getErrorNum())
		{
			throw new JBSMInstallerException ($this->db->getErrorMsg(), $this->db->getErrorNum());
		}
	}

	/**
	 * @param        $version
	 * @param string $type
	 * @param null   $action
	 *
	 * @return string
	 */
	function getActionText($version, $type = '', $action = null)
	{
		/* Translations generated:

		Installation types: COM_BIBLESTUDY_INSTALL_UPGRADE, COM_BIBLESTUDY_INSTALL_DOWNGRADE, COM_BIBLESTUDY_INSTALL_REINSTALL,
		COM_BIBLESTUDY_INSTALL_MIGRATE, COM_BIBLESTUDY_INSTALL_INSTALL, COM_BIBLESTUDY_INSTALL_UNINSTALL, COM_BIBLESTUDY_INSTALL_RESTORE

		Installation descriptions: COM_BIBLESTUDY_INSTALL_UPGRADE_DESC, COM_BIBLESTUDY_INSTALL_DOWNGRADE_DESC, COM_BIBLESTUDY_INSTALL_REINSTALL_DESC,
		COM_BIBLESTUDY_INSTALL_MIGRATE_DESC, COM_BIBLESTUDY_INSTALL_INSTALL_DESC, COM_BIBLESTUDY_INSTALL_UNINSTALL_DESC, COM_BIBLESTUDY_INSTALL_RESTORE_DESC

		Installation hints: COM_BIBLESTUDY_INSTALL_UPGRADE_HINT, COM_BIBLESTUDY_INSTALL_DOWNGRADE_HINT, COM_BIBLESTUDY_INSTALL_REINSTALL_HINT,
		COM_BIBLESTUDY_INSTALL_MIGRATE_HINT, COM_BIBLESTUDY_INSTALL_INSTALL_HINT, COM_BIBLESTUDY_INSTALL_UNINSTALL_HINT, COM_BIBLESTUDY_INSTALL_RESTORE_HINT

		Installation warnings: COM_BIBLESTUDY_INSTALL_UPGRADE_WARN, COM_BIBLESTUDY_INSTALL_DOWNGRADE_WARN,
		COM_BIBLESTUDY_INSTALL_MIGRATE_WARN, COM_BIBLESTUDY_INSTALL_UNINSTALL_WARN, COM_BIBLESTUDY_INSTALL_RESTORE_WARN

		 */

		static $search = array('#COMPONENT_OLD#', '#VERSION_OLD#', '#VERSION#');
		$replace = array($version->component, $version->version, JBSM::version());

		if (!$action)
		{
			$action = $version->action;
		}

		if ($type == 'warn' && ($action == 'INSTALL' || $action == 'REINSTALL'))
		{
			return '';
		}

		$str = '';

		if ($type == 'hint' || $type == 'warn')
		{
			$str .= '<strong class="k' . $type . '">' . JText::_('COM_BIBLESTUDY_INSTALL_' . $type) . '</strong> ';
		}

		if ($action && $type)
		{
			$type = '_' . $type;
		}

		$str .= str_replace($search, $replace, JText::_('COM_BIBLESTUDY_INSTALL_' . $action . $type));

		return $str;
	}

	/**
	 * @param null $version
	 *
	 * @return bool|string
	 */
	public function getInstallAction($version = null)
	{
		require_once __DIR__ . '/../api.php';

		if ($version->component === null)
		{
			$this->_action = 'INSTALL';
		}
		else
		{
			if ($version->prefix != 'bsms_')
			{
				$this->_action = 'MIGRATE';
			}
			else
			{
				if (version_compare(strtolower(JBSM::version()), strtolower($version->version), '>'))
				{
					$this->_action = 'UPGRADE';
				}
				else
				{
					if (version_compare(strtolower(JBSM::version()), strtolower($version->version), '<'))
					{
						$this->_action = 'DOWNGRADE';
					}
					else
					{
						$this->_action = 'REINSTALL';
					}
				}
			}
		}

		return $this->_action;
	}

	/**
	 * @param $detectlist
	 *
	 * @return array
	 * @throws \JBSMInstallerException
	 */
	protected function detectTable($detectlist)
	{
		// Cache
		static $tables = array();
		static $fields = array();

		$found = 0;
		if (is_string($detectlist))
		{
			$detectlist = array(array('table' => $detectlist));
		}

		foreach ($detectlist as $detect)
		{
			// If no detection is needed, return current item
			if (!isset ($detect ['table']))
			{
				return $detect;
			}

			$table = $this->db->getPrefix() . $detect ['table'];

			// Match if table exists
			if (!isset ($tables [$table])) // Not cached
			{
				$this->db->setQuery("SHOW TABLES LIKE " . $this->db->quote($table));
				$result = $this->db->loadResult();

				if ($this->db->getErrorNum())
				{
					throw new JBSMInstallerException ($this->db->getErrorMsg(), $this->db->getErrorNum());
				}

				$tables [$table] = $result;
			}

			if (!empty ($tables [$table]))
			{
				$found = 1;
			}

			// Match if column in a table exists
			if ($found && isset ($detect ['column']))
			{
				if (!isset ($fields [$table])) // Not cached
				{
					$this->db->setQuery("SHOW COLUMNS FROM " . $this->db->quoteName($table));
					$result = $this->db->loadObjectList('Field');

					if ($this->db->getErrorNum())
					{
						throw new JBSMInstallerException ($this->db->getErrorMsg(), $this->db->getErrorNum());
					}

					$fields [$table] = $result;
				}

				if (!isset ($fields [$table] [$detect ['column']]))
				{
					$found = 0;
				} // Sorry, no match
			}

			if ($found)
			{
				return $detect;
			}
		}

		return array();
	}

	// helper function to migrate table
	// TODO: move to migration
	/**
	 * @param $oldprefix
	 * @param $oldtable
	 * @param $newtable
	 *
	 * @return array
	 * @throws \JBSMInstallerException
	 */
	protected function migrateTable($oldprefix, $oldtable, $newtable)
	{
		$tables    = $this->listTables('bsms_');
		$oldtables = $this->listTables($oldprefix);

		if ($oldtable == $newtable || !isset ($oldtables [$oldtable]) || isset ($tables [$newtable]))
		{
			return null;
		} // Nothing to migrate

		// Make identical copy from the table with new name
		$create = $this->db->getTableCreate($this->db->getPrefix() . $oldtable);
		$create = implode(' ', $create);

		$collation = $this->db->getCollation();

		if (!strstr($collation, 'utf8'))
		{
			$collation = 'utf8_general_ci';
		}

		if (!$create)
		{
			return null;
		}

		$create = preg_replace('/(DEFAULT )?CHARACTER SET [\w\d]+/', '', $create);
		$create = preg_replace('/(DEFAULT )?CHARSET=[\w\d]+/', '', $create);
		$create = preg_replace('/COLLATE [\w\d_]+/', '', $create);
		$create = preg_replace('/TYPE\s*=?/', 'ENGINE=', $create);
		$create .= " DEFAULT CHARACTER SET utf8 COLLATE {$collation}";
		$query = preg_replace('/' . $this->db->getPrefix() . $oldtable . '/', $this->db->getPrefix() . $newtable, $create);
		$this->db->setQuery($query);
		$this->db->execute();

		if ($this->db->getErrorNum())
		{
			throw new JBSMInstallerException ($this->db->getErrorMsg(), $this->db->getErrorNum());
		}

		$this->tables ['bsms_'] [$newtable] = $newtable;

		// And copy data into it
		$sql = "INSERT INTO " . $this->db->quoteName($this->db->getPrefix() . $newtable) . ' ' . $this->selectWithStripslashes($this->db->getPrefix() . $oldtable);
		$this->db->setQuery($sql);
		$this->db->execute();

		if ($this->db->getErrorNum())
		{
			throw new JBSMInstallerException ($this->db->getErrorMsg(), $this->db->getErrorNum());
		}

		return array('name' => $oldtable, 'action' => 'migrate', 'sql' => $sql);
	}

	// TODO: move to migration
	/**
	 * @param $table
	 *
	 * @return string
	 */
	public function selectWithStripslashes($table)
	{
		$fields = $this->db->getTableColumns($table);
		$select = array();

		foreach ($fields as $field => $type)
		{
			$isString = preg_match('/text|char/', $type);
			$select[] = ($isString ? "REPLACE(REPLACE(REPLACE({$this->db->quoteName($field)}, {$this->db->Quote('\\\\')}, {$this->db->Quote('\\')}),{$this->db->Quote('\\\'')} ,{$this->db->Quote('\'')}),{$this->db->Quote('\"')} ,{$this->db->Quote('"')}) AS " : '') . $this->db->quoteName($field);
		}

		$select = implode(', ', $select);

		return "SELECT {$select} FROM {$table}";
	}

	/**
	 * @return array|null
	 * @throws \JBSMInstallerException
	 */
	function createVersionTable()
	{
		$tables = $this->listTables('bsms_');

		if (isset ($tables ['bsms_version']))
		{
			return null;
		} // Nothing to migrate

		$collation = $this->db->getCollation();

		if (!strstr($collation, 'utf8'))
		{
			$collation = 'utf8_general_ci';
		}

		$query = "CREATE TABLE IF NOT EXISTS `" . $this->db->getPrefix() . "bsms_version` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`version` varchar(20) NOT NULL,
		`versiondate` date NOT NULL,
		`installdate` date NOT NULL,
		`build` varchar(20) NOT NULL,
		`versionname` varchar(40) DEFAULT NULL,
		`state` varchar(32) NOT NULL,
		PRIMARY KEY (`id`)
		) DEFAULT CHARACTER SET utf8 COLLATE {$collation};";
		$this->db->setQuery($query);
		$this->db->execute();

		if ($this->db->getErrorNum())
		{
			throw new JBSMInstallerException ($this->db->getErrorMsg(), $this->db->getErrorNum());
		}

		$this->tables ['bsms_'] ['bsms_version'] = 'bsms_version';

		return array('action' => JText::_('COM_BIBLESTUDY_INSTALL_CREATE'), 'name' => 'bsms_version', 'sql' => $query);
	}

	// also insert old version if not in the table
	/**
	 * @param        $version
	 * @param        $versiondate
	 * @param        $versionname
	 * @param string $state
	 *
	 * @throws \JBSMInstallerException
	 */
	protected function insertVersionData($version, $versiondate, $versionname, $state = '')
	{
		$this->db->setQuery("INSERT INTO `#__bsms_version` SET
			`version` = {$this->db->quote($version)},
			`versiondate` = {$this->db->quote($versiondate)},
			`installdate` = CURDATE(),
			`versionname` = {$this->db->quote($versionname)},
			`state` = {$this->db->quote($state)}");
		$this->db->execute();

		if ($this->db->getErrorNum())
		{
			throw new JBSMInstallerException ($this->db->getErrorMsg(), $this->db->getErrorNum());
		}
	}

	/**
	 * @param      $prefix
	 * @param bool $reload
	 *
	 * @return mixed
	 * @throws \JBSMInstallerException
	 */
	protected function listTables($prefix, $reload = false)
	{
		if (isset ($this->tables [$prefix]) && !$reload)
		{
			return $this->tables [$prefix];
		}

		$this->db->setQuery("SHOW TABLES LIKE " . $this->db->quote($this->db->getPrefix() . $prefix . '%'));
		$list = (array) $this->db->loadColumn();

		if ($this->db->getErrorNum())
		{
			throw new JBSMInstallerException ($this->db->getErrorMsg(), $this->db->getErrorNum());
		}

		$this->tables [$prefix] = array();

		foreach ($list as $table)
		{
			$table                           = preg_replace('/^' . $this->db->getPrefix() . '/', '', $table);
			$this->tables [$prefix] [$table] = $table;
		}

		return $this->tables [$prefix];
	}

	/**
	 * @param $prefix
	 *
	 * @throws \JBSMInstallerException
	 */
	function deleteTables($prefix)
	{
		$tables = $this->listTables($prefix);

		foreach ($tables as $table)
		{
			$this->db->setQuery("DROP TABLE IF EXISTS " . $this->db->quoteName($this->db->getPrefix() . $table));
			$this->db->execute();

			if ($this->db->getErrorNum())
			{
				throw new JBSMInstallerException ($this->db->getErrorMsg(), $this->db->getErrorNum());
			}
		}

		unset($this->tables [$prefix]);
	}

	/**
	 * @throws \Exception
	 */
	protected function deleteMenu()
	{
		/** @type JTableMenu $table */
		$table = JTable::getInstance('MenuType');
		$table->load(array('menutype' => 'biblestudymenu'));

		if ($table->id)
		{
			$success = $table->delete();

			if (!$success)
			{
				JFactory::getApplication()->enqueueMessage($table->getError(), 'error');
			}
		}

		/** @var JCache|JCacheController $cache */
		$cache = JFactory::getCache();
		$cache->clean('mod_menu');
	}

	/**
	 * @param bool $stop
	 * @param int  $timeout
	 *
	 * @return bool
	 */
	public function checkTimeout($stop = false, $timeout = 1)
	{
		static $start = null;

		if ($stop)
		{
			$start = 0;
		}

		$time = microtime(true);

		if ($start === null)
		{
			$start = $time;

			return false;
		}

		if ($time - $start < $timeout)
		{
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function recountThankyou()
	{
		//Only perform this action if upgrading form previous version
		$version = $this->getVersion();

		if (version_compare($version->version, '2.0.0-BETA2', ">"))
		{
			return true;
		}

		// If the migration is from previous version thant 1.6.0 doesn't need to recount
		if (version_compare($version->version, '1.6.0', "<"))
		{
			return true;
		}

		return true;
	}
}

/**
 * Class JBSMInstallerException
 */
class JBSMInstallerException extends Exception
{
}
