<?php

/**
 * Unit tests for Cwmmedia Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for Cwmmedia helper
 *
 * @since  10.0.0
 */
class CwmmediaTest extends ProclaimTestCase
{
    /**
     * Test isExternal method
     *
     * @return void
     */
    public function testIsExternal(): void
    {
        // Test absolute URLs
        $this->assertTrue(Cwmmedia::isExternal('http://google.com/image.jpg'));
        $this->assertTrue(Cwmmedia::isExternal('https://example.com/file.mp3'));

        // Test relative URLs (should be false)
        $this->assertFalse(Cwmmedia::isExternal('images/local.jpg'));
        $this->assertFalse(Cwmmedia::isExternal('/images/local.jpg'));
    }

    /**
     * A brand icon stored in FA4 form must end up in the brand family.
     *
     * This is the regression that broke ~263 media rows: `fa fa-youtube` was
     * rewritten to `fa-solid fa-youtube`, which is tofu because the Free font has
     * no glyph at the YouTube codepoint. The FA4 form had been rendering fine via
     * Joomla's shim `.fa.fa-youtube { font-family: "Font Awesome 6 Brands" }`,
     * which keys on the bare `.fa` class the rewrite removed.
     *
     * @return void
     */
    public function testFa4BrandIconGetsBrandFamily(): void
    {
        $this->assertSame('fa-brands fa-youtube', Cwmmedia::normalizeIconClass('fa fa-youtube'));
        $this->assertSame('fa-brands fa-vimeo', Cwmmedia::normalizeIconClass('fa fa-vimeo'));
    }

    /**
     * Values already damaged in the database must self-heal at render time.
     *
     * The 10.3.0 migration rewrote stored params in place, so sites carry
     * `fa-solid fa-youtube` on disk. Normalizing must repair it rather than
     * faithfully reproduce it.
     *
     * @return void
     */
    public function testAlreadyBrokenSolidBrandIsRepaired(): void
    {
        $this->assertSame('fa-brands fa-youtube', Cwmmedia::normalizeIconClass('fa-solid fa-youtube'));
        $this->assertSame('fa-brands fa-vimeo-v', Cwmmedia::normalizeIconClass('fa-solid fa-vimeo-v'));
    }

    /**
     * fa-vimeo is a prefix of fa-vimeo-v, so token-wise matching matters.
     *
     * @return void
     */
    public function testPrefixOverlappingBrandNamesAreNotCorrupted(): void
    {
        $this->assertSame('fa-brands fa-vimeo-v', Cwmmedia::normalizeIconClass('fab fa-vimeo-v'));
        $this->assertSame('fa-brands fa-vimeo', Cwmmedia::normalizeIconClass('fab fa-vimeo'));
    }

    /**
     * FA4 names that exist only as a shim must be renamed, not just re-prefixed.
     *
     * `fa-video-camera` has no plain rule in joomla-fontawesome.css — only
     * `.fa.fa-video-camera` — so `fa-solid fa-video-camera` is tofu. Affected
     * ~181 media rows.
     *
     * @return void
     */
    public function testShimOnlyFa4NamesAreRenamedToFa6(): void
    {
        $this->assertSame('fa-solid fa-video', Cwmmedia::normalizeIconClass('fa fa-video-camera'));
        $this->assertSame('fa-brands fa-youtube', Cwmmedia::normalizeIconClass('fa fa-youtube-play'));
        $this->assertSame('fa-brands fa-square-youtube', Cwmmedia::normalizeIconClass('fab fa-youtube-square'));
    }

    /**
     * Non-brand icons must keep the solid family, and already-FA6 values must be
     * returned untouched.
     *
     * @return void
     */
    public function testNonBrandIconsAndIdempotence(): void
    {
        $this->assertSame('fa-solid fa-play', Cwmmedia::normalizeIconClass('fa fa-play'));
        $this->assertSame('fa-solid fa-play', Cwmmedia::normalizeIconClass('fas fa-play'));
        $this->assertSame('fa-solid fa-file-pdf', Cwmmedia::normalizeIconClass('fas fa-file-pdf'));

        // Idempotent — normalizing twice changes nothing
        $this->assertSame('fa-solid fa-play', Cwmmedia::normalizeIconClass('fa-solid fa-play'));
        $this->assertSame(
            'fa-brands fa-youtube',
            Cwmmedia::normalizeIconClass(Cwmmedia::normalizeIconClass('fa fa-youtube'))
        );
    }

    /**
     * The JBS_MED_CUSTOM sentinel must pass through untouched.
     *
     * getIcons() maps 'JBS_MED_CUSTOM' => '1', and the caller branches on it to
     * read media_custom_icon instead. It is a marker, not a class.
     *
     * @return void
     */
    public function testCustomIconSentinelIsUntouched(): void
    {
        $this->assertSame('1', Cwmmedia::normalizeIconClass('1'));
    }
}
