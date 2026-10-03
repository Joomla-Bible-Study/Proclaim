<?php

/**
 * Seed layer: the Smart Search index over the seeded content.
 *
 * Runs Joomla's own indexer (`finder:index`) after the content layer, so the Proclaim finder
 * plugin indexes the seeded studies exactly as it would on a live site. That needs the site's own
 * command line, which for a DDEV site means `ddev exec` (see runJoomlaCli() in lib.php).
 *
 * Run two ways:
 *
 *   php build/seed/finder.php apply|remove|check
 *       Standalone: every `role = test` install in build.properties.
 *
 *   cwm-seed ... (layer `finder`)
 *       Under cwm-seed, which names one site through CWM_SEED_* variables.
 *
 * `apply` rebuilds the whole index, not just Proclaim's rows: that is what the command does, and
 * the search filters are kept. `remove` deletes only the index entries of the seeded studies
 * (their links, terms and taxonomy rows), so it must run while those studies still exist, which
 * is the order cwm-seed removes layers in. `check` fails for a seeded study that should be
 * searchable and is not in the index. build/verify-frontend.php then searches as each role.
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
    fwrite(STDERR, "Usage: php build/seed/finder.php apply|remove|check\n");

    exit(2);
}

$content = json_decode((string) file_get_contents(__DIR__ . '/content.json'), true, 512, JSON_THROW_ON_ERROR);
$marker  = getenv('CWM_SEED_MARKER') ?: markerFromConfig($root);

$failures = 0;

foreach (targets($root) as $label => $makeSite) {
    echo "=== {$action} finder on {$label} ===\n";

    try {
        $site = $makeSite();

        $failures += match ($action) {
            'apply'  => indexSite($site, sitePathOf($label)),
            'remove' => unindexSeeded($site, $marker),
            default  => checkIndex($site, $content, $marker),
        };
    } catch (\RuntimeException | \PDOException $e) {
        fwrite(STDERR, '  ' . $e->getMessage() . "\n");
        $failures++;
    }

    echo "\n";
}

if ($failures > 0) {
    fwrite(STDERR, "Finder seeding ({$action}) failed: {$failures} problem(s).\n");

    exit(1);
}

echo 'Finder ' . ($action === 'apply' ? 'applied' : ($action === 'remove' ? 'removed' : 'checked')) . ".\n";

/**
 * The site folder, from the environment under cwm-seed or the standalone target label.
 */
function sitePathOf(string $label): string
{
    $path = (string) getenv('CWM_SEED_SITE_PATH');

    if ($path === '' && preg_match('/\((.*)\)$/', $label, $m) === 1) {
        $path = $m[1];
    }

    return $path;
}

/**
 * Rebuild the Smart Search index with Joomla's indexer.
 *
 * @return int  Problems found
 */
function indexSite(TestSite $site, string $path): int
{
    [$code, $output] = runJoomlaCli($path, ['finder:index']);

    $tail = trim(implode("\n", \array_slice(explode("\n", trim($output)), -4)));

    if ($code !== 0) {
        fwrite(STDERR, "  ! finder:index exited {$code}:\n" . preg_replace('/^/m', '    ', $tail) . "\n");

        return 1;
    }

    $count = (int) scalar($site->db(), 'SELECT COUNT(*) FROM ' . $site->table('#__finder_links') . " WHERE url LIKE '%option=com_proclaim%'");

    if ($count === 0) {
        fwrite(STDERR, "  ! finder:index ran but indexed no Proclaim item — is the finder plugin enabled, and the content seeded?\n");

        return 1;
    }

    echo "  + indexed; {$count} Proclaim item(s) in the index\n";

    return 0;
}

/**
 * Delete the index entries of the seeded studies: links, their terms and their taxonomy rows.
 *
 * @return int  Problems found (none)
 */
function unindexSeeded(TestSite $site, string $marker): int
{
    $db  = $site->db();
    $ids = $db->prepare('SELECT id FROM ' . $site->table('#__bsms_studies') . ' WHERE alias LIKE ?');
    $ids->execute([$marker . '%']);

    $removed = 0;
    $find    = $db->prepare('SELECT link_id FROM ' . $site->table('#__finder_links') . ' WHERE url = ?');

    foreach ($ids->fetchAll(PDO::FETCH_COLUMN) as $id) {
        $find->execute(['index.php?option=com_proclaim&view=cwmsermon&id=' . (int) $id]);

        foreach ($find->fetchAll(PDO::FETCH_COLUMN) as $linkId) {
            foreach (['#__finder_links_terms', '#__finder_taxonomy_map', '#__finder_links'] as $table) {
                $db->exec('DELETE FROM ' . $site->table($table) . ' WHERE link_id = ' . (int) $linkId);
            }

            $removed++;
        }
    }

    echo "  - {$removed} index entr" . ($removed === 1 ? 'y' : 'ies') . "\n";

    return 0;
}

/**
 * Every seeded study a guest may open must be in the index.
 *
 * @param  array<string, mixed>  $content
 *
 * @return int  Problems found
 */
function checkIndex(TestSite $site, array $content, string $marker): int
{
    $db       = $site->db();
    $problems = 0;
    $study    = $db->prepare('SELECT id FROM ' . $site->table('#__bsms_studies') . ' WHERE alias = ?');
    $link     = $db->prepare('SELECT COUNT(*) FROM ' . $site->table('#__finder_links') . ' WHERE url = ?');

    foreach ($content['studies'] as $declared) {
        if ((int) ($declared['guest'] ?? 200) !== 200) {
            continue;
        }

        $study->execute([$marker . $declared['key']]);
        $id = $study->fetchColumn();

        if ($id === false) {
            fwrite(STDERR, "  ! {$declared['key']}: not seeded\n");
            $problems++;

            continue;
        }

        $link->execute(['index.php?option=com_proclaim&view=cwmsermon&id=' . $id]);

        if ((int) $link->fetchColumn() !== 1) {
            fwrite(STDERR, "  ! {$declared['key']}: not in the index\n");
            $problems++;
        } else {
            echo "  ok {$declared['key']}\n";
        }
    }

    return $problems;
}
