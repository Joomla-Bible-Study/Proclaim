<?php

/**
 * PHPUnit Bootstrap for Proclaim Component Unit Tests
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// Define _JEXEC to prevent "restricted access" errors
\define('_JEXEC', 1);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define Joomla path constants for testing
if (!\defined('JPATH_BASE')) {
    \define('JPATH_BASE', \dirname(__DIR__, 2));
}

if (!\defined('JPATH_ROOT')) {
    \define('JPATH_ROOT', JPATH_BASE);
}

if (!\defined('JPATH_SITE')) {
    \define('JPATH_SITE', JPATH_ROOT);
}

if (!\defined('JPATH_ADMINISTRATOR')) {
    \define('JPATH_ADMINISTRATOR', JPATH_ROOT . '/administrator');
}

if (!\defined('BIBLESTUDY_PATH_ADMIN')) {
    \define('BIBLESTUDY_PATH_ADMIN', JPATH_ROOT . '/admin');
}

if (!\defined('JPATH_TESTS')) {
    \define('JPATH_TESTS', \dirname(__DIR__));
}

if (!\defined('JPATH_LIBRARIES')) {
    \define('JPATH_LIBRARIES', JPATH_ROOT . '/libraries');
}

if (!\defined('JPATH_CACHE')) {
    \define('JPATH_CACHE', JPATH_BASE . '/cache');
}

if (!\defined('JPATH_CONFIGURATION')) {
    \define('JPATH_CONFIGURATION', JPATH_BASE);
}

if (!\defined('JPATH_PLUGINS')) {
    \define('JPATH_PLUGINS', JPATH_BASE . '/plugins');
}

if (!\defined('JPATH_THEMES')) {
    \define('JPATH_THEMES', JPATH_BASE . '/templates');
}

if (!\defined('JDEBUG')) {
    \define('JDEBUG', false);
}

// Load Joomla CMS stubs BEFORE Composer autoloader so they are available
// when our source classes (which extend Joomla base classes) are loaded.
// These stubs provide minimal class/interface/trait definitions that satisfy
// PHP's autoloader without requiring the full Joomla CMS framework.
require_once __DIR__ . '/Stubs/JoomlaCmsStubs.php';

// Load Composer autoloader
$composerAutoload = JPATH_ROOT . '/libraries/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Register PSR-4 autoloader for the Proclaim component
spl_autoload_register(function ($class) {
    $prefix  = 'CWM\\Component\\Proclaim\\Administrator\\';
    $baseDir = JPATH_ROOT . '/admin/src/';

    $len = \strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relativeClass = substr($class, $len);
        $file          = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    $prefix  = 'CWM\\Component\\Proclaim\\Site\\';
    $baseDir = JPATH_ROOT . '/site/src/';

    $len = \strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relativeClass = substr($class, $len);
        $file          = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    // Autoload test classes
    $prefix  = 'CWM\\Component\\Proclaim\\Tests\\';
    $baseDir = JPATH_ROOT . '/tests/unit/';

    $len = \strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relativeClass = substr($class, $len);
        $file          = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    // Autoload integration test classes
    $prefix  = 'CWM\\Component\\Proclaim\\Tests\\Integration\\';
    $baseDir = JPATH_ROOT . '/tests/integration/';

    $len = \strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relativeClass = substr($class, $len);
        $file          = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    return false;
});

// Load the base test case
require_once __DIR__ . '/ProclaimTestCase.php';
