<?php

/**
 * Give every `role = test` install the demo content a fresh install used to
 * seed via raw SQL, now created through the real `proclaim:import` console
 * command instead (#2176).
 *
 * `admin/sql/install.mysql.utf8.sql` no longer inserts a sample study,
 * teacher, series or media files — that content is opt-in now (#2145). But
 * several post-release checks still need *something* to look at:
 * `build/seed-testsite-menus.php` throws if no published study exists,
 * `build/verify-frontend.php` asserts on a study's cited book, and multiple
 * E2E a11y specs (`tests/e2e/site/a11y.spec.js`) skip themselves — reporting
 * green for testing nothing — on an empty listing or an empty media player
 * link. This script is what keeps all of that exercising the real path
 * instead of its own fallback.
 *
 * `build/fixtures/demo-content.json` reproduces the removed seed rows
 * field-for-field, with the fields Cwmcontentimporter does not yet accept
 * (a teacher photo, the study's location and study number, a PDF's popup
 * margin) left out — see the #2176 PR body for the full list.
 *
 * Idempotent via the import tag itself: `Cwmcontentimporter::import()`
 * refuses to run a second time under the same tag, so a re-run against an
 * install this already seeded is a no-op, not a duplicate import.
 *
 * Runs right after the package installs (step 4 of `composer test:install`),
 * before any verification that needs content to exist.
 *
 * SAFETY: only installs marked `role = test` in build.properties are touched,
 * the same guard reset-testsite.php uses. Never point a dev or production
 * install at role = test.
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

declare(strict_types=1);

use CWM\BuildTools\Dev\PropertiesReader;
use CWM\BuildTools\Dev\TestSite;

$root = \dirname(__DIR__);

require $root . '/libraries/vendor/autoload.php';

/**
 * Refused a second time under the same tag — see Cwmcontentimporter::import().
 * Fixed, not random, so a re-run of this script is what makes it idempotent.
 */
const IMPORT_TAG = 'proclaim-demo-content';

const FIXTURE_PATH = __DIR__ . '/fixtures/demo-content.json';

$reader   = new PropertiesReader($root . '/build.properties');
$installs = $reader->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install in build.properties — nothing to seed.\n");

    exit(0);
}

if (!is_file(FIXTURE_PATH)) {
    fwrite(STDERR, 'Fixture not found: ' . FIXTURE_PATH . "\n");

    exit(1);
}

$failures = 0;

foreach ($installs as $install) {
    echo "=== seed demo content on {$install->id} ({$install->path}) ===\n";

    $cli = $install->path . '/cli/joomla.php';

    if (!is_file($cli)) {
        fwrite(STDERR, "  no Joomla console at {$cli} — skipping.\n");
        $failures++;

        continue;
    }

    try {
        $site = TestSite::fromPath($install->path);
        $db   = $site->db();
    } catch (\RuntimeException $e) {
        fwrite(STDERR, '  cannot connect: ' . $e->getMessage() . "\n");
        $failures++;

        continue;
    }

    try {
        seedInstall($site, $install->path, $cli);
    } catch (\RuntimeException $e) {
        fwrite(STDERR, '  ' . $e->getMessage() . "\n");
        $failures++;
    }

    echo "\n";
}

if ($failures > 0) {
    fwrite(STDERR, "Demo content seeding failed for {$failures} install(s).\n");

    exit(1);
}

echo "Demo content seeded.\n";

/**
 * @param   TestSite  $site  The install being seeded.
 * @param   string    $path  Filesystem path to the install.
 * @param   string    $cli   Path to that install's cli/joomla.php.
 *
 * @return  void
 *
 * @throws  \RuntimeException  when the console command is unavailable or the import fails.
 *
 * @since __DEPLOY_VERSION__
 */
function seedInstall(TestSite $site, string $path, string $cli): void
{
    $db = $site->db();

    $alreadyImported = (int) $db
        ->query(
            'SELECT COUNT(*) FROM ' . $site->table('#__bsms_import_manifest')
            . ' WHERE import_tag = ' . $db->quote(IMPORT_TAG)
        )
        ->fetchColumn();

    if ($alreadyImported > 0) {
        echo "  already imported under tag '" . IMPORT_TAG . "' — skipping.\n";

        return;
    }

    // proclaim:import only exists once plg_system_proclaim registers it, and
    // that only happens for a ConsoleApplication — confirm it is actually
    // there rather than let a missing command read as "the import found
    // nothing to do" further down.
    exec('cd ' . escapeshellarg($path) . ' && php ' . escapeshellarg($cli) . ' list 2>&1', $listOutput, $listStatus);

    if ($listStatus !== 0 || !str_contains(implode("\n", $listOutput), 'proclaim:import')) {
        throw new \RuntimeException(
            'proclaim:import is not registered on this install — is plg_system_proclaim enabled?'
        );
    }

    exec(
        'cd ' . escapeshellarg($path) . ' && php ' . escapeshellarg($cli)
        . ' proclaim:import ' . escapeshellarg(FIXTURE_PATH) . ' ' . escapeshellarg(IMPORT_TAG) . ' 2>&1',
        $importOutput,
        $importStatus
    );

    $output = implode("\n", $importOutput);

    if ($importStatus !== 0) {
        throw new \RuntimeException("proclaim:import failed (exit {$importStatus}):\n{$output}");
    }

    echo "  {$output}\n";
}
