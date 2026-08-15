<?php

/**
 * Verify what the install script recorded about the install it just did.
 *
 * The CLI installer prints "Extension installed successfully." and nothing
 * else. Everything the update reports about itself — which legacy migrations
 * were needed, which were skipped, how long each took — goes to enqueueMessage
 * (dropped entirely under CLI) and to the install log. So the gate could
 * install an upgrade, pass every other assertion, and still tell you nothing
 * about the part of the release that is *about* upgrading.
 *
 * That is the gap this closes. The log is the only durable record, so the gate
 * reads it back.
 *
 * The load-bearing assertion is the version gate (#1842). Every legacy
 * migration is skipped when the site is already past the release the migration
 * shipped in; without the gate all of them run on every update, which is what
 * made a routine update take minutes. Both states install cleanly and leave an
 * identical schema, so no other check in this harness can tell them apart —
 * only the log can.
 *
 * ⚠️ This covers the gate's SKIP branch only. The harness upgrades one release
 * at a time, and every migration shipped at or before that baseline, so none of
 * them is ever supposed to run here. Asserting that a migration that IS needed
 * runs and transforms the data correctly needs an older database restored
 * before the upgrade, which this harness has no fixture for. These eight steps
 * are 10.x-era data fixes; a 9.x site is carried forward by the upgrade wizard
 * in the Admin Center, which is a different path and is not exercised here.
 *
 * Assertions, per role=test install:
 *   - the log exists and its last block is the install just performed
 *   - preflight saw the expected type, and upgraded FROM the expected version
 *     (an unread source version reads as 'n/a' and makes the gate run
 *     everything — a silent, total regression of #1842)
 *   - every migration step was skipped, none ran, none failed
 *   - as many steps were considered as there are migrations in the script, so
 *     "nothing ran" cannot pass by nothing having been attempted
 *   - postflight reported both its timing lines, and its own step count agrees
 *     with the steps logged individually
 *
 * Usage: php build/verify-install-log.php <type> [from-version]
 *          <type>          install | update, as preflight recorded it
 *          [from-version]  required for update; the release upgraded from
 *
 * Exit:  0 clean, 1 assertion(s) failed.
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

$expectedType = $argv[1] ?? '';
$expectedFrom = $argv[2] ?? '';

if (!\in_array($expectedType, ['install', 'update'], true)) {
    fwrite(STDERR, "Usage: php build/verify-install-log.php <install|update> [from-version]\n");

    exit(1);
}

if ($expectedType === 'update' && $expectedFrom === '') {
    fwrite(STDERR, "An update must be told which version it upgraded from.\n");
    fwrite(STDERR, "Without it 'from n/a' — the regression this checks for — would pass.\n");

    exit(1);
}

/**
 * How many legacy migrations the install script registers.
 *
 * The count comes from the script rather than a constant here so that adding a
 * migration does not silently widen what "every step was considered" means. A
 * step that never reaches the log is a step whose gate nobody is checking.
 *
 * @param   string  $scriptFile  Path to proclaim.script.php
 *
 * @return  int  Number of step() call sites, or -1 when it cannot be counted
 *
 * @since __DEPLOY_VERSION__
 */
function countRegisteredSteps(string $scriptFile): int
{
    if (!is_file($scriptFile)) {
        return -1;
    }

    return preg_match_all('/\$this->step\(/', (string) file_get_contents($scriptFile));
}

/**
 * Where a Joomla install writes its logs.
 *
 * Read from the site's own configuration rather than assumed, because a site
 * that moved its log path would otherwise look like a site that logged nothing
 * — a missing file being read as a failure to log is the one wrong answer this
 * check must not give.
 *
 * @param   string  $installPath  Site root
 *
 * @return  string  Absolute path to the log directory
 *
 * @since __DEPLOY_VERSION__
 */
function logPathFor(string $installPath): string
{
    $configFile = $installPath . '/configuration.php';
    $configured = '';

    if (is_file($configFile)) {
        $matched = preg_match(
            '/\$log_path\s*=\s*[\'"]([^\'"]+)[\'"]/',
            (string) file_get_contents($configFile),
            $m
        );

        if ($matched === 1) {
            $configured = $m[1];
        }
    }

    if ($configured === '') {
        return $installPath . '/administrator/logs';
    }

    // Joomla stores this absolute, but a hand-edited configuration.php may not.
    return str_starts_with($configured, '/') ? $configured : $installPath . '/' . $configured;
}

/**
 * The messages of the last install recorded in a Joomla text log.
 *
 * A log accumulates across installs. Only the entries from the most recent
 * preflight describe the install under test; anything earlier belongs to a run
 * this check is not making assertions about.
 *
 * @param   string  $logFile  Path to the log
 *
 * @return  string[]  Messages, in order, from the last preflight onwards
 *
 * @since __DEPLOY_VERSION__
 */
function lastInstallBlock(string $logFile): array
{
    $messages = [];

    foreach (file($logFile, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $fields = explode("\t", $line);

        if (\count($fields) < 5) {
            continue;
        }

        // Rejoin: the message is everything past the four fixed fields.
        $messages[] = implode("\t", \array_slice($fields, 4));
    }

    for ($i = \count($messages) - 1; $i >= 0; $i--) {
        if (str_starts_with($messages[$i], 'preflight: ')) {
            return \array_slice($messages, $i);
        }
    }

    return [];
}

$reader   = new PropertiesReader($root . '/build.properties');
$installs = $reader->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install in build.properties — nothing to verify.\n");

    exit(0);
}

$registered = countRegisteredSteps($root . '/proclaim.script.php');
$failures   = 0;

foreach ($installs as $install) {
    echo "=== verify install log on {$install->id} ({$install->path}) ===\n";

    $logFile = logPathFor($install->path) . '/com_proclaim.install.php';

    if (!is_file($logFile)) {
        fwrite(STDERR, "  FAIL no install log at {$logFile} —\n");
        fwrite(STDERR, "       preflight opens the logger before anything else runs, so an\n");
        fwrite(STDERR, "       absent log means it never ran or the category lost its logger.\n");
        $failures++;

        continue;
    }

    $block = lastInstallBlock($logFile);

    if ($block === []) {
        fwrite(STDERR, "  FAIL {$logFile} records no preflight line.\n");
        $failures++;

        continue;
    }

    // --- preflight: the type, and the version the gate reasons from ---------
    $matched = preg_match('/^preflight: (\S+), from ([^,]+), PHP /', $block[0], $m);

    if ($matched !== 1) {
        fwrite(STDERR, "  FAIL could not read the preflight line: {$block[0]}\n");
        $failures++;

        continue;
    }

    [, $type, $from] = $m;

    if ($type !== $expectedType) {
        fwrite(STDERR, "  FAIL preflight recorded type '{$type}', expected '{$expectedType}' —\n");
        fwrite(STDERR, "       an upgrade Joomla treats as a fresh install skips update() entirely.\n");
        $failures++;
    } else {
        echo "  OK   preflight recorded an {$type}\n";
    }

    if ($expectedType === 'update') {
        if ($from !== $expectedFrom) {
            fwrite(STDERR, "  FAIL preflight read the installed version as '{$from}', expected '{$expectedFrom}' —\n");
            fwrite(STDERR, "       every migration gate compares against this. Read as 'n/a' it is\n");
            fwrite(STDERR, "       treated as unknown, and unknown runs everything.\n");
            $failures++;
        } else {
            echo "  OK   preflight upgraded from {$from}\n";
        }
    }

    // --- the migration steps ------------------------------------------------
    $skipped = 0;
    $ran     = [];
    $failed  = [];

    foreach ($block as $message) {
        $matched = preg_match('/^ {2}(skip|ran|FAIL)\s+(\S.*)$/', $message, $m);

        if ($matched !== 1) {
            continue;
        }

        match ($m[1]) {
            'skip' => $skipped++,
            'ran'  => $ran[]    = trim($m[2]),
            'FAIL' => $failed[] = trim($m[2]),
        };
    }

    $considered = $skipped + \count($ran) + \count($failed);

    if ($failed !== []) {
        fwrite(STDERR, "  FAIL " . \count($failed) . " migration step(s) threw —\n");
        fwrite(STDERR, "       a step that fails is recorded and the update carries on, so this\n");
        fwrite(STDERR, "       is the only place it is ever reported:\n");

        foreach ($failed as $line) {
            fwrite(STDERR, "         {$line}\n");
        }

        $failures++;
    }

    if ($expectedType !== 'update') {
        // A fresh install's SQL creates the current schema, so the migrations
        // are never attempted at all — there is no gate here to assert on.
        echo "  OK   {$considered} migration step(s) considered (fresh install migrates nothing)\n";
    } elseif ($registered < 0) {
        fwrite(STDERR, "  FAIL could not read proclaim.script.php to count its migrations.\n");
        $failures++;
    } elseif ($considered !== $registered) {
        fwrite(STDERR, "  FAIL the log accounts for {$considered} migration step(s), but the script\n");
        fwrite(STDERR, "       registers {$registered}. 'Nothing ran' proves nothing about the\n");
        fwrite(STDERR, "       migrations that were never attempted.\n");
        $failures++;
    } else {
        echo "  OK   all {$registered} registered migration step(s) reached the log\n";
    }

    if ($ran !== []) {
        fwrite(STDERR, "  FAIL " . \count($ran) . " legacy migration(s) ran upgrading from {$expectedFrom}:\n");

        foreach ($ran as $line) {
            fwrite(STDERR, "         {$line}\n");
        }

        fwrite(STDERR, "       Every migration shipped in a release at or before {$expectedFrom},\n");
        fwrite(STDERR, "       so all of them should have been gated out. This is what the gate\n");
        fwrite(STDERR, "       exists to prevent.\n");
        fwrite(STDERR, "       If this release genuinely adds a migration newer than {$expectedFrom},\n");
        fwrite(STDERR, "       that one is expected to run — teach this check about it.\n");
        $failures++;
    } elseif ($skipped > 0) {
        echo "  OK   all {$skipped} legacy migration(s) gated out, none ran\n";
    }

    // --- postflight reported on itself --------------------------------------
    $elapsed = false;
    $summary = -1;

    foreach ($block as $message) {
        if (str_contains($message, 'elapsed since preflight')) {
            $elapsed = true;
        }

        if (preg_match('/^postflight: (\d+) step\(s\) in /', $message, $m) === 1) {
            $summary = (int) $m[1];
        }
    }

    if (!$elapsed) {
        fwrite(STDERR, "  FAIL postflight never reported the time spent before it ran —\n");
        fwrite(STDERR, "       unpack, file copy and schema replay are invisible without it.\n");
        $failures++;
    }

    if ($summary < 0) {
        fwrite(STDERR, "  FAIL postflight never reported its summary line —\n");
        fwrite(STDERR, "       the install did not reach the end of postflight.\n");
        $failures++;
    } elseif ($summary !== $considered) {
        fwrite(STDERR, "  FAIL postflight counted {$summary} step(s); {$considered} were logged —\n");
        fwrite(STDERR, "       the per-step lines and the summary disagree about what happened.\n");
        $failures++;
    } else {
        echo "  OK   postflight reported its timings and {$summary} step(s)\n";
    }
}

if ($failures > 0) {
    fwrite(STDERR, "\nINSTALL LOG VERIFICATION FAILED ({$failures} assertion(s)).\n");

    exit(1);
}

echo "Install log verification passed.\n";
