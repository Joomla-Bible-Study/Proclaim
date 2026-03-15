<?php

/**
 * PHPUnit Bootstrap for Proclaim Component Integration Tests
 *
 * Loads the unit bootstrap (which includes optional DB bootstrapping from
 * build.properties) and registers the integration test autoloader.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// Load the unit test bootstrap (stubs, autoloading, constants, optional DB)
require_once \dirname(__DIR__) . '/unit/bootstrap.php';

// Register PSR-4 autoloader for integration test classes
spl_autoload_register(function ($class) {
    $prefix  = 'CWM\\Component\\Proclaim\\Tests\\Integration\\';
    $baseDir = __DIR__ . '/';

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
