<?php

/**
 * PHPUnit Bootstrap for Proclaim Component Unit Tests
 *
 * Loads the real Joomla CMS framework from a local joomla-cms clone
 * (configured via builder.joomla_dir in build.properties). This ensures
 * tests validate against actual Joomla class signatures.
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

// Set fixed precision value to avoid round related issues (matches Joomla)
ini_set('precision', 14);

// Set server variables needed by Joomla's Uri class in CLI context
$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
$_SERVER['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'] ?? '/index.php';

// Component root (this repo)
$componentRoot = \dirname(__DIR__, 2);

// ---------------------------------------------------------------------------
// Resolve Joomla CMS path from build.properties
// ---------------------------------------------------------------------------

$joomlaCmsPath = '';

$propsFile = $componentRoot . '/build.properties';

if (file_exists($propsFile)) {
    $lines = file($propsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        $eq = strpos($trimmed, '=');

        if ($eq === false) {
            continue;
        }

        $key   = trim(substr($trimmed, 0, $eq));
        $value = trim(substr($trimmed, $eq + 1));

        if ($key === 'builder.joomla_dir' && $value !== '' && is_dir($value)) {
            $joomlaCmsPath = $value;
            break;
        }
    }
}

// Environment variable override (useful for CI)
$envPath = getenv('JOOMLA_CMS_PATH');
if (empty($joomlaCmsPath) && $envPath !== false && $envPath !== '' && is_dir($envPath)) {
    $joomlaCmsPath = $envPath;
}

// ---------------------------------------------------------------------------
// Load Joomla CMS framework
// ---------------------------------------------------------------------------

$joomlaLoaded = false;

if ($joomlaCmsPath !== '' && is_dir($joomlaCmsPath)) {
    $loaderFile = rtrim($joomlaCmsPath, '/') . '/libraries/loader.php';
    $cmsSrcDir  = rtrim($joomlaCmsPath, '/') . '/libraries/src';

    if (is_file($loaderFile) && is_dir($cmsSrcDir)) {
        // Define Joomla path constants pointing at the CMS clone
        $rootDir = rtrim($joomlaCmsPath, '/');

        if (!\defined('JPATH_BASE')) {
            \define('JPATH_BASE', $rootDir);
        }

        if (!\defined('JPATH_ROOT')) {
            \define('JPATH_ROOT', $rootDir);
        }

        if (!\defined('JPATH_SITE')) {
            \define('JPATH_SITE', $rootDir);
        }

        if (!\defined('JPATH_ADMINISTRATOR')) {
            \define('JPATH_ADMINISTRATOR', $rootDir . '/administrator');
        }

        if (!\defined('JPATH_LIBRARIES')) {
            \define('JPATH_LIBRARIES', $rootDir . '/libraries');
        }

        if (!\defined('JPATH_CACHE')) {
            \define('JPATH_CACHE', $rootDir . '/administrator/cache');
        }

        if (!\defined('JPATH_CONFIGURATION')) {
            \define('JPATH_CONFIGURATION', $rootDir);
        }

        if (!\defined('JPATH_PLUGINS')) {
            \define('JPATH_PLUGINS', $rootDir . '/plugins');
        }

        if (!\defined('JPATH_THEMES')) {
            \define('JPATH_THEMES', $rootDir . '/templates');
        }

        if (!\defined('JPATH_API')) {
            \define('JPATH_API', $rootDir . '/api');
        }

        if (!\defined('JPATH_INSTALLATION')) {
            \define('JPATH_INSTALLATION', $rootDir . '/installation');
        }

        if (!\defined('JPATH_MANIFESTS')) {
            \define('JPATH_MANIFESTS', JPATH_ADMINISTRATOR . '/manifests');
        }

        if (!\defined('JDEBUG')) {
            \define('JDEBUG', false);
        }

        // Load our Composer autoloader FIRST — provides PHPUnit and framework
        // packages (joomla/database, joomla/registry, etc.) without conflicts.
        $ourAutoload = $componentRoot . '/libraries/vendor/autoload.php';
        if (is_file($ourAutoload)) {
            require_once $ourAutoload;
        }

        // Load runtime shims for classes that tests call (Factory, Text, Uri, Route).
        // These must load BEFORE the CMS source autoloader so they take priority
        // over the real CMS classes which have heavy dependency chains.
        require_once $componentRoot . '/tests/unit/Stubs/CmsRuntimeShims.php';

        // Register a PSR-4 autoloader for Joomla CMS source classes.
        // We do NOT use JLoader (intercepts PHPUnit lookups) or joomla-cms's
        // vendor/autoload.php (PSR package version conflicts).
        // Our Composer provides framework packages; joomla-cms provides CMS classes.
        //
        // IMPORTANT: This autoloader is appended (not prepended) so our Composer
        // autoloader always wins for framework classes like Joomla\Database\*.
        spl_autoload_register(function ($class) use ($rootDir) {
            // Joomla\CMS\ → libraries/src/
            $cmsPrefix = 'Joomla\\CMS\\';

            if (str_starts_with($class, $cmsPrefix)) {
                $file = $rootDir . '/libraries/src/' . str_replace('\\', '/', substr($class, \strlen($cmsPrefix))) . '.php';

                if (is_file($file)) {
                    require_once $file;

                    return true;
                }
            }

            // Joomla\Component\ → administrator/components/
            $compPrefix = 'Joomla\\Component\\';

            if (str_starts_with($class, $compPrefix)) {
                $relative = substr($class, \strlen($compPrefix));
                $parts    = explode('\\', $relative, 3);

                if (\count($parts) >= 3) {
                    $component = strtolower($parts[0]);
                    $file      = $rootDir . '/administrator/components/com_' . $component . '/src/'
                        . str_replace('\\', '/', $parts[2]) . '.php';

                    if (is_file($file)) {
                        require_once $file;

                        return true;
                    }
                }
            }

            return false;
        });

        // Define Joomla version
        \defined('JVERSION') or \define('JVERSION', '5.4.0');

        $joomlaLoaded = true;

        fwrite(STDERR, "Joomla CMS loaded from: $joomlaCmsPath" . PHP_EOL);
    }
}

if (!$joomlaLoaded) {
    fwrite(STDERR, "ERROR: Joomla CMS not found. Set builder.joomla_dir in build.properties" . PHP_EOL);
    fwrite(STDERR, "       or set JOOMLA_CMS_PATH environment variable." . PHP_EOL);
    fwrite(STDERR, "       See: https://github.com/Joomla-Bible-Study/Proclaim/wiki/Development-Setup" . PHP_EOL);
    exit(1);
}

// ---------------------------------------------------------------------------
// Component-specific constants
// ---------------------------------------------------------------------------

if (!\defined('BIBLESTUDY_PATH_ADMIN')) {
    \define('BIBLESTUDY_PATH_ADMIN', $componentRoot . '/admin');
}

if (!\defined('JPATH_TESTS')) {
    \define('JPATH_TESTS', $componentRoot . '/tests');
}

// Our Composer autoloader was loaded earlier (before JLoader) to avoid
// PSR package version conflicts with joomla-cms's vendor directory.

// ---------------------------------------------------------------------------
// Register PSR-4 autoloader for the Proclaim component
// ---------------------------------------------------------------------------

spl_autoload_register(function ($class) use ($componentRoot) {
    $mappings = [
        'CWM\\Component\\Proclaim\\Administrator\\' => $componentRoot . '/admin/src/',
        'CWM\\Component\\Proclaim\\Site\\'           => $componentRoot . '/site/src/',
        'CWM\\Component\\Proclaim\\Tests\\'          => $componentRoot . '/tests/unit/',
        'CWM\\Component\\Proclaim\\Tests\\Integration\\' => $componentRoot . '/tests/integration/',
        'CWM\\Module\\Proclaim\\Site\\'              => $componentRoot . '/modules/site/mod_proclaim/src/',
    ];

    foreach ($mappings as $prefix => $baseDir) {
        $len = \strlen($prefix);

        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file          = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require $file;

                return true;
            }
        }
    }

    return false;
});

// Load the base test case
require_once __DIR__ . '/ProclaimTestCase.php';

// ---------------------------------------------------------------------------
// Optional: Bootstrap a real database connection
// Sources: 1) JTEST_DB_* env vars (CI), 2) build.properties → Joomla config
// ---------------------------------------------------------------------------

(function () use ($componentRoot) {
    $host   = '';
    $dbName = '';
    $user   = '';
    $pass   = '';
    $prefix = '';

    // Source 1: JTEST_DB_* environment variables (CI)
    if (getenv('JTEST_DB_HOST') && getenv('JTEST_DB_NAME')) {
        $host   = getenv('JTEST_DB_HOST');
        $dbName = getenv('JTEST_DB_NAME');
        $user   = getenv('JTEST_DB_USER') ?: '';
        $pass   = getenv('JTEST_DB_PASSWORD') ?: '';
        $prefix = getenv('JTEST_DB_PREFIX') ?: '';
    }

    // Source 2: build.properties → Joomla configuration.php
    if ($dbName === '') {
        $propsFile = $componentRoot . '/build.properties';

        if (file_exists($propsFile)) {
            $props = [];
            $lines = file($propsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $trimmed = trim($line);

                if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                    continue;
                }

                $eq = strpos($trimmed, '=');

                if ($eq !== false) {
                    $props[trim(substr($trimmed, 0, $eq))] = trim(substr($trimmed, $eq + 1));
                }
            }

            $joomlaPath = '';

            if (!empty($props['builder.joomla_paths'])) {
                $paths      = array_map('trim', explode(',', $props['builder.joomla_paths']));
                $joomlaPath = $paths[0] ?? '';
            } elseif (!empty($props['builder.joomla_path'])) {
                $joomlaPath = $props['builder.joomla_path'];
            }

            $configFile = $joomlaPath !== '' && is_dir($joomlaPath)
                ? rtrim($joomlaPath, '/') . '/configuration.php'
                : '';

            if ($configFile !== '' && file_exists($configFile)) {
                require_once $configFile;

                if (class_exists('JConfig', false)) {
                    $config = new \JConfig();
                    $host   = $config->host ?? 'localhost';
                    $dbName = $config->db ?? '';
                    $user   = $config->user ?? '';
                    $pass   = $config->password ?? '';
                    $prefix = $config->dbprefix ?? '';
                }
            }
        }
    }

    if ($dbName === '' || $user === '') {
        return;
    }

    // Parse host:port
    $port = 3306;

    if (str_contains($host, ':')) {
        [$host, $port] = explode(':', $host, 2);
        $port          = (int) $port;
    }

    try {
        $db = \Joomla\Database\DatabaseDriver::getInstance([
            'driver'   => 'mysqli',
            'host'     => $host,
            'port'     => $port,
            'user'     => $user,
            'password' => $pass,
            'database' => $dbName,
            'prefix'   => $prefix,
        ]);
        $db->connect();

        \define('PROCLAIM_TEST_DB_AVAILABLE', true);

        fwrite(STDERR, "Database connected: $dbName@$host:$port" . PHP_EOL);
    } catch (\Throwable $e) {
        \define('PROCLAIM_TEST_DB_AVAILABLE', false);
        fwrite(STDERR, "Database not available: " . $e->getMessage() . PHP_EOL);
    }
})();
