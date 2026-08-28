<?php

/**
 * Part of Proclaim Package
 *
 * Uninstall test — runs the real uninstall against every `role = test` install
 * and asserts what it did, and what it left alone.
 *
 * ⚠️ DESTRUCTIVE. It sets `drop_tables = 1` and then uninstalls the component,
 * which is the point: that path drops all 23 Proclaim tables and deletes the
 * component's `#__assets` rows. It runs only against `role = test` installs,
 * which are real, non-symlinked sites that exist to be destroyed. It refuses to
 * run if no such install is declared.
 *
 * The headline assertion is not that Proclaim's own rows went. It is that
 * **another extension's rows did not**. Until #1987 the uninstall matched
 * `name LIKE 'com_proclaim%'`, which also matches `com_proclaimtools` and
 * anything else sharing the prefix — on an uninstall that match is a DELETE.
 * The narrowing is the change worth guarding; the rest is a smoke test.
 *
 * Run via: composer test:uninstall
 *
 * Exit code: 0 = every assertion passed on every test install;
 *            1 = one or more failed, or no role=test install exists.
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since      __DEPLOY_VERSION__
 */

use CWM\BuildTools\Dev\PropertiesReader;
use CWM\BuildTools\Dev\TestSite;

$root = \dirname(__DIR__);

require $root . '/libraries/vendor/autoload.php';

$green  = "\033[32m";
$red    = "\033[31m";
$yellow = "\033[33m";
$reset  = "\033[0m";

$reader   = new PropertiesReader($root . '/build.properties');
$installs = $reader->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install found in build.properties — refusing to run.\n");
    fwrite(STDERR, "This test destroys the site it runs against; it will not touch a dev install.\n");

    exit(1);
}

/**
 * A foreign asset name that shares Proclaim's prefix.
 *
 * ⚠️ The whole point of the fixture. `com_proclaim%` matches this;
 * `com_proclaim` and `com_proclaim.%` do not.
 */
const FOREIGN_ASSET = 'com_proclaimtools.widget.1';

/**
 * Tables lib_cwmscripture owns, which a Proclaim uninstall must leave alone.
 *
 * ⚠️ Surviving is the correct behaviour, not a failure. The library stays
 * installed because other extensions may be reading it, and the uninstall says
 * so on the way out. Counting them made the test demand data loss.
 */
const LIBRARY_TABLES = [
    'bsms_bible_translations',
    'bsms_bible_verses',
    'bsms_scripture_cache',
    'bsms_scripture_consumers',
];

$overall = true;

foreach ($installs as $install) {
    echo "\n=== {$install->id} ({$install->path}) ===\n";

    try {
        $site = TestSite::fromPath($install->path);
        $pdo  = $site->db();
    } catch (\RuntimeException $e) {
        echo "{$red}FAIL{$reset} DB connection: " . $e->getMessage() . "\n";
        $overall = false;

        continue;
    }

    $assets     = $site->table('#__assets');
    $extensions = $site->table('#__extensions');
    $admin      = $site->table('#__bsms_admin');

    // ---------------------------------------------------------------- seed --
    // A foreign extension's asset row sharing our prefix, and a real one from
    // core, so the test covers both the contrived case and a genuine bystander.
    $pdo->exec("DELETE FROM {$assets} WHERE name = " . $pdo->quote(FOREIGN_ASSET));
    $rootAsset = $pdo->query("SELECT id FROM {$assets} WHERE name = 'root.1'")->fetchColumn();
    $pdo->prepare(
        "INSERT INTO {$assets} (parent_id, lft, rgt, level, name, title, rules) VALUES (?, 0, 0, 1, ?, ?, '{}')"
    )->execute([(int) $rootAsset, FOREIGN_ASSET, 'Foreign fixture']);

    $before = [
        'proclaim_assets' => (int) $pdo->query(
            "SELECT COUNT(*) FROM {$assets} WHERE name = 'com_proclaim' OR name LIKE 'com_proclaim.%'"
        )->fetchColumn(),
        'foreign_asset' => (int) $pdo->query(
            "SELECT COUNT(*) FROM {$assets} WHERE name = " . $pdo->quote(FOREIGN_ASSET)
        )->fetchColumn(),
        'com_content' => (int) $pdo->query(
            "SELECT COUNT(*) FROM {$assets} WHERE name LIKE 'com_content%'"
        )->fetchColumn(),
        'root'        => (int) $pdo->query("SELECT COUNT(*) FROM {$assets} WHERE name = 'root.1'")->fetchColumn(),
        'bsms_tables' => (int) $pdo->query(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '
            . $pdo->quote($site->database()) . ' AND table_name LIKE '
            . $pdo->quote($site->prefix() . 'bsms_%') . ' AND table_name NOT IN ('
            . implode(',', array_map(
                static fn ($t) => $pdo->quote($site->prefix() . $t),
                LIBRARY_TABLES
            )) . ')'
        )->fetchColumn(),
    ];

    printf(
        "seeded: %d Proclaim assets, %d foreign, %d com_content, %d bsms tables\n",
        $before['proclaim_assets'],
        $before['foreign_asset'],
        $before['com_content'],
        $before['bsms_tables']
    );

    if ($before['bsms_tables'] === 0) {
        echo "{$red}FAIL{$reset} no Proclaim tables present — install the package before running this.\n";
        $overall = false;

        continue;
    }

    // ⚠️ Without this the uninstall takes the no-op path and the run reports
    // green while testing nothing. Asserted, not assumed.
    $pdo->exec("UPDATE {$admin} SET drop_tables = 1 WHERE id = 1");
    $dropTables = (int) $pdo->query("SELECT drop_tables FROM {$admin} WHERE id = 1")->fetchColumn();

    if ($dropTables !== 1) {
        echo "{$red}FAIL{$reset} drop_tables could not be set — the destructive path would not run.\n";
        $overall = false;

        continue;
    }

    // ----------------------------------------------------------- uninstall --
    // ⚠️ The package, not the component. Everything Proclaim ships carries
    // package_id = the pkg_proclaim row, and Joomla refuses to uninstall a
    // package-owned extension on its own — "Extension not removed", with no
    // reason given. Removing the package is also how an administrator does it,
    // so this is the path worth testing rather than a workaround for it.
    $target = $pdo->query(
        "SELECT extension_id, element FROM {$extensions} WHERE type = 'package' AND element = 'pkg_proclaim'"
    )->fetch(\PDO::FETCH_ASSOC);

    // A component installed on its own, without the package, is still a
    // supported shape — fall back to it rather than failing.
    if (!$target) {
        $target = $pdo->query(
            "SELECT extension_id, element FROM {$extensions} WHERE type = 'component' AND element = 'com_proclaim'"
        )->fetch(\PDO::FETCH_ASSOC);
    }

    if (!$target) {
        echo "{$red}FAIL{$reset} neither pkg_proclaim nor com_proclaim is registered — nothing to uninstall.\n";
        $overall = false;

        continue;
    }

    $extensionId = (int) $target['extension_id'];

    echo "uninstalling {$target['element']} (extension_id {$extensionId}) with drop_tables = 1\n";

    // ⚠️ Joomla's own console command, not a hand-booted application. An
    // earlier attempt constructed AdministratorApplication directly and had to
    // fake $_SERVER for the Uri, then wire the session aliases that
    // administrator/includes/app.php registers, and then still failed to
    // autoload the extension plugins the installer dispatches events to.
    // cli/joomla.php is the supported path and gets all of that right.
    $command = \sprintf(
        'cd %s && php cli/joomla.php extension:remove %d --no-interaction 2>&1',
        escapeshellarg($install->path),
        $extensionId
    );

    $output = [];
    exec($command, $output, $status);

    foreach ($output as $line) {
        echo '  | ' . $line . "\n";
    }

    if ($status !== 0) {
        echo "{$red}FAIL{$reset} the uninstall itself errored (exit {$status}).\n";
        $overall = false;
    }

    // -------------------------------------------------------------- verify --
    $after = [
        'proclaim_assets' => (int) $pdo->query(
            "SELECT COUNT(*) FROM {$assets} WHERE name = 'com_proclaim' OR name LIKE 'com_proclaim.%'"
        )->fetchColumn(),
        'foreign_asset' => (int) $pdo->query(
            "SELECT COUNT(*) FROM {$assets} WHERE name = " . $pdo->quote(FOREIGN_ASSET)
        )->fetchColumn(),
        'com_content' => (int) $pdo->query(
            "SELECT COUNT(*) FROM {$assets} WHERE name LIKE 'com_content%'"
        )->fetchColumn(),
        'root'        => (int) $pdo->query("SELECT COUNT(*) FROM {$assets} WHERE name = 'root.1'")->fetchColumn(),
        'bsms_tables' => (int) $pdo->query(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '
            . $pdo->quote($site->database()) . ' AND table_name LIKE '
            . $pdo->quote($site->prefix() . 'bsms_%') . ' AND table_name NOT IN ('
            . implode(',', array_map(
                static fn ($t) => $pdo->quote($site->prefix() . $t),
                LIBRARY_TABLES
            )) . ')'
        )->fetchColumn(),
    ];

    /**
     * label => [passed, detail]
     *
     * The foreign-row assertions are first because they are the ones testing a
     * change rather than restating that a delete deletes.
     */
    $checks = [
        'another extension sharing our prefix keeps its asset row' => [$after['foreign_asset'] === 1, "{$before['foreign_asset']} -> {$after['foreign_asset']} (want 1)"],
        'com_content keeps every asset row'                        => [$after['com_content'] === $before['com_content'], "{$before['com_content']} -> {$after['com_content']}"],
        'the ACL root survives'                                    => [$after['root'] === 1, "{$before['root']} -> {$after['root']} (want 1)"],
        "Proclaim's own asset rows are gone"                       => [$after['proclaim_assets'] === 0, "{$before['proclaim_assets']} -> {$after['proclaim_assets']} (want 0)"],
        'the Proclaim tables are dropped'                          => [$after['bsms_tables'] === 0, "{$before['bsms_tables']} -> {$after['bsms_tables']} (want 0)"],
    ];

    foreach ($checks as $label => [$passed, $detail]) {
        printf("%s %-56s %s\n", $passed ? "{$green}PASS{$reset}" : "{$red}FAIL{$reset}", $label, $detail);

        if (!$passed) {
            $overall = false;
        }
    }

    // Left behind deliberately when it worked: a Proclaim-free site is the
    // state this test exists to produce. Only say so when it is true — an
    // earlier version printed this after a failed uninstall, which described
    // the opposite of what had happened.
    if ($after['bsms_tables'] === 0) {
        echo "{$yellow}NOTE{$reset} {$install->id} is now Proclaim-free. "
            . "Run composer test:install to restore it.\n";
    }
}

echo "\n" . ($overall ? "{$green}Uninstall test passed{$reset}\n" : "{$red}Uninstall test FAILED{$reset}\n");

exit($overall ? 0 : 1);
