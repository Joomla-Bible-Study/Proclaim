<?php

/**
 * Ensure a Joomla CMS source clone exists for unit testing.
 *
 * Resolves the clone path in this order:
 *   1. tests.joomla_cms_path in build.properties (explicit override)
 *   2. ../joomla-cms sibling of this repo (default)
 * Clones a known-stable Joomla tag into the resolved location if it does not
 * already exist. Called automatically via `composer install --dev`
 * (post-install-cmd).
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.3.0
 */

$root      = \dirname(__DIR__);
$propsFile = $root . '/build.properties';

// Parse build.properties (if it exists) for an explicit override.
$joomlaDir = '';

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

        if ($key === 'tests.joomla_cms_path' && $value !== '') {
            $joomlaDir = $value;
            break;
        }
    }
}

// Default to ../joomla-cms if no override. We don't write this back to
// build.properties — the path is derived, not configured. Anyone who wants
// to pin a custom location can add tests.joomla_cms_path themselves.
if ($joomlaDir === '') {
    $joomlaDir = \dirname($root) . '/joomla-cms';
}

if (!is_dir($joomlaDir)) {
    echo "  Cloning joomla-cms (one-time setup)..." . PHP_EOL;

    // Clone the 5.4.3 stable tag — known-compatible with our framework v4.0 packages.
    // Using a stable tag ensures consistent class signatures across environments.
    $cmd    = \sprintf('git clone --depth 1 --branch 5.4.3 https://github.com/joomla/joomla-cms.git %s 2>&1', escapeshellarg($joomlaDir));
    $output = [];
    $code   = 0;

    exec($cmd, $output, $code);

    if ($code !== 0) {
        echo "  \033[31m✗ Failed to clone joomla-cms (exit code: $code)\033[0m" . PHP_EOL;

        foreach ($output as $line) {
            echo "    $line" . PHP_EOL;
        }

        return;
    }

    echo "  \033[32m✓ joomla-cms cloned to $joomlaDir\033[0m" . PHP_EOL;
}

// Verify the clone has the required files
$loaderFile = rtrim($joomlaDir, '/') . '/libraries/loader.php';

if (file_exists($loaderFile)) {
    echo "  \033[32m✓ joomla-cms source ready (no composer install needed)\033[0m" . PHP_EOL;
} else {
    echo "  \033[31m✗ joomla-cms clone appears incomplete — missing libraries/loader.php\033[0m" . PHP_EOL;
}
