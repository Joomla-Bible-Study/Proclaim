<?php

declare(strict_types=1);

/**
 * Assert a site on the previous release is actually offered this one.
 *
 * The second half of the post-release check. verify-update-stream.php reads the
 * ARS stream directly and asserts what it declares; this asserts what a Joomla
 * site does with it — the update site row the site actually holds, the fetch,
 * #__updates, and Joomla's own version comparison. That gap is where "the XML
 * looks right but nobody is offered the update" lives, and it is where 10.3.4
 * through 10.4.0 shipped invisible to every Joomla 5 site.
 *
 * ⚠️ NOT a CI gate. It fetches the live update stream over the network, so
 * wiring it into ci.yml buys a flake on every pull request and tells you
 * nothing about the branch. It belongs in the post-release checklist, run
 * locally, alongside verify-update-stream.php.
 *
 * WHY THIS NEEDS NO SECOND SITE. Joomla decides whether an update is newer by
 * comparing against #__extensions.manifest_cache, not against the files on
 * disk (Updater.php, the version_compare on the decoded manifest_cache). A
 * second install kept at the previous release would feed the same comparison
 * the same input. So this rewrites that one value, runs the real discovery,
 * and puts it back — the release gate can keep sole ownership of the test site.
 *
 * ⚠️ IT MUST BE com_proclaim, NOT pkg_proclaim. ExtensionAdapter keeps a single
 * $this->latest while parsing a whole stream and never consults element or
 * type: the highest version in the file wins and everything else is discarded.
 * Stream id=1 carries a com_proclaim entry at the same version as the newest
 * pkg_proclaim entry, parsed first, so the component entry survives the tie and
 * the package entry never does. Faking the package version therefore proves
 * nothing — the run reports "no updates available" whatever you set it to,
 * which is exactly the answer this script exists to distrust.
 *
 * Usage:
 *   php build/verify-update-offered.php <expected-version> [previous-version]
 *
 * Exit codes:
 *   0  the site was offered the expected version, and was not offered one when
 *      it already had it
 *   1  a check failed, or the site could not be restored afterwards
 *   2  wrong invocation
 *
 * @package    Proclaim.Build
 * @since      __DEPLOY_VERSION__
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use CWM\BuildTools\Dev\ExtensionQuery;
use CWM\BuildTools\Dev\PropertiesReader;
use CWM\BuildTools\Dev\TestSite;

$root = \dirname(__DIR__);

require $root . '/libraries/vendor/autoload.php';

$expected = $argv[1] ?? '';
$previous = $argv[2] ?? '';

if ($expected === '') {
    fwrite(STDERR, "Usage: php build/verify-update-offered.php <expected-version> [previous-version]\n");

    exit(2);
}

if ($previous === '') {
    // ⚠️ Not versions.json: post-release its "current" is the version being
    // verified, so deriving the previous release from it gives the same
    // number twice and the comparison can never be true.
    $tags = [];
    exec('git -C ' . escapeshellarg($root) . ' tag --sort=-v:refname', $tags);
    $tags = array_values(array_filter(array_map(
        static fn (string $t): string => ltrim(trim($t), 'v'),
        $tags
    )));

    foreach ($tags as $tag) {
        if ($tag !== $expected && version_compare($tag, $expected, '<')) {
            $previous = $tag;
            break;
        }
    }
}

if ($previous === '' || version_compare($previous, $expected, '>=')) {
    fwrite(STDERR, "Could not determine a previous release below {$expected}. Pass one explicitly.\n");

    exit(1);
}

$reader   = new PropertiesReader($root . '/build.properties');
$installs = $reader->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install in build.properties — nothing to check.\n");

    // ⚠️ Not exit(0). A verification with no target verified nothing.
    exit(1);
}

$failures = 0;

foreach ($installs as $install) {
    echo "=== update offered on {$install->id} ({$install->path}) ===\n";
    echo "    asking whether a site on {$previous} is offered {$expected}\n";

    $cli = $install->path . '/cli/joomla.php';

    if (!is_file($cli)) {
        fwrite(STDERR, "  FAIL no Joomla console at {$cli}\n");
        $failures++;

        continue;
    }

    try {
        $site = TestSite::fromPath($install->path);
        $pdo  = $site->db();
    } catch (\RuntimeException $e) {
        fwrite(STDERR, '  FAIL ' . $e->getMessage() . "\n");
        $failures++;

        continue;
    }

    $query = new ExtensionQuery($site);
    $eid   = $query->id('component', 'com_proclaim');

    if ($eid === null) {
        fwrite(STDERR, "  FAIL com_proclaim is not registered on this site.\n");
        $failures++;

        continue;
    }

    $extensions = $site->table('#__extensions');
    $updates    = $site->table('#__updates');
    $sites      = $site->table('#__update_sites');

    $original = (string) $pdo
        ->query("SELECT manifest_cache FROM {$extensions} WHERE extension_id = " . (int) $eid)
        ->fetchColumn();

    if ($original === '') {
        fwrite(STDERR, "  FAIL com_proclaim has no manifest_cache to compare against.\n");
        $failures++;

        continue;
    }

    /**
     * Run Joomla's own discovery and report what it recorded for Proclaim.
     *
     * @return array{version: ?string, checked: bool}
     */
    $discover = static function () use ($cli, $install, $pdo, $updates, $sites): array {
        // findUpdates() purges #__updates and zeroes every timestamp first, so
        // a row afterwards was written by this run and cannot be a leftover.
        exec('cd ' . escapeshellarg($install->path) . ' && php ' . escapeshellarg($cli)
            . ' update:extensions:check 2>&1', $out, $code);

        $version = $pdo
            ->query("SELECT version FROM {$updates} WHERE element = 'com_proclaim' LIMIT 1")
            ->fetchColumn();

        $stamp = (int) $pdo
            ->query("SELECT MAX(last_check_timestamp) FROM {$sites}")
            ->fetchColumn();

        return [
            'version' => $version === false ? null : (string) $version,
            // ⚠️ A run that never reached the network leaves this at zero and
            // reports "no updates available" — the same words as a real,
            // correct answer. Without this, a site that cannot resolve the
            // update host passes the negative check and fails the positive one
            // for a reason that has nothing to do with the release.
            'checked' => $stamp > 0,
        ];
    };

    $restored = false;

    try {
        // --- negative control -------------------------------------------------
        // The site is on $expected already. Nothing may be offered. Without
        // this, a script that reports "offered" unconditionally passes forever.
        $before = $discover();

        if (!$before['checked']) {
            fwrite(STDERR, "  FAIL the update sites were never contacted; nothing below means anything.\n");
            $failures++;
        } elseif ($before['version'] !== null) {
            fwrite(
                STDERR,
                "  FAIL a site already on {$expected} was offered {$before['version']}.\n"
            );
            $failures++;
        } else {
            echo "    ok  a site already on {$expected} is offered nothing\n";
        }

        // --- the question ------------------------------------------------------
        $manifest = json_decode($original, true);

        if (!\is_array($manifest) || !isset($manifest['version'])) {
            throw new \RuntimeException('manifest_cache is not readable JSON carrying a version.');
        }

        $manifest['version'] = $previous;

        $write = $pdo->prepare("UPDATE {$extensions} SET manifest_cache = :m WHERE extension_id = :id");
        $write->execute([':m' => json_encode($manifest), ':id' => (int) $eid]);

        $after = $discover();

        if (!$after['checked']) {
            fwrite(STDERR, "  FAIL the update sites were never contacted.\n");
            $failures++;
        } elseif ($after['version'] === null) {
            fwrite(
                STDERR,
                "  FAIL a site on {$previous} was offered nothing. The stream may be correct and still "
                . "not reach anyone — check the update site URL, its targetplatform against this site's "
                . "Joomla version, and php_minimum against its PHP.\n"
            );
            $failures++;
        } elseif ($after['version'] !== $expected) {
            fwrite(STDERR, "  FAIL a site on {$previous} was offered {$after['version']}, not {$expected}.\n");
            $failures++;
        } else {
            echo "    ok  a site on {$previous} is offered {$expected}\n";
        }
    } catch (\Throwable $e) {
        fwrite(STDERR, '  FAIL ' . $e->getMessage() . "\n");
        $failures++;
    } finally {
        // ⚠️ The restore is the dangerous half. A manifest_cache left at the
        // previous release makes verify-installed-version.php — a release gate
        // — report a confident wrong answer on the next run. Put it back, then
        // read it back, and fail the script if it did not take even when every
        // check above passed.
        try {
            $put = $pdo->prepare("UPDATE {$extensions} SET manifest_cache = :m WHERE extension_id = :id");
            $put->execute([':m' => $original, ':id' => (int) $eid]);

            $now = (string) $pdo
                ->query("SELECT manifest_cache FROM {$extensions} WHERE extension_id = " . (int) $eid)
                ->fetchColumn();

            $restored = $now === $original;
        } catch (\Throwable $e) {
            fwrite(STDERR, '  RESTORE ERROR ' . $e->getMessage() . "\n");
        }

        // Leave no offer sitting in the interface for a version the site has.
        try {
            $pdo->exec("DELETE FROM {$updates} WHERE element IN ('com_proclaim', 'pkg_proclaim')");
        } catch (\Throwable) {
            // Cosmetic; the next check purges the table anyway.
        }

        if ($restored) {
            echo "    ok  com_proclaim manifest_cache restored\n";
        } else {
            fwrite(
                STDERR,
                "  FAIL com_proclaim manifest_cache was NOT restored on {$install->path}. "
                . "Put it back before running any release gate against this site.\n"
            );
            $failures++;
        }
    }
}

if ($failures > 0) {
    fwrite(STDERR, "\n{$failures} check(s) failed.\n");

    exit(1);
}

echo "\nThe update is offered to a site on the previous release.\n";

exit(0);
