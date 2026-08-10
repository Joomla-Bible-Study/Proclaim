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

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerHelper;
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

        // Also before the children: com_proclaim's own preflight refuses to
        // install when the scripture library is absent.
        //
        // Install paths only. Joomla calls preflight with $type === 'uninstall'
        // too, and without this guard a package REMOVAL reinstalled the
        // scripture stack on its way out — which re-registered com_proclaim as
        // a consumer immediately after com_proclaim's own uninstall had
        // unregistered it, leaving a row describing an extension that was being
        // removed in the same request.
        if ($type !== 'uninstall' && !$this->installScriptureStack($adapter)) {
            return false;
        }

        return true;
    }

    /**
     * Install the bundled scripture stack, which is not a package child.
     *
     * pkg_cwmscripture ships inside this archive but `pkg_proclaim.xml` does not
     * declare it — see the comment there. A package uninstalls exactly what its
     * `<files>` list names, so anything declared would be removed along with
     * Proclaim, and the scripture stack is shared with ScriptureLinks and Living
     * Word. Installing it here instead of declaring it is what lets Proclaim
     * bundle it without owning its removal (#1675).
     *
     * Runs in preflight rather than postflight because ordering matters:
     * com_proclaim's own preflight aborts when `CWM\Library\Scripture` cannot be
     * resolved, and as a package child the library used to be installed before
     * it. Postflight would be too late on a fresh install.
     *
     * Skips when an equal or newer version is already present, so a site running
     * the standalone pkg_cwmscripture is never downgraded by installing Proclaim.
     *
     * @param   InstallerAdapter  $adapter  The installer adapter
     *
     * @return  bool  False only when the stack is genuinely required and could not be installed
     *
     * @since   __DEPLOY_VERSION__
     */
    private function installScriptureStack(InstallerAdapter $adapter): bool
    {
        $zip = $adapter->getParent()->getPath('source') . '/packages/pkg_cwmscripture.zip';

        if (!is_file($zip)) {
            // Nothing bundled. If the stack is already installed this is fine —
            // a site can legitimately carry it from the standalone package.
            if ($this->scriptureLibraryPresent()) {
                return true;
            }

            Factory::getApplication()->enqueueMessage(
                'The scripture package is missing from this Proclaim archive and no scripture '
                . 'library is installed. Install pkg_cwmscripture first, then retry.',
                'error'
            );

            return false;
        }

        try {
            $bundled   = $this->manifestVersionInZip($zip);
            $installed = $this->installedVersion('pkg_cwmscripture', 'package');

            if ($installed !== null && $bundled !== null && version_compare($installed, $bundled, '>=')) {
                Log::add(
                    "pkg_proclaim: pkg_cwmscripture {$installed} already installed (bundled {$bundled}) — leaving it alone.",
                    Log::INFO,
                    'com_proclaim'
                );

                return true;
            }

            $package = InstallerHelper::unpack($zip, true);

            if ($package === false) {
                throw new \RuntimeException('could not unpack pkg_cwmscripture.zip');
            }

            $installer = new Installer();
            $installer->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));
            $result = $installer->install($package['dir']);

            InstallerHelper::cleanupInstall($zip, $package['dir']);

            if (!$result) {
                throw new \RuntimeException('the installer reported a failure');
            }

            Log::add('pkg_proclaim: installed the bundled scripture stack.', Log::INFO, 'com_proclaim');

            return true;
        } catch (\Throwable $e) {
            // Only fatal when Proclaim genuinely cannot run: if a usable library
            // is already there, a failed refresh is not worth blocking on.
            if ($this->scriptureLibraryPresent()) {
                Log::add(
                    'pkg_proclaim: could not refresh the scripture stack (' . $e->getMessage()
                    . ') — continuing with the installed one.',
                    Log::WARNING,
                    'com_proclaim'
                );

                return true;
            }

            Factory::getApplication()->enqueueMessage(
                'Proclaim could not install the scripture library it requires: ' . $e->getMessage(),
                'error'
            );

            return false;
        }
    }

    /**
     * Is a usable scripture library on disk?
     *
     * Checked by file rather than by `#__extensions`, because a row without
     * files is exactly the broken state this needs to detect.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    private function scriptureLibraryPresent(): bool
    {
        return is_file(JPATH_LIBRARIES . '/cwmscripture/src/Helper/ScriptureHelper.php');
    }

    /**
     * Version of an installed extension, or null when it is not installed.
     *
     * @param   string  $element  Extension element
     * @param   string  $type     Extension type
     *
     * @return  string|null
     *
     * @since   __DEPLOY_VERSION__
     */
    private function installedVersion(string $element, string $type): ?string
    {
        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->select($db->quoteName('manifest_cache'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = :element')
                ->where($db->quoteName('type') . ' = :type')
                ->bind(':element', $element)
                ->bind(':type', $type);

            $cache = $db->setQuery($query)->loadResult();

            if (!$cache) {
                return null;
            }

            $decoded = json_decode((string) $cache, true, 512, JSON_THROW_ON_ERROR);

            return $decoded['version'] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Version declared by the manifest inside a package zip.
     *
     * @param   string  $zip  Absolute path to the zip
     *
     * @return  string|null
     *
     * @since   __DEPLOY_VERSION__
     */
    private function manifestVersionInZip(string $zip): ?string
    {
        $archive = new \ZipArchive();

        if ($archive->open($zip) !== true) {
            return null;
        }

        $version = null;

        for ($i = 0; $i < $archive->numFiles; $i++) {
            $name = $archive->getNameIndex($i);

            // The package manifest sits at the archive root.
            if ($name === null || substr_count($name, '/') > 0 || !str_ends_with($name, '.xml')) {
                continue;
            }

            $xml = simplexml_load_string((string) $archive->getFromIndex($i));

            if ($xml !== false && isset($xml->version)) {
                $version = (string) $xml->version;

                break;
            }
        }

        $archive->close();

        return $version;
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

        // Retire update sites left registered against com_proclaim
        $this->removeComponentUpdateSites();

        return true;
    }

    /**
     * Retire update sites still registered against com_proclaim.
     *
     * Until 10.4.0 the component manifest carried its own <updateservers>, so
     * every site installed before then holds an update site owned by
     * com_proclaim. Joomla does not remove an update site when a manifest stops
     * declaring one, so those rows outlive the change: a site with history ends
     * up polling the stream twice and reporting Proclaim as a *component*
     * update, while a fresh install polls once as a package.
     *
     * Some of those rows point at ARS stream id=2, which only ever served
     * 9.2.x. A site carrying one polls a stream whose newest entry predates
     * every 10.x release, so it can never be offered anything from it.
     *
     * Updates belong to the package in the bundled-package distribution model,
     * so the package's own update site is the one to keep.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function removeComponentUpdateSites(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        try {
            $sitesFor = function (string $element, string $type) use ($db): array {
                $query = $db->createQuery()
                    ->select($db->quoteName('se.update_site_id'))
                    ->from($db->quoteName('#__update_sites_extensions', 'se'))
                    ->join(
                        'INNER',
                        $db->quoteName('#__extensions', 'e'),
                        $db->quoteName('e.extension_id') . ' = ' . $db->quoteName('se.extension_id')
                    )
                    ->where($db->quoteName('e.element') . ' = :element')
                    ->where($db->quoteName('e.type') . ' = :type')
                    ->bind(':element', $element)
                    ->bind(':type', $type);

                return array_map('intval', $db->setQuery($query)->loadColumn() ?: []);
            };

            $packageSites = $sitesFor('pkg_proclaim', 'package');

            // Never leave a site with no way to hear about updates. If the
            // package has not registered its own update site — a partial
            // install, or a component-only site that has not taken the package
            // yet — the component's row is the only channel there is, so it
            // stays. The next successful package install runs this again.
            if ($packageSites === []) {
                return;
            }

            $stale = array_diff($sitesFor('com_proclaim', 'component'), $packageSites);

            if ($stale === []) {
                return;
            }

            $ids = implode(',', $stale);

            // #__updates rows are keyed to the site and would otherwise be
            // orphaned, leaving phantom entries on Extensions > Update.
            foreach (['#__updates', '#__update_sites_extensions', '#__update_sites'] as $table) {
                $db->setQuery(
                    $db->createQuery()
                        ->delete($db->quoteName($table))
                        ->where($db->quoteName('update_site_id') . ' IN (' . $ids . ')')
                )->execute();
            }

            Log::add(
                'pkg_proclaim: retired ' . \count($stale) . ' com_proclaim update site(s); '
                . 'the package now owns updates.',
                Log::INFO,
                'com_proclaim'
            );
        } catch (\Throwable $e) {
            // Housekeeping. A site that keeps a duplicate update site still
            // updates correctly, so this must never fail an install.
            Log::add(
                'pkg_proclaim: could not retire com_proclaim update sites: ' . $e->getMessage(),
                Log::WARNING,
                'com_proclaim'
            );
        }
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
        $this->reportRetainedScriptureStack();

        return true;
    }

    /**
     * Tell the administrator what Proclaim deliberately left installed.
     *
     * Proclaim no longer removes anything scripture-related. pkg_cwmscripture is
     * not a child of this package (see build/pkg_proclaim.xml), so uninstalling
     * Proclaim cannot reach it — which is the point: ScriptureLinks and Living
     * Word share that stack, and the downloaded Bible translations in
     * #__bsms_bible_verses can run to hundreds of megabytes that nobody else
     * would ever put back.
     *
     * The cost of that choice is orphans on a site where Proclaim was the only
     * consumer, so say so plainly rather than leaving them to be discovered.
     * Removing them is then a deliberate act, which is what was asked for
     * (#1675).
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function reportRetainedScriptureStack(): void
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $query = $db->createQuery()
                ->select($db->quoteName(['name', 'type', 'element']))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' IN (' . implode(',', $db->quote([
                    'cwmscripture',
                    'scripturelinks',
                    'pkg_cwmscripture',
                ])) . ')');

            $retained = $db->setQuery($query)->loadObjectList() ?: [];

            if ($retained === []) {
                return;
            }

            $names = [];

            foreach ($retained as $row) {
                $names[] = $row->name ?: $row->element;
            }

            Factory::getApplication()->enqueueMessage(
                'Proclaim has been removed. The scripture extensions it bundles were left installed '
                . 'because other extensions may be using them: ' . implode(', ', array_unique($names)) . '. '
                . 'Your downloaded Bible translations are untouched. To remove them as well, uninstall '
                . 'the CWM Scripture package from Extensions - Manage.',
                'notice'
            );
        } catch (\Throwable $e) {
            // A notice is not worth failing an uninstall over.
            Log::add('pkg_proclaim: could not report retained scripture extensions: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
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
