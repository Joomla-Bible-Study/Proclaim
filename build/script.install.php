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
        return true;
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
