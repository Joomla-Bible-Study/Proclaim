<?php

/**
 * Seed layer: the Proclaim plugins a site should have working.
 *
 * Declared in `plugins.json`: the system, content, finder, schema.org, task and webservices
 * plugins. `apply` enables any that are installed but off, which is a precondition, not a
 * fixture: with a plugin off, the checks that depend on it say nothing. `remove` leaves them
 * enabled, because switching a plugin off cannot be told apart from a site that never had it on.
 *
 * `apply` also gives one seeded study a saved schema.org row, stored under the form context the
 * admin edit form writes; `remove` deletes it. Run after the content layer, which makes the study.
 *
 * Run two ways:
 *
 *   php build/seed/plugins.php apply|remove|check
 *       Standalone: every `role = test` install in build.properties.
 *
 *   cwm-seed ... (layer `plugins`)
 *       Under cwm-seed, which names one site through CWM_SEED_* variables.
 *
 * `check` fails for a plugin that is not installed or not enabled. What the plugins do for a
 * guest is checked by build/verify-frontend.php: the webservices routes answer 401 without a
 * token (404 if not registered), and the sermon page carries its own schema.org node.
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
    fwrite(STDERR, "Usage: php build/seed/plugins.php apply|remove|check\n");

    exit(2);
}

$declared = json_decode((string) file_get_contents(__DIR__ . '/plugins.json'), true, 512, JSON_THROW_ON_ERROR);
$marker   = getenv('CWM_SEED_MARKER') ?: markerFromConfig($root);

$failures = 0;

foreach (targets($root) as $label => $makeSite) {
    echo "=== {$action} plugins on {$label} ===\n";

    try {
        $site = $makeSite();

        $failures += match ($action) {
            'apply'  => enablePlugins($site, $declared['plugins']) + storeSchema($site, $declared['schemaorg']['stored'], $marker),
            'remove' => leavePlugins() + removeSchema($site, $declared['schemaorg']['stored'], $marker),
            default  => checkPlugins($site, $declared['plugins']),
        };
    } catch (\RuntimeException | \PDOException $e) {
        fwrite(STDERR, '  ' . $e->getMessage() . "\n");
        $failures++;
    }

    echo "\n";
}

if ($failures > 0) {
    fwrite(STDERR, "Plugin seeding ({$action}) failed: {$failures} problem(s).\n");

    exit(1);
}

echo 'Plugins ' . ($action === 'apply' ? 'applied' : ($action === 'remove' ? 'left alone' : 'checked')) . ".\n";

/**
 * Nothing to undo: see the file comment.
 *
 * @return int  Problems found (none)
 */
function leavePlugins(): int
{
    echo "  plugins are left as they are\n";

    return 0;
}

/**
 * Enable every declared plugin that is installed and off.
 *
 * @param  list<array<string, string>>  $plugins
 *
 * @return int  Problems found: a declared plugin that is not installed
 */
function enablePlugins(TestSite $site, array $plugins): int
{
    $db       = $site->db();
    $problems = 0;

    $find   = $db->prepare('SELECT extension_id, enabled FROM ' . $site->table('#__extensions') . " WHERE type = 'plugin' AND folder = ? AND element = ?");
    $enable = $db->prepare('UPDATE ' . $site->table('#__extensions') . ' SET enabled = 1 WHERE extension_id = ?');

    foreach ($plugins as $plugin) {
        $name = $plugin['folder'] . '/' . $plugin['element'];
        $find->execute([$plugin['folder'], $plugin['element']]);
        $row = $find->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            fwrite(STDERR, "  ! {$name}: not installed — run test:install first.\n");
            $problems++;

            continue;
        }

        if ((int) $row['enabled'] === 1) {
            echo "  = {$name}\n";

            continue;
        }

        $enable->execute([(int) $row['extension_id']]);
        echo "  + {$name} enabled\n";
    }

    return $problems;
}

/**
 * The seeded study a stored schema row is given, or null if the content layer has not run.
 *
 * @param  array{study: string, name: string}  $stored
 */
function storedSchemaStudy(TestSite $site, array $stored, string $marker): ?int
{
    $find = $site->db()->prepare('SELECT id FROM ' . $site->table('#__bsms_studies') . ' WHERE alias = ?');
    $find->execute([$marker . $stored['study']]);
    $id = $find->fetchColumn();

    return $id === false ? null : (int) $id;
}

/**
 * Give the declared study a saved schema row, the way the admin edit form stores one.
 *
 * @param  array{study: string, name: string}  $stored
 *
 * @return int  Problems found: the study is not seeded
 */
function storeSchema(TestSite $site, array $stored, string $marker): int
{
    $studyId = storedSchemaStudy($site, $stored, $marker);

    if ($studyId === null) {
        fwrite(STDERR, "  ! schema.org: study \"{$stored['study']}\" is not seeded — run the content layer first.\n");

        return 1;
    }

    removeSchema($site, $stored, $marker);

    $schema = json_encode(['@context' => 'https://schema.org', '@type' => 'CreativeWork', 'name' => $stored['name']], JSON_THROW_ON_ERROR);
    $site->db()
        ->prepare('INSERT INTO ' . $site->table('#__schemaorg') . " (itemId, context, schemaType, `schema`) VALUES (?, 'com_proclaim.cwmmessage', 'Sermon', ?)")
        ->execute([$studyId, $schema]);
    echo "  + schema.org row for study \"{$stored['study']}\"\n";

    return 0;
}

/**
 * Delete the declared study's saved schema row.
 *
 * @param  array{study: string, name: string}  $stored
 *
 * @return int  Problems found (none: a missing study has no row to delete)
 */
function removeSchema(TestSite $site, array $stored, string $marker): int
{
    $studyId = storedSchemaStudy($site, $stored, $marker);

    if ($studyId !== null) {
        $site->db()
            ->prepare('DELETE FROM ' . $site->table('#__schemaorg') . " WHERE itemId = ? AND context = 'com_proclaim.cwmmessage'")
            ->execute([$studyId]);
    }

    return 0;
}

/**
 * @param  list<array<string, string>>  $plugins
 *
 * @return int  Problems found
 */
function checkPlugins(TestSite $site, array $plugins): int
{
    $db       = $site->db();
    $problems = 0;
    $find     = $db->prepare('SELECT enabled FROM ' . $site->table('#__extensions') . " WHERE type = 'plugin' AND folder = ? AND element = ?");

    foreach ($plugins as $plugin) {
        $name = $plugin['folder'] . '/' . $plugin['element'];
        $find->execute([$plugin['folder'], $plugin['element']]);
        $enabled = $find->fetchColumn();

        if ($enabled === false) {
            fwrite(STDERR, "  ! {$name}: not installed\n");
            $problems++;
        } elseif ((int) $enabled !== 1) {
            fwrite(STDERR, "  ! {$name}: installed but not enabled\n");
            $problems++;
        } else {
            echo "  ok {$name}\n";
        }
    }

    return $problems;
}
