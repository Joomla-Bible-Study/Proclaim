<?php

/**
 * Install Composer dependencies in the joomla-cms clone for unit testing.
 *
 * Reads builder.joomla_dir from build.properties and runs `composer install`
 * inside that directory if vendor/autoload.php is missing.
 *
 * Called automatically via `composer install --dev` (post-install-cmd).
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.3.0
 */

$root      = dirname(__DIR__);
$propsFile = $root . '/build.properties';

if (!file_exists($propsFile)) {
    // No build.properties yet — skip silently (will be created by setup)
    return;
}

// Parse build.properties for joomla_dir
$joomlaDir = '';
$lines     = file($propsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

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

    if ($key === 'builder.joomla_dir') {
        $joomlaDir = $value;
        break;
    }
}

if ($joomlaDir === '') {
    echo "  \033[33mSkipping joomla-cms deps: builder.joomla_dir not set in build.properties\033[0m" . PHP_EOL;
    echo "  Set it to your joomla-cms clone path to enable unit testing." . PHP_EOL;

    return;
}

if (!is_dir($joomlaDir)) {
    echo "  \033[33mSkipping joomla-cms deps: directory not found: $joomlaDir\033[0m" . PHP_EOL;
    echo "  Clone it: git clone https://github.com/joomla/joomla-cms.git $joomlaDir" . PHP_EOL;

    return;
}

$vendorAutoload = rtrim($joomlaDir, '/') . '/libraries/vendor/autoload.php';

if (file_exists($vendorAutoload)) {
    echo "  \033[32m✓ joomla-cms dependencies already installed\033[0m" . PHP_EOL;

    return;
}

echo "  Installing joomla-cms Composer dependencies..." . PHP_EOL;

$composerJson = rtrim($joomlaDir, '/') . '/composer.json';

if (!file_exists($composerJson)) {
    echo "  \033[31m✗ No composer.json found in $joomlaDir\033[0m" . PHP_EOL;

    return;
}

// Run composer install inside the joomla-cms directory
$cmd    = sprintf('cd %s && composer install --no-interaction --quiet 2>&1', escapeshellarg($joomlaDir));
$output = [];
$code   = 0;

exec($cmd, $output, $code);

if ($code === 0) {
    echo "  \033[32m✓ joomla-cms dependencies installed successfully\033[0m" . PHP_EOL;
} else {
    echo "  \033[31m✗ Failed to install joomla-cms dependencies (exit code: $code)\033[0m" . PHP_EOL;

    foreach ($output as $line) {
        echo "    $line" . PHP_EOL;
    }
}
