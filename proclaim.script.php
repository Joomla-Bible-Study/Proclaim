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
class com_proclaimInstallerScript
{
	/** @var string The component's name
	 * @since 1.5
	 */
	protected $extension = 'com_proclaim';

	/** @var string
	 * @since 1.5
	 */
	protected $xml;

	/** @var string
	 * @since 1.5
	 */
	protected $srcxml;

	/** @var object
	 * @since 1.5
	 */
	protected $status;

	/** @var string Path to Mysql files
	 * @since 1.5
	 */
	public $filePath = '/components/com_proclaim/install/sql/updates/mysql';

	/**
	 * This is Minimum requirements for: PHP, MySQL, Joomla
	 *
	 * @var array Requirements
	 * @since 9.0.9
	 */
	static protected $versions = array(
		'PHP'     => array(
			'7.1' => '7.1.0',
			'7.2' => '7.2.1',
			'0'   => '7.4.1' // Preferred version
		),
		'MySQL'   => array(
			'5.1' => '5.1',
			'5.5' => '5.5.3',
			'0'   => '5.5.3' // Preferred version
		),
		'Joomla!' => array(
			'3.6' => '3.6.3',
			'3.7' => '3.7.0',
			'0'   => '3.9.3' // Preferred version
		)
	);

	/**
	 * The list of extra modules and plugins to install
	 * @var   array $Installation_Queue Array of Items to install
	 * @author CWM Team
	 * @since  9.0.18
	 */
	static private $installActionQueue = array(
		// -- modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules' => array(
			'administrator' => array(),
			'site'  => array('biblestudy' => 0, 'biblestudy_podcast' => 0,),
		),
		// -- plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
			'finder' => array('biblestudy' => 1,),
			'search' => array('biblestudysearch' => 0,),
			'system' => array('jbspodcast' => 0, 'jbsbackup' => '0'),
		),
	);

	/**
	 * @var array $extensions test
	 *
	 * @since 9.0.18
	 */
	static protected $extensions = array('dom', 'gd', 'json', 'pcre', 'SimpleXML');

	/**
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 *
	 * @param   string             $type    Type of install
	 * @param   JInstallerAdapter  $parent  Where it is coming from
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
				'text_file' => 'com_proclaim.errors.php'
			),
			JLog::ALL,
			'com_proclaim'
		);

		// Prevent installation if requirements are not met.
		if (!$this->checkRequirements($manifest->version))
		{
			return false;
		}

		$adminPath = $parent->getPath('extension_administrator');
		$sitePath  = $parent->getPath('extension_site');

		if (is_file($adminPath . '/administration.biblestudy.php'))
		{
			// JBSM 8.0 or older release found, clean up the directories.
			static $ignoreAdmin = array('index.html', 'biblestudy.xml', 'archive');

			if (is_file($adminPath . '/install.script.php'))
			{
				// JBSM 6.2 or older release..
				$ignoreAdminarray[] = 'install.script.php';
				$ignoreAdminarray[] = 'administration.biblestudy.php';
			}

			static $ignoreSite = array('index.html', 'biblestudy.php', 'router.php', 'COPYRIGHT.php', 'CHANGELOG.php');
			$this->deleteFolder($adminPath, $ignoreAdmin);
			$this->deleteFolder($sitePath, $ignoreSite);
		}

		// Remove all old install files before install/upgrade
		if (JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_proclaim/install'))
		{
			JFolder::delete(JPATH_ADMINISTRATOR . '/components/com_proclaim/install');
		}

		// Remove old BibleStudy Helper. @since 9.0.14
		if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_proclaim/helpers/biblestudy.php'))
		{
			JFile::delete(JPATH_ADMINISTRATOR . '/components/com_proclaim/helpers/biblestudy.php');
		}

		// Remove Variability plupload folder from media not use. 9.1.2
		if (JFolder::exists(JPATH_ROOT . '/media/com_proclaim/plupload'))
		{
			JFolder::delete(JPATH_ROOT . '/media/com_proclaim/plupload');
		}

		return true;
	}

	/**
	 * Install
	 *
	 * @param   JInstallerFile  $parent  Where call is coming from
	 *
	 * @return  boolean
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
	 * Update will go to install
	 *
	 * @param   JInstallerFile  $parent  ?
	 *
	 * @return boolean
	 *
	 * @since 1.5
	 */
	public function update($parent)
	{
		return $this->install($parent);
	}

	/**
	 * Uninstall rout to JBSMModelInstall
	 *
	 * @param   JInstallerFile  $parent  ?
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since 1.5
	 */
	public function uninstall($parent)
	{
		$adminpath = $parent->getParent()->getPath('extension_administrator');
		$model     = "{$adminpath}/models/InstallController.php";

		if (file_exists($model))
		{
			require_once $model;
			$installer = new BibleStudyModelInstall;
			$installer->uninstall();
		}

		// Uninstall sub-extensions
		$this->uninstallSubextensions($parent);

		// Show the post-uninstalling page
		$this->renderPostUninstallation($this->status, $parent);

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
		if (!JFile::exists(JPATH_SITE . '/images/biblestudy/logo.png'))
		{
			// Copy the images to the new folder
			JFolder::copy('/media/com_proclaim/images', 'images/biblestudy/', JPATH_SITE, true);
		}

		// Install subExtensions
		$this->installSubextensions($parent);

		// Clear FOF's cache
		if (!defined('FOF_INCLUDED') && JFile::exists(JPATH_LIBRARIES . '/fof/include.php'))
		{
			include_once JPATH_LIBRARIES . '/fof/include.php';
		}

		if (defined('FOF_INCLUDED'))
		{
			FOFPlatform::getInstance()->clearCache();
		}

		if ($type === 'install')
		{
			// An redirect to a new location after the install is completed.
			$controller = JControllerLegacy::getInstance('Biblestudy');
			$controller->setRedirect(
				JUri::base() .
				'index.php?option=com_proclaim&view=install&task=install.browse&scanstate=start&' .
				JSession::getFormToken() . '=1'
			);
			$controller->redirect();
		}

		// Show the post-installation page
		$this->renderPostInstallation($this->status, $parent);
	}

	/**
	 * Check Requirements
	 *
	 * @param   string  $version  JBSM version to check for.
	 *
	 * @return boolean
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
				'text_file' => 'com_proclaim.errors.php'
			),
			JLog::ALL,
			'com_proclaim'
		);

		$db   = Factory::getDbo();
		$pass = $this->checkVersion('PHP', phpversion());
		$pass &= $this->checkVersion('Joomla!', JVERSION);
		$pass &= $this->checkVersion('MySQL', $db->getVersion());
		$pass &= $this->checkDbo($db->name, array('mysql', 'mysqli', 'pdo'));
		$pass &= $this->checkExtensions(self::$extensions);
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
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @since 7.1.0
	 */
	protected function checkDbo($name, $types)
	{
		$app = Factory::getApplication();

		if (in_array($name, $types))
		{
			return true;
		}

		$app->enqueueMessage(sprintf("Database driver '%s' is not supported. Please use MySQL instead.", $name), 'notice');
		JLog::add(
			sprintf("Database driver '%s' is not supported. Please use MySQL instead.", $name),
			JLog::NOTICE,
			$this->extension
		);

		return false;
	}

	/**
	 * Check PHP Extension Requirement
	 *
	 * @param   array  $extensions  Array of version to look for
	 *
	 * @return integer 1 is passing, 0 failed php version.
	 *
	 * @throws Exception
	 *
	 * @since 7.1.0
	 */
	protected function checkExtensions($extensions)
	{
		$app  = Factory::getApplication();
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
					$this->extension
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
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @since 7.1.0
	 */
	protected function checkVersion($name, $version)
	{
		$app   = Factory::getApplication();
		$major = $minor = 0;

		foreach (self::$versions[$name] as $major => $minor)
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
			$minor = reset(self::$versions[$name]);
		}

		$recommended = end(self::$versions[$name]);
		$app->enqueueMessage(
			sprintf("%s %s is not supported. Minimum required version is %s %s, but it is higly recommended to use %s %s or later.",
				$name, $version, $name, $minor, $name, $recommended
			), 'notice'
		);

		JLog::add(
			sprintf("%s %s is not supported. Minimum required version is %s %s, but it is higly recommended to use %s %s or later.",
				$name, $version, $name, $minor, $name, $recommended
			), JLog::ERROR, 'com_proclaim'
		);

		return false;
	}

	/**
	 * Check the installed version of Proclaim
	 *
	 * @param   string  $version  Proclaim Version to check for
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @since 7.1.0
	 */
	protected function checkJBSM($version)
	{
		$app = Factory::getApplication();

		$db = Factory::getDbo();

		// Check if JBSM can be found from the database
		$table = $db->getPrefix() . 'bsms_update';
		$db->setQuery("SHOW TABLES LIKE {$db->q($table)}");

		if ($db->loadResult() !== $table)
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
			JLog::add('Found No installd version.', JLog::NOTICE, $this->extension);

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
			$this->extension
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
	 * Renders the post-installation message
	 *
	 * @param   object             $status  ?
	 * @param   JInstallerAdapter  $parent  is the class calling this method.
	 *
	 * @return void
	 *
	 * @since 1.7.0
	 *
	 * @todo  need to add version check system.
	 */
	private function renderPostInstallation($status, $parent)
	{
		$language = Factory::getLanguage();
		$language->load('com_proclaim', JPATH_ADMINISTRATOR . '/components/com_proclaim', 'en-GB', true);
		$language->load('com_proclaim', JPATH_ADMINISTRATOR . '/components/com_proclaim', null, true);
		echo '<img src="../media/com_proclaim/images/proclaim.jpg" width="48" height="48"
             alt="Proclaim"/>

        <h2>Welcome to CWM Proclaim System</h2>

        <table class="adminlist table" style="width: 300px;">
            <thead>
            <tr>
                <th class="title">Extension</th>
                <th class="title">Client</th>
                <th class="title">' . JText::_('JBS_INS_STATUS') . '</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="3"></td>
            </tr>
            </tfoot>
            <tbody>
            <tr>
                <td class="key">' . JText::_('JBS_CMN_com_proclaim') . '</td>
                <td class="key">Site</td>
                <td><strong style="color: green;">' . JText::_('JBS_INS_INSTALLED') . '</strong></td>
            </tr>';

		if (count($status->modules))
		{
			echo "<tr>
                <th>Module</th>
                <th>Client</th>
                <th><?php echo JText::_('JBS_INS_STATUS'); ?></th>
            </tr>";

			foreach ($status->modules as $module)
			{
				echo '<tr>';
				echo '<td class="key">' . $module['name'] . '</td>';
				echo '<td class="key">' . ucfirst($module['client']) . '</td>';
				echo '<td class="key">';
				echo '<strong style="color: ' . ($module['result'] ? 'green' : 'red') . ';">';
				echo ' ' . ($module['result'] ? JText::_('JBS_INS_INSTALLED') : JText::_('JBS_INS_NOT_INSTALLED')) . ' ';
				echo '</strong>';
				echo '</td>';
				echo '</tr>';
			}
		}

		if (count($status->plugins))
		{
			?>
			<tr>
				<th>Plugin</th>
				<th>Group</th>
				<th><?php echo JText::_('JBS_INS_STATUS'); ?></th>
			</tr>
			<?php
			foreach ($status->plugins as $plugin)
			{
				echo '<tr>';
				echo '<td class="key">' . ucfirst($plugin['name']) . '</td>';
				echo '<td class="key">' . ucfirst($plugin['group']) . '</td>';
				echo '<td>';
				echo '<strong style="color: ' . ($plugin['result'] ? 'green' : 'red') . ';">';
				echo '' . ($plugin['result'] ? JText::_('JBS_INS_INSTALLED') : JText::_('JBS_INS_NOT_INSTALLED')) . '';
				echo '</strong>';
				echo '</td>';
				echo '</tr>';
			}
		}

		if (count($status->libraries))
		{
			?>
			<tr>
				<th>Libraries</th>
				<th>Version</th>
				<th><?php echo JText::_('JBS_INS_STATUS'); ?></th>
			</tr>
			<?php
			foreach ($status->libraries as $library)
			{
				echo '<tr>
					<td class="key">' . ucfirst($library['name']) . '</td>
					<td class="key">' . ucfirst($library['version']) . '</td>
					<td>
						<strong style="color: ' . ($library['result'] ? 'green' : 'red') . ';">
						' . $library['result'] . '
						</strong>
					</td>
				</tr>';
			}
		}

		echo '</tbody></table>';
	}

	/**
	 * Render Post Uninstalling
	 *
	 * @param   object             $status  ?
	 * @param   JInstallerAdapter  $parent  is the class calling this method.
	 *
	 * @return void
	 *
	 * @since 1.7.0
	 */
	private function renderPostUninstallation($status, $parent)
	{
		$rows = 0;
		echo '<h2>' . JText::_('JBS_INS_UNINSTALL') . '</h2>
		<table class="adminlist">
			<thead>
			<tr>
				<th class="title" colspan="2">' . JText::_('JBS_INS_EXTENSION') . '</th>
				<th width="30%">' . JText::_('JBS_INS_STATUS') . '</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="3"></td>
			</tr>
			</tfoot>
			<tbody>
			<tr class="row0">
				<td class="key" colspan="2">' . JText::_('JBS_CMN_com_proclaim') . '</td>
				<td><strong style="color: green;">' . JText::_('JBS_INS_REMOVED') . '</strong></td>
			</tr>';

		if (count($status->modules))
		{
			?>
			<tr>
				<th><?php echo JText::_('JBS_INS_MODULE'); ?></th>
				<th><?php echo JText::_('JBS_INS_CLIENT'); ?></th>
				<th></th>
			</tr>
			<?php
			foreach ($status->modules as $module)
			{
				?>
				<tr class="row<?php echo $rows++; ?>">
					<td class="key"><?php echo $module['name']; ?></td>
					<td class="key"><?php echo ucfirst($module['client']); ?></td>
					<td>
						<strong style="color: <?php echo '' . ($module['result'] ? 'green' : 'red'); ?>">
							<?php echo '' . ($module['result'] ? JText::_('JBS_INS_REMOVED')
									: JText::_('JBS_INS_NOT_REMOVED')); ?></strong>
					</td>
				</tr>
				<?php
			}
		}
		?>
		<?php
		if (count($status->plugins))
		{
			?>
			<tr>
				<th><?php echo JText::_('Plugin'); ?></th>
				<th><?php echo JText::_('Group'); ?></th>
				<th></th>
			</tr>
			<?php

			foreach ($status->plugins as $plugin)
			{
				?>
				<tr class="row<?php echo $rows++; ?>">
					<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
					<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
					<td><strong style="color: <?php echo '' . ($plugin['result'] ? 'green' : 'red'); ?>;">
							<?php echo '' . ($plugin['result'] ?
									JText::_('JBS_INS_REMOVED') :
									JText::_('JBS_INS_NOT_REMOVED')); ?>
						</strong>
					</td>
				</tr>
				<?php
			}
		}

		echo '</tbody></table>';
	}


	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   JInstallerAdapter  $parent  is the class calling this method.
	 *
	 * @return  void
	 *
	 * @since 1.7.0
	 */
	private function installSubextensions($parent)
	{
		$src             = $parent->getParent()->getPath('source');
		$db              = Factory::getDbo();
		$this->status          = new stdClass;
		$this->status->modules = array();
		$this->status->plugins = array();

		// Modules installation
		if (count(self::$installActionQueue['modules']))
		{
			foreach (self::$installActionQueue['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module => $modulePreferences)
					{
						// Install the module
						if (empty($folder))
						{
							$folder = 'site';
						}

						$path = "$src/modules/$folder/$module";

						if (!is_dir($path))
						{
							$path = "$src/modules/$folder/mod_$module";
						}

						if (!is_dir($path))
						{
							$path = "$src/modules/$module";
						}

						if (!is_dir($path))
						{
							$path = "$src/modules/mod_$module";
						}

						if (!is_dir($path))
						{
							continue;
						}

						// Was the module already installed?
						$sql = $db->getQuery(true)->select('COUNT(*)')
							->from('#__modules')
							->where($db->qn('module') . ' = ' . $db->q('mod_' . $module));
						$db->setQuery($sql);
						$count                  = $db->loadResult();
						$installer              = new JInstaller;
						$result                 = $installer->install($path);
						$this->status->modules[] = array(
							'name'   => 'mod_' . $module,
							'client' => $folder,
							'result' => $result
						);

						// Modify where it's published and its published state
						if (!$count)
						{
							// A. Position and state
							list($modulePosition, $modulePublished) = $modulePreferences;

							if ($modulePosition === 'cpanel')
							{
								$modulePosition = 'icon';
							}

							$sql = $db->getQuery(true)
								->update($db->qn('#__modules'))
								->set($db->qn('position') . ' = ' . $db->q($modulePosition))
								->where($db->qn('module') . ' = ' . $db->q('mod_' . $module));

							if ($modulePublished)
							{
								$sql->set($db->qn('published') . ' = ' . $db->q('1'));
							}

							$db->setQuery($sql);
							$db->execute();

							// B. Change the ordering of back-end modules to 1 + max ordering
							if ($folder === 'administrator')
							{
								$query = $db->getQuery(true);
								$query->select('MAX(' . $db->qn('ordering') . ')')
									->from($db->qn('#__modules'))
									->where($db->qn('position') . '=' . $db->q($modulePosition));
								$db->setQuery($query);
								$position = $db->loadResult();
								$position++;
								$query = $db->getQuery(true);
								$query->update($db->qn('#__modules'))
									->set($db->qn('ordering') . ' = ' . $db->q($position))
									->where($db->qn('module') . ' = ' . $db->q('mod_' . $module));
								$db->setQuery($query);
								$db->execute();
							}

							// C. Link to all pages
							$query = $db->getQuery(true);
							$query->select('id')
								->from($db->qn('#__modules'))
								->where($db->qn('module') . ' = ' . $db->q('mod_' . $module));
							$db->setQuery($query);
							$moduleid = $db->loadResult();
							$query    = $db->getQuery(true);
							$query->select('*')
								->from($db->qn('#__modules_menu'))
								->where($db->qn('moduleid') . ' = ' . $db->q($moduleid));
							$db->setQuery($query);
							$assignments = $db->loadObjectList();
							$isAssigned  = !empty($assignments);

							if (!$isAssigned)
							{
								$o = (object) array(
									'moduleid' => $moduleid,
									'menuid'   => 0
								);
								$db->insertObject('#__modules_menu', $o);
							}
						}
					}
				}
			}
		}

		// Plugins installation
		if (count(self::$installActionQueue['plugins']))
		{
			foreach (self::$installActionQueue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$path = "$src/plugins/$folder/$plugin";

						if (!is_dir($path))
						{
							$path = "$src/plugins/$folder/plg_$plugin";
						}

						if (!is_dir($path))
						{
							$path = "$src/plugins/$plugin";
						}

						if (!is_dir($path))
						{
							$path = "$src/plugins/plg_$plugin";
						}

						if (!is_dir($path))
						{
							continue;
						}

						// Was the plugin already installed?
						$query = $db->getQuery(true)
							->select('COUNT(*)')
							->from($db->qn('#__extensions'))
							->where($db->qn('element') . ' = ' . $db->q($plugin))
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($query);
						$count                  = $db->loadResult();
						$installer              = new JInstaller;
						$result                 = $installer->install($path);
						$this->status->plugins[] = array(
							'name'   => 'plg_' . $plugin,
							'group'  => $folder,
							'result' => $result
						);

						if ($published && !$count)
						{
							$query = $db->getQuery(true)
								->update($db->qn('#__extensions'))
								->set($db->qn('enabled') . ' = ' . $db->q('1'))
								->where($db->qn('element') . ' = ' . $db->q($plugin))
								->where($db->qn('folder') . ' = ' . $db->q($folder));
							$db->setQuery($query);
							$db->execute();
						}
					}
				}
			}
		}
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
	private function uninstallSubextensions($parent)
	{
		jimport('joomla.installer.installer');

		$db = Factory::getDbo();

		// Modules uninstalling
		if (count(self::$installActionQueue['modules']))
		{
			foreach (self::$installActionQueue['modules'] as $folder => $modules)
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
							$installer                    = new JInstaller;
							$result                       = $installer->uninstall('module', $id, 1);
							$this->status->modules[] = array(
								'name'   => 'mod_' . $module,
								'client' => $folder,
								'result' => $result
							);
						}
					}
				}
			}
		}

		// Plugins uninstalling
		if (count(self::$installActionQueue['plugins']))
		{
			foreach (self::$installActionQueue['plugins'] as $folder => $plugins)
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
							$installer                    = new JInstaller;
							$result                       = $installer->uninstall('plugin', $id, 1);
							$this->status->plugins[] = array(
								'name'   => 'plg_' . $plugin,
								'group'  => $folder,
								'result' => $result
							);
						}
					}
				}
			}
		}
	}
}
