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

use CWM\BuildTools\Dev\ExtensionQuery;
use CWM\BuildTools\Dev\PropertiesReader;
use CWM\BuildTools\Dev\TestSite;

$root = \dirname(__DIR__);

require $root . '/libraries/vendor/autoload.php';

$reader   = new PropertiesReader($root . '/build.properties');
$installs = $reader->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install in build.properties — nothing to check.\n");
    fwrite(STDERR, "Declare one (builder.<id>.role = test) so this check has a target.\n");

    // ⚠️ Not exit(0). A verification with no target verified nothing, and a
    // green exit here would let the whole release gate pass while testing
    // an empty set -- the same silence that made #1866 expensive.
    exit(1);
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

    // Connection, credentials and prefix from TestSite: configuration.php is
    // parsed as text rather than required, so this no longer executes the
    // site's PHP and defines JConfig here, and PDO raises on a broken query
    // instead of returning false and reading as "not installed".
    try {
        $site   = TestSite::fromPath($install->path);
        $db     = $site->db();
        $prefix = $site->prefix();
    } catch (\RuntimeException $e) {
        fwrite(STDERR, '  FAIL ' . $e->getMessage() . "\n");
        $failures++;

        continue;
    }

    $query = new ExtensionQuery($site);

    // --- the library itself registered and enabled --------------------------
    // The library registers under its <libraryname>, so element is
    // 'cwmscripture' -- shared with two plugins, which is why type matters.
    $row = $query->find('library', 'cwmscripture');

    if ($row === null) {
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
        // hasTable() matches the name exactly through information_schema.
        // `SHOW TABLES LIKE '{$prefix}{$table}'` treated every underscore in
        // these names as a single-character wildcard, so it would also have
        // matched bsmsXbibleYtranslations had one existed.
        if ($site->hasTable('#__' . $table)) {
            echo "  OK   {$prefix}{$table} exists\n";
        } else {
            fwrite(STDERR, "  FAIL {$prefix}{$table} missing — the library's install SQL never ran\n");
            fwrite(STDERR, "       (discover-install made the package zip look like an update, and\n");
            fwrite(STDERR, "       ensureTables() did not repair it).\n");
            $failures++;
        }
    }

    // --- the translation catalog is seeded, not just structured -------------
    $count = (int) $db->query('SELECT COUNT(*) FROM ' . $site->table('#__bsms_bible_translations'))
        ->fetchColumn();

    if ($count > 0) {
        echo "  OK   translation catalog seeded ({$count} translations)\n";
    } else {
        fwrite(STDERR, "  FAIL bsms_bible_translations is empty — tables without the catalog seed\n");
        fwrite(STDERR, "       (the nfsda.org manual-fix scenario). The library install SQL seeds it.\n");
        $failures++;
    }

    // --- scripturelinks installed AND enabled -------------------------------
    $row = $query->find('plugin', 'scripturelinks', 'content');

    if ($row === null) {
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

}

if ($failures > 0) {
    fwrite(STDERR, "\nSCRIPTURE INSTALL VERIFICATION FAILED ({$failures} assertion(s)).\n");

    exit(1);
}

echo "Scripture install verification passed.\n";
