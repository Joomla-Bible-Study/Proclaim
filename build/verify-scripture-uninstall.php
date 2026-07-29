<?php

/**
 * Prove the scripture library's uninstall guards actually hold.
 *
 * Three properties, none of which the upgrade probe covers:
 *
 *   1. The 1.1.6 schema update really creates #__bsms_scripture_consumers on an
 *      upgraded site. If it does not, ConsumerRegistry::registered() silently
 *      returns an empty list and every third-party consumer becomes invisible
 *      again — the failure mode is quiet, so it needs an explicit assertion.
 *   2. Uninstalling the library while com_proclaim is installed is refused by
 *      lib_cwmscripture's script.php::preflight(). Without this an admin can
 *      remove the library out from under Proclaim and fatal every sermon view.
 *   3. A registered third-party consumer keeps the shared tables alive when the
 *      whole Proclaim package is removed. This is the property the registry
 *      exists for: without it the package's dropScriptureTablesIfLastConsumer()
 *      would judge the tables orphaned and destroy someone else's downloads.
 *
 * Driven from build/test-upgrade.sh. Some modes print a value for the shell to
 * consume rather than asserting:
 *
 *   php build/verify-scripture-uninstall.php schema
 *   php build/verify-scripture-uninstall.php site-path
 *   php build/verify-scripture-uninstall.php ext-id library cwmscripture
 *   php build/verify-scripture-uninstall.php seed-consumer
 *   php build/verify-scripture-uninstall.php assert-library-present
 *   php build/verify-scripture-uninstall.php assert-tables-present
 *   php build/verify-scripture-uninstall.php assert-tables-gone
 *
 * Mirrors build/verify-scripture-upgrade.php.
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

$modes = [
    'schema',
    'site-path',
    'ext-id',
    'seed-consumer',
    'seed-translation',
    'assert-library-present',
    'assert-tables-present',
    'assert-tables-gone',
    'assert-sql-armed',
    'assert-sql-disarmed',
    'disarm',
    'assert-translation-survived',
    'assert-translation-destroyed',
];

if (!\in_array($mode, $modes, true)) {
    fwrite(STDERR, "Usage: php build/verify-scripture-uninstall.php <" . implode('|', $modes) . ">\n");

    exit(2);
}

/**
 * A pretend third-party consumer. Nothing ships with this element, so if the
 * guards see it, they saw it via the registry and not via the hardcoded list.
 */
const FAKE_ELEMENT = 'com_thirdpartyprobe';
const FAKE_NAME    = 'Third Party Probe';

/**
 * A downloaded translation, so the assertions can prove the *data* survived
 * rather than merely that a table of some shape still exists.
 */
const PROBE_ABBR  = 'uninstallprobe';
const PROBE_ROWS  = 3;

$reader   = new PropertiesReader($root . '/build.properties');
$installs = $reader->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install in build.properties — nothing to check.\n");

    exit(0);
}

/**
 * Open a mysqli connection for an install, returning [db, prefix].
 */
$connect = static function (object $install): array {
    $configFile = $install->path . '/configuration.php';

    if (!is_file($configFile)) {
        return [null, ''];
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

    return [$db === false ? null : $db, $prefix];
};

// --- value-printing modes: first test install only, for the shell to consume --
if (\in_array($mode, ['site-path', 'ext-id'], true)) {
    $install = $installs[0];

    if ($mode === 'site-path') {
        echo $install->path . "\n";

        exit(0);
    }

    $type    = $argv[2] ?? '';
    $element = $argv[3] ?? '';

    if ($type === '' || $element === '') {
        fwrite(STDERR, "Usage: php build/verify-scripture-uninstall.php ext-id <type> <element>\n");

        exit(2);
    }

    [$db, $prefix] = $connect($install);

    if ($db === null) {
        fwrite(STDERR, "Could not connect to the test database.\n");

        exit(1);
    }

    $type    = mysqli_real_escape_string($db, $type);
    $element = mysqli_real_escape_string($db, $element);

    $row = mysqli_fetch_row(mysqli_query(
        $db,
        "SELECT `extension_id` FROM `{$prefix}extensions`
         WHERE `type` = '{$type}' AND `element` = '{$element}' LIMIT 1"
    ) ?: null);

    mysqli_close($db);

    if ($row === null || $row === false) {
        fwrite(STDERR, "No {$type} '{$element}' registered in #__extensions.\n");

        exit(1);
    }

    echo $row[0] . "\n";

    exit(0);
}

// --- asserting modes: every test install ------------------------------------
/**
 * Executable DROP TABLE present? Mirrors Cwmscripture::sqlIsArmed() — comments
 * that merely mention DROP TABLE do not count.
 */
function sqlIsArmed(string $sql): bool
{
    if (stripos($sql, 'DROP TABLE') === false) {
        return false;
    }

    foreach (preg_split('/\R/', $sql) ?: [] as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '--')) {
            continue;
        }

        if (stripos($line, 'DROP TABLE') !== false) {
            return true;
        }
    }

    return false;
}

$failures = 0;

foreach ($installs as $install) {
    echo "=== scripture uninstall probe ({$mode}) on {$install->id} ===\n";

    [$db, $prefix] = $connect($install);

    if ($db === null) {
        fwrite(STDERR, "  FAIL could not connect to the database for {$install->id}.\n");
        $failures++;

        continue;
    }

    $consumers    = $prefix . 'bsms_scripture_consumers';
    $translations = $prefix . 'bsms_bible_translations';
    $verses       = $prefix . 'bsms_bible_verses';
    $extensions   = $prefix . 'extensions';

    $tableExists = static function (mysqli $db, string $table): bool {
        $res = mysqli_query($db, "SHOW TABLES LIKE '" . mysqli_real_escape_string($db, $table) . "'");

        return $res !== false && mysqli_num_rows($res) > 0;
    };

    switch ($mode) {
        case 'schema':
            if ($tableExists($db, $consumers)) {
                echo "  OK   {$consumers} exists — the 1.1.6 schema update ran\n";
            } else {
                fwrite(STDERR, "  FAIL {$consumers} is missing after the upgrade.\n");
                fwrite(STDERR, "       sql/updates/mysql/1.1.6.sql did not run, so the consumer registry\n");
                fwrite(STDERR, "       is silently empty and third-party consumers are invisible.\n");
                $failures++;
            }

            break;

        case 'seed-consumer':
            if (!$tableExists($db, $consumers)) {
                fwrite(STDERR, "  FAIL cannot register a probe consumer — {$consumers} does not exist.\n");
                $failures++;

                break;
            }

            mysqli_query(
                $db,
                "INSERT INTO `{$consumers}` (`element`, `type`, `folder`, `name`, `registered`)
                 VALUES ('" . FAKE_ELEMENT . "', 'component', '', '" . FAKE_NAME . "', NOW())
                 ON DUPLICATE KEY UPDATE `registered` = NOW()"
            ) or fwrite(STDERR, '  WARN registering probe consumer: ' . mysqli_error($db) . "\n");

            // The registry prunes entries whose extension is gone, so the probe
            // needs a matching #__extensions row to look genuinely installed.
            mysqli_query(
                $db,
                "INSERT INTO `{$extensions}`
                    (`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`,
                     `manifest_cache`, `params`, `custom_data`)
                 VALUES ('" . FAKE_NAME . "', 'component', '" . FAKE_ELEMENT . "', '', 1, 1, 1, 0, '{}', '{}', '')"
            ) or fwrite(STDERR, '  WARN registering probe extension: ' . mysqli_error($db) . "\n");

            $ok = mysqli_fetch_row(mysqli_query(
                $db,
                "SELECT COUNT(*) FROM `{$consumers}` WHERE `element` = '" . FAKE_ELEMENT . "'"
            ) ?: null);

            if ($ok === null || $ok === false || (int) $ok[0] !== 1) {
                fwrite(STDERR, "  FAIL could not register the probe consumer.\n");
                $failures++;
            } else {
                echo '  OK   registered ' . FAKE_ELEMENT . " as a third-party consumer\n";
            }

            break;

        case 'assert-library-present':
            $row = mysqli_fetch_row(mysqli_query(
                $db,
                "SELECT COUNT(*) FROM `{$extensions}`
                 WHERE `type` = 'library' AND `element` = 'cwmscripture'"
            ) ?: null);

            if ($row !== null && $row !== false && (int) $row[0] > 0) {
                echo "  OK   lib_cwmscripture is still installed — the uninstall was refused\n";
            } else {
                fwrite(STDERR, "  FAIL lib_cwmscripture was uninstalled while com_proclaim is installed.\n");
                fwrite(STDERR, "       script.php::preflight() should have aborted it via allowUninstall().\n");
                $failures++;
            }

            break;

        case 'seed-translation':
            if (!$tableExists($db, $translations) || !$tableExists($db, $verses)) {
                fwrite(STDERR, "  FAIL cannot seed — the bible tables do not exist.\n");
                $failures++;

                break;
            }

            mysqli_query(
                $db,
                "INSERT INTO `{$translations}`
                    (`abbreviation`, `name`, `language`, `source`, `installed`, `bundled`, `verse_count`)
                 VALUES ('" . PROBE_ABBR . "', 'Uninstall Probe', 'en', 'getbible', 1, 0, " . PROBE_ROWS . ")
                 ON DUPLICATE KEY UPDATE `installed` = 1"
            ) or fwrite(STDERR, '  WARN seeding translation: ' . mysqli_error($db) . "\n");

            for ($verse = 1; $verse <= PROBE_ROWS; $verse++) {
                mysqli_query(
                    $db,
                    "INSERT IGNORE INTO `{$verses}` (`translation`, `book`, `chapter`, `verse`, `text`)
                     VALUES ('" . PROBE_ABBR . "', 1, 1, {$verse}, 'probe verse {$verse}')"
                ) or fwrite(STDERR, '  WARN seeding verse: ' . mysqli_error($db) . "\n");
            }

            echo '  OK   seeded ' . PROBE_ROWS . " probe verses\n";

            break;

        case 'assert-tables-present':
            $allPresent = true;

            foreach ([$translations, $verses] as $table) {
                if ($tableExists($db, $table)) {
                    echo "  OK   {$table} survived — a registered consumer kept it\n";
                } else {
                    fwrite(STDERR, "  FAIL {$table} was dropped despite " . FAKE_ELEMENT . " being registered.\n");
                    fwrite(STDERR, "       dropScriptureTablesIfLastConsumer() ignored the registry — a third\n");
                    fwrite(STDERR, "       party's downloaded translations would have been destroyed.\n");
                    $failures++;
                    $allPresent = false;
                }
            }

            if (!$allPresent) {
                break;
            }

            // The tables existing is not enough — the rows have to still be in them.
            $left = mysqli_fetch_row(mysqli_query(
                $db,
                "SELECT COUNT(*) FROM `{$verses}` WHERE `translation` = '" . PROBE_ABBR . "'"
            ) ?: null);

            $found = ($left === null || $left === false) ? 0 : (int) $left[0];

            if ($found === PROBE_ROWS) {
                echo '  OK   all ' . PROBE_ROWS . " downloaded verses survived the package removal\n";
            } else {
                fwrite(STDERR, "  FAIL {$found}/" . PROBE_ROWS . " probe verses left — the tables survived\n");
                fwrite(STDERR, "       but their contents did not.\n");
                $failures++;
            }

            break;


        case 'assert-sql-armed':
        case 'assert-sql-disarmed':
        case 'disarm':
        case 'assert-translation-survived':
        case 'assert-translation-destroyed':
            $sqlFile = $install->path . '/libraries/cwmscripture/sql/uninstall.mysql.utf8.sql';

            if (\in_array($mode, ['assert-sql-armed', 'assert-sql-disarmed', 'disarm'], true)) {
                if (!is_file($sqlFile)) {
                    fwrite(STDERR, "  FAIL {$sqlFile} not found — is the library installed?\n");
                    $failures++;

                    break;
                }

                $armed = sqlIsArmed((string) file_get_contents($sqlFile));

                if ($mode === 'assert-sql-armed') {
                    if ($armed) {
                        echo "  OK   the installed library's uninstall SQL is armed (the real pre-fix hazard)\n";
                    } else {
                        fwrite(STDERR, "  FAIL expected an armed 1.1.4-style uninstall SQL, found none.\n");
                        fwrite(STDERR, "       Without it this phase proves nothing — check the baseline install.\n");
                        $failures++;
                    }

                    break;
                }

                if ($mode === 'assert-sql-disarmed') {
                    if ($armed) {
                        fwrite(STDERR, "  FAIL the uninstall SQL is still armed after the disarm step.\n");
                        $failures++;
                    } else {
                        echo "  OK   the uninstall SQL is disarmed\n";
                    }

                    break;
                }

                // disarm — exactly what plg_system_cwmscripture does on an admin
                // com_installer page load.
                file_put_contents(
                    $sqlFile,
                    "--\n-- Neutralised by the upgrade harness, mirroring plg_system_cwmscripture.\n"
                    . "-- Do not reintroduce DROP TABLE statements here.\n--\n"
                );
                echo "  OK   disarmed the installed library's uninstall SQL\n";

                break;
            }

            // translation survival checks
            $left = mysqli_fetch_row(mysqli_query(
                $db,
                "SELECT COUNT(*) FROM `{$verses}` WHERE `translation` = '" . PROBE_ABBR . "'"
            ) ?: null);

            $found = ($left === null || $left === false) ? 0 : (int) $left[0];

            if ($mode === 'assert-translation-survived') {
                if ($found === PROBE_ROWS) {
                    echo '  OK   all ' . PROBE_ROWS . " verses survived the library-only update\n";
                } else {
                    fwrite(STDERR, "  FAIL {$found}/" . PROBE_ROWS . " verses left after a library-only update.\n");
                    fwrite(STDERR, "       The disarm did not protect the standalone library update path.\n");
                    $failures++;
                }
            } else {
                if ($found === 0) {
                    echo "  OK   verses destroyed as expected — the hazard is real and detectable\n";
                } else {
                    fwrite(STDERR, "  FAIL expected the un-disarmed update to destroy the probe verses,\n");
                    fwrite(STDERR, "       but {$found} survived. This negative control is not proving anything.\n");
                    $failures++;
                }
            }

            break;

        case 'assert-tables-gone':
            foreach ([$translations, $verses, $consumers] as $table) {
                if ($tableExists($db, $table)) {
                    fwrite(STDERR, "  FAIL {$table} still exists after the last consumer was removed.\n");
                    $failures++;
                } else {
                    echo "  OK   {$table} removed — nothing was left using it\n";
                }
            }

            break;
    }

    mysqli_close($db);
}

if ($failures > 0) {
    fwrite(STDERR, "\nSCRIPTURE UNINSTALL PROBE FAILED ({$failures} assertion(s)).\n");

    exit(1);
}

echo "Scripture uninstall probe passed ({$mode}).\n";
