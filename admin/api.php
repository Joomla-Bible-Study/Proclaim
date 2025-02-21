<?php

/**
 * Core Admin BibleStudy file
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
try {
    $app = Factory::getApplication();
} catch (Exception $e) {
    return;
}

// Component debugging
try {
    if (CwmproclaimHelper::debug() === 1 || $app->input->getInt('jbsmdbg', '0') === 1) {
        \define('JBSMDEBUG', 1);
    } else {
        \define('JBSMDEBUG', 0);
    }
} catch (\RuntimeException $e) {
    throw new \RuntimeException("Could not find Debug setting.");
}
// phpcs:enable PSR1.Files.SideEffects

// Version information
const BIBLESTUDY_VERSION = '10.0.0-beta1';
const BIBLESTUDY_VERSION_UPDATEFILE = 'JBS Version ' . BIBLESTUDY_VERSION;

// Default values
const BIBLESTUDY_COMPONENT_NAME = 'com_proclaim';

// File system paths
const BIBLESTUDY_COMPONENT_RELPATH = 'components' . DIRECTORY_SEPARATOR . BIBLESTUDY_COMPONENT_NAME;

// Root system paths
const BIBLESTUDY_ROOT_PATH = JPATH_ROOT;
const BIBLESTUDY_ROOT_PATH_ADMIN = JPATH_ADMINISTRATOR;
const BIBLESTUDY_MEDIA_PATH = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_proclaim';

// Admin Component paths
const BIBLESTUDY_PATH_ADMIN = BIBLESTUDY_ROOT_PATH_ADMIN . DIRECTORY_SEPARATOR . BIBLESTUDY_COMPONENT_RELPATH;
const BIBLESTUDY_PATH_ADMIN_HELPERS = BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'helpers';

HTMLHelper::addIncludePath(BIBLESTUDY_PATH_ADMIN_HELPERS . '/html');

// If a phrase is not found in a specific language file, load english language file:
$language = $app->getLanguage();
$language->load('com_proclaim', BIBLESTUDY_PATH_ADMIN, 'en-GB', true);
$language->load('com_proclaim', BIBLESTUDY_PATH_ADMIN, null, true);

if (is_dir(JPATH_ROOT . 'modules/mod_proclaim/')) {
    $language->load('mod_proclaim', JPATH_ROOT . '/modules/mod_proclaim/', 'en-GB', true);
    $language->load('moc_proclaim', JPATH_ROOT . '/modules/mod_proclaim/', null, true);
}

// Add to api to load the core CSS and JS for the component to work.
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();

// We register the extension registry because in  modules and plugins the registry is not automatically loaded
$wa->getRegistry()->addExtensionRegistryFile('com_proclaim');
$wa->useStyle('com_proclaim.cwmcore')
    ->useScript('com_proclaim.cwmcorejs');

// Include the JLog class.
Log::addLogger(
    array(
        'text_file' => 'com_proclaim.errors.php'
    ),
    Log::ALL,
    'com_proclaim'
);

// CWM has been initialized
const CWM_LOADED = 1;
