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

    [$host, $user, $pass, $name, $prefix] = (static function (string $file): array {
        require $file;
        $c = new \JConfig();

        return [$c->host, $c->user, $c->password, $c->db, $c->dbprefix];
    })($configFile);

    $port = 3306;

    if (str_contains($host, ':')) {
        [$host, $portString] = explode(':', $host, 2);
        $port                = (int) $portString;
    }

    $db = @mysqli_connect($host, $user, $pass, $name, $port);

    if (!$db) {
        fwrite(STDERR, '  cannot connect: ' . mysqli_connect_error() . "\n");
        $failures++;

        continue;
    }

    try {
        seedInstall($db, $prefix);
    } catch (\RuntimeException $e) {
        fwrite(STDERR, '  ' . $e->getMessage() . "\n");
        $failures++;
    } finally {
        mysqli_close($db);
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
 * @param   \mysqli  $db      Open connection to the install's database
 * @param   string   $prefix  That install's table prefix
 *
 * @return  void
 *
 * @throws  \RuntimeException  when the install is missing something to link to
 *
 * @since __DEPLOY_VERSION__
 */
function seedInstall(\mysqli $db, string $prefix): void
{
    $componentId = scalar($db, "SELECT extension_id FROM {$prefix}extensions "
        . "WHERE element = 'com_proclaim' AND type = 'component'");

    if ($componentId === null) {
        throw new \RuntimeException('com_proclaim is not installed here — run test:install first.');
    }

    $templateId = scalar($db, "SELECT id FROM {$prefix}bsms_templates WHERE published = 1 ORDER BY id LIMIT 1");
    $studyId    = scalar($db, "SELECT id FROM {$prefix}bsms_studies WHERE published = 1 ORDER BY id LIMIT 1");

    if ($templateId === null || $studyId === null) {
        throw new \RuntimeException('no published template or study to link to — the install seed did not land.');
    }

    $menutype = scalar($db, "SELECT menutype FROM {$prefix}menu "
        . "WHERE client_id = 0 AND home = 1 AND published = 1 LIMIT 1")
        ?? scalar($db, "SELECT menutype FROM {$prefix}menu_types ORDER BY id LIMIT 1");

    if ($menutype === null) {
        throw new \RuntimeException('this site has no site menu to add items to.');
    }

    enableLandingSections($db, $prefix, (int) $templateId);

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

    run($db, "DELETE FROM {$prefix}menu WHERE client_id = 0 AND note = '" . SEED_NOTE . "'");

    foreach ($items as $item) {
        $sql = \sprintf(
            "INSERT INTO {$prefix}menu "
            . '(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, '
            . 'browserNav, access, img, template_style_id, params, lft, rgt, home, language, client_id) '
            . "VALUES ('%s', '%s', '%s', '%s', '%s', '%s', 'component', 1, 1, 1, %d, 0, 1, '', 0, '{}', 0, 0, 0, '*', 0)",
            esc($db, $menutype),
            esc($db, $item['title']),
            esc($db, $item['alias']),
            SEED_NOTE,
            esc($db, $item['alias']),
            esc($db, $item['link']),
            (int) $componentId
        );

        run($db, $sql);

        printf("  + %-24s Itemid=%d\n", $item['alias'], mysqli_insert_id($db));
    }

    rebuildTree($db, $prefix);

    echo "  menu tree rebuilt (menutype '{$menutype}', template {$templateId}, study {$studyId})\n";
}

/**
 * Switch the landing sections on for the seeded template.
 *
 * Writes `landing_layout`, the format the Layout Editor produces, rather than
 * the legacy headingorder_* / show* pairs: a site built today has the former,
 * and seeding the shape nobody uses any more would exercise a fallback path
 * instead of the real one.
 *
 * @param   \mysqli  $db          Open connection
 * @param   string   $prefix      Table prefix
 * @param   int      $templateId  Template whose params to edit
 *
 * @return  void
 *
 * @throws  \RuntimeException  when the stored params cannot be read as JSON
 *
 * @since __DEPLOY_VERSION__
 */
function enableLandingSections(\mysqli $db, string $prefix, int $templateId): void
{
    $stored = scalar($db, "SELECT params FROM {$prefix}bsms_templates WHERE id = {$templateId}");

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

    run($db, "UPDATE {$prefix}bsms_templates SET params = '" . esc($db, $encoded) . "' WHERE id = {$templateId}");

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
 * @param   \mysqli  $db      Open connection
 * @param   string   $prefix  Table prefix
 *
 * @return  void
 *
 * @since __DEPLOY_VERSION__
 */
function rebuildTree(\mysqli $db, string $prefix): void
{
    $children = [];
    $result   = mysqli_query($db, "SELECT id, parent_id FROM {$prefix}menu ORDER BY parent_id, lft, id");

    while ($row = mysqli_fetch_assoc($result)) {
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

    foreach ($updates as [$id, $left, $right, $level]) {
        run($db, "UPDATE {$prefix}menu SET lft = {$left}, rgt = {$right}, level = {$level} WHERE id = {$id}");
    }
}

/**
 * Run a statement, turning a failure into an exception.
 *
 * @param   \mysqli  $db   Open connection
 * @param   string   $sql  Statement to run
 *
 * @return  void
 *
 * @throws  \RuntimeException  when the statement fails
 *
 * @since __DEPLOY_VERSION__
 */
function run(\mysqli $db, string $sql): void
{
    if (mysqli_query($db, $sql) === false) {
        throw new \RuntimeException('query failed: ' . mysqli_error($db));
    }
}

/**
 * Fetch the first column of the first row, or null when there is no row.
 *
 * @param   \mysqli  $db   Open connection
 * @param   string   $sql  Statement to run
 *
 * @return  string|null
 *
 * @throws  \RuntimeException  when the statement fails
 *
 * @since __DEPLOY_VERSION__
 */
function scalar(\mysqli $db, string $sql): ?string
{
    $result = mysqli_query($db, $sql);

    if ($result === false) {
        throw new \RuntimeException('query failed: ' . mysqli_error($db));
    }

    $row = mysqli_fetch_row($result);

    return $row === null ? null : (string) $row[0];
}

/**
 * Escape a value for interpolation.
 *
 * @param   \mysqli  $db     Open connection
 * @param   string   $value  Value to escape
 *
 * @return  string
 *
 * @since __DEPLOY_VERSION__
 */
function esc(\mysqli $db, string $value): string
{
    return mysqli_real_escape_string($db, $value);
}
