<?php

/**
 * Give every `role = test` install the site menu items post-release
 * verification needs, and turn on the landing sections it has to render.
 *
 * Without this the test site has no `client_id = 0` menu item pointing at
 * com_proclaim at all, so verification can only ever prove that an update
 * installs — never that the build it installed renders anything (#1701). The
 * two most visible fixes in 10.5.7 were both render-path: a landing page whose
 * Books section died with a database error on MySQL, and podcast titles showing
 * a raw language key. Neither could be reached on the one site that runs the
 * real published artifact.
 *
 * Four items, chosen to cover four different query paths rather than four
 * different screens:
 *
 *   landing page  the sections aggregate, including the Books query that only
 *                 failed on MySQL — which is why a real install matters and a
 *                 unit test did not catch it
 *   sermons list  the list model, its filters and pagination
 *   sermon        a single item with its media, teacher and scripture joins
 *   podcast list  the feed builder, where episode titles are composed
 *
 * The landing item is pointless unless the sections are switched on, and
 * `showbooks` is off in the shipped default template, so this also writes a
 * `landing_layout` enabling them. It merges into the existing params rather
 * than replacing them: template params are one payload, and rewriting the whole
 * thing to set one key produces a template no screen in the admin describes.
 *
 * Idempotent. Every row it writes carries a `note` marker and is deleted before
 * re-seeding, so running it twice leaves the same four items rather than eight.
 *
 * SAFETY: only installs marked `role = test` in build.properties are touched,
 * the same guard reset-testsite.php uses. Never point a dev or production
 * install at role = test.
 *
 * Runs as step 8 of `composer test:install`, after the package is installed and
 * verified, and is what build/verify-frontend.php then fetches.
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

declare(strict_types=1);

use CWM\BuildTools\Dev\PropertiesReader;
use CWM\BuildTools\Dev\TestSite;

$root = \dirname(__DIR__);

require $root . '/libraries/vendor/autoload.php';

/**
 * Marker written to #__menu.note so a re-seed can find its own rows.
 *
 * Deleting by link or title would also delete menu items someone added by hand
 * while testing; deleting by marker only ever removes what this script wrote.
 */
const SEED_NOTE = 'proclaim-testsite-seed';

/**
 * Landing sections switched on for the seeded landing page.
 *
 * books first, deliberately: it is the section that broke, and putting it at
 * the top means a truncated or half-rendered page still shows whether it ran.
 */
const LANDING_SECTIONS = ['books', 'teachers', 'series', 'topics'];

$reader   = new PropertiesReader($root . '/build.properties');
$installs = $reader->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install in build.properties — nothing to seed.\n");

    exit(0);
}

$failures = 0;

foreach ($installs as $install) {
    echo "=== seed {$install->id} ({$install->path}) ===\n";

    $configFile = $install->path . '/configuration.php';

    if (!is_file($configFile)) {
        fwrite(STDERR, "  configuration.php not found — skipping.\n");
        $failures++;

        continue;
    }

    try {
        $site = TestSite::fromPath($install->path);
        $site->db();
    } catch (\RuntimeException $e) {
        fwrite(STDERR, '  cannot connect: ' . $e->getMessage() . "\n");
        $failures++;

        continue;
    }

    try {
        seedInstall($site);
    } catch (\RuntimeException | \PDOException $e) {
        fwrite(STDERR, '  ' . $e->getMessage() . "\n");
        $failures++;
    }

    echo "\n";
}

if ($failures > 0) {
    fwrite(STDERR, "Seeding failed for {$failures} install(s).\n");

    exit(1);
}

echo "Site menus seeded.\n";

/**
 * Seed one install: enable the landing sections, then write the menu items.
 *
 * @param   TestSite  $site  The install being seeded
 *
 * @return  void
 *
 * @throws  \RuntimeException  when the install is missing something to link to
 *
 * @since __DEPLOY_VERSION__
 */
function seedInstall(TestSite $site): void
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

    $items = [
        [
            'title' => 'Proclaim Landing (seed)',
            'alias' => 'proclaim-seed-landing',
            'link'  => 'index.php?option=com_proclaim&view=cwmlandingpage&t=' . $templateId,
        ],
        [
            'title' => 'Proclaim Sermons (seed)',
            'alias' => 'proclaim-seed-sermons',
            'link'  => 'index.php?option=com_proclaim&view=cwmsermons&t=' . $templateId,
        ],
        [
            'title' => 'Proclaim Sermon (seed)',
            'alias' => 'proclaim-seed-sermon',
            'link'  => 'index.php?option=com_proclaim&view=cwmsermon&id=' . $studyId . '&t=' . $templateId,
        ],
        [
            'title' => 'Proclaim Podcasts (seed)',
            'alias' => 'proclaim-seed-podcasts',
            'link'  => 'index.php?option=com_proclaim&view=cwmseriespodcastlist&t=' . $templateId,
        ],
    ];

    $delete = $db->prepare('DELETE FROM ' . $site->table('#__menu') . ' WHERE client_id = 0 AND note = ?');
    $delete->execute([SEED_NOTE]);

    // Prepared once, executed four times. The table name still has to be
    // interpolated -- identifiers do not bind -- but every value now does,
    // which is what retires esc().
    $insert = $db->prepare(
        'INSERT INTO ' . $site->table('#__menu')
        . ' (menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, '
        . 'browserNav, access, img, template_style_id, params, lft, rgt, home, language, client_id) '
        . "VALUES (?, ?, ?, ?, ?, ?, 'component', 1, 1, 1, ?, 0, 1, '', 0, '{}', 0, 0, 0, '*', 0)"
    );

    foreach ($items as $item) {
        $insert->execute([
            $menutype,
            $item['title'],
            $item['alias'],
            SEED_NOTE,
            $item['alias'],
            $item['link'],
            (int) $componentId,
        ]);

        printf("  + %-24s Itemid=%d\n", $item['alias'], $db->lastInsertId());
    }

    rebuildTree($site);

    echo "  menu tree rebuilt (menutype '{$menutype}', template {$templateId}, study {$studyId})\n";
}

/**
 * Make sure some published study cites a book, so the Books section has a row.
 *
 * The Books section lists the books the studies are actually in (#1687), so with
 * no scripture reference anywhere it renders nothing and the strongest assertion
 * in verify-frontend.php quietly skips itself. That is what happens on the CI
 * disposable install, whose seed study carries no reference — the check reported
 * "no cited book" and passed, covering none of the path it exists for.
 *
 * Colossians (booknumber 151) for no reason beyond it being what the shipped
 * sample data uses, so a site that already has data and one that does not end up
 * looking the same.
 *
 * Only ever adds. A study that already cites something is left exactly as it is,
 * because the point is to guarantee a floor, not to impose a fixture.
 *
 * @param   TestSite  $site     The install being seeded
 * @param   int      $studyId  Study to attach the reference to
 *
 * @return  void
 *
 * @since __DEPLOY_VERSION__
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
 * Writes `landing_layout`, the format the Layout Editor produces, rather than
 * the legacy headingorder_* / show* pairs: a site built today has the former,
 * and seeding the shape nobody uses any more would exercise a fallback path
 * instead of the real one.
 *
 * @param   TestSite  $site        The install being seeded
 * @param   int      $templateId  Template whose params to edit
 *
 * @return  void
 *
 * @throws  \RuntimeException  when the stored params cannot be read as JSON
 *
 * @since __DEPLOY_VERSION__
 */
function enableLandingSections(TestSite $site, int $templateId): void
{
    $db = $site->db();

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

        // The legacy pair as well: a template that predates landing_layout is
        // still read through getSectionOrderFromLegacy(), and a site upgrading
        // into this seed should not depend on which branch runs.
        $params['show' . $section] = 1;
    }

    $params['landing_layout'] = $layout;

    $encoded = json_encode($params, JSON_THROW_ON_ERROR);

    $update = $db->prepare('UPDATE ' . $site->table('#__bsms_templates') . ' SET params = ? WHERE id = ?');
    $update->execute([$encoded, $templateId]);

    echo '  landing sections enabled: ' . implode(', ', LANDING_SECTIONS) . "\n";
}

/**
 * Renumber lft/rgt across the whole menu tree from parent_id.
 *
 * Inserting with lft = rgt = 0 leaves the nested set wrong, and Joomla reads
 * lft to order and to resolve a menu item's ancestors. Rebuilding by depth-first
 * walk is used in preference to shifting neighbours: it costs one pass, it is
 * the same operation Joomla's own "Rebuild" button performs, and it repairs any
 * drift already present instead of preserving it.
 *
 * The walk starts at the root (id 1) and covers every row, admin items
 * included — they share this table and this tree.
 *
 * @param   TestSite  $site  The install being seeded
 *
 * @return  void
 *
 * @since __DEPLOY_VERSION__
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

    // One prepared statement reused across every row in the tree, which is the
    // whole table -- admin items included, per the note above.
    $update = $db->prepare(
        'UPDATE ' . $site->table('#__menu') . ' SET lft = ?, rgt = ?, level = ? WHERE id = ?'
    );

    foreach ($updates as [$id, $left, $right, $level]) {
        $update->execute([$left, $right, $level, $id]);
    }
}


/**
 * Fetch the first column of the first row, or null when there is no row.
 *
 * @param   PDO  $db   Open connection
 * @param   string   $sql  Statement to run
 *
 * @return  string|null
 *
 * @throws  \RuntimeException  when the statement fails
 *
 * @since __DEPLOY_VERSION__
 */
function scalar(PDO $db, string $sql): ?string
{
    $value = $db->query($sql)->fetchColumn();

    return $value === false ? null : (string) $value;
}
