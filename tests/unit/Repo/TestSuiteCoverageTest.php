<?php

/**
 * Test-suite reachability contract.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Repo;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * A test file only runs if it clears two independent gates: it must sit under a
 * <directory> some phpunit.xml testsuite declares, AND that testsuite's name
 * must appear in the explicit --testsuite list composer.json passes. Miss
 * either and the file is silently inert — it does not error, it does not skip,
 * it simply never runs, and the suite goes green without it.
 *
 * Both gates have been missed. ScriptureStackOwnershipTest sat directly in
 * tests/unit/Admin/ while every suite points at a subdirectory, so its four
 * contract tests never ran. Separately, a new tests/unit/Admin/View directory
 * was registered in phpunit.xml but not in composer.json, and the total stayed
 * at exactly 2064 until both were done.
 *
 * @since  __DEPLOY_VERSION__
 */
class TestSuiteCoverageTest extends ProclaimTestCase
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
     * Directories declared by phpunit.xml, keyed by the suite that declares
     * them, limited to suites the composer scripts actually invoke.
     *
     * @return  string[]  Repo-relative directories
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function wiredDirectories(): array
    {
        $xml      = simplexml_load_file(self::repoRoot() . '/phpunit.xml');
        $composer = file_get_contents(self::repoRoot() . '/composer.json');

        $wired = [];

        foreach ($xml->testsuites->testsuite as $suite) {
            $name = (string) $suite['name'];

            // The suite has to be named in a composer script, or nothing runs it.
            if (!str_contains($composer, $name)) {
                continue;
            }

            foreach ($suite->directory as $directory) {
                $wired[] = trim((string) $directory, '/');
            }
        }

        return $wired;
    }

    #[TestDox('every test file sits in a suite that a composer script runs')]
    public function testEveryTestFileIsReachable(): void
    {
        $root  = self::repoRoot();
        $wired = self::wiredDirectories();

        self::assertNotEmpty($wired, 'No phpunit.xml suite is referenced by composer.json at all.');

        $orphans = [];

        foreach (['tests/unit', 'tests/integration'] as $tree) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root . '/' . $tree, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!str_ends_with($file->getFilename(), 'Test.php')) {
                    continue;
                }

                $relative = str_replace($root . '/', '', $file->getPathname());
                $covered  = false;

                foreach ($wired as $directory) {
                    if (str_starts_with($relative, $directory . '/')) {
                        $covered = true;

                        break;
                    }
                }

                if (!$covered) {
                    $orphans[] = $relative;
                }
            }
        }

        sort($orphans);

        self::assertSame(
            [],
            $orphans,
            "These test files never run — no phpunit.xml suite that composer invokes covers them:\n  "
            . implode("\n  ", $orphans)
            . "\n\nMove each into a covered directory, or declare a suite in phpunit.xml AND add its name to the "
            . '--testsuite list in composer.json. Both are required.'
        );
    }
}
