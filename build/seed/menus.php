<?php

/**
 * Seed layer: the site menu items the front end is reached through.
 *
 * Without a `client_id = 0` menu item pointing at com_proclaim, a site can prove
 * only that an update installs, never that the build renders anything.
 * The landing page's Books section once died with a database error on MySQL and
 * podcast titles once showed a raw language key; neither could be reached on the
 * one site that runs the real published artifact. `verify-frontend.php` fetches
 * every item this layer writes, so each view listed in `menus.json` is a page the
 * release gate renders.
 *
 * The items are declared in `menus.json`, one per view. A view that needs an id
 * names what it needs (`target`) and gets the first published row, so nothing
 * here hardcodes one. The landing page is pointless unless its sections are on,
 * and `showbooks` is off in the shipped default template, so this also writes a
 * `landing_layout` enabling them, merged into the template's existing params:
 * template params are one payload, and rewriting the whole thing to set one key
 * yields a template no screen in the admin describes.
 *
 * Run two ways:
 *
 *   php build/seed/menus.php apply|remove
 *       Standalone: every `role = test` install in build.properties. This is how
 *       `composer test:install` runs it.
 *
 *   cwm-seed ... (layer `menus`)
 *       Under cwm-seed, which names one site through CWM_SEED_* variables.
 *
 * Idempotent. Every row carries a `note` of "<marker>menus" and is deleted before
 * re-seeding, so a second run leaves the same items, not twice as many; `remove`
 * deletes exactly those rows. It does not undo the landing sections or the cited
 * book: both are additive floors a site should have anyway, and neither can be
 * told apart from what was already there.
 *
 * SAFETY: standalone, only `role = test` installs are touched, the same guard
 * reset-testsite uses. Never point a dev or production install at role = test.
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
 * The note the first version of this seeder wrote. Cleared along with the new one
 * so a site seeded before the move does not keep two sets of items.
 */
const LEGACY_NOTE = 'proclaim-testsite-seed';

/**
 * Landing sections switched on for the seeded landing page.
 *
 * books first, deliberately: it is the section that broke, and putting it at the
 * top means a truncated or half-rendered page still shows whether it ran.
 */
const LANDING_SECTIONS = ['books', 'teachers', 'series', 'topics'];

/**
 * Tables a view's `target` is looked up in.
 */
const TARGET_TABLES = [
    'study'   => '#__bsms_studies',
    'series'  => '#__bsms_series',
    'teacher' => '#__bsms_teachers',
];

$action = $argv[1] ?? 'apply';

if (!\in_array($action, ['apply', 'remove'], true)) {
    fwrite(STDERR, "Usage: php build/seed/menus.php apply|remove\n");

    exit(2);
}

$declared = json_decode((string) file_get_contents(__DIR__ . '/menus.json'), true, 512, JSON_THROW_ON_ERROR);
$items    = (array) ($declared['items'] ?? []);
$marker   = getenv('CWM_SEED_MARKER') ?: markerFromConfig($root);
$note     = $marker . 'menus';

$failures = 0;

foreach (targets($root) as $label => $makeSite) {
    echo "=== {$action} menus on {$label} ===\n";

    try {
        $site = $makeSite();

        if ($action === 'apply') {
            seedInstall($site, $items, $note);
        } else {
            removeInstall($site, $note);
        }
    } catch (\RuntimeException | \PDOException $e) {
        fwrite(STDERR, '  ' . $e->getMessage() . "\n");
        $failures++;
    }

    echo "\n";
}

if ($failures > 0) {
    fwrite(STDERR, "Menu seeding failed for {$failures} install(s).\n");

    exit(1);
}

echo 'Site menus ' . ($action === 'apply' ? 'applied' : 'removed') . ".\n";

/**
 * Seed one install: enable the landing sections, then write the menu items.
 *
 * @param  list<array<string, mixed>>  $items
 *
 * @throws \RuntimeException  when the install is missing something to link to
 */
function seedInstall(TestSite $site, array $items, string $note): void
{
    $db = $site->db();

    $componentId = scalar($db, 'SELECT extension_id FROM ' . $site->table('#__extensions')
        . " WHERE element = 'com_proclaim' AND type = 'component'");

    if ($componentId === null) {
        throw new \RuntimeException('com_proclaim is not installed here — run test:install first.');
    }

    $templateId = scalar($db, 'SELECT id FROM ' . $site->table('#__bsms_templates') . ' WHERE published = 1 ORDER BY id LIMIT 1');
    $studyId    = scalar($db, 'SELECT id FROM ' . $site->table('#__bsms_studies') . ' WHERE published = 1 ORDER BY id LIMIT 1');

    if ($templateId === null || $studyId === null) {
        throw new \RuntimeException('no published template or study to link to — the install seed did not land.');
    }

    $menutype = scalar($db, 'SELECT menutype FROM ' . $site->table('#__menu')
        . ' WHERE client_id = 0 AND home = 1 AND published = 1 LIMIT 1')
        ?? scalar($db, 'SELECT menutype FROM ' . $site->table('#__menu_types') . ' ORDER BY id LIMIT 1');

    if ($menutype === null) {
        throw new \RuntimeException('this site has no site menu to add items to.');
    }

    enableLandingSections($site, (int) $templateId);
    ensureCitedBook($site, (int) $studyId);

    clearItems($site, $note);

    // Prepared once, executed per item. The table name still has to be interpolated, because
    // identifiers do not bind, but every value does.
    $insert = $db->prepare(
        'INSERT INTO ' . $site->table('#__menu')
        . ' (menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, '
        . 'browserNav, access, img, template_style_id, params, lft, rgt, home, language, client_id) '
        . "VALUES (?, ?, ?, ?, ?, ?, 'component', ?, 1, 1, ?, 0, ?, '', 0, ?, 0, 0, 0, ?, 0)"
    );

    foreach ($items as $item) {
        $insert->execute([
            $menutype,
            (string) $item['title'],
            (string) $item['alias'],
            $note,
            (string) $item['alias'],
            linkFor($site, $item, (int) $templateId),
            (int) ($item['published'] ?? 1),
            (int) $componentId,
            (int) ($item['access'] ?? 1),
            json_encode($item['params'] ?? new \stdClass(), JSON_THROW_ON_ERROR),
            (string) ($item['language'] ?? '*'),
        ]);

        printf("  + %-24s Itemid=%d\n", $item['alias'], $db->lastInsertId());
    }

    rebuildTree($site);
    echo "  menu tree rebuilt (menutype '{$menutype}', template {$templateId}, study {$studyId})\n";
}

/**
 * Remove the items this layer wrote, and the ones the first version of it wrote.
 */
function removeInstall(TestSite $site, string $note): void
{
    $removed = clearItems($site, $note);
    rebuildTree($site);

    echo "  removed {$removed} menu item(s)\n";
}

/**
 * @return int  Rows removed
 */
function clearItems(TestSite $site, string $note): int
{
    $delete = $site->db()->prepare(
        'DELETE FROM ' . $site->table('#__menu') . ' WHERE client_id = 0 AND note IN (?, ?)'
    );
    $delete->execute([$note, LEGACY_NOTE]);

    return $delete->rowCount();
}

/**
 * The `link` for an item: its view, the id of the row it needs (if any), and the template.
 *
 * @param  array<string, mixed>  $item
 *
 * @throws \RuntimeException  when the view needs a row that the site does not have
 */
function linkFor(TestSite $site, array $item, int $templateId): string
{
    $link = 'index.php?option=com_proclaim&view=' . $item['view'];

    if (isset($item['target'])) {
        $table = TARGET_TABLES[$item['target']] ?? null;

        if ($table === null) {
            throw new \RuntimeException("menu item {$item['alias']} has an unknown target \"{$item['target']}\".");
        }

        $id = scalar($site->db(), 'SELECT id FROM ' . $site->table($table) . ' WHERE published = 1 ORDER BY id LIMIT 1');

        if ($id === null) {
            throw new \RuntimeException("no published {$item['target']} to link menu item {$item['alias']} to.");
        }

        $link .= '&id=' . $id;
    }

    return $link . '&t=' . $templateId;
}

/**
 * Make sure some published study cites a book, so the Books section has a row.
 *
 * The Books section lists the books the studies are actually in, so with no
 * scripture reference anywhere it renders nothing and the strongest assertion in
 * verify-frontend.php quietly skips itself. That is what happens on a disposable
 * install whose seed study carries no reference: the check reported "no cited book"
 * and passed, covering none of the path it exists for.
 *
 * Colossians (booknumber 151) for no reason beyond it being what the shipped sample
 * data uses. Only ever adds: a study that already cites something is left exactly as
 * it is, because the point is to guarantee a floor, not to impose a fixture.
 */
function ensureCitedBook(TestSite $site, int $studyId): void
{
    $db = $site->db();

    $existing = scalar($db, 'SELECT COUNT(*) FROM ' . $site->table('#__bsms_study_scriptures') . ' AS s '
        . 'INNER JOIN ' . $site->table('#__bsms_studies') . ' AS st ON st.id = s.study_id '
        . "WHERE st.published = 1 AND s.reference_text <> ''");

    if ((int) $existing > 0) {
        echo "  cited book already present ({$existing} reference(s))\n";

        return;
    }

    $insert = $db->prepare(
        'INSERT INTO ' . $site->table('#__bsms_study_scriptures')
        . ' (study_id, ordering, booknumber, chapter_begin, verse_begin, chapter_end, verse_end, '
        . 'bible_version, reference_text) '
        . "VALUES (?, 0, 151, 3, 5, 3, 11, '', 'Colossians 3:5-11')"
    );
    $insert->execute([$studyId]);

    echo "  cited book added to study {$studyId}: Colossians 3:5-11\n";
}

/**
 * Switch the landing sections on for the seeded template.
 *
 * Writes `landing_layout`, the format the Layout Editor produces, rather than the
 * legacy headingorder_* / show* pairs: a site built today has the former, and
 * seeding the shape nobody uses any more would exercise a fallback path instead
 * of the real one.
 *
 * @throws \RuntimeException  when the stored params cannot be read as JSON
 */
function enableLandingSections(TestSite $site, int $templateId): void
{
    $db   = $site->db();
    $read = $db->prepare('SELECT params FROM ' . $site->table('#__bsms_templates') . ' WHERE id = ?');
    $read->execute([$templateId]);

    $stored = $read->fetchColumn();
    $stored = $stored === false ? null : (string) $stored;

    try {
        $params = json_decode((string) ($stored ?: '{}'), true, 512, JSON_THROW_ON_ERROR) ?: [];
    } catch (\JsonException $e) {
        throw new \RuntimeException("template {$templateId} has unreadable params: " . $e->getMessage());
    }

    $layout = [];

    foreach (LANDING_SECTIONS as $section) {
        $layout[] = ['id' => $section, 'enabled' => true];

        // The legacy pair as well: a template that predates landing_layout is still read through
        // getSectionOrderFromLegacy(), and a site upgrading into this seed should not depend on
        // which branch runs.
        $params['show' . $section] = 1;
    }

    $params['landing_layout'] = $layout;

    $update = $db->prepare('UPDATE ' . $site->table('#__bsms_templates') . ' SET params = ? WHERE id = ?');
    $update->execute([json_encode($params, JSON_THROW_ON_ERROR), $templateId]);

    echo '  landing sections enabled: ' . implode(', ', LANDING_SECTIONS) . "\n";
}

/**
 * Renumber lft/rgt across the whole menu tree from parent_id.
 *
 * The tree is rebuilt for the whole table, admin items included, because lft/rgt
 * are shared across client_id: renumbering only the site items would leave the two
 * halves overlapping.
 */
function rebuildTree(TestSite $site): void
{
    $db       = $site->db();
    $children = [];

    $rows = $db->query('SELECT id, parent_id FROM ' . $site->table('#__menu') . ' ORDER BY parent_id, lft, id');

    foreach ($rows as $row) {
        $children[(int) $row['parent_id']][] = (int) $row['id'];
    }

    $updates = [];

    $walk = static function (int $id, int $left, int $level) use (&$walk, $children, &$updates): int {
        $right = $left + 1;

        foreach ($children[$id] ?? [] as $childId) {
            $right = $walk($childId, $right, $level + 1);
        }

        $updates[] = [$id, $left, $right, $level];

        return $right + 1;
    };

    $walk(1, 0, 0);

    $update = $db->prepare('UPDATE ' . $site->table('#__menu') . ' SET lft = ?, rgt = ?, level = ? WHERE id = ?');

    foreach ($updates as [$id, $left, $right, $level]) {
        $update->execute([$left, $right, $level, $id]);
    }
}
