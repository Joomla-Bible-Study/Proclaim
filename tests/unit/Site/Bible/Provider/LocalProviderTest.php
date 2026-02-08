<?php

/**
 * Unit tests for LocalProvider
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Bible\Provider;

use CWM\Component\Proclaim\Site\Bible\AbstractBibleProvider;
use CWM\Component\Proclaim\Site\Bible\Provider\LocalProvider;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for LocalProvider
 *
 * @since  10.1.0
 */
class LocalProviderTest extends ProclaimTestCase
{
    /**
     * @var LocalProvider
     */
    private LocalProvider $provider;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new LocalProvider();
    }

    /**
     * Test provider properties
     *
     * @return void
     */
    public function testProviderProperties(): void
    {
        $this->assertSame('local', $this->provider->getName());
        $this->assertTrue($this->provider->returnsText());
        $this->assertTrue($this->provider->isOfflineCapable());
    }

    /**
     * Test proclaimToStandard mapping
     *
     * @return void
     */
    public function testProclaimToStandard(): void
    {
        // Genesis = 101 -> 1
        $this->assertSame(1, AbstractBibleProvider::proclaimToStandard(101));
        // Revelation = 166 -> 66
        $this->assertSame(66, AbstractBibleProvider::proclaimToStandard(166));
        // Psalms = 119 -> 19
        $this->assertSame(19, AbstractBibleProvider::proclaimToStandard(119));
        // Invalid
        $this->assertSame(0, AbstractBibleProvider::proclaimToStandard(999));
        $this->assertSame(0, AbstractBibleProvider::proclaimToStandard(0));
    }

    /**
     * Test standardToProclaim mapping
     *
     * @return void
     */
    public function testStandardToProclaim(): void
    {
        $this->assertSame(101, AbstractBibleProvider::standardToProclaim(1));
        $this->assertSame(166, AbstractBibleProvider::standardToProclaim(66));
        $this->assertSame(151, AbstractBibleProvider::standardToProclaim(51));
    }

    /**
     * Test getBookName
     *
     * @return void
     */
    public function testGetBookName(): void
    {
        $this->assertSame('Genesis', AbstractBibleProvider::getBookName(1));
        $this->assertSame('Revelation', AbstractBibleProvider::getBookName(66));
        $this->assertSame('Psalms', AbstractBibleProvider::getBookName(19));
        $this->assertSame('', AbstractBibleProvider::getBookName(0));
        $this->assertSame('', AbstractBibleProvider::getBookName(67));
    }

    /**
     * Test getPassage method signature
     *
     * @return void
     */
    public function testGetPassageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(LocalProvider::class, 'getPassage');

        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName(
            'CWM\Component\Proclaim\Site\Bible\BiblePassageResult',
            $reflection
        );

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('reference', $params[0]->getName());
        $this->assertEquals('translation', $params[1]->getName());
    }

    /**
     * Test parseReference method via reflection
     *
     * @return void
     */
    public function testParseReference(): void
    {
        $method = new \ReflectionMethod(LocalProvider::class, 'parseReference');
        $method->setAccessible(true);

        // Single verse
        $parsed = $method->invoke($this->provider, 'John+3:16');
        $this->assertNotNull($parsed);
        $this->assertSame(43, $parsed['book']); // John = 43
        $this->assertSame(3, $parsed['chapter_begin']);
        $this->assertSame(16, $parsed['verse_begin']);
        $this->assertSame(3, $parsed['chapter_end']);
        $this->assertSame(16, $parsed['verse_end']);

        // Verse range (same chapter)
        $parsed = $method->invoke($this->provider, 'Genesis+1:1-5');
        $this->assertNotNull($parsed);
        $this->assertSame(1, $parsed['book']); // Genesis = 1
        $this->assertSame(1, $parsed['chapter_begin']);
        $this->assertSame(1, $parsed['verse_begin']);
        $this->assertSame(1, $parsed['chapter_end']);
        $this->assertSame(5, $parsed['verse_end']);

        // Cross-chapter range
        $parsed = $method->invoke($this->provider, 'Psalms+23:1-24:3');
        $this->assertNotNull($parsed);
        $this->assertSame(19, $parsed['book']); // Psalms = 19
        $this->assertSame(23, $parsed['chapter_begin']);
        $this->assertSame(1, $parsed['verse_begin']);
        $this->assertSame(24, $parsed['chapter_end']);
        $this->assertSame(3, $parsed['verse_end']);

        // Whole chapter
        $parsed = $method->invoke($this->provider, 'Romans+8');
        $this->assertNotNull($parsed);
        $this->assertSame(45, $parsed['book']); // Romans = 45
        $this->assertSame(8, $parsed['chapter_begin']);
        $this->assertSame(0, $parsed['verse_begin']);

        // Invalid reference
        $parsed = $method->invoke($this->provider, 'not-a-reference');
        $this->assertNull($parsed);
    }

    /**
     * Test resolveBookNumber via reflection
     *
     * @return void
     */
    public function testResolveBookNumber(): void
    {
        $method = new \ReflectionMethod(LocalProvider::class, 'resolveBookNumber');
        $method->setAccessible(true);

        $this->assertSame(1, $method->invoke($this->provider, 'Genesis'));
        $this->assertSame(1, $method->invoke($this->provider, 'genesis'));
        $this->assertSame(66, $method->invoke($this->provider, 'Revelation'));
        $this->assertSame(19, $method->invoke($this->provider, 'Psalms'));

        // Abbreviations
        $this->assertSame(1, $method->invoke($this->provider, 'gen'));
        $this->assertSame(66, $method->invoke($this->provider, 'rev'));
        $this->assertSame(40, $method->invoke($this->provider, 'matt'));

        // Unknown
        $this->assertSame(0, $method->invoke($this->provider, 'Unknown Book'));
    }
}
