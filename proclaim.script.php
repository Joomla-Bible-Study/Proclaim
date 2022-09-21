<?php
/**
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

use CWM\Component\Proclaim\Administrator\Model\CWMInstallModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Adapter\FileAdapter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

/**
 * Proclaim Install Script
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class com_proclaimInstallerScript
{
	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 * @since 10.0.0
	 */
	protected string $minimumJoomlaVersion = '4.0.0';

	/** @var string The component's name
	 * @since 1.5
	 */
	protected string $extension = 'com_proclaim';

	/** @var string
	 * @since 1.5
	 */
	protected $xml;

	/** @var string
	 * @since 1.5
	 */
	protected string $srcxml;

	/** @var object
	 * @since 1.5
	 */
	protected object $status;

	/** @var string Path to Mysql files
	 * @since 1.5
	 */
	public string $filePath = '/components/com_proclaim/install/sql/updates/mysql';

	/**
	 * This is Minimum requirements for: PHP, MySQL, Joomla
	 *
	 * @var array Requirements
	 * @since 9.0.9
	 */
	static protected array $versions = array(
		'PHP'   => array(
			'7.1' => '7.1.0',
			'7.2' => '7.2.1',
			'0'   => '7.4.1' // Preferred version
		),
		'MySQL' => array(
			'5.1' => '5.1',
			'5.5' => '5.5.3',
			'0'   => '5.5.3' // Preferred version
		)
	);

	/**
	 * The list of extra modules and plugins to install
	 * @var   array $Installation_Queue Array of Items to install
	 * @author CWM Team
	 * @since  9.0.18
	 */
	static private array $installActionQueue = array(
		// -- modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules' => array(
			'administrator' => array(),
			'site'          => array('proclaim' => 0, 'proclaim_podcast' => 0,),
		),
		// -- plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
			'finder' => array('proclaim' => 1,),
			'system' => array('proclaimpodcast' => 0, 'proclaimpodcast' => '0'),
            'system' => array('proclaimbackup' => 0, 'proclaimbackup' => '0'),
		),
	);

	/**
	 * @var array $extensions test
	 *
	 * @since 9.0.18
	 */
	static protected array $extensions = array('dom', 'gd', 'json', 'pcre', 'SimpleXML');

	/**
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 *
	 * @param   string            $type    Type of install
	 * @param   InstallerAdapter  $parent  Where it is coming from
	 *
	 * @return boolean
	 *
	 * @throws  Exception
	 * @since   1.5
	 */
	public function preflight($type, $parent)
	{
		$manifest = $parent->getManifest();

		// Check the minimum Joomla! version
		if (!version_compare(JVERSION, $this->minimumJoomlaVersion, 'ge'))
		{
			$msg = "<p>You need Joomla! $this->minimumJoomlaVersion or later to install this component</p>";
			Log::add($msg, Log::WARNING, 'jerror');

			return false;
		}

		// Include the JLog class.
		Log::addLogger(
			array(
				'text_file' => 'com_proclaim.errors.php'
			),
			Log::ALL,
			'com_proclaim'
		);

		// Prevent installation if requirements are not met.
		if (!$this->checkRequirements($manifest->version))
		{
			return false;
		}

		// Remove all old install files before install/upgrade
		if (Folder::exists(JPATH_ADMINISTRATOR . '/components/com_proclaim/install'))
		{
			Folder::delete(JPATH_ADMINISTRATOR . '/components/com_proclaim/install');
		}

		return true;
	}

	/**
	 * Install
	 *
	 * @param   FileAdapter  $parent  Where call is coming from
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
			Folder::delete($cacheDir);
		}

		Folder::create($cacheDir);

		return true;
	}

	/**
	 * Update will go to install
	 *
	 * @param   FileAdapter  $parent  ?
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
	 * Uninstall rout to CWMInstallModel
	 *
	 * @param   FileAdapter  $parent  ?
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
			$installer = new CWMInstallModel;
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
	 * @param   string       $type    Type of install
	 * @param   FileAdapter  $parent  Where it is coming from
	 *
	 * @return   void
	 *
	 * @throws  Exception
	 * @since   1.5
	 */
	public function postflight($type, $parent)
	{
		if (!File::exists(JPATH_SITE . '/images/com_proclaim/logo.png'))
		{
			// Copy the images to the new folder
			Folder::copy('/media/com_proclaim/images', 'images/com_proclaim/', JPATH_SITE, true);
		}

		// Install subExtensions
		$this->installSubextensions($parent);

		if ($type === 'install')
		{
			// Redirect to a new location after the installer is completed.
			$controller = BaseController::getInstance('Proclaim');
			$controller->setRedirect(
				Uri::base() .
				'index.php?option=com_proclaim&view=install&task=install.browse&scanstate=start&' .
				Session::getFormToken() . '=1'
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
		Log::addLogger(
			array(
				'text_file' => 'com_proclaim.errors.php'
			),
			Log::ALL,
			'com_proclaim'
		);

		$db   = Factory::getContainer()->get('DatabaseDriver');
		$pass = $this->checkVersion('PHP', PHP_VERSION);
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

		if (in_array($name, $types, true))
		{
			return true;
		}

		$app->enqueueMessage(sprintf("Database driver '%s' is not supported. Please use MySQL instead.", $name), 'notice');
		Log::add(
			sprintf("Database driver '%s' is not supported. Please use MySQL instead.", $name),
			Log::NOTICE,
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
				Log::add(
					sprintf("Required PHP extension '%s' is missing. Please install it into your system.", $name),
					Log::NOTICE,
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

		Log::add(
			sprintf("%s %s is not supported. Minimum required version is %s %s, but it is higly recommended to use %s %s or later.",
				$name, $version, $name, $minor, $name, $recommended
			), Log::ERROR, 'com_proclaim'
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

		$db = Factory::getContainer()->get('DatabaseDriver');

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
			Log::add('Found No installed version.', Log::NOTICE, $this->extension);

			return true;
		}

		// Always allow upgrade to the newer version
		if (version_compare($version, $installed, '>='))
		{
			return true;
		}

		// @todo need to move to language string.
		$app->enqueueMessage(sprintf('Sorry, it is not possible to downgrade BibleStudy %s to version %s.', $installed, $version), 'notice');
		Log::add(
			sprintf('Sorry, it is not possible to downgrade BibleStudy %s to version %s.', $installed, $version),
			Log::NOTICE,
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

		if (Folder::exists($path))
		{
			foreach (Folder::files($path, '.', false, true, $ignore) as $file)
			{
				if (File::exists($file))
				{
					File::delete($file);
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

		if (Folder::exists($path))
		{
			foreach (Folder::folders($path, '.', false, true, $ignore) as $folder)
			{
				if (Folder::exists($folder))
				{
					Folder::delete($folder);
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
	 * @param   object            $status  ?
	 * @param   InstallerAdapter  $parent  is the class calling this method.
	 *
	 * @return void
	 *
	 * @since 1.7.0
	 *
	 * @todo  need to add version check system.
	 */
	private function renderPostInstallation($status, $parent)
	{
		$language = Factory::getApplication()->getLanguage();
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
                <th class="title">' . Text::_('JBS_INS_STATUS') . '</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="3"></td>
            </tr>
            </tfoot>
            <tbody>
            <tr>
                <td class="key">' . Text::_('JBS_CMN_com_proclaim') . '</td>
                <td class="key">Site</td>
                <td><strong style="color: green;">' . Text::_('JBS_INS_INSTALLED') . '</strong></td>
            </tr>';

		if (count($status->modules))
		{
			echo "<tr>
                <th>Module</th>
                <th>Client</th>
                <th><?php echo Text::_('JBS_INS_STATUS'); ?></th>
            </tr>";

			foreach ($status->modules as $module)
			{
				echo '<tr>';
				echo '<td class="key">' . $module['name'] . '</td>';
				echo '<td class="key">' . ucfirst($module['client']) . '</td>';
				echo '<td class="key">';
				echo '<strong style="color: ' . ($module['result'] ? 'green' : 'red') . ';">';
				echo ' ' . ($module['result'] ? Text::_('JBS_INS_INSTALLED') : Text::_('JBS_INS_NOT_INSTALLED')) . ' ';
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
				<th><?php echo Text::_('JBS_INS_STATUS'); ?></th>
			</tr>
			<?php
			foreach ($status->plugins as $plugin)
			{
				echo '<tr>';
				echo '<td class="key">' . ucfirst($plugin['name']) . '</td>';
				echo '<td class="key">' . ucfirst($plugin['group']) . '</td>';
				echo '<td>';
				echo '<strong style="color: ' . ($plugin['result'] ? 'green' : 'red') . ';">';
				echo '' . ($plugin['result'] ? Text::_('JBS_INS_INSTALLED') : Text::_('JBS_INS_NOT_INSTALLED')) . '';
				echo '</strong>';
				echo '</td>';
				echo '</tr>';
			}
		}

		echo '</tbody></table>';
	}

	/**
	 * Render Post Uninstalling
	 *
	 * @param   object            $status  ?
	 * @param   InstallerAdapter  $parent  is the class calling this method.
	 *
	 * @return void
	 *
	 * @since 1.7.0
	 */
	private function renderPostUninstallation($status, $parent)
	{
		$rows = 0;
		echo '<h2>' . Text::_('JBS_INS_UNINSTALL') . '</h2>
		<table class="adminlist">
			<thead>
			<tr>
				<th class="title" colspan="2">' . Text::_('JBS_INS_EXTENSION') . '</th>
				<th width="30%">' . Text::_('JBS_INS_STATUS') . '</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="3"></td>
			</tr>
			</tfoot>
			<tbody>
			<tr class="row0">
				<td class="key" colspan="2">' . Text::_('JBS_CMN_COM_PROCLAIM') . '</td>
				<td><strong style="color: green;">' . Text::_('JBS_INS_REMOVED') . '</strong></td>
			</tr>';

		if (count($status->modules))
		{
			?>
			<tr>
				<th><?php echo Text::_('JBS_INS_MODULE'); ?></th>
				<th><?php echo Text::_('JBS_INS_CLIENT'); ?></th>
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
							<?php echo '' . ($module['result'] ? Text::_('JBS_INS_REMOVED')
									: Text::_('JBS_INS_NOT_REMOVED')); ?></strong>
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
				<th><?php echo Text::_('Plugin'); ?></th>
				<th><?php echo Text::_('Group'); ?></th>
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
									Text::_('JBS_INS_REMOVED') :
									Text::_('JBS_INS_NOT_REMOVED')); ?>
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
	 * @param   InstallerAdapter  $parent  is the class calling this method.
	 *
	 * @return  void
	 *
	 * @since 1.7.0
	 */
	private function installSubextensions($parent)
	{
		$src                   = $parent->getParent()->getPath('source');
		$db                    = Factory::getContainer()->get('DatabaseDriver');
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
						$count                   = $db->loadResult();
						$installer               = new Installer;
						$result                  = $installer->install($path);
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

							if ($modulePosition === 'cwmcpanel')
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
						$count                   = $db->loadResult();
						$installer               = new JInstaller;
						$result                  = $installer->install($path);
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
	 * @param   InstallerAdapter  $parent  is the class calling this method.
	 *
	 * @return void
	 *
	 * @since 9.0.18
	 */
	private function uninstallSubextensions($parent)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');

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
							$installer               = new Installer;
							$result                  = $installer->uninstall('module', $id, 1);
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
							$installer               = new Installer;
							$result                  = $installer->uninstall('plugin', $id, 1);
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
