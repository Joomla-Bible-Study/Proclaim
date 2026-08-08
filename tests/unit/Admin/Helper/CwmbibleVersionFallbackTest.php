<?php

/**
 * Unit tests for Bible version option building and its fallback.
 *
 * @package    Proclaim.Test
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmbibleVersionHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1585.
 *
 * buildOptions() substituted four hardcoded versions whenever the collected
 * list came out empty. That is right when the catalogue itself is empty -- the
 * documented "translation sync has not run yet" case -- and wrong when
 * servable-only filtering emptied it, because then a catalogue does exist and
 * nothing in it can be served. Offering kjv/web/asv/ylt there presented four
 * versions as selectable in a field whose whole point is that its options are
 * servable, and the reference failed to resolve when rendered.
 *
 * Two fields ask for servable-only: bible_version in scripture_row.xml and
 * default_bible_version in admin.xml.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmbibleVersionHelper::class)]
class CwmbibleVersionFallbackTest extends ProclaimTestCase
{
    /**
     * Call the private option builder directly — it exists precisely so this
     * logic can be exercised with synthetic rows rather than live catalogue
     * data.
     *
     * @param   array<int, \stdClass>  $rows            Catalogue rows
     * @param   bool                   $servableOnly    Restrict to servable
     * @param   string[]               $enabledSources  Enabled providers
     *
     * @return  array<int, array{value: string, text: string}>
     */
    private function build(array $rows, bool $servableOnly, array $enabledSources = []): array
    {
        return (new \ReflectionMethod(CwmbibleVersionHelper::class, 'buildOptions'))
            ->invoke(null, $rows, $servableOnly, $enabledSources, 'en');
    }

    /**
     * @param   string  $abbr       Abbreviation
     * @param   int     $installed  1 when installed locally
     * @param   string  $source     Provider source
     *
     * @return  \stdClass
     */
    private function row(string $abbr, int $installed = 0, string $source = 'getbible'): \stdClass
    {
        return (object) [
            'abbreviation' => $abbr,
            'name'         => strtoupper($abbr) . ' Translation',
            'language'     => 'en',
            'installed'    => $installed,
            'source'       => $source,
        ];
    }

    /**
     * @param   array<int, array{value: string, text: string}>  $options  Built options
     *
     * @return  string[]
     */
    private function values(array $options): array
    {
        return array_map(static fn (array $o): string => $o['value'], $options);
    }

    // -------------------------------------------------------------------------
    // The case the fallback exists for
    // -------------------------------------------------------------------------

    #[TestDox('An empty catalogue still falls back to the common defaults')]
    public function testEmptyCatalogueFallsBack(): void
    {
        $values = $this->values($this->build([], false));

        $this->assertContains('kjv', $values, 'Before the translation sync runs there is nothing else to offer');
        $this->assertContains('web', $values);
    }

    #[TestDox('An empty catalogue falls back even in servable-only mode')]
    public function testEmptyCatalogueFallsBackWhenServableOnly(): void
    {
        $values = $this->values($this->build([], true, []));

        $this->assertContains(
            'kjv',
            $values,
            'With no catalogue at all there is nothing to filter, so the documented fallback still applies'
        );
    }

    // -------------------------------------------------------------------------
    // The case it must not apply to
    // -------------------------------------------------------------------------

    /**
     * The reported scenario: a catalogue exists, GDPR mode has disabled every
     * provider, and nothing is installed locally.
     */
    #[TestDox('Filtering everything out yields nothing, not the hardcoded defaults')]
    public function testServableFilteringToEmptyReturnsEmpty(): void
    {
        $rows = [
            $this->row('niv', 0, 'getbible'),
            $this->row('esv', 0, 'apibible'),
        ];

        $options = $this->build($rows, true, []);

        $this->assertSame(
            [],
            $options,
            'Offering kjv/web/asv/ylt here promises servable versions that cannot be served — see #1585'
        );
    }

    #[TestDox('A locally installed translation survives servable-only filtering')]
    public function testInstalledTranslationIsOffered(): void
    {
        $rows = [
            $this->row('niv', 0, 'getbible'),
            $this->row('kjv', 1, 'local'),
        ];

        $values = $this->values($this->build($rows, true, []));

        $this->assertSame(['kjv'], $values, 'Installed translations are servable regardless of providers');
    }

    #[TestDox('A translation from an enabled provider survives filtering')]
    public function testEnabledProviderTranslationIsOffered(): void
    {
        $rows = [
            $this->row('niv', 0, 'getbible'),
            $this->row('esv', 0, 'apibible'),
        ];

        $values = $this->values($this->build($rows, true, ['getbible']));

        $this->assertSame(['niv'], $values, 'Only sources actually enabled may be offered');
    }

    /**
     * Without servable-only the catalogue is offered as-is, and the fallback
     * must not appear alongside it.
     */
    #[TestDox('A populated catalogue is never padded with the defaults')]
    public function testPopulatedCatalogueIsNotPadded(): void
    {
        $values = $this->values($this->build([$this->row('niv')], false));

        $this->assertSame(['niv'], $values);
        $this->assertNotContains('kjv', $values, 'The fallback is for an empty catalogue, not a small one');
    }
}
