<?php

/**
 * Release migration verifier — asserts the active-development schema actually
 * landed on every `role = test` install after an install/upgrade.
 *
 * `cwm-verify --target test` only confirms the extension is *registered* at the
 * expected version in #__extensions. It does not confirm the individual columns,
 * tables, and indexes the update SQL is supposed to create. This script closes
 * that gap: for the active-development version (from build/versions.json) it
 * connects to each role=test install's database — reading connection details
 * straight from that install's configuration.php, the reliable path for schema
 * inspection (Joomla's CLI bootstrap is unreliable here) — and checks the DDL.
 *
 * Data-driven: EXPECTATIONS is keyed by version. When a release adds migrations,
 * add an entry describing the tables/columns/indexes it introduces and the
 * #__schemas version it should advance to. `columnsAbsent` asserts a DROP
 * actually happened, which is the only way a removal can be proven.
 *
 * Every entry at or below the version under test is verified, not just the newest.
 * The release being cut has to carry what all of its predecessors introduced, and
 * checking only one of them meant an assertion added to an older entry — the usual
 * way a gap gets recorded once it is found — quietly never ran again.
 *
 * A release that ships no migrations must not fail the gate, but one that ships
 * migrations nobody described must:
 *   - exact x.y.z entry exists      => verify it, plus every earlier entry
 *   - no entry, but update SQL for  => FAIL; the expectations were forgotten
 *     this version exists
 *   - no entry and no update SQL    => verify every earlier entry as regression
 *     cover, which is what catches install.sql drifting behind the update SQL.
 *
 * Usage:   php build/verify-migrations.php [version]
 *   version  Optional override; defaults to active_development in versions.json.
 *
 * Exit code: 0 = every assertion passed on every test install (or there is
 * genuinely nothing to verify); 1 = one or more failed, no role=test install, or
 * a version shipped migrations with no expectations describing them.
 *
 * @package  Proclaim.Build
 * @since    __DEPLOY_VERSION__
 */

declare(strict_types=1);

use CWM\BuildTools\Dev\PropertiesReader;

$root = \dirname(__DIR__);

require $root . '/libraries/vendor/autoload.php';

/**
 * Per-version schema expectations. Extend this when a release adds migrations.
 *
 *   tables    list<string>                 tables that must exist
 *   columns   array<string, list<string>>  table => columns that must exist
 *   indexes   array<string, list<string>>  table => index names that must exist
 *   schemaMin string                        minimum #__schemas version_id for com_proclaim
 *
 * Table names use the Joomla `#__` prefix placeholder; it is expanded per install.
 */
$EXPECTATIONS = [
    /*
     * 10.1.0 shipped three ALTER statements that each carried several clauses.
     * MysqlChangeItem reads only words 3 and 4 of a statement, so all three read
     * as "ADD COLUMN" and everything after the first clause was neither checked
     * nor repairable. Splitting them (#1664) made these visible; asserting them
     * here is what proves the split actually reaches a database.
     *
     * itunes_category is deliberately listed alongside the three that were
     * hidden: it was the one clause the checker could see, so it is the control.
     */
    '10.1.0' => [
        'columns' => [
            '#__bsms_analytics_events'  => ['series_id'],
            '#__bsms_analytics_monthly' => ['series_id'],
            '#__bsms_mediafiles'        => ['content_origin'],
            '#__bsms_podcast'           => [
                'itunes_category',
                'itunes_subcategory',
                'itunes_explicit',
                'itunes_type',
            ],
        ],
        'indexes' => [
            '#__bsms_analytics_events' => ['idx_series_created'],
            // Reconciled by proclaim.script.php rather than by SQL: the rework
            // needs a DROP that MySQL cannot make conditional, and a standalone
            // DROP INDEX item expects zero rows forever after the key is re-added.
            '#__bsms_analytics_monthly' => ['uq_aggregate'],
        ],
        'schemaMin' => '10.1.0',
    ],
    '10.3.3' => [
        'tables' => [
            '#__bsms_playlists',
            '#__bsms_playlist_items',
            '#__bsms_podcast_download_log',
        ],
        'columns' => [
            '#__bsms_studies'        => ['transcript'],
            '#__bsms_playlists'      => ['writeback_enabled'],
            '#__bsms_playlist_items' => ['source'],
            '#__bsms_mediafiles'     => ['podcast_downloads', 'podcast_guid'],
            '#__bsms_podcast'        => ['track_downloads'],
        ],
        'indexes' => [
            '#__bsms_studies' => ['idx_transcript'],
            // Declared inside the CREATE TABLE, which MysqlChangeItem reduces to
            // SHOW TABLES LIKE — so System → Database cannot see this index, let
            // alone restore it. CwmplaylistSyncHelper's upserts depend on it
            // firing (#1658); without it they silently duplicate rows instead of
            // updating them. This gate is the only thing that checks.
            '#__bsms_playlist_items' => ['idx_playlist_video'],
        ],
        'schemaMin' => '10.3.3',
    ],
    // 10.4.1-20260729.sql repairs Font Awesome brand icons in stored params. It is
    // pure DML — REPLACE() over #__bsms_mediafiles.params, #__bsms_servers.media and
    // #__bsms_admin.params — so there is no table, column or index to assert; only
    // that the migration ran and #__schemas advanced. Asserting the repair itself
    // needs a damaged-row fixture seeded before the upgrade, which this gate has no
    // vocabulary for yet.
    '10.4.1' => [
        'schemaMin' => '10.4.1',
    ],
    // 10.5.1-20260801.sql adds the podcast copyright/rights-holder column (#1412).
    '10.5.1' => [
        'columns' => [
            '#__bsms_podcast' => ['copyright'],
        ],
        'schemaMin' => '10.5.1',
    ],
    // 10.5.3-20260801.sql closes the gap #1189 (Podcasting 2.0) and #1188
    // (teacher-user linking) left behind: both added columns to
    // install.mysql.utf8.sql without a matching update SQL file, so any site
    // that existed before 2026-03-19 never got them (#1416).
    '10.5.3' => [
        'columns' => [
            '#__bsms_podcast' => [
                'funding_url',
                'funding_text',
                'podcast_license',
                'podcast_license_url',
                'podcast_publisher',
                'podcast_txt_verify',
                'update_frequency',
            ],
            '#__bsms_teachers' => ['user_id'],
        ],
        'schemaMin' => '10.5.3',
    ],
    /*
     * 10.5.6-20260807.sql carries four uniqueness/cleanup migrations.
     *
     * Both new columns are STORED generated columns that exist only to back a
     * unique index: MySQL treats '' as a value, so a plain unique index would
     * have collided across every unnumbered message and every hand-made
     * playlist. Mapping the empty case to NULL leaves those rows
     * unconstrained. Asserting the column and the index together is what
     * catches half the pair being dropped.
     *
     * The DML halves are not asserted here: #1622's orphan cleanup, #1612's
     * referrer rewrite and #1611's session_hash re-key all need a damaged-row
     * fixture seeded before the upgrade, which this gate has no vocabulary for
     * (same limitation noted on 10.4.1 above).
     */
    '10.5.6' => [
        'columns' => [
            // #1579 — uq_series_studynumber
            '#__bsms_studies' => ['studynumber_uk'],
            // #1560 — uq_server_remote_playlist
            '#__bsms_playlists' => ['remote_playlist_uk'],
        ],
        'indexes' => [
            '#__bsms_studytopics' => ['uq_study_topic'],
            '#__bsms_studies'     => ['uq_series_studynumber'],
            '#__bsms_playlists'   => ['uq_server_remote_playlist', 'idx_remote_playlist'],
        ],
        'schemaMin' => '10.5.6',
    ],
    /*
     * 10.5.8 removes seven columns from #__bsms_studies that were declared and
     * never used: the CD/DVD production set from when messages shipped on
     * physical media, plus a second study-text field (#1690). Empty on every
     * database available, including one carrying 827 imported studies.
     *
     * Asserted by absence, one per column. The migration deliberately issues
     * seven separate ALTER statements: MysqlChangeItem reads only words 3 and 4,
     * so a compound DROP would register as one and leave six unchecked.
     */
    '10.5.8' => [
        'columnsAbsent' => [
            '#__bsms_studies' => [
                'prod_dvd',
                'prod_cd',
                'server_cd',
                'server_dvd',
                'image_cd',
                'image_dvd',
                'studytext2',
            ],
        ],
        'schemaMin' => '10.5.8',
    ],
];

// ---------------------------------------------------------------------------

$version = $argv[1] ?? null;

if ($version === null) {
    $versions = json_decode((string) file_get_contents($root . '/build/versions.json'), true);
    $version  = $versions['active_development']['version'] ?? null;
}

if ($version === null) {
    fwrite(STDERR, "Could not determine version (no argument, no active_development in versions.json).\n");

    exit(1);
}

// Match a dotted x.y.z prefix so tags like 10.3.3-dev / 10.3.3-beta1 resolve.
if (preg_match('/^(\d+\.\d+\.\d+)/', $version, $m) !== 1) {
    fwrite(STDERR, "Could not parse an x.y.z version from '{$version}'.\n");

    exit(1);
}

$base = $m[1];

/*
 * Resolve which expectation sets to verify.
 *
 * An exact match names the release under test; when there is none, the version
 * either ships migrations nobody described — a real gap, so fail — or ships none
 * at all, which is not a reason to skip the check.
 *
 * Either way every entry at or below the version is verified. A fresh install of
 * any release must carry every table, column and index its predecessors
 * introduced, and that is what catches install.sql drifting behind the update
 * SQL. It also keeps an assertion added to an older entry alive: verifying only
 * the newest set meant such an assertion ran on the release that introduced it
 * and never again.
 */
$mode = 'exact';

if (isset($EXPECTATIONS[$base])) {
    $key = $base;
} elseif (($shipped = migrationFilesFor($root, $base)) !== []) {
    fwrite(STDERR, "Version {$base} ships migration SQL but has no \$EXPECTATIONS entry:\n");

    foreach ($shipped as $file) {
        fwrite(STDERR, '  ' . basename($file) . "\n");
    }

    fwrite(STDERR, "Describe the tables/columns/indexes they add in " . __FILE__ . ".\n");

    exit(1);
} else {
    $earlier = array_filter(
        array_keys($EXPECTATIONS),
        static fn (string $k): bool => version_compare($k, $base, '<=')
    );
    usort($earlier, 'version_compare');

    $key  = $earlier === [] ? null : end($earlier);
    $mode = 'regression';
}

if ($key === null) {
    echo "Version {$base} ships no migrations and no earlier expectations exist — nothing to verify.\n";

    exit(0);
}

// Everything the release must carry, oldest first, so failures read in the order
// the schema was built up.
$keys = array_filter(
    array_keys($EXPECTATIONS),
    static fn (string $k): bool => version_compare($k, $base, '<=')
);
usort($keys, 'version_compare');

$reader   = new PropertiesReader($root . '/build.properties');
$installs = $reader->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install found in build.properties — nothing to verify.\n");
    fwrite(STDERR, "Declare one (builder.<id>.role = test) so cwm-install-zip / this check have a target.\n");

    exit(1);
}

$green   = "\033[32m";
$red     = "\033[31m";
$yellow  = "\033[33m";
$reset   = "\033[0m";
$overall = true;

if ($mode === 'regression') {
    echo "Version {$base} ships no migrations — verifying earlier schemas as regression cover\n";
    echo "(a fresh {$base} install must still carry everything they introduced).\n";
}

echo 'Verifying ' . implode(', ', $keys) . ' migrations across ' . \count($installs) . " test install(s)\n";

foreach ($installs as $install) {
    echo "\n=== {$install->id} ({$install->path}) ===\n";

    $config = $install->path . '/configuration.php';

    if (!is_file($config)) {
        echo "{$red}FAIL{$reset} configuration.php not found at {$config}\n";
        $overall = false;

        continue;
    }

    // Load the install's config in an isolated scope to read DB connection details.
    $db = (static function (string $configFile): array {
        require $configFile;
        $c = new \JConfig();

        return [
            'host'   => $c->host,
            'user'   => $c->user,
            'pass'   => $c->password,
            'name'   => $c->db,
            'prefix' => $c->dbprefix,
        ];
    })($config);

    // configuration.php may store host as "host:port".
    $host = $db['host'];
    $port = null;

    if (str_contains($host, ':')) {
        [$host, $portStr] = explode(':', $host, 2);
        $port             = (int) $portStr;
    }

    $mysqli = @new mysqli($host, $db['user'], $db['pass'], $db['name'], $port ?? 3306);

    if ($mysqli->connect_errno !== 0) {
        echo "{$red}FAIL{$reset} DB connection: {$mysqli->connect_error}\n";
        $overall = false;

        continue;
    }

    $expand = static fn (string $t): string => str_replace('#__', $db['prefix'], $t);

    /*
     * Proclaim's tables outlive Proclaim: an uninstall keeps them unless the
     * administrator opts out. So a site can pass every table, column and index
     * assertion here while having no com_proclaim row at all, and the only sign
     * would be the #__schemas check failing for a reason that reads like schema
     * drift. Say what actually happened instead.
     */
    if (!proclaimInstalled($mysqli, $db['prefix'])) {
        echo "{$red}FAIL{$reset} com_proclaim is not installed on this site — nothing to verify a schema against.\n";
        echo "       Its bsms_ tables may still be present; an uninstall keeps them by default.\n";
        echo "       Install Proclaim here (composer test:install) and run this again.\n";
        $overall = false;
        $mysqli->close();

        continue;
    }

    $results = [];

    foreach ($keys as $version) {
        $expected = $EXPECTATIONS[$version];

        // --- tables -------------------------------------------------------
        foreach ($expected['tables'] ?? [] as $table) {
            $real      = $expand($table);
            $ok        = tableExists($mysqli, $db['name'], $real);
            $results[] = [$ok, "[{$version}] table {$table}"];
        }

        // --- columns ------------------------------------------------------
        foreach ($expected['columns'] ?? [] as $table => $cols) {
            $real = $expand($table);

            foreach ($cols as $col) {
                $ok        = columnExists($mysqli, $db['name'], $real, $col);
                $results[] = [$ok, "[{$version}] column {$table}.{$col}"];
            }
        }

        // --- columns that must be gone ------------------------------------
        // A DROP migration is only proven by absence. Joomla's MysqlChangeItem
        // reads words 3 and 4 of a statement, so a compound ALTER drops every
        // column but registers only the first — asserting each one is what
        // catches that.
        foreach ($expected['columnsAbsent'] ?? [] as $table => $cols) {
            $real = $expand($table);

            foreach ($cols as $col) {
                $ok        = !columnExists($mysqli, $db['name'], $real, $col);
                $results[] = [$ok, "[{$version}] column {$table}.{$col} removed"];
            }
        }

        // --- indexes ------------------------------------------------------
        foreach ($expected['indexes'] ?? [] as $table => $idxs) {
            $real = $expand($table);

            foreach ($idxs as $idx) {
                $ok        = indexExists($mysqli, $db['name'], $real, $idx);
                $results[] = [$ok, "[{$version}] index {$table}.{$idx}"];
            }
        }
    }

    // --- #__schemas version -----------------------------------------------
    // Only the newest applicable minimum is asserted; it implies every earlier one.
    if (!empty($EXPECTATIONS[$key]['schemaMin'])) {
        $schemaMin     = $EXPECTATIONS[$key]['schemaMin'];
        $schemaVersion = schemaVersion($mysqli, $db['prefix']);
        $ok            = $schemaVersion !== null
            && version_compare($schemaVersion, $schemaMin, '>=');
        $label         = "#__schemas >= {$schemaMin} (found " . ($schemaVersion ?? 'none') . ')';
        $results[]     = [$ok, $label];
    }

    foreach ($results as [$ok, $label]) {
        if ($ok) {
            echo "  {$green}PASS{$reset} {$label}\n";
        } else {
            echo "  {$red}FAIL{$reset} {$label}\n";
            $overall = false;
        }
    }

    $mysqli->close();
}

echo "\n";

if ($overall) {
    echo "{$green}All migration assertions passed (" . implode(", ", $keys) . ").{$reset}\n";

    exit(0);
}

echo "{$red}One or more migration assertions FAILED.{$reset}\n";

exit(1);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Update SQL files belonging to a given x.y.z version.
 *
 * Joomla schema files here are named `<version>[-suffix].sql` (e.g.
 * `10.3.3-20260711.sql`), so match the version segment exactly rather than as a
 * loose prefix — otherwise 10.3.4 would also claim 10.3.40's files.
 *
 * @return list<string>
 *
 * @since __DEPLOY_VERSION__
 */
function migrationFilesFor(string $root, string $version): array
{
    $files = glob($root . '/admin/sql/updates/mysql/' . $version . '*.sql') ?: [];

    return array_values(
        array_filter($files, static function (string $file) use ($version): bool {
            $name = basename($file, '.sql');

            return $name === $version || str_starts_with($name, $version . '-');
        })
    );
}

/**
 * @since __DEPLOY_VERSION__
 */
function tableExists(mysqli $db, string $schema, string $table): bool
{
    $stmt = $db->prepare(
        'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? LIMIT 1'
    );
    $stmt->bind_param('ss', $schema, $table);
    $stmt->execute();
    $exists = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();

    return $exists;
}

/**
 * @since __DEPLOY_VERSION__
 */
function columnExists(mysqli $db, string $schema, string $table, string $column): bool
{
    $stmt = $db->prepare(
        'SELECT 1 FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
    );
    $stmt->bind_param('sss', $schema, $table, $column);
    $stmt->execute();
    $exists = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();

    return $exists;
}

/**
 * @since __DEPLOY_VERSION__
 */
function indexExists(mysqli $db, string $schema, string $table, string $index): bool
{
    $stmt = $db->prepare(
        'SELECT 1 FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1'
    );
    $stmt->bind_param('sss', $schema, $table, $index);
    $stmt->execute();
    $exists = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();

    return $exists;
}

/**
 * Whether com_proclaim is registered on this install.
 *
 * @param   mysqli  $db      Connection to the install's database.
 * @param   string  $prefix  That install's table prefix.
 *
 * @return  bool
 *
 * @since __DEPLOY_VERSION__
 */
function proclaimInstalled(mysqli $db, string $prefix): bool
{
    $res = $db->query(
        'SELECT extension_id FROM ' . $prefix . "extensions"
        . " WHERE element = 'com_proclaim' AND type = 'component' LIMIT 1"
    );

    return $res !== false && $res->num_rows > 0;
}

/**
 * The recorded schema version for com_proclaim, or null when there is none.
 *
 * @param   mysqli  $db      Connection to the install's database.
 * @param   string  $prefix  That install's table prefix.
 *
 * @return  string|null
 *
 * @since __DEPLOY_VERSION__
 */
function schemaVersion(mysqli $db, string $prefix): ?string
{
    $ext    = $prefix . 'extensions';
    $schema = $prefix . 'schemas';

    $sql = "SELECT s.version_id
            FROM {$schema} s
            INNER JOIN {$ext} e ON e.extension_id = s.extension_id
            WHERE e.element = 'com_proclaim' AND e.type = 'component'
            LIMIT 1";

    $res = $db->query($sql);

    if ($res === false) {
        return null;
    }

    $row = $res->fetch_row();

    return $row ? (string) $row[0] : null;
}
