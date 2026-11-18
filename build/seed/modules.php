<?php

/**
 * Seed layer: module instances, one per behaviour worth seeing.
 *
 * A fresh install registers the modules but leaves each instance unpublished and without a
 * position, so no module renders anywhere and the whole path from parameters to page is
 * untested. This layer, declared in `modules.json`, places the sermons module with its main
 * parameter variations, the podcast and YouTube modules, one instance a guest may not see, one
 * unpublished, and the admin icon module with every tile on.
 *
 * Parameters are written as strings, the way the admin form saves them.
 *
 * Instances are assigned to the seeded landing menu item (`page` in the declaration) and carry the seed marker in `note`, which is how
 * `remove` finds them. The instances the installer wrote are left alone.
 *
 * Run two ways:
 *
 *   php build/seed/modules.php apply|remove|check
 *       Standalone: every `role = test` install in build.properties.
 *
 *   cwm-seed ... (layer `modules`)
 *       Under cwm-seed, which names one site through CWM_SEED_* variables.
 *
 * `check` compares the database with the declaration (installed, client, position, published,
 * access, assigned to the landing page). build/verify-frontend.php then fetches a page as a guest and
 * checks which module titles appear.
 *
 * The servers, content and menus layers must have run: some parameters name a seeded server or
 * teacher, and the instances are assigned to a seeded menu item.
 * Idempotent: `apply` removes what it wrote first.
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
    fwrite(STDERR, "Usage: php build/seed/modules.php apply|remove|check\n");

    exit(2);
}

$declared = json_decode((string) file_get_contents(__DIR__ . '/modules.json'), true, 512, JSON_THROW_ON_ERROR);
$marker   = getenv('CWM_SEED_MARKER') ?: markerFromConfig($root);
$note     = $marker . 'modules';

$failures = 0;

foreach (targets($root) as $label => $makeSite) {
    echo "=== {$action} modules on {$label} ===\n";

    try {
        $site = $makeSite();

        match ($action) {
            'apply'  => applyModules($site, $declared['modules'], $marker, $note, $declared['page']),
            'remove' => removeModules($site, $note),
            default  => $failures += checkModules($site, $declared['modules'], $marker, $note, $declared['page']),
        };
    } catch (\RuntimeException | \PDOException $e) {
        fwrite(STDERR, '  ' . $e->getMessage() . "\n");
        $failures++;
    }

    echo "\n";
}

if ($failures > 0) {
    fwrite(STDERR, "Module seeding ({$action}) failed: {$failures} problem(s).\n");

    exit(1);
}

echo 'Modules ' . ($action === 'apply' ? 'applied' : ($action === 'remove' ? 'removed' : 'checked')) . ".\n";

/**
 * The client id for a declared client name.
 */
function clientId(array $module): int
{
    return ($module['client'] ?? 'site') === 'administrator' ? 1 : 0;
}

/**
 * Resolve the "@template", "@server:<type>" and "@teacher:<key>" placeholders in a params array.
 *
 * @param  array<string, mixed>  $params
 *
 * @return array<string, mixed>
 *
 * @throws \RuntimeException  when a placeholder names something the site does not have
 */
function resolveParams(TestSite $site, array $params, string $marker): array
{
    $db = $site->db();

    $resolve = static function (mixed $value, string $name) use ($site, $db, $marker): mixed {
        if (!\is_string($value) || !str_starts_with($value, '@')) {
            return $value;
        }

        [$kind, $arg] = array_pad(explode(':', substr($value, 1), 2), 2, '');

        $id = match ($kind) {
            'template' => scalar($db, 'SELECT id FROM ' . $site->table('#__bsms_templates') . ' WHERE published = 1 ORDER BY id LIMIT 1'),
            'server'   => scalar($db, 'SELECT id FROM ' . $site->table('#__bsms_servers') . ' WHERE server_name = ' . $db->quote($marker . $arg)),
            'teacher'  => scalar($db, 'SELECT id FROM ' . $site->table('#__bsms_teachers') . ' WHERE alias = ' . $db->quote($marker . $arg)),
            default    => throw new \RuntimeException("module parameter \"{$name}\" has an unknown placeholder \"{$value}\"."),
        };

        if ($id === null) {
            throw new \RuntimeException("module parameter \"{$name}\" needs {$value}, which this site does not have — run the servers and content layers first.");
        }

        return $id;
    };

    // The admin form saves every parameter as a string, and a multi-select as a list of strings. The module
    // reads some of them with a strict comparison (simple_mode against '1'), so a number written here would
    // exercise a combination no real site has.
    $string = static fn (mixed $value): mixed => \is_scalar($value) ? (string) $value : $value;

    foreach ($params as $name => $value) {
        $params[$name] = \is_array($value)
            ? array_map(static fn (mixed $item): mixed => $string($resolve($item, $name)), $value)
            : $string($resolve($value, $name));
    }

    return $params;
}

/**
 * Write the declared module instances.
 *
 * @param  list<array<string, mixed>>  $modules
 *
 * @throws \RuntimeException  when a module's extension is not installed or a placeholder cannot be resolved
 */
function applyModules(TestSite $site, array $modules, string $marker, string $note, string $page): void
{
    $db = $site->db();

    removeModules($site, $note, true);

    // Site instances go on the one seeded menu item built to carry them, not on every page: the
    // browser suites open bare component URLs and follow the first card link, and a module's
    // cards would be first in the document. Administrator instances have no menu assignment.
    $pageId = scalar($db, 'SELECT id FROM ' . $site->table('#__menu')
        . ' WHERE client_id = 0 AND alias = ' . $db->quote($page) . ' AND note = ' . $db->quote($marker . 'menus'));

    if ($pageId === null) {
        throw new \RuntimeException("no \"{$page}\" menu item to carry the modules — run the menus layer first.");
    }

    $installed = $db->prepare(
        'SELECT COUNT(*) FROM ' . $site->table('#__extensions') . " WHERE type = 'module' AND element = ? AND client_id = ?"
    );
    $insert = $db->prepare(
        'INSERT INTO ' . $site->table('#__modules')
        . ' (title, note, content, ordering, position, published, module, access, showtitle, params, client_id, language) '
        . "VALUES (?, ?, '', ?, ?, ?, ?, ?, 1, ?, ?, '*')"
    );
    $assign = $db->prepare('INSERT INTO ' . $site->table('#__modules_menu') . ' (moduleid, menuid) VALUES (?, ?)');

    foreach ($modules as $order => $module) {
        $client = clientId($module);

        $installed->execute([$module['module'], $client]);

        if ((int) $installed->fetchColumn() === 0) {
            throw new \RuntimeException("{$module['module']} is not installed here — run test:install first.");
        }

        $insert->execute([
            $module['title'],
            $note,
            $order + 1,
            $module['position'],
            $module['published'] ?? 1,
            $module['module'],
            $module['access'] ?? 1,
            json_encode(resolveParams($site, $module['params'], $marker), JSON_THROW_ON_ERROR),
            $client,
        ]);

        $id = (int) $db->lastInsertId();
        $assign->execute([$id, $client === 0 ? (int) $pageId : 0]);

        printf("  + %-18s id %-5d %-14s %s%s\n", $module['key'], $id, $module['position'], $module['module'], $client === 1 ? ' (administrator)' : '');
    }
}

/**
 * Delete the module instances this layer wrote, and their page assignments.
 */
function removeModules(TestSite $site, string $note, bool $quiet = false): void
{
    $db = $site->db();

    $find = $db->prepare('SELECT id FROM ' . $site->table('#__modules') . ' WHERE note = ?');
    $find->execute([$note]);
    $ids = array_map('intval', $find->fetchAll(PDO::FETCH_COLUMN));

    if ($ids === []) {
        return;
    }

    $in = implode(',', $ids);

    $db->exec('DELETE FROM ' . $site->table('#__modules_menu') . " WHERE moduleid IN ({$in})");
    $db->exec('DELETE FROM ' . $site->table('#__modules') . " WHERE id IN ({$in})");

    if (!$quiet) {
        echo '  - ' . \count($ids) . " module instance(s)\n";
    }
}

/**
 * Compare the database with the declaration.
 *
 * @param  list<array<string, mixed>>  $modules
 *
 * @return int  Problems found
 */
function checkModules(TestSite $site, array $modules, string $marker, string $note, string $page): int
{
    $db       = $site->db();
    $problems = 0;

    $pageId = (int) scalar($db, 'SELECT id FROM ' . $site->table('#__menu')
        . ' WHERE client_id = 0 AND alias = ' . $db->quote($page) . ' AND note = ' . $db->quote($marker . 'menus'));

    $find = $db->prepare(
        'SELECT m.id, m.position, m.published, m.access, m.client_id, '
        . '(SELECT GROUP_CONCAT(mm.menuid) FROM ' . $site->table('#__modules_menu') . ' AS mm WHERE mm.moduleid = m.id) AS assigned '
        . 'FROM ' . $site->table('#__modules') . ' AS m WHERE m.note = ? AND m.title = ?'
    );

    foreach ($modules as $module) {
        $find->execute([$note, $module['title']]);
        $row = $find->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            fwrite(STDERR, "  ! {$module['key']}: not seeded\n");
            $problems++;

            continue;
        }

        $expected = [
            'position'  => $module['position'],
            'published' => $module['published'] ?? 1,
            'access'    => $module['access'] ?? 1,
            'client_id' => clientId($module),
            'assigned'  => clientId($module) === 0 ? $pageId : 0,
        ];
        $wrong = [];

        foreach ($expected as $column => $value) {
            if ((string) $row[$column] !== (string) $value) {
                $wrong[] = "{$column} {$row[$column]} (expected {$value})";
            }
        }

        if ($wrong !== []) {
            fwrite(STDERR, "  ! {$module['key']}: " . implode(', ', $wrong) . "\n");
            $problems++;
        } else {
            echo "  ok {$module['key']}\n";
        }
    }

    return $problems;
}
