#!/usr/bin/env php
<?php

/**
 * Seed a development site with content worth looking at, and content worth
 * breaking.
 *
 * Two modes, because they want opposite things:
 *
 *   --demo       clean, fully-populated, everything published. What a site
 *                should look like when someone is shown it.
 *   --scenarios  the awkward cases -- unpublished, access-restricted, no
 *                teacher, no scripture, four references, three teachers, a
 *                title full of entities. Useful, and it makes a site look
 *                broken, which is why it is separate.
 *
 * Everything written carries the `cwmseed-` alias prefix and is removable with
 * --remove, so a dev site can be put back exactly as it was.
 *
 * ⚠️ Deliberately not a fixture library for automated tests. Those build and
 * roll back their own rows; this is for looking at pages and for exercising
 * paths that only a browser reaches -- the player, podcast feeds, Smart Search.
 *
 * Usage:
 *   php build/seed-dev-data.php --site=j6-dev --demo
 *   php build/seed-dev-data.php --site=j5-dev --scenarios
 *   php build/seed-dev-data.php --site=j5-dev --remove
 *   php build/seed-dev-data.php --site=j6-dev --list
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

const SEED_PREFIX = 'cwmseed-';
const SITES_ROOT  = '/Volumes/BCCExt_APFS_Extreme_Pro/Sites';

/**
 * Read a site's database settings out of its configuration.php.
 *
 * @param   string  $site  Site directory name
 *
 * @return  array{host: string, user: string, password: string, db: string, prefix: string}
 */
function siteConfig(string $site): array
{
    $path = SITES_ROOT . '/' . $site . '/configuration.php';

    if (!is_file($path)) {
        fwrite(STDERR, "No configuration.php for '{$site}' under " . SITES_ROOT . "\n");
        exit(1);
    }

    $source = (string) file_get_contents($path);
    $config = [];

    foreach (['host', 'user', 'password', 'db', 'dbprefix'] as $key) {
        preg_match('/public \$' . $key . "\s*=\s*'(.*?)';/", $source, $match);
        $config[$key] = $match[1] ?? '';
    }

    return [
        // MAMP writes host:port; mysqli wants them apart.
        'host'     => explode(':', $config['host'])[0] ?: 'localhost',
        'port'     => (int) (explode(':', $config['host'])[1] ?? 3306),
        'user'     => $config['user'],
        'password' => $config['password'],
        'db'       => $config['db'],
        'prefix'   => $config['dbprefix'],
    ];
}

/**
 * @param   array  $cfg  Site config from siteConfig()
 *
 * @return  mysqli
 */
function connect(array $cfg): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    return new mysqli($cfg['host'], $cfg['user'], $cfg['password'], $cfg['db'], $cfg['port']);
}

/**
 * Insert a row and return its id.
 *
 * @param   mysqli  $db     Connection
 * @param   string  $table  Prefixed table name
 * @param   array   $row    Column => value
 *
 * @return  int
 */
function insert(mysqli $db, string $table, array $row): int
{
    $cols = implode(', ', array_map(static fn ($c) => "`$c`", array_keys($row)));
    $vals = implode(', ', array_map(
        static fn ($v) => $v === null ? 'NULL' : "'" . $db->real_escape_string((string) $v) . "'",
        array_values($row)
    ));

    $db->query("INSERT INTO `$table` ($cols) VALUES ($vals)");

    return (int) $db->insert_id;
}

/**
 * Columns every #__bsms_studies row needs, whatever the scenario.
 *
 * @return  array
 */
function studyDefaults(): array
{
    return [
        'studydate'   => '2026-02-01 10:00:00',
        'published'   => 1,
        'access'      => 1,
        'language'    => '*',
        'ordering'    => 0,
        'params'      => '{}',
        'messagetype' => 1,
        'location_id' => 1,
        'series_id'   => 0,
        'hits'        => 0,
        'comments'    => 1,
        'user_id'     => 0,
        'show_level'  => '0',
    ];
}

/**
 * @param   mysqli  $db      Connection
 * @param   string  $p       Table prefix
 * @param   string  $name    Teacher name
 * @param   string  $title   Teacher title
 * @param   bool    $images  Reuse an existing teacher's images so the page is not full of broken thumbnails
 *
 * @return  int
 */
function seedTeacher(mysqli $db, string $p, string $name, string $title, bool $images = true): int
{
    $slug = SEED_PREFIX . strtolower(str_replace(' ', '-', $name));

    return insert($db, $p . 'bsms_teachers', [
        'teachername' => $name,
        'alias'       => $slug,
        'title'       => $title,
        'short'       => '<p>' . $name . ' teaches regularly and is here to give the templates '
            . 'something to render.</p>',
        'information'       => '<p>A longer biography, so the teacher detail page has a body.</p>',
        'address'           => '',
        'phone'             => '',
        'website'           => '',
        'email'             => '',
        'image'             => $images ? 'images/biblestudy/teachers/melvin-santos-2/melvin-santos.jpg' : '',
        'teacher_thumbnail' => $images ? 'images/biblestudy/teachers/melvin-santos-2/thumb_melvin-santos.jpg' : '',
        'published'         => 1,
        'access'            => 1,
        'language'          => '*',
        'ordering'          => 0,
    ]);
}

/**
 * @param   mysqli  $db     Connection
 * @param   string  $p      Table prefix
 * @param   string  $title  Series title
 * @param   string  $desc   Series description
 *
 * @return  int
 */
function seedSeries(mysqli $db, string $p, string $title, string $desc): int
{
    return insert($db, $p . 'bsms_series', [
        'series_text'      => $title,
        'alias'            => SEED_PREFIX . strtolower(str_replace([' ', ','], ['-', ''], $title)),
        'description'      => '<p>' . $desc . '</p>',
        'series_thumbnail' => 'images/biblestudy/series/worship-series-1/thumb_worship-series.jpg',
        'published'        => 1,
        'access'           => 1,
        'language'         => '*',
        'ordering'         => 0,
    ]);
}

/**
 * Write a study plus everything hanging off it.
 *
 * @param   mysqli  $db    Connection
 * @param   string  $p     Table prefix
 * @param   array   $spec  title, teachers[], scriptures[[book,ch,vs,chEnd,vsEnd]], topics[], media, overrides
 *
 * @return  int  Study id
 */
function seedStudy(mysqli $db, string $p, array $spec): int
{
    $title = $spec['title'];
    $slug  = SEED_PREFIX . substr(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)) ?? '', 0, 60);

    $studyId = insert($db, $p . 'bsms_studies', array_merge(studyDefaults(), [
        'studytitle' => $title,
        'alias'      => $slug,
        'studyintro' => $spec['intro'] ?? '<p>A short introduction, so listings have something to show.</p>',
        'studytext'  => $spec['body'] ?? '<p>The body of the message.</p>',
        'thumbnailm' => 'images/biblestudy/series/worship-series-1/thumb_worship-series.jpg',
        'image'      => 'images/biblestudy/series/worship-series-1/thumb_worship-series.jpg',
    ], $spec['overrides'] ?? []));

    foreach ($spec['teachers'] ?? [] as $ordering => $teacherId) {
        insert($db, $p . 'bsms_study_teachers', [
            'study_id'   => $studyId,
            'teacher_id' => $teacherId,
            'ordering'   => $ordering,
        ]);
    }

    foreach ($spec['scriptures'] ?? [] as $ordering => $ref) {
        [$book, $chapterBegin, $verseBegin, $chapterEnd, $verseEnd] = array_pad($ref, 5, 0);

        insert($db, $p . 'bsms_study_scriptures', [
            'study_id'      => $studyId,
            'ordering'      => $ordering,
            'booknumber'    => $book,
            'chapter_begin' => $chapterBegin,
            'verse_begin'   => $verseBegin,
            'chapter_end'   => $chapterEnd ?: $chapterBegin,
            'verse_end'     => $verseEnd ?: $verseBegin,
            'bible_version' => 'kjv',
            // Left empty on purpose: postflight and the control panel data fixes
            // fill it, and seeing that happen is part of what this is for.
            'reference_text' => '',
        ]);
    }

    foreach ($spec['topics'] ?? [] as $topicId) {
        insert($db, $p . 'bsms_studytopics', ['study_id' => $studyId, 'topic_id' => $topicId]);
    }

    if ($spec['media'] ?? true) {
        // Real, public URLs. A made-up path renders a broken player, which
        // defeats the point of having media at all.
        insert($db, $p . 'bsms_mediafiles', [
            'study_id'   => $studyId,
            'server_id'  => 5,
            'metadata'   => '[]',
            'language'   => '*',
            'access'     => 1,
            'published'  => 1,
            'ordering'   => 0,
            'createdate' => '2026-02-01 10:00:00',
            'params'     => json_encode([
                'filename'    => 'youtu.be/0LROzQHy140',
                'mime_type'   => 'video/mp4',
                'size'        => 0,
                'media_image' => 1,
            ]),
        ]);
    }

    return $studyId;
}

/**
 * Clean, presentable content. Everything published, every field filled.
 *
 * @param   mysqli  $db  Connection
 * @param   string  $p   Table prefix
 *
 * @return  int  Studies written
 */
function seedDemo(mysqli $db, string $p): int
{
    $ruth   = seedTeacher($db, $p, 'Ruth Delacroix', 'Senior Pastor');
    $samuel = seedTeacher($db, $p, 'Samuel Oyelaran', 'Associate Pastor');
    $hana   = seedTeacher($db, $p, 'Hana Petrova', 'Guest Speaker');

    $mount   = seedSeries($db, $p, 'The Sermon on the Mount', 'Four messages through Matthew 5 to 7.');
    $letters = seedSeries($db, $p, 'Letters to the Churches', 'Three messages from the epistles.');

    $topics = array_column(
        $db->query("SELECT id FROM {$p}bsms_topics WHERE published = 1 LIMIT 3")->fetch_all(MYSQLI_ASSOC),
        'id'
    );

    // book numbers are Proclaim's: 101 = Genesis, so Matthew is 140.
    $studies = [
        ['title'         => 'Blessed Are the Poor in Spirit', 'teachers' => [$ruth],
            'scriptures' => [[140, 5, 1, 5, 12]], 'series' => $mount],
        ['title'         => 'Salt and Light', 'teachers' => [$ruth],
            'scriptures' => [[140, 5, 13, 5, 16]], 'series' => $mount],
        // Two teachers: the thing the flat column could never express.
        ['title'         => 'On Prayer and Fasting', 'teachers' => [$ruth, $samuel],
            'scriptures' => [[140, 6, 5, 6, 18]], 'series' => $mount],
        // Three references: the thing the flat pair could never hold.
        ['title'         => 'Do Not Worry', 'teachers' => [$samuel],
            'scriptures' => [[140, 6, 25, 6, 34], [119, 37, 1, 37, 7], [150, 4, 6, 4, 7]], 'series' => $mount],
        ['title'         => 'To the Church at Ephesus', 'teachers' => [$hana],
            'scriptures' => [[149, 1, 1, 1, 14]], 'series' => $letters],
        ['title'         => 'To the Church at Philippi', 'teachers' => [$hana, $ruth],
            'scriptures' => [[150, 1, 1, 1, 11], [150, 4, 4, 4, 9]], 'series' => $letters],
        ['title'         => 'To the Church at Colossae', 'teachers' => [$samuel],
            'scriptures' => [[151, 3, 1, 3, 17]], 'series' => $letters],
        ['title'         => 'The Good Shepherd', 'teachers' => [$ruth],
            'scriptures' => [[143, 10, 1, 10, 18], [119, 23, 1, 23, 6]]],
        ['title'         => 'A Living Hope', 'teachers' => [$hana],
            'scriptures' => [[160, 1, 3, 1, 9]]],
        ['title'         => 'Faith and Works', 'teachers' => [$samuel],
            'scriptures' => [[159, 2, 14, 2, 26]]],
        ['title'         => 'The Road to Emmaus', 'teachers' => [$ruth, $hana],
            'scriptures' => [[142, 24, 13, 24, 35], [123, 53, 1, 53, 12], [119, 22, 1, 22, 18]]],
        ['title'         => 'Come and See', 'teachers' => [$samuel],
            'scriptures' => [[143, 1, 35, 1, 51]]],
    ];

    foreach ($studies as $i => $spec) {
        seedStudy($db, $p, [
            'title'      => $spec['title'],
            'teachers'   => $spec['teachers'],
            'scriptures' => $spec['scriptures'],
            'topics'     => \array_slice($topics, 0, ($i % 3)),
            'overrides'  => [
                'series_id' => $spec['series'] ?? 0,
                'studydate' => date('Y-m-d H:i:s', strtotime('2026-01-04 10:00:00 +' . $i . ' weeks')),
            ],
        ]);
    }

    return \count($studies);
}

/**
 * The awkward cases. Useful to have, and they make a site look broken.
 *
 * @param   mysqli  $db  Connection
 * @param   string  $p   Table prefix
 *
 * @return  int  Studies written
 */
function seedScenarios(mysqli $db, string $p): int
{
    $one   = seedTeacher($db, $p, 'Edge One', 'Pastor');
    $two   = seedTeacher($db, $p, 'Edge Two', 'Elder');
    $three = seedTeacher($db, $p, 'Edge Three', 'Deacon', false);

    $cases = [
        ['title'         => 'Scenario unpublished', 'teachers' => [$one],
            'scriptures' => [[143, 3, 16]], 'overrides' => ['published' => 0]],
        ['title'         => 'Scenario archived', 'teachers' => [$one],
            'scriptures' => [[143, 3, 16]], 'overrides' => ['published' => 2]],
        ['title'         => 'Scenario access Registered', 'teachers' => [$one],
            'scriptures' => [[143, 3, 16]], 'overrides' => ['access' => 2]],
        ['title'         => 'Scenario access Special', 'teachers' => [$one],
            'scriptures' => [[143, 3, 16]], 'overrides' => ['access' => 3]],
        ['title'         => 'Scenario no teacher', 'teachers' => [],
            'scriptures' => [[143, 3, 16]]],
        ['title' => 'Scenario no scripture', 'teachers' => [$one], 'scriptures' => []],
        ['title'         => 'Scenario three teachers', 'teachers' => [$one, $two, $three],
            'scriptures' => [[145, 8, 1, 8, 4]]],
        ['title'         => 'Scenario four references', 'teachers' => [$two],
            'scriptures' => [[101, 1, 1, 1, 5], [119, 1, 1, 1, 6], [143, 1, 1, 1, 14], [145, 8, 28, 8, 39]]],
        ['title'         => 'Scenario other language', 'teachers' => [$two],
            'scriptures' => [[143, 3, 16]], 'overrides' => ['language' => 'en-GB']],
        ['title'         => 'Scenario no media', 'teachers' => [$two],
            'scriptures' => [[143, 3, 16]], 'media' => false],
        ['title'         => 'Scenario secondary reference text', 'teachers' => [$three],
            'scriptures' => [[143, 3, 16]],
            'overrides'  => ['secondary_reference' => 'See also the readings for Advent']],
        [
            'title' => 'Scenario a title that is quite a lot longer than anyone expected, with '
                . '"quotes", <em>markup</em>, an ampersand & a dash — and an accent: Café',
            'teachers'   => [$three],
            'scriptures' => [[143, 3, 16]],
        ],
    ];

    foreach ($cases as $i => $spec) {
        seedStudy($db, $p, [
            'title'      => $spec['title'],
            'teachers'   => $spec['teachers'],
            'scriptures' => $spec['scriptures'],
            'media'      => $spec['media'] ?? true,
            'overrides'  => array_merge(
                ['studydate' => date('Y-m-d H:i:s', strtotime('2026-03-01 10:00:00 +' . $i . ' days'))],
                $spec['overrides'] ?? []
            ),
        ]);
    }

    return \count($cases);
}

/**
 * Remove everything this script wrote, and nothing else.
 *
 * @param   mysqli  $db  Connection
 * @param   string  $p   Table prefix
 *
 * @return  array<string, int>  Table => rows removed
 */
function removeSeeded(mysqli $db, string $p): array
{
    $like    = SEED_PREFIX . '%';
    $studies = array_column(
        $db->query("SELECT id FROM {$p}bsms_studies WHERE alias LIKE '{$like}'")->fetch_all(MYSQLI_ASSOC),
        'id'
    );

    $removed = [];

    if ($studies !== []) {
        $ids = implode(',', array_map('intval', $studies));

        foreach (['bsms_study_scriptures', 'bsms_study_teachers', 'bsms_studytopics', 'bsms_mediafiles'] as $table) {
            $db->query("DELETE FROM {$p}{$table} WHERE study_id IN ($ids)");
            $removed[$table] = $db->affected_rows;
        }

        $db->query("DELETE FROM {$p}bsms_studies WHERE id IN ($ids)");
        $removed['bsms_studies'] = $db->affected_rows;
    }

    foreach (['bsms_teachers', 'bsms_series'] as $table) {
        $db->query("DELETE FROM {$p}{$table} WHERE alias LIKE '{$like}'");
        $removed[$table] = $db->affected_rows;
    }

    return array_filter($removed);
}

// ---------------------------------------------------------------------------

$options = getopt('', ['site:', 'demo', 'scenarios', 'remove', 'list']);

if (!isset($options['site'])) {
    fwrite(STDERR, "Usage: php build/seed-dev-data.php --site=<j5-dev|j6-dev|j62-dev|j70-dev> "
        . "[--demo|--scenarios|--remove|--list]\n");
    exit(1);
}

$site   = (string) $options['site'];
$cfg    = siteConfig($site);
$db     = connect($cfg);
$prefix = $cfg['prefix'];

if (isset($options['list'])) {
    $rows = $db->query(
        "SELECT id, studytitle, published, access, language FROM {$prefix}bsms_studies
         WHERE alias LIKE '" . SEED_PREFIX . "%' ORDER BY id"
    )->fetch_all(MYSQLI_ASSOC);

    printf("%s: %d seeded studies\n", $site, \count($rows));

    foreach ($rows as $row) {
        printf(
            "  %-5s pub=%-2s access=%-2s %-6s %s\n",
            $row['id'],
            $row['published'],
            $row['access'],
            $row['language'],
            substr($row['studytitle'], 0, 60)
        );
    }

    exit(0);
}

if (isset($options['remove'])) {
    foreach (removeSeeded($db, $prefix) as $table => $count) {
        printf("  removed %-24s %d\n", $table, $count);
    }

    exit(0);
}

// Re-running should replace, not pile up.
removeSeeded($db, $prefix);

$written = 0;

if (isset($options['demo'])) {
    $written += seedDemo($db, $prefix);
}

if (isset($options['scenarios'])) {
    $written += seedScenarios($db, $prefix);
}

if ($written === 0) {
    fwrite(STDERR, "Nothing to do: pass --demo, --scenarios, --remove or --list.\n");
    exit(1);
}

printf("%s: seeded %d studies. Remove with --site=%s --remove\n", $site, $written, $site);
