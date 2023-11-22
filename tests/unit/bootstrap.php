<?php

/**
 * Prepares a minimalist framework for unit testing.
 *
 * Joomla is assumed to include the /unittest/ directory.
 * eg, /path/to/joomla/unittest/
 *
 * @package    Proclaim.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 * @link       http://www.phpunit.de/manual/current/en/installation.html
 */

define('_JEXEC', 1);


// Fix magic quotes.
ini_set('magic_quotes_runtime', 0);

// Maximise error reporting.
ini_set('zend.ze1_compatibility_mode', '0');
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', 1);

/*.joomla-dev/libraries
 * Ensure that required path constants are defined.  These can be overridden within the phpunit.xml file
 * if you chose to create a custom version of that file.
 */
if (!defined('JPATH_TESTS')) {
    define('JPATH_TESTS', realpath(__DIR__));
}
if (!defined('JPATH_PLATFORM')) {
    define('JPATH_PLATFORM', realpath(dirname(dirname(dirname(__FILE__))) . '/.joomla-dev/libraries'));
}
if (!defined('JPATH_LIBRARIES')) {
    define('JPATH_LIBRARIES', realpath(dirname(dirname(dirname(__FILE__))) . '/.joomla-dev/libraries'));
}
if (!defined('JPATH_BASE')) {
    define('JPATH_BASE', realpath(dirname(dirname(dirname(__FILE__))) . '/.joomla-dev'));
}
if (!defined('JPATH_ROOT')) {
    define('JPATH_ROOT', realpath(JPATH_BASE));
}
if (!defined('JPATH_CACHE')) {
    define('JPATH_CACHE', JPATH_BASE . '/cache');
}
if (!defined('JPATH_CONFIGURATION')) {
    define('JPATH_CONFIGURATION', JPATH_BASE);
}
if (!defined('JPATH_SITE')) {
    define('JPATH_SITE', JPATH_ROOT);
}
if (!defined('JPATH_ADMINISTRATOR')) {
    define('JPATH_ADMINISTRATOR', JPATH_ROOT . '/administrator');
}
if (!defined('JPATH_INSTALLATION')) {
    define('JPATH_INSTALLATION', JPATH_ROOT . '/installation');
}
if (!defined('JPATH_MANIFESTS')) {
    define('JPATH_MANIFESTS', JPATH_ADMINISTRATOR . '/manifests');
}
if (!defined('JPATH_PLUGINS')) {
    define('JPATH_PLUGINS', JPATH_BASE . '/plugins');
}
if (!defined('JPATH_THEMES')) {
    define('JPATH_THEMES', JPATH_BASE . '/templates');
}
if (!defined('JDEBUG')) {
    define('JDEBUG', false);
}

// Import the platform in legacy mode.
require_once JPATH_PLATFORM . '/import.legacy.php';


// Force library to be in JError legacy mode
Error::setErrorHandling(E_NOTICE, 'message');
Error::setErrorHandling(E_WARNING, 'message');

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Register the core Joomla test classes.
Loader::registerPrefix('Test', __DIR__ . '/core');
