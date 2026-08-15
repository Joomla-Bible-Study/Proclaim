<?php

/**
 * Legacy migrations must run for the sites that need them, and no others.
 *
 * Nine migrations in postflight were gated only on `$type === 'update'`, with no
 * record of having run and no cheap way to tell whether there was anything to
 * do. A site on 10.5.7 therefore re-ran migrations written for 10.0 → 10.1,
 * loading whole tables each time to discover the answer was no. On a site with
 * real content that turned a routine update into minutes of spinner (#1841).
 *
 * ⚠️ The failure modes are not symmetric. Running a migration that was not
 * needed costs time; skipping one that was needed loses data. Every assertion
 * here is written from that asymmetry — an unknown source version must run
 * everything.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since  __DEPLOY_VERSION__
 */
class MigrationVersionGateTest extends ProclaimTestCase
{
    /**
     * @return  string  The manifest script's source
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function script(): string
    {
        return (string) file_get_contents(\dirname(__DIR__, 4) . '/proclaim.script.php');
    }

    /**
     * The gate's own logic, mirrored so it can be exercised without booting the
     * installer. Kept honest by testTheMirrorMatchesTheSource() below.
     *
     * @param   string  $from   Version being upgraded from ('' when unknown)
     * @param   string  $since  Release the migration shipped in
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function gate(string $from, string $since): bool
    {
        if ($from === '') {
            return true;
        }

        return version_compare($from, $since, '<');
    }

    /**
     * @return  array<string, array{0: string, 1: string, 2: bool, 3: string}>
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function cases(): array
    {
        return [
            'unknown source runs everything'                    => ['', '10.1.0', true, 'A source version that cannot be read must be treated as the oldest case.'],
            'a 10.5.7 site skips a 10.1.0 migration'            => ['10.5.7', '10.1.0', false, 'This is the case that made updates slow.'],
            'a 10.0.0 site still runs it'                       => ['10.0.0', '10.1.0', true, 'The migration exists for exactly this site.'],
            'the release that introduced it does not re-run it' => ['10.1.0', '10.1.0', false, 'It ran on the update that installed 10.1.0.'],
            'a pre-release of the target still runs it'         => ['10.1.0-beta1', '10.1.0', true, 'A beta predates the release, so the migration has not run.'],
            'a 10.3.1 site runs a 10.3.2 migration'             => ['10.3.1', '10.3.2', true, 'One patch short is still short.'],
            'a 9.x source runs everything'                      => ['9.2.8', '10.5.8', true, 'The oldest supported source needs every migration.'],
        ];
    }

    /**
     * @param   string  $from   Source version
     * @param   string  $since  Migration's release
     * @param   bool    $runs   Whether it should run
     * @param   string  $why    Why that is the answer
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('cases')]
    #[TestDox('$_dataName')]
    public function testTheGate(string $from, string $since, bool $runs, string $why): void
    {
        self::assertSame($runs, self::gate($from, $since), $why);
    }

    /**
     * The mirror above is only useful if it is the same rule the script applies.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('the gate mirrored here matches the one in the script')]
    public function testTheMirrorMatchesTheSource(): void
    {
        $source = self::script();

        self::assertStringContainsString(
            'private function upgradingFromBefore(string $version): bool',
            $source,
            'The gate has been renamed or removed; this test is no longer checking anything real.'
        );

        self::assertMatchesRegularExpression(
            '~upgradingFromBefore\(string \$version\): bool\s*\{\s*if \(\$this->fromVersion === \'\'\) \{\s*return true;~',
            $source,
            'The unknown-source case no longer returns true, so a site whose version cannot be read would '
            . 'silently skip migrations it needs.'
        );

        self::assertStringContainsString(
            "version_compare(\$this->fromVersion, \$version, '<')",
            $source,
            'The comparison is no longer a strict less-than, so the release that introduced a migration would '
            . 're-run it (or a site one release short would skip it).'
        );
    }

    /**
     * ⚠️ Anti-regression: a migration added to that block without a gate would
     * reintroduce exactly the problem, and nothing else would notice.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('every migration in the update block is gated')]
    public function testEveryMigrationIsGated(): void
    {
        $source = self::script();
        $start  = strpos($source, 'The remaining legacy data migrations run on UPGRADES only');

        self::assertNotFalse($start, 'The update-only migration block could not be found.');

        $end = strpos($source, "if (\$type === 'install' || \$type === 'update')", $start);

        self::assertNotFalse($end, 'The end of the migration block could not be found.');

        $block = substr($source, $start, $end - $start);

        // A migration called directly, rather than through step(), is neither
        // gated nor recorded — which is how every update came to re-run all of
        // them.
        preg_match_all('~^\s{12}(?:\$this->|CwmmigrationHelper::)(\w+)\(\);~m', $block, $ungated);

        $bare = array_values(array_diff($ungated[1], ['step']));

        self::assertSame(
            [],
            $bare,
            'These migrations are called directly instead of through step(), so they are neither version-gated '
            . 'nor timed: ' . implode(', ', $bare)
            . '. Wrap each in $this->step(\'Name\', \'X.Y.Z\', fn () => ...).'
        );

        // Every step() call must name the release it shipped in.
        preg_match_all('~\$this->step\(\s*\'[^\']+\',\s*\'(\d+\.\d+\.\d+)\'~', $block, $versions);

        self::assertSame(
            preg_match_all('~\$this->step\(~', $block),
            \count($versions[1]),
            'A step() call is missing its version argument, so it would run on every update.'
        );

        self::assertGreaterThanOrEqual(
            8,
            \count($versions[1]),
            'Fewer gated steps than expected — a migration may have lost its gate.'
        );
    }

    /**
     * The version has to be read before Joomla overwrites it.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('the source version is captured in preflight, not postflight')]
    public function testTheVersionIsReadEarly(): void
    {
        $source    = self::script();
        $preflight = strpos($source, 'public function preflight(');
        $capture   = strpos($source, '$this->fromVersion = $this->readInstalledVersion();');

        self::assertNotFalse($capture, 'The source version is never captured.');
        self::assertNotFalse($preflight, 'preflight() could not be found.');

        self::assertGreaterThan(
            $preflight,
            $capture,
            'The capture must be inside preflight. By postflight, manifest_cache already holds the incoming '
            . 'version, so every gate would compare the new version against itself and skip everything.'
        );
    }
}
