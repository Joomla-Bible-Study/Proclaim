<?php

/**
 * Seed layer: awkward content, the cases that make a page break.
 *
 * Teachers, series and studies declared in `content.json`: unpublished, archived,
 * access-restricted, no teacher, no scripture, three teachers, four references, another
 * language, a series with no teacher, a title full of markup. Each is a page that once failed,
 * or could. This is not demo data: demo content is small, clean and shown to users, and this is
 * deliberately the opposite.
 *
 * Media attaches to the servers the `servers` layer wrote, by addon type, so that layer must
 * run first. Every row carries the seed marker as an alias prefix, which is how `remove` finds
 * exactly what was written.
 *
 * Run two ways:
 *
 *   php build/seed/content.php apply|remove|check
 *       Standalone: every `role = test` install in build.properties.
 *
 *   cwm-seed ... (layer `content`)
 *       Under cwm-seed, which names one site through CWM_SEED_* variables.
 *
 * `check` compares the database with the declaration: every study exists with the declared
 * state, teacher, scripture and media counts. build/verify-frontend.php then fetches each
 * study's page and compares the status a guest gets with the declared one.
 *
 * Idempotent: `apply` removes what it wrote before writing, so a second run leaves the same rows.
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
 * Stand-in portraits, reused from the shipped sample teachers so the pages do not fill with broken thumbnails.
 */
const TEACHER_IMAGE = 'images/biblestudy/teachers/melvin-santos-2/melvin-santos.jpg';
const TEACHER_THUMB = 'images/biblestudy/teachers/melvin-santos-2/thumb_melvin-santos.jpg';
const SERIES_THUMB  = 'images/biblestudy/series/worship-series-1/thumb_worship-series.jpg';

$action = $argv[1] ?? 'apply';

if (!\in_array($action, ['apply', 'remove', 'check'], true)) {
    fwrite(STDERR, "Usage: php build/seed/content.php apply|remove|check\n");

    exit(2);
}

$declared = json_decode((string) file_get_contents(__DIR__ . '/content.json'), true, 512, JSON_THROW_ON_ERROR);
$marker   = getenv('CWM_SEED_MARKER') ?: markerFromConfig($root);

$failures = 0;

foreach (targets($root) as $label => $makeSite) {
    echo "=== {$action} content on {$label} ===\n";

    try {
        $site = $makeSite();

        match ($action) {
            'apply'  => applyContent($site, $declared, $marker),
            'remove' => removeContent($site, $marker),
            default  => $failures += checkContent($site, $declared, $marker),
        };
    } catch (\RuntimeException | \PDOException $e) {
        fwrite(STDERR, '  ' . $e->getMessage() . "\n");
        $failures++;
    }

    echo "\n";
}

if ($failures > 0) {
    fwrite(STDERR, "Content seeding ({$action}) failed: {$failures} problem(s).\n");

    exit(1);
}

echo 'Content ' . ($action === 'apply' ? 'applied' : ($action === 'remove' ? 'removed' : 'checked')) . ".\n";

/**
 * A URL-safe alias for a key, carrying the marker.
 */
function aliasFor(string $marker, string $key): string
{
    return $marker . $key;
}

/**
 * Insert a row and return its id.
 *
 * @param  array<string, scalar|null>  $row  Column => value
 */
function insertRow(TestSite $site, string $table, array $row): int
{
    $columns = implode(', ', array_map(static fn (string $c): string => "`{$c}`", array_keys($row)));
    $marks   = implode(', ', array_fill(0, \count($row), '?'));

    $statement = $site->db()->prepare('INSERT INTO ' . $site->table($table) . " ({$columns}) VALUES ({$marks})");
    $statement->execute(array_values($row));

    return (int) $site->db()->lastInsertId();
}

/**
 * The id of the seeded server of a given addon type.
 *
 * @throws \RuntimeException  when the servers layer has not run
 */
function serverId(TestSite $site, string $marker, string $type): int
{
    $id = scalar($site->db(), 'SELECT id FROM ' . $site->table('#__bsms_servers')
        . ' WHERE server_name = ' . $site->db()->quote($marker . $type));

    if ($id === null) {
        throw new \RuntimeException("no \"{$marker}{$type}\" server — run the servers layer first.");
    }

    return (int) $id;
}

/**
 * Write the declared teachers, series and studies.
 *
 * @param  array<string, mixed>  $declared
 *
 * @throws \RuntimeException  when a study names a teacher, series or server that does not exist
 */
function applyContent(TestSite $site, array $declared, string $marker): void
{
    if (scalar($site->db(), 'SELECT COUNT(*) FROM ' . $site->table('#__bsms_studies')) === null) {
        throw new \RuntimeException('com_proclaim is not installed here — run test:install first.');
    }

    removeContent($site, $marker, true);

    $teachers = [];

    foreach ($declared['teachers'] as $teacher) {
        $teachers[$teacher['key']] = insertRow($site, '#__bsms_teachers', [
            'teachername'       => $teacher['name'],
            'alias'             => aliasFor($marker, $teacher['key']),
            'title'             => $teacher['title'],
            'short'             => '<p>' . $teacher['name'] . ' is here to give the templates something to render.</p>',
            'information'       => '<p>A longer biography, so the teacher detail page has a body.</p>',
            'address'           => '',
            'phone'             => '',
            'website'           => '',
            'email'             => '',
            'image'             => ($teacher['images'] ?? true) ? TEACHER_IMAGE : '',
            'teacher_thumbnail' => ($teacher['images'] ?? true) ? TEACHER_THUMB : '',
            'published'         => 1,
            'access'            => 1,
            'language'          => '*',
            'ordering'          => 0,
        ]);
    }

    $series = [];

    foreach ($declared['series'] as $row) {
        // `teacher` is deliberately not written: the column is nullable, and a series without one is the case.
        $series[$row['key']] = insertRow($site, '#__bsms_series', [
            'series_text'      => $row['title'],
            'alias'            => aliasFor($marker, 'series-' . $row['key']),
            'description'      => '<p>' . $row['description'] . '</p>',
            'series_thumbnail' => SERIES_THUMB,
            'published'        => 1,
            'access'           => 1,
            'language'         => '*',
            'ordering'         => 0,
        ]);
    }

    $defaultMedia = [['server' => 'youtube', 'filename' => 'youtu.be/0LROzQHy140', 'mime' => 'video/mp4']];

    foreach ($declared['studies'] as $study) {
        $studyId = insertRow($site, '#__bsms_studies', [
            'studytitle'          => $study['title'],
            'alias'               => aliasFor($marker, $study['key']),
            'studyintro'          => '<p>A short introduction, so listings have something to show.</p>',
            'studytext'           => '<p>The body of the message.</p>',
            'thumbnailm'          => SERIES_THUMB,
            'image'               => SERIES_THUMB,
            'studydate'           => $study['date'],
            'published'           => $study['published'] ?? 1,
            'access'              => $study['access'] ?? 1,
            'language'            => $study['language'] ?? '*',
            'secondary_reference' => $study['secondary_reference'] ?? '',
            'series_id'           => isset($study['series']) ? namedId($series, $study['series'], 'series', $study['key']) : 0,
            'ordering'            => 0,
            'params'              => '{}',
            'messagetype'         => 1,
            'location_id'         => 1,
            'hits'                => 0,
            'comments'            => 1,
            'user_id'             => 0,
            'show_level'          => '0',
        ]);

        foreach (array_values($study['teachers']) as $order => $key) {
            insertRow($site, '#__bsms_study_teachers', [
                'study_id'   => $studyId,
                'teacher_id' => namedId($teachers, $key, 'teacher', $study['key']),
                'ordering'   => $order,
            ]);
        }

        foreach (array_values($study['scriptures']) as $order => $ref) {
            [$book, $chapterBegin, $verseBegin, $chapterEnd, $verseEnd] = array_pad($ref, 5, 0);

            insertRow($site, '#__bsms_study_scriptures', [
                'study_id'      => $studyId,
                'ordering'      => $order,
                'booknumber'    => $book,
                'chapter_begin' => $chapterBegin,
                'verse_begin'   => $verseBegin,
                'chapter_end'   => $chapterEnd ?: $chapterBegin,
                'verse_end'     => $verseEnd ?: $verseBegin,
                'bible_version' => 'kjv',
                // Left empty on purpose: postflight and the control-panel data fixes fill it.
                'reference_text' => '',
            ]);
        }

        foreach (array_values($study['media'] ?? $defaultMedia) as $order => $media) {
            insertRow($site, '#__bsms_mediafiles', [
                'study_id'   => $studyId,
                'server_id'  => serverId($site, $marker, $media['server']),
                'metadata'   => '[]',
                'language'   => '*',
                'access'     => 1,
                'published'  => 1,
                'ordering'   => $order,
                'createdate' => $study['date'],
                'params'     => json_encode([
                    'filename'    => $media['filename'],
                    'mime_type'   => $media['mime'],
                    'size'        => 0,
                    'media_image' => 1,
                ], JSON_THROW_ON_ERROR),
            ]);
        }

        printf("  + %-22s id %-4d published %d, access %d\n", $study['key'], $studyId, $study['published'] ?? 1, $study['access'] ?? 1);
    }
}

/**
 * Look a declared key up in what was just written.
 *
 * @param  array<string, int>  $ids
 *
 * @throws \RuntimeException  for a key the file never declared
 */
function namedId(array $ids, string $key, string $kind, string $study): int
{
    return $ids[$key] ?? throw new \RuntimeException("study \"{$study}\" names an undeclared {$kind} \"{$key}\".");
}

/**
 * Delete every row this layer wrote, and nothing else.
 */
function removeContent(TestSite $site, string $marker, bool $quiet = false): void
{
    $db   = $site->db();
    $like = $marker . '%';

    $find = $db->prepare('SELECT id FROM ' . $site->table('#__bsms_studies') . ' WHERE alias LIKE ?');
    $find->execute([$like]);
    $ids = array_map('intval', $find->fetchAll(PDO::FETCH_COLUMN));

    $removed = [];

    if ($ids !== []) {
        $in = implode(',', $ids);

        foreach (['#__bsms_study_scriptures', '#__bsms_study_teachers', '#__bsms_studytopics', '#__bsms_mediafiles'] as $table) {
            $removed[$table] = $db->exec('DELETE FROM ' . $site->table($table) . " WHERE study_id IN ({$in})");
        }

        $removed['#__bsms_studies'] = $db->exec('DELETE FROM ' . $site->table('#__bsms_studies') . " WHERE id IN ({$in})");
    }

    foreach (['#__bsms_teachers', '#__bsms_series'] as $table) {
        $delete = $db->prepare('DELETE FROM ' . $site->table($table) . ' WHERE alias LIKE ?');
        $delete->execute([$like]);
        $removed[$table] = $delete->rowCount();
    }

    if (!$quiet) {
        foreach (array_filter($removed) as $table => $count) {
            echo "  - {$count} from {$table}\n";
        }
    }
}

/**
 * Compare the database with the declaration.
 *
 * @param  array<string, mixed>  $declared
 *
 * @return int  Problems found
 */
function checkContent(TestSite $site, array $declared, string $marker): int
{
    $db       = $site->db();
    $problems = 0;

    $count = static function (string $table, int $id) use ($db, $site): int {
        return (int) scalar($db, 'SELECT COUNT(*) FROM ' . $site->table($table) . ' WHERE study_id = ' . $id);
    };

    $find = $db->prepare('SELECT id, published, access, language FROM ' . $site->table('#__bsms_studies') . ' WHERE alias = ?');

    foreach ($declared['studies'] as $study) {
        $find->execute([aliasFor($marker, $study['key'])]);
        $row = $find->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            fwrite(STDERR, "  ! {$study['key']}: not seeded\n");
            $problems++;

            continue;
        }

        $id       = (int) $row['id'];
        $expected = [
            'published' => $study['published'] ?? 1,
            'access'    => $study['access'] ?? 1,
            'language'  => $study['language'] ?? '*',
        ];
        $wrong = [];

        foreach ($expected as $column => $value) {
            if ((string) $row[$column] !== (string) $value) {
                $wrong[] = "{$column} {$row[$column]} (expected {$value})";
            }
        }

        foreach ([
            'teachers'   => ['#__bsms_study_teachers', \count($study['teachers'])],
            'scriptures' => ['#__bsms_study_scriptures', \count($study['scriptures'])],
            'media'      => ['#__bsms_mediafiles', \count($study['media'] ?? [1])],
        ] as $label => [$table, $want]) {
            if ($count($table, $id) !== $want) {
                $wrong[] = "{$label} " . $count($table, $id) . " (expected {$want})";
            }
        }

        if ($wrong !== []) {
            fwrite(STDERR, "  ! {$study['key']}: " . implode(', ', $wrong) . "\n");
            $problems++;
        } else {
            echo \sprintf("  ok %s\n", $study['key']);
        }
    }

    return $problems;
}
