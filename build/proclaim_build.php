#!/usr/bin/env php
<?php

/**
 * Proclaim Build Script
 * Replaces Phing build.xml
 */

\define('BASE_DIR', \dirname(__DIR__));
\define('BUILD_DIR', BASE_DIR . '/build');
\define('PROPERTIES_FILE', BASE_DIR . '/build.properties');

$command = $argv[1] ?? 'help';

try {
    switch ($command) {
        case 'setup':
            doSetup();
            break;
        case 'link':
            doLink();
            break;
        case 'clean':
            doClean();
            break;
        case 'build':
            doBuild();
            break;
        case 'install-joomla':
            doInstallJoomla();
            break;
        case 'joomla-latest':
            doJoomlaLatest();
            break;
        case 'lint-syntax':
            doLintSyntax();
            break;
        case 'help':
        default:
            showHelp();
            break;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Displays the help message with available commands.
 *
 * @return void
 */
function showHelp()
{
    echo "Proclaim Build Tool\n";
    echo "Usage: php build/proclaim_build.php [command]\n\n";
    echo "Commands:\n";
    echo "  setup           Interactive setup wizard for build.properties\n";
    echo "  link            Setup symbolic links to local Joomla installation\n";
    echo "  clean           Remove symbolic links (clean dev state)\n";
    echo "  build           Build component package (zip)\n";
    echo "  install-joomla  Download and install Joomla\n";
    echo "  joomla-latest   Show latest available Joomla version\n";
    echo "  lint-syntax     Check PHP syntax errors\n";
}

/**
 * Reads and parses the build.properties file.
 *
 * @return array Associative array of properties.
 * @throws Exception If build.properties does not exist.
 */
function getProperties()
{
    if (!file_exists(PROPERTIES_FILE)) {
        throw new Exception("build.properties not found. Run 'composer setup' first.");
    }
    $lines = file(PROPERTIES_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $props = [];
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $props[trim($key)] = trim($value);
    }
    return $props;
}

/**
 * Prompts the user for input via STDIN.
 *
 * @param string $question The question to ask.
 * @param string|null $default The default value if no input is provided.
 * @return string The user's input or the default value.
 */
function ask($question, $default = null)
{
    echo $question . ($default ? " [$default]" : "") . ": ";
    $handle = fopen("php://stdin", "r");
    $line   = fgets($handle);
    fclose($handle);
    $line = trim($line);
    return $line === '' ? $default : $line;
}

/**
 * Runs the interactive setup wizard to configure build.properties.
 *
 * @return void
 */
function doSetup()
{
    echo "=== Proclaim Development Setup Wizard ===\n\n";

    $currentProps = file_exists(PROPERTIES_FILE) ? getProperties() : [];

    $joomlaPath    = ask("Enter the full path to your Joomla installation", $currentProps['builder.joomla_path'] ?? '');
    $joomlaDir     = ask("Enter subdirectory within Joomla path (leave empty if none)", $currentProps['builder.joomla_dir'] ?? '');
    $joomlaVersion = ask("Enter the Joomla version for testing", $currentProps['joomla.version'] ?? '5.4.2');

    $content = "# Build properties\n";
    $content .= "builder.joomla_path=$joomlaPath\n";
    $content .= "builder.joomla_dir=$joomlaDir\n";
    $content .= "joomla.version=$joomlaVersion\n";

    file_put_contents(PROPERTIES_FILE, $content);
    echo "\nConfiguration saved to build.properties\n";

    $install = ask("Do you want to download and install Joomla $joomlaVersion? (y/n)", "n");
    if (strtolower($install) === 'y') {
        doInstallJoomla();
    }
}

/**
 * Creates symbolic links between the project and a local Joomla installation.
 *
 * @param bool $quiet If true, suppresses non-error output.
 * @return void
 * @throws Exception If the Joomla path does not exist.
 */
function doLink($quiet = false)
{
    $props      = getProperties();
    $joomlaPath = rtrim($props['builder.joomla_path'], '/') . '/' . trim($props['builder.joomla_dir'], '/');
    $joomlaPath = rtrim($joomlaPath, '/');

    if (!is_dir($joomlaPath)) {
        throw new Exception("Joomla path does not exist: $joomlaPath");
    }

    if (!$quiet) {
        echo "Linking to Joomla at: $joomlaPath\n";
    }

    // Internal links (dev.init)
    symlink_force(BASE_DIR . '/proclaim.xml', BASE_DIR . '/admin/proclaim.xml', $quiet);
    symlink_force(BASE_DIR . '/proclaim.script.php', BASE_DIR . '/admin/proclaim.script.php', $quiet);
    if (!is_dir(BASE_DIR . '/media/css/site')) {
        mkdir(BASE_DIR . '/media/css/site', 0777, true);
    }
    symlink_force(BASE_DIR . '/media/css/cwmcore.css', BASE_DIR . '/media/css/site/cwmcore.css', $quiet);

    // External links
    $links = [
        BASE_DIR . '/media'                             => "$joomlaPath/media/com_proclaim",
        BASE_DIR . '/admin'                             => "$joomlaPath/administrator/components/com_proclaim",
        BASE_DIR . '/site'                              => "$joomlaPath/components/com_proclaim",
        BASE_DIR . '/modules/site/mod_proclaim'         => "$joomlaPath/modules/mod_proclaim",
        BASE_DIR . '/modules/admin/mod_proclaimicon'    => "$joomlaPath/administrator/modules/mod_proclaimicon",
        BASE_DIR . '/modules/site/mod_proclaim_podcast' => "$joomlaPath/modules/mod_proclaim_podcast",
        BASE_DIR . '/modules/site/mod_proclaim_youtube' => "$joomlaPath/modules/mod_proclaim_youtube",
        BASE_DIR . '/plugins/finder/proclaim'           => "$joomlaPath/plugins/finder/proclaim",
        BASE_DIR . '/plugins/task/proclaim'             => "$joomlaPath/plugins/task/proclaim",
    ];

    foreach ($links as $target => $link) {
        symlink_force($target, $link, $quiet);
    }

    if (!$quiet) {
        echo "Symlinks created successfully.\n";
    }
}

/**
 * Forces creation of a symbolic link, removing any existing file or directory at the link path.
 *
 * @param string $target The target path the link should point to.
 * @param string $link The path where the link should be created.
 * @param bool $quiet If true, suppresses success messages.
 * @return void
 */
function symlink_force($target, $link, $quiet = false)
{
    // Clear file status cache to ensure we get fresh results
    clearstatcache(true, $link);

    // Check if link/file exists
    // Note: is_link() returns true for symlinks (even broken ones)
    // file_exists() returns true for files and directories (and valid symlinks to them)
    if (is_link($link)) {
        // It is a symlink, just unlink it
        if (!@unlink($link)) {
            echo "WARNING: Failed to unlink symlink $link\n";
        }
    } elseif (file_exists($link)) {
        // It is a real file or directory (not a symlink)
        if (is_dir($link)) {
            // Recursive delete for directories
            if (PHP_OS_FAMILY === 'Windows') {
                exec("rmdir /s /q " . escapeshellarg($link), $output, $returnVar);
            } else {
                exec("rm -rf " . escapeshellarg($link), $output, $returnVar);
            }
            if (isset($returnVar) && $returnVar !== 0) {
                echo "WARNING: Failed to remove directory $link\n";
            }
        } else {
            // Regular file
            if (!@unlink($link)) {
                echo "WARNING: Failed to unlink file $link\n";
            }
        }
    }

    // Ensure parent dir exists
    $parent = \dirname($link);
    if (!is_dir($parent)) {
        if (!mkdir($parent, 0777, true)) {
            echo "ERROR: Failed to create parent directory $parent\n";
            return;
        }
    }

    if (!$quiet) {
        echo "Linking $link -> $target\n";
    }
    
    if (!@symlink($target, $link)) {
        echo "ERROR: Failed to create symlink $link -> $target\n";
        $e = error_get_last();
        if ($e) {
            echo "  Details: " . $e['message'] . "\n";
        }
    }
}

/**
 * Builds the component package (ZIP file).
 *
 * @return void
 * @throws Exception If asset build fails or ZIP creation fails.
 */
function doBuild()
{
    // Build assets first
    echo "Building frontend assets...\n";
    passthru("npm install && npm run build", $returnVar);
    if ($returnVar !== 0) {
        throw new Exception("Asset build failed");
    }

    // Get version from proclaim.xml
    $xmlVersion = "10.0.x";
    if (file_exists(BASE_DIR . '/proclaim.xml')) {
        $xml = simplexml_load_file(BASE_DIR . '/proclaim.xml');
        if ($xml && isset($xml->version)) {
            $xmlVersion = (string) $xml->version;
        }
    }

    // Generate date-based version
    $dateVersion = date('Ymd');

    // Check if running in a non-interactive environment
    if (stream_isatty(STDIN)) {
        echo "\nSelect version to build:\n";
        echo "  [1] XML Version ($xmlVersion) - Default\n";
        echo "  [2] Date Version ($dateVersion)\n";
        echo "  [3] Custom Version\n";
        
        $choice = ask("Enter choice [1-3]", "1");
        
        switch ($choice) {
            case '2':
                $version = $dateVersion;
                break;
            case '3':
                $version = ask("Enter custom version");
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

    $zipFile = BUILD_DIR . "/com_proclaim-$version.zip";

    if (file_exists($zipFile)) {
        unlink($zipFile);
    }

    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
        throw new Exception("Cannot open <$zipFile>");
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(BASE_DIR, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $excludes = [
        'build.xml', 'build.properties', 'build.dist.properties', 'phpunit.xml', 'phpunit.xml.bak',
        '.php-cs-fixer.dist.php', 'CLAUDE.md', 'GEMINI.md', 'SECURITY.md', '_config.yml',
        '.git', '.vscode', '.idea', '.ds_store', 'node_modules', 'composer.json', 'composer.lock',
        'package.json', 'package-lock.json', 'build', 'tests',
        // Exclude internal symlinks created by doLink
        'admin/proclaim.xml',
        'admin/proclaim.script.php',
        'media/css/site/cwmcore.css',
    ];

    $includes    = ['admin/', 'media/', 'modules/', 'plugins/', 'site/', 'libraries/'];
    $includeExts = ['php', 'xml', 'txt', 'md'];

    foreach ($files as $name => $file) {
        if ($file->isDir()) {
            continue;
        }

        $filePath     = $file->getRealPath();
        // Normalize path separators to forward slashes
        $relativePath = str_replace('\\', '/', substr($filePath, \strlen(BASE_DIR) + 1));

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
            if (\in_array($ext, $includeExts) && !str_contains($relativePath, '/')) {
                // Root files
                $shouldInclude = true;
            }
        }

        if ($shouldInclude) {
            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();

    echo "\n" . str_repeat('=', 50) . "\n";
    echo "  BUILD COMPLETE\n";
    echo str_repeat('=', 50) . "\n";
    echo "  Version:  $version\n";
    echo "  Package:  " . basename($zipFile) . "\n";
    echo "  Location: $zipFile\n";
    echo str_repeat('=', 50) . "\n";
}

/**
 * Downloads and installs a specific version of Joomla.
 *
 * @return void
 */
function doInstallJoomla()
{
    $props       = getProperties();
    $version     = $props['joomla.version'] ?? '5.4.2';
    $installPath = rtrim($props['builder.joomla_path'], '/') . '/' . trim($props['builder.joomla_dir'], '/');
    $installPath = rtrim($installPath, '/');

    if (is_dir($installPath)) {
        $reinstall = ask("Directory $installPath already exists. Remove and reinstall? (y/n)", "n");
        if (strtolower($reinstall) !== 'y') {
            echo "Skipping installation.\n";
            return;
        }
        echo "Removing $installPath...\n";
        if (PHP_OS_FAMILY === 'Windows') {
            exec("rmdir /s /q " . escapeshellarg($installPath));
        } else {
            exec("rm -rf " . escapeshellarg($installPath));
        }
    }

    if (!is_dir($installPath)) {
        mkdir($installPath, 0777, true);
    }

    $url     = "https://github.com/joomla/joomla-cms/releases/download/$version/Joomla_$version-Stable-Full_Package.zip";
    $zipFile = BUILD_DIR . "/joomla-$version.zip";

    echo "Downloading Joomla $version...\n";
    copy($url, $zipFile);

    echo "Extracting to $installPath...\n";
    $zip = new ZipArchive();
    if ($zip->open($zipFile) === true) {
        $zip->extractTo($installPath);
        $zip->close();
        echo "Joomla installed.\n";
    } else {
        echo "Failed to extract Joomla.\n";
    }
    unlink($zipFile);
}

/**
 * Removes all symbolic links created by the link command.
 *
 * @return void
 */
function doClean()
{
    echo "Cleaning up development state...\n";

    // Internal symlinks
    $internalLinks = [
        BASE_DIR . '/admin/proclaim.xml',
        BASE_DIR . '/admin/proclaim.script.php',
        BASE_DIR . '/media/css/site/cwmcore.css',
    ];

    foreach ($internalLinks as $link) {
        if (is_link($link) || file_exists($link)) {
            unlink($link);
            echo "Removed: $link\n";
        }
    }

    // Try to get Joomla path for external links
    if (file_exists(PROPERTIES_FILE)) {
        try {
            $props      = getProperties();
            $joomlaPath = rtrim($props['builder.joomla_path'] ?? '', '/');
            if (!empty($props['builder.joomla_dir'])) {
                $joomlaPath .= '/' . trim($props['builder.joomla_dir'], '/');
            }
            $joomlaPath = rtrim($joomlaPath, '/');

            if (!empty($joomlaPath) && is_dir($joomlaPath)) {
                $externalLinks = [
                    "$joomlaPath/media/com_proclaim",
                    "$joomlaPath/administrator/components/com_proclaim",
                    "$joomlaPath/components/com_proclaim",
                    "$joomlaPath/modules/mod_proclaim",
                    "$joomlaPath/administrator/modules/mod_proclaimicon",
                    "$joomlaPath/modules/mod_proclaim_podcast",
                    "$joomlaPath/modules/mod_proclaim_youtube",
                    "$joomlaPath/plugins/finder/proclaim",
                    "$joomlaPath/plugins/task/proclaim",
                ];

                foreach ($externalLinks as $link) {
                    if (is_link($link)) {
                        unlink($link);
                        echo "Removed: $link\n";
                    }
                }
            }
        } catch (Exception $e) {
            // Ignore if properties can't be read
        }
    }

    echo "Clean complete.\n";
}

/**
 * Fetches and displays the latest available Joomla version from GitHub.
 *
 * @return void
 * @throws Exception If the GitHub API request fails.
 */
function doJoomlaLatest()
{
    echo "Fetching latest Joomla version from GitHub...\n";

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'User-Agent: Proclaim-Build-Tool',
        ],
    ]);

    $json = @file_get_contents('https://api.github.com/repos/joomla/joomla-cms/releases/latest', false, $context);

    if ($json === false) {
        throw new Exception("Failed to fetch from GitHub API");
    }

    $data = json_decode($json, true);
    if (!$data || !isset($data['tag_name'])) {
        throw new Exception("Invalid response from GitHub API");
    }

    $version   = $data['tag_name'];
    $published = $data['published_at'] ?? 'unknown';

    echo "\nLatest Joomla Version: $version\n";
    echo "Published: $published\n";
    echo "\nTo install: composer joomla-install\n";
}

/**
 * Checks all PHP files in the project for syntax errors.
 *
 * @return void
 */
function doLintSyntax()
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
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filePath = $file->getRealPath();
            $fileCount++;

            // Run php -l on the file
            $output    = [];
            $returnVar = 0;
            exec('php -l ' . escapeshellarg($filePath) . ' 2>&1', $output, $returnVar);

            if ($returnVar !== 0) {
                $errors[] = [
                    'file'  => str_replace(BASE_DIR . '/', '', $filePath),
                    'error' => implode("\n", $output),
                ];
            }
        }
    }

    echo "Checked $fileCount files.\n";

    if (\count($errors) > 0) {
        echo "\nSyntax errors found:\n";
        echo str_repeat('-', 60) . "\n";
        foreach ($errors as $error) {
            echo "File: {$error['file']}\n";
            echo "{$error['error']}\n\n";
        }
        exit(1);
    }

    echo "No syntax errors detected.\n";
}
