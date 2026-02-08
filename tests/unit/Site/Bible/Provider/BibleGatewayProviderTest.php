<?php

/**
 * Unit tests for BibleGatewayProvider
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Bible\Provider;

use CWM\Component\Proclaim\Site\Bible\Provider\BibleGatewayProvider;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for BibleGatewayProvider
 *
 * @since  10.1.0
 */
class BibleGatewayProviderTest extends ProclaimTestCase
{
    /**
     * @var BibleGatewayProvider
     */
    private BibleGatewayProvider $provider;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new BibleGatewayProvider();
    }

    /**
     * Test getPassage returns iframe result
     *
     * @return void
     */
    public function testGetPassageReturnsIframeResult(): void
    {
        $result = $this->provider->getPassage('John+3:16', '51');

        $this->assertTrue($result->isIframe);
        $this->assertFalse($result->hasText());
        $this->assertStringContainsString('biblegateway.com', $result->iframeUrl);
        $this->assertStringContainsString('John+3:16', $result->iframeUrl);
        $this->assertStringContainsString('version=51', $result->iframeUrl);
        $this->assertStringContainsString('interface=print', $result->iframeUrl);
    }

    /**
     * Test provider properties
     *
     * @return void
     */
    public function testProviderProperties(): void
    {
        $this->assertSame('biblegateway', $this->provider->getName());
        $this->assertFalse($this->provider->returnsText());
        $this->assertFalse($this->provider->isOfflineCapable());
    }

    /**
     * Test getAvailableTranslations returns empty array
     *
     * @return void
     */
    public function testGetAvailableTranslationsReturnsEmpty(): void
    {
        $this->assertSame([], $this->provider->getAvailableTranslations());
    }

    /**
     * Test VERSION_MAP contains common versions
     *
     * @return void
     */
    public function testVersionMapContainsCommonVersions(): void
    {
        $this->assertSame('kjv', BibleGatewayProvider::VERSION_MAP[9]);
        $this->assertSame('nlt', BibleGatewayProvider::VERSION_MAP[51]);
        $this->assertSame('esv', BibleGatewayProvider::VERSION_MAP[47]);
        $this->assertSame('niv', BibleGatewayProvider::VERSION_MAP[31]);
    }

    /**
     * Test getPassage with different reference formats
     *
     * @return void
     */
    public function testGetPassageWithDifferentReferences(): void
    {
        $result1 = $this->provider->getPassage('Genesis+1:1-5', '9');
        $this->assertStringContainsString('Genesis+1:1-5', $result1->iframeUrl);

        $result2 = $this->provider->getPassage('Psalms+23', '9');
        $this->assertStringContainsString('Psalms+23', $result2->iframeUrl);
    }
}
