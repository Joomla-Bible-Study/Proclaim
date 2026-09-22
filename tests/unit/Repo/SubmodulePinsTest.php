<?php

/**
 * Submodule pin contract.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Repo;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * `lib_cwmscripture` is checked out twice, and the copies serve different
 * masters:
 *
 *   libraries/lib_cwmscripture
 *       Pinned by Proclaim. composer.json maps CWM\Library\Scripture\ at its
 *       src/, so this is the copy the tests and the running site load.
 *
 *   plugins/content/scripturelinks/libraries/lib_cwmscripture
 *       Pinned by ScriptureLinks. cwm-build.config.json declares a subBuild on
 *       plugins/content/scripturelinks, which runs the ScriptureLinks package
 *       build against *its* submodule to produce pkg_cwmscripture.zip.
 *
 * So Proclaim tests one checkout and ships the other. While the two pins agree
 * that is invisible; the moment they diverge the suite passes against a library
 * version the release does not contain, and nothing says so. Same shape as
 * #1757, where the release bundled whatever zip was newest on disk instead of
 * the pinned submodule.
 *
 * @since  __DEPLOY_VERSION__
 */
class SubmodulePinsTest extends ProclaimTestCase
{
    /**
     * @return  string
     * @since   __DEPLOY_VERSION__
     */
    private static function repoRoot(): string
    {
        return \dirname(__DIR__, 3);
    }

    /**
     * The commit a repository records for one of its submodules.
     *
     * Reads the gitlink rather than the checked-out HEAD: the pin is what
     * ships, and a locally checked-out working tree may legitimately sit
     * elsewhere while someone is mid-change.
     *
     * @param   string  $repo        Repository working directory
     * @param   string  $submodule   Submodule path, relative to that repository
     *
     * @return  string|null  The recorded commit, or null if it cannot be read
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function pinnedCommit(string $repo, string $submodule): ?string
    {
        $command = \sprintf(
            'git -C %s ls-tree HEAD %s 2>/dev/null',
            escapeshellarg($repo),
            escapeshellarg($submodule)
        );

        $line = trim((string) shell_exec($command));

        // "160000 commit <sha>\t<path>" — 160000 is git's gitlink mode.
        if (!preg_match('/^160000 commit ([0-9a-f]{40})\t/', $line, $match)) {
            return null;
        }

        return $match[1];
    }

    #[TestDox('both lib_cwmscripture checkouts are pinned to the same commit')]
    public function testScriptureLibraryPinsAgree(): void
    {
        $root = self::repoRoot();

        $ours   = self::pinnedCommit($root, 'libraries/lib_cwmscripture');
        $theirs = self::pinnedCommit($root . '/plugins/content/scripturelinks', 'libraries/lib_cwmscripture');

        // Never let an unreadable pin pass as agreement — that is the failure
        // this test exists to catch, wearing a different hat.
        self::assertNotNull(
            $ours,
            'Could not read Proclaim\'s lib_cwmscripture pin. Run with submodules checked out '
            . '(git submodule update --init --recursive).'
        );

        self::assertNotNull(
            $theirs,
            'Could not read the ScriptureLinks lib_cwmscripture pin. The scripturelinks submodule is probably '
            . 'not checked out; this test cannot verify anything without it.'
        );

        self::assertSame(
            $ours,
            $theirs,
            "The two lib_cwmscripture checkouts pin different commits.\n"
            . "  libraries/lib_cwmscripture                      = $ours  (what the tests load)\n"
            . "  plugins/content/scripturelinks/libraries/…      = $theirs  (what the release ships)\n"
            . 'Update both pins together — lib, then scripturelinks, then Proclaim. See #1933.'
        );
    }
}
