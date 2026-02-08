<?php

/**
 * Unit tests for BiblePassageResult
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Bible;

use CWM\Component\Proclaim\Site\Bible\BiblePassageResult;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for BiblePassageResult value object
 *
 * @since  10.1.0
 */
class BiblePassageResultTest extends ProclaimTestCase
{
    /**
     * Test default constructor creates empty result
     *
     * @return void
     */
    public function testDefaultConstructor(): void
    {
        $result = new BiblePassageResult();

        $this->assertSame('', $result->text);
        $this->assertSame('', $result->reference);
        $this->assertSame('', $result->translation);
        $this->assertSame('', $result->copyright);
        $this->assertFalse($result->isHtml);
        $this->assertFalse($result->isIframe);
        $this->assertSame('', $result->iframeUrl);
    }

    /**
     * Test constructor with all parameters
     *
     * @return void
     */
    public function testConstructorWithParams(): void
    {
        $result = new BiblePassageResult(
            text: 'In the beginning...',
            reference: 'Genesis+1:1',
            translation: 'kjv',
            copyright: 'Public Domain',
            isHtml: true,
            isIframe: false,
            iframeUrl: ''
        );

        $this->assertSame('In the beginning...', $result->text);
        $this->assertSame('Genesis+1:1', $result->reference);
        $this->assertSame('kjv', $result->translation);
        $this->assertSame('Public Domain', $result->copyright);
        $this->assertTrue($result->isHtml);
        $this->assertFalse($result->isIframe);
    }

    /**
     * Test hasText returns true when text is present
     *
     * @return void
     */
    public function testHasTextReturnsTrueWithText(): void
    {
        $result = new BiblePassageResult(text: 'Some text');
        $this->assertTrue($result->hasText());
    }

    /**
     * Test hasText returns false when text is empty
     *
     * @return void
     */
    public function testHasTextReturnsFalseWithoutText(): void
    {
        $result = new BiblePassageResult();
        $this->assertFalse($result->hasText());
    }

    /**
     * Test iframe result
     *
     * @return void
     */
    public function testIframeResult(): void
    {
        $result = new BiblePassageResult(
            reference: 'John+3:16',
            translation: '51',
            isIframe: true,
            iframeUrl: 'https://www.biblegateway.com/passage/?search=John+3:16&version=51'
        );

        $this->assertTrue($result->isIframe);
        $this->assertFalse($result->hasText());
        $this->assertStringContainsString('biblegateway.com', $result->iframeUrl);
    }
}
