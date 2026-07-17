<?php

/**
 * Tear Proclaim down on every `role = test` install so the next install/upgrade
 * starts from a genuine clean slate.
 *
 * A repeatable install/upgrade test must begin from a known state:
 *   - a stale #__extensions row makes Joomla route a fresh install to update()
 *     (so the "clean install" test would silently become an upgrade), and
 *   - stale #__bsms_* tables mask CREATE TABLE / ADD COLUMN failures.
 * This removes the whole Proclaim family (component, library, plugins, modules)
 * from #__extensions/#__schemas/#__assets/#__categories and drops the bsms_
 * tables, connecting straight to each test install's database.
 *
 * SAFETY: only installs marked `role = test` in build.properties are touched.
 * Never point a dev/production install at role = test.
 *
 * @package  Proclaim.Build
 * @since    __DEPLOY_VERSION__
 */

declare(strict_types=1);

use CWM\BuildTools\Dev\PropertiesReader;

$root = \dirname(__DIR__);

require $root . '/libraries/vendor/autoload.php';

$reader   = new PropertiesReader($root . '/build.properties');
$installs = $reader->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install in build.properties — nothing to reset.\n");

    exit(0);
}

foreach ($installs as $install) {
    echo "=== reset {$install->id} ({$install->path}) ===\n";

    $configFile = $install->path . '/configuration.php';

    if (!is_file($configFile)) {
        fwrite(STDERR, "  configuration.php not found — skipping.\n");

        continue;
    }

    $cfg = (static function (string $file): array {
        require $file;
        $c = new \JConfig();

        return [$c->host, $c->user, $c->password, $c->db, $c->dbprefix];
    })($configFile);

    [$host, $user, $pass, $name, $prefix] = $cfg;
    $port = 3306;

    if (str_contains($host, ':')) {
        [$host, $portStr] = explode(':', $host, 2);
        $port             = (int) $portStr;
    }

    $db = @new mysqli($host, $user, $pass, $name, $port);

    if ($db->connect_errno !== 0) {
        fwrite(STDERR, "  DB connection failed: {$db->connect_error}\n");

        exit(1);
    }

    // 1. Drop all Proclaim tables (prefix + bsms_*). Escape the prefix's
    //    LIKE metacharacters, then quote the resulting table names.
    $likeBsms = $db->real_escape_string($prefix) . 'bsms\_%';
    $res      = $db->query("SHOW TABLES LIKE '{$likeBsms}'");
    $dropped  = 0;

    if ($res instanceof mysqli_result) {
        while ($row = $res->fetch_row()) {
            $table = $row[0];
            $db->query('DROP TABLE IF EXISTS `' . str_replace('`', '', $table) . '`');
            $dropped++;
        }
    }

    echo "  dropped {$dropped} bsms_ table(s)\n";

    // 2. Remove the Proclaim extension family from #__extensions, capturing
    //    ids first so the matching #__schemas rows go too.
    $ext     = $prefix . 'extensions';
    $schemas = $prefix . 'schemas';

    $familyWhere = "element = 'com_proclaim'
        OR element = 'lib_cwmscripture'
        OR element LIKE '%proclaim%'
        OR element LIKE '%scripturelink%'
        OR element LIKE '%cwmscripture%'";

    $ids = [];
    $res = $db->query("SELECT extension_id FROM `{$ext}` WHERE {$familyWhere}");

    if ($res instanceof mysqli_result) {
        while ($row = $res->fetch_row()) {
            $ids[] = (int) $row[0];
        }
    }

    if ($ids !== []) {
        $idList = implode(',', $ids);
        $db->query("DELETE FROM `{$schemas}` WHERE extension_id IN ({$idList})");
        $db->query("DELETE FROM `{$ext}` WHERE extension_id IN ({$idList})");
    }

    echo '  removed ' . \count($ids) . " extension row(s) + their #__schemas rows\n";

    // 3. Clean up assets + categories owned by the component (best-effort).
    $assets = $prefix . 'assets';
    $cats   = $prefix . 'categories';
    $db->query("DELETE FROM `{$assets}` WHERE name = 'com_proclaim' OR name LIKE 'com_proclaim.%'");
    $db->query("DELETE FROM `{$cats}` WHERE extension = 'com_proclaim'");

    echo "  cleared com_proclaim assets + categories\n";

    // 3b. Admin menu items + update sites. Joomla treats leftovers here as an
    //     existing install (a stale update-site/menu makes a fresh install run
    //     the update SQL path against tables that don't exist yet), so a truly
    //     clean slate must remove them too.
    $menu     = $prefix . 'menu';
    $usites   = $prefix . 'update_sites';
    $usitesx  = $prefix . 'update_sites_extensions';

    $db->query("DELETE FROM `{$menu}` WHERE link LIKE '%option=com_proclaim%'");
    $db->query(
        "DELETE x FROM `{$usitesx}` x
         INNER JOIN `{$usites}` u ON u.update_site_id = x.update_site_id
         WHERE u.name LIKE '%roclaim%' OR u.name LIKE '%cripture%'"
    );
    $db->query("DELETE FROM `{$usites}` WHERE name LIKE '%roclaim%' OR name LIKE '%cripture%'");

    echo "  cleared com_proclaim admin menu items + update sites\n";

    // 4. Remove installed files on disk so the next install is genuinely fresh.
    //    Joomla refuses to install a library/component over an existing,
    //    unregistered folder, so a DB-only reset leaves the site un-installable.
    //    Scoped to the known Proclaim install locations under this test site.
    $siteDirs = [
        'administrator/components/com_proclaim',
        'components/com_proclaim',
        'api/components/com_proclaim',
        'media/com_proclaim',
        'libraries/cwmscripture',
        'media/lib_cwmscripture',
        'administrator/modules/mod_proclaimicon',
        'modules/mod_proclaim',
        'modules/mod_proclaim_podcast',
        'modules/mod_proclaim_youtube',
        'plugins/finder/proclaim',
        'plugins/schemaorg/proclaim',
        'plugins/system/proclaim',
        'plugins/task/proclaim',
        'plugins/webservices/proclaim',
        'plugins/content/scripturelinks',
        'plugins/task/cwmscripture',
        'administrator/manifests/packages/proclaim',
    ];

    $removed = 0;

    foreach ($siteDirs as $rel) {
        $abs = $install->path . '/' . $rel;

        if (is_dir($abs)) {
            rrmdir($abs);
            $removed++;
        }
    }

    echo "  removed {$removed} on-disk extension dir(s)\n";

    // 4b. Loose files Joomla scatters outside the component folders: install
    //     manifests (their presence makes a fresh install register as an UPDATE
    //     and run ALTER SQL against tables that don't exist yet), language files
    //     across every locale, and stale language cache. Glob so we catch every
    //     locale/plugin/module variant, not a hand-maintained list.
    $globs = [
        'administrator/manifests/libraries/cwmscripture.xml',
        'administrator/manifests/packages/pkg_proclaim.xml',
        'administrator/language/*/*proclaim*',
        'administrator/language/*/*cwmscripture*',
        'administrator/language/*/*scripturelinks*',
        'language/*/*proclaim*',
        'language/*/*cwmscripture*',
        'api/language/*/*proclaim*',
        'administrator/cache/language/*proclaim*',
        'administrator/cache/language/*cwmscripture*',
    ];

    $files = 0;

    foreach ($globs as $glob) {
        foreach (glob($install->path . '/' . $glob) ?: [] as $file) {
            if (is_file($file) && @unlink($file)) {
                $files++;
            }
        }
    }

    echo "  removed {$files} loose manifest/language file(s)\n";

    $db->close();
}

echo "Reset complete.\n";

/**
 * Recursively delete a directory.
 *
 * @since __DEPLOY_VERSION__
 */
function rrmdir(string $dir): void
{
    $items = scandir($dir);

    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . '/' . $item;

        if (is_dir($path) && !is_link($path)) {
            rrmdir($path);
        } else {
            @unlink($path);
        }
    }

    @rmdir($dir);
}
