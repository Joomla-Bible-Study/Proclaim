<?php

declare(strict_types=1);

/**
 * Accept Proclaim's licence on every role=test install, so the browser suites
 * can reach the admin views they drive.
 *
 * Proclaim's admin dispatcher redirects every view and task to the licence
 * screen until `license_accepted` is set in the component's params. On a site
 * nobody has clicked through, a spec that opens any Proclaim admin page lands
 * on the licence agreement and fails looking for a field that is not there.
 *
 * Deliberately not part of test-install.sh. `composer test:install` asserts
 * what a clean install produces, and the gate standing in the way is part of
 * that: accepting it inside the install check would hide a regression in the
 * gate itself. Accepting it is a test-harness step, run after the install and
 * upgrade checks and before Playwright — by `composer test:release`,
 * `composer test:api` and the E2E workflow alike.
 *
 * The flag is written as the string '1', the way CwmlicenseController stores
 * it, and read back. A write that did not take fails here, by name, rather
 * than as an element-not-found in whichever spec happens to run first.
 *
 * Usage: php build/accept-license.php
 *
 * Exit codes:
 *   0  the flag reads back on every role=test install
 *   1  a write that did not take, no com_proclaim row, or no usable install
 *
 * @package    Proclaim.Build
 * @since      __DEPLOY_VERSION__
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use CWM\BuildTools\Dev\PropertiesReader;
use CWM\BuildTools\Dev\TestSite;

$root = \dirname(__DIR__);

require $root . '/libraries/vendor/autoload.php';

$installs = (new PropertiesReader($root . '/build.properties'))->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install in build.properties — no licence to accept.\n");

    // Not exit(0): whatever runs next would fail on the licence screen instead.
    exit(1);
}

$failures = 0;

foreach ($installs as $install) {
    echo "=== accept the licence on {$install->id} ({$install->path}) ===\n";

    try {
        $site  = TestSite::fromInstall($install);
        $table = $site->table('#__extensions');
        $where = " WHERE type = 'component' AND element = 'com_proclaim'";

        // Params that are not valid JSON are replaced, as Cwmparams::setCompParams() does.
        $site->db()
            ->prepare('UPDATE ' . $table . ' SET params = JSON_SET(IF(JSON_VALID(params), params, ?), ?, ?)' . $where)
            ->execute(['{}', '$.license_accepted', '1']);

        $params = $site->db()->query('SELECT params FROM ' . $table . $where)->fetchColumn();
    } catch (\RuntimeException $e) {
        fwrite(STDERR, '  FAIL ' . $e->getMessage() . "\n");
        $failures++;

        continue;
    }

    if ($params === false) {
        fwrite(STDERR, "  FAIL com_proclaim is not registered on this site. Was the package installed?\n");
        $failures++;

        continue;
    }

    $accepted = json_decode((string) $params, true)['license_accepted'] ?? null;

    if ($accepted !== '1') {
        fwrite(STDERR, '  FAIL license_accepted reads back as ' . var_export($accepted, true) . ", not '1'.\n");
        $failures++;

        continue;
    }

    echo "  OK   license_accepted is '1'\n";
}

if ($failures > 0) {
    fwrite(STDERR, "\nLicence acceptance FAILED on {$failures} install(s).\n");

    exit(1);
}

echo "Licence accepted on every role=test install.\n";
