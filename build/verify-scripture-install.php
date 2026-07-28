<?php

/**
 * Verify the scripture library landed on every role=test install — the
 * assertions the 10.3.0 library split needed and never had.
 *
 * Three failure modes shipped around the split, each fixed in code but
 * never asserted until now:
 *
 *   - lib_cwmscripture registered via discover-install makes Joomla treat
 *     the package's library zip as an "update", skipping its install SQL —
 *     the three bible tables silently never exist. ensureTables() in the
 *     library's script repairs this; asserted here as the tables existing.
 *   - The translation catalog seed is part of that same install SQL — a
 *     site can have the tables and an empty catalog (the nfsda.org manual
 *     fix). Asserted here as a non-empty bsms_bible_translations.
 *   - plg_content_scripturelinks installed but disabled, so scripture
 *     references never render. enablePlugin() in the package script fixes
 *     it; asserted here as an enabled #__extensions row.
 *
 * Runs as part of `composer test:install` against the site the installer
 * just built. Mirrors build/verify-api-install.php.
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
    echo "=== verify scripture install on {$install->id} ({$install->path}) ===\n";

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

    // --- the library itself registered and enabled --------------------------
    $row = mysqli_fetch_assoc(mysqli_query(
        $db,
        "SELECT enabled FROM {$prefix}extensions
         WHERE type = 'library' AND element = 'cwmscripture'"
    ) ?: null);

    if ($row === null || $row === false) {
        fwrite(STDERR, "  FAIL lib_cwmscripture has no #__extensions row — the package never installed it.\n");
        $failures++;
    } elseif ((int) $row['enabled'] !== 1) {
        fwrite(STDERR, "  FAIL lib_cwmscripture is registered but disabled.\n");
        $failures++;
    } else {
        echo "  OK   lib_cwmscripture registered and enabled\n";
    }

    // --- its three tables actually exist (the skipped-install-SQL signature) -
    foreach (['bsms_bible_translations', 'bsms_bible_verses', 'bsms_scripture_cache'] as $table) {
        $exists = mysqli_num_rows(mysqli_query(
            $db,
            "SHOW TABLES LIKE '{$prefix}{$table}'"
        ) ?: null);

        if ($exists === 1) {
            echo "  OK   {$prefix}{$table} exists\n";
        } else {
            fwrite(STDERR, "  FAIL {$prefix}{$table} missing — the library's install SQL never ran\n");
            fwrite(STDERR, "       (discover-install made the package zip look like an update, and\n");
            fwrite(STDERR, "       ensureTables() did not repair it).\n");
            $failures++;
        }
    }

    // --- the translation catalog is seeded, not just structured -------------
    $count = mysqli_fetch_row(mysqli_query(
        $db,
        "SELECT COUNT(*) FROM {$prefix}bsms_bible_translations"
    ) ?: null);

    if ($count !== null && $count !== false && (int) $count[0] > 0) {
        echo "  OK   translation catalog seeded ({$count[0]} translations)\n";
    } else {
        fwrite(STDERR, "  FAIL bsms_bible_translations is empty — tables without the catalog seed\n");
        fwrite(STDERR, "       (the nfsda.org manual-fix scenario). The library install SQL seeds it.\n");
        $failures++;
    }

    // --- scripturelinks installed AND enabled -------------------------------
    $row = mysqli_fetch_assoc(mysqli_query(
        $db,
        "SELECT enabled FROM {$prefix}extensions
         WHERE type = 'plugin' AND folder = 'content' AND element = 'scripturelinks'"
    ) ?: null);

    if ($row === null || $row === false) {
        fwrite(STDERR, "  FAIL plg_content_scripturelinks has no #__extensions row —\n");
        fwrite(STDERR, "       the package never installed it.\n");
        $failures++;
    } elseif ((int) $row['enabled'] !== 1) {
        fwrite(STDERR, "  FAIL plg_content_scripturelinks is installed but disabled —\n");
        fwrite(STDERR, "       enablePlugin() in build/script.install.php must switch it on.\n");
        $failures++;
    } else {
        echo "  OK   plg_content_scripturelinks installed and enabled\n";
    }

    mysqli_close($db);
}

if ($failures > 0) {
    fwrite(STDERR, "\nSCRIPTURE INSTALL VERIFICATION FAILED ({$failures} assertion(s)).\n");

    exit(1);
}

echo "Scripture install verification passed.\n";
