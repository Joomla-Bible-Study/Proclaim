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
    'assert-first-party-registered',
    'assert-detected-consumer',
    'seed-detected-consumer',
    'assert-tables-present',
    'assert-registry-pruned',
    'assert-tables-gone',
    'assert-no-other-consumer',
    'other-consumer-ids',
    'missing-scripture-stack',
    'assert-sql-armed',
    'assert-sql-disarmed',
    'arm',
    'disarm',
    'restore-manifest',
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

/**
 * Provider cache rows, seeded live (expiry in the future). The old uninstall SQL
 * dropped this table alongside the verse tables; losing it on an upgrade sends
 * every cached passage back out to GetBible / API.Bible to be re-fetched.
 */
const PROBE_CACHE_ROWS = 2;

/**
 * The uninstall SQL lib_cwmscripture shipped up to 1.1.4, verbatim.
 *
 * The negative control needs a library that still destroys data, and no
 * released baseline supplies one any more: the file has shipped disarmed since
 * 1.1.5, so asserting the baseline carries it fails for good. The harness
 * plants the hazard itself instead.
 *
 * plg_system_proclaim rewrites this file on sight, but only while an admin is
 * on com_installer in a web request. Every install here runs through the CLI,
 * so nothing disarms the planted file before the phase can use it.
 */
const ARMED_UNINSTALL_SQL = <<<'SQL'
--
-- CWM Scripture Library - Uninstall SQL
-- Only runs when the library is uninstalled standalone (not locked by Proclaim).
--

DROP TABLE IF EXISTS `#__bsms_scripture_cache`;
DROP TABLE IF EXISTS `#__bsms_bible_verses`;
DROP TABLE IF EXISTS `#__bsms_bible_translations`;

SQL;

/**
 * What the disarm writes back — exactly what plg_system_cwmscripture writes on
 * an admin com_installer page load. Shared by the 'disarm' step and by the
 * 'restore-manifest' cleanup so the two cannot drift into disagreeing about
 * what "disarmed" looks like; sqlIsArmed() must read this as inert.
 */
const DISARMED_UNINSTALL_SQL = "--\n"
    . "-- Neutralised by the upgrade harness, mirroring plg_system_cwmscripture.\n"
    . "-- Do not reintroduce DROP TABLE statements here.\n--\n";

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

// --- cleanup mode: undo the planted hazard, on every test install -----------
// Runs from a shell trap, so it touches the filesystem only and never fails: a
// trap that can exit non-zero would mask the real failure that triggered it.
//
// 'arm' plants two things — the armed SQL file and an <uninstall><sql> block in
// the installed manifest. 'disarm' deliberately reverts only the file, because
// phase 16 has to prove that disarming the *file* is what saves the data while
// the manifest still declares it. That leaves the declaration behind, and
// reset-testsite.php does not clear the library (#1860), so without this the
// fixture outlives the run and the next run's first library install inherits it.
if ($mode === 'restore-manifest') {
    foreach ($installs as $install) {
        $manifest = $install->path . '/administrator/manifests/libraries/cwmscripture.xml';
        $sqlFile  = $install->path . '/libraries/cwmscripture/sql/uninstall.mysql.utf8.sql';

        // Disarm first. If the manifest write fails the file is already inert,
        // which is the half that actually destroys data.
        if (is_file($sqlFile)) {
            $buffer = @file_get_contents($sqlFile);

            if ($buffer !== false && sqlIsArmed($buffer)) {
                if (@file_put_contents($sqlFile, DISARMED_UNINSTALL_SQL) === false) {
                    echo "  WARN could not disarm {$sqlFile}.\n";
                } else {
                    echo "  OK   disarmed the planted uninstall SQL\n";
                }
            }
        }

        if (!is_file($manifest)) {
            continue;
        }

        $doc                     = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput       = true;

        if (!@$doc->load($manifest)) {
            echo "  WARN {$manifest} is not parseable XML — leaving it alone.\n";

            continue;
        }

        $xpath   = new DOMXPath($doc);
        $removed = 0;

        // Removes any <uninstall> carrying an <sql> child, not only one this run
        // planted: a previous run that died before its own trap fired is exactly
        // the state this exists to clean up.
        foreach (iterator_to_array($xpath->query('/extension/uninstall[sql]')) as $node) {
            $node->parentNode->removeChild($node);
            $removed++;
        }

        foreach (iterator_to_array($xpath->query('/extension/comment()')) as $node) {
            if (str_contains($node->nodeValue ?? '', 'Planted by the upgrade harness')) {
                $node->parentNode->removeChild($node);
            }
        }

        if ($removed === 0) {
            continue;
        }

        if (@$doc->save($manifest) === false) {
            echo "  WARN could not rewrite {$manifest}.\n";

            continue;
        }

        echo "  OK   removed the planted <uninstall><sql> declaration from the library manifest\n";
    }

    exit(0);
}

// --- value-printing modes: first test install only, for the shell to consume --
if (\in_array($mode, ['site-path', 'ext-id', 'other-consumer-ids', 'missing-scripture-stack'], true)) {
    $install = $installs[0];

    if ($mode === 'site-path') {
        echo $install->path . "\n";

        exit(0);
    }

    if ($mode === 'missing-scripture-stack') {
        // Which members of the shared scripture stack the consumer removals
        // actually took, one "type/folder/element" per line, nothing when all
        // four survive. Phase 10 reinstalls only when this prints something.
        //
        // A consumer that declares our extensions as its own children takes them
        // with it (PackageAdapter::removeExtensionFiles() resolves by element),
        // but a consumer packaged correctly takes none — so the reinstall has to
        // be driven by what is missing, not by whether a consumer was present.
        // The reinstall is a library install, which routes through
        // LibraryAdapter::install() -> checkExtensionInFilesystem() -> uninstall()
        // and so runs whatever uninstall SQL is on disk; doing that when nothing
        // needs restoring is risk with no upside.
        [$db, $prefix] = $connect($install);

        if ($db === null) {
            fwrite(STDERR, "Could not connect to the test database.\n");

            exit(1);
        }

        // folder is '' for a library and distinguishes the two plugins that
        // share the element 'cwmscripture' (task and system).
        $stack = [
            ['library', '', 'cwmscripture'],
            ['plugin', 'content', 'scripturelinks'],
            ['plugin', 'task', 'cwmscripture'],
            ['plugin', 'system', 'cwmscripture'],
        ];

        $missing = [];

        foreach ($stack as [$type, $folder, $element]) {
            $folderSql = $folder === ''
                ? "AND (`folder` = '' OR `folder` IS NULL)"
                : "AND `folder` = '" . mysqli_real_escape_string($db, $folder) . "'";

            $row = mysqli_fetch_row(mysqli_query(
                $db,
                "SELECT `extension_id` FROM `{$prefix}extensions`
                 WHERE `type` = '" . mysqli_real_escape_string($db, $type) . "'
                   AND `element` = '" . mysqli_real_escape_string($db, $element) . "'
                 {$folderSql} LIMIT 1"
            ) ?: null);

            if ($row === null || $row === false) {
                $missing[] = $type . '/' . $folder . '/' . $element;
            }
        }

        mysqli_close($db);

        echo implode("\n", $missing) . (empty($missing) ? '' : "\n");

        exit(0);
    }

    if ($mode === 'other-consumer-ids') {
        // Extension ids for scripture consumers that are NOT part of pkg_proclaim,
        // so the uninstall phases can clear the field before asserting that a
        // package removal drops the shared tables.
        //
        // Deliberately not hardcoded to com_livingword: the phases broke once
        // already because they assumed which consumers a test site carries
        // (lib_cwmscripture#37). Anything FIRST_PARTY or registered counts.
        //
        // Prefers the parent package where one exists — removing com_livingword
        // while pkg_livingword still owns it leaves a broken package behind.
        [$db, $prefix] = $connect($install);

        if ($db === null) {
            fwrite(STDERR, "Could not connect to the test database.\n");

            exit(1);
        }

        // plg_task_cwmscripture is deliberately NOT here. Since 10.5.7 it ships
        // inside pkg_cwmscripture, which Proclaim bundles but does not own
        // (#1675) — so it is part of our own stack, not a third party to clear.
        // It is also a package child, and Joomla refuses to remove one
        // individually, which aborted this phase when it was listed.
        $wanted = [
            ['pkg_livingword', 'package', ''],
            ['com_livingword', 'component', ''],
        ];

        $ids  = [];
        $seen = false;

        foreach ($wanted as [$element, $type, $folder]) {
            $folderSql = $folder === ''
                ? "AND (`folder` = '' OR `folder` IS NULL)"
                : "AND `folder` = '" . mysqli_real_escape_string($db, $folder) . "'";

            $row = mysqli_fetch_row(mysqli_query(
                $db,
                "SELECT `extension_id` FROM `{$prefix}extensions`
                 WHERE `type` = '" . mysqli_real_escape_string($db, $type) . "'
                   AND `element` = '" . mysqli_real_escape_string($db, $element) . "'
                 {$folderSql} LIMIT 1"
            ) ?: null);

            if ($row === null || $row === false) {
                continue;
            }

            // pkg_livingword covers com_livingword; do not remove both.
            if ($element === 'pkg_livingword') {
                $seen = true;
            }

            if ($element === 'com_livingword' && $seen) {
                continue;
            }

            $ids[] = $row[0];
        }

        // Registered third parties the hardcoded list cannot know about.
        $consumersTable = $prefix . 'bsms_scripture_consumers';
        $exists         = mysqli_query($db, "SHOW TABLES LIKE '" . mysqli_real_escape_string($db, $consumersTable) . "'");

        if ($exists !== false && mysqli_num_rows($exists) > 0) {
            $extra = mysqli_query(
                $db,
                "SELECT e.`extension_id` FROM `{$consumersTable}` c
                 JOIN `{$prefix}extensions` e
                   ON e.`element` = c.`element` AND e.`type` = c.`type`
                 WHERE c.`element` NOT IN ('com_proclaim', 'scripturelinks', 'cwmscripture')"
            );

            while ($row = mysqli_fetch_row($extra ?: null)) {
                if (!\in_array($row[0], $ids, true)) {
                    $ids[] = $row[0];
                }
            }
        }

        mysqli_close($db);

        echo implode("\n", $ids) . (empty($ids) ? '' : "\n");

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
    $cache        = $prefix . 'bsms_scripture_cache';
    $extensions   = $prefix . 'extensions';

    $tableExists = static function (mysqli $db, string $table): bool {
        $res = mysqli_query($db, "SHOW TABLES LIKE '" . mysqli_real_escape_string($db, $table) . "'");

        return $res !== false && mysqli_num_rows($res) > 0;
    };

    /**
     * Consumers of the scripture tables other than the ones pkg_proclaim ships.
     *
     * The package excludes its own children when it asks "is anyone left", so
     * anything this returns is a legitimate reason for the tables to survive a
     * package removal. Both sources the library consults have to be checked:
     * the registry, and FIRST_PARTY members that are recognised without a
     * registry row.
     *
     * lib_cwmscripture#37 was filed because step 11 asserted the tables were
     * dropped on a site with com_livingword installed. Living Word is
     * FIRST_PARTY and genuinely uses the tables, so keeping them was correct —
     * the probe was asserting an outcome its own preconditions ruled out, and
     * had no way to say so.
     */
    $otherConsumers = static function (mysqli $db, string $consumers, string $extensions) use ($tableExists): array {
        $ownChildren = "'com_proclaim', 'scripturelinks', 'cwmscripture'";
        $blockers    = [];

        if ($tableExists($db, $consumers)) {
            $registered = mysqli_query(
                $db,
                "SELECT `element` FROM `{$consumers}` WHERE `element` NOT IN ({$ownChildren})"
            );

            while ($row = mysqli_fetch_row($registered ?: null)) {
                $blockers[] = $row[0] . ' (registered)';
            }
        }

        $firstParty = mysqli_query(
            $db,
            "SELECT `element` FROM `{$extensions}`
             WHERE (`type` = 'component' AND `element` = 'com_livingword')
                OR (`type` = 'plugin' AND `folder` = 'task' AND `element` = 'cwmscripture')"
        );

        while ($row = mysqli_fetch_row($firstParty ?: null)) {
            $blockers[] = $row[0] . ' (first-party, installed)';
        }

        return $blockers;
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

            for ($i = 1; $i <= PROBE_CACHE_ROWS; $i++) {
                mysqli_query(
                    $db,
                    "INSERT IGNORE INTO `{$cache}`
                        (`provider`, `translation`, `reference`, `text`, `expires_at`)
                     VALUES ('getbible', '" . PROBE_ABBR . "', 'Probe {$i}:1', 'probe cached text',
                             DATE_ADD(NOW(), INTERVAL 30 DAY))"
                ) or fwrite(STDERR, '  WARN seeding cache row: ' . mysqli_error($db) . "\n");
            }

            echo '  OK   seeded ' . PROBE_ROWS . ' probe verses and '
                . PROBE_CACHE_ROWS . " provider cache rows\n";

            break;

        case 'assert-registry-pruned':
            // After a package removal, the registry must no longer name
            // com_proclaim — the one extension the package still removes.
            //
            // The registry now outlives every Proclaim uninstall, because the
            // scripture stack is not removed with it (#1675), so a stale row
            // persists rather than vanishing with the dropped table.
            //
            // installedExcluding() prunes stale rows, but it runs from the
            // manifest script — which InstallerAdapter::uninstall() calls
            // BEFORE removeExtensionFiles(). At that moment the children are
            // still installed, so it correctly leaves them alone, and nothing
            // runs afterwards. Hence the explicit unregister this asserts.
            $consumers = $prefix . 'bsms_scripture_consumers';

            if (!$tableExists($db, $consumers)) {
                fwrite(STDERR, "  FAIL {$consumers} is missing — this phase expects the tables to have been KEPT.\n");
                $failures++;

                break;
            }

            // com_proclaim only. scripturelinks is no longer removed with
            // Proclaim (#1675), so its registry row is correct and must stay.
            foreach ([['com_proclaim', 'component']] as [$element, $type]) {
                $row = mysqli_fetch_row(mysqli_query(
                    $db,
                    "SELECT COUNT(*) FROM `{$consumers}` WHERE `element` = '"
                    . mysqli_real_escape_string($db, $element) . "' AND `type` = '"
                    . mysqli_real_escape_string($db, $type) . "'"
                ) ?: null);

                $left = ($row === null || $row === false) ? 0 : (int) $row[0];

                if ($left === 0) {
                    echo "  OK   registry no longer names {$element} ({$type})\n";
                } else {
                    fwrite(STDERR, "  FAIL registry still names {$element} ({$type}) after its removal.\n");
                    fwrite(STDERR, "       A reinstall would inherit a registry describing the previous install,\n");
                    fwrite(STDERR, "       and anything reading the table directly sees extensions that do not exist.\n");
                    $failures++;
                }
            }

            break;

        case 'assert-tables-present':
            $allPresent = true;

            foreach ([$translations, $verses, $cache] as $table) {
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

            $cachedLeft = mysqli_fetch_row(mysqli_query(
                $db,
                "SELECT COUNT(*) FROM `{$cache}` WHERE `translation` = '" . PROBE_ABBR . "'"
            ) ?: null);

            $cachedFound = ($cachedLeft === null || $cachedLeft === false) ? 0 : (int) $cachedLeft[0];

            if ($cachedFound === PROBE_CACHE_ROWS) {
                echo '  OK   all ' . PROBE_CACHE_ROWS . " provider cache rows survived the package removal\n";
            } else {
                fwrite(STDERR, "  FAIL {$cachedFound}/" . PROBE_CACHE_ROWS . " cache rows left in {$cache}.\n");
                $failures++;
            }

            break;


        case 'assert-sql-armed':
        case 'assert-sql-disarmed':
        case 'arm':
        case 'disarm':
        case 'assert-translation-survived':
        case 'assert-translation-destroyed':
            $sqlFile = $install->path . '/libraries/cwmscripture/sql/uninstall.mysql.utf8.sql';

            if (\in_array($mode, ['assert-sql-armed', 'assert-sql-disarmed', 'arm', 'disarm'], true)) {
                if (!is_file($sqlFile)) {
                    fwrite(STDERR, "  FAIL {$sqlFile} not found — is the library installed?\n");
                    $failures++;

                    break;
                }

                // arm — restore the pre-1.1.5 hazard the negative control needs.
                // Written and then read back: a silently failed write would leave
                // the phase asserting nothing, which is the exact failure this
                // whole phase exists to rule out.
                if ($mode === 'arm') {
                    if (file_put_contents($sqlFile, ARMED_UNINSTALL_SQL) === false) {
                        fwrite(STDERR, "  FAIL could not write {$sqlFile}.\n");
                        $failures++;

                        break;
                    }

                    if (!sqlIsArmed((string) file_get_contents($sqlFile))) {
                        fwrite(STDERR, "  FAIL planted the uninstall SQL but it does not read back as armed.\n");
                        $failures++;

                        break;
                    }

                    // The file alone is inert. LibraryAdapter runs uninstall SQL
                    // only because the *manifest* declares it, and the shipped
                    // manifest drops that block deliberately — so the pre-1.1.5
                    // manifest has to come back too, or the update reads nothing
                    // and the negative control passes while proving nothing.
                    $manifest = $install->path . '/administrator/manifests/libraries/cwmscripture.xml';

                    if (!is_file($manifest)) {
                        fwrite(STDERR, "  FAIL {$manifest} not found — is the library installed?\n");
                        $failures++;

                        break;
                    }

                    // Edited through DOM, not string replacement: the shipped
                    // manifest carries a comment *explaining* the absent
                    // <uninstall><sql> block, so a substring test for it matches
                    // the prose and skips the edit.
                    $doc                     = new DOMDocument();
                    $doc->preserveWhiteSpace = false;
                    $doc->formatOutput       = true;

                    if (!@$doc->load($manifest)) {
                        fwrite(STDERR, "  FAIL {$manifest} is not parseable XML.\n");
                        $failures++;

                        break;
                    }

                    $xpath = new DOMXPath($doc);

                    if ($xpath->query('/extension/uninstall/sql/file')->length === 0) {
                        $file = $doc->createElement('file', 'sql/uninstall.mysql.utf8.sql');
                        $file->setAttribute('driver', 'mysql');
                        $file->setAttribute('charset', 'utf8');

                        $sql = $doc->createElement('sql');
                        $sql->appendChild($file);

                        $uninstall = $doc->createElement('uninstall');
                        $uninstall->appendChild($sql);

                        $doc->documentElement->appendChild(
                            $doc->createComment(' Planted by the upgrade harness to restore the pre-1.1.5 hazard. ')
                        );
                        $doc->documentElement->appendChild($uninstall);

                        if ($doc->save($manifest) === false) {
                            fwrite(STDERR, "  FAIL could not write {$manifest}.\n");
                            $failures++;

                            break;
                        }
                    }

                    // Re-read from disk: a malformed manifest would make Joomla
                    // skip the library silently, and the phase would pass while
                    // proving nothing.
                    $check = new DOMDocument();

                    if (!@$check->load($manifest)
                        || (new DOMXPath($check))->query('/extension/uninstall/sql/file')->length !== 1) {
                        fwrite(STDERR, "  FAIL {$manifest} does not parse with exactly one uninstall SQL file.\n");
                        $failures++;

                        break;
                    }

                    echo "  OK   planted the 1.1.4-style armed uninstall SQL and its manifest declaration\n";

                    break;
                }

                $armed = sqlIsArmed((string) file_get_contents($sqlFile));

                if ($mode === 'assert-sql-armed') {
                    if ($armed) {
                        echo "  OK   the installed library's uninstall SQL is armed (the real pre-fix hazard)\n";
                    } else {
                        fwrite(STDERR, "  FAIL expected an armed 1.1.4-style uninstall SQL, found none.\n");
                        fwrite(STDERR, "       Without it this phase proves nothing — check the preceding arm step.\n");
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
                //
                // Reverts the file only, deliberately: phase 16 has to prove the
                // disarmed *file* is what saves the data while the manifest still
                // declares <uninstall><sql>. Removing the declaration here would
                // make that phase pass because nothing was declared. The
                // declaration is cleaned up by 'restore-manifest' instead.
                file_put_contents($sqlFile, DISARMED_UNINSTALL_SQL);
                echo "  OK   disarmed the installed library's uninstall SQL\n";

                break;
            }

            // translation survival checks
            $left = mysqli_fetch_row(mysqli_query(
                $db,
                "SELECT COUNT(*) FROM `{$verses}` WHERE `translation` = '" . PROBE_ABBR . "'"
            ) ?: null);

            $found = ($left === null || $left === false) ? 0 : (int) $left[0];

            $cachedLeft = mysqli_fetch_row(mysqli_query(
                $db,
                "SELECT COUNT(*) FROM `{$cache}` WHERE `translation` = '" . PROBE_ABBR . "'"
            ) ?: null);

            $cachedFound = ($cachedLeft === null || $cachedLeft === false) ? 0 : (int) $cachedLeft[0];

            if ($mode === 'assert-translation-survived') {
                if ($cachedFound === PROBE_CACHE_ROWS) {
                    echo '  OK   all ' . PROBE_CACHE_ROWS . " provider cache rows survived too\n";
                } else {
                    fwrite(STDERR, "  FAIL {$cachedFound}/" . PROBE_CACHE_ROWS . " cache rows left after a\n");
                    fwrite(STDERR, "       library-only update — cached passages are being discarded.\n");
                    $failures++;
                }

                if ($found === PROBE_ROWS) {
                    echo '  OK   all ' . PROBE_ROWS . " verses survived the library-only update\n";
                } else {
                    fwrite(STDERR, "  FAIL {$found}/" . PROBE_ROWS . " verses left after a library-only update.\n");
                    fwrite(STDERR, "       The disarm did not protect the standalone library update path.\n");
                    $failures++;
                }
            } else {
                if ($cachedFound === 0) {
                    echo "  OK   provider cache destroyed as expected — the hazard covers it too\n";
                } else {
                    fwrite(STDERR, "  FAIL expected the un-disarmed update to destroy the cache rows,\n");
                    fwrite(STDERR, "       but {$cachedFound} survived.\n");
                    $failures++;
                }

                if ($found === 0) {
                    echo "  OK   verses destroyed as expected — the hazard is real and detectable\n";
                } else {
                    fwrite(STDERR, "  FAIL expected the un-disarmed update to destroy the probe verses,\n");
                    fwrite(STDERR, "       but {$found} survived. This negative control is not proving anything.\n");
                    $failures++;
                }
            }

            break;

        case 'assert-first-party-registered':
            // #1368 / CWMScriptureLinks#12: the extensions that ship with the
            // packages must put themselves on the register, rather than leaning on
            // the library's hardcoded FIRST_PARTY fallback. Only assert for the
            // ones actually installed here — pkg_proclaim does not ship the task
            // plugin, so its absence is normal, not a failure.
            if (!$tableExists($db, $consumers)) {
                fwrite(STDERR, "  FAIL {$consumers} does not exist — the 1.1.6 schema update never ran.\n");
                $failures++;

                break;
            }

            $expected = [
                ['element' => 'com_proclaim',   'type' => 'component', 'folder' => ''],
                ['element' => 'scripturelinks', 'type' => 'plugin',    'folder' => 'content'],
                ['element' => 'cwmscripture',   'type' => 'plugin',    'folder' => 'task'],
            ];

            foreach ($expected as $consumer) {
                $label = $consumer['element'] . ($consumer['folder'] !== '' ? " ({$consumer['folder']})" : '');

                $installedQuery = "SELECT COUNT(*) FROM `{$extensions}`"
                    . " WHERE `type` = '{$consumer['type']}' AND `element` = '{$consumer['element']}'"
                    . ($consumer['folder'] !== '' ? " AND `folder` = '{$consumer['folder']}'" : '');

                $isInstalled = mysqli_fetch_row(mysqli_query($db, $installedQuery) ?: null);

                if ($isInstalled === null || $isInstalled === false || (int) $isInstalled[0] === 0) {
                    echo "  --   {$label} is not installed here, nothing to register\n";

                    continue;
                }

                $registeredQuery = "SELECT COUNT(*) FROM `{$consumers}`"
                    . " WHERE `type` = '{$consumer['type']}' AND `element` = '{$consumer['element']}'"
                    . " AND `folder` = '{$consumer['folder']}'";

                $row = mysqli_fetch_row(mysqli_query($db, $registeredQuery) ?: null);

                if ($row !== null && $row !== false && (int) $row[0] > 0) {
                    echo "  OK   {$label} registered itself as a consumer\n";

                    continue;
                }

                fwrite(STDERR, "  FAIL {$label} is installed but has no row in {$consumers}.\n");
                fwrite(STDERR, "       Its install script never called the library's consumer entry point,\n");
                fwrite(STDERR, "       so protection depends entirely on the FIRST_PARTY fallback.\n");
                $failures++;
            }

            break;

        case 'seed-detected-consumer':
            // The CWMLivingWord scenario: an extension that uses the library, is in
            // #__extensions, and is in neither the registry nor FIRST_PARTY. Only
            // ConsumerScanner can see it — it has to find the namespace reference on
            // disk. Deliberately NOT registered, or this would test the registry again.
            //
            // First clear phase 12's registered fixture. Leaving it behind made this
            // phase pass with the scanner stubbed out: com_thirdpartyprobe was still
            // registered and installed, so the tables were kept for it rather than for
            // the extension under test. A vacuous pass is worse than no test.
            mysqli_query($db, "DELETE FROM `{$consumers}` WHERE `element` = '" . FAKE_ELEMENT . "'");
            mysqli_query($db, "DELETE FROM `{$extensions}` WHERE `element` = '" . FAKE_ELEMENT . "'");

            // Precondition: nothing OTHER than this package's own children may be able
            // to account for the tables surviving. com_proclaim and scripturelinks are
            // fine — they register themselves, and the package excludes them from its
            // own "is anyone left" question. Anything else (a leftover fixture, or a
            // FIRST_PARTY entry that happens to be installed) would keep the tables on
            // its own and make this phase vacuous.
            $blockers = $otherConsumers($db, $consumers, $extensions);

            if ($blockers !== []) {
                fwrite(STDERR, '  FAIL something else already keeps the tables: ' . implode(', ', $blockers) . ".\n");
                fwrite(STDERR, "       This phase would then pass without ConsumerScanner doing anything.\n");
                $failures++;

                break;
            }

            $dir = $install->path . '/administrator/components/com_detectedprobe';

            if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
                fwrite(STDERR, "  FAIL could not create {$dir}.\n");
                $failures++;

                break;
            }

            file_put_contents(
                $dir . '/probe.php',
                "<?php\n// Fixture for the upgrade harness. References the library the way a real\n"
                . "// consumer would, which is the only signal ConsumerScanner has.\n"
                . "use CWM\\Library\\Scripture\\Helper\\ScriptureHelper;\n"
            );

            mysqli_query(
                $db,
                "INSERT IGNORE INTO `{$extensions}`
                    (`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`,
                     `protected`, `locked`, `manifest_cache`, `params`)
                 VALUES ('Detected Probe', 'component', 'com_detectedprobe', '', 1, 1, 1, 0, 0, '{}', '{}')"
            ) or fwrite(STDERR, '  WARN seeding extension row: ' . mysqli_error($db) . "\n");

            $registered = mysqli_fetch_row(mysqli_query(
                $db,
                "SELECT COUNT(*) FROM `{$consumers}` WHERE `element` = 'com_detectedprobe'"
            ) ?: null);

            if ($registered !== null && $registered !== false && (int) $registered[0] > 0) {
                fwrite(STDERR, "  FAIL com_detectedprobe is in the registry — this phase must prove\n");
                fwrite(STDERR, "       detection, not registration.\n");
                $failures++;

                break;
            }

            echo "  OK   planted an unregistered consumer that references the library namespace\n";

            break;

        case 'assert-detected-consumer':
            // Cleanup runs whatever the outcome: a leftover fixture would silently
            // pin the tables for every later phase.
            $dir      = $install->path . '/administrator/components/com_detectedprobe';
            $survived = $tableExists($db, $translations) && $tableExists($db, $verses);

            if ($survived) {
                echo "  OK   tables kept for an unregistered consumer — ConsumerScanner saw it\n";
            } else {
                fwrite(STDERR, "  FAIL the shared tables were dropped while com_detectedprobe still\n");
                fwrite(STDERR, "       referenced the library. ConsumerScanner did not detect it, so a\n");
                fwrite(STDERR, "       third party that never registered loses its translations.\n");
                $failures++;
            }

            if (is_file($dir . '/probe.php')) {
                @unlink($dir . '/probe.php');
            }

            if (is_dir($dir)) {
                @rmdir($dir);
            }

            mysqli_query($db, "DELETE FROM `{$extensions}` WHERE `element` = 'com_detectedprobe'");

            break;

        case 'assert-no-other-consumer':
            // Precondition for assert-tables-gone. Run it BEFORE the removal:
            // afterwards the registry rows and extension rows are gone and the
            // question can no longer be answered.
            $blockers = $otherConsumers($db, $consumers, $extensions);

            if ($blockers !== []) {
                fwrite(STDERR, '  FAIL another scripture consumer is installed: ' . implode(', ', $blockers) . ".\n");
                fwrite(STDERR, "       Removing pkg_proclaim will correctly KEEP the tables, so asserting\n");
                fwrite(STDERR, "       they are dropped would be asserting a bug. Remove it first, or run\n");
                fwrite(STDERR, "       this phase on a site with no other consumer.\n");
                $failures++;

                break;
            }

            echo "  OK   no other scripture consumer — a package removal should drop the tables\n";

            break;

        case 'assert-tables-gone':
            $survivors = [];

            foreach ([$translations, $verses, $cache, $consumers] as $table) {
                if ($tableExists($db, $table)) {
                    fwrite(STDERR, "  FAIL {$table} still exists after the last consumer was removed.\n");
                    $survivors[] = $table;
                    $failures++;
                } else {
                    echo "  OK   {$table} removed — nothing was left using it\n";
                }
            }

            // Say WHY, rather than leaving the reader to guess. "Kept because
            // Living Word is installed" and "kept because the cleanup never ran"
            // look identical from the outside, and the first was mistaken for the
            // second in lib_cwmscripture#37.
            if ($survivors !== []) {
                $blockers = $otherConsumers($db, $consumers, $extensions);

                if ($blockers !== []) {
                    fwrite(STDERR, '       Reason: another consumer is installed — ' . implode(', ', $blockers) . ".\n");
                    fwrite(STDERR, "       That makes keeping the tables CORRECT. This phase needed\n");
                    fwrite(STDERR, "       assert-no-other-consumer to run before the removal.\n");
                } else {
                    fwrite(STDERR, "       No other consumer is installed, so the cleanup in pkg_proclaim's\n");
                    fwrite(STDERR, "       script.install.php::uninstall() should have dropped these and did not.\n");
                    fwrite(STDERR, "       Note it logs to the 'jerror' category, which has no logger in CLI —\n");
                    fwrite(STDERR, "       its messages are discarded there, so silence is not evidence.\n");
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
