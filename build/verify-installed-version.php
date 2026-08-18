<?php

declare(strict_types=1);

/**
 * Assert the site now runs the version this run claims to have installed.
 *
 * The harness already checks the *from* side: verify-install-log.php asserts
 * the update reported upgrading from the expected release (#1842's gate). It
 * checked nothing about where it ended up, and that is the half #1862 was
 * about — the built package declared the baseline version at both ends, so
 * every assertion in a 17-phase run agreed while the transition under test had
 * not happened.
 *
 * test-upgrade.sh guards the *intent* before building: the package manifest
 * must declare the target version. This guards the *outcome*: an install that
 * silently did not apply leaves the manifests correct and the site behind, and
 * intent alone cannot tell those apart.
 *
 * Reads what the site believes it has — #__extensions.manifest_cache — rather
 * than the files on disk, because that is what Joomla wrote at install time and
 * what every version-gated code path consults.
 *
 * Usage: php build/verify-installed-version.php <expected-version>
 *
 * Exit codes:
 *   0  every checked extension reports the expected version
 *   1  a mismatch, a missing extension, or no usable install
 *   2  wrong invocation
 *
 * @package    Proclaim.Build
 * @since      __DEPLOY_VERSION__
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use CWM\BuildTools\Dev\ExtensionQuery;
use CWM\BuildTools\Dev\PropertiesReader;
use CWM\BuildTools\Dev\TestSite;

$root = \dirname(__DIR__);

require $root . '/libraries/vendor/autoload.php';

$expected = $argv[1] ?? '';

if ($expected === '') {
    fwrite(STDERR, "Usage: php build/verify-installed-version.php <expected-version>\n");

    exit(2);
}

$reader   = new PropertiesReader($root . '/build.properties');
$installs = $reader->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install in build.properties — nothing to check.\n");

    // ⚠️ Not exit(0). A verification with no target verified nothing.
    exit(1);
}

// The package and the component are bumped together and installed together, so
// a disagreement between them is itself worth catching.
$targets = [
    ['package', 'pkg_proclaim'],
    ['component', 'com_proclaim'],
];

$failures = 0;

foreach ($installs as $install) {
    echo "=== installed version on {$install->id} ({$install->path}) ===\n";

    try {
        $site = TestSite::fromPath($install->path);
        $site->db();
    } catch (\RuntimeException $e) {
        fwrite(STDERR, '  FAIL ' . $e->getMessage() . "\n");
        $failures++;

        continue;
    }

    $query = new ExtensionQuery($site);

    foreach ($targets as [$type, $element]) {
        if (!$query->exists($type, $element)) {
            fwrite(STDERR, "  FAIL {$element} is not registered on this site.\n");
            $failures++;

            continue;
        }

        $found = $query->version($type, $element);

        if ($found === $expected) {
            echo "  OK   {$element} reports {$found}\n";

            continue;
        }

        fwrite(STDERR, "  FAIL {$element} reports " . ($found ?? 'no version') . ", expected {$expected}.\n");
        fwrite(STDERR, "       The install did not take, or it installed a package built from\n");
        fwrite(STDERR, "       manifests carrying a different version (#1862).\n");
        $failures++;
    }
}

if ($failures > 0) {
    fwrite(STDERR, "\nInstalled-version check FAILED ({$failures} assertion(s)).\n");

    exit(1);
}

echo "Installed version verified: {$expected}.\n";
