<?php

/**
 * Committed vendor contract.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Repo;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * A nested Composer project that commits its vendor directory ships that
 * directory as it is: the package build never runs composer install there. The
 * YouTube server addon is one.
 *
 * Dependabot can only change composer.lock. Its update merges green and closes
 * the security alert, and the package goes on shipping the old code until
 * someone runs composer install in that directory and commits the result. This
 * holds each committed vendor to its lock, so CI fails until they agree.
 *
 * @since  10.7.8
 */
class CommittedVendorMatchesLockTest extends ProclaimTestCase
{
    /**
     * The addon this contract was written for. A discovery that misses it has
     * stopped finding anything, and would pass every project it never checked.
     *
     * @var    string
     * @since  10.7.8
     */
    private const string YOUTUBE_ADDON = 'admin/src/Addons/Servers/Youtube';

    /**
     * The repository root.
     *
     * @return  string  Its absolute path
     *
     * @since   10.7.8
     */
    private static function repoRoot(): string
    {
        return \dirname(__DIR__, 3);
    }

    /**
     * The nested Composer projects that commit their vendor directory.
     *
     * Found from the files git tracks, submodules included, so a project only
     * counts when its installed.json is committed: a vendor directory that is
     * merely present on disk is not what ships.
     *
     * @return  string[]  Project directories, relative to the repository root
     *
     * @since   10.7.8
     */
    private static function projectsWithCommittedVendor(): array
    {
        $root   = self::repoRoot();
        $output = shell_exec('git -C ' . escapeshellarg($root) . ' ls-files --recurse-submodules 2>/dev/null');

        $tracked  = array_flip(preg_split('/\R/', trim((string) $output)));
        $projects = [];

        foreach (array_keys($tracked) as $file) {
            if (basename($file) !== 'composer.lock') {
                continue;
            }

            $dir       = \dirname($file);
            $prefix    = $dir === '.' ? '' : $dir . '/';
            $config    = json_decode((string) file_get_contents($root . '/' . $prefix . 'composer.json'), true, 512, JSON_THROW_ON_ERROR);
            $vendor    = $config['config']['vendor-dir'] ?? 'vendor';
            $installed = $prefix . $vendor . '/composer/installed.json';

            if (isset($tracked[$installed])) {
                $projects[$prefix . $vendor] = $dir;
            }
        }

        return $projects;
    }

    /**
     * Each package's version and commit, keyed by package name.
     *
     * @param   array  $packages  Package entries from composer.lock or installed.json
     *
     * @return  array<string, array{version: string, reference: string}>  Each package's version and commit reference
     *                                                                    (dist, else source, else empty), keyed by name
     *
     * @since   10.7.8
     */
    private static function packageMap(array $packages): array
    {
        $map = [];

        foreach ($packages as $package) {
            $map[$package['name']] = [
                'version'   => $package['version'],
                'reference' => $package['dist']['reference'] ?? $package['source']['reference'] ?? '',
            ];
        }

        return $map;
    }

    /**
     * How a project's committed vendor differs from its lock.
     *
     * @param   string  $dir     Project directory, relative to the repository root
     * @param   string  $vendor  Vendor directory, relative to the repository root
     *
     * @return  string[]  One line per difference; none when they agree
     *
     * @since   10.7.8
     */
    private static function differences(string $dir, string $vendor): array
    {
        $root      = self::repoRoot();
        // Unreadable files throw: two that both decode to nothing would agree.
        $lock      = json_decode((string) file_get_contents($root . '/' . $dir . '/composer.lock'), true, 512, JSON_THROW_ON_ERROR);
        $installed = json_decode((string) file_get_contents($root . '/' . $vendor . '/composer/installed.json'), true, 512, JSON_THROW_ON_ERROR);

        // Dev packages are only expected when the vendor was installed with them.
        $pinned = $lock['packages'] ?? [];

        if ($installed['dev'] ?? false) {
            $pinned = array_merge($pinned, $lock['packages-dev'] ?? []);
        }

        $wanted = self::packageMap($pinned);
        $have   = self::packageMap($installed['packages'] ?? []);
        $lines  = [];

        foreach ($wanted as $name => $package) {
            if (!isset($have[$name])) {
                $lines[] = $name . ' ' . $package['version'] . ' is locked but not in vendor/';
            } elseif ($have[$name]['version'] !== $package['version']) {
                $lines[] = $name . ': the lock pins ' . $package['version'] . ', vendor/ has ' . $have[$name]['version'];
            } elseif ($have[$name]['reference'] !== $package['reference']) {
                $lines[] = $name . ' ' . $package['version'] . ': vendor/ holds a different commit than the lock';
            }
        }

        foreach (array_diff_key($have, $wanted) as $name => $package) {
            $lines[] = $name . ' ' . $package['version'] . ' is in vendor/ but not in the lock';
        }

        return $lines;
    }

    /**
     * The YouTube addon is among the projects whose committed vendor is checked.
     *
     * @return  void
     *
     * @since   10.7.8
     */
    #[TestDox('The YouTube addon is among the projects whose committed vendor is checked')]
    public function testTheYoutubeAddonIsChecked(): void
    {
        self::assertContains(
            self::YOUTUBE_ADDON,
            self::projectsWithCommittedVendor(),
            'Found no committed vendor/ for the YouTube addon. If git cannot list the tracked files, '
            . 'this contract checks nothing; run it from a git checkout with submodules.'
        );
    }

    /**
     * Every committed vendor folder installs exactly the packages its composer.lock pins.
     *
     * @return  void
     *
     * @since   10.7.8
     */
    #[TestDox('Every committed vendor installs exactly the packages its composer.lock pins')]
    public function testCommittedVendorMatchesTheLock(): void
    {
        $report = [];

        foreach (self::projectsWithCommittedVendor() as $vendor => $dir) {
            $lines = self::differences($dir, $vendor);

            if ($lines) {
                $report[] = $dir . ":\n  - " . implode("\n  - ", $lines);
            }
        }

        self::assertSame(
            [],
            $report,
            "The committed vendor/ does not match composer.lock, so the package would ship other code than the lock names.\n"
            . implode("\n", $report) . "\n"
            . 'Run `composer install --no-dev` in that directory and commit the vendor/ changes with the lock. '
            . 'A lock-only update, such as a Dependabot pull request, changes nothing that ships.'
        );
    }
}
