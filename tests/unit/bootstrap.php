<?php

/**
 * PHPUnit Bootstrap for Proclaim Component Unit Tests
 *
 * Loads the real Joomla CMS framework from a local joomla-cms clone.
 * Resolution order: tests.joomla_cms_path in build.properties → JOOMLA_CMS_PATH
 * env var → ../joomla-cms sibling (auto-cloned by cwm-joomla-cms-deps)
 * → first entry in builder.joomla_paths. This ensures tests validate against
 * actual Joomla class signatures.
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

$joomlaCmsPath    = '';
$joomlaConfigPath = '';
$props            = [];

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

        $props[trim(substr($trimmed, 0, $eq))] = trim(substr($trimmed, $eq + 1));
    }

    // Resolution order for the Joomla CMS source path:
    //   1. tests.joomla_cms_path     — explicit override for unusual setups
    //   2. JOOMLA_CMS_PATH env var   — handled below (CI / one-off)
    //   3. ../joomla-cms             — sibling default (auto-cloned by cwm-joomla-cms-deps)
    //   4. builder.joomla_paths[0]   — last resort: first dev site (j5-dev is a full Joomla tree)
    //
    // Note: builder.joomla_dir is intentionally NOT consulted here. cwm-build-tools
    // owns that key with relative-subpath semantics (appended onto each install).
    // Earlier Proclaim revisions overloaded it as an absolute CMS source path —
    // emit a one-line nudge if we still see that, so users migrate cleanly.
    if (!empty($props['builder.joomla_dir']) && str_starts_with($props['builder.joomla_dir'], '/')) {
        fwrite(
            STDERR,
            "WARNING: builder.joomla_dir looks like an absolute path. cwm-build-tools "
            . "treats this key as a relative subdirectory; Proclaim's test harness now "
            . "reads tests.joomla_cms_path. Please rename the entry in build.properties."
            . PHP_EOL
        );
    }

    if (!empty($props['tests.joomla_cms_path']) && is_dir($props['tests.joomla_cms_path'])) {
        $joomlaCmsPath = $props['tests.joomla_cms_path'];
    }

    if ($joomlaCmsPath === '') {
        $sibling = \dirname($componentRoot) . '/joomla-cms';

        if (is_dir($sibling)) {
            $joomlaCmsPath = $sibling;
        } elseif (!empty($props['builder.joomla_paths'])) {
            $candidate = trim(explode(',', $props['builder.joomla_paths'])[0]);

            if ($candidate !== '' && is_dir($candidate)) {
                $joomlaCmsPath = $candidate;
            }
        } elseif (!empty($props['builder.joomla_path']) && is_dir($props['builder.joomla_path'])) {
            $joomlaCmsPath = $props['builder.joomla_path'];
        }
    }

    // Resolve a path that actually has configuration.php (a deployed dev site)
    // for JPATH_CONFIGURATION. The CMS clone above provides class signatures;
    // the dev site provides JConfig so Joomla's container Config provider can
    // hand a populated Registry to the Database service.
    $configCandidates = [];

    if (!empty($props['builder.joomla_paths'])) {
        foreach (explode(',', $props['builder.joomla_paths']) as $p) {
            $configCandidates[] = trim($p);
        }
    }

    if (!empty($props['builder.joomla_path'])) {
        $configCandidates[] = $props['builder.joomla_path'];
    }

    foreach ($configCandidates as $candidate) {
        if ($candidate !== '' && is_file(rtrim($candidate, '/') . '/configuration.php')) {
            $joomlaConfigPath = rtrim($candidate, '/');
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

        // lib_cwmscripture loads its own language file from
        // JPATH_LIBRARIES/cwmscripture — the *installed* name, which differs from
        // the repo's libraries/lib_cwmscripture because the manifest declares
        // <libraryname>cwmscripture</libraryname>. A deployed site bridges the two
        // with a symlink; a bare CMS checkout used only for tests does not, so the
        // load silently failed and every JBS_BBK_* key came back as itself.
        //
        // That went unnoticed while com_proclaim shipped its own duplicate copy of
        // those keys: the strings resolved from the component, so the tests passed
        // without the library ever being consulted (#1761).
        $linkTarget = \dirname(__DIR__, 2) . '/libraries/lib_cwmscripture';
        $linkPath   = JPATH_LIBRARIES . '/cwmscripture';

        if (!file_exists($linkPath) && is_dir($linkTarget)) {
            // Best effort: a failure here is not fatal, it just restores the old
            // behaviour of unresolved keys.
            @symlink($linkTarget, $linkPath);
        }

        if (!\defined('JPATH_CACHE')) {
            \define('JPATH_CACHE', $rootDir . '/administrator/cache');
        }

        // Point JPATH_CONFIGURATION at the deployed dev site (which has a real
        // configuration.php) when one is available — otherwise fall back to the
        // CMS clone path (which usually has no config). This lets Joomla's
        // container Config service provider load JConfig and downstream
        // services (Database, Session) build with valid credentials.
        if (!\defined('JPATH_CONFIGURATION')) {
            \define('JPATH_CONFIGURATION', $joomlaConfigPath !== '' ? $joomlaConfigPath : $rootDir);
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

        /*
         * Make the site component resolvable from JPATH_SITE.
         *
         * Joomla's LayoutHelper looks for a component layout under
         * JPATH_SITE/components/<component>/layouts. The CMS clone is a bare
         * Joomla with nothing installed into it, so anything rendered through a
         * layout returned an empty string — and a test comparing two empty
         * strings passes while verifying nothing. Two characterization tests for
         * multi-reference scripture rendering skipped for exactly this reason,
         * which left the seam #1623 has to move through uncovered.
         *
         * A symlink is what a linked dev install already does (composer symlink),
         * so this only reproduces the layout a real site has. Created once and
         * left in place; never replaced, so a clone that has Proclaim genuinely
         * installed is not disturbed.
         */
        $siteComponentLink = $rootDir . '/components/com_proclaim';

        if (!file_exists($siteComponentLink) && is_dir($componentRoot . '/site')) {
            if (!is_dir(\dirname($siteComponentLink))) {
                @mkdir(\dirname($siteComponentLink), 0755, true);
            }

            // Failure is not fatal: the tests that need it skip with a message
            // saying so, which is how this was found in the first place.
            @symlink($componentRoot . '/site', $siteComponentLink);
        }

        if (!\defined('JDEBUG')) {
            \define('JDEBUG', false);
        }

        // Load the real Joomla CMS framework exactly the way cli/joomla.php
        // and includes/framework.php do. The point of this test harness is
        // that tests validate against real Joomla classes, not stubs — no
        // more shim files declaring fake Factory / Text / Uri / etc.
        //
        // Order is:
        //
        //   1. libraries/bootstrap.php — sets up JLoader, registers the
        //      decorated Composer class loader against the CMS's own
        //      vendor/, installs the error handler, defines JVERSION.
        //      After this, any Joomla\CMS\*, Joomla\Database\*,
        //      Joomla\Registry\*, Joomla\Event\* etc. resolves to the
        //      exact versions the CMS was built against.
        //
        //   2. configuration.php — gives us JConfig, which the Config
        //      service provider reads during Factory::createContainer().
        //
        //   3. Our own vendor/autoload.php — registered *after* the CMS
        //      loader so it's a secondary lookup for classes the CMS
        //      doesn't ship (PHPUnit, prophecy, test tooling). The CMS
        //      loader has already declared the framework classes, so
        //      any duplicate copies in our vendor are never required.
        require_once JPATH_LIBRARIES . '/bootstrap.php';

        if (is_file(JPATH_CONFIGURATION . '/configuration.php')) {
            require_once JPATH_CONFIGURATION . '/configuration.php';
        }

        $ourAutoload = $componentRoot . '/libraries/vendor/autoload.php';
        if (is_file($ourAutoload)) {
            require_once $ourAutoload;
        }

        $joomlaLoaded = true;

        fwrite(STDERR, "Joomla CMS loaded from: $joomlaCmsPath" . PHP_EOL);
    }
}

if (!$joomlaLoaded) {
    fwrite(STDERR, "ERROR: Joomla CMS not found. Run 'composer install' to auto-clone ../joomla-cms," . PHP_EOL);
    fwrite(STDERR, "       set tests.joomla_cms_path in build.properties," . PHP_EOL);
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
        'CWM\\Component\\Proclaim\\Administrator\\'       => $componentRoot . '/admin/src/',
        'CWM\\Component\\Proclaim\\Api\\'                 => $componentRoot . '/api/src/',
        'CWM\\Component\\Proclaim\\Site\\'                => $componentRoot . '/site/src/',
        'CWM\\Component\\Proclaim\\Tests\\'               => $componentRoot . '/tests/unit/',
        'CWM\\Component\\Proclaim\\Tests\\Integration\\'  => $componentRoot . '/tests/integration/',
        'CWM\\Module\\Proclaim\\Site\\'                   => $componentRoot . '/modules/site/mod_proclaim/src/',
        'CWM\\Plugin\\WebServices\\Proclaim\\Extension\\' => $componentRoot . '/plugins/webservices/proclaim/src/Extension/',
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

    // Set up the container + Console application unconditionally. The
    // Console app is what code paths like Route::_, CwmcommentTable::check
    // and Cwmparams::getAdmin reach for via Factory::getApplication().
    // Creating it does not require a working DB, so unit tests that touch
    // those paths must not be coupled to DB availability.
    $container = \Joomla\CMS\Factory::getContainer();

    // The Application service provider (registered by createContainer)
    // expects SessionInterface to be resolvable. The Session provider
    // registers concrete implementations (session.web.*, session.cli)
    // but leaves the generic aliasing to the caller — cli/joomla.php
    // does this for CLI context, so we do the same here.
    $container->alias('session', 'session.cli')
        ->alias('JSession', 'session.cli')
        ->alias(\Joomla\CMS\Session\Session::class, 'session.cli')
        ->alias(\Joomla\Session\Session::class, 'session.cli')
        ->alias(\Joomla\Session\SessionInterface::class, 'session.cli');

    \Joomla\CMS\Factory::$application = $container->get(\Joomla\Console\Application::class);

    // DB connection is best-effort: integration tests that need a live
    // driver check PROCLAIM_TEST_DB_AVAILABLE; pure unit tests don't.
    try {
        $db = $container->get(\Joomla\Database\DatabaseInterface::class);
        $db->connect();
        $GLOBALS['__proclaim_test_db'] = $db;

        \define('PROCLAIM_TEST_DB_AVAILABLE', true);

        fwrite(STDERR, "Database connected: $dbName@$host:$port" . PHP_EOL);
    } catch (\Throwable $e) {
        \define('PROCLAIM_TEST_DB_AVAILABLE', false);
        fwrite(STDERR, "Database not available: " . $e->getMessage() . PHP_EOL);
    }
})();
