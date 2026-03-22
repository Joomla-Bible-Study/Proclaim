<?php

/**
 * Core Admin Proclaim API file
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

try {
    $app = Factory::getApplication();
} catch (Exception $e) {
    throw new \RuntimeException('Could not load Factory');
}

// Component debugging — defer DB query to first use on the front end.
// On admin pages the debug check runs immediately; on the site side we
// default to off and let Cwmdownload (the only front-end consumer) check
// on demand, avoiding a DB query on every module/page load.
if ($app->isClient('administrator') || $app->getInput()->getInt('jbsmdbg', 0) === 1) {
    try {
        if (CwmproclaimHelper::debug() === 1 || $app->getInput()->getInt('jbsmdbg', 0) === 1) {
            \define('JBSMDEBUG', 1);
        } else {
            \define('JBSMDEBUG', 0);
        }
    } catch (\RuntimeException $e) {
        \define('JBSMDEBUG', 0);
    }
} else {
    \define('JBSMDEBUG', 0);
}

// Version information — defer XML parsing to admin only (the only consumer
// is CwminstallModel). On the front end, set a placeholder to avoid the
// cost of file_get_contents + simplexml on every page load.
if ($app->isClient('administrator')) {
    $manifestFile    = JPATH_ADMINISTRATOR . '/components/com_proclaim/proclaim.xml';
    $manifestVersion = '0.0.0';

    if (is_file($manifestFile) && is_readable($manifestFile)) {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string(file_get_contents($manifestFile));

        if ($xml instanceof \SimpleXMLElement && isset($xml->version)) {
            $manifestVersion = trim((string) $xml->version);
        } else {
            foreach (libxml_get_errors() as $error) {
                Log::add('XML Error in proclaim.xml: ' . trim($error->message), Log::WARNING, 'com_proclaim');
            }
        }

        libxml_clear_errors();
    }

    \define('BIBLESTUDY_VERSION', $manifestVersion);
} else {
    \define('BIBLESTUDY_VERSION', '');
}

// Default values
const BIBLESTUDY_COMPONENT_NAME = 'com_proclaim';

// File system paths
const BIBLESTUDY_COMPONENT_RELPATH = 'components' . DIRECTORY_SEPARATOR . BIBLESTUDY_COMPONENT_NAME;

// Root system paths
const BIBLESTUDY_ROOT_PATH       = JPATH_ROOT;
const BIBLESTUDY_ROOT_PATH_ADMIN = JPATH_ADMINISTRATOR;
const BIBLESTUDY_MEDIA_PATH      = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_proclaim';

// Admin Component paths
const BIBLESTUDY_PATH_ADMIN         = BIBLESTUDY_ROOT_PATH_ADMIN . DIRECTORY_SEPARATOR . BIBLESTUDY_COMPONENT_RELPATH;

// If a phrase is not found in a specific language file, load the English language file:
$language = $app->getLanguage();

$language->load('com_proclaim', BIBLESTUDY_PATH_ADMIN, 'en-GB', true);
$language->load('com_proclaim', BIBLESTUDY_PATH_ADMIN, null, true);

$modProclaimPath = JPATH_ROOT . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'mod_proclaim';
if (is_dir($modProclaimPath)) {
    $language->load('mod_proclaim', $modProclaimPath, 'en-GB', true);
    $language->load('mod_proclaim', $modProclaimPath, null, true);
}

// Register web asset registries so assets are available when views/modules request them.
// Actual useStyle/useScript calls are made by individual views and module dispatchers.
$wa = $app->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_proclaim');

// Admin pages load core assets globally; front-end views load them on demand
// to avoid render-blocking CSS/JS on pages that don't need them.
if ($app->isClient('administrator')) {
    $wa->useStyle('com_proclaim.cwmcore')
        ->useScript('com_proclaim.cwmcorejs')
        ->useScript('com_proclaim.cwmcorejs-admin');
}

// Register lib_cwmscripture web assets (libraries aren't auto-discovered by Joomla)
$wa->getRegistry()->addExtensionRegistryFile('lib_cwmscripture');

// Include the JLog class.
Log::addLogger(
    [
        'text_file' => 'com_proclaim.errors.php',
    ],
    Log::ALL,
    'com_proclaim'
);

// CWM has been initialized
const CWM_LOADED = 1;
// phpcs:enable PSR1.Files.SideEffects
