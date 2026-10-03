<?php

/**
 * Plumbing shared by the seed layers: which sites to work on, the marker, a query helper.
 *
 * Each layer is a script that runs standalone against every `role = test` install (how
 * `composer test:install` calls it) or under cwm-seed against one named site. Both entry
 * points resolve their sites here, so the safety rule lives in one place: standalone, only
 * `role = test` installs are touched; under cwm-seed, only the site cwm-seed names.
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

declare(strict_types=1);

use CWM\BuildTools\Dev\InstallConfig;
use CWM\BuildTools\Dev\PropertiesReader;
use CWM\BuildTools\Dev\TestSite;

/**
 * The marker the project declares in cwm-build.config.json, for standalone runs.
 */
function markerFromConfig(string $root): string
{
    $config = is_file($root . '/cwm-build.config.json')
        ? json_decode((string) file_get_contents($root . '/cwm-build.config.json'), true)
        : null;

    return (string) ($config['seed']['marker'] ?? 'cwmseed-');
}

/**
 * The sites to work on, by label, each as a function that connects.
 *
 * @return array<string, \Closure(): TestSite>
 */
function targets(string $root): array
{
    $path = (string) getenv('CWM_SEED_SITE_PATH');

    if ($path !== '') {
        $host = (string) getenv('CWM_SEED_DB_HOST');
        $id   = getenv('CWM_SEED_SITE_ID') ?: 'seed';

        // The address is passed along when one was recorded. TestSite uses it only if the host
        // configuration.php names does not resolve from here, and older build-tools ignore it.
        return [$id => static fn (): TestSite => TestSite::fromInstall(new InstallConfig(
            id: $id,
            path: $path,
            db: $host === '' ? [] : ['host' => $host]
        ))];
    }

    $installs = (new PropertiesReader($root . '/build.properties'))->installsFor('test');

    if ($installs === []) {
        fwrite(STDERR, "No role=test install in build.properties — nothing to seed.\n");

        exit(0);
    }

    $targets = [];

    foreach ($installs as $install) {
        $targets[$install->id . ' (' . $install->path . ')'] = static fn (): TestSite => TestSite::fromInstall($install);
    }

    return $targets;
}

/**
 * The first column of the first row as a string, or null for no row.
 */
function scalar(PDO $db, string $sql): ?string
{
    $value = $db->query($sql)->fetchColumn();

    return $value === false ? null : (string) $value;
}
