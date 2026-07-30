<?php

/**
 * Package installer script for pkg_proclaim.
 *
 * Handles pre-install checks and post-install tasks when the package
 * is installed, updated, or uninstalled via Joomla's Extension Manager.
 *
 * @package    Proclaim
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.3.0
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Library\Scripture\Installer\ConsumerRegistry;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

/**
 * Returns an anonymous class implementing InstallerScriptInterface.
 *
 * Joomla 5+ expects the script file to return an InstallerScriptInterface
 * instance directly (not define a named class).
 *
 * @since  10.3.0
 */
return new class () implements InstallerScriptInterface {
    /**
     * Minimum PHP version required.
     *
     * @var string
     * @since 10.3.0
     */
    private string $minimumPhp = '8.3.0';

    /**
     * Minimum Joomla version required.
     *
     * @var string
     * @since 10.3.0
     */
    private string $minimumJoomla = '5.1.0';

    /**
     * Runs before install/update to check requirements.
     *
     * @param   string            $type     Install type (install, update, discover_install)
     * @param   InstallerAdapter  $adapter  The installer adapter
     *
     * @return  bool  True to continue, false to abort
     *
     * @since  10.3.0
     */
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        if (version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
            Factory::getApplication()->enqueueMessage(
                \sprintf(
                    'CWM Proclaim requires PHP %s or later. You are running PHP %s.',
                    $this->minimumPhp,
                    PHP_VERSION
                ),
                'error'
            );

            return false;
        }

        if (version_compare(JVERSION, $this->minimumJoomla, '<')) {
            Factory::getApplication()->enqueueMessage(
                \sprintf(
                    'CWM Proclaim requires Joomla %s or later. You are running Joomla %s.',
                    $this->minimumJoomla,
                    JVERSION
                ),
                'error'
            );

            return false;
        }

        // Must happen before the child extensions install — see the method docblock.
        $this->disarmLegacyScriptureUninstallSql();

        return true;
    }

    /**
     * Runs after install/update completes.
     *
     * @param   string            $type     Install type (install, update, discover_install)
     * @param   InstallerAdapter  $adapter  The installer adapter
     *
     * @return  bool
     *
     * @since  10.3.0
     */
    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        // Rebuild the namespace map so Joomla discovers the library's classes
        $cacheFile = JPATH_ADMINISTRATOR . '/cache/autoload_psr4.php';

        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }

        // Ensure the scripture links plugin is enabled
        $this->enablePlugin('scripturelinks', 'content');

        return true;
    }

    /**
     * Runs on install.
     *
     * @param   InstallerAdapter  $adapter  The installer adapter
     *
     * @return  bool
     *
     * @since  10.3.0
     */
    public function install(InstallerAdapter $adapter): bool
    {
        return true;
    }

    /**
     * Runs on update.
     *
     * @param   InstallerAdapter  $adapter  The installer adapter
     *
     * @return  bool
     *
     * @since  10.3.0
     */
    public function update(InstallerAdapter $adapter): bool
    {
        return true;
    }

    /**
     * Runs on uninstall.
     *
     * @param   InstallerAdapter  $adapter  The installer adapter
     *
     * @return  bool
     *
     * @since  10.3.0
     */
    public function uninstall(InstallerAdapter $adapter): bool
    {
        $this->dropScriptureTablesIfLastConsumer();

        return true;
    }

    /**
     * Remove the shared scripture tables when this package is the last consumer.
     *
     * The library deliberately will not do this itself during a package removal:
     * PackageAdapter::removeExtensionFiles() uninstalls each child with
     * setPackageUninstall(true), and lib_cwmscripture treats that flag as "an
     * upgrade cycle may be in progress, keep the data". Correct for the library,
     * but it means nothing cleans up when the whole package genuinely goes away.
     *
     * Doing it here is safe because PackageAdapter does NOT uninstall-then-install
     * on update — checkExtensionInFilesystem() only sets the route — so a package
     * manifest script's uninstall() runs on genuine removal and never on upgrade.
     * It also runs before removeExtensionFiles(), so the children are still
     * present and the drop happens exactly once.
     *
     * The "is anything else using it" question is delegated to the library's
     * ConsumerRegistry rather than answered with a hardcoded list. Joomla tracks
     * no library dependencies, so a third-party extension is invisible unless it
     * registered itself; asking the registry means such a consumer is seen and
     * its data left alone, instead of being silently dropped.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function dropScriptureTablesIfLastConsumer(): void
    {
        try {
            $registry = JPATH_LIBRARIES . '/cwmscripture/src/Installer/ConsumerRegistry.php';

            if (!class_exists(ConsumerRegistry::class) && is_file($registry)) {
                require_once $registry;
            }

            if (!class_exists(ConsumerRegistry::class)) {
                Log::add(
                    'pkg_proclaim: ConsumerRegistry unavailable — keeping the scripture tables.',
                    Log::WARNING,
                    'jerror'
                );

                return;
            }

            // Everything this package removes; anything left is someone else's.
            $remaining = ConsumerRegistry::installedExcluding([
                ['element' => 'com_proclaim',   'type' => 'component', 'folder' => ''],
                ['element' => 'scripturelinks', 'type' => 'plugin',    'folder' => 'content'],
            ]);

            if ($remaining !== []) {
                Log::add(
                    'pkg_proclaim: scripture tables still used by ' . implode(', ', $remaining) . ' — keeping them.',
                    Log::INFO,
                    'jerror'
                );

                return;
            }

            $db = Factory::getContainer()->get(DatabaseInterface::class);

            foreach (['#__bsms_scripture_consumers', '#__bsms_scripture_cache', '#__bsms_bible_verses', '#__bsms_bible_translations'] as $table) {
                $db->setQuery('DROP TABLE IF EXISTS ' . $db->quoteName($table));
                $db->execute();
            }

            Log::add(
                'pkg_proclaim: removed the scripture tables (last consumer uninstalled).',
                Log::INFO,
                'jerror'
            );
        } catch (\Throwable $e) {
            Log::add(
                'pkg_proclaim: scripture table cleanup skipped — ' . $e->getMessage(),
                Log::WARNING,
                'jerror'
            );
        }
    }

    /**
     * Stop the installed scripture library from dropping its own tables.
     *
     * lib_cwmscripture up to 1.1.4 shipped an <uninstall><sql> block pointing at
     * DROP TABLE statements. Joomla's LibraryAdapter uninstalls the installed
     * library before writing the new one (checkExtensionInFilesystem() calls
     * uninstall()), so that SQL ran on every UPDATE — taking #__bsms_bible_verses
     * and #__bsms_bible_translations with it. Every locally downloaded
     * translation disappeared and the Local Translations panel came back empty.
     *
     * The library no longer declares uninstall SQL, but that only helps the
     * upgrade *after* this one: during this install Joomla reads the manifest and
     * the SQL file already sitting in JPATH_LIBRARIES — the old, destructive
     * ones. Installer::parseSQLFiles() resolves the file against the installed
     * extension root, so blanking it here, before any child extension installs,
     * is what actually saves the data.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function disarmLegacyScriptureUninstallSql(): void
    {
        $sqlFile = JPATH_LIBRARIES . '/cwmscripture/sql/uninstall.mysql.utf8.sql';

        if (!is_file($sqlFile)) {
            return;
        }

        $buffer = @file_get_contents($sqlFile);

        if ($buffer === false || stripos($buffer, 'DROP TABLE') === false) {
            return;
        }

        $replacement = "--\n"
            . "-- Neutralised by the pkg_proclaim installer.\n"
            . "--\n"
            . "-- Joomla runs a library's uninstall SQL on every update, so the DROP TABLE\n"
            . "-- statements this file used to hold wiped every downloaded Bible translation\n"
            . "-- each time lib_cwmscripture was upgraded. Table removal now lives in the\n"
            . "-- library's script.php, which can tell an upgrade from a real uninstall.\n"
            . "--\n";

        if (@file_put_contents($sqlFile, $replacement) === false) {
            Factory::getApplication()->enqueueMessage(
                'Proclaim could not rewrite ' . $sqlFile . '. Joomla may drop your locally '
                . 'downloaded Bible translations during this update; re-download them from '
                . 'Proclaim → Admin Center → Scripture if the Local Translations list comes '
                . 'back empty.',
                'warning'
            );

            Log::add(
                'pkg_proclaim: could not disarm legacy scripture uninstall SQL at ' . $sqlFile,
                Log::WARNING,
                'com_proclaim'
            );

            return;
        }

        Log::add(
            'pkg_proclaim: disarmed legacy scripture uninstall SQL (bible tables preserved).',
            Log::INFO,
            'com_proclaim'
        );
    }

    /**
     * Enable a plugin by element and group.
     *
     * @param   string  $element  Plugin element name
     * @param   string  $group    Plugin group (e.g. 'content', 'system')
     *
     * @return  void
     *
     * @since  10.3.0
     */
    private function enablePlugin(string $element, string $group): void
    {
        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('element') . ' = ' . $db->quote($element))
                ->where($db->quoteName('folder') . ' = ' . $db->quote($group));

            $db->setQuery($query);
            $db->execute();
        } catch (\Exception $e) {
            Log::add(
                'pkg_proclaim: Could not enable plugin ' . $element . ': ' . $e->getMessage(),
                Log::WARNING,
                'com_proclaim'
            );
        }
    }
};
