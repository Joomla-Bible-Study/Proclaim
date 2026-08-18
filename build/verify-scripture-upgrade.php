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
 * Runs in three passes from build/test-upgrade.sh, around the upgrade step:
 *
 *   php build/verify-scripture-upgrade.php seed     # after the baseline install
 *   php build/verify-scripture-upgrade.php verify   # after the new build lands
 *   php build/verify-scripture-upgrade.php cleanup  # once, after the last verify
 *
 * The seed writes one sentinel translation marked installed plus a handful of
 * verses; the verify pass asserts both are still there, byte for byte. Mirrors
 * build/verify-scripture-install.php.
 *
 * ⚠️ verify is read-only and may be called as often as needed. Teardown is a
 * separate mode on purpose: while it was the tail of verify, a second verify
 * always failed against rows the first had deleted.
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

$mode = $argv[1] ?? '';

if (!\in_array($mode, ['seed', 'verify', 'cleanup'], true)) {
    fwrite(STDERR, "Usage: php build/verify-scripture-upgrade.php seed|verify|cleanup\n");

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

/**
 * The provider cache is the third table the old uninstall SQL dropped. Its rows
 * are rebuildable, but only by going back out to GetBible / API.Bible — so
 * losing them on a routine upgrade means a latency and API-quota hit on every
 * cached passage, and on API.Bible a fresh round of billable FUMS calls. Seeded
 * with an expiry in the future so a live row is being checked, not an expired
 * one the cache would discard anyway.
 */
const PROBE_CACHE_ROWS = 3;

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
    echo "=== scripture upgrade probe ({$mode}) on {$install->id} ({$install->path}) ===\n";

    $configFile = $install->path . '/configuration.php';

    if (!is_file($configFile)) {
        fwrite(STDERR, "  FAIL configuration.php not found — is this a Joomla install?\n");
        $failures++;

        continue;
    }

    try {
        $site = TestSite::fromPath($install->path);
        $db   = $site->db();
    } catch (\RuntimeException $e) {
        fwrite(STDERR, '  FAIL ' . $e->getMessage() . "\n");
        $failures++;

        continue;
    }

    $translations = $site->table('#__bsms_bible_translations');
    $verses       = $site->table('#__bsms_bible_verses');
    $cache        = $site->table('#__bsms_scripture_cache');

    // ⚠️ Every assertion below asks whether the table is there before querying
    // it, because a dropped table is the thing this probe exists to detect and
    // it must be reported, not thrown. The original relied on
    // `mysqli_query(...) ?: null` for that, which has not worked since PHP 8.1
    // made mysqli throw: the branch printing "the library's uninstall SQL
    // dropped the table" could not be reached on any supported PHP, and the
    // one scenario this file was written for produced a stack trace instead.
    $absent = static fn (string $table): bool => !$site->hasTable($table);

    if ($mode === 'seed') {
        // A warning, not a failure: the count assertions below decide the
        // outcome. These stayed warnings by design, so the catch is what keeps
        // them warnings now that a failed statement raises instead of
        // returning false.
        $warn = static function (string $what, \PDOException $e): void {
            fwrite(STDERR, "  WARN seeding {$what}: " . $e->getMessage() . "\n");
        };

        try {
            $insert = $db->prepare(
                "INSERT INTO `{$translations}`
                (`abbreviation`, `name`, `language`, `source`, `installed`, `bundled`, `verse_count`)
             VALUES (?, ?, 'en', 'getbible', 1, 0, ?)
             ON DUPLICATE KEY UPDATE `installed` = 1"
            );
            $insert->execute([PROBE_ABBR, PROBE_NAME, PROBE_ROWS]);
        } catch (\PDOException $e) {
            $warn('translation', $e);
        }

        try {
            $insert = $db->prepare(
                "INSERT IGNORE INTO `{$verses}` (`translation`, `book`, `chapter`, `verse`, `text`)
                 VALUES (?, ?, 1, ?, ?)"
            );

            for ($verse = 1; $verse <= PROBE_ROWS; $verse++) {
                $insert->execute([PROBE_ABBR, PROBE_BOOK, $verse, PROBE_VERSE]);
            }
        } catch (\PDOException $e) {
            $warn('verse', $e);
        }

        $seeded = null;

        if (!$absent($verses)) {
            $count = $db->prepare("SELECT COUNT(*) FROM `{$verses}` WHERE `translation` = ?");
            $count->execute([PROBE_ABBR]);
            $seeded = [$count->fetchColumn()];
        }

        if ($seeded === null || (int) $seeded[0] !== PROBE_ROWS) {
            fwrite(STDERR, "  FAIL could not seed the probe translation — the baseline install\n");
            fwrite(STDERR, "       is missing the bible tables.\n");
            $failures++;
        } else {
            echo '  OK   seeded ' . PROBE_ROWS . " probe verses marked as locally installed\n";
        }

        // Provider cache — the third table the old uninstall SQL dropped.
        try {
            $insert = $db->prepare(
                "INSERT IGNORE INTO `{$cache}`
                    (`provider`, `translation`, `reference`, `text`, `expires_at`)
                 VALUES ('getbible', ?, ?, ?,
                         DATE_ADD(NOW(), INTERVAL 30 DAY))"
            );

            for ($i = 1; $i <= PROBE_CACHE_ROWS; $i++) {
                $insert->execute([PROBE_ABBR, "Probe {$i}:1", PROBE_VERSE]);
            }
        } catch (\PDOException $e) {
            $warn('cache row', $e);
        }

        $cached = null;

        if (!$absent($cache)) {
            $count = $db->prepare("SELECT COUNT(*) FROM `{$cache}` WHERE `translation` = ?");
            $count->execute([PROBE_ABBR]);
            $cached = [$count->fetchColumn()];
        }

        if ($cached === null || (int) $cached[0] !== PROBE_CACHE_ROWS) {
            fwrite(STDERR, "  FAIL could not seed the provider cache — {$cache} is missing.\n");
            $failures++;
        } else {
            echo '  OK   seeded ' . PROBE_CACHE_ROWS . " live provider cache rows\n";
        }

        continue;
    }

    // --- cleanup ------------------------------------------------------------
    // ⚠️ Teardown is its own mode, never the tail of `verify`. It used to run at
    // the end of the verify pass ("leave the site clean for whatever runs next"),
    // which made verify single-use: the second call always failed, because the
    // first had deleted the rows it asserts on. That is not a hypothetical --
    // phase 10's assertion was added against a fixture phase 9 had already torn
    // down, and it reported destroyed bible data on a site where nothing was
    // wrong. Keep verify read-only so it can be called at every point that needs
    // proof the data is still there.
    if ($mode === 'cleanup') {
        // Teardown ignored query failures before and still does: a table that
        // is not there has no probe rows to remove, which is the outcome this
        // mode wants either way.
        foreach ([[$cache, 'translation'], [$verses, 'translation'], [$translations, 'abbreviation']] as [$table, $column]) {
            if ($absent($table)) {
                continue;
            }

            $delete = $db->prepare("DELETE FROM `{$table}` WHERE `{$column}` = ?");
            $delete->execute([PROBE_ABBR]);
        }

        echo "  OK   removed the probe rows\n";

        continue;
    }

    // --- verify -------------------------------------------------------------
    $row = null;

    if (!$absent($translations)) {
        $select = $db->prepare("SELECT `installed` FROM `{$translations}` WHERE `abbreviation` = ?");
        $select->execute([PROBE_ABBR]);
        $row = $select->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    if ($row === null) {
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

    $found = 0;

    if (!$absent($verses)) {
        $count = $db->prepare("SELECT COUNT(*) FROM `{$verses}` WHERE `translation` = ?");
        $count->execute([PROBE_ABBR]);
        $found = (int) $count->fetchColumn();
    }

    if ($found === PROBE_ROWS) {
        echo '  OK   all ' . PROBE_ROWS . " downloaded verses survived the upgrade\n";
    } else {
        fwrite(STDERR, "  FAIL {$found}/" . PROBE_ROWS . " probe verses left in {$verses} —\n");
        fwrite(STDERR, "       downloaded translations are being destroyed by the upgrade.\n");
        $failures++;
    }

    $cachedFound = 0;

    if (!$absent($cache)) {
        $cachedLeft = $db->prepare("SELECT COUNT(*) FROM `{$cache}` WHERE `translation` = ?");
        $cachedLeft->execute([PROBE_ABBR]);
        $cachedFound = (int) $cachedLeft->fetchColumn();
    }

    if ($cachedFound === PROBE_CACHE_ROWS) {
        echo '  OK   all ' . PROBE_CACHE_ROWS . " provider cache rows survived the upgrade\n";
    } else {
        fwrite(STDERR, "  FAIL {$cachedFound}/" . PROBE_CACHE_ROWS . " cache rows left in {$cache} —\n");
        fwrite(STDERR, "       the upgrade is discarding cached passages, so every one of them costs\n");
        fwrite(STDERR, "       another provider round trip (and API.Bible FUMS calls) to rebuild.\n");
        $failures++;
    }
}

if ($failures > 0) {
    fwrite(STDERR, "\nSCRIPTURE UPGRADE PROBE FAILED ({$failures} assertion(s)).\n");

    exit(1);
}

echo "Scripture upgrade probe passed ({$mode}).\n";
