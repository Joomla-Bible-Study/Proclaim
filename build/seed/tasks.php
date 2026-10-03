<?php

/**
 * Seed layer: scheduled tasks, run for real.
 *
 * For each Proclaim routine in `tasks.json` that only touches this site's own tables and files
 * (analytics rollup, podcast feeds, database backup, publish and expire, archive), the layer gives
 * it a task row, runs it once through Joomla's own command line (`scheduler:run --id`), and
 * disables the row again. A disabled task cannot be run by id, so it has to be enabled to run; it is
 * disabled straight after so Joomla's web-triggered scheduler never fires it later.
 *
 * The publish and archive routines change study state, so the layer writes four small studies of its
 * own (`fixtures`) and leaves the content layer's alone. The scheduled one must come out published,
 * the expired one unpublished, and the old one and the control untouched, because archiving is off
 * unless a site turns it on.
 *
 * ⚠️ Tasks write files (a database backup) and change rows, so, like the accounts layer, this
 * refuses any site that is not disposable (see disposable() in lib.php).
 *
 * Run two ways:
 *
 *   php build/seed/tasks.php apply|remove|check
 *       Standalone: every `role = test` install in build.properties.
 *
 *   cwm-seed ... (layer `tasks`)
 *       Under cwm-seed, which names one site through CWM_SEED_* variables.
 *
 * `check` reads the result: every task ran once or more, exited 0 and never failed, and each
 * fixture has the state it should. `remove` deletes the task rows and the fixtures; it does not delete
 * a backup file a task wrote. Run it with the finder layer after this one, so the index sees the
 * fixtures' final state.
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
    fwrite(STDERR, "Usage: php build/seed/tasks.php apply|remove|check\n");

    exit(2);
}

$declared = json_decode((string) file_get_contents(__DIR__ . '/tasks.json'), true, 512, JSON_THROW_ON_ERROR);
$marker   = getenv('CWM_SEED_MARKER') ?: markerFromConfig($root);
$note     = $marker . 'tasks';

if (($reason = disposable()) !== null && $action !== 'remove') {
    fwrite(STDERR, "Refusing to {$action} scheduled tasks: {$reason}.\n");

    exit(1);
}

$failures = 0;

foreach (targets($root) as $label => $makeSite) {
    echo "=== {$action} tasks on {$label} ===\n";

    try {
        $site = $makeSite();

        $failures += match ($action) {
            'apply'  => runTasks($site, $declared, $marker, $note, taskSitePath($label)),
            'remove' => removeTasks($site, $declared, $marker, $note),
            default  => checkTasks($site, $declared, $marker, $note),
        };
    } catch (\RuntimeException | \PDOException $e) {
        fwrite(STDERR, '  ' . $e->getMessage() . "\n");
        $failures++;
    }

    echo "\n";
}

if ($failures > 0) {
    fwrite(STDERR, "Task seeding ({$action}) failed: {$failures} problem(s).\n");

    exit(1);
}

echo 'Tasks ' . ($action === 'apply' ? 'applied' : ($action === 'remove' ? 'removed' : 'checked')) . ".\n";

/**
 * The site folder, from the environment under cwm-seed or the standalone target label.
 */
function taskSitePath(string $label): string
{
    $path = (string) getenv('CWM_SEED_SITE_PATH');

    if ($path === '' && preg_match('/\((.*)\)$/', $label, $m) === 1) {
        $path = $m[1];
    }

    return $path;
}

/**
 * Write the fixtures and task rows, run each task once, then disable the rows.
 *
 * @param  array<string, mixed>  $declared
 *
 * @return int  Problems found
 */
function runTasks(TestSite $site, array $declared, string $marker, string $note, string $path): int
{
    $db = $site->db();

    if (scalar($db, 'SELECT COUNT(*) FROM ' . $site->table('#__scheduler_tasks')) === null) {
        throw new \RuntimeException('the scheduler is not set up on this site.');
    }

    removeTasks($site, $declared, $marker, $note, true);

    foreach ($declared['fixtures'] as $fixture) {
        insertFixture($site, $fixture, $marker);
        echo "  + study {$fixture['key']} (published {$fixture['published']})\n";
    }

    $insert = $db->prepare(
        'INSERT INTO ' . $site->table('#__scheduler_tasks')
        . ' (title, type, execution_rules, cron_rules, state, params, priority, note, created, created_by) '
        . 'VALUES (?, ?, ?, ?, 1, ?, 0, ?, ?, 0)'
    );
    $params = json_encode([
        'individual_log' => false,
        'log_file'       => '',
        'notifications'  => ['success_mail' => '0', 'failure_mail' => '0', 'fatal_failure_mail' => '0', 'orphan_mail' => '0'],
    ], JSON_THROW_ON_ERROR);

    $problems = 0;
    $ids      = [];

    foreach ($declared['tasks'] as $task) {
        $insert->execute([
            $task['title'],
            $task['type'],
            '{"rule-type":"interval-hours","interval-hours":"24","exec-day":"01","exec-time":"03:00"}',
            '{"type":"interval","exp":"PT24H"}',
            $params,
            $note,
            gmdate('Y-m-d H:i:s'),
        ]);

        $id    = (int) $db->lastInsertId();
        $ids[] = $id;

        [$code, $output] = runJoomlaCli($path, ['scheduler:run', '--id=' . $id]);

        if ($code !== 0) {
            $tail = trim(implode("\n", \array_slice(explode("\n", trim($output)), -3)));
            fwrite(STDERR, "  ! {$task['key']}: scheduler:run exited {$code}\n" . preg_replace('/^/m', '    ', $tail) . "\n");
            $problems++;

            continue;
        }

        echo "  + task {$task['key']} (id {$id}) ran\n";
    }

    // Enabled only long enough to be run by id; Joomla's lazy scheduler must never pick them up.
    $db->exec('UPDATE ' . $site->table('#__scheduler_tasks') . ' SET state = 0 WHERE id IN (' . implode(',', $ids) . ')');

    return $problems;
}

/**
 * Insert one fixture study, with its dates resolved against now.
 *
 * @param  array<string, mixed>  $fixture
 */
function insertFixture(TestSite $site, array $fixture, string $marker): void
{
    $when = static fn (?string $relative): ?string => $relative === null ? null : gmdate('Y-m-d H:i:s', strtotime($relative) ?: time());

    $row = [
        'studytitle'          => $fixture['title'],
        'alias'               => $marker . $fixture['key'],
        'studyintro'          => '<p>A task fixture.</p>',
        'studytext'           => '<p>A task fixture.</p>',
        'studydate'           => $fixture['studydate'] ?? gmdate('Y-m-d H:i:s'),
        'published'           => $fixture['published'],
        'publish_up'          => $when($fixture['publish_up'] ?? null),
        'publish_down'        => $when($fixture['publish_down'] ?? null),
        'access'              => 1,
        'language'            => '*',
        'secondary_reference' => '',
        'series_id'           => 0,
        'ordering'            => 0,
        'params'              => '{}',
        'messagetype'         => 1,
        'location_id'         => 1,
        'hits'                => 0,
        'comments'            => 1,
        'user_id'             => 0,
        'show_level'          => '0',
    ];

    // A date the fixture does not declare is left to the column's default: both are NOT NULL.
    $row = array_filter($row, static fn (mixed $value): bool => $value !== null);

    $columns = implode(', ', array_map(static fn (string $c): string => "`{$c}`", array_keys($row)));
    $marks   = implode(', ', array_fill(0, \count($row), '?'));

    $site->db()->prepare('INSERT INTO ' . $site->table('#__bsms_studies') . " ({$columns}) VALUES ({$marks})")->execute(array_values($row));
}

/**
 * Delete the task rows and the fixture studies.
 *
 * @param  array<string, mixed>  $declared
 */
function removeTasks(TestSite $site, array $declared, string $marker, string $note, bool $quiet = false): int
{
    $db = $site->db();

    $tasks = $db->prepare('DELETE FROM ' . $site->table('#__scheduler_tasks') . ' WHERE note = ?');
    $tasks->execute([$note]);
    $taskCount = $tasks->rowCount();

    $studies = $db->prepare('DELETE FROM ' . $site->table('#__bsms_studies') . ' WHERE alias = ?');
    $removed = 0;

    foreach ($declared['fixtures'] as $fixture) {
        $studies->execute([$marker . $fixture['key']]);
        $removed += $studies->rowCount();
    }

    if (!$quiet) {
        echo "  - {$taskCount} task(s), {$removed} fixture stud" . ($removed === 1 ? 'y' : 'ies') . "\n";
    }

    return 0;
}

/**
 * Read the result: every task ran and succeeded, and each fixture is in its final state.
 *
 * @param  array<string, mixed>  $declared
 *
 * @return int  Problems found
 */
function checkTasks(TestSite $site, array $declared, string $marker, string $note): int
{
    $db       = $site->db();
    $problems = 0;

    $task = $db->prepare('SELECT last_exit_code, times_executed, times_failed, state FROM ' . $site->table('#__scheduler_tasks') . ' WHERE note = ? AND type = ?');

    foreach ($declared['tasks'] as $declaredTask) {
        $task->execute([$note, $declaredTask['type']]);
        $row = $task->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            fwrite(STDERR, "  ! {$declaredTask['key']}: no task row\n");
            $problems++;

            continue;
        }

        $wrong = [];

        if ((int) $row['times_executed'] < 1) {
            $wrong[] = 'never ran';
        }

        if ((int) $row['last_exit_code'] !== 0) {
            $wrong[] = "exit code {$row['last_exit_code']}";
        }

        if ((int) $row['times_failed'] > 0) {
            $wrong[] = "failed {$row['times_failed']} time(s)";
        }

        if ((int) $row['state'] !== 0) {
            $wrong[] = 'left enabled';
        }

        if ($wrong !== []) {
            fwrite(STDERR, "  ! {$declaredTask['key']}: " . implode(', ', $wrong) . "\n");
            $problems++;
        } else {
            echo "  ok task {$declaredTask['key']}\n";
        }
    }

    $study = $db->prepare('SELECT published FROM ' . $site->table('#__bsms_studies') . ' WHERE alias = ?');

    foreach ($declared['fixtures'] as $fixture) {
        $study->execute([$marker . $fixture['key']]);
        $state = $study->fetchColumn();

        if ($state === false) {
            fwrite(STDERR, "  ! {$fixture['key']}: not seeded\n");
            $problems++;
        } elseif ((int) $state !== (int) $fixture['after']) {
            fwrite(STDERR, "  ! {$fixture['key']}: published {$state}, expected {$fixture['after']} after the tasks\n");
            $problems++;
        } else {
            echo "  ok study {$fixture['key']} (published {$state})\n";
        }
    }

    return $problems;
}
