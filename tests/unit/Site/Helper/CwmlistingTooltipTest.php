<?php

/**
 * Unit tests for Cwmlisting tooltip methods
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;

/**
 * Test class for Cwmlisting tooltip methods
 *
 * @since  10.1.0
 */
class CwmlistingTooltipTest extends ProclaimTestCase
{
    /**
     * Helper method to call private buildApiReference
     *
     * @param   string  $book  Book name
     * @param   int     $ch_b  Chapter begin
     * @param   int     $ch_e  Chapter end
     * @param   int     $v_b   Verse begin
     * @param   int     $v_e   Verse end
     *
     * @return  string
     */
    private function callBuildApiReference(string $book, int $ch_b, int $ch_e, int $v_b, int $v_e): string
    {
        $listing = new Cwmlisting();
        $method  = new \ReflectionMethod($listing, 'buildApiReference');

        return $method->invoke($listing, $book, $ch_b, $ch_e, $v_b, $v_e);
    }

    /**
     * Helper method to call private wrapScriptureTooltip
     *
     * @param   string    $text           Display text
     * @param   string    $apiRef         API reference
     * @param   string    $version        Bible version
     * @param   Registry  $params         Params
     * @param   ?object   $elementConfig  Per-element config
     *
     * @return  string
     */
    private function callWrapTooltip(
        string $text,
        string $apiRef,
        string $version,
        Registry $params,
        ?object $elementConfig = null
    ): string {
        $listing = new Cwmlisting();
        $method  = new \ReflectionMethod($listing, 'wrapScriptureTooltip');

        return $method->invoke($listing, $text, $apiRef, $version, $params, $elementConfig);
    }

    /**
     * Test buildApiReference with single verse
     *
     * @return void
     */
    public function testBuildApiReferenceSingleVerse(): void
    {
        $result = $this->callBuildApiReference('Genesis', 1, 0, 1, 0);

        $this->assertSame('Genesis+1:1', $result);
    }

    /**
     * Test buildApiReference with verse range in same chapter
     *
     * @return void
     */
    public function testBuildApiReferenceSameChapterRange(): void
    {
        $result = $this->callBuildApiReference('Luke', 7, 7, 36, 38);

        $this->assertSame('Luke+7:36-38', $result);
    }

    /**
     * Test buildApiReference with cross-chapter range
     *
     * @return void
     */
    public function testBuildApiReferenceCrossChapter(): void
    {
        $result = $this->callBuildApiReference('Luke', 1, 2, 20, 5);

        $this->assertSame('Luke+1:20-2:5', $result);
    }

    /**
     * Test buildApiReference with chapters only
     *
     * @return void
     */
    public function testBuildApiReferenceChaptersOnly(): void
    {
        $result = $this->callBuildApiReference('Romans', 5, 8, 0, 0);

        $this->assertSame('Romans+5-8', $result);
    }

    /**
     * Test buildApiReference with single chapter
     *
     * @return void
     */
    public function testBuildApiReferenceSingleChapter(): void
    {
        $result = $this->callBuildApiReference('John', 3, 0, 0, 0);

        $this->assertSame('John+3', $result);
    }

    /**
     * Test buildApiReference returns empty for zero chapter
     *
     * @return void
     */
    public function testBuildApiReferenceZeroChapter(): void
    {
        $result = $this->callBuildApiReference('Genesis', 0, 0, 0, 0);

        $this->assertSame('', $result);
    }

    /**
     * Test wrapScriptureTooltip wraps when enabled via per-element config
     *
     * @return void
     */
    public function testWrapTooltipEnabled(): void
    {
        $params        = new Registry();
        $elementConfig = (object) ['show_tooltip' => '1'];
        $result        = $this->callWrapTooltip('Luke 7:36-38 NKJV', 'Luke+7:36-38', 'nkjv', $params, $elementConfig);

        $this->assertStringContainsString('proclaim-scripture-ref', $result);
        $this->assertStringContainsString('data-scripture-ref="Luke+7:36-38"', $result);
        $this->assertStringContainsString('data-bible-version="nkjv"', $result);
        $this->assertStringContainsString('Luke 7:36-38 NKJV', $result);
        // Must use <span> (not <a>) to avoid content plugin conflicts
        $this->assertStringStartsWith('<span ', $result);
        $this->assertStringEndsWith('</span>', $result);
    }

    /**
     * Test wrapScriptureTooltip returns plain text when disabled via per-element config
     *
     * @return void
     */
    public function testWrapTooltipDisabledByElement(): void
    {
        $params        = new Registry();
        $elementConfig = (object) ['show_tooltip' => '0'];
        $result        = $this->callWrapTooltip('Luke 7:36-38', 'Luke+7:36-38', 'nkjv', $params, $elementConfig);

        $this->assertSame('Luke 7:36-38', $result);
        $this->assertStringNotContainsString('proclaim-scripture-ref', $result);
    }

    /**
     * Test wrapScriptureTooltip defaults to off when no element config
     *
     * @return void
     */
    public function testWrapTooltipDefaultsToOff(): void
    {
        $params = new Registry();
        $result = $this->callWrapTooltip('Luke 7:36-38', 'Luke+7:36-38', 'nkjv', $params);

        $this->assertSame('Luke 7:36-38', $result);
        $this->assertStringNotContainsString('proclaim-scripture-ref', $result);
    }

    /**
     * Test wrapScriptureTooltip returns plain text when no API reference
     *
     * @return void
     */
    public function testWrapTooltipEmptyRef(): void
    {
        $params        = new Registry();
        $elementConfig = (object) ['show_tooltip' => '1'];
        $result        = $this->callWrapTooltip('Some Book', '', 'kjv', $params, $elementConfig);

        $this->assertSame('Some Book', $result);
    }

    /**
     * Test wrapScriptureTooltip returns empty for empty text
     *
     * @return void
     */
    public function testWrapTooltipEmptyText(): void
    {
        $params        = new Registry();
        $elementConfig = (object) ['show_tooltip' => '1'];
        $result        = $this->callWrapTooltip('', 'Gen+1:1', 'kjv', $params, $elementConfig);

        $this->assertSame('', $result);
    }

    /**
     * Test wrapScriptureTooltip defaults version to kjv
     *
     * @return void
     */
    public function testWrapTooltipDefaultsVersion(): void
    {
        $params        = new Registry();
        $elementConfig = (object) ['show_tooltip' => '1'];
        $result        = $this->callWrapTooltip('Genesis 1:1', 'Genesis+1:1', '', $params, $elementConfig);

        $this->assertStringContainsString('data-bible-version="kjv"', $result);
    }

    /**
     * Test wrapScriptureTooltip escapes HTML in reference
     *
     * @return void
     */
    public function testWrapTooltipEscapesHtml(): void
    {
        $params        = new Registry();
        $elementConfig = (object) ['show_tooltip' => '1'];
        $result        = $this->callWrapTooltip('Test', 'Book"<script>', 'kjv', $params, $elementConfig);

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }
}
