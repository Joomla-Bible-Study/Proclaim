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

/**
 * Why the current target may not receive accounts with a published password, or null when it may.
 *
 * Standalone, only `role = test` installs are ever targeted, and those are reset by the release
 * gate. Under cwm-seed the role comes from the environment: a test install is fine, and a dev
 * install only when cwm-site-create made it (it leaves `.ddev/docker-compose.cwm.yaml`), because
 * every other dev install is somebody's working copy, often with real data behind it.
 */
function disposable(): ?string
{
    $path = (string) getenv('CWM_SEED_SITE_PATH');

    if ($path === '') {
        return null;
    }

    $role = (string) getenv('CWM_SEED_SITE_ROLE');

    if ($role === 'test') {
        return null;
    }

    if ($role === 'dev' && is_file($path . '/.ddev/docker-compose.cwm.yaml')) {
        return null;
    }

    return "not a disposable site (role \"{$role}\", no cwm-site-create marker at {$path}/.ddev/docker-compose.cwm.yaml)";
}

/**
 * The site secret Joomla signs API tokens with, read from configuration.php.
 *
 * @throws \RuntimeException  when it cannot be read
 */
function siteSecret(string $path): string
{
    $source = is_file($path . '/configuration.php') ? (string) file_get_contents($path . '/configuration.php') : '';

    if (preg_match('/public\s+\$secret\s*=\s*([\'"])(.*?)\1\s*;/s', $source, $m) !== 1 || $m[2] === '') {
        throw new \RuntimeException("cannot read the site secret from {$path}/configuration.php.");
    }

    return stripslashes($m[2]);
}

/**
 * The bearer token for a user whose token seed is stored, or null when the user has none.
 *
 * Joomla's token plugin keeps a seed per user and accepts base64("sha256:<id>:<hmac of the seed under
 * the site secret>"), so the token follows from the database and configuration.php alone.
 *
 * @throws \RuntimeException  when the site secret cannot be read
 */
function apiToken(TestSite $site, string $username, string $path): ?string
{
    $statement = $site->db()->prepare(
        'SELECT u.id, p.profile_value FROM ' . $site->table('#__users') . ' AS u '
        . 'JOIN ' . $site->table('#__user_profiles') . " AS p ON p.user_id = u.id AND p.profile_key = 'joomlatoken.token' "
        . 'WHERE u.username = ?'
    );
    $statement->execute([$username]);
    $found = $statement->fetch(PDO::FETCH_ASSOC);
    $seed  = $found === false ? false : base64_decode((string) $found['profile_value'], true);

    if ($seed === false || $seed === '') {
        return null;
    }

    return base64_encode('sha256:' . $found['id'] . ':' . hash_hmac('sha256', $seed, siteSecret($path)));
}

/**
 * Run a command of Joomla's own command line on a site, the way that site is meant to be run.
 *
 * A DDEV site's database host (`db`) only resolves inside its container, so those run through
 * `ddev exec` from the site folder; any other site runs under the PHP that is running this script.
 *
 * @param  list<string>  $arguments  e.g. ['finder:index']
 *
 * @return array{0: int, 1: string}  Exit code and combined output
 */
function runJoomlaCli(string $sitePath, array $arguments): array
{
    $command = is_dir($sitePath . '/.ddev')
        ? array_merge(['ddev', 'exec', 'php', 'cli/joomla.php'], $arguments)
        : array_merge([PHP_BINARY, $sitePath . '/cli/joomla.php'], $arguments);

    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $sitePath);

    if (!\is_resource($process)) {
        return [1, 'could not start ' . $command[0]];
    }

    $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);

    fclose($pipes[1]);
    fclose($pipes[2]);

    return [proc_close($process), preg_replace('/\e\[[0-9;]*m/', '', $output) ?? $output];
}
