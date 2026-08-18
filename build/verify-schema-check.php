<?php

/**
 * Run Joomla's own schema check against every `role = test` install and fail on
 * any error — the same check System → Maintenance → Database performs.
 *
 * This is not what build/verify-migrations.php does, and the difference is the
 * whole point. That script asserts a column or index EXISTS. Joomla's ChangeSet
 * instead reads every historical file in `sql/updates/mysql`, derives a check
 * query from each statement, and runs it against the CURRENT schema. The two
 * disagree in both directions:
 *
 *   - A statement Joomla cannot parse is never checked, so a migration can be
 *     silently unverified while the column it adds is present (#1664).
 *   - A change that a LATER version deliberately undid still reports an error
 *     forever, because ChangeSet has no notion of a statement being superseded.
 *
 * The second is what shipped. 10.5.6 replaced idx_study_topic with the UNIQUE
 * uq_study_topic and dropped the old index as redundant, and the ADD KEY in
 * 10.1.0-20260207.sql went on being checked against a schema that correctly no
 * longer had it. Every site that took 10.5.6 was left with a red error in
 * Database Maintenance — reported from a live site — and nothing in the release
 * gate could see it: the schema was right, the migrations were right, and the
 * only thing wrong was what Joomla's checker made of them.
 *
 * Checks the component and the scripture library, since both ship their own
 * `<schemapath>` and both appear on that screen.
 *
 * Usage:
 *   php build/verify-schema-check.php              # every role=test install
 *   php build/verify-schema-check.php <site-path>  # one site (used internally)
 *
 * SAFETY: only installs marked `role = test` in build.properties are visited,
 * and nothing here writes — ChangeSet::check() only reads.
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

// Child mode: one site, whose CMS this process is about to load.
if (isset($argv[1]) && $argv[1] !== '') {
    exit(checkSite((string) $argv[1]));
}

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

echo "========================================================================\n";
echo " SCHEMA CHECK — Joomla's own ChangeSet (System → Maintenance → Database)\n";
echo "========================================================================\n";

$failures = 0;

foreach ($installs as $install) {
    echo "\n=== {$install->id} ({$install->path}) ===\n";

    if (!is_file($install->path . '/configuration.php')) {
        fwrite(STDERR, "  configuration.php not found — skipping.\n");
        $failures++;

        continue;
    }

    // Each install needs its own JPATH_* constants and its own copy of the CMS,
    // so the work happens in a subprocess. Defining those once in this process
    // would pin every later install to the first one's paths.
    $status = 0;
    passthru(
        \sprintf(
            '%s %s %s',
            escapeshellarg(PHP_BINARY),
            escapeshellarg(__FILE__),
            escapeshellarg($install->path)
        ),
        $status
    );

    if ($status !== 0) {
        $failures++;
    }
}

echo "\n";

if ($failures > 0) {
    fwrite(STDERR, "Schema check FAILED on {$failures} install(s).\n");

    exit(1);
}

echo "Schema OK — Joomla reports no database errors.\n";

/**
 * Load one site's CMS and run ChangeSet over each schema folder it ships.
 *
 * @param   string  $site  Absolute path to the Joomla root
 *
 * @return  int  0 when Joomla reports no errors, 1 otherwise
 *
 * @since __DEPLOY_VERSION__
 */
function checkSite(string $site): int
{
    \define('_JEXEC', 1);
    \define('JPATH_BASE', $site);
    \define('JPATH_ROOT', $site);
    \define('JPATH_SITE', $site);
    \define('JPATH_ADMINISTRATOR', $site . '/administrator');
    \define('JPATH_LIBRARIES', $site . '/libraries');
    \define('JPATH_PLUGINS', $site . '/plugins');
    \define('JPATH_CONFIGURATION', $site);
    \define('JPATH_THEMES', $site . '/templates');
    \define('JPATH_CACHE', $site . '/cache');
    \define('JPATH_MANIFESTS', $site . '/administrator/manifests');

    require $site . '/libraries/vendor/autoload.php';
    require $site . '/libraries/bootstrap.php';
    require $site . '/configuration.php';

    $cfg  = new \JConfig();
    $host = $cfg->host;

    $db = (new \Joomla\Database\DatabaseFactory())->getDriver(
        $cfg->dbtype === 'mysql' ? 'mysqli' : $cfg->dbtype,
        [
            'host'     => $host,
            'user'     => $cfg->user,
            'password' => $cfg->password,
            'database' => $cfg->db,
            'prefix'   => $cfg->dbprefix,
        ]
    );

    // ChangeItem resolves the driver through Factory when it builds its checks.
    \Joomla\CMS\Factory::$database = $db;

    // Paths are given WITHOUT the driver folder: ChangeSet appends the server
    // type itself, so passing `.../mysql` makes it look for `.../mysql/mysql`,
    // find nothing, and report a clean bill of health for zero files checked.
    $folders = [
        'com_proclaim'     => JPATH_ADMINISTRATOR . '/components/com_proclaim/sql/updates',
        'lib_cwmscripture' => JPATH_LIBRARIES . '/cwmscripture/sql/updates',
    ];

    $failed = 0;

    foreach ($folders as $label => $folder) {
        if (!is_dir($folder . '/mysql')) {
            echo "  {$label}: no schema folder installed — skipped\n";

            continue;
        }

        // Constructed directly rather than through getInstance(), which caches a
        // single instance for the whole process and would hand back the first
        // folder's ChangeSet for the second.
        $changeSet = new \Joomla\CMS\Schema\ChangeSet($db, $folder);
        $changeSet->check();
        $status = $changeSet->getStatus();

        $errors = $status['error'] ?? [];

        printf(
            "  %-17s schema=%-18s ok=%-4d error=%-3d skipped=%d\n",
            $label,
            $changeSet->getSchema() ?: '(none)',
            \count($status['ok'] ?? []),
            \count($errors),
            \count($status['skipped'] ?? [])
        );

        foreach ($errors as $item) {
            $failed++;
            echo "\n    \033[31mERROR\033[0m in " . basename((string) $item->file) . "\n";
            echo '      statement: ' . squash((string) $item->updateQuery) . "\n";
            echo '      check:     ' . squash((string) $item->checkQuery) . "\n";
        }
    }

    if ($failed > 0) {
        // Deliberately STDOUT, not STDERR: the two streams buffer differently,
        // and a summary on STDERR jumped ahead of the per-folder lines it was
        // summarising, which reads as if the library folder caused the error.
        echo "\n  Joomla reports {$failed} database error(s) for this site.\n";

        return 1;
    }

    return 0;
}

/**
 * Collapse a SQL statement onto one line for printing.
 *
 * @param   string  $sql  Statement to squash
 *
 * @return  string
 *
 * @since __DEPLOY_VERSION__
 */
function squash(string $sql): string
{
    return trim((string) preg_replace('/\s+/', ' ', $sql));
}
