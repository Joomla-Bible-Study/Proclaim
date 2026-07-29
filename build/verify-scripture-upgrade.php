<?php

/**
 * Prove that locally downloaded Bible translations survive a package upgrade.
 *
 * lib_cwmscripture <= 1.1.4 declared <uninstall><sql> pointing at DROP TABLE
 * statements. Joomla's LibraryAdapter uninstalls the installed library before it
 * writes the new one (checkExtensionInFilesystem() -> uninstall()), so that SQL
 * ran on every UPDATE: #__bsms_bible_verses and #__bsms_bible_translations were
 * dropped, the library's ensureTables() recreated them empty, and the Local
 * Translations panel came back with nothing downloaded.
 *
 * Runs in two passes from build/test-upgrade.sh, around the upgrade step:
 *
 *   php build/verify-scripture-upgrade.php seed     # after the baseline install
 *   php build/verify-scripture-upgrade.php verify   # after the new build lands
 *
 * The seed writes one sentinel translation marked installed plus a handful of
 * verses; the verify pass asserts both are still there, byte for byte. Mirrors
 * build/verify-scripture-install.php.
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

$mode = $argv[1] ?? '';

if (!\in_array($mode, ['seed', 'verify'], true)) {
    fwrite(STDERR, "Usage: php build/verify-scripture-upgrade.php seed|verify\n");

    exit(2);
}

/**
 * Sentinel translation. 'upgradeprobe' fits the VARCHAR(20) abbreviation column
 * and cannot collide with a real GetBible slug.
 */
const PROBE_ABBR  = 'upgradeprobe';
const PROBE_NAME  = 'Upgrade Probe Translation';
const PROBE_BOOK  = 1;
const PROBE_VERSE = 'In the beginning God created the heaven and the earth.';
const PROBE_ROWS  = 5;

$reader   = new PropertiesReader($root . '/build.properties');
$installs = $reader->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install in build.properties — nothing to check.\n");

    exit(0);
}

$failures = 0;

foreach ($installs as $install) {
    echo "=== scripture upgrade probe ({$mode}) on {$install->id} ({$install->path}) ===\n";

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

    $translations = $prefix . 'bsms_bible_translations';
    $verses       = $prefix . 'bsms_bible_verses';

    if ($mode === 'seed') {
        $text = mysqli_real_escape_string($db, PROBE_VERSE);

        mysqli_query(
            $db,
            "INSERT INTO `{$translations}`
                (`abbreviation`, `name`, `language`, `source`, `installed`, `bundled`, `verse_count`)
             VALUES ('" . PROBE_ABBR . "', '" . PROBE_NAME . "', 'en', 'getbible', 1, 0, " . PROBE_ROWS . ")
             ON DUPLICATE KEY UPDATE `installed` = 1"
        ) or fwrite(STDERR, '  WARN seeding translation: ' . mysqli_error($db) . "\n");

        for ($verse = 1; $verse <= PROBE_ROWS; $verse++) {
            mysqli_query(
                $db,
                "INSERT IGNORE INTO `{$verses}` (`translation`, `book`, `chapter`, `verse`, `text`)
                 VALUES ('" . PROBE_ABBR . "', " . PROBE_BOOK . ", 1, {$verse}, '{$text}')"
            ) or fwrite(STDERR, '  WARN seeding verse: ' . mysqli_error($db) . "\n");
        }

        $seeded = mysqli_fetch_row(mysqli_query(
            $db,
            "SELECT COUNT(*) FROM `{$verses}` WHERE `translation` = '" . PROBE_ABBR . "'"
        ) ?: null);

        if ($seeded === null || $seeded === false || (int) $seeded[0] !== PROBE_ROWS) {
            fwrite(STDERR, "  FAIL could not seed the probe translation — the baseline install\n");
            fwrite(STDERR, "       is missing the bible tables.\n");
            $failures++;
        } else {
            echo '  OK   seeded ' . PROBE_ROWS . " probe verses marked as locally installed\n";
        }

        mysqli_close($db);

        continue;
    }

    // --- verify -------------------------------------------------------------
    $row = mysqli_fetch_assoc(mysqli_query(
        $db,
        "SELECT `installed` FROM `{$translations}` WHERE `abbreviation` = '" . PROBE_ABBR . "'"
    ) ?: null);

    if ($row === null || $row === false) {
        fwrite(STDERR, "  FAIL the probe translation is gone from {$translations}.\n");
        fwrite(STDERR, "       The library's uninstall SQL dropped the table during the upgrade —\n");
        fwrite(STDERR, "       see build/script.install.php::disarmLegacyScriptureUninstallSql().\n");
        $failures++;
    } elseif ((int) $row['installed'] !== 1) {
        fwrite(STDERR, "  FAIL the probe translation survived but is no longer marked installed —\n");
        fwrite(STDERR, "       the catalog seed overwrote local download state.\n");
        $failures++;
    } else {
        echo "  OK   probe translation still registered as locally installed\n";
    }

    $count = mysqli_fetch_row(mysqli_query(
        $db,
        "SELECT COUNT(*) FROM `{$verses}` WHERE `translation` = '" . PROBE_ABBR . "'"
    ) ?: null);

    $found = ($count === null || $count === false) ? 0 : (int) $count[0];

    if ($found === PROBE_ROWS) {
        echo '  OK   all ' . PROBE_ROWS . " downloaded verses survived the upgrade\n";
    } else {
        fwrite(STDERR, "  FAIL {$found}/" . PROBE_ROWS . " probe verses left in {$verses} —\n");
        fwrite(STDERR, "       downloaded translations are being destroyed by the upgrade.\n");
        $failures++;
    }

    // Leave the site clean for whatever runs next.
    mysqli_query($db, "DELETE FROM `{$verses}` WHERE `translation` = '" . PROBE_ABBR . "'");
    mysqli_query($db, "DELETE FROM `{$translations}` WHERE `abbreviation` = '" . PROBE_ABBR . "'");

    mysqli_close($db);
}

if ($failures > 0) {
    fwrite(STDERR, "\nSCRIPTURE UPGRADE PROBE FAILED ({$failures} assertion(s)).\n");

    exit(1);
}

echo "Scripture upgrade probe passed ({$mode}).\n";
