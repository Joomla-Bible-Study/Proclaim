#!/usr/bin/env php
<?php

// phpcs:disable PSR1.Files.SideEffects
/**
 * Proclaim Build Script
 * Replaces Phing build.xml
 */

const BASE_DIR        = __DIR__ . '/..';
const BUILD_DIR       = BASE_DIR . '/build';
const PROPERTIES_FILE = BASE_DIR . '/build.properties';

$command = $argv[1] ?? 'help';
$verbose = \in_array('--verbose', $argv, true) || \in_array('-v', $argv, true);

try {
    switch ($command) {
        case 'setup':
            doSetup();
            break;
        case 'link':
            doLink(verbose: $verbose);
            break;
        case 'clean':
            doClean($verbose);
            break;
        case 'build':
            doBuild($verbose);
            break;
        case 'package':
            doPackage($verbose);
            break;
        case 'install-joomla':
            doInstallJoomla();
            break;
        case 'joomla-latest':
            doJoomlaLatest();
            break;
        case 'verify':
            doVerifyExtensions($verbose);
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

/**
 * Displays the help message with available commands.
 *
 * @return void
 * @since 10.1.0
 */
function showHelp(): void
{
    echo "Proclaim Build Tool\n";
    echo "Usage: php build/proclaim_build.php [command]\n\n";
    echo "Commands:\n";
    echo "  setup           Interactive setup wizard for build.properties\n";
    echo "  link            Setup symbolic links to local Joomla installation(s)\n";
    echo "  clean           Remove symbolic links (clean dev state)\n";
    echo "  build           Build component package (zip)\n";
    echo "  package         Build pkg_proclaim (library + plugin + component zips)\n";
    echo "  install-joomla  Download and install Joomla\n";
    echo "  joomla-latest   Show latest available Joomla version\n";
    echo "  verify          Verify all sub-extensions are registered in dev Joomla DB(s)\n";
    echo "  lint-syntax     Check PHP syntax errors\n";
    echo "\nOptions:\n";
    echo "  -v, --verbose   Show detailed output (e.g., each symlink path)\n";
    echo "\nMultiple Joomla paths are supported via builder.joomla_paths (comma-separated)\n";
    echo "in build.properties. The singular builder.joomla_path is also supported.\n";
}

/**
 * Reads and parses the build.properties file.
 *
 * @return array Associative array of properties.
 * @throws Exception If build.properties does not exist.
 * @since 10.1.0
 */
function getProperties(): array
{
    if (!file_exists(PROPERTIES_FILE)) {
        throw new \RuntimeException("build.properties not found. Run 'composer setup' first.");
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
        [$key, $value]     = explode('=', $line, 2);
        $props[trim($key)] = trim($value);
    }
    return $props;
}

/**
 * Returns an array of Joomla installation paths from build.properties.
 *
 * Checks `builder.joomla_paths` (plural, comma-separated) first, then falls
 * back to `builder.joomla_path` (singular) for backward compatibility.
 * Appends `builder.joomla_dir` to each path if set.
 *
 * @param   array  $props  Properties array from getProperties().
 *
 * @return array List of resolved Joomla paths.
 * @since 10.1.0
 */
function getJoomlaPaths(array $props): array
{
    $raw = '';

    // Prefer plural form (comma-separated), fall back to singular
    if (!empty($props['builder.joomla_paths'])) {
        $raw = $props['builder.joomla_paths'];
    } elseif (!empty($props['builder.joomla_path'])) {
        $raw = $props['builder.joomla_path'];
    }

    if ($raw === '') {
        return [];
    }

    $dir   = trim($props['builder.joomla_dir'] ?? '', '/');
    $paths = [];

    foreach (explode(',', $raw) as $entry) {
        $entry = trim($entry);
        if ($entry === '') {
            continue;
        }
        $path = rtrim($entry, '/');
        if ($dir !== '') {
            $path .= '/' . $dir;
        }
        $paths[] = $path;
    }

    return $paths;
}

/**
 * Returns the list of external symlink mappings for a given Joomla path.
 *
 * @param   string  $joomlaPath  The resolved Joomla installation path.
 *
 * @return array Associative array of target => link.
 * @since 10.1.0
 */
function getExternalLinks(string $joomlaPath): array
{
    return [
        BASE_DIR . '/media'                                           => "$joomlaPath/media/com_proclaim",
        BASE_DIR . '/admin'                                           => "$joomlaPath/administrator/components/com_proclaim",
        BASE_DIR . '/site'                                            => "$joomlaPath/components/com_proclaim",
        BASE_DIR . '/modules/site/mod_proclaim'                       => "$joomlaPath/modules/mod_proclaim",
        BASE_DIR . '/modules/admin/mod_proclaimicon'                  => "$joomlaPath/administrator/modules/mod_proclaimicon",
        BASE_DIR . '/modules/site/mod_proclaim_podcast'               => "$joomlaPath/modules/mod_proclaim_podcast",
        BASE_DIR . '/modules/site/mod_proclaim_youtube'               => "$joomlaPath/modules/mod_proclaim_youtube",
        BASE_DIR . '/plugins/finder/proclaim'                         => "$joomlaPath/plugins/finder/proclaim",
        BASE_DIR . '/plugins/schemaorg/proclaim'                      => "$joomlaPath/plugins/schemaorg/proclaim",
        BASE_DIR . '/plugins/system/proclaim'                         => "$joomlaPath/plugins/system/proclaim",
        BASE_DIR . '/plugins/task/proclaim'                           => "$joomlaPath/plugins/task/proclaim",
        BASE_DIR . '/admin/language/en-GB/en-GB.com_proclaim.ini'     => "$joomlaPath/administrator/language/en-GB/en-GB.com_proclaim.ini",
        BASE_DIR . '/admin/language/en-GB/en-GB.com_proclaim.sys.ini' => "$joomlaPath/administrator/language/en-GB/en-GB.com_proclaim.sys.ini",
        // CWM Scripture Library (separate submodule)
        BASE_DIR . '/libraries/lib_cwmscripture'                              => "$joomlaPath/libraries/cwmscripture",
        BASE_DIR . '/libraries/lib_cwmscripture/cwmscripture.xml'             => "$joomlaPath/administrator/manifests/libraries/cwmscripture.xml",
        BASE_DIR . '/libraries/lib_cwmscripture/media/lib_cwmscripture'       => "$joomlaPath/media/lib_cwmscripture",
        // ScriptureLinks plugin (separate submodule)
        BASE_DIR . '/plugins/content/scripturelinks' => "$joomlaPath/plugins/content/scripturelinks",
    ];
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

    // Countdown timer with single-keypress detection
    if ($timeout > 0 && $default !== null && stream_isatty(STDIN)) {
        $oldStty = trim((string) shell_exec('stty -g 2>/dev/null'));
        system('stty cbreak -echo 2>/dev/null');

        for ($remaining = $timeout; $remaining > 0; $remaining--) {
            // Overwrite line with updated countdown
            echo "\r" . $prompt . " ({$remaining}s): ";

            $read   = [STDIN];
            $write  = null;
            $except = null;
            $ready  = @stream_select($read, $write, $except, 1);

            if ($ready > 0) {
                $char = fread(STDIN, 1);
                system('stty ' . escapeshellarg($oldStty) . ' 2>/dev/null');
                echo "\r" . $prompt . ': ' . $char . "    \n";
                return $char === '' ? $default : $char;
            }
        }

        // Timeout — no input
        system('stty ' . escapeshellarg($oldStty) . ' 2>/dev/null');
        echo "\r" . $prompt . ': ' . $default . " (auto)\n";
        return $default;
    }

    echo $prompt . ': ';

    $handle = fopen('php://stdin', 'rb');
    $line   = fgets($handle);
    fclose($handle);
    $line = trim($line);
    return $line === '' ? $default : $line;
}

/**
 * Runs the interactive setup wizard to configure build.properties.
 *
 * @return void
 * @throws Exception
 * @since 10.1.0
 */
function doSetup(): void
{
    echo "=== Proclaim Development Setup Wizard ===\n\n";

    $currentProps = file_exists(PROPERTIES_FILE) ? getProperties() : [];

    // Collect multiple Joomla paths
    $existingPaths = '';
    if (!empty($currentProps['builder.joomla_paths'])) {
        $existingPaths = $currentProps['builder.joomla_paths'];
    } elseif (!empty($currentProps['builder.joomla_path'])) {
        $existingPaths = $currentProps['builder.joomla_path'];
    }

    echo "Enter Joomla installation paths (one per prompt, blank when done):\n";
    if ($existingPaths !== '') {
        echo "  Current: $existingPaths\n";
    }

    $existing = $existingPaths !== ''
        ? array_map('trim', explode(',', $existingPaths))
        : [];

    $paths = [];
    $i     = 1;
    while (true) {
        $default = $existing[$i - 1] ?? null;
        $label   = "  Joomla path #$i";
        if ($default === null && $i > 1) {
            $label .= ' (blank to finish)';
        }
        $path = ask($label, $default);
        if ($path === null || $path === '') {
            break;
        }
        $paths[] = $path;
        $i++;
    }

    if (\count($paths) === 0) {
        echo "No paths entered. At least one Joomla path is required.\n";
        return;
    }

    $joomlaDir     = ask('Enter subdirectory within Joomla path (leave empty if none)', $currentProps['builder.joomla_dir'] ?? '');
    $joomlaVersion = ask('Enter the default Joomla version for testing', $currentProps['joomla.version'] ?? '5.4.2');

    // Per-site configuration for J5 and J6
    $sites      = ['j5dev' => 'Joomla 5', 'j6dev' => 'Joomla 6'];
    $siteConfig = [];

    foreach ($sites as $key => $label) {
        echo "\n--- $label Development Site ---\n";
        $prefix           = "builder.$key";
        $siteConfig[$key] = [
            'url'      => ask("$label site URL", $currentProps["$prefix.url"] ?? "https://$key.local:8890"),
            'db_host'  => ask("$label database host", $currentProps["$prefix.db_host"] ?? 'localhost'),
            'db_user'  => ask("$label database username", $currentProps["$prefix.db_user"] ?? ''),
            'db_pass'  => ask("$label database password", $currentProps["$prefix.db_pass"] ?? ''),
            'db_name'  => ask("$label database name", $currentProps["$prefix.db_name"] ?? ''),
            'username' => ask("$label admin username", $currentProps["$prefix.username"] ?? 'admin'),
            'password' => ask("$label admin password", $currentProps["$prefix.password"] ?? 'admin'),
            'email'    => ask("$label admin email", $currentProps["$prefix.email"] ?? 'admin@example.com'),
        ];
    }

    // Start from the dist template so all comments are preserved
    $distFile = BUILD_DIR . '/../../build.dist.properties';

    if (!file_exists($distFile)) {
        $distFile = BASE_DIR . '/build.dist.properties';
    }

    if (!file_exists($distFile)) {
        throw new \RuntimeException('build.dist.properties not found.');
    }

    $content = file_get_contents($distFile);

    // Replace template values with user input
    $replacements = [
        'builder.joomla_paths' => implode(',', $paths),
        'builder.joomla_dir'   => $joomlaDir,
        'joomla.version'       => $joomlaVersion,
    ];

    foreach ($siteConfig as $key => $cfg) {
        $prefix                           = "builder.$key";
        $replacements["$prefix.url"]      = $cfg['url'];
        $replacements["$prefix.db_host"]  = $cfg['db_host'];
        $replacements["$prefix.db_user"]  = $cfg['db_user'];
        $replacements["$prefix.db_pass"]  = $cfg['db_pass'];
        $replacements["$prefix.db_name"]  = $cfg['db_name'];
        $replacements["$prefix.username"] = $cfg['username'];
        $replacements["$prefix.password"] = $cfg['password'];
        $replacements["$prefix.email"]    = $cfg['email'];
    }

    foreach ($replacements as $key => $value) {
        // Replace "key=anything" with "key=value", preserving the line
        $content = preg_replace(
            '/^(' . preg_quote($key, '/') . ')=.*$/m',
            '$1=' . $value,
            $content
        );
    }

    file_put_contents(PROPERTIES_FILE, $content);
    echo "\nConfiguration saved to build.properties\n";

    $install = ask('Do you want to download and install Joomla? (y/n)', 'n');
    if (strtolower($install) === 'y') {
        doInstallJoomla();
    }
}

/**
 * Creates symbolic links between the project and a local Joomla installation.
 *
 * @param   bool  $quiet  If true, suppresses non-error output.
 *
 * @return void
 * @throws Exception If no Joomla paths are configured.
 * @since 10.1.0
 */
function doLink(bool $quiet = false, bool $verbose = false): void
{
    $props       = getProperties();
    $joomlaPaths = getJoomlaPaths($props);

    if (\count($joomlaPaths) === 0) {
        throw new \RuntimeException('No Joomla paths configured. Run \'composer setup\' first.');
    }

    // Internal links (dev.init) — run once
    $silent = !$verbose;
    symlink_force(BASE_DIR . '/proclaim.xml', BASE_DIR . '/admin/proclaim.xml', $silent);
    symlink_force(BASE_DIR . '/proclaim.script.php', BASE_DIR . '/admin/proclaim.script.php', $silent);
    if (
        !is_dir(BASE_DIR . '/media/css/site') && !mkdir(
            $concurrentDirectory = BASE_DIR . '/media/css/site',
            0777,
            true
        ) && !is_dir($concurrentDirectory)
    ) {
        throw new \RuntimeException(\sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
    symlink_force(BASE_DIR . '/media/css/cwmcore.css', BASE_DIR . '/media/css/site/cwmcore.css', $silent);

    if (!$quiet) {
        echo "Internal links created.\n";
    }

    // External links — iterate all Joomla paths
    $linked = 0;
    foreach ($joomlaPaths as $joomlaPath) {
        if (!is_dir($joomlaPath)) {
            echo "WARNING: Path not found, skipping: $joomlaPath\n";
            continue;
        }

        if (!$quiet) {
            echo "\nLinked to: $joomlaPath\n";
        }

        foreach (getExternalLinks($joomlaPath) as $target => $link) {
            symlink_force($target, $link, $silent);
        }

        if (!$quiet && !$verbose) {
            echo "  Component:  admin, site, media\n";
            echo "  Libraries:  lib_cwmscripture (src + media)\n";
            echo "  Modules:    mod_proclaim, mod_proclaimicon, mod_proclaim_podcast, mod_proclaim_youtube\n";
            echo "  Plugins:    content/scripturelinks, finder, system, task\n";
            echo "  Language:   en-GB.com_proclaim.ini, en-GB.com_proclaim.sys.ini\n";
        }
        $linked++;
    }

    if (!$quiet) {
        echo "\nDone! Symlinks created for $linked Joomla installation" . ($linked !== 1 ? 's' : '') . ".\n";
    }
}

/**
 * Forces creation of a symbolic link, removing any existing file or directory at the link path.
 *
 * @param   string  $target  The target path the link should point to.
 * @param   string  $link    The path where the link should be created.
 * @param   bool    $quiet   If true, suppresses success messages.
 *
 * @return void
 * @since 10.1.0
 */
function symlink_force(string $target, string $link, bool $quiet = false): void
{
    // Clear the file status cache to ensure we get fresh results
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
                exec('rmdir /s /q ' . escapeshellarg($link), $output, $returnVar);
            } else {
                exec('rm -rf ' . escapeshellarg($link), $output, $returnVar);
            }
            if (isset($returnVar) && $returnVar !== 0) {
                echo "WARNING: Failed to remove directory $link\n";
            }
        } elseif (!@unlink($link)) {
            // Regular file
            echo "WARNING: Failed to unlink file $link\n";
        }
    }

    // Ensure parent dir exists
    $parent = \dirname($link);
    if (!is_dir($parent) && !mkdir($parent, 0777, true) && !is_dir($parent)) {
        echo "ERROR: Failed to create parent directory $parent\n";
        return;
    }

    if (!$quiet) {
        echo "Linking $link -> $target\n";
    }

    if (!@symlink($target, $link)) {
        echo "ERROR: Failed to create symlink $link -> $target\n";
        $e = error_get_last();
        if ($e) {
            echo '  Details: ' . $e['message'] . "\n";
        }
    }
}

/**
 * Read Joomla configuration.php and return DB connection details + table prefix.
 *
 * @param   string  $joomlaPath  Path to Joomla installation root
 *
 * @return  array{host: string, user: string, password: string, db: string, dbprefix: string}|null
 *
 * @since  10.3.0
 */
function getJoomlaDbConfig(string $joomlaPath): ?array
{
    $configFile = $joomlaPath . '/configuration.php';

    if (!file_exists($configFile)) {
        return null;
    }

    // Load JConfig class from configuration.php
    $content = file_get_contents($configFile);
    // Avoid redeclaring JConfig if already loaded from another site
    $content = str_replace('class JConfig', 'class JConfig_' . md5($joomlaPath), $content);
    $content = str_replace('<?php', '', $content);
    eval($content);

    $className = 'JConfig_' . md5($joomlaPath);

    if (!class_exists($className)) {
        return null;
    }

    $config = new $className();

    return [
        'host'     => $config->host ?? 'localhost',
        'user'     => $config->user ?? '',
        'password' => $config->password ?? '',
        'db'       => $config->db ?? '',
        'dbprefix' => $config->dbprefix ?? 'jos_',
    ];
}

/**
 * Verify and register all Proclaim sub-extensions in each dev Joomla database.
 *
 * Checks libraries, plugins, and modules from the install action queue are
 * registered in #__extensions. Inserts missing entries and runs install SQL
 * for libraries. Also enables plugins that should be enabled.
 *
 * @param   bool  $verbose  Show detailed output
 *
 * @return  void
 *
 * @since  10.3.0
 */
function doVerifyExtensions(bool $verbose = false): void
{
    $props       = getProperties();
    $joomlaPaths = getJoomlaPaths($props);

    if (\count($joomlaPaths) === 0) {
        throw new \RuntimeException('No Joomla paths configured. Run \'composer setup\' first.');
    }

    // Define all expected extensions
    $expected = [
        // Libraries
        ['type' => 'library', 'element' => 'cwmscripture', 'name' => 'lib_cwmscripture', 'folder' => '', 'enabled' => 1, 'locked' => 1],
        // Content plugin (from ScriptureLinks)
        ['type' => 'plugin', 'element' => 'scripturelinks', 'name' => 'plg_content_scripturelinks', 'folder' => 'content', 'enabled' => 1, 'locked' => 0],
        // Proclaim plugins
        ['type' => 'plugin', 'element' => 'proclaim', 'name' => 'plg_finder_proclaim', 'folder' => 'finder', 'enabled' => 1, 'locked' => 0],
        ['type' => 'plugin', 'element' => 'proclaim', 'name' => 'plg_schemaorg_proclaim', 'folder' => 'schemaorg', 'enabled' => 1, 'locked' => 0],
        ['type' => 'plugin', 'element' => 'proclaim', 'name' => 'plg_system_proclaim', 'folder' => 'system', 'enabled' => 1, 'locked' => 0],
        ['type' => 'plugin', 'element' => 'proclaim', 'name' => 'plg_task_proclaim', 'folder' => 'task', 'enabled' => 1, 'locked' => 0],
        // Component
        ['type' => 'component', 'element' => 'com_proclaim', 'name' => 'com_proclaim', 'folder' => '', 'enabled' => 1, 'locked' => 0],
    ];

    foreach ($joomlaPaths as $joomlaPath) {
        if (!is_dir($joomlaPath)) {
            echo "WARNING: Path not found, skipping: $joomlaPath\n";
            continue;
        }

        echo "\n=== Verifying: $joomlaPath ===\n";

        $dbConfig = getJoomlaDbConfig($joomlaPath);

        if ($dbConfig === null) {
            echo "  ERROR: Could not read configuration.php\n";
            continue;
        }

        try {
            $pdo = new PDO(
                'mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['db'] . ';charset=utf8mb4',
                $dbConfig['user'],
                $dbConfig['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            echo "  ERROR: DB connection failed: " . $e->getMessage() . "\n";
            continue;
        }

        $prefix  = $dbConfig['dbprefix'];
        $ok      = 0;
        $fixed   = 0;
        $errors  = 0;

        // Check if the extensions table has a namespace column (Joomla 6+)
        $nsCheck      = $pdo->query("SHOW COLUMNS FROM {$prefix}extensions LIKE 'namespace'");
        $hasNamespace = $nsCheck && $nsCheck->rowCount() > 0;

        foreach ($expected as $ext) {
            $type    = $ext['type'];
            $element = $ext['element'];
            $folder  = $ext['folder'];
            $name    = $ext['name'];

            // Check if extension exists in #__extensions
            $sql    = "SELECT extension_id, enabled, locked FROM {$prefix}extensions WHERE type = ? AND element = ?";
            $params = [$type, $element];

            if ($type === 'plugin' && $folder !== '') {
                $sql .= ' AND folder = ?';
                $params[] = $folder;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Extension exists — check enabled/locked state
                $needsUpdate = false;
                $updates     = [];

                if ((int) $row['enabled'] !== $ext['enabled']) {
                    $updates[]   = "enabled = {$ext['enabled']}";
                    $needsUpdate = true;
                }

                if ($ext['locked'] && (int) $row['locked'] !== 1) {
                    $updates[]   = 'locked = 1';
                    $needsUpdate = true;
                }

                if ($needsUpdate) {
                    $updateSql = "UPDATE {$prefix}extensions SET " . implode(', ', $updates)
                        . " WHERE extension_id = " . (int) $row['extension_id'];
                    $pdo->exec($updateSql);
                    echo "  FIXED:  $name ($type) — updated " . implode(', ', $updates) . "\n";
                    $fixed++;
                } else {
                    if ($verbose) {
                        echo "  OK:     $name ($type)\n";
                    }
                    $ok++;
                }
            } else {
                // Extension missing — register it
                if ($type === 'library') {
                    // Register a library and run install SQL
                    $manifest  = '{"name":"lib_cwmscripture","libraryname":"cwmscripture","version":"1.0.0"}';

                    if ($hasNamespace) {
                        $namespace = 'CWM\\\\Library\\\\Scripture';
                        $insertSql = "INSERT INTO {$prefix}extensions "
                            . "(name, type, element, folder, client_id, enabled, access, locked, manifest_cache, params, custom_data, namespace) "
                            . "VALUES (?, 'library', ?, '', 0, 1, 1, ?, ?, '{}', '', ?)";
                        $stmt = $pdo->prepare($insertSql);
                        $stmt->execute([$name, $element, $ext['locked'], $manifest, $namespace]);
                    } else {
                        $insertSql = "INSERT INTO {$prefix}extensions "
                            . "(name, type, element, folder, client_id, enabled, access, locked, manifest_cache, params, custom_data) "
                            . "VALUES (?, 'library', ?, '', 0, 1, 1, ?, ?, '{}', '')";
                        $stmt = $pdo->prepare($insertSql);
                        $stmt->execute([$name, $element, $ext['locked'], $manifest]);
                    }

                    // Run install SQL for tables
                    $installSql = BASE_DIR . '/libraries/lib_cwmscripture/sql/install.mysql.utf8.sql';

                    if (file_exists($installSql)) {
                        $sql = file_get_contents($installSql);
                        $sql = str_replace('#__', $prefix, $sql);
                        // Execute each statement separately
                        $statements = array_filter(array_map('trim', explode(';', $sql)));

                        foreach ($statements as $statement) {
                            if ($statement !== '' && !str_starts_with($statement, '--')) {
                                try {
                                    $pdo->exec($statement);
                                } catch (PDOException $e) {
                                    // Table may already exist — that's fine
                                    if ($verbose) {
                                        echo "  NOTE:   SQL: " . substr($e->getMessage(), 0, 80) . "\n";
                                    }
                                }
                            }
                        }
                    }

                    echo "  ADDED:  $name (library) — registered + tables created\n";
                    $fixed++;
                } elseif ($type === 'plugin') {
                    if ($hasNamespace) {
                        $namespace = match ($folder) {
                            'content'   => 'CWM\\\\Plugin\\\\Content\\\\ScriptureLinks',
                            'finder'    => 'CWM\\\\Plugin\\\\Finder\\\\Proclaim',
                            'system'    => 'CWM\\\\Plugin\\\\System\\\\Proclaim',
                            'task'      => 'CWM\\\\Plugin\\\\Task\\\\Proclaim',
                            'schemaorg' => 'CWM\\\\Plugin\\\\Schemaorg\\\\Proclaim',
                            default     => '',
                        };
                        $insertSql = "INSERT INTO {$prefix}extensions "
                            . "(name, type, element, folder, client_id, enabled, access, locked, manifest_cache, params, custom_data, namespace) "
                            . "VALUES (?, 'plugin', ?, ?, 0, ?, 1, 0, '{}', '{}', '', ?)";
                        $stmt = $pdo->prepare($insertSql);
                        $stmt->execute([$name, $element, $folder, $ext['enabled'], $namespace]);
                    } else {
                        $insertSql = "INSERT INTO {$prefix}extensions "
                            . "(name, type, element, folder, client_id, enabled, access, locked, manifest_cache, params, custom_data) "
                            . "VALUES (?, 'plugin', ?, ?, 0, ?, 1, 0, '{}', '{}', '')";
                        $stmt = $pdo->prepare($insertSql);
                        $stmt->execute([$name, $element, $folder, $ext['enabled']]);
                    }
                    echo "  ADDED:  $name (plugin/$folder)\n";
                    $fixed++;
                } elseif ($type === 'component') {
                    // Component should already exist if Proclaim is installed
                    echo "  MISS:   $name (component) — install Proclaim via Extension Manager first\n";
                    $errors++;
                }
            }
        }

        echo "  Summary: $ok OK, $fixed fixed, $errors errors\n";
    }

    echo "\nDone.\n";
}

/**
 * Builds the component package (ZIP file).
 *
 * @param   bool  $verbose  If true, lists each file added to the package.
 *
 * @return void
 * @throws Exception If asset build fails or ZIP creation fails.
 * @since 10.1.0
 */
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

    $zipFile = BUILD_DIR . "/com_proclaim-$version.zip";

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
        'media/css/site/cwmcore.css',
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

    foreach (glob(BUILD_DIR . '/com_proclaim-*.zip') as $candidate) {
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
    $pkgZipPath = BUILD_DIR . '/pkg_proclaim-' . $version . '.zip';

    if (file_exists($pkgZipPath)) {
        unlink($pkgZipPath);
    }

    $pkgZip = new ZipArchive();

    if ($pkgZip->open($pkgZipPath, ZipArchive::CREATE) !== true) {
        throw new \RuntimeException("Cannot create $pkgZipPath");
    }

    $pkgZip->addFile(BUILD_DIR . '/pkg_proclaim.xml', 'pkg_proclaim.xml');
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
 * Downloads and installs a specific version of Joomla.
 *
 * @return void
 * @throws Exception
 * @since 10.1.0
 */
function doInstallJoomla(): void
{
    $props          = getProperties();
    $defaultVersion = $props['joomla.version'] ?? '5.4.2';
    $joomlaPaths    = getJoomlaPaths($props);

    if (\count($joomlaPaths) === 0) {
        // Fallback for legacy config without paths
        $joomlaPaths = [rtrim($props['builder.joomla_path'] ?? '', '/') . '/' . trim($props['builder.joomla_dir'] ?? '', '/')];
        $joomlaPaths = [rtrim($joomlaPaths[0], '/')];
    }

    foreach ($joomlaPaths as $installPath) {
        echo "\nInstall target: $installPath\n";

        $version = ask("  Joomla version", $defaultVersion);

        if (is_dir($installPath)) {
            $reinstall = ask("  Directory exists. Remove and reinstall? (y/n)", 'n');
            if (strtolower($reinstall) !== 'y') {
                echo "  Skipped.\n";
                continue;
            }
            if (PHP_OS_FAMILY === 'Windows') {
                exec('rmdir /s /q ' . escapeshellarg($installPath));
            } else {
                exec('rm -rf ' . escapeshellarg($installPath));
            }
        }

        if (!is_dir($installPath) && !mkdir($installPath, 0777, true) && !is_dir($installPath)) {
            echo "  ERROR: Failed to create directory.\n";
            continue;
        }

        $url     = "https://github.com/joomla/joomla-cms/releases/download/$version/Joomla_$version-Stable-Full_Package.zip";
        $zipFile = BUILD_DIR . "/joomla-$version.zip";

        echo "  Downloading Joomla $version...";
        copy($url, $zipFile);

        $zip = new ZipArchive();
        if ($zip->open($zipFile) === true) {
            $zip->extractTo($installPath);
            $zip->close();
            echo " installed.\n";
        } else {
            echo " FAILED to extract.\n";
        }
        if (file_exists($zipFile)) {
            unlink($zipFile);
        }
    }
}

/**
 * Removes all symbolic links created by the link command.
 *
 * @param   bool  $verbose  If true, prints each removed path.
 *
 * @return void
 * @since 10.1.0
 */
function doClean(bool $verbose = false): void
{
    echo "Cleaning up development state...\n";

    // Internal symlinks
    $internalLinks = [
        BASE_DIR . '/admin/proclaim.xml',
        BASE_DIR . '/admin/proclaim.script.php',
        BASE_DIR . '/media/css/site/cwmcore.css',
    ];

    $removed = 0;
    foreach ($internalLinks as $link) {
        if (is_link($link) || file_exists($link)) {
            unlink($link);
            $removed++;
            if ($verbose) {
                echo "Removed: $link\n";
            }
        }
    }
    echo "Internal links removed ($removed).\n";

    // External symlinks — remove from ALL configured Joomla paths
    if (file_exists(PROPERTIES_FILE)) {
        try {
            $props       = getProperties();
            $joomlaPaths = getJoomlaPaths($props);

            foreach ($joomlaPaths as $joomlaPath) {
                if (!is_dir($joomlaPath)) {
                    continue;
                }

                $count = 0;
                foreach (getExternalLinks($joomlaPath) as $link) {
                    if (is_link($link)) {
                        unlink($link);
                        $count++;
                        if ($verbose) {
                            echo "  Removed: $link\n";
                        }
                    }
                }
                echo "\nCleaned: $joomlaPath ($count symlinks)\n";
            }
        } catch (Exception $e) {
            // Ignore if properties can't be read
        }
    }

    echo "\nClean complete.\n";
}

/**
 * Fetches and displays the latest available Joomla version from GitHub.
 *
 * @return void
 * @throws Exception If the GitHub API request fails.
 * @since 10.1.0
 */
function doJoomlaLatest(): void
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
        throw new \RuntimeException('Failed to fetch from GitHub API');
    }

    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    if (!$data || !isset($data['tag_name'])) {
        throw new \RuntimeException('Invalid response from GitHub API');
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
