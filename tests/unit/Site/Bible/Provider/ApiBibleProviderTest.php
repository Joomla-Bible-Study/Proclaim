<?php

/**
 * Unit tests for ApiBibleProvider
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Bible\Provider;

use CWM\Component\Proclaim\Site\Bible\Provider\ApiBibleProvider;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for ApiBibleProvider
 *
 * @since  10.1.0
 */
class ApiBibleProviderTest extends ProclaimTestCase
{
    /**
     * @var ApiBibleProvider
     */
    private ApiBibleProvider $provider;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new ApiBibleProvider('test-api-key');
    }

    /**
     * Test provider properties
     *
     * @return void
     */
    public function testProviderProperties(): void
    {
        $this->assertSame('api_bible', $this->provider->getName());
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
        $reflection = new \ReflectionMethod(ApiBibleProvider::class, 'getPassage');

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
        $reflection = new \ReflectionMethod(ApiBibleProvider::class, 'getAvailableTranslations');

        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test OSIS codes mapping has all 66 books
     *
     * @return void
     */
    public function testOsisCodesMapping(): void
    {
        $codes = ApiBibleProvider::getOsisCodes();

        $this->assertCount(66, $codes);
        $this->assertSame('GEN', $codes[1]);
        $this->assertSame('REV', $codes[66]);
        $this->assertSame('JHN', $codes[43]);
        $this->assertSame('PSA', $codes[19]);
        $this->assertSame('MAT', $codes[40]);
    }

    /**
     * Test buildPassageId with single verse
     *
     * @return void
     */
    public function testBuildPassageIdSingleVerse(): void
    {
        $result = $this->provider->buildPassageId('John+3:16');
        $this->assertSame('JHN.3.16', $result);
    }

    /**
     * Test buildPassageId with verse range
     *
     * @return void
     */
    public function testBuildPassageIdVerseRange(): void
    {
        $result = $this->provider->buildPassageId('John+3:16-18');
        $this->assertSame('JHN.3.16-JHN.3.18', $result);
    }

    /**
     * Test buildPassageId with Genesis
     *
     * @return void
     */
    public function testBuildPassageIdGenesis(): void
    {
        $result = $this->provider->buildPassageId('Genesis+1:1');
        $this->assertSame('GEN.1.1', $result);
    }

    /**
     * Test buildPassageId with numbered book
     *
     * @return void
     */
    public function testBuildPassageIdNumberedBook(): void
    {
        $result = $this->provider->buildPassageId('1 Corinthians+13:4-7');
        $this->assertSame('1CO.13.4-1CO.13.7', $result);
    }

    /**
     * Test buildPassageId with invalid reference
     *
     * @return void
     */
    public function testBuildPassageIdInvalid(): void
    {
        $result = $this->provider->buildPassageId('invalid-reference');
        $this->assertSame('', $result);
    }

    /**
     * Test buildPassageId with unknown book
     *
     * @return void
     */
    public function testBuildPassageIdUnknownBook(): void
    {
        $result = $this->provider->buildPassageId('FakeBook+1:1');
        $this->assertSame('', $result);
    }

    /**
     * Test constructor accepts empty API key
     *
     * @return void
     */
    public function testConstructorAcceptsEmptyKey(): void
    {
        $provider = new ApiBibleProvider();
        $this->assertSame('api_bible', $provider->getName());
    }
}
