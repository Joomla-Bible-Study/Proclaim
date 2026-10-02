<?php

/**
 * Seed layer: one server per addon type.
 *
 * A fresh install seeds only legacy servers, which new media may not use, so anything
 * that needs to attach media (E2E, the content scenarios, demo checks) would otherwise
 * have to create its own server first. This layer gives every supported addon type a row
 * named "<marker><type>", declared in `servers.json`.
 *
 * Servers hold no credentials: params and media are left empty and the addon's own
 * defaults apply, so no seeded server can reach an outside account. A type that needs a
 * component the site does not have (Docman, Virtuemart) is skipped and reported.
 *
 * Run two ways:
 *
 *   php build/seed/servers.php apply|remove|check
 *       Standalone: every `role = test` install in build.properties.
 *
 *   cwm-seed ... (layer `servers`)
 *       Under cwm-seed, which names one site through CWM_SEED_* variables.
 *
 * `check` reads the declaration and the site: every declared type must have an addon class
 * in the repository, and every server that is not skipped must exist, be published and be of
 * that type. It exits 1 on any miss.
 *
 * Idempotent. Servers are matched by name, so a second `apply` leaves one row per type, and
 * `remove` deletes only rows carrying the marker, after detaching nothing: media attached to a
 * seeded server is another layer's and is removed by it first.
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

declare(strict_types=1);

use CWM\BuildTools\Dev\TestSite;

$root = \dirname(__DIR__, 2);

require $root . '/libraries/vendor/autoload.php';
require __DIR__ . '/lib.php';

$action = $argv[1] ?? 'apply';

if (!\in_array($action, ['apply', 'remove', 'check'], true)) {
    fwrite(STDERR, "Usage: php build/seed/servers.php apply|remove|check\n");

    exit(2);
}

$declared = json_decode((string) file_get_contents(__DIR__ . '/servers.json'), true, 512, JSON_THROW_ON_ERROR);
$servers  = (array) ($declared['servers'] ?? []);
$marker   = getenv('CWM_SEED_MARKER') ?: markerFromConfig($root);

$failures = 0;

foreach (targets($root) as $label => $makeSite) {
    echo "=== {$action} servers on {$label} ===\n";

    try {
        $site = $makeSite();

        $failures += match ($action) {
            'apply'  => seedServers($site, $servers, $marker),
            'remove' => removeServers($site, $marker),
            default  => checkServers($site, $servers, $marker, $root),
        };
    } catch (\RuntimeException | \PDOException $e) {
        fwrite(STDERR, '  ' . $e->getMessage() . "\n");
        $failures++;
    }

    echo "\n";
}

if ($failures > 0) {
    fwrite(STDERR, "Server seeding ({$action}) failed: {$failures} problem(s).\n");

    exit(1);
}

echo 'Servers ' . ($action === 'apply' ? 'applied' : ($action === 'remove' ? 'removed' : 'checked')) . ".\n";

/**
 * Whether the site has the component a server type needs (always true when none is named).
 */
function hasRequirement(TestSite $site, array $server): bool
{
    $component = (string) ($server['requires'] ?? '');

    if ($component === '') {
        return true;
    }

    return scalar($site->db(), 'SELECT extension_id FROM ' . $site->table('#__extensions')
        . ' WHERE type = ' . $site->db()->quote('component') . ' AND element = ' . $site->db()->quote($component)) !== null;
}

/**
 * Create the declared servers that are missing; leave existing ones alone.
 *
 * @param  list<array<string, string>>  $servers
 *
 * @return int  Problems found (always 0: a skipped type is reported, not a failure)
 */
function seedServers(TestSite $site, array $servers, string $marker): int
{
    $db = $site->db();

    if (scalar($db, 'SELECT COUNT(*) FROM ' . $site->table('#__bsms_servers')) === null) {
        throw new \RuntimeException('com_proclaim is not installed here — run test:install first.');
    }

    $find   = $db->prepare('SELECT id FROM ' . $site->table('#__bsms_servers') . ' WHERE server_name = ?');
    $insert = $db->prepare(
        'INSERT INTO ' . $site->table('#__bsms_servers')
        . " (server_name, type, published, access, params, media, created) VALUES (?, ?, 1, 1, '{}', '{}', ?)"
    );

    foreach ($servers as $server) {
        $type = (string) $server['type'];
        $name = $marker . $type;

        if (!hasRequirement($site, $server)) {
            echo \sprintf("  - %-22s skipped: %s is not installed\n", $name, $server['requires']);

            continue;
        }

        $find->execute([$name]);
        $id = $find->fetchColumn();

        if ($id !== false) {
            echo \sprintf("  = %-22s already present (id %d)\n", $name, $id);

            continue;
        }

        $insert->execute([$name, $type, gmdate('Y-m-d H:i:s')]);
        echo \sprintf("  + %-22s id %d\n", $name, $db->lastInsertId());
    }

    return 0;
}

/**
 * Delete the servers this layer wrote.
 *
 * @return int  Problems found: a seeded server that media still points at is left in place and counted
 */
function removeServers(TestSite $site, string $marker): int
{
    $db     = $site->db();
    $like   = $marker . '%';
    $in     = $db->prepare(
        'SELECT s.id, s.server_name, COUNT(m.id) AS used FROM ' . $site->table('#__bsms_servers') . ' AS s '
        . 'LEFT JOIN ' . $site->table('#__bsms_mediafiles') . ' AS m ON m.server_id = s.id '
        . 'WHERE s.server_name LIKE ? GROUP BY s.id, s.server_name'
    );
    $in->execute([$like]);

    $delete   = $db->prepare('DELETE FROM ' . $site->table('#__bsms_servers') . ' WHERE id = ?');
    $problems = 0;

    foreach ($in->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if ((int) $row['used'] > 0) {
            fwrite(STDERR, \sprintf("  ! %s kept: %d media file(s) still use it\n", $row['server_name'], $row['used']));
            $problems++;

            continue;
        }

        $delete->execute([(int) $row['id']]);
        echo "  - {$row['server_name']}\n";
    }

    return $problems;
}

/**
 * Check the declaration against the repository and the site.
 *
 * @param  list<array<string, string>>  $servers
 *
 * @return int  Problems found
 */
function checkServers(TestSite $site, array $servers, string $marker, string $root): int
{
    $problems = 0;
    $db       = $site->db();
    $find     = $db->prepare('SELECT type, published FROM ' . $site->table('#__bsms_servers') . ' WHERE server_name = ?');

    foreach ($servers as $server) {
        $type  = (string) $server['type'];
        $name  = $marker . $type;
        $class = $root . '/admin/src/Addons/Servers/' . ucfirst($type) . '/CWMAddon' . ucfirst($type) . '.php';

        if (!is_file($class)) {
            fwrite(STDERR, "  ! {$type}: no addon class at " . substr($class, \strlen($root) + 1) . "\n");
            $problems++;

            continue;
        }

        if (!hasRequirement($site, $server)) {
            echo \sprintf("  - %-22s skipped: %s is not installed\n", $name, $server['requires']);

            continue;
        }

        $find->execute([$name]);
        $row = $find->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            fwrite(STDERR, "  ! {$name}: not seeded\n");
            $problems++;
        } elseif ($row['type'] !== $type || (int) $row['published'] !== 1) {
            fwrite(STDERR, "  ! {$name}: type {$row['type']}, published {$row['published']}\n");
            $problems++;
        } else {
            echo \sprintf("  ok %-22s\n", $name);
        }
    }

    $undeclared = array_diff(
        array_map(static fn (string $dir): string => strtolower(basename($dir)), glob($root . '/admin/src/Addons/Servers/*', GLOB_ONLYDIR) ?: []),
        array_merge(array_column($servers, 'type'), ['legacy'])
    );

    foreach ($undeclared as $type) {
        fwrite(STDERR, "  ! addon \"{$type}\" exists but servers.json does not declare it\n");
        $problems++;
    }

    return $problems;
}
