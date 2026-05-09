#!/usr/bin/env php
<?php

// phpcs:disable PSR1.Files.SideEffects
/**
 * Proclaim Build Script — packaging + syntax linting.
 *
 * Most dev-environment commands (setup, link, link-check, clean, verify,
 * install-joomla, joomla-latest, version, sync-languages, changelog,
 * release, ars-publish) now live in cwm/build-tools and are invoked through
 * composer via the cwm-* binaries. This file retains only the Proclaim-
 * specific package builder and PHP syntax linter.
 */

\define('BASE_DIR', realpath(__DIR__ . '/..'));
const BUILD_DIR = BASE_DIR . '/build';
// Standardize zip output to build/dist/ — matches cwm-build-tools' canonical
// convention (cwm-build.config.json.tmpl, init.php, sync-configs.php all
// expect /build/dist/) and keeps build/ for source-controlled scripts only.
const DIST_DIR = BUILD_DIR . '/dist';

$command = $argv[1] ?? 'help';
$verbose = \in_array('--verbose', $argv, true) || \in_array('-v', $argv, true);

try {
    switch ($command) {
        case 'build':
            doBuild($verbose);
            break;
        case 'package':
            doPackage($verbose);
            break;
        case 'lint-syntax':
            doLintSyntax($verbose);
            break;
        case 'help':
        default:
            showHelp();
            break;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(1);
}

function showHelp(): void
{
    echo "Proclaim Build Tool\n";
    echo "Usage: php build/proclaim_build.php [command]\n\n";
    echo "Commands:\n";
    echo "  build           Build com_proclaim component zip\n";
    echo "  package         Build pkg_proclaim (library + plugin + component zips)\n";
    echo "  lint-syntax     Check PHP syntax errors\n";
    echo "\nOptions:\n";
    echo "  -v, --verbose   Show detailed output\n";
    echo "\nDev-environment commands moved to cwm/build-tools — use composer scripts:\n";
    echo "  composer setup, symlink, symlink:check, clean, verify,\n";
    echo "  composer joomla-install, joomla-latest, version, sync-languages,\n";
    echo "  composer changelog, release, ars-publish.\n";
}


/**
 * Prompts the user for input via STDIN.
 *
 * @param   string       $question  The question to ask.
 * @param   string|null  $default   The default value if no input is provided.
 * @param   int          $timeout   Seconds to wait before auto-accepting the default (0 = no timeout).
 *
 * @return string|null The user's input or the default value.
 * @since 10.1.0
 */
function ask(string $question, string|null $default = null, int $timeout = 0): string|null
{
    $prompt = $question . ($default ? " [$default]" : '');

    // Skip the interactive countdown when called from a wrapper that's not
    // a real terminal session (cwm-release, CI, scripted runs). Honors
    // common signals so chained release scripts get the default cleanly
    // without leaving half-drawn countdown fragments behind.
    $nonInteractive = !stream_isatty(STDIN)
        || !stream_isatty(STDOUT)
        || getenv('CI') !== false
        || getenv('CWM_NONINTERACTIVE') !== false;

    // Countdown timer with single-keypress detection
    if ($timeout > 0 && $default !== null && !$nonInteractive) {
        $oldStty = trim((string) shell_exec('stty -g 2>/dev/null'));
        system('stty cbreak -echo 2>/dev/null');

        // ANSI clear-to-end-of-line wipes any leftover characters from a
        // previous, longer redraw (e.g. "(10s):" being replaced by "(9s):"
        // would otherwise leave a stray "0" on the line).
        $clear = "\r\033[K";

        for ($remaining = $timeout; $remaining > 0; $remaining--) {
            echo $clear . $prompt . " ({$remaining}s): ";

            $read   = [STDIN];
            $write  = null;
            $except = null;
            $ready  = @stream_select($read, $write, $except, 1);

            if ($ready > 0) {
                $char = fread(STDIN, 1);
                system('stty ' . escapeshellarg($oldStty) . ' 2>/dev/null');
                echo $clear . $prompt . ': ' . $char . "\n";
                return $char === '' ? $default : $char;
            }
        }

        // Timeout — no input
        system('stty ' . escapeshellarg($oldStty) . ' 2>/dev/null');
        echo $clear . $prompt . ': ' . $default . " (auto)\n";
        return $default;
    }

    // Non-interactive: take the default immediately, no prompt drawn.
    if ($nonInteractive && $default !== null) {
        echo $prompt . ': ' . $default . " (auto, non-interactive)\n";
        return $default;
    }

    echo $prompt . ': ';

    $handle = fopen('php://stdin', 'rb');
    $line   = fgets($handle);
    fclose($handle);
    $line = trim($line);
    return $line === '' ? $default : $line;
}

function doBuild(bool $verbose = false): void
{
    // Build assets first
    echo "Building frontend assets...\n";
    passthru('npm install && npm run build', $returnVar);
    if ($returnVar !== 0) {
        throw new \RuntimeException('Asset build failed');
    }

    // Get version from proclaim.xml
    $xmlVersion = '10.0.x';
    if (file_exists(BASE_DIR . '/proclaim.xml')) {
        $xml = simplexml_load_string(file_get_contents(BASE_DIR . '/proclaim.xml'));
        if ($xml && isset($xml->version)) {
            $xmlVersion = (string) $xml->version;
        }
    }

    // Generate a date-based version
    $dateVersion = date('Ymd');

    // Check if running in a non-interactive environment
    if (stream_isatty(STDIN)) {
        echo "\nSelect version to build:\n";
        echo "  [1] XML Version ($xmlVersion) - Default\n";
        echo "  [2] Date Version ($dateVersion)\n";
        echo "  [3] Custom Version\n";

        $choice = ask('Enter choice [1-3]', '1', 10);

        switch ($choice) {
            case '2':
                $version = $dateVersion;
                break;
            case '3':
                $version = ask('Enter custom version');
                break;
            case '1':
            default:
                $version = $xmlVersion;
                break;
        }
    } else {
        echo "Non-interactive mode detected. Using XML version: $xmlVersion\n";
        $version = $xmlVersion;
    }

    echo "\nPackaging Proclaim v$version...\n";

    if (!is_dir(DIST_DIR)) {
        mkdir(DIST_DIR, 0777, true);
    }

    $zipFile = DIST_DIR . "/com_proclaim-$version.zip";

    if (file_exists($zipFile)) {
        unlink($zipFile);
    }

    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
        throw new \RuntimeException("Cannot open <$zipFile>");
    }

    // Resolve BASE_DIR to a real path so it matches getRealPath() output
    $resolvedBase = realpath(BASE_DIR);

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($resolvedBase, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $excludes = [
        'build.xml', 'build.properties', 'build.dist.properties', 'phpunit.xml', 'phpunit.xml.bak',
        '.php-cs-fixer.dist.php', 'CLAUDE.md', 'GEMINI.md', 'SECURITY.md', '_config.yml',
        '.git', '.vscode', '.idea', '.DS_Store', 'node_modules', 'composer.json', 'composer.lock',
        'package.json', 'package-lock.json', 'build', 'tests', '.jshintrc',
        // Exclude internal symlinks created by doLink
        'admin/proclaim.xml',
        'admin/proclaim.script.php',
        // Exclude dev files
        'media/js/joomla.d.ts',
        // Exclude Composer vendor (dev-only)
        'libraries/vendor',
        // Exclude submodules — installed as separate zips by pkg_proclaim
        'libraries/lib_cwmscripture',
        'plugins/content/scripturelinks',
    ];

    // File extensions to exclude (dev/debug files)
    $excludeExts = ['map'];

    $includes    = ['admin/', 'media/', 'modules/', 'plugins/', 'site/', 'libraries/'];
    $includeExts = ['php', 'xml', 'txt', 'md'];

    $fileCount = 0;
    foreach ($files as $name => $file) {
        if ($file->isDir()) {
            continue;
        }

        $filePath     = $file->getRealPath();
        // Normalize path separators to forward slashes
        $relativePath = str_replace('\\', '/', substr($filePath, \strlen($resolvedBase) + 1));

        // Check excludes
        $excludeFile = false;
        foreach ($excludes as $exclude) {
            $cleanExclude = rtrim($exclude, '/');

            // 1. Exact match (root file or folder)
            if ($relativePath === $cleanExclude) {
                $excludeFile = true;
                break;
            }

            // 2. Start with exclude + / (content of excluded folder in root)
            if (str_starts_with($relativePath, $cleanExclude . '/')) {
                $excludeFile = true;
                break;
            }

            // 3. Inside a subdirectory (e.g. some/path/exclude/...)
            if (str_contains($relativePath, '/' . $cleanExclude . '/')) {
                $excludeFile = true;
                break;
            }

            // 4. End with /exclude (file or folder in subdirectory)
            if (str_ends_with($relativePath, '/' . $cleanExclude)) {
                $excludeFile = true;
                break;
            }
        }

        // Check excluded extensions (e.g., .map files for source maps)
        if (!$excludeFile) {
            $ext = pathinfo($relativePath, PATHINFO_EXTENSION);
            if (\in_array($ext, $excludeExts, true)) {
                $excludeFile = true;
            }
        }

        // Exclude SQL files from media/backup/ (database backups)
        if (!$excludeFile && str_starts_with($relativePath, 'media/backup/') && pathinfo($relativePath, PATHINFO_EXTENSION) === 'sql') {
            $excludeFile = true;
        }

        // Exclude non-essential files from addon vendor directories
        if (!$excludeFile && str_contains($relativePath, '/vendor/')) {
            $basename  = basename($relativePath);
            $upperBase = strtoupper(pathinfo($basename, PATHINFO_FILENAME));

            // Composer metadata (not needed at runtime)
            if ($basename === 'installed.json' || $basename === 'installed.php') {
                $excludeFile = true;
            }

            // Docs, changelogs, and readmes
            if (\in_array($upperBase, ['README', 'CHANGELOG', 'BACKERS', 'AUTHORS', 'CONTRIBUTING', 'UPGRADE', 'SECURITY'], true)) {
                $excludeFile = true;
            }

            // LICENSE files in subdirectories (keep root LICENSE only)
            if ($upperBase === 'LICENSE' || $upperBase === 'COPYING') {
                $excludeFile = true;
            }
        }

        if ($excludeFile) {
            continue;
        }

        // Check includes
        $shouldInclude = false;
        foreach ($includes as $include) {
            if (str_starts_with($relativePath, $include)) {
                $shouldInclude = true;
                break;
            }
        }
        if (!$shouldInclude) {
            $ext = pathinfo($relativePath, PATHINFO_EXTENSION);
            if (\in_array($ext, $includeExts, true) && !str_contains($relativePath, '/')) {
                // Root files
                $shouldInclude = true;
            }
        }

        if ($shouldInclude) {
            $zip->addFile($filePath, $relativePath);
            $fileCount++;
            if ($verbose) {
                echo "  + $relativePath\n";
            }
        }
    }

    $zip->close();

    echo "\nBuild complete: com_proclaim-$version.zip ($fileCount files)\n";
    echo "Location: $zipFile\n";
}

/**
 * Builds pkg_proclaim package containing lib_cwmscripture.zip, plg_content_scripturelinks.zip, and com_proclaim.zip.
 *
 * @param   bool  $verbose  Show detailed output
 *
 * @return void
 * @throws Exception
 * @since 10.3.0
 */
function doPackage(bool $verbose = false): void
{
    // Get version from proclaim.xml
    $version = '10.3.0';

    if (file_exists(BASE_DIR . '/proclaim.xml')) {
        $xml = simplexml_load_string(file_get_contents(BASE_DIR . '/proclaim.xml'));

        if ($xml && isset($xml->version)) {
            $version = (string) $xml->version;
        }
    }

    echo "Building pkg_proclaim v$version\n\n";

    $packageDir = BUILD_DIR . '/package';

    if (is_dir($packageDir)) {
        exec('rm -rf ' . escapeshellarg($packageDir));
    }

    mkdir($packageDir, 0777, true);

    // Step 1: Build lib_cwmscripture.zip from submodule
    echo "Step 1: Building lib_cwmscripture.zip...\n";
    $libBuildScript = BASE_DIR . '/libraries/lib_cwmscripture/build/build-package.php';

    if (!file_exists($libBuildScript)) {
        throw new \RuntimeException('Library build script not found: ' . $libBuildScript);
    }

    $libDistDir = BASE_DIR . '/libraries/lib_cwmscripture/build/dist';
    passthru('php ' . escapeshellarg($libBuildScript), $returnVar);

    if ($returnVar !== 0) {
        throw new \RuntimeException('Library build failed');
    }

    // Find the built zip
    $libZipSource = null;

    if (is_dir($libDistDir)) {
        foreach (glob($libDistDir . '/lib_cwmscripture-*.zip') as $candidate) {
            $libZipSource = $candidate;
            break;
        }
    }

    if (!$libZipSource || !file_exists($libZipSource)) {
        throw new \RuntimeException('lib_cwmscripture ZIP not found after build');
    }

    copy($libZipSource, $packageDir . '/lib_cwmscripture.zip');
    echo "  Done: " . basename($libZipSource) . "\n";

    // Step 2: Build plg_content_scripturelinks.zip using --plugin-only flag
    echo "Step 2: Building plg_content_scripturelinks.zip...\n";
    $plgBuildScript = BASE_DIR . '/plugins/content/scripturelinks/build/build-package.php';

    if (!file_exists($plgBuildScript)) {
        throw new \RuntimeException('Plugin build script not found: ' . $plgBuildScript);
    }

    passthru('php ' . escapeshellarg($plgBuildScript) . ' --plugin-only', $returnVar);

    if ($returnVar !== 0) {
        throw new \RuntimeException('Plugin build failed');
    }

    $plgDistDir   = BASE_DIR . '/plugins/content/scripturelinks/build/dist';
    $plgZipSource = $plgDistDir . '/plg_content_scripturelinks.zip';

    if (!file_exists($plgZipSource)) {
        throw new \RuntimeException('plg_content_scripturelinks.zip not found after build');
    }

    copy($plgZipSource, $packageDir . '/plg_content_scripturelinks.zip');
    echo "  Done.\n";

    // Step 3: Build com_proclaim.zip (calls existing doBuild)
    echo "Step 3: Building com_proclaim.zip...\n";
    doBuild($verbose);

    // Find the com_proclaim zip that doBuild created
    $comZipSource = null;

    foreach (glob(DIST_DIR . '/com_proclaim-*.zip') as $candidate) {
        $comZipSource = $candidate;
        break;
    }

    if (!$comZipSource || !file_exists($comZipSource)) {
        throw new \RuntimeException('com_proclaim ZIP not found after build');
    }

    copy($comZipSource, $packageDir . '/com_proclaim.zip');
    echo "  Done.\n";

    // Step 4: Assemble pkg_proclaim zip
    echo "\nAssembling pkg_proclaim-$version.zip...\n";
    if (!is_dir(DIST_DIR)) {
        mkdir(DIST_DIR, 0777, true);
    }

    $pkgZipPath = DIST_DIR . '/pkg_proclaim-' . $version . '.zip';

    if (file_exists($pkgZipPath)) {
        unlink($pkgZipPath);
    }

    $pkgZip = new ZipArchive();

    if ($pkgZip->open($pkgZipPath, ZipArchive::CREATE) !== true) {
        throw new \RuntimeException("Cannot create $pkgZipPath");
    }

    // Package manifest
    $pkgZip->addFile(BUILD_DIR . '/pkg_proclaim.xml', 'pkg_proclaim.xml');

    // Install script
    $scriptFile = BUILD_DIR . '/script.install.php';

    if (!file_exists($scriptFile)) {
        throw new \RuntimeException('Package install script not found: ' . $scriptFile);
    }

    $pkgZip->addFile($scriptFile, 'script.install.php');

    // Language file
    $langFile = BUILD_DIR . '/language/en-GB/en-GB.pkg_proclaim.sys.ini';

    if (!file_exists($langFile)) {
        throw new \RuntimeException('Package language file not found: ' . $langFile);
    }

    $pkgZip->addFile($langFile, 'language/en-GB/en-GB.pkg_proclaim.sys.ini');

    // Child extension packages
    $pkgZip->addFile($packageDir . '/lib_cwmscripture.zip', 'packages/lib_cwmscripture.zip');
    $pkgZip->addFile($packageDir . '/plg_content_scripturelinks.zip', 'packages/plg_content_scripturelinks.zip');
    $pkgZip->addFile($packageDir . '/com_proclaim.zip', 'packages/com_proclaim.zip');
    $pkgZip->close();

    // Cleanup temp package dir
    exec('rm -rf ' . escapeshellarg($packageDir));

    echo "\nPackage complete: pkg_proclaim-$version.zip\n";
    echo "Location: $pkgZipPath\n";
}

/**
 * Checks all PHP files in the project for syntax errors.
 *
 * @param   bool  $verbose  If true, prints each file as it is checked.
 *
 * @return void
 * @since 10.1.0
 */
function doLintSyntax(bool $verbose = false): void
{
    echo "Checking PHP syntax...\n";

    $directories = ['admin/src', 'site/src', 'libraries/src', 'modules', 'plugins'];
    $errors      = [];
    $fileCount   = 0;

    foreach ($directories as $dir) {
        $path = BASE_DIR . '/' . $dir;
        if (!is_dir($path)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filePath     = $file->getRealPath();
            $relativePath = str_replace(BASE_DIR . '/', '', $filePath);
            $fileCount++;

            if ($verbose) {
                echo "  $relativePath\n";
            }

            // Run php -l on the file
            $output    = [];
            $returnVar = 0;
            exec('php -l ' . escapeshellarg($filePath) . ' 2>&1', $output, $returnVar);

            if ($returnVar !== 0) {
                $errors[] = [
                    'file'  => $relativePath,
                    'error' => implode("\n", $output),
                ];
            }
        }
    }

    if (\count($errors) > 0) {
        echo "\nSyntax errors found in $fileCount files checked:\n";
        echo str_repeat('-', 60) . "\n";
        foreach ($errors as $error) {
            echo "File: {$error['file']}\n";
            echo "{$error['error']}\n\n";
        }
        exit(1);
    }

    echo "No syntax errors in $fileCount files.\n";
}
// phpcs:enable PSR1.Files.SideEffects
