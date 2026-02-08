<?php

/**
 * Unit tests for GetBibleProvider
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Bible\Provider;

use CWM\Component\Proclaim\Site\Bible\Provider\GetBibleProvider;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for GetBibleProvider
 *
 * @since  10.1.0
 */
class GetBibleProviderTest extends ProclaimTestCase
{
    /**
     * @var GetBibleProvider
     */
    private GetBibleProvider $provider;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new GetBibleProvider();
    }

    /**
     * Test provider properties
     *
     * @return void
     */
    public function testProviderProperties(): void
    {
        $this->assertSame('getbible', $this->provider->getName());
        $this->assertTrue($this->provider->returnsText());
        $this->assertFalse($this->provider->isOfflineCapable());
    }

    /**
     * Test getPassage method signature
     *
     * @return void
     */
    public function testGetPassageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(GetBibleProvider::class, 'getPassage');

        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName(
            'CWM\Component\Proclaim\Site\Bible\BiblePassageResult',
            $reflection
        );

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('reference', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertEquals('translation', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
    }

    /**
     * Test getAvailableTranslations method signature
     *
     * @return void
     */
    public function testGetAvailableTranslationsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(GetBibleProvider::class, 'getAvailableTranslations');

        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }
}
