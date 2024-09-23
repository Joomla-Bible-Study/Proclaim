<?php

/**
 * Proclaim Script install
 *
 * @package        Proclaim
 * @subpackage     com_proclaim
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use Joomla\CMS\Installer\Adapter\FileAdapter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
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
     * The version number of the extension. Max 20 characters
     *
     * @var    string
     * @since  3.6
     */
    protected $release = '10.0.0-alpha.240321';

    /**
     * @var   DatabaseDriver|DatabaseInterface|null
     *
     * @since 7.2.0
     */
    protected DatabaseDriver|null|DatabaseInterface $dbo;

    /**
     * Minimum PHP version required to install the extension
     *
     * @var    string
     * @since  3.6
     */
    protected $minimumPhp = '8.1.0';

    /**
     * Minimum Joomla! Version required to install the extension
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
    private static array $installActionQueue = [
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
            'task'   => [
                'proclaim' => 1,
            ],
        ],
    ];

    /**
     * @var array $extensions test
     *
     * @since 9.0.18
     */
    protected static array $extensions = [
        'dom',
        'gd',
        'json',
        'pcre',
        'SimpleXML',
    ];

    /**
     * Allow downgrades of your extension
     *
     * Use at your own risk as if there is a change in functionality people may wish to downgrade.
     *
     * @var    bool
     * @since  3.6
     */
    protected $allowDowngrades = true;

    /**
     * Function called before extension installation/update/removal procedure commences
     *
     * @param   string            $type    The type of change (install, update or discover_install, not uninstall)
     * @param   ComponentAdapter  $parent  The class calling this method
     *
     * @return  bool  True on success
     *
     * @throws Exception
     * @since  1.5
     */
    public function preflight($type, $parent): bool
    {
        $this->setDboFromAdapter($parent);

        /**
         * Find a partial string in the array
         * @param   array  $array
         *
         * @return array
         *
         * @since 10.0
         */
        function array_partial_search(array $array): array
        {
            $found = [];
            // Loop through each item and check for a match.
            foreach ($array as $string) {
                // If found somewhere inside the string, add.
                if (str_contains($string, 'bsms_series')) {
                    $found[] = $string;
                }
            }
            return $found;
        }

        $results = $this->dbo->setQuery('SHOW TABLES')->loadColumn();
        if (!empty(array_partial_search($results))) {
            // Add field if missing Subtitle to series
            $query = "SHOW COLUMNS FROM " . $this->dbo->quoteName('#__bsms_series') . " LIKE " . $this->dbo->quote('subtitle');
            $this->dbo->setQuery($query);
            $db = $this->dbo->loadResult();
            if (empty($db)) {
                $alter = "ALTER TABLE #__bsms_series ADD subtitle TEXT";
                $this->dbo->setquery($alter);
                $this->dbo->execute();
            }
        }

        return parent::preflight($type, $parent);
    }

    /**
     * Uninstall rout
     *
     * @param   FileAdapter  $parent  The class calling this method
     *
     * @return bool
     *
     * @throws Exception
     * @since  1.5
     */
    public function uninstall($parent): bool
    {
        // Uninstall sub-extensions
        $this->uninstallSubextensions();

        // Show the post-uninstalling page
        $this->renderPostUninstallation($this->status, $parent);

        return true;
    }


    /**
     * Post Flight
     *
     * @param   string            $type    The type of change (install, update or discover_install, not uninstall)
     * @param   ComponentAdapter  $parent  The class calling this method
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

        //Remove old com_biblestudy menu items on admin side
        $this->removeBibleStudyVersion();

        if ($type === 'install') {
            // A redirect to a new location after the installation is completed.
            $controller = new CWM\Component\Proclaim\Administrator\Controller\CwmadminController();
            $controller->setRedirect(
                JUri::base() .
                'index.php?option=com_proclaim&view=cwminstall&task=cwminstall.browse&scanstate=start&' .
                JSession::getFormToken() . '=1'
            );
            $controller->redirect();
        }
    }

    /**
     * Remove left overs do to upgrade from an older Proclaim version.
     *
     * @return void
     * @throws Exception
     * @since 10.0.0
     */
    private function removeBibleStudyVersion(): void
    {
        $menuBibleStudyStatus = false;
        $comBibleStudyStatus  = false;

        // Get BibleStudy extension ID
        $query      = $this->dbo->getQuery(true);
        $query->select('id');
        $query->from('#__extensions');
        $query->where($this->dbo->quoteName('element') . ' = ' . $this->dbo->q('com_biblestudy'));
        $this->dbo->setQuery($query);
        $biblestudyID = (int) $this->dbo->loadResult();

        if ($biblestudyID !== 0) {
            // Get Proclaim extension ID
            $query      = $this->dbo->getQuery(true);
            $query->select('id');
            $query->from('#__extensions');
            $query->where($this->dbo->quoteName('element') . ' = ' . $this->dbo->q('com_proclaim'));
            $this->dbo->setQuery($query);
            $proclaimID = (int) $this->dbo->loadResult();

            // Remove old com_biblestudy folders and files as we can't uninstall them
            $this->deleteFolders = ['/components/com_biblestudy', '/administrator/components/com_biblestudy'];
            $this->deleteFiles   = ['/language/en-GB/en-GB.com_biblestudy.ini'];
            $this->removefiles();

            // Clean up Admin Menus form old install
            $query      = $this->dbo->getQuery(true);
            $query->delete($this->dbo->quoteName('#__menu'));
            $query->where($this->dbo->quoteName('link') . 'LIKE "%com_biblestudy%"');

            // This check to make sure the menu items is from the Admin Main menu only
            $query->where($this->dbo->quoteName('client_id') . ' = 1');
            $query->where($this->dbo->quoteName('menutype') . ' = ' . $this->dbo->quote('main'));
            $this->dbo->setQuery($query);

            try {
                $menuBibleStudyStatus = $this->dbo->execute();
            } catch (RuntimeException $e) {
                JFactory::getApplication()->enqueueMessage("Failed to execute Admin Menu removal", "error");
            }

            // Update Site Menus for BibleStudy to Proclaim
            $query      = $this->dbo->getQuery(true);
            $query->select('id, component_id');
            $query->from($this->dbo->quoteName('#__menu'));
            $query->where($this->dbo->quoteName('link') . 'LIKE "%com_biblestudy%"');
            $query->where($this->dbo->quoteName('client_id') . ' = 0');
            $this->dbo->setQuery($query);
            $results = $this->dbo->loadObjectList();

            foreach ($results as $result) {
                $query      = $this->dbo->getQuery(true);

                // Fields to update.
                $fields = array(
                    $this->dbo->quoteName('link') . ' = ' . $this->dbo->quote(str_replace("com_biblestudy", "com_proclaim", $result->link)),
                    $this->dbo->quoteName('component_id') . ' = ' . $proclaimID,
                );

                // Conditions for which records should be updated.
                $conditions = array(
                    $this->dbo->quoteName('id') . ' = ' . $result->id,
                );
                $query->update($this->dbo->quoteName('#__menu'))->set($fields)->where($conditions);

                $this->dbo->setQuery($query);
                $menuBibleStudyStatus = $this->dbo->execute();
            }

            // Update Site Modules for BibleStudy to Proclaim
            $query      = $this->dbo->getQuery(true);
            $query->select('id, module');
            $query->from($this->dbo->quoteName('#__modules'));
            $query->where($this->dbo->quoteName('module') . 'LIKE "%mod_biblestudy%"');
            $this->dbo->setQuery($query);
            $results = $this->dbo->loadObjectList();

            foreach ($results as $result) {
                $query      = $this->dbo->getQuery(true);

                // Fields to update.
                $fields = array(
                    $this->dbo->quoteName('module') . ' = ' . $this->dbo->quote(str_replace("mod_biblestudy", "mod_proclaim", $result->module)),
                );

                // Conditions for which records should be updated.
                $conditions = array(
                    $this->dbo->quoteName('id') . ' = ' . $result->id,
                );
                $query->update($this->dbo->quoteName('#__menu'))->set($fields)->where($conditions);

                $this->dbo->setQuery($query);
                $menuBibleStudyStatus = $this->dbo->execute();
            }

            // Delete Old stale com_biblestudy extension.
            $query      = $this->dbo->getQuery(true);
            $query->delete($this->dbo->quoteName('#__extensions'));
            $query->where($this->dbo->quoteName('element') . ' = ' . $this->dbo->q('com_biblestudy'));
            $this->dbo->setQuery($query);

            try {
                $comBibleStudyStatus = $this->dbo->execute();
            } catch (RuntimeException $e) {
                JFactory::getApplication()->enqueueMessage("Failed to execute com_biblestudy removal", "error");
            }

            // Delete Old stale pkg_biblestudy_package extension.
            $query      = $this->dbo->getQuery(true);
            $query->delete($this->dbo->quoteName('#__extensions'));
            $query->where($this->dbo->quoteName('element') . ' = ' . $this->dbo->q('pkg_biblestudy_package'));
            $this->dbo->setQuery($query);

            try {
                $comBibleStudyStatus = $this->dbo->execute();
            } catch (RuntimeException $e) {
                JFactory::getApplication()->enqueueMessage("Failed to execute pkg_biblestudy_package removal", "error");
            }

            // Reset Status info
            $this->status          = new stdClass();
            $this->status->modules = [];
            $this->status->plugins = [];

            // Update Install Actions to uninstall the BibleStudy components
            self::$installActionQueue = [
            // -- modules => { (folder) => { (module) => { (position), (published) } }* }*
            'modules' => [
                'administrator' => [],
                'site'          => [
                    'biblestudy'         => 0,
                    'biblestudy_podcast' => 0,
                ],
            ],
            // -- plugins => { (folder) => { (element) => (published) }* }*
            'plugins' => [
                'finder' => ['biblestudy' => 0],
                'search' => ['biblestudysearch' => 0,],
                'system' => [
                    'jbspodcast' => 0,
                    'jbsbackup'  => 0,
                ],
            ],
            ];

            // Remove old Components
            $this->uninstallSubextensions();

            if (
                $menuBibleStudyStatus ||
                $comBibleStudyStatus ||
                count($this->status->modules) ||
                count($this->status->plugins)
            ) {
                JFactory::getApplication()->enqueueMessage("We have removed leftovers from Proclaim old version", "notice");
            }
        }
    }

    /**
     * Renders the post-installation message
     *
     * @param   object            $status  Object of things to install
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return void
     * @since  1.7.0
     */
    private function renderPostInstallation($status, $parent): void
    {
        try {
            $language = Factory::getApplication()->getLanguage();
            $language->load('com_proclaim', JPATH_ADMINISTRATOR . '/components/com_proclaim', 'en-GB', true);
            $language->load('com_proclaim', JPATH_ADMINISTRATOR . '/components/com_proclaim', null, true);
        } catch (Exception $e) {
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

        if (count($status->modules)) {
            echo "<tr>
                <th>Module</th>
                <th>Client</th>
                <th><?php echo Text::_('JBS_INS_STATUS'); ?></th>
            </tr>";

            foreach ($status->modules as $module) {
                echo '<tr>';
                echo '<td class="key">' . $module['name'] . '</td>';
                echo '<td class="key">' . ucfirst($module['client']) . '</td>';
                echo '<td class="key">';
                echo '<strong style="color: ' . ($module['result'] ? 'green' : 'red') . ';">';
                echo ' ' . ($module['result'] ? Text::_('JBS_INS_INSTALLED') : Text::_(
                    'JBS_INS_NOT_INSTALLED'
                )) . ' ';
                echo '</strong>';
                echo '</td>';
                echo '</tr>';
            }
        }

        if (count($status->plugins)) {
            ?>
                <tr>
                    <th>Plugin</th>
                    <th>Group</th>
                    <th><?php
                    echo Text::_('JBS_INS_STATUS'); ?></th>
                </tr>
                <?php
                foreach ($status->plugins as $plugin) {
                    echo '<tr>';
                    echo '<td class="key">' . ucfirst($plugin['name']) . '</td>';
                    echo '<td class="key">' . ucfirst($plugin['group']) . '</td>';
                    echo '<td>';
                    echo '<strong style="color: ' . ($plugin['result'] ? 'green' : 'red') . ';">';
                    echo ' ' . ($plugin['result'] ? Text::_('JBS_INS_INSTALLED') : Text::_(
                        'JBS_INS_NOT_INSTALLED'
                    )) . '';
                    echo '</strong>';
                    echo '</td>';
                    echo '</tr>';
                }
        }//end if

        echo '</tbody></table>';
    }


    /**
     * Render Post Uninstalling
     *
     * @param   object            $status  Object of things to uninstall
     * @param   InstallerAdapter  $parent  The class calling this method
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

        if (count($status->modules)) {
            ?>
                <tr>
                    <th><?php
                    echo Text::_('JBS_INS_MODULE'); ?></th>
                    <th><?php
                    echo Text::_('JBS_INS_CLIENT'); ?></th>
                    <th></th>
                </tr>
                <?php
                foreach ($status->modules as $module) {
                    ?>
                    <tr class="row<?php
                    echo $rows++; ?>">
                        <td class="key"><?php
                            echo $module['name']; ?></td>
                        <td class="key"><?php
                            echo ucfirst($module['client']); ?></td>
                        <td>
                            <strong style="color: <?php
                            echo '' . ($module['result'] ? 'green' : 'red'); ?>">
                                <?php
                                echo ' ' . ($module['result'] ? Text::_('JBS_INS_REMOVED') : Text::_(
                                    'JBS_INS_NOT_REMOVED'
                                ));
                                ?>
                            </strong>
                        </td>
                    </tr>
                    <?php
                }
        }//end if
        ?>
        <?php
        if (count($status->plugins)) {
            ?>
                <tr>
                    <th><?php
                    echo Text::_('Plugin'); ?></th>
                    <th><?php
                    echo Text::_('Group'); ?></th>
                    <th></th>
                </tr>
                <?php
                foreach ($status->plugins as $plugin) {
                    ?>
                    <tr class="row<?php
                    echo $rows++; ?>">
                        <td class="key"><?php
                            echo ucfirst($plugin['name']); ?></td>
                        <td class="key"><?php
                            echo ucfirst($plugin['group']); ?></td>
                        <td><strong style="color: <?php
                            echo '' . ($plugin['result'] ? 'green' : 'red'); ?>;">
                                <?php
                                echo '' . ($plugin['result'] ? Text::_('JBS_INS_REMOVED') : Text::_(
                                    'JBS_INS_NOT_REMOVED'
                                ));
                                ?>
                            </strong>
                        </td>
                    </tr>
                    <?php
                }
        }//end if

        echo '</tbody></table>';
    }


    /**
     * Installs extensions (modules, plugins) bundled with the main extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return void
     *
     * @since 1.7.0
     */
    private function installSubextensions($parent): void
    {
        $src                   = $parent->getParent()->getPath('source');
        $this->status          = new stdClass();
        $this->status->modules = [];
        $this->status->plugins = [];

        // Clean up old installed language files
        $languages = [
            "en-GB.com_proclaim.ini",
            "en-GB.com_proclaim.sys.ini",
            "en-GB.mod_proclaim.ini",
            "en-GB.mod_proclaim.sys.ini",
            "en-GB.mod_proclaim_podcast.ini",
            "en-GB.mod_proclaim_podcast.sys.ini",
            "en-GB.plg_proclaim.ini",
            "en-GB.plg_proclaim.sys.ini",
            "en-GB.plg_finder_proclaim.ini",
            "en-GB.plg_finder_proclaim.sys.ini"
        ];

        foreach ($languages as $language) {
            if (file_exists(JPATH_ADMINISTRATOR . "/language/en-GB/$language")) {
                File::delete(JPATH_ADMINISTRATOR . "/language/en-GB/$language");
            }
            if (file_exists(JPATH_ROOT . "/language/en-GB/$language")) {
                File::delete(JPATH_ROOT . "/language/en-GB/$language");
            }
        }

        // Modules installation
        if (count(self::$installActionQueue['modules'])) {
            foreach (self::$installActionQueue['modules'] as $folder => $modules) {
                if (count($modules)) {
                    foreach ($modules as $module => $modulePreferences) {
                        // Install the module
                        if (empty($folder)) {
                            $folder = 'site';
                        }

                        $path = "$src/modules/$folder/$module";

                        if (!is_dir($path)) {
                            $path = "$src/modules/$folder/mod_$module";
                        }

                        if (!is_dir($path)) {
                            $path = "$src/modules/$module";
                        }

                        if (!is_dir($path)) {
                            $path = "$src/modules/mod_$module";
                        }

                        if (!is_dir($path)) {
                            continue;
                        }

                        // Was the module already installed?
                        $sql = $this->dbo->getQuery(true)
                            ->select('COUNT(*)')
                            ->from('#__modules')
                            ->where($this->dbo->qn('module') . ' = ' . $this->dbo->q('mod_' . $module))
                            ->bind(':module', $module);
                        $this->dbo->setQuery($sql);
                        $count                   = $this->dbo->loadResult();
                        $installer               = new Installer();
                        $result                  = $installer->install($path);
                        $this->status->modules[] = [
                            'name'   => 'mod_' . $module,
                            'client' => $folder,
                            'result' => $result,
                        ];

                        // Modify where it's published and its published state
                        if (!$count) {
                            // A. Position and state
                            list($modulePosition, $modulePublished) = $modulePreferences;

                            if ($modulePosition === 'cwmcpanel') {
                                $modulePosition = 'icon';
                            }

                            $sql = $this->dbo->getQuery(true)
                                ->update($this->dbo->qn('#__modules'))
                                ->set($this->dbo->qn('position') . ' = ' . $this->dbo->q($modulePosition))
                                ->where($this->dbo->qn('module') . ' = ' . $this->dbo->q('mod_' . $module))
                                ->bind(':module', $module)
                                ->bind(':modulePosition', $modulePosition);

                            if ($modulePublished) {
                                $sql->set($this->dbo->qn('published') . ' = ' . $this->dbo->q('1'));
                            }

                            $this->dbo->setQuery($sql);
                            $this->dbo->execute();

                            // B. Change the ordering of back-end modules to 1 + max ordering
                            if ($folder === 'administrator') {
                                $query = $this->dbo->getQuery(true);
                                $query->select('MAX(' . $this->dbo->qn('ordering') . ')')
                                    ->from($this->dbo->qn('#__modules'))
                                    ->where($this->dbo->qn('position') . '=' . $this->dbo->q($modulePosition))
                                    ->bind(':modulePosition', $modulePosition);
                                $this->dbo->setQuery($query);
                                $position = $this->dbo->loadResult();
                                $position++;
                                $query = $this->dbo->getQuery(true);
                                $query->update($this->dbo->qn('#__modules'))
                                    ->set($this->dbo->qn('ordering') . ' = ' . $this->dbo->q($position))
                                    ->where($this->dbo->qn('module') . ' = ' . $this->dbo->q('mod_' . $module))
                                    ->bind(':position', $position)
                                    ->bind(':module', $module);
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
                                ->where($this->dbo->qn('moduleid') . ' = ' . $this->dbo->q($moduleid))
                                ->bind(':moduleid', $moduleid);
                            $this->dbo->setQuery($query);
                            $assignments = $this->dbo->loadObjectList();
                            $isAssigned  = !empty($assignments);

                            if (!$isAssigned) {
                                $o = (object)[
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
        if (count(self::$installActionQueue['plugins'])) {
            foreach (self::$installActionQueue['plugins'] as $folder => $plugins) {
                if (count($plugins) !== 0) {
                    foreach ($plugins as $plugin => $published) {
                        $path = "$src/plugins/$folder/$plugin";

                        if (!is_dir($path)) {
                            $path = "$src/plugins/$folder/plg_$plugin";
                        }

                        if (!is_dir($path)) {
                            $path = "$src/plugins/$plugin";
                        }

                        if (!is_dir($path)) {
                            $path = "$src/plugins/plg_$plugin";
                        }

                        if (!is_dir($path)) {
                            continue;
                        }

                        // Was the plugin already installed?
                        $query = $this->dbo->getQuery(true)
                            ->select('COUNT(*)')
                            ->from($this->dbo->qn('#__extensions'))
                            ->where($this->dbo->qn('element') . ' = ' . $this->dbo->q($plugin))
                            ->where($this->dbo->qn('folder') . ' = ' . $this->dbo->q($folder))
                            ->bind(':plugin', $plugin)
                            ->bind(':folder', $folder);
                        $this->dbo->setQuery($query);
                        $count                   = $this->dbo->loadResult();
                        $installer               = new JInstaller();
                        $result                  = $installer->install($path);
                        $this->status->plugins[] = [
                            'name'   => 'plg_' . $plugin,
                            'group'  => $folder,
                            'result' => $result,
                        ];

                        if ($published && !$count) {
                            $query = $this->dbo->getQuery(true)
                                ->update($this->dbo->qn('#__extensions'))
                                ->set($this->dbo->qn('enabled') . ' = ' . $this->dbo->q('1'))
                                ->where($this->dbo->qn('element') . ' = ' . $this->dbo->q($plugin))
                                ->where($this->dbo->qn('folder') . ' = ' . $this->dbo->q($folder))
                                ->bind(':plugin', $plugin)
                                ->bind(':folder', $folder);
                            $this->dbo->setQuery($query);
                            $this->dbo->execute();
                        }
                    }//end foreach
                }//end if
            }//end foreach
        }//end if
    }

    /**
     * Uninstalls extensions (modules, plugins) bundled with the main extension
     *
     * @return void
     *
     * @since 9.0.18
     */
    private function uninstallSubextensions(): void
    {
        $this->status          = new stdClass();
        $this->status->modules = [];
        $this->status->plugins = [];

        // Modules uninstalling
        if (count(self::$installActionQueue['modules'])) {
            foreach (self::$installActionQueue['modules'] as $folder => $modules) {
                if (count($modules)) {
                    foreach ($modules as $module => $modulePreferences) {
                        // Find the module ID
                        $sql = $this->dbo->getQuery(true)
                            ->select($this->dbo->qn('extension_id'))
                            ->from($this->dbo->qn('#__extensions'))
                            ->where($this->dbo->qn('element') . ' = ' . $this->dbo->q('mod_' . $module))
                            ->where($this->dbo->qn('type') . ' = ' . $this->dbo->q('module'))
                            ->bind(':module', $module);
                        $this->dbo->setQuery($sql);
                        $id = $this->dbo->loadResult();

                        // Uninstall the module
                        if ($id !== null) {
                            $installer               = new Installer();
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
        if (count(self::$installActionQueue['plugins'])) {
            foreach (self::$installActionQueue['plugins'] as $folder => $plugins) {
                if (count($plugins)) {
                    foreach ($plugins as $plugin => $published) {
                        $sql = $this->dbo->getQuery(true)
                            ->select($this->dbo->qn('extension_id'))
                            ->from($this->dbo->qn('#__extensions'))
                            ->where($this->dbo->qn('type') . ' = ' . $this->dbo->q('plugin'))
                            ->where($this->dbo->qn('element') . ' = ' . $this->dbo->q($plugin))
                            ->where($this->dbo->qn('folder') . ' = ' . $this->dbo->q($folder))
                            ->bind(':plugin', $plugin)
                            ->bind(':folder', $folder);
                        $this->dbo->setQuery($sql);

                        $id = $this->dbo->loadResult();

                        if ($id !== null) {
                            $installer               = new Installer();
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
    }

    /**
     * Set the database object from the installation adapter, if possible
     *
     * @param   InstallerAdapter|mixed  $adapter  The class calling this method
     *
     * @return  void
     * @since   7.2.0
     */
    private function setDboFromAdapter(mixed $adapter): void
    {
        $this->dbo = null;

        if (class_exists(InstallerAdapter::class) && ($adapter instanceof InstallerAdapter)) {
            /**
             * If this is Joomla 4.2+ the adapter has a protected getDatabase() method which we can access with the
             * magic property $adapter->db. On Joomla 4.1 and lower this is not available. So, we have to first figure
             * out if we can actually use the magic property...
             */

            try {
                $refObj = new ReflectionObject($adapter);

                if ($refObj->hasMethod('getDatabase')) {
                    $this->dbo = $adapter->db;

                    return;
                }
            } catch (Throwable $e) {
                // If something breaks we will fall through
            }
        }

        $this->dbo = Factory::getContainer()->get('DatabaseDriver');
    }
}
