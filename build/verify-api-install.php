<?php

/**
 * Verify the REST API landed on every role=test install — the assertions
 * #1309, #1310 and #1331 needed and never had (#1330).
 *
 * Three bugs shipped together and left the API unreachable on every install
 * that wasn't hand-prepared, each invisible to a different layer of testing:
 *
 *   #1309  plg_webservices_proclaim never installed — asserted here as an
 *          enabled #__extensions row.
 *   #1310  API controllers never deployed to the api application — asserted
 *          here as the controller file existing on disk under api/.
 *   #1331  the plugin's parameters must be seeded at install so the API is
 *          reachable (authenticated) out of the box — asserted here as
 *          public_reads present in the row's params.
 *
 * Runs as part of `composer test:install` against the site the installer
 * just built — not a dev site assembled by reset-testsite file copying,
 * which is precisely what hid #1309 and #1310. The admin-facing and
 * over-the-wire halves of the acceptance test live in
 * tests/e2e/api/api-acceptance.spec.js.
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

declare(strict_types=1);

use CWM\BuildTools\Dev\PropertiesReader;

$root = \dirname(__DIR__);

require $root . '/libraries/vendor/autoload.php';

$reader   = new PropertiesReader($root . '/build.properties');
$installs = $reader->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install in build.properties — nothing to verify.\n");

    exit(0);
}

$failures = 0;

foreach ($installs as $install) {
    echo "=== verify API install on {$install->id} ({$install->path}) ===\n";

    // --- #1310: the api application actually received the controllers -------
    $controller = $install->path . '/api/components/com_proclaim/src/Controller/SermonsController.php';

    if (is_file($controller)) {
        echo "  OK   api/ controllers deployed (SermonsController.php present)\n";
    } else {
        fwrite(STDERR, "  FAIL api/components/com_proclaim/src/Controller/SermonsController.php missing —\n");
        fwrite(STDERR, "       the manifest's <api> section did not deploy (the #1310 signature).\n");
        $failures++;
    }

    // --- #1309 + #1331: plugin row, enabled, with seeded params -------------
    $configFile = $install->path . '/configuration.php';

    if (!is_file($configFile)) {
        fwrite(STDERR, "  FAIL configuration.php not found — is this a Joomla install?\n");
        $failures++;

        continue;
    }

    [$host, $user, $pass, $name, $prefix] = (static function (string $file): array {
        require $file;
        $c = new \JConfig();

        return [$c->host, $c->user, $c->password, $c->db, $c->dbprefix];
    })($configFile);

    $port = 3306;

    if (str_contains($host, ':')) {
        [$host, $port] = explode(':', $host, 2);
        $port          = (int) $port;
    }

    $db = mysqli_connect($host, $user, $pass, $name, $port);

    if ($db === false) {
        fwrite(STDERR, "  FAIL could not connect to database {$name}.\n");
        $failures++;

        continue;
    }

    $row = mysqli_fetch_assoc(mysqli_query(
        $db,
        "SELECT enabled, params FROM {$prefix}extensions
         WHERE type = 'plugin' AND folder = 'webservices' AND element = 'proclaim'"
    ) ?: null);

    if ($row === null || $row === false) {
        fwrite(STDERR, "  FAIL plg_webservices_proclaim has no #__extensions row —\n");
        fwrite(STDERR, "       the package never installed it (the #1309 signature).\n");
        $failures++;
    } elseif ((int) $row['enabled'] !== 1) {
        fwrite(STDERR, "  FAIL plg_webservices_proclaim is installed but disabled —\n");
        fwrite(STDERR, "       a clean install must come up with the API reachable (#1331).\n");
        $failures++;
    } else {
        echo "  OK   plg_webservices_proclaim installed and enabled\n";

        $params = json_decode((string) $row['params'], true);

        if (!\is_array($params) || !\array_key_exists('public_reads', $params)) {
            fwrite(STDERR, "  FAIL plugin params not seeded at install (no public_reads) —\n");
            fwrite(STDERR, "       defaults must be stored, not implied (#1331). params: {$row['params']}\n");
            $failures++;
        } else {
            echo "  OK   plugin params seeded (public_reads={$params['public_reads']})\n";
        }
    }

    mysqli_close($db);
}

if ($failures > 0) {
    fwrite(STDERR, "\nAPI INSTALL VERIFICATION FAILED ({$failures} assertion(s)).\n");

    exit(1);
}

echo "API install verification passed.\n";
