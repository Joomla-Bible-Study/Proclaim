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
if (empty($joomlaCmsPath) && !empty(getenv('JOOMLA_CMS_PATH'))) {
    $joomlaCmsPath = getenv('JOOMLA_CMS_PATH');
}

// ---------------------------------------------------------------------------
// Load Joomla CMS framework
// ---------------------------------------------------------------------------

$joomlaLoaded = false;

if ($joomlaCmsPath !== '' && is_dir($joomlaCmsPath)) {
    $loaderFile    = rtrim($joomlaCmsPath, '/') . '/libraries/loader.php';
    $vendorFile    = rtrim($joomlaCmsPath, '/') . '/libraries/vendor/autoload.php';
    $namespaceFile = rtrim($joomlaCmsPath, '/') . '/libraries/namespacemap.php';

    if (is_file($loaderFile) && is_file($vendorFile)) {
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

        // Load the Joomla Platform (same as Joomla's own test bootstrap)
        require_once $loaderFile;

        if (!class_exists('JLoader')) {
            throw new RuntimeException('Joomla Platform not loaded from: ' . $loaderFile);
        }

        \JLoader::setup();

        // Load Joomla's Composer autoloader (provides CMS classes)
        // NOTE: We do NOT prepend it — our own autoloader (loaded later)
        // takes priority so our PHPUnit version isn't overridden by Joomla's.
        /** @var \Composer\Autoload\ClassLoader $joomlaLoader */
        $joomlaLoader = require $vendorFile;

        // Decorate with Joomla's class loader
        class_exists('\\Joomla\\CMS\\Autoload\\ClassLoader');
        $joomlaLoader->unregister();
        spl_autoload_register([new \Joomla\CMS\Autoload\ClassLoader($joomlaLoader), 'loadClass'], true, false);

        // Load extension namespace map
        if (is_file($namespaceFile)) {
            require_once $namespaceFile;
            $extensionPsr4Loader = new \JNamespacePsr4Map();
            $extensionPsr4Loader->load();
        }

        // Define Joomla version
        \defined('JVERSION') or \define('JVERSION', (new \Joomla\CMS\Version())->getShortVersion());

        $joomlaLoaded = true;

        // Create a minimal mock application so Factory::getApplication() works in tests
        if (\Joomla\CMS\Factory::$application === null) {
            $mockApp = new class extends \Joomla\CMS\Application\CMSApplication {
                public function __construct()
                {
                    // Skip parent constructor — no real app bootstrap needed
                    $this->input  = new \Joomla\Input\Input();
                    $this->config = new \Joomla\Registry\Registry();
                }

                protected function doExecute(): void
                {
                }

                public function getName(): string
                {
                    return 'test';
                }

                public function isClient($identifier): bool
                {
                    return $identifier === 'site';
                }

                public function getIdentity(): \Joomla\CMS\User\User
                {
                    $user     = new \Joomla\CMS\User\User();
                    $user->id = 42;

                    return $user;
                }
            };
            \Joomla\CMS\Factory::$application = $mockApp;
        }

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

// ---------------------------------------------------------------------------
// Load our Composer autoloader (for component dev dependencies like PHPUnit)
// ---------------------------------------------------------------------------

$composerAutoload = $componentRoot . '/libraries/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

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
// Optional: Bootstrap a real database connection from build.properties
// ---------------------------------------------------------------------------

(function () use ($componentRoot) {
    $propsFile = $componentRoot . '/build.properties';

    if (!file_exists($propsFile)) {
        return;
    }

    // Parse build.properties
    $props = [];
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

        $props[trim(substr($trimmed, 0, $eq))] = trim(substr($trimmed, $eq + 1));
    }

    // Find the first Joomla installation path (for database config)
    $joomlaPath = '';

    if (!empty($props['builder.joomla_paths'])) {
        $paths      = array_map('trim', explode(',', $props['builder.joomla_paths']));
        $joomlaPath = $paths[0] ?? '';
    } elseif (!empty($props['builder.joomla_path'])) {
        $joomlaPath = $props['builder.joomla_path'];
    }

    if ($joomlaPath === '' || !is_dir($joomlaPath)) {
        return;
    }

    $configFile = rtrim($joomlaPath, '/') . '/configuration.php';

    if (!file_exists($configFile)) {
        return;
    }

    // Load Joomla's configuration
    require_once $configFile;

    if (!class_exists('JConfig', false)) {
        return;
    }

    $config = new \JConfig();

    // Create a real database connection
    $host   = $config->host ?? 'localhost';
    $dbName = $config->db ?? '';
    $user   = $config->user ?? '';
    $pass   = $config->password ?? '';
    $prefix = $config->dbprefix ?? '';

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
        $options = [
            'driver'   => 'mysqli',
            'host'     => $host,
            'port'     => $port,
            'user'     => $user,
            'password' => $pass,
            'database' => $dbName,
            'prefix'   => $prefix,
        ];

        $db = \Joomla\Database\DatabaseDriver::getInstance($options);
        $db->connect();

        // Register in DI container for Factory::getContainer()->get(DatabaseInterface::class)
        try {
            $container = \Joomla\CMS\Factory::getContainer();

            if ($container instanceof \Joomla\DI\Container) {
                $container->set(\Joomla\Database\DatabaseInterface::class, $db);
            }
        } catch (\Throwable) {
            // Container not available or key protected — tests use direct DB access
        }

        // Store reference for tests that need direct access
        \define('PROCLAIM_TEST_DB_AVAILABLE', true);

        fwrite(STDERR, "Database connected: $dbName@$host:$port" . PHP_EOL);
    } catch (\Throwable $e) {
        // Database not available — integration tests will skip gracefully
        \define('PROCLAIM_TEST_DB_AVAILABLE', false);
        fwrite(STDERR, "Database not available: " . $e->getMessage() . PHP_EOL);
    }
})();
