<?php

/**
 * One Joomla minimum, declared consistently, and actually exercised.
 *
 * The package declared 5.1.0, the component 5.0.0, the README badge 5.1.0 and
 * the library 5.2.0, while CI built against 5.4.3 and the code had quietly
 * come to need 5.3.0 — five numbers, no two of which agreed, and nothing that
 * would notice (#1963). A site on the advertised minimum got a failure partway
 * through installing, or a fatal on a list view.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Repo;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @since 10.6.0
 */
class JoomlaFloorTest extends TestCase
{
    /**
     * Joomla APIs this repository calls that did not exist in every 5.x.
     *
     * ⚠️ This is the check that matters. The floor has to come from what the
     * code calls, not from what a manifest happens to say — the 5.3.0 entry
     * below was found in `Cwmseries` while every declared minimum still said
     * 5.0, 5.1 or 5.2, so the Series list view was a fatal on three of the
     * versions the package claimed to support.
     *
     * Add a row when you reach for something newer than the floor, and the
     * test will tell you the floor has to move with it.
     *
     * @var array<string, string>
     * @since 10.6.0
     */
    private const array API_FLOOR = [
        // Joomla\CMS\Form\Form::addControlField() / renderControlFields()
        'addControlField'     => '5.3.0',
        'renderControlFields' => '5.3.0',
    ];

    /**
     * @var string[]
     * @since 10.6.0
     */
    private const array SOURCE_ROOTS = ['admin', 'site', 'api', 'plugins', 'modules'];

    /**
     * Both checkouts of the scripture library, relative to the repository root.
     *
     * The nested one is what the shipped package is built from
     * (`subBuild` runs the ScriptureLinks package build); the top-level one is
     * what this repository develops against. They are pinned separately and
     * have drifted before.
     *
     * @var string[]
     * @since 10.6.0
     */
    private const array LIBRARY_SCRIPTS = [
        '/libraries/lib_cwmscripture/script.php',
        '/plugins/content/scripturelinks/libraries/lib_cwmscripture/script.php',
    ];

    /**
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('The component and the package installer declare the same Joomla minimum')]
    public function testDeclarationsAgree(): void
    {
        $this->assertSame(
            $this->packageFloor(),
            $this->componentFloor(),
            'proclaim.script.php and build/script.install.php declare different Joomla minimums. '
            . 'The package gate runs first, so a site gets past one boundary and is refused at the next.'
        );
    }

    /**
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('The README advertises the minimum the installers enforce')]
    public function testReadmeMatches(): void
    {
        $readme = (string) file_get_contents(self::root() . '/README.md');
        $floor  = $this->componentFloor();

        $this->assertStringContainsString(
            'Joomla-' . $floor . '+-blue',
            $readme,
            'The README badge advertises a Joomla version the installer does not accept.'
        );
        $this->assertStringContainsString(
            '- Joomla ' . $floor . '+ installation',
            $readme,
            'The README requirements list disagrees with the installer.'
        );
    }

    /**
     * ⚠️ A minimum nothing runs against is a guess. CI clones one Joomla, and
     * if that is above the declared floor then every version between the two
     * is claimed and never exercised.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('CI builds against a Joomla in the supported range')]
    public function testCiBuildsAtTheFloor(): void
    {
        $ci = (string) file_get_contents(self::root() . '/.github/workflows/ci.yml');

        $this->assertNotSame('', $ci, 'ci.yml could not be read — this test would pass on nothing.');

        preg_match_all('/--branch\s+([0-9]+\.[0-9]+\.[0-9]+)\s+https:\/\/github\.com\/joomla\/joomla-cms/', $ci, $m);

        $this->assertNotEmpty($m[1], 'No pinned joomla-cms clone found in ci.yml.');

        $floor = $this->componentFloor();

        foreach (array_unique($m[1]) as $tested) {
            $this->assertTrue(
                version_compare($tested, $floor, '>='),
                'CI builds against Joomla ' . $tested . ', below the declared floor of ' . $floor . '.'
            );

            // Same minor as the floor, so the version CI proves is the version
            // sites at the minimum are running. A newer minor would leave the
            // floor itself untested.
            $this->assertSame(
                $this->minor($floor),
                $this->minor($tested),
                'CI builds against Joomla ' . $tested . ' but the floor is ' . $floor
                . '. Nothing then exercises the minimum that is advertised.'
            );
        }
    }

    /**
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('The floor is high enough for every Joomla API the code calls')]
    public function testFloorCoversTheApisUsed(): void
    {
        $floor = $this->componentFloor();
        $seen  = 0;

        foreach (self::API_FLOOR as $method => $introduced) {
            $hits = $this->grepSources($method);

            if ($hits === []) {
                continue;
            }

            $seen++;

            $this->assertTrue(
                version_compare($floor, $introduced, '>='),
                $method . '() was added in Joomla ' . $introduced . ' and is called from '
                . implode(', ', $hits) . ', but the declared floor is ' . $floor . '. '
                . 'On anything below ' . $introduced . ' that is a fatal, not a degraded feature.'
            );
        }

        // ⚠️ Not a silent pass. If every entry has been removed from the code
        // the loop asserts nothing, and the map should be pruned deliberately
        // rather than left looking like coverage.
        $this->assertGreaterThan(
            0,
            $seen,
            'No API in API_FLOOR is still called. Prune the map rather than leaving it as decoration.'
        );
    }

    /**
     * ⚠️ The shape of the original bug, guarded in the direction that hurts.
     *
     * `lib_cwmscripture` enforces its own minimum, and com_proclaim cannot
     * install without it. If the library ever demands more than the package
     * gate lets through, a site is admitted at the package boundary and
     * refused partway in — installed halfway and told nothing useful. That is
     * exactly what shipped: the package said 5.1.0 and the library 5.2.0.
     *
     * The library sitting *below* the package floor is merely an inaccurate
     * claim on its standalone-install path, and it lives in a submodule with
     * its own release chain — so this asserts the direction that breaks
     * sites, not equality.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('The scripture library never demands more Joomla than the package gate allows')]
    public function testLibraryFloorDoesNotExceedThePackage(): void
    {
        $checked = 0;

        // ⚠️ Both copies. The library is a submodule of this repository AND a
        // submodule of the ScriptureLinks one, and the shipped package is
        // built from the nested checkout -- so testing the top-level copy
        // alone would pass while the zip carried a different number.
        foreach (self::LIBRARY_SCRIPTS as $relative) {
            $script = self::root() . $relative;

            if (!is_file($script)) {
                continue;
            }

            $src = (string) file_get_contents($script);

            $this->assertMatchesRegularExpression(
                '/\$minimumJoomla\s*=\s*\'([0-9.]+)\'/',
                $src,
                $relative . ' no longer declares $minimumJoomla.'
            );

            preg_match('/\$minimumJoomla\s*=\s*\'([0-9.]+)\'/', $src, $m);

            $checked++;

            $this->assertTrue(
                version_compare($m[1], $this->packageFloor(), '<='),
                $relative . ' requires Joomla ' . $m[1] . ' but the package admits sites from '
                . $this->packageFloor() . '. Those sites install the package and then fail on the library.'
            );
        }

        if ($checked === 0) {
            self::markTestSkipped('No lib_cwmscripture checkout is present.');
        }

        // ⚠️ Not a silent pass on one copy. Finding only one means a submodule
        // is missing, and the copy that ships is the one that was skipped.
        $this->assertCount(
            $checked,
            self::LIBRARY_SCRIPTS,
            'Only ' . $checked . ' of the two lib_cwmscripture checkouts was present. '
            . 'The shipped package is built from the nested one.'
        );
    }

    /**
     * Files under the source roots mentioning a symbol.
     *
     * @param   string  $needle  The symbol to look for.
     *
     * @return  string[]  Repository-relative paths.
     *
     * @since   10.6.0
     */
    private function grepSources(string $needle): array
    {
        $root = self::root();
        $hits = [];

        foreach (self::SOURCE_ROOTS as $dir) {
            if (!is_dir($root . '/' . $dir)) {
                continue;
            }

            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root . '/' . $dir));

            foreach ($it as $file) {
                $path = str_replace('\\', '/', $file->getPathname());

                if (!str_ends_with($path, '.php') || str_contains($path, '/vendor/')) {
                    continue;
                }

                if (str_contains((string) file_get_contents($path), $needle . '(')) {
                    $hits[] = str_replace($root . '/', '', $path);
                }
            }
        }

        return $hits;
    }

    /**
     * The floor the component enforces.
     *
     * @return  string
     *
     * @since   10.6.0
     */
    private function componentFloor(): string
    {
        return $this->readFloor('/proclaim.script.php');
    }

    /**
     * The floor the package installer enforces.
     *
     * @return  string
     *
     * @since   10.6.0
     */
    private function packageFloor(): string
    {
        return $this->readFloor('/build/script.install.php');
    }

    /**
     * Pull `$minimumJoomla` out of an install script.
     *
     * @param   string  $relative  Path from the repository root.
     *
     * @return  string
     *
     * @since   10.6.0
     */
    private function readFloor(string $relative): string
    {
        $src = (string) file_get_contents(self::root() . $relative);

        $this->assertNotSame('', $src, $relative . ' could not be read — this test would pass on nothing.');
        $this->assertMatchesRegularExpression(
            '/\$minimumJoomla\s*=\s*\'([0-9.]+)\'/',
            $src,
            $relative . ' no longer declares $minimumJoomla.'
        );

        preg_match('/\$minimumJoomla\s*=\s*\'([0-9.]+)\'/', $src, $m);

        return $m[1];
    }

    /**
     * The `major.minor` of a version.
     *
     * @param   string  $version  A full version string.
     *
     * @return  string
     *
     * @since   10.6.0
     */
    private function minor(string $version): string
    {
        $parts = explode('.', $version);

        return $parts[0] . '.' . ($parts[1] ?? '0');
    }

    /**
     * The repository root.
     *
     * @return  string
     *
     * @since   10.6.0
     */
    private static function root(): string
    {
        return \dirname(__DIR__, 3);
    }
}
