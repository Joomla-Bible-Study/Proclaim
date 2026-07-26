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
 * #__schemas version it should advance to.
 *
 * Version resolution — a release that ships no migrations must not fail the gate,
 * but a release that ships migrations nobody described must:
 *   - exact x.y.z entry exists      => verify it
 *   - no entry, but update SQL for  => FAIL; the expectations were forgotten
 *     this version exists
 *   - no entry and no update SQL    => re-verify the newest earlier entry as
 *     regression cover. A fresh install of a no-migration release still has to
 *     carry everything its predecessors introduced, so this is what catches
 *     install.sql drifting behind the update SQL.
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
        ],
        'schemaMin' => '10.3.3',
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
 * Resolve which expectation set to verify.
 *
 * An exact match is used when present. Otherwise the version either ships
 * migrations nobody described — a real gap, so fail — or ships none at all, in
 * which case the newest earlier set is re-verified rather than skipping the
 * check. That matters: a fresh install of a no-migration release must still
 * carry every table/column/index its predecessors introduced, which is what
 * catches install.sql drifting behind the update SQL.
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

$expected = $EXPECTATIONS[$key];

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
    echo "Version {$base} ships no migrations — re-verifying the {$key} schema as regression cover\n";
    echo "(a fresh {$base} install must still carry everything {$key} introduced).\n";
}

echo "Verifying {$key} migrations across " . \count($installs) . " test install(s)\n";

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

    $results = [];

    // --- tables -----------------------------------------------------------
    foreach ($expected['tables'] ?? [] as $table) {
        $real      = $expand($table);
        $ok        = tableExists($mysqli, $db['name'], $real);
        $results[] = [$ok, "table {$table}"];
    }

    // --- columns ----------------------------------------------------------
    foreach ($expected['columns'] ?? [] as $table => $cols) {
        $real = $expand($table);

        foreach ($cols as $col) {
            $ok        = columnExists($mysqli, $db['name'], $real, $col);
            $results[] = [$ok, "column {$table}.{$col}"];
        }
    }

    // --- indexes ----------------------------------------------------------
    foreach ($expected['indexes'] ?? [] as $table => $idxs) {
        $real = $expand($table);

        foreach ($idxs as $idx) {
            $ok        = indexExists($mysqli, $db['name'], $real, $idx);
            $results[] = [$ok, "index {$table}.{$idx}"];
        }
    }

    // --- #__schemas version -----------------------------------------------
    if (!empty($expected['schemaMin'])) {
        $schemaVersion = schemaVersion($mysqli, $db['prefix']);
        $ok            = $schemaVersion !== null
            && version_compare($schemaVersion, $expected['schemaMin'], '>=');
        $label         = "#__schemas >= {$expected['schemaMin']} (found " . ($schemaVersion ?? 'none') . ')';
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
    echo "{$green}All {$key} migration assertions passed.{$reset}\n";

    exit(0);
}

echo "{$red}One or more {$key} migration assertions FAILED.{$reset}\n";

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
 * Latest schema version recorded for com_proclaim in #__schemas, or null.
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
