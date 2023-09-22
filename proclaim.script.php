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
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use Joomla\CMS\Installer\Adapter\FileAdapter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Proclaim Install Script
 *
 * @package Proclaim.Admin
 * @since   7.0.0
 */
class com_proclaimInstallerScript extends InstallerScript
{
	/**
	 * The version number of the extension.
	 *
	 * @var    string
	 * @since  3.6
	 */
	protected $release = '10.0.0';

	/**
	 * @var   DatabaseDriver|DatabaseInterface|null
	 *
	 * @since 7.2.0
	 */
	protected $dbo;

	/**
	 * Minimum PHP version required to install the extension
	 *
	 * @var    string
	 * @since  3.6
	 */
	protected $minimumPhp = '8.0.0';

	/**
	 * Minimum Joomla! version required to install the extension
	 *
	 * @var    string
	 * @since  3.6
	 */
	protected $minimumJoomla = '4.2.0';

	/**
	 * @var   string The component's name
	 * @since 1.5
	 */
	protected $extension = 'com_proclaim';

	/**
	 * @var   string
	 * @since 1.5
	 */
	protected $xml;

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
				'proclaimbackup'  => 0,
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
	 * @param   string            $type    Type of installation
	 * @param   ComponentAdapter  $parent  Where it is coming from
	 *
	 * @return boolean
	 *
	 * @throws \Exception
	 * @since  1.5
	 */
	public function preflight($type, $parent): bool
	{
		if (!parent::preflight($type, $parent))
		{
			return false;
		}

		$this->setDboFromAdapter($parent);

		// Do not run uninstall at this point.
		if ($type === 'uninstall')
		{
			return true;
		}

		// Prevent users from installing this on Joomla 3
		if (version_compare(JVERSION, '3.999.999', 'le'))
		{
			$msg = "<p>This version of Proclaim cannot run on Joomla 3.</p>";

			Log::add($msg, Log::WARNING, 'jerror');

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
	public function uninstall($parent): bool
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
	 * @param   string            $type    Type of install
	 * @param   ComponentAdapter  $parent  Where it is coming from
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since 1.5
	 */
	public function postflight(string $type, ComponentAdapter $parent): void
	{
		// Install subExtensions
		$this->installSubextensions($parent);

		// Show the post-installation page
		$this->renderPostInstallation($this->status, $parent);

	}//end postflight()


	/**
	 * Check Requirements
	 *
	 * @param   string  $version  CWM version to check for.
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since  7.1.0
	 */
	public function checkRequirements(string $version): bool
	{
		// Include the JLog class.
		Log::addLogger(
			['text_file' => 'com_proclaim.errors.php'],
			Log::ALL,
			'com_proclaim'
		);

		return $this->checkExtensions(self::$extensions);

	}//end checkRequirements()


	/**
	 * Check PHP Extension Requirement
	 *
	 * @param   array  $extensions  Array of version to look for
	 *
	 * @return boolean true is passing, false is failed php version.
	 *
	 * @throws Exception
	 * @since 7.1.0
	 */
	protected function checkExtensions(array $extensions): bool
	{
		$app  = Factory::getApplication();
		$pass = true;

		foreach ($extensions as $name)
		{
			if (!extension_loaded($name))
			{
				$pass = false;
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
	 * Renders the post-installation message
	 *
	 * @param   object           $status ?
	 * @param   InstallerAdapter $parent is the class calling this method.
	 *
	 * @return void
	 * @since  1.7.0
	 */
	private function renderPostInstallation($status, $parent): void
	{
		try
		{
			$language = Factory::getApplication()->getLanguage();
			$language->load('com_proclaim', JPATH_ADMINISTRATOR . '/components/com_proclaim', 'en-GB', true);
			$language->load('com_proclaim', JPATH_ADMINISTRATOR . '/components/com_proclaim', null, true);
		}
		catch (\Exception $e)
		{
			return;
		}

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
				echo ' ' . ($plugin['result'] ? Text::_('JBS_INS_INSTALLED') : Text::_('JBS_INS_NOT_INSTALLED')) . '';
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
	private function renderPostUninstallation($status, $parent): void
	{
		$rows = 0;
		echo '<h2>' . Text::_('JBS_INS_UNINSTALL') . '</h2>
		<table class="adminlist">
			<thead>
			<tr>
				<th class="title" colspan="2">' . Text::_('JBS_INS_EXTENSION') . '</th>
				<th >' . Text::_('JBS_INS_STATUS') . '</th>
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
							echo ' ' . ($module['result'] ? Text::_('JBS_INS_REMOVED') : Text::_('JBS_INS_NOT_REMOVED'));
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
	private function installSubextensions($parent): void
	{
		$src                   = $parent->getParent()->getPath('source');
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
						$sql = $this->dbo->getQuery(true)
							->select('COUNT(*)')
							->from('#__modules')
							->where($this->dbo->qn('module') . ' = ' . $this->dbo->q('mod_' . $module));
						$this->dbo->setQuery($sql);
						$count                   = $this->dbo->loadResult();
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

							$sql = $this->dbo->getQuery(true)
								->update($this->dbo->qn('#__modules'))
								->set($this->dbo->qn('position') . ' = ' . $this->dbo->q($modulePosition))
								->where($this->dbo->qn('module') . ' = ' . $this->dbo->q('mod_' . $module));

							if ($modulePublished)
							{
								$sql->set($this->dbo->qn('published') . ' = ' . $this->dbo->q('1'));
							}

							$this->dbo->setQuery($sql);
							$this->dbo->execute();

							// B. Change the ordering of back-end modules to 1 + max ordering
							if ($folder === 'administrator')
							{
								$query = $this->dbo->getQuery(true);
								$query->select('MAX(' . $this->dbo->qn('ordering') . ')')
									->from($this->dbo->qn('#__modules'))
									->where($this->dbo->qn('position') . '=' . $this->dbo->q($modulePosition));
								$this->dbo->setQuery($query);
								$position = $this->dbo->loadResult();
								$position++;
								$query = $this->dbo->getQuery(true);
								$query->update($this->dbo->qn('#__modules'))
									->set($this->dbo->qn('ordering') . ' = ' . $this->dbo->q($position))
									->where($this->dbo->qn('module') . ' = ' . $this->dbo->q('mod_' . $module));
								$this->dbo->setQuery($query);
								$this->dbo->execute();
							}

							// C. Link to all pages
							$query = $this->dbo->getQuery(true);
							$query->select('id')
								->from($this->dbo->qn('#__modules'))
								->where($this->dbo->qn('module') . ' = ' . $this->dbo->q('mod_' . $module));
							$this->dbo->setQuery($query);
							$moduleid = $this->dbo->loadResult();
							$query    = $this->dbo->getQuery(true);
							$query->select('*')
								->from($this->dbo->qn('#__modules_menu'))
								->where($this->dbo->qn('moduleid') . ' = ' . $this->dbo->q($moduleid));
							$this->dbo->setQuery($query);
							$assignments = $this->dbo->loadObjectList();
							$isAssigned  = !empty($assignments);

							if (!$isAssigned)
							{
								$o = (object) [
									'moduleid' => $moduleid,
									'menuid'   => 0,
								];
								$this->dbo->insertObject('#__modules_menu', $o);
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
						$query = $this->dbo->getQuery(true)
							->select('COUNT(*)')
							->from($this->dbo->qn('#__extensions'))
							->where($this->dbo->qn('element') . ' = ' . $this->dbo->q($plugin))
							->where($this->dbo->qn('folder') . ' = ' . $this->dbo->q($folder));
						$this->dbo->setQuery($query);
						$count                   = $this->dbo->loadResult();
						$installer               = new JInstaller;
						$result                  = $installer->install($path);
						$this->status->plugins[] = [
							'name'   => 'plg_' . $plugin,
							'group'  => $folder,
							'result' => $result,
						];

						if ($published && !$count)
						{
							$query = $this->dbo->getQuery(true)
								->update($this->dbo->qn('#__extensions'))
								->set($this->dbo->qn('enabled') . ' = ' . $this->dbo->q('1'))
								->where($this->dbo->qn('element') . ' = ' . $this->dbo->q($plugin))
								->where($this->dbo->qn('folder') . ' = ' . $this->dbo->q($folder));
							$this->dbo->setQuery($query);
							$this->dbo->execute();
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
	private function uninstallSubextensions($parent): void
	{
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
						$sql = $this->dbo->getQuery(true)
							->select($this->dbo->qn('extension_id'))
							->from($this->dbo->qn('#__extensions'))
							->where($this->dbo->qn('element') . ' = ' . $this->dbo->q('mod_' . $module))
							->where($this->dbo->qn('type') . ' = ' . $this->dbo->q('module'));
						$this->dbo->setQuery($sql);
						$id = $this->dbo->loadResult();

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
						$sql = $this->dbo->getQuery(true)
							->select($this->dbo->qn('extension_id'))
							->from($this->dbo->qn('#__extensions'))
							->where($this->dbo->qn('type') . ' = ' . $this->dbo->q('plugin'))
							->where($this->dbo->qn('element') . ' = ' . $this->dbo->q($plugin))
							->where($this->dbo->qn('folder') . ' = ' . $this->dbo->q($folder));
						$this->dbo->setQuery($sql);

						$id = $this->dbo->loadResult();

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

	/**
	 * Set the database object from the installation adapter, if possible
	 *
	 * @param   InstallerAdapter|mixed  $adapter  The installation adapter, hopefully.
	 *
	 * @return  void
	 * @since   7.2.0
	 */
	private function setDboFromAdapter($adapter): void
	{
		$this->dbo = null;

		if (class_exists(InstallerAdapter::class) && ($adapter instanceof InstallerAdapter))
		{
			/**
			 * If this is Joomla 4.2+ the adapter has a protected getDatabase() method which we can access with the
			 * magic property $adapter->db. On Joomla 4.1 and lower this is not available. So, we have to first figure
			 * out if we can actually use the magic property...
			 */

			try
			{
				$refObj = new ReflectionObject($adapter);

				if ($refObj->hasMethod('getDatabase'))
				{
					$this->dbo = $adapter->db;

					return;
				}
			}
			catch (Throwable $e)
			{
				// If something breaks we will fall through
			}
		}

		$this->dbo = Factory::getContainer()->get('DatabaseDriver');
	}


}
