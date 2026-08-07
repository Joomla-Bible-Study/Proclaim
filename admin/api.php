<?php

/**
 * Core Admin Proclaim API file
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

use CWM\Component\Proclaim\Administrator\Helper\CwmlogHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use Joomla\CMS\Factory;

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
// A URL parameter is not authorisation. `?jbsmdbg=1` previously switched
// JBSMDEBUG on for *any* client, so an anonymous site visitor could enable the
// component's debug buffer -- and CwmsermonsController::filterAjax() ships that
// buffer to the browser as `_debug`, handing out raw SQL for the sermon listing
// query. Honour the parameter only for a user who actually holds core.admin.
//
// Checked on both clients rather than restricted to the administrator client:
// Cwmdownload logs through CwmDebug on the front end, so an admin debugging a
// site-side download still needs ?jbsmdbg=1 to work there. Anything that fails
// to resolve an identity falls through to "not allowed". See #1569.
$jbsmDebugRequested = $app->getInput()->getInt('jbsmdbg', 0) === 1;
$jbsmDebugAllowed   = false;

if ($jbsmDebugRequested) {
    try {
        $jbsmDebugUser    = $app->getIdentity();
        $jbsmDebugAllowed = $jbsmDebugUser && $jbsmDebugUser->authorise('core.admin', 'com_proclaim');
    } catch (\Throwable $e) {
        $jbsmDebugAllowed = false;
    }
}

if ($app->isClient('administrator') || $jbsmDebugAllowed) {
    try {
        if (CwmproclaimHelper::debug() === 1 || $jbsmDebugAllowed) {
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

// File system paths — defined in ProclaimComponent::boot(), which runs in every
// application. This call is for the paths that reach this file without booting
// the component, such as the postinstall messages. It is guarded/idempotent.
CwmproclaimHelper::defineLegacyPathConstants();

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

// Log categories are registered by CwmlogHelper, not here.
//
// This file only runs in the administrator and site applications — it needs a
// document for the web asset manager, which the API application does not have.
// Registering loggers here therefore left API code with no logger at all, and
// silently discarded everything it logged.
//
// Registration now lives in CwmlogHelper (called from ProclaimComponent::boot(),
// so every application is covered). The call below is for the paths that reach
// this file without booting the component, such as the postinstall messages.
// It is idempotent: registering a category twice would write every entry twice.
CwmlogHelper::register();

// CWM has been initialized
const CWM_LOADED = 1;
// phpcs:enable PSR1.Files.SideEffects
