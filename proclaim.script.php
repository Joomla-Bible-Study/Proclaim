<?php
/**
 * Proclaim Script install
 *
 * @package    Proclaim.Admin
 * @subpackage com_proclaim
 *
 * @copyright  2007 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * */

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use Joomla\CMS\Installer\Adapter\FileAdapter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Proclaim Install Script
 *
 * @package Proclaim.Admin
 * @since   7.0.0
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

	/**
	 * @var   string The component's name
	 * @since 1.5
	 */
	protected string $extension = 'com_proclaim';

	/**
	 * @var   string
	 * @since 1.5
	 */
	protected $xml;

	/**
	 * @var   string
	 * @since 1.5
	 */
	protected string $srcxml;

	/**
	 * @var   object
	 * @since 1.5
	 */
	protected object $status;

	/**
	 * @var   string Path to Mysql files
	 * @since 1.5
	 */
	public string $filePath = '/components/com_proclaim/install/sql/updates/mysql';

	/**
	 * This is Minimum requirements for: PHP, MySQL, Joomla
	 *
	 * @var   array Requirements
	 * @since 9.0.9
	 */
	static protected array $versions = [
		'PHP'   => [
			'7.1' => '7.1.0',
			'7.2' => '7.2.1',
			'0'   => '7.4.1',
			// Preferred version
		],
		'MySQL' => [
			'5.1' => '5.1',
			'5.5' => '5.5.3',
			'0'   => '5.5.3',
			// Preferred version
		],
	];

	/**
	 * The list of extra modules and plugins to install
	 *
	 * @var    array $Installation_Queue Array of Items to install
	 * @author CWM Team
	 * @since  9.0.18
	 */
	static private array $installActionQueue = [
		// -- modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules' => [
			'administrator' => [],
			'site'          => [
				'proclaim'         => 0,
				'proclaim_podcast' => 0,
			],
		],
		// -- plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => [
			'finder' => ['proclaim' => 1],
			'system' => [
				'proclaimpodcast' => 0,
				'proclaimbackup'  => '0',
			],
		],
	];

	/**
	 * @var array $extensions test
	 *
	 * @since 9.0.18
	 */
	static protected array $extensions = [
		'dom',
		'gd',
		'json',
		'pcre',
		'SimpleXML',
	];


	/**
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 *
	 * @param   string           $type   Type of install
	 * @param   ComponentAdapter $parent Where it is coming from
	 *
	 * @return boolean
	 *
	 * @throws \Exception
	 * @since  1.5
	 */
	public function preflight(string $type, ComponentAdapter $parent): bool
	{
		// Do not run on uninstall.
		if ($type === 'uninstall')
		{
			return true;
		}

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
			['text_file' => 'com_proclaim.errors.php'],
			Log::ALL,
			'com_proclaim'
		);

		// Prevent installation if requirements are not met.
		if (!$this->checkRequirements($manifest->version))
		{
			return false;
		}

		return true;

	}//end preflight()


	/**
	 * Uninstall rout to CWMInstallModel
	 *
	 * @param   FileAdapter $parent ?
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since  1.5
	 */
	public function uninstall($parent)
	{
		// Uninstall sub-extensions
		$this->uninstallSubextensions($parent);

		// Show the post-uninstalling page
		$this->renderPostUninstallation($this->status, $parent);

		return true;

	}//end uninstall()


	/**
	 * Post Flight
	 *
	 * @param   string           $type   Type of install
	 * @param   ComponentAdapter $parent Where it is coming from
	 *
	 * @return void
	 *
	 * @since 1.5
	 */
	public function postflight(string $type, ComponentAdapter $parent)
	{
		// Install subExtensions
		$this->installSubextensions($parent);

		// Show the post-installation page
		$this->renderPostInstallation($this->status, $parent);

	}//end postflight()


	/**
	 * Check Requirements
	 *
	 * @param   string $version CWM version to check for.
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since  7.1.0
	 */
	public function checkRequirements($version)
	{
		// Include the JLog class.
		Log::addLogger(
			['text_file' => 'com_proclaim.errors.php'],
			Log::ALL,
			'com_proclaim'
		);

		$db    = Factory::getContainer()->get('DatabaseDriver');
		$pass  = $this->checkVersion('PHP', PHP_VERSION);
		$pass &= $this->checkVersion('MySQL', $db->getVersion());
		$pass &= $this->checkDbo($db->name, ['mysql', 'mysqli', 'pdo']);
		$pass &= $this->checkExtensions(self::$extensions);

		return $pass;

	}//end checkRequirements()


	// Internal functions


	/**
	 * Check Database Driver
	 *
	 * @param   string $name  Driver
	 * @param   array  $types Array of drivers supported
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

	}//end checkDbo()


	/**
	 * Check PHP Extension Requirement
	 *
	 * @param   array $extensions Array of version to look for
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

	}//end checkExtensions()


	/**
	 * Check Versions of JBSM
	 *
	 * @param   string $name    Name of version
	 * @param   string $version Version to look for
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
			sprintf(
				'%s %s is not supported. Minimum required version is %s %s, but it is higly recommended to use %s %s or later.',
				$name,
				$version,
				$name,
				$minor,
				$name,
				$recommended
			),
			'notice'
		);

		Log::add(
			sprintf(
				'%s %s is not supported. Minimum required version is %s %s, but it is higly recommended to use %s %s or later.',
				$name,
				$version,
				$name,
				$minor,
				$name,
				$recommended
			),
			Log::ERROR,
			'com_proclaim'
		);

		return false;

	}//end checkVersion()


	/**
	 * Check the installed version of Proclaim
	 *
	 * @param   string $version Proclaim Version to check for
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @since 7.1.0
	 */
	protected function checkJBSM($version)
	{
		return false;

	}//end checkJBSM()


	/**
	 * Delete Files
	 *
	 * @param   string $path   Path of File
	 * @param   array  $ignore Array of files to Ignore
	 *
	 * @return void
	 *
	 * @since 7.1.0
	 */
	public function deleteFiles($path, $ignore=[])
	{
		$ignore = array_merge($ignore, ['.git', '.svn', 'CVS', '.DS_Store', '__MACOSX']);

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

	}//end deleteFiles()


	/**
	 * Delete Folders
	 *
	 * @param   string $path   Path to folders
	 * @param   array  $ignore Ingnore array of files
	 *
	 * @return void;
	 *
	 * @since 7.1.0
	 */
	public function deleteFolders($path, $ignore=[])
	{
		$ignore = array_merge($ignore, ['.git', '.svn', 'CVS', '.DS_Store', '__MACOSX']);

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

	}//end deleteFolders()


	/**
	 * Delete Folder
	 *
	 * @param   string $path   Path of folder
	 * @param   array  $ignore Ignore list
	 *
	 * @return void
	 *
	 * @since 7.1.0
	 */
	public function deleteFolder($path, $ignore=[])
	{
		$this->deleteFiles($path, $ignore);
		$this->deleteFolders($path, $ignore);

	}//end deleteFolder()


	/**
	 * Renders the post-installation message
	 *
	 * @param   object           $status ?
	 * @param   InstallerAdapter $parent is the class calling this method.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @todo   need to add version check system.
	 * @since  1.7.0
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
		}//end if

		echo '</tbody></table>';

	}//end renderPostInstallation()


	/**
	 * Render Post Uninstalling
	 *
	 * @param   object           $status ?
	 * @param   InstallerAdapter $parent is the class calling this method.
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
							<?php
							echo '' . ($module['result'] ? Text::_('JBS_INS_REMOVED') : Text::_('JBS_INS_NOT_REMOVED'));
							?>
						</strong>
					</td>
				</tr>
				<?php
			}
		}//end if
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
						<?php
						echo '' . ($plugin['result'] ? Text::_('JBS_INS_REMOVED') : Text::_('JBS_INS_NOT_REMOVED'));
						?>
						</strong>
					</td>
				</tr>
				<?php
			}
		}//end if

		echo '</tbody></table>';

	}//end renderPostUninstallation()


	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   InstallerAdapter $parent is the class calling this method.
	 *
	 * @return void
	 *
	 * @since 1.7.0
	 */
	private function installSubextensions($parent)
	{
		$src                   = $parent->getParent()->getPath('source');
		$db                    = Factory::getContainer()->get('DatabaseDriver');
		$this->status          = new stdClass;
		$this->status->modules = [];
		$this->status->plugins = [];

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
						$sql = $db->getQuery(true)->select('COUNT(*)')->from('#__modules')->where($db->qn('module') . ' = ' . $db->q('mod_' . $module));
						$db->setQuery($sql);
						$count                   = $db->loadResult();
						$installer               = new Installer;
						$result                  = $installer->install($path);
						$this->status->modules[] = [
							'name'   => 'mod_' . $module,
							'client' => $folder,
							'result' => $result,
						];

						// Modify where it's published and its published state
						if (!$count)
						{
							// A. Position and state
							list($modulePosition, $modulePublished) = $modulePreferences;

							if ($modulePosition === 'cwmcpanel')
							{
								$modulePosition = 'icon';
							}

							$sql = $db->getQuery(true)->update($db->qn('#__modules'))->set($db->qn('position') . ' = ' . $db->q($modulePosition))->where($db->qn('module') . ' = ' . $db->q('mod_' . $module));

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
								$query->select('MAX(' . $db->qn('ordering') . ')')->from($db->qn('#__modules'))->where($db->qn('position') . '=' . $db->q($modulePosition));
								$db->setQuery($query);
								$position = $db->loadResult();
								$position++;
								$query = $db->getQuery(true);
								$query->update($db->qn('#__modules'))->set($db->qn('ordering') . ' = ' . $db->q($position))->where($db->qn('module') . ' = ' . $db->q('mod_' . $module));
								$db->setQuery($query);
								$db->execute();
							}

							// C. Link to all pages
							$query = $db->getQuery(true);
							$query->select('id')->from($db->qn('#__modules'))->where($db->qn('module') . ' = ' . $db->q('mod_' . $module));
							$db->setQuery($query);
							$moduleid = $db->loadResult();
							$query    = $db->getQuery(true);
							$query->select('*')->from($db->qn('#__modules_menu'))->where($db->qn('moduleid') . ' = ' . $db->q($moduleid));
							$db->setQuery($query);
							$assignments = $db->loadObjectList();
							$isAssigned  = !empty($assignments);

							if (!$isAssigned)
							{
								$o = (object) [
									'moduleid' => $moduleid,
									'menuid'   => 0,
								];
								$db->insertObject('#__modules_menu', $o);
							}
						}//end if
					}//end foreach
				}//end if
			}//end foreach
		}//end if

		// Plugins installation
		if (count(self::$installActionQueue['plugins']))
		{
			foreach (self::$installActionQueue['plugins'] as $folder => $plugins)
			{
				if (count($plugins) !== 0)
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
						$query = $db->getQuery(true)->select('COUNT(*)')->from($db->qn('#__extensions'))->where($db->qn('element') . ' = ' . $db->q($plugin))->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($query);
						$count                   = $db->loadResult();
						$installer               = new JInstaller;
						$result                  = $installer->install($path);
						$this->status->plugins[] = [
							'name'   => 'plg_' . $plugin,
							'group'  => $folder,
							'result' => $result,
						];

						if ($published && !$count)
						{
							$query = $db->getQuery(true)->update($db->qn('#__extensions'))->set($db->qn('enabled') . ' = ' . $db->q('1'))->where($db->qn('element') . ' = ' . $db->q($plugin))->where($db->qn('folder') . ' = ' . $db->q($folder));
							$db->setQuery($query);
							$db->execute();
						}
					}//end foreach
				}//end if
			}//end foreach
		}//end if

	}//end installSubextensions()


	/**
	 * Uninstalls subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   InstallerAdapter $parent is the class calling this method.
	 *
	 * @return void
	 *
	 * @since 9.0.18
	 */
	private function uninstallSubextensions($parent)
	{
		$db                    = Factory::getContainer()->get('DatabaseDriver');
		$this->status          = new stdClass;
		$this->status->modules = [];
		$this->status->plugins = [];

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
						$sql = $db->getQuery(true)->select($db->qn('extension_id'))->from($db->qn('#__extensions'))->where($db->qn('element') . ' = ' . $db->q('mod_' . $module))->where($db->qn('type') . ' = ' . $db->q('module'));
						$db->setQuery($sql);
						$id = $db->loadResult();

						// Uninstall the module
						if ($id !== null)
						{
							$installer               = new Installer;
							$result                  = $installer->uninstall('module', $id, 1);
							$this->status->modules[] = [
								'name'   => 'mod_' . $module,
								'client' => $folder,
								'result' => $result,
							];
						}
					}//end foreach
				}//end if
			}//end foreach
		}//end if

		// Plugins uninstalling
		if (count(self::$installActionQueue['plugins']))
		{
			foreach (self::$installActionQueue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$sql = $db->getQuery(true)->select($db->qn('extension_id'))->from($db->qn('#__extensions'))->where($db->qn('type') . ' = ' . $db->q('plugin'))->where($db->qn('element') . ' = ' . $db->q($plugin))->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($sql);

						$id = $db->loadResult();

						if ($id !== null)
						{
							$installer               = new Installer;
							$result                  = $installer->uninstall('plugin', $id, 1);
							$this->status->plugins[] = [
								'name'   => 'plg_' . $plugin,
								'group'  => $folder,
								'result' => $result,
							];
						}
					}//end foreach
				}//end if
			}//end foreach
		}//end if

	}//end uninstallSubextensions()


}//end class

