<?php
/**
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Proclaim Install Script
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class Com_BiblestudyInstallerScript
{
	/** @var string The component's name
	 * @since 1.5 */
	protected $biblestudy_extension = 'com_biblestudy';

	/** @var string Path to Mysql files
	 * @since 1.5
	 */
	public $filePath = '/components/com_biblestudy/install/sql/updates/mysql';

	/**
	 * This is Minimum requirements for: PHP, MySQL, Joomla
	 *
	 * @var array Requirements
	 * @since 9.0.9
	 */
	protected $versions = array(
		'PHP'     => array(
			'5.6' => '5.6.30',
			'7.0' => '7.0.13',
			'7.1' => '7.1.0',
			'7.2' => '7.2.1',
			'0'   => '7.2.11' // Preferred version
		),
		'MySQL'   => array(
			'5.1' => '5.1',
			'5.5' => '5.5.3',
			'0'   => '5.5.3' // Preferred version
		),
		'Joomla!' => array(
			'3.6' => '3.6.3',
			'3.7' => '3.7.0',
			'0'   => '3.8.3' // Preferred version
		)
	);

	protected $status;

	/**
	 * The list of extra modules and plugins to install
	 *
	 * @author CWM Team
	 * @var   array $_installation_queue Array of Items to install
	 * @since 9.0.18
	 */
	private $installation_queue = [
		// -- modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules' => [
			'admin' => [],
			'site'  => ['biblestudy' => 0, 'biblestudy_podcast' => 0,],
		],
		// -- plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => [
			'finder' => ['biblestudy' => 1,],
			'search' => ['biblestudysearch' => 0,],
			'system' => ['jbspodcast' => 0, 'jbsbackup' => '0'],
		],
	];

	protected $extensions = array('dom', 'gd', 'json', 'pcre', 'SimpleXML');

	/**
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 *
	 * @param   string          $type    Type of install
	 * @param   JInstallerFile  $parent  Where it is coming from
	 *
	 * @return boolean
	 *
	 * @throws  Exception
	 * @since   1.5
	 */
	public function preflight($type, $parent)
	{
		$parent   = $parent->getParent();
		$manifest = $parent->getManifest();

		// Include the JLog class.
		jimport('joomla.log.log');
		JLog::addLogger(
			array(
				'text_file' => 'com_biblestudy.errors.php'
			),
			JLog::ALL,
			'com_biblestudy'
		);

		// Prevent installation if requirements are not met.
		if (!$this->checkRequirements($manifest->version))
		{
			return false;
		}

		$adminPath = $parent->getPath('extension_administrator');
		$sitePath  = $parent->getPath('extension_site');

		if (is_file($adminPath . '/admin.biblestudy.php'))
		{
			// JBSM 8.0 or older release found, clean up the directories.
			static $ignoreAdmin = array('index.html', 'biblestudy.xml', 'archive');

			if (is_file($adminPath . '/install.script.php'))
			{
				// JBSM 6.2 or older release..
				$ignoreAdmin[] = 'install.script.php';
				$ignoreAdmin[] = 'admin.biblestudy.php';
			}

			static $ignoreSite = array('index.html', 'biblestudy.php', 'router.php', 'COPYRIGHT.php', 'CHANGELOG.php');
			$this->deleteFolder($adminPath, $ignoreAdmin);
			$this->deleteFolder($sitePath, $ignoreSite);
		}

		// Remove all old install files before install/upgrade
		if (JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_biblestudy/install'))
		{
			JFolder::delete(JPATH_ADMINISTRATOR . '/components/com_biblestudy/install');
		}

		// Remove old BibleStudy Helper. @since 9.0.14
		if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/biblestudy.php'))
		{
			JFile::delete(JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/biblestudy.php');
		}

		// Remove Variability plupload folder from media not use. 9.1.2
		if (JFolder::exists(JPATH_ROOT . '/media/com_biblestudy/plupload'))
		{
			JFolder::delete(JPATH_ROOT . '/media/com_biblestudy/plupload');
		}

		return true;
	}

	/**
	 * Install
	 *
	 * @param   JInstallerFile  $parent  Where call is coming from
	 *
	 * @return  bool
	 *
	 * @since 1.5
	 */
	public function install($parent)
	{
		// Delete all cached files.
		$cacheDir = JPATH_CACHE . '/biblestudy';

		if (is_dir($cacheDir))
		{
			JFolder::delete($cacheDir);
		}

		JFolder::create($cacheDir);

		return true;
	}

	/**
	 * Rout to Install
	 *
	 * @param   JInstallerFile  $parent  ?
	 *
	 * @return bool
	 *
	 * @since 1.5
	 */
	public function discover_install($parent)
	{
		return self::install($parent);
	}

	/**
	 * Update will go to install
	 *
	 * @param   JInstallerFile  $parent  ?
	 *
	 * @return bool
	 *
	 * @since 1.5
	 */
	public function update($parent)
	{
		return self::install($parent);
	}

	/**
	 * Uninstall rout to JBSMModelInstall
	 *
	 * @param   JInstallerFile  $parent  ?
	 *
	 * @return bool
	 *
	 * @since 1.5
	 * @throws Exception
	 */
	public function uninstall($parent)
	{
		$adminpath = $parent->getParent()->getPath('extension_administrator');
		$model     = "{$adminpath}/models/install.php";

		if (file_exists($model))
		{
			require_once $model;
			$installer = new BibleStudyModelInstall;
			$installer->uninstall();
		}

		// Uninstall sub-extensions
		$this->_uninstallSubextensions($parent);

		return true;
	}

	/**
	 * Post Flight
	 *
	 * @param   string          $type    Type of install
	 * @param   JInstallerFile  $parent  Where it is coming from
	 *
	 * @return   void
	 *
	 * @throws  Exception
	 * @since   1.5
	 */
	public function postflight($type, $parent)
	{
		// Import filesystem libraries. Perhaps not necessary, but does not hurt
		jimport('joomla.filesystem.file');

		if (!JFile::exists(JPATH_SITE . '/images/biblestudy/logo.png'))
		{
			// Copy the images to the new folder
			JFolder::copy('/media/com_biblestudy/images', 'images/biblestudy/', JPATH_SITE, true);
		}

		// An redirect to a new location after the install is completed.
		$controller = JControllerLegacy::getInstance('Biblestudy');
		$controller->setRedirect(
			JUri::base() .
			'index.php?option=com_biblestudy&view=install&task=install.browse&scanstate=start&' .
			JSession::getFormToken() . '=1');
		$controller->redirect();
	}

	/**
	 * Check Requirements
	 *
	 * @param   string  $version  JBSM version to check for.
	 *
	 * @return bool
	 *
	 * @throws  Exception
	 * @since 7.1.0
	 */
	public function checkRequirements($version)
	{
		// Include the JLog class.
		jimport('joomla.log.log');
		JLog::addLogger(
			array(
				'text_file' => 'com_biblestudy.errors.php'
			),
			JLog::ALL,
			'com_biblestudy'
		);

		$db   = JFactory::getDbo();
		$pass = $this->checkVersion('PHP', phpversion());
		$pass &= $this->checkVersion('Joomla!', JVERSION);
		$pass &= $this->checkVersion('MySQL', $db->getVersion());
		$pass &= $this->checkDbo($db->name, array('mysql', 'mysqli', 'pdo'));
		$pass &= $this->checkExtensions($this->extensions);
		$pass &= $this->checkJBSM($version);

		return $pass;
	}

	// Internal functions

	/**
	 * Check Database Driver
	 *
	 * @param   string  $name   Driver
	 * @param   array   $types  Array of drivers supported
	 *
	 * @return bool
	 *
	 * @throws Exception
	 *
	 * @since 7.1.0
	 */
	protected function checkDbo($name, $types)
	{
		$app = JFactory::getApplication();

		if (in_array($name, $types))
		{
			return true;
		}

		$app->enqueueMessage(sprintf("Database driver '%s' is not supported. Please use MySQL instead.", $name), 'notice');
		JLog::add(
			sprintf("Database driver '%s' is not supported. Please use MySQL instead.", $name),
			JLog::NOTICE,
			$this->biblestudy_extension
		);

		return false;
	}

	/**
	 * Check PHP Extension Requirement
	 *
	 * @param   array  $extensions  Array of version to look for
	 *
	 * @return int
	 *
	 * @throws Exception
	 *
	 * @since 7.1.0
	 */
	protected function checkExtensions($extensions)
	{
		$app  = JFactory::getApplication();
		$pass = 1;

		foreach ($extensions as $name)
		{
			if (!extension_loaded($name))
			{
				$pass = 0;
				$app->enqueueMessage(sprintf("Required PHP extension '%s' is missing. Please install it into your system.", $name), 'notice');
				JLog::add(
					sprintf("Required PHP extension '%s' is missing. Please install it into your system.", $name),
					JLog::NOTICE,
					$this->biblestudy_extension
				);
			}
		}

		return $pass;
	}

	/**
	 * Check Versions of JBSM
	 *
	 * @param   string  $name     Name of version
	 * @param   string  $version  Version to look for
	 *
	 * @return bool
	 *
	 * @throws Exception
	 *
	 * @since 7.1.0
	 */
	protected function checkVersion($name, $version)
	{
		$app   = JFactory::getApplication();
		$major = $minor = 0;

		foreach ($this->versions[$name] as $major => $minor)
		{
			if (!$major || version_compare($version, $major, '<'))
			{
				continue;
			}

			if (version_compare($version, $minor, '>='))
			{
				return true;
			}

			break;
		}

		if (!$major)
		{
			$minor = reset($this->versions[$name]);
		}

		$recommended = end($this->versions[$name]);
		$app->enqueueMessage(
			sprintf("%s %s is not supported. Minimum required version is %s %s, but it is higly recommended to use %s %s or later.",
			$name, $version, $name, $minor, $name, $recommended
			), 'notice'
		);

		JLog::add(
			sprintf("%s %s is not supported. Minimum required version is %s %s, but it is higly recommended to use %s %s or later.",
			$name, $version, $name, $minor, $name, $recommended
		), JLog::ERROR, 'com_biblestudy');

		return false;
	}

	/**
	 * Check the installed version of Proclaim
	 *
	 * @param   string  $version  Proclaim Version to check for
	 *
	 * @return bool
	 *
	 * @throws Exception
	 *
	 * @since 7.1.0
	 */
	protected function checkJBSM($version)
	{
		$app = JFactory::getApplication();

		$db = JFactory::getDbo();

		// Check if JBSM can be found from the database
		$table = $db->getPrefix() . 'bsms_update';
		$db->setQuery("SHOW TABLES LIKE {$db->q($table)}");

		if ($db->loadResult() != $table)
		{
			return true;
		}

		// Get installed JBSM version
		$query = $db->getQuery(true);
		$query->select('version')
			->from($db->qn($table))
			->order('id DESC');
		$db->setQuery($query, 0, 1);
		$installed = $db->loadResult();

		if (!$installed)
		{
			JLog::add('Found No installd version.', JLog::NOTICE, $this->biblestudy_extension);

			return true;
		}

		// Always allow upgrade to the newer version
		if (version_compare($version, $installed, '>='))
		{
			return true;
		}

		// @todo need to move to language string.
		$app->enqueueMessage(sprintf('Sorry, it is not possible to downgrade BibleStudy %s to version %s.', $installed, $version), 'notice');
		JLog::add(
			sprintf('Sorry, it is not possible to downgrade BibleStudy %s to version %s.', $installed, $version),
			JLog::NOTICE,
			$this->biblestudy_extension
		);

		return false;
	}

	/**
	 * Delete Files
	 *
	 * @param   string  $path    Path of File
	 * @param   array   $ignore  Array of files to Ignore
	 *
	 * @return void
	 *
	 * @since 7.1.0
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
	 * Delete Folders
	 *
	 * @param   string  $path    Path to folders
	 * @param   array   $ignore  Ingnore array of files
	 *
	 * @return void;
	 *
	 * @since 7.1.0
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
	 * Delete Folder
	 *
	 * @param   string  $path    Path of folder
	 * @param   array   $ignore  Ignore list
	 *
	 * @return void
	 *
	 * @since 7.1.0
	 */
	public function deleteFolder($path, $ignore = array())
	{
		$this->deleteFiles($path, $ignore);
		$this->deleteFolders($path, $ignore);
	}

	/**
	 * Uninstalls subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   JInstallerAdapter  $parent  is the class calling this method.
	 *
	 * @return void
	 *
	 * @since 9.0.18
	 */
	private function _uninstallSubextensions($parent)
	{
		jimport('joomla.installer.installer');

		$db = JFactory::getDbo();

		// Modules uninstalling
		if (count($this->installation_queue['modules']))
		{
			foreach ($this->installation_queue['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module => $modulePreferences)
					{
						// Find the module ID
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('element') . ' = ' . $db->q('mod_' . $module))
							->where($db->qn('type') . ' = ' . $db->q('module'));
						$db->setQuery($sql);
						$id = $db->loadResult();

						// Uninstall the module
						if ($id)
						{
							$installer         = new JInstaller;
							$result            = $installer->uninstall('module', $id, 1);
							$this->status->modules[] = [
								'name'   => 'mod_' . $module,
								'client' => $folder,
								'result' => $result
							];
						}
					}
				}
			}
		}

		// Plugins uninstalling
		if (count($this->installation_queue['plugins']))
		{
			foreach ($this->installation_queue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('type') . ' = ' . $db->q('plugin'))
							->where($db->qn('element') . ' = ' . $db->q($plugin))
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($sql);

						$id = $db->loadResult();

						if ($id)
						{
							$installer         = new JInstaller;
							$result            = $installer->uninstall('plugin', $id, 1);
							$this->status->plugins[] = [
								'name'   => 'plg_' . $plugin,
								'group'  => $folder,
								'result' => $result
							];
						}
					}
				}
			}
		}
	}
}
