<?php

/**
 * Proclaim Script install
 *
 * @package        Proclaim
 * @subpackage     com_proclaim
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

use CWM\Component\Proclaim\Administrator\Helper\CwmguidedtourHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmmigrationHelper;
use CWM\Component\Proclaim\Administrator\Lib\CwmscriptureMigration;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use Joomla\CMS\Installer\Adapter\FileAdapter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

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
     * The list of extra modules and plugins to install
     *
     * @var    array $Installation_Queue Array of Items to install
     * @author CWM Team
     * @since  9.0.18
     */
    private static array $installActionQueue = [
        // -- libraries => { (element) => (lock) }*
        // lock=1 means set #__extensions.locked=1 so the library can't be disabled while Proclaim depends on it
        'libraries' => [
            'cwmscripture' => 1,
        ],
        // -- modules => { (folder) => { (module) => { (position), (published) } }* }*
        'modules' => [
            'admin' => [
                'proclaimicon' => ['icon', 1],
            ],
            'site' => [
                'proclaim'         => ['', 0],
                'proclaim_podcast' => ['', 0],
                'proclaim_youtube' => ['', 0],
            ],
        ],
        // -- plugins => { (folder) => { (element) => (published) }* }*
        'plugins' => [
            'content'   => ['scripturelinks' => 1],
            'finder'    => ['proclaim' => 1],
            'schemaorg' => ['proclaim' => 1],
            'system'    => ['proclaim' => 1],
            'task'      => [
                'proclaim' => 1,
            ],
        ],
    ];

    /**
     * @var   string Path to MySQL files
     * @since 1.5
     */
    public string $filePath = '/components/com_proclaim/install/sql/updates/mysql';

    /**
     * The version number of the extension. Max 20 characters
     *
     * @var    string
     * @since  3.6
     */
    protected $release = '10.1.0';

    /**
     * @var   DatabaseInterface|null
     *
     * @since 7.2.0
     */
    protected DatabaseInterface|null $dbo;

    /**
     * Minimum PHP version required to install the extension
     *
     * @var    string
     * @since  3.6
     */
    protected $minimumPhp = '8.3.0';

    /**
     * Minimum Joomla! Version required to install the extension
     *
     * @var    string
     * @since  3.6
     */
    protected $minimumJoomla = '5.0.0';

    /**
     * @var   string The component's name
     * @since 1.5
     */
    protected $extension = 'com_proclaim';

    /**
     * @var   object
     * @since 1.5
     */
    protected object $status;

    /**
     * Allow downgrades of your extension
     *
     * Use at your own risk, as if there is a change in functionality, people may wish to downgrade.
     *
     * @var    bool
     * @since  3.6
     */
    protected $allowDowngrades = true;

    /**
     * A list of files to be deleted since version 10.0.1
     *
     * @var    array
     * @since  3.6
     */
    protected $deleteFiles = [
        // Media CSS files removed
        '/media/com_proclaim/css/icons.css',
        '/media/com_proclaim/css/icons.min.css',
        '/media/com_proclaim/css/bsmImport.css',
        '/media/com_proclaim/css/bsmImport.min.css',
        '/media/com_proclaim/css/general.min.min.css',
        '/media/com_proclaim/css/biblestudy.css.map',
        // Media CSS files removed in 10.1.0
        '/media/com_proclaim/css/bsms.fancybox.css',
        '/media/com_proclaim/css/bsms.fancybox.min.css',
        '/media/com_proclaim/css/biblestudy-debug.css',
        // Media JS files removed
        '/media/com_proclaim/js/bsmImport.js',
        '/media/com_proclaim/js/bsmImport.min.js',
        '/media/com_proclaim/js/noconflict.js',
        '/media/com_proclaim/js/noconflict.min.js',
        '/media/com_proclaim/js/videoswitch.js',
        '/media/com_proclaim/js/videoswitch.min.js',
        '/media/com_proclaim/js/jquery.mousewheel.pack.js',
        '/media/com_proclaim/js/jquery.mousewheel.pack.min.js',
        '/media/com_proclaim/js/cwmadmin-mediafiles-default-fileconvert-footer.js',
        '/media/com_proclaim/js/cwmadmin-mediafiles-default-fileconvert-footer.min.js',
        '/media/com_proclaim/js/assat.js',
        '/media/com_proclaim/js/assat.min.js',
        '/media/com_proclaim/js/assat.min.min.js',
        '/media/com_proclaim/js/biblestudy-ui.js',
        '/media/com_proclaim/js/biblestudy-ui.min.js',
        '/media/com_proclaim/js/biblestudy.min.js',
        '/media/com_proclaim/js/grunt-config.json',
        '/media/com_proclaim/js/modernizr-config.json',
        '/media/com_proclaim/js/modernizr.old.js',
        '/media/com_proclaim/js/modernizr.old.min.js',
        '/media/com_proclaim/js/modernizr.old.min.min.js',
        // Media JS files removed in 10.1.0
        '/media/com_proclaim/js/popper.min.js',
        '/media/com_proclaim/js/modernizr.min.js',
        // Fancybox old files (jQuery version)
        '/media/com_proclaim/fancybox/jquery.fancybox.min.css',
        '/media/com_proclaim/fancybox/jquery.fancybox.min.js',
        // Fancybox files removed in 10.1.0 (ESM replaced with UMD)
        '/media/com_proclaim/fancybox/fancybox.esm.js',
        '/media/com_proclaim/fancybox/fancybox.esm.min.js',
        // Legacy site files
        '/components/com_proclaim/controller.php',
        '/components/com_proclaim/proclaim.php',
        '/components/com_proclaim/proclaim.xml',
        '/components/com_proclaim/proclaimbak.php',
        '/components/com_proclaim/src/Model/CwmpodcastdisplayModel.php',
        '/components/com_proclaim/router.php',
        // German language files for the finder plugin
        '/plugins/finder/proclaim/language/de-DE/de-DE.plg_finder_biblestudy.ini',
        '/plugins/finder/proclaim/language/de-DE/de-DE.plg_finder_biblestudy.sys.ini',
        // Module files removed in 10.1.0
        '/modules/mod_proclaim_youtube/helper.php',
        // Dead helper classes removed in 10.1.0
        '/administrator/components/com_proclaim/src/Helper/Cwmsearchfilters.php',
        // Dead site server picker removed in 10.1.0
        '/components/com_proclaim/src/Model/CwmserverslistModel.php',
        '/components/com_proclaim/forms/filter_serverslist.xml',
        // Dead site form XMLs (no frontend models use them) removed in 10.1.0
        '/components/com_proclaim/forms/mediafile.xml',
        '/components/com_proclaim/forms/message.xml',
        // Bible provider files moved to lib_cwmscripture in 10.3.0
        '/components/com_proclaim/src/Bible/BibleProviderInterface.php',
        '/components/com_proclaim/src/Bible/AbstractBibleProvider.php',
        '/components/com_proclaim/src/Bible/BiblePassageResult.php',
        '/components/com_proclaim/src/Bible/BibleProviderFactory.php',
        '/components/com_proclaim/src/Bible/Provider/ApiBibleProvider.php',
        '/components/com_proclaim/src/Bible/Provider/GetBibleProvider.php',
        '/components/com_proclaim/src/Bible/Provider/LocalProvider.php',
        '/administrator/components/com_proclaim/src/Bible/BibleImporter.php',
        '/administrator/components/com_proclaim/src/Helper/ScriptureReference.php',
        // Scripture JS/CSS moved to lib_cwmscripture in 10.3.0
        // (cwm-fetch and scripture-autocomplete stay in com_proclaim)
        '/media/com_proclaim/js/bible-translations.js',
        '/media/com_proclaim/js/bible-translations.min.js',
        '/media/com_proclaim/js/bible-translations.min.js.gz',
        '/media/com_proclaim/js/bible-translations.min.js.map',
        '/media/com_proclaim/js/scripture-switcher.js',
        '/media/com_proclaim/js/scripture-switcher.min.js',
        '/media/com_proclaim/js/scripture-switcher.min.js.gz',
        '/media/com_proclaim/js/scripture-switcher.min.js.map',
        '/media/com_proclaim/js/scripture-tooltip.js',
        '/media/com_proclaim/js/scripture-tooltip.min.js',
        '/media/com_proclaim/js/scripture-tooltip.min.js.gz',
        '/media/com_proclaim/js/scripture-tooltip.min.js.map',
        '/media/com_proclaim/css/scripture-text.css',
        '/media/com_proclaim/css/scripture-text.min.css',
        '/media/com_proclaim/css/scripture-text.min.css.map',
        '/media/com_proclaim/css/scripture-switcher.css',
        '/media/com_proclaim/css/scripture-switcher.min.css',
        '/media/com_proclaim/css/scripture-switcher.min.css.map',
        '/media/com_proclaim/css/scripture-tooltip.css',
        '/media/com_proclaim/css/scripture-tooltip.min.css',
        '/media/com_proclaim/css/scripture-tooltip.min.css.map',
    ];

    /**
     * A list of folders to be deleted since version 10.0.1
     *
     * @var    array
     * @since  3.6
     */
    protected $deleteFolders = [
        // Media folders
        '/media/com_proclaim/player',
        '/media/com_proclaim/less',
        '/media/com_proclaim/backup',
        '/media/com_proclaim/js/plugins',
        '/media/com_proclaim/js/views',
        '/media/com_proclaim/js/mediafile',
        // Media folders removed in 10.1.0
        '/media/com_proclaim/carousel',
        '/media/com_proclaim/panzoom',
        // Legacy site folders
        '/components/com_proclaim/views',
        '/components/com_proclaim/models',
        '/components/com_proclaim/helpers',
        '/components/com_proclaim/lib',
        '/components/com_proclaim/old info',
        '/components/com_proclaim/sef_ext',
        // Old CWM prefixed View folders (renamed to Cwm prefix)
        '/components/com_proclaim/src/View/CWMCommentList',
        '/components/com_proclaim/src/View/CWMLandingPage',
        '/components/com_proclaim/src/View/CWMLatest',
        '/components/com_proclaim/src/View/CWMMediaFileForm',
        '/components/com_proclaim/src/View/CWMMediaFileList',
        '/components/com_proclaim/src/View/CWMMessageForm',
        '/components/com_proclaim/src/View/CWMMessageList',
        '/components/com_proclaim/src/View/Cwmpodcastdisplay',
        '/components/com_proclaim/src/View/CWMPodcastList',
        '/components/com_proclaim/src/View/CWMPopUp',
        '/components/com_proclaim/src/View/CWMSeriesDisplay',
        '/components/com_proclaim/src/View/CWMSeriesDisplays',
        '/components/com_proclaim/src/View/CWMSermon',
        '/components/com_proclaim/src/View/CWMSermons',
        '/components/com_proclaim/src/View/CWMServersList',
        '/components/com_proclaim/src/View/Cwmserverslist',
        '/components/com_proclaim/tmpl/cwmserverslist',
        '/components/com_proclaim/src/View/CWMSqueezeBox',
        '/components/com_proclaim/src/View/CWMTeacher',
        '/components/com_proclaim/src/View/CWMTeachers',
        '/components/com_proclaim/src/View/CWMTerms',
        '/components/com_proclaim/src/View/CWMcommentform',
        '/components/com_proclaim/src/View/Teacher',
        // Old CWM prefixed tmpl folders
        '/components/com_proclaim/tmpl/CWMCommentForm',
        '/components/com_proclaim/tmpl/CWMCommentList',
        '/components/com_proclaim/tmpl/CWMMediaFileForm',
        '/components/com_proclaim/tmpl/CWMMediaFileList',
        '/components/com_proclaim/tmpl/Cwmpodcastdisplay',
        '/components/com_proclaim/tmpl/CWMMessageForm',
        '/components/com_proclaim/tmpl/CWMMessageList',
        '/components/com_proclaim/tmpl/Teacher',
        // Removed plugins
        '/plugins/search/biblestudysearch',
        '/plugins/system/proclaimbackup',
        '/plugins/system/proclaimpodcast',
        // Bible provider folders moved to lib_cwmscripture in 10.3.0
        '/components/com_proclaim/src/Bible/Provider',
        '/components/com_proclaim/src/Bible',
        '/administrator/components/com_proclaim/src/Bible',
    ];

    /**
     * A list of folders to be renamed since version 10.0.1
     * Format: ['old_path' => 'new_path']
     *
     * @var    array
     * @since  10.0.2
     */
    protected array $renameFolders = [
        // Site View folders - CWM prefix renamed to Cwm
        '/components/com_proclaim/src/View/CWMCommentList'    => '/components/com_proclaim/src/View/Cwmcommentlist',
        '/components/com_proclaim/src/View/CWMLandingPage'    => '/components/com_proclaim/src/View/Cwmlandingpage',
        '/components/com_proclaim/src/View/CWMLatest'         => '/components/com_proclaim/src/View/Cwmlatest',
        '/components/com_proclaim/src/View/CWMMediaFileForm'  => '/components/com_proclaim/src/View/Cwmmediafileform',
        '/components/com_proclaim/src/View/CWMMediaFileList'  => '/components/com_proclaim/src/View/Cwmmediafilelist',
        '/components/com_proclaim/src/View/CWMMessageForm'    => '/components/com_proclaim/src/View/Cwmmessageform',
        '/components/com_proclaim/src/View/CWMMessageList'    => '/components/com_proclaim/src/View/Cwmmessagelist',
        '/components/com_proclaim/src/View/CWMPodcastList'    => '/components/com_proclaim/src/View/Cwmpodcastlist',
        '/components/com_proclaim/src/View/CWMPopUp'          => '/components/com_proclaim/src/View/Cwmpopup',
        '/components/com_proclaim/src/View/CWMSeriesDisplay'  => '/components/com_proclaim/src/View/Cwmseriesdisplay',
        '/components/com_proclaim/src/View/CWMSeriesDisplays' => '/components/com_proclaim/src/View/Cwmseriesdisplays',
        '/components/com_proclaim/src/View/CWMSermon'         => '/components/com_proclaim/src/View/Cwmsermon',
        '/components/com_proclaim/src/View/CWMSermons'        => '/components/com_proclaim/src/View/Cwmsermons',
        // CWMServersList removed — dead legacy modal picker (no frontend usage)
        '/components/com_proclaim/src/View/CWMSqueezeBox'  => '/components/com_proclaim/src/View/Cwmsqueezebox',
        '/components/com_proclaim/src/View/CWMTeacher'     => '/components/com_proclaim/src/View/Cwmteacher',
        '/components/com_proclaim/src/View/CWMTeachers'    => '/components/com_proclaim/src/View/Cwmteachers',
        '/components/com_proclaim/src/View/CWMTerms'       => '/components/com_proclaim/src/View/Cwmterms',
        '/components/com_proclaim/src/View/CWMcommentform' => '/components/com_proclaim/src/View/Cwmcommentform',
        '/components/com_proclaim/src/View/Teacher'        => '/components/com_proclaim/src/View/Cwmteacher',
        // Site tmpl folders - CWM prefix renamed to Cwm
        '/components/com_proclaim/tmpl/CWMCommentForm'   => '/components/com_proclaim/tmpl/Cwmcommentform',
        '/components/com_proclaim/tmpl/CWMCommentList'   => '/components/com_proclaim/tmpl/Cwmcommentlist',
        '/components/com_proclaim/tmpl/CWMMediaFileForm' => '/components/com_proclaim/tmpl/Cwmmediafileform',
        '/components/com_proclaim/tmpl/CWMMediaFileList' => '/components/com_proclaim/tmpl/Cwmmediafilelist',
        '/components/com_proclaim/tmpl/CWMMessageForm'   => '/components/com_proclaim/tmpl/Cwmmessageform',
        '/components/com_proclaim/tmpl/CWMMessageList'   => '/components/com_proclaim/tmpl/Cwmmessagelist',
        '/components/com_proclaim/tmpl/Teacher'          => '/components/com_proclaim/tmpl/Cwmteacher',
    ];

    /**
     * Function called before the extension installation/update/removal procedure commences
     *
     * @param   string            $type    The type of change (install, update, or discover_install, not uninstall)
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

        if ($type === 'update') {
            $this->ensureSchemaReady();
        }

        return parent::preflight($type, $parent);
    }

    /**
     * Ensure database schema matches the currently installed version.
     *
     * Reads the OLD install.mysql.utf8.sql (still on disk from the
     * installed version) and parses the expected columns per table.
     * Compares against the live database and drops any columns that
     * exist in the DB but NOT in the install SQL — these are leftovers
     * from a failed partial upgrade that would cause "Duplicate column"
     * errors when Joomla re-runs the migration SQL files.
     *
     * This restores the DB to the clean state of the installed version
     * so Joomla's migration path can run without errors.
     *
     * @return  void
     *
     * @since   10.2.1
     */
    private function ensureSchemaReady(): void
    {
        // Read the CURRENTLY INSTALLED install SQL (old version, not the new package)
        $installSqlPath = JPATH_ADMINISTRATOR . '/components/com_proclaim/sql/install.mysql.utf8.sql';

        if (!file_exists($installSqlPath)) {
            return;
        }

        $sql = @file_get_contents($installSqlPath);

        if ($sql === false || $sql === '') {
            return;
        }

        // Parse the install SQL to get expected columns per table
        $expectedSchema = $this->parseInstallSql($sql);

        if (empty($expectedSchema)) {
            return;
        }

        $db = $this->dbo;

        foreach ($expectedSchema as $table => $expectedColumns) {
            if (!$this->tableExists($table)) {
                continue;
            }

            $liveColumns = $this->getTableColumns($table);

            // Find columns in the live DB that are NOT in the install SQL.
            // These are leftovers from a failed partial upgrade — drop them
            // so the migration SQL's ADD COLUMN won't hit "Duplicate column".
            $extraColumns = array_diff($liveColumns, $expectedColumns);

            foreach ($extraColumns as $column) {
                try {
                    $db->setQuery(
                        'ALTER TABLE ' . $db->quoteName($table)
                        . ' DROP COLUMN ' . $db->quoteName($column)
                    );
                    $db->execute();
                } catch (\Exception $e) {
                    // Ignore — column may have been dropped by concurrent request
                }
            }
        }

        // Second pass: fix columns that SHOULD exist but are missing.
        // This catches bugs where a migration's schema version advanced
        // but the column was never added (e.g., missing from install SQL).
        $requiredFixes = [
            '#__bsms_podcast' => [
                'platform_links' => 'TEXT DEFAULT NULL',
            ],
        ];

        foreach ($requiredFixes as $table => $columns) {
            if (!$this->tableExists($table)) {
                continue;
            }

            $liveColumns = $this->getTableColumns($table);

            foreach ($columns as $column => $definition) {
                if (!\in_array($column, $liveColumns, true)) {
                    try {
                        $db->setQuery(
                            'ALTER TABLE ' . $db->quoteName($table)
                            . ' ADD COLUMN ' . $db->quoteName($column) . ' ' . $definition
                        );
                        $db->execute();
                    } catch (\Exception $e) {
                        // Ignore
                    }
                }
            }
        }
    }

    /**
     * Parse install.mysql.utf8.sql to extract expected columns per table.
     *
     * Reads CREATE TABLE statements and extracts column names (not keys,
     * constraints, or other non-column definitions).
     *
     * @param   string  $sql  The full install SQL content
     *
     * @return  array<string, array<string>>  Map of table name => [column names]
     *
     * @since   10.2.1
     */
    private function parseInstallSql(string $sql): array
    {
        $schema = [];

        // Match CREATE TABLE blocks: table name and body inside parentheses
        if (!preg_match_all(
            '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`([^`]+)`\s*\((.*?)\)\s*ENGINE/si',
            $sql,
            $matches,
            PREG_SET_ORDER
        )) {
            return [];
        }

        foreach ($matches as $match) {
            $table = $match[1];

            // Ensure #__ prefix for consistency
            if (!str_starts_with($table, '#__')) {
                $table = '#__' . $table;
            }

            $body = $match[2];

            // Extract column names: lines starting with `column_name` followed by a type
            // Skip PRIMARY KEY, KEY, INDEX, UNIQUE, CONSTRAINT lines
            $columns = [];

            foreach (explode("\n", $body) as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '--')) {
                    continue;
                }

                // Column definitions start with `column_name` then a space and type keyword
                if (preg_match('/^`([^`]+)`\s+\w/', $line)) {
                    $columns[] = preg_replace('/^`([^`]+)`.*/', '$1', $line);
                }
            }

            if (!empty($columns)) {
                $schema[$table] = $columns;
            }
        }

        return $schema;
    }

    /**
     * Get column names for a table.
     *
     * @param   string  $table  Table name with #__ prefix
     *
     * @return  array  List of column names
     *
     * @since   10.2.1
     */
    private function getTableColumns(string $table): array
    {
        try {
            $columns = $this->dbo->getTableColumns($table);

            return array_keys($columns);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Check if a database table exists
     *
     * @param   string  $table  The table name (with #__ prefix)
     *
     * @return  bool  True if table exists
     *
     * @since   10.0.0
     */
    private function tableExists(string $table): bool
    {
        $table = str_replace('#__', $this->dbo->getPrefix(), $table);

        try {
            $tables = $this->dbo->getTableList();

            return \in_array($table, $tables, true);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Find the installation path for an extension
     *
     * @param   string  $src       The source directory
     * @param   string  $type      The extension type ('module' or 'plugin')
     * @param   string  $folder    The folder (client for modules, group for plugins)
     * @param   string  $element   The extension element name
     *
     * @return  string|null  The path if found, null otherwise
     *
     * @since   10.0.0
     */
    private function findExtensionPath(string $src, string $type, string $folder, string $element): ?string
    {
        $prefix = $type === 'module' ? 'mod_' : 'plg_';
        $subdir = $type === 'module' ? 'modules' : 'plugins';

        $paths = [
            "$src/$subdir/$folder/$element",
            "$src/$subdir/$folder/{$prefix}$element",
            "$src/$subdir/$element",
            "$src/$subdir/{$prefix}$element",
        ];

        foreach ($paths as $path) {
            if (is_dir($path)) {
                return $path;
            }
        }

        return null;
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
             * magic property $adapter->db. On Joomla 4.1 and lower, this is not available. So, we have to first figure
             * out if we can actually use the magic property...
             */

            try {
                $refObj = new ReflectionObject($adapter);

                if ($refObj->hasMethod('getDatabase')) {
                    $this->dbo = $adapter->db;

                    return;
                }
            } catch (Throwable $e) {
                // If something breaks, we will fall through
            }
        }

        $this->dbo = Factory::getContainer()->get(DatabaseInterface::class);
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
        $this->uninstallSubExtensions();

        // Show the post-uninstalling page
        $this->renderPostUninstallation($this->status, $parent);

        return true;
    }

    /**
     * Uninstalls extensions (modules, plugins) bundled with the main extension
     *
     * @return void
     *
     * @since 9.0.18
     */
    private function uninstallSubExtensions(): void
    {
        $this->status            = new stdClass();
        $this->status->libraries = [];
        $this->status->modules   = [];
        $this->status->plugins   = [];

        // Unlock libraries (don't uninstall — they may be used standalone)
        if (!empty(self::$installActionQueue['libraries'])) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            foreach (self::$installActionQueue['libraries'] as $element => $lock) {
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__extensions'))
                    ->set($db->quoteName('locked') . ' = 0')
                    ->where($db->quoteName('type') . ' = ' . $db->quote('library'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote($element));
                $db->setQuery($query);
                $db->execute();

                $this->status->libraries[] = ['name' => $element, 'result' => true];
                Log::add('Library "' . $element . '" unlocked (Proclaim uninstalled).', Log::INFO, 'com_proclaim');
            }
        }

        // Modules uninstalling
        if (!empty(self::$installActionQueue['modules'])) {
            foreach (self::$installActionQueue['modules'] as $client => $modules) {
                if (!empty($modules)) {
                    $this->uninstallModules($client, $modules);
                }
            }
        }

        // Plugins uninstalling
        if (!empty(self::$installActionQueue['plugins'])) {
            foreach (self::$installActionQueue['plugins'] as $group => $plugins) {
                if (!empty($plugins)) {
                    $this->uninstallPlugins($group, $plugins);
                }
            }
        }
    }

    /**
     * Uninstall modules for a specific client
     *
     * @param   string  $client   Client (site/admin)
     * @param   array   $modules  List of modules
     *
     * @return void
     * @since 10.1.0
     */
    private function uninstallModules(string $client, array $modules): void
    {
        foreach ($modules as $module => $modulePreferences) {
            $element = 'mod_' . $module;
            $id      = $this->getExtensionId('module', $element);

            if ($id) {
                $installer = new Installer();
                $installer->setDatabase($this->dbo);
                $result = $installer->uninstall('module', $id, 1);

                $this->status->modules[] = [
                    'name'   => $element,
                    'client' => $client,
                    'result' => $result,
                ];
            }
        }
    }

    /**
     * Uninstall plugins for a specific group
     *
     * @param   string  $group    Plugin group
     * @param   array   $plugins  List of plugins
     *
     * @return void
     * @since 10.1.0
     */
    private function uninstallPlugins(string $group, array $plugins): void
    {
        foreach ($plugins as $plugin => $published) {
            $id = $this->getExtensionId('plugin', $plugin, $group);

            if ($id) {
                $installer = new Installer();
                $installer->setDatabase($this->dbo);
                $result = $installer->uninstall('plugin', $id, 1);

                $this->status->plugins[] = [
                    'name'   => 'plg_' . $plugin,
                    'group'  => $group,
                    'result' => $result,
                ];
            }
        }
    }

    /**
     * Get extension ID
     *
     * @param   string       $type     Extension type
     * @param   string       $element  Extension element
     * @param   string|null  $folder   Extension folder (optional)
     *
     * @return int|null
     * @since 10.1.0
     */
    private function getExtensionId(string $type, string $element, ?string $folder = null): ?int
    {
        $query = $this->dbo->getQuery(true)
            ->select($this->dbo->qn('extension_id'))
            ->from($this->dbo->qn('#__extensions'))
            ->where($this->dbo->qn('element') . ' = ' . $this->dbo->q($element))
            ->where($this->dbo->qn('type') . ' = ' . $this->dbo->q($type));

        if ($folder) {
            $query->where($this->dbo->qn('folder') . ' = ' . $this->dbo->q($folder));
        }

        $this->dbo->setQuery($query);
        $result = $this->dbo->loadResult();

        return $result ? (int) $result : null;
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

        if (!empty($status->modules)) {
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
        if (!empty($status->plugins)) {
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
     * Post Flight
     *
     * @param   string            $type    The type of change (install, update, or discover_install, not uninstall)
     * @param   ComponentAdapter  $parent  The class calling this method
     *
     * @return void
     *
     * @throws Exception
     * @since 1.5
     */
    public function postflight(string $type, ComponentAdapter $parent): void
    {
        // Rename old folders before deletion (must happen before removeFiles is called)
        if ($type === 'update') {
            $this->renameLegacyFolders();
        }

        // Install subExtensions
        $this->installSubExtensions($parent);

        // Copy language files to the system folder
        $this->copyLanguageFiles();

        // Show the post-installation page
        $this->renderPostInstallation($this->status, $parent);

        //Remove old com_biblestudy menu items on the admin side
        $this->removeBibleStudyVersion($parent);

        if ($type === 'install' || $type === 'update') {
            // This is a fresh install. Register for the guided tour directly.
            try {
                $helperPath = JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Helper/CwmguidedtourHelper.php';

                if (file_exists($helperPath)) {
                    require_once $helperPath;
                    $tourHelper = new CwmguidedtourHelper();

                    $tours      = $tourHelper->registerGuidedTours();
                    $messages   = $tourHelper->registerPostInstallMessages();

                    if ($tours > 0) {
                        Factory::getApplication()->enqueueMessage($tours . ' guided tour(s) have been installed.', 'message');
                    } else {
                        Factory::getApplication()->enqueueMessage('No new guided tours were installed.', 'notice');
                    }

                    if ($messages > 0) {
                        Factory::getApplication()->enqueueMessage($messages . ' post-installation message(s) have been installed.', 'message');
                    }
                } else {
                    Factory::getApplication()->enqueueMessage('Guided tour helper file not found.', 'warning');
                }
            } catch (\Exception $e) {
                Factory::getApplication()->enqueueMessage('Failed to register guided tour: ' . $e->getMessage(), 'error');
            }

            // Migrate legacy scripture columns to junction table
            try {
                $migrationPath = JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Lib/CwmscriptureMigration.php';

                if (file_exists($migrationPath)) {
                    require_once $migrationPath;
                    // Also require the helper dependencies
                    $helperDir = JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Helper/';
                    require_once $helperDir . 'ScriptureReference.php';
                    require_once $helperDir . 'CwmscriptureHelper.php';

                    $migrated = CwmscriptureMigration::migrate();

                    if ($migrated > 0) {
                        Factory::getApplication()->enqueueMessage(
                            $migrated . ' study scripture reference(s) migrated to new format.',
                            'message'
                        );
                    }
                }
            } catch (\Exception $e) {
                Factory::getApplication()->enqueueMessage(
                    'Scripture migration notice: ' . $e->getMessage(),
                    'warning'
                );
            }

            // Fix legacy image paths in mediafile params (images/biblestudy/ -> media/com_proclaim/images/)
            try {
                CwmmigrationHelper::fixMediafileLegacyPaths();
            } catch (\Exception $e) {
                Factory::getApplication()->enqueueMessage(
                    'Mediafile path migration notice: ' . $e->getMessage(),
                    'warning'
                );
            }

            // Migrate studyimage param values to thumbnailm column
            try {
                $this->migrateStudyImageParams();
            } catch (\Exception $e) {
                Factory::getApplication()->enqueueMessage(
                    'StudyImage migration notice: ' . $e->getMessage(),
                    'warning'
                );
            }

            // Drop vestigial text/pdf columns from templates table.
            // Done in PHP because ALTER TABLE DROP COLUMN is not idempotent.
            $this->dropLegacyTemplateColumns();

            // Migrate existing alternate link data to platform_links JSON
            $this->migratePodcastAlternateLinks();

            // Copy legacy image field to podcastimage where podcastimage is empty
            $this->migratePodcastImageField();

            // Match legacy podcastlink URLs to Joomla menu item IDs
            $this->migratePodcastLinkToMenuItem();

            // Migrate scripture settings from component params to plugin params
            $this->migrateScriptureParamsToPlugin();

            // Ensure all Proclaim tables have primary keys.
            // Sites upgraded from v7/v8/v9 may lack PKs because the original
            // CREATE TABLE IF NOT EXISTS skipped existing tables.  We check
            // information_schema first so this is safe on fresh installs where
            // the install SQL already created the PKs.
            $this->ensurePrimaryKeys();

            // Set helpURL for wiki-based help if not already configured
            try {
                $db     = Factory::getContainer()->get(DatabaseInterface::class);
                $query  = $db->getQuery(true)
                    ->select($db->quoteName('params'))
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('com_proclaim'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
                $params = new \Joomla\Registry\Registry($db->setQuery($query)->loadResult() ?: '{}');

                if (empty($params->get('helpURL'))) {
                    $params->set('helpURL', 'https://github.com/Joomla-Bible-Study/Proclaim/wiki/Help-{keyref}');
                    $update = $db->getQuery(true)
                        ->update($db->quoteName('#__extensions'))
                        ->set($db->quoteName('params') . ' = ' . $db->quote($params->toString()))
                        ->where($db->quoteName('element') . ' = ' . $db->quote('com_proclaim'))
                        ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
                    $db->setQuery($update)->execute();
                }
            } catch (\Exception $e) {
                // Non-critical — help will fall back to default behavior
            }
        }
        // Detect 9.x legacy schema and warn the user to run the upgrade wizard
        if ($type === 'install') {
            try {
                $db        = Factory::getContainer()->get(DatabaseInterface::class);
                $prefix    = $db->getPrefix();
                $tableList = $db->getTableList();

                if (
                    \in_array($prefix . 'bsms_version', $tableList, true)
                    || \in_array($prefix . 'bsms_schemaversion', $tableList, true)
                ) {
                    Factory::getApplication()->enqueueMessage(
                        'Proclaim 9.x data detected in your database. After installation completes, '
                        . 'go to <strong>Components &rarr; Proclaim &rarr; Admin Center</strong> and click the '
                        . '<strong>9.x Upgrade</strong> tab to complete the migration.',
                        'warning'
                    );
                }
            } catch (\Exception $e) {
                // Silently ignore detection failures during install
            }
        }

        // For updates, we use the migration process
        $parent->getParent()->setRedirectURL(
            'index.php?option=com_proclaim&view=cwminstall&task=cwminstall.browse&scanstate=start&' .
            Session::getFormToken() . '=1'
        );
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
    private function installSubExtensions(InstallerAdapter $parent): void
    {
        $src                     = $parent->getParent()->getPath('source');
        $this->status            = new stdClass();
        $this->status->libraries = [];
        $this->status->modules   = [];
        $this->status->plugins   = [];

        // Libraries installation (must be first — other extensions may depend on them)
        if (!empty(self::$installActionQueue['libraries'])) {
            $this->installLibraries(self::$installActionQueue['libraries'], $src);
        }

        // Modules installation
        if (!empty(self::$installActionQueue['modules'])) {
            foreach (self::$installActionQueue['modules'] as $client => $modules) {
                if (!empty($modules)) {
                    $this->installModules($client, $modules, $src, $parent->route === 'install');
                }
            }
        }

        // Plugin's installation
        if (!empty(self::$installActionQueue['plugins'])) {
            foreach (self::$installActionQueue['plugins'] as $group => $plugins) {
                if (!empty($plugins)) {
                    $this->installPlugins($group, $plugins, $src);
                }
            }
        }
    }

    /**
     * Copy language files to the system folder
     *
     * @return void
     * @since 10.1.0
     */
    private function copyLanguageFiles(): void
    {
        $src  = JPATH_ADMINISTRATOR . '/components/com_proclaim/language';
        $dest = JPATH_ADMINISTRATOR . '/language';

        if (!is_dir($src)) {
            Log::add('Language source folder not found: ' . $src, Log::WARNING, 'com_proclaim');
            return;
        }

        $folders = Folder::folders($src);

        if (empty($folders)) {
            Log::add('No language folders found in: ' . $src, Log::WARNING, 'com_proclaim');
            return;
        }

        foreach ($folders as $folder) {
            $targetDir = $dest . '/' . $folder;

            if (!is_dir($targetDir)) {
                Folder::create($targetDir);
            }

            $files = Folder::files($src . '/' . $folder);
            foreach ($files as $file) {
                $srcFile  = $src . '/' . $folder . '/' . $file;
                $destFile = $targetDir . '/' . $file;

                try {
                    if (File::copy($srcFile, $destFile)) {
                        Log::add('Copied language file: ' . $file, Log::INFO, 'com_proclaim');
                    } else {
                        Log::add('Failed to copy language file: ' . $file, Log::WARNING, 'com_proclaim');
                    }
                } catch (\Exception $e) {
                    Log::add('Exception copying language file: ' . $file . ' - ' . $e->getMessage(), Log::ERROR, 'com_proclaim');
                }
            }
        }
    }

    /**
     * Install library extensions and optionally lock them.
     *
     * Libraries are installed from the submodule path within the package.
     * When lock=1, the extension is marked as locked in #__extensions so
     * it cannot be disabled while Proclaim depends on it.
     *
     * @param   array   $libraries  Map of element => lock flag
     * @param   string  $src        Source path of the package
     *
     * @return  void
     *
     * @since  10.3.0
     */
    private function installLibraries(array $libraries, string $src): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        foreach ($libraries as $element => $lock) {
            // Check submodule path first (libraries/cwmscripture_src/lib_cwmscripture/)
            $path = $src . '/libraries/' . $element . '_src/lib_' . $element;

            if (!is_dir($path)) {
                // Fallback: direct path (for when built into the package)
                $path = $src . '/libraries/' . $element;
            }

            if (!is_dir($path)) {
                Log::add('Library path not found: ' . $path, Log::WARNING, 'com_proclaim');
                $this->status->libraries[] = ['name' => $element, 'result' => false];

                continue;
            }

            $installer = new Installer();
            $result    = $installer->install($path);

            $this->status->libraries[] = ['name' => $element, 'result' => $result];

            if ($result && $lock) {
                // Lock the library so it cannot be disabled from Extension Manager
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__extensions'))
                    ->set($db->quoteName('locked') . ' = 1')
                    ->where($db->quoteName('type') . ' = ' . $db->quote('library'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote($element));
                $db->setQuery($query);
                $db->execute();

                Log::add('Library "' . $element . '" installed and locked.', Log::INFO, 'com_proclaim');
            }
        }
    }

    /**
     * Install modules for a specific client
     *
     * @param   string  $client        Client (site/admin)
     * @param   array   $modules       List of modules
     * @param   string  $src           Source path
     * @param   bool    $isNewInstall  True if new install
     *
     * @return void
     * @since 10.1.0
     */
    private function installModules(string $client, array $modules, string $src, bool $isNewInstall): void
    {
        $client = $client ?: 'site';

        foreach ($modules as $module => $preferences) {
            $path = $this->findExtensionPath($src, 'module', $client, $module);

            if ($path === null) {
                continue;
            }

            $installer = new Installer();
            $installer->setDatabase($this->dbo);
            $result = $installer->install($path);

            $this->status->modules[] = [
                'name'   => 'mod_' . $module,
                'client' => $client,
                'result' => $result,
            ];

            if ($isNewInstall && $result) {
                $this->configureModule($module, $client, $preferences);
            }
        }
    }

    /**
     * Configure a module after installation
     *
     * @param   string  $module       Module name
     * @param   string  $client       Client
     * @param   array   $preferences  Preferences
     *
     * @return void
     * @since 10.1.0
     */
    private function configureModule(string $module, string $client, array $preferences): void
    {
        [$position, $published] = (array) $preferences;
        $element                = 'mod_' . $module;

        $query = $this->dbo->getQuery(true)
            ->update($this->dbo->qn('#__modules'))
            ->set($this->dbo->qn('position') . ' = ' . $this->dbo->q($position))
            ->where($this->dbo->qn('module') . ' = ' . $this->dbo->q($element));

        if ($published !== null) {
            $query->set($this->dbo->qn('published') . ' = ' . (int) $published);
        }

        if ($module === 'proclaimicon') {
            $params = '{"context":"mod_proclaimicon","header_icon":"fas fa-bible","show_messages":"1","show_mediafiles":"0","show_teachers":"1","show_series":"0","show_messagetypes":"0","show_locations":"0","show_topics":"0","show_comments":"0","show_servers":"0","show_podcasts":"0","show_templates":"0","show_templatecode":"0","show_admin":"0","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}';
            $query->set($this->dbo->qn('params') . ' = ' . $this->dbo->q($params));
        }

        $this->dbo->setQuery($query);
        $this->dbo->execute();

        if ($client === 'admin') {
            $this->reorderAdminModule($position, $element);
        }

        $this->assignModuleToAllPages($element);
    }

    /**
     * Reorder admin module
     *
     * @param   string  $position  Module position
     * @param   string  $element   Module element
     *
     * @return void
     * @since 10.1.0
     */
    private function reorderAdminModule(string $position, string $element): void
    {
        $query = $this->dbo->getQuery(true)
            ->select('MAX(' . $this->dbo->qn('ordering') . ')')
            ->from($this->dbo->qn('#__modules'))
            ->where($this->dbo->qn('position') . '=' . $this->dbo->q($position));
        $this->dbo->setQuery($query);
        $maxOrder = (int) $this->dbo->loadResult();

        $query = $this->dbo->getQuery(true)
            ->update($this->dbo->qn('#__modules'))
            ->set($this->dbo->qn('ordering') . ' = ' . ($maxOrder + 1))
            ->where($this->dbo->qn('module') . ' = ' . $this->dbo->q($element));
        $this->dbo->setQuery($query);
        $this->dbo->execute();
    }

    /**
     * Assign module to all pages
     *
     * @param   string  $element  Module element
     *
     * @return void
     * @since 10.1.0
     */
    private function assignModuleToAllPages(string $element): void
    {
        $query = $this->dbo->getQuery(true)
            ->select('id')
            ->from($this->dbo->qn('#__modules'))
            ->where($this->dbo->qn('module') . ' = ' . $this->dbo->q($element));
        $this->dbo->setQuery($query);
        $moduleId = (int) $this->dbo->loadResult();

        if ($moduleId) {
            $query = $this->dbo->getQuery(true)
                ->select('COUNT(*)')
                ->from($this->dbo->qn('#__modules_menu'))
                ->where($this->dbo->qn('moduleid') . ' = ' . $moduleId);
            $this->dbo->setQuery($query);

            if (!$this->dbo->loadResult()) {
                $obj = (object) ['moduleid' => $moduleId, 'menuid' => 0];
                $this->dbo->insertObject('#__modules_menu', $obj);
            }
        }
    }

    /**
     * Install plugins for a specific group
     *
     * @param   string  $group    Plugin group
     * @param   array   $plugins  List of plugins
     * @param   string  $src      Source path
     *
     * @return void
     * @since 10.1.0
     */
    private function installPlugins(string $group, array $plugins, string $src): void
    {
        foreach ($plugins as $plugin => $published) {
            $path = $this->findExtensionPath($src, 'plugin', $group, $plugin);

            if ($path === null) {
                continue;
            }

            // Check if already installed
            $query = $this->dbo->getQuery(true)
                ->select('COUNT(*)')
                ->from($this->dbo->qn('#__extensions'))
                ->where($this->dbo->qn('element') . ' = ' . $this->dbo->q($plugin))
                ->where($this->dbo->qn('folder') . ' = ' . $this->dbo->q($group));
            $this->dbo->setQuery($query);
            $isInstalled = (bool) $this->dbo->loadResult();

            $installer = new Installer();
            $installer->setDatabase($this->dbo);
            $result = $installer->install($path);

            $this->status->plugins[] = [
                'name'   => 'plg_' . $plugin,
                'group'  => $group,
                'result' => $result,
            ];

            if ($published && !$isInstalled) {
                $query = $this->dbo->getQuery(true)
                    ->update($this->dbo->qn('#__extensions'))
                    ->set($this->dbo->qn('enabled') . ' = 1')
                    ->where($this->dbo->qn('element') . ' = ' . $this->dbo->q($plugin))
                    ->where($this->dbo->qn('folder') . ' = ' . $this->dbo->q($group));
                $this->dbo->setQuery($query);
                $this->dbo->execute();
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

        if (!empty($status->modules)) {
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

        if (!empty($status->plugins)) {
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
     * Remove leftovers due to upgrading from an older Proclaim version.
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return void
     * @throws Exception
     * @since 10.0.0
     */
    private function removeBibleStudyVersion(InstallerAdapter $parent): void
    {
        $biblestudyID = $this->getExtensionId('component', 'com_biblestudy');

        // Check if the migration helper exists and include it
        $migrationHelperPath = JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Helper/CwmmigrationHelper.php';
        if (file_exists($migrationHelperPath)) {
            require_once $migrationHelperPath;
        }

        if ($biblestudyID) {
            $proclaimID = $this->getExtensionId('component', 'com_proclaim');

            // Remove old com_biblestudy folders and files as we can't uninstall them
            $this->deleteFolders = ['/components/com_biblestudy', '/administrator/components/com_biblestudy'];
            $this->deleteFiles   = ['/language/en-GB/en-GB.com_biblestudy.ini'];

            // Call parent removeFiles with the required argument
            $this->removeFiles($parent);

            // Clean up Admin Menus from old install
            $query = $this->dbo->getQuery(true)
                ->delete($this->dbo->qn('#__menu'))
                ->where($this->dbo->qn('link') . ' LIKE ' . $this->dbo->q('%com_biblestudy%'))
                ->where($this->dbo->qn('client_id') . ' = 1')
                ->where($this->dbo->qn('menutype') . ' = ' . $this->dbo->q('main'));
            $this->dbo->setQuery($query);

            try {
                $this->dbo->execute();
            } catch (RuntimeException $e) {
                Factory::getApplication()->enqueueMessage('Failed to execute Admin Menu removal', 'error');
            }

            // Update Site Menus for BibleStudy to Proclaim
            if ($proclaimID) {
                $query = $this->dbo->getQuery(true)
                    ->update($this->dbo->qn('#__menu'))
                    ->set($this->dbo->qn('component_id') . ' = ' . (int) $proclaimID)
                    ->set($this->dbo->qn('link') . ' = REPLACE(' . $this->dbo->qn('link') . ', ' . $this->dbo->q('com_biblestudy&view=') . ', ' . $this->dbo->q('com_proclaim&view=cwm') . ')')
                    ->where($this->dbo->qn('link') . ' LIKE ' . $this->dbo->q('%com_biblestudy%'))
                    ->where($this->dbo->qn('client_id') . ' = 0');
                $this->dbo->setQuery($query);
                $this->dbo->execute();
            }

            // Update Site Modules for BibleStudy to Proclaim
            $query = $this->dbo->getQuery(true)
                ->update($this->dbo->qn('#__modules'))
                ->set($this->dbo->qn('module') . ' = REPLACE(' . $this->dbo->qn('module') . ', ' . $this->dbo->q('mod_biblestudy') . ', ' . $this->dbo->q('mod_proclaim') . ')')
                ->where($this->dbo->qn('module') . ' LIKE ' . $this->dbo->q('%mod_biblestudy%'));
            $this->dbo->setQuery($query);
            $this->dbo->execute();

            // Migrate Plugin Podcast to Tasks
            $message                     = new \stdClass();
            $message->title_key          = 'New Scheduled Task for Podcast RSS Rile Creation'; // Language string
            $message->description_key    = 'You may now want to set up Podcast RSS Task to replace your old system. We could not migrate your old podcast plugin schedule'; // Language string
            $message->type               = 'message'; // message | action
            $message->version_introduced = '10.0.0';
            CwmmigrationHelper::postInstallMessages($message);

            // Migrate Plugin Backup to Tasks
            $message                     = new \stdClass();
            $message->title_key          = 'New Scheduled Task for Backups'; // Language string
            $message->description_key    = 'You may now what to setup backups to replace your old system. We could not migrate your old backup plugin schedule'; // Language string
            $message->type               = 'message'; // message | action
            $message->version_introduced = '10.0.0';
            CwmmigrationHelper::postInstallMessages($message);

            // Delete Old stale com_biblestudy extension.
            $this->deleteExtension('component', 'com_biblestudy');

            // Delete Old stale pkg_biblestudy_package extension.
            $this->deleteExtension('package', 'pkg_biblestudy_package');

            // Reset Status info
            $this->status          = new stdClass();
            $this->status->modules = [];
            $this->status->plugins = [];

            // Update Install Actions to uninstall the BibleStudy components
            $originalQueue            = self::$installActionQueue;
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
                'search' => ['biblestudysearch' => 0],
                'system' => [
                    'jbspodcast' => 0,
                    'jbsbackup'  => 0,
                ],
            ],
            ];

            // Remove old Components
            $this->uninstallSubExtensions();

            // Restore queue
            self::$installActionQueue = $originalQueue;

            if (
                !empty($this->status->modules) ||
                !empty($this->status->plugins)
            ) {
                Factory::getApplication()->enqueueMessage(
                    'We have removed leftovers from the Proclaim old version',
                    'notice'
                );
            }
        }
    }

    /**
     * Delete an extension from the database
     *
     * @param   string  $type     Extension type
     * @param   string  $element  Extension element
     *
     * @return void
     * @throws Exception
     * @since 10.1.0
     */
    private function deleteExtension(string $type, string $element): void
    {
        $query = $this->dbo->getQuery(true)
            ->delete($this->dbo->qn('#__extensions'))
            ->where($this->dbo->qn('element') . ' = ' . $this->dbo->q($element))
            ->where($this->dbo->qn('type') . ' = ' . $this->dbo->q($type));
        $this->dbo->setQuery($query);

        try {
            $this->dbo->execute();
        } catch (RuntimeException $e) {
            Factory::getApplication()->enqueueMessage("Failed to execute $element removal", 'error');
        }
    }

    /**
     * Rename legacy folders to the new naming convention before deletion.
     * This preserves any user customizations by renaming rather than deleting.
     *
     * @return void
     *
     * @since 10.0.2
     */
    private function renameLegacyFolders(): void
    {
        foreach ($this->renameFolders as $oldPath => $newPath) {
            $oldFullPath = JPATH_ROOT . $oldPath;
            $newFullPath = JPATH_ROOT . $newPath;

            // Only rename if the old folder exists and the new folder doesn't
            if (is_dir($oldFullPath) && !is_dir($newFullPath)) {
                Folder::move($oldFullPath, $newFullPath);
            }
        }
    }

    /**
     * Migrate studyimage param values to thumbnailm column
     *
     * Messages that only have a studyimage (stock image) but no thumbnailm
     * need the value preserved since the studyimage field is being removed.
     *
     * @return void
     *
     * @since 10.1.0
     */
    /**
     * Drop unused text and pdf columns from the templates table.
     *
     * These columns were never rendered on the frontend. The drop is done in
     * PHP rather than SQL because ALTER TABLE … DROP COLUMN is not idempotent
     * in MySQL — it errors if the column was already removed.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function dropLegacyTemplateColumns(): void
    {
        try {
            $db     = Factory::getContainer()->get(DatabaseInterface::class);
            $prefix = $db->getPrefix();
            $table  = $prefix . 'bsms_templates';

            $columns = ['text', 'pdf'];

            foreach ($columns as $column) {
                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from('INFORMATION_SCHEMA.COLUMNS')
                    ->where('TABLE_SCHEMA = DATABASE()')
                    ->where($db->quoteName('TABLE_NAME') . ' = ' . $db->quote($table))
                    ->where($db->quoteName('COLUMN_NAME') . ' = ' . $db->quote($column));
                $db->setQuery($query);

                if ((int) $db->loadResult() > 0) {
                    $db->setQuery('ALTER TABLE ' . $db->quoteName($table) . ' DROP COLUMN ' . $db->quoteName($column));
                    $db->execute();
                }
            }
        } catch (\Exception $e) {
            // Non-fatal — column may already be gone
        }
    }

    /**
     * Add missing primary keys to Proclaim tables.
     *
     * Legacy sites upgraded from v7/v8/v9 may lack PKs because the original
     * install used CREATE TABLE IF NOT EXISTS which skipped existing tables.
     * We query information_schema to avoid "Multiple primary key" errors on
     * tables that already have the correct PK.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function ensurePrimaryKeys(): void
    {
        try {
            $db     = Factory::getContainer()->get(DatabaseInterface::class);
            $prefix = $db->getPrefix();

            // table_suffix => PK column
            $tables = [
                'bsms_admin'              => 'id',
                'bsms_books'              => 'id',
                'bsms_comments'           => 'id',
                'bsms_locations'          => 'id',
                'bsms_mediafiles'         => 'id',
                'bsms_message_type'       => 'id',
                'bsms_podcast'            => 'id',
                'bsms_series'             => 'id',
                'bsms_servers'            => 'id',
                'bsms_studies'            => 'id',
                'bsms_studytopics'        => 'id',
                'bsms_teachers'           => 'id',
                'bsms_templatecode'       => 'id',
                'bsms_templates'          => 'id',
                'bsms_topics'             => 'id',
                'bsms_study_scriptures'   => 'id',
                'bsms_study_teachers'     => 'id',
                'bsms_analytics_events'   => 'id',
                'bsms_analytics_monthly'  => 'id',
                'bsms_platform_stats'     => 'id',
                'bsms_bible_translations' => 'id',
                'bsms_bible_verses'       => 'id',
                'bsms_scripture_cache'    => 'id',
                'bsms_timeset'            => 'timeset',
            ];

            $added = 0;

            foreach ($tables as $suffix => $pkColumn) {
                $fullName = $prefix . $suffix;

                // Check if a PK already exists
                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('information_schema.TABLE_CONSTRAINTS'))
                    ->where($db->quoteName('TABLE_SCHEMA') . ' = DATABASE()')
                    ->where($db->quoteName('TABLE_NAME') . ' = ' . $db->quote($fullName))
                    ->where($db->quoteName('CONSTRAINT_TYPE') . ' = ' . $db->quote('PRIMARY KEY'));
                $db->setQuery($query);

                if ((int) $db->loadResult() > 0) {
                    continue;
                }

                // Table exists but has no PK — add it
                $db->setQuery(
                    'ALTER TABLE ' . $db->quoteName($fullName)
                    . ' ADD PRIMARY KEY (' . $db->quoteName($pkColumn) . ')'
                );
                $db->execute();
                $added++;
            }

            if ($added > 0) {
                Factory::getApplication()->enqueueMessage(
                    'Added primary keys to ' . $added . ' table(s) missing them.',
                    'message'
                );
            }
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage(
                'Primary key check notice: ' . $e->getMessage(),
                'warning'
            );
        }
    }

    private function migrateStudyImageParams(): void
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->qn(['id', 'params']))
            ->from($db->qn('#__bsms_studies'))
            ->where('(' . $db->qn('thumbnailm') . ' IS NULL OR ' . $db->qn('thumbnailm') . ' = ' . $db->q('') . ')');
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $migrated = 0;

        foreach ($rows as $row) {
            if (empty($row->params)) {
                continue;
            }

            try {
                $params = json_decode($row->params, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                continue;
            }

            if (!$params || empty($params['studyimage']) || $params['studyimage'] === '-1') {
                continue;
            }

            $studyImage = $params['studyimage'];

            // Strip hash fragment if present
            if (str_contains($studyImage, '#')) {
                $studyImage = substr($studyImage, 0, strpos($studyImage, '#'));
            }

            // Skip invalid values
            if (empty($studyImage) || $studyImage === '-1') {
                continue;
            }

            // If it's just a filename, prepend the old stock images path
            if (!str_contains($studyImage, '/')) {
                $studyImage = 'media/com_proclaim/images/stockimages/' . $studyImage;
            }

            // Only migrate if the file actually exists
            if (!is_file(JPATH_ROOT . '/' . $studyImage)) {
                continue;
            }

            // Update thumbnailm and remove studyimage from params
            unset($params['studyimage']);
            $newParams = json_encode($params);

            $update = $db->getQuery(true)
                ->update($db->qn('#__bsms_studies'))
                ->set($db->qn('thumbnailm') . ' = ' . $db->q($studyImage))
                ->set($db->qn('params') . ' = ' . $db->q($newParams))
                ->where($db->qn('id') . ' = ' . (int) $row->id);
            $db->setQuery($update);
            $db->execute();

            $migrated++;
        }

        if ($migrated > 0) {
            Factory::getApplication()->enqueueMessage(
                $migrated . ' study image(s) migrated from stock image field to thumbnail.',
                'message'
            );
        }
    }

    /**
     * Migrate existing alternate link data to the new platform_links JSON column.
     *
     * Converts alternatelink/alternatewords/alternateimage into a single-entry
     * platform_links JSON array.  Idempotent: skips rows that already have
     * platform_links populated or have no alternate link set.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function migratePodcastAlternateLinks(): void
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            // Check if the platform_links column exists yet
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('INFORMATION_SCHEMA.COLUMNS')
                ->where('TABLE_SCHEMA = DATABASE()')
                ->where($db->quoteName('TABLE_NAME') . ' = ' . $db->quote($db->getPrefix() . 'bsms_podcast'))
                ->where($db->quoteName('COLUMN_NAME') . ' = ' . $db->quote('platform_links'));
            $db->setQuery($query);

            if ((int) $db->loadResult() === 0) {
                return;
            }

            // Find podcasts with alternate link but no platform_links yet
            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'alternatelink', 'alternatewords', 'alternateimage']))
                ->from($db->quoteName('#__bsms_podcast'))
                ->where($db->quoteName('alternatelink') . ' IS NOT NULL')
                ->where($db->quoteName('alternatelink') . ' != ' . $db->quote(''))
                ->where('(' . $db->quoteName('platform_links') . ' IS NULL OR '
                    . $db->quoteName('platform_links') . ' = ' . $db->quote('') . ')');
            $db->setQuery($query);
            $rows = $db->loadObjectList();

            // Load platform patterns from XML for auto-detection
            $platformPatterns = [];
            $xmlFile          = JPATH_ADMINISTRATOR . '/components/com_proclaim/forms/podcast-platforms.xml';

            if (file_exists($xmlFile)) {
                $xml = simplexml_load_file($xmlFile);

                if ($xml !== false) {
                    foreach ($xml->platform as $p) {
                        $pattern = (string) ($p['pattern'] ?? '');

                        if ($pattern !== '') {
                            $platformPatterns[] = [
                                'key'     => (string) $p['key'],
                                'pattern' => $pattern,
                            ];
                        }
                    }
                }
            }

            $migrated = 0;

            foreach ($rows as $row) {
                // Auto-detect platform from URL (e.g. itpc:// → apple, spotify.com → spotify)
                $detectedPlatform = 'custom';
                $lower            = strtolower($row->alternatelink);

                foreach ($platformPatterns as $pp) {
                    foreach (explode('|', $pp['pattern']) as $pat) {
                        if (str_contains($lower, trim($pat))) {
                            $detectedPlatform = $pp['key'];
                            break 2;
                        }
                    }
                }

                $links = [
                    [
                        'platform'    => $detectedPlatform,
                        'url'         => $row->alternatelink,
                        'label'       => $row->alternatewords ?: '',
                        'badge_image' => $row->alternateimage ?: '',
                    ],
                ];

                $update = $db->getQuery(true)
                    ->update($db->quoteName('#__bsms_podcast'))
                    ->set($db->quoteName('platform_links') . ' = ' . $db->quote(json_encode($links)))
                    ->where($db->quoteName('id') . ' = ' . (int) $row->id);
                $db->setQuery($update);
                $db->execute();

                $migrated++;
            }

            if ($migrated > 0) {
                Factory::getApplication()->enqueueMessage(
                    $migrated . ' podcast alternate link(s) migrated to platform links.',
                    'message'
                );
            }
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage(
                'Podcast alternate link migration notice: ' . $e->getMessage(),
                'warning'
            );
        }
    }

    /**
     * Copy legacy `image` field value to `podcastimage` where podcastimage is empty.
     *
     * The `image` field has been removed from the form in favour of a single
     * `podcastimage` field.  Existing data is preserved by copying values
     * during upgrade.
     *
     * @return void
     *
     * @since 10.1.0
     */
    private function migratePodcastImageField(): void
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_podcast'))
                ->set($db->quoteName('podcastimage') . ' = ' . $db->quoteName('image'))
                ->where($db->quoteName('image') . ' IS NOT NULL')
                ->where($db->quoteName('image') . ' != ' . $db->quote(''))
                ->where('(' . $db->quoteName('podcastimage') . ' IS NULL OR '
                    . $db->quoteName('podcastimage') . ' = ' . $db->quote('') . ')');
            $db->setQuery($query);
            $affected = $db->execute();

            $count = $db->getAffectedRows();

            if ($count > 0) {
                Factory::getApplication()->enqueueMessage(
                    $count . ' podcast image(s) migrated from legacy image field to podcast artwork.',
                    'message'
                );
            }
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage(
                'Podcast image migration notice: ' . $e->getMessage(),
                'warning'
            );
        }
    }

    /**
     * Match legacy podcastlink URL strings to Joomla menu item IDs.
     *
     * Before 10.1, podcastlink stored raw URLs. Now it stores menu item IDs.
     * This migration attempts to match stored URLs against published site
     * menu items by comparing URL path segments against menu item routes.
     * Unmatched URLs are left as-is so the edit form can display a warning.
     *
     * @return void
     *
     * @since 10.1.0
     */
    private function migratePodcastLinkToMenuItem(): void
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            // Find podcasts with non-empty, non-numeric podcastlink (legacy URLs)
            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'podcastlink']))
                ->from($db->quoteName('#__bsms_podcast'))
                ->where($db->quoteName('podcastlink') . ' IS NOT NULL')
                ->where($db->quoteName('podcastlink') . ' != ' . $db->quote(''))
                ->where($db->quoteName('podcastlink') . ' NOT REGEXP ' . $db->quote('^[0-9]+$'));
            $db->setQuery($query);
            $podcasts = $db->loadObjectList();

            if (empty($podcasts)) {
                return;
            }

            // Load all published site menu items
            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'link', 'path', 'alias']))
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('published') . ' = 1')
                ->where($db->quoteName('client_id') . ' = 0');
            $db->setQuery($query);
            $menuItems = $db->loadObjectList();

            if (empty($menuItems)) {
                return;
            }

            // Build lookup maps for matching
            // 1. Full link match (index.php?option=com_proclaim&view=...)
            // 2. Path/alias match against URL path segments
            $byLink  = [];
            $byAlias = [];

            foreach ($menuItems as $mi) {
                $byLink[$mi->link] = $mi->id;

                // Extract last segment of menu path (the alias)
                $alias = $mi->alias ?: basename($mi->path ?? '');

                if ($alias !== '') {
                    // Store by alias — if duplicates, first wins
                    if (!isset($byAlias[$alias])) {
                        $byAlias[$alias] = $mi->id;
                    }
                }

                // Also index by full path for multi-level menus
                if (!empty($mi->path) && !isset($byAlias[$mi->path])) {
                    $byAlias[$mi->path] = $mi->id;
                }
            }

            $matched   = 0;
            $unmatched = 0;

            foreach ($podcasts as $podcast) {
                $url     = trim($podcast->podcastlink);
                $matchId = null;

                // Try direct link match (stored as index.php?option=...)
                if (isset($byLink[$url])) {
                    $matchId = $byLink[$url];
                }

                if ($matchId === null) {
                    // Extract path from URL
                    $parsed = parse_url($url);
                    $path   = trim($parsed['path'] ?? $url, '/');

                    // Try full path match
                    if ($path !== '' && isset($byAlias[$path])) {
                        $matchId = $byAlias[$path];
                    }

                    // Try last segment only (e.g. "https://example.com/sermons" → "sermons")
                    if ($matchId === null && $path !== '') {
                        $lastSegment = basename($path);

                        if ($lastSegment !== '' && isset($byAlias[$lastSegment])) {
                            $matchId = $byAlias[$lastSegment];
                        }
                    }
                }

                if ($matchId !== null) {
                    $update = $db->getQuery(true)
                        ->update($db->quoteName('#__bsms_podcast'))
                        ->set($db->quoteName('podcastlink') . ' = ' . $db->quote((string) $matchId))
                        ->where($db->quoteName('id') . ' = ' . (int) $podcast->id);
                    $db->setQuery($update);
                    $db->execute();
                    $matched++;
                } else {
                    $unmatched++;
                }
            }

            if ($matched > 0) {
                Factory::getApplication()->enqueueMessage(
                    $matched . ' podcast link(s) matched to menu items.',
                    'message'
                );
            }

            if ($unmatched > 0) {
                Factory::getApplication()->enqueueMessage(
                    $unmatched . ' podcast link(s) could not be matched to a menu item. '
                    . 'Please edit those podcasts and select the correct sermon page menu item.',
                    'warning'
                );
            }
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage(
                'Podcast link migration notice: ' . $e->getMessage(),
                'warning'
            );
        }
    }

    /**
     * Migrate scripture settings from #__bsms_admin.params to plg_content_scripturelinks params.
     *
     * Copies provider_getbible, gdpr_mode, provider_api_bible, api_bible_api_key,
     * scripture_cache_days, and default_bible_version from the component admin row
     * to the plugin's #__extensions.params, then removes them from the component.
     *
     * Idempotent — skips if plugin row doesn't exist or keys aren't present.
     *
     * @return  void
     *
     * @since  10.3.0
     */
    private function migrateScriptureParamsToPlugin(): void
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            // Load component admin params
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_admin'))
                ->where($db->quoteName('id') . ' = 1');
            $db->setQuery($query);
            $adminJson = $db->loadResult();

            if (empty($adminJson)) {
                return;
            }

            $adminParams = new \Joomla\Registry\Registry($adminJson);

            // Check if any scripture keys exist in component params
            $keyMap = [
                'provider_getbible'    => 'provider_getbible',
                'gdpr_mode'            => 'gdpr_mode',
                'provider_api_bible'   => 'provider_api_bible',
                'api_bible_api_key'    => 'api_bible_api_key',
                'scripture_cache_days' => 'cache_days',
                'default_bible_version' => 'default_version',
            ];

            $hasAny = false;

            foreach (array_keys($keyMap) as $compKey) {
                if ($adminParams->get($compKey) !== null) {
                    $hasAny = true;
                    break;
                }
            }

            if (!$hasAny) {
                return;
            }

            // Load plugin params
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('content'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('scripturelinks'));
            $db->setQuery($query);
            $pluginJson = $db->loadResult();

            if ($pluginJson === null) {
                // Plugin not installed yet — migration will happen when it is
                return;
            }

            $pluginParams = new \Joomla\Registry\Registry($pluginJson);

            // Copy values from component to plugin.
            // gdpr_mode is shared — copy to plugin but keep in component params
            // (Proclaim uses it for analytics/privacy beyond just scripture).
            foreach ($keyMap as $compKey => $pluginKey) {
                $value = $adminParams->get($compKey);

                if ($value !== null) {
                    $pluginParams->set($pluginKey, $value);

                    if ($compKey !== 'gdpr_mode') {
                        $adminParams->remove($compKey);
                    }
                }
            }

            // Save plugin params
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('params') . ' = ' . $db->quote($pluginParams->toString()))
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('content'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('scripturelinks'));
            $db->setQuery($query);
            $db->execute();

            // Save cleaned component params
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_admin'))
                ->set($db->quoteName('params') . ' = ' . $db->quote($adminParams->toString()))
                ->where($db->quoteName('id') . ' = 1');
            $db->setQuery($query);
            $db->execute();

            Factory::getApplication()->enqueueMessage(
                'Scripture settings migrated to ScriptureLinks plugin.',
                'message'
            );
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage(
                'Scripture settings migration notice: ' . $e->getMessage(),
                'warning'
            );
        }
    }
}
