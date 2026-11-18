<?php

/**
 * Seed layer: a template for each layout the package ships besides the default one.
 *
 * Declared in `templates.json`. A Proclaim template is its params blob, so each seeded template
 * is a copy of the installed Default template's params with the declared ones merged over it,
 * never a partial blob. A template is applied through a menu item (a `t=` query parameter is
 * ignored once a menu item matches), so each declared page also gets a menu item linking the
 * view to its template. build/verify-frontend.php fetches every seeded menu item, and checks
 * that each of these pages carries the markup only its layout produces.
 *
 * Never writes `#__bsms_templatecode`: storing a row there writes PHP into the web root, and
 * rendering does not read that table anyway. Needs the content layer, for the teacher and
 * series the pages show.
 *
 * Run two ways:
 *
 *   php build/seed/templates.php apply|remove|check
 *       Standalone: every `role = test` install in build.properties.
 *
 *   cwm-seed ... (layer `templates`)
 *       Under cwm-seed, which names one site through CWM_SEED_* variables.
 *
 * `check` fails for a template or menu item that is missing, a stored param that differs from
 * the declaration, or a declared param that admin/forms/template.xml does not declare (unless
 * the entry lists it as `undeclared`): the form drops such keys on save, so a typo would seed a
 * state no site can have and the page check would still pass.
 *
 * Idempotent: templates are titled "<marker><key>: ..." and menu items noted "<marker>templates";
 * `apply` removes both before writing, and `remove` deletes exactly those.
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

/**
 * Where an `item` names its row: the table, and the alias prefix the content layer gives it.
 */
const ITEM_TABLES = [
    'teacher' => ['#__bsms_teachers', ''],
    'series'  => ['#__bsms_series', 'series-'],
];

$action = $argv[1] ?? 'apply';

if (!\in_array($action, ['apply', 'remove', 'check'], true)) {
    fwrite(STDERR, "Usage: php build/seed/templates.php apply|remove|check\n");

    exit(2);
}

$declared = json_decode((string) file_get_contents(__DIR__ . '/templates.json'), true, 512, JSON_THROW_ON_ERROR);
$marker   = getenv('CWM_SEED_MARKER') ?: markerFromConfig($root);

$failures = 0;

foreach (targets($root) as $label => $makeSite) {
    echo "=== {$action} templates on {$label} ===\n";

    try {
        $site = $makeSite();

        $failures += match ($action) {
            'apply'  => applyTemplates($site, $declared['templates'], $marker),
            'remove' => removeTemplates($site, $marker),
            default  => checkTemplates($site, $declared['templates'], $marker, $root),
        };
    } catch (\RuntimeException | \PDOException | \JsonException $e) {
        fwrite(STDERR, '  ' . $e->getMessage() . "\n");
        $failures++;
    }

    echo "\n";
}

if ($failures > 0) {
    fwrite(STDERR, "Template seeding ({$action}) failed: {$failures} problem(s).\n");

    exit(1);
}

echo 'Templates ' . ($action === 'apply' ? 'applied' : ($action === 'remove' ? 'removed' : 'checked')) . ".\n";

/**
 * Write each declared template and a menu item for each of its pages.
 *
 * @param  list<array<string, mixed>>  $templates
 *
 * @return int  Problems found
 *
 * @throws \RuntimeException  when the install lacks the Default template, the component or a site menu
 * @throws \JsonException
 */
function applyTemplates(TestSite $site, array $templates, string $marker): int
{
    removeTemplates($site, $marker);

    $db      = $site->db();
    $default = $db->query('SELECT type, params, access FROM ' . $site->table('#__bsms_templates')
        . ' WHERE published = 1 ORDER BY id LIMIT 1')->fetch(PDO::FETCH_ASSOC);

    if ($default === false) {
        throw new \RuntimeException('no published template to copy — the install seed did not land.');
    }

    $base        = json_decode((string) ($default['params'] ?: '{}'), true, 512, JSON_THROW_ON_ERROR) ?: [];
    $componentId = scalar($db, 'SELECT extension_id FROM ' . $site->table('#__extensions')
        . " WHERE element = 'com_proclaim' AND type = 'component'");
    $menutype    = scalar($db, 'SELECT menutype FROM ' . $site->table('#__menu')
        . ' WHERE client_id = 0 AND home = 1 AND published = 1 LIMIT 1');

    if ($componentId === null || $menutype === null) {
        throw new \RuntimeException('com_proclaim or a home menu is missing — run test:install first.');
    }

    $insertTemplate = $db->prepare('INSERT INTO ' . $site->table('#__bsms_templates')
        . ' (type, published, params, title, access) VALUES (?, 1, ?, ?, ?)');
    $insertItem     = $db->prepare(
        'INSERT INTO ' . $site->table('#__menu')
        . ' (menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, '
        . 'browserNav, access, img, template_style_id, params, lft, rgt, home, language, client_id) '
        . "VALUES (?, ?, ?, ?, ?, ?, 'component', 1, 1, 1, ?, 0, 1, '', 0, '{}', 0, 0, 0, '*', 0)"
    );
    $problems = 0;

    foreach ($templates as $template) {
        $insertTemplate->execute([
            $default['type'],
            json_encode(array_merge($base, $template['params']), JSON_THROW_ON_ERROR),
            $marker . $template['key'] . ': ' . $template['title'],
            $default['access'],
        ]);
        $templateId = (int) $db->lastInsertId();

        foreach ($template['pages'] as $page) {
            $link = 'index.php?option=com_proclaim&view=' . $page['view'];

            if (isset($page['item'])) {
                $itemId = itemId($site, $page['item'], $marker);

                if ($itemId === null) {
                    fwrite(STDERR, "  ! {$template['key']}: {$page['item']} is not seeded — run the content layer first.\n");
                    $problems++;

                    continue;
                }

                $link .= '&id=' . $itemId;
            }

            $alias = $marker . 'tpl-' . $template['key'] . '-' . $page['view'];
            $insertItem->execute([
                $menutype,
                $template['title'],
                $alias,
                $marker . 'templates',
                $alias,
                $link . '&t=' . $templateId,
                (int) $componentId,
            ]);
        }

        printf("  + %-22s template %d\n", $template['key'], $templateId);
    }

    rebuildTree($site);

    return $problems;
}

/**
 * Delete the seeded menu items and templates.
 *
 * @return int  Problems found (none)
 */
function removeTemplates(TestSite $site, string $marker): int
{
    $db = $site->db();

    $items = $db->prepare('DELETE FROM ' . $site->table('#__menu') . ' WHERE client_id = 0 AND note = ?');
    $items->execute([$marker . 'templates']);
    $templates = $db->prepare('DELETE FROM ' . $site->table('#__bsms_templates') . ' WHERE LEFT(title, ?) = ?');
    $templates->execute([\strlen($marker), $marker]);

    if ($items->rowCount() > 0) {
        rebuildTree($site);
    }

    echo "  removed {$templates->rowCount()} template(s), {$items->rowCount()} menu item(s)\n";

    return 0;
}

/**
 * Compare the database with the declaration, and the declaration with the template form.
 *
 * @param  list<array<string, mixed>>  $templates
 *
 * @return int  Problems found
 *
 * @throws \JsonException
 */
function checkTemplates(TestSite $site, array $templates, string $marker, string $root): int
{
    $db       = $site->db();
    $formKeys = templateFormKeys($root);
    $problems = 0;

    $find  = $db->prepare('SELECT id, params FROM ' . $site->table('#__bsms_templates') . ' WHERE title LIKE ?');
    $items = $db->prepare('SELECT COUNT(*) FROM ' . $site->table('#__menu') . ' WHERE client_id = 0 AND note = ? AND link LIKE ?');

    foreach ($templates as $template) {
        $key    = $template['key'];
        $before = $problems;

        foreach (array_keys($template['params']) as $param) {
            if (!isset($formKeys[$param]) && !\in_array($param, $template['undeclared'] ?? [], true)) {
                fwrite(STDERR, "  ! {$key}: param \"{$param}\" is not declared in template.xml (list it as undeclared if that is the point)\n");
                $problems++;
            }
        }

        $find->execute([$marker . $key . ':%']);
        $row = $find->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            fwrite(STDERR, "  ! {$key}: template missing\n");
            $problems++;

            continue;
        }

        $stored = json_decode((string) $row['params'], true, 512, JSON_THROW_ON_ERROR);

        foreach ($template['params'] as $param => $value) {
            if (($stored[$param] ?? null) !== $value) {
                fwrite(STDERR, "  ! {$key}: {$param} is " . json_encode($stored[$param] ?? null) . ', declared ' . json_encode($value) . "\n");
                $problems++;
            }
        }

        $items->execute([$marker . 'templates', '%&t=' . $row['id']]);

        if ((int) $items->fetchColumn() !== \count($template['pages'])) {
            fwrite(STDERR, "  ! {$key}: expected " . \count($template['pages']) . " menu item(s) for template {$row['id']}\n");
            $problems++;
        } elseif ($problems === $before) {
            echo "  ok {$key}\n";
        }
    }

    return $problems;
}

/**
 * The id of the content-layer row an `item` names ("teacher:<key>", "series:<key>").
 *
 * @throws \RuntimeException  for an unknown item kind
 */
function itemId(TestSite $site, string $item, string $marker): ?int
{
    [$kind, $key] = explode(':', $item, 2) + [1 => ''];

    if (!isset(ITEM_TABLES[$kind])) {
        throw new \RuntimeException("unknown item kind \"{$kind}\" in templates.json");
    }

    [$table, $prefix] = ITEM_TABLES[$kind];
    $find             = $site->db()->prepare('SELECT id FROM ' . $site->table($table) . ' WHERE alias = ?');
    $find->execute([$marker . $prefix . $key]);
    $id = $find->fetchColumn();

    return $id === false ? null : (int) $id;
}

/**
 * Every field name admin/forms/template.xml declares, as keys.
 *
 * @return array<string, true>
 *
 * @throws \RuntimeException  when the form cannot be read
 */
function templateFormKeys(string $root): array
{
    $xml = simplexml_load_file($root . '/admin/forms/template.xml');

    if ($xml === false) {
        throw new \RuntimeException('admin/forms/template.xml could not be read');
    }

    $keys = [];

    foreach ($xml->xpath('//field[@name]') ?: [] as $field) {
        $keys[(string) $field['name']] = true;
    }

    return $keys;
}
