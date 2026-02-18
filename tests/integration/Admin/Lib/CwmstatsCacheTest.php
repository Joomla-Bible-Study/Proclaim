<?php

/**
 * Integration tests for Cwmstats static cache behavior
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Cwmstats::class)]
class CwmstatsCacheTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetStaticCache(Cwmstats::class, 'cache', []);
    }

    public function testCachePropertyExists(): void
    {
        $ref = new \ReflectionClass(Cwmstats::class);
        $this->assertTrue($ref->hasProperty('cache'));

        $prop = $ref->getProperty('cache');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPrivate());
    }

    public function testCacheIsArray(): void
    {
        $ref  = new \ReflectionClass(Cwmstats::class);
        $prop = $ref->getProperty('cache');
        $prop->setAccessible(true);

        $this->assertIsArray($prop->getValue());
    }

    public function testResetCacheClearsAll(): void
    {
        // Seed the cache with a value
        $ref  = new \ReflectionClass(Cwmstats::class);
        $prop = $ref->getProperty('cache');
        $prop->setAccessible(true);
        $prop->setValue(null, ['topStudies' => 'cached-html']);

        // Verify it was set
        $this->assertNotEmpty($prop->getValue());

        // Reset using our helper
        $this->resetStaticCache(Cwmstats::class, 'cache', []);

        $this->assertEmpty($prop->getValue());
    }

    public function testTotalPlaysSignatureAcceptsInt(): void
    {
        $ref    = new \ReflectionMethod(Cwmstats::class, 'totalPlays');
        $params = $ref->getParameters();

        $this->assertCount(1, $params);
        $this->assertEquals('id', $params[0]->getName());
        $this->assertParamTypeName('int', $params[0]);
        $this->assertReturnTypeName('int', $ref);
    }

    public function testPersistentCacheHelperExists(): void
    {
        $ref = new \ReflectionClass(Cwmstats::class);
        $this->assertTrue($ref->hasMethod('getPersistentCache'), 'getPersistentCache() method must exist');

        $method = $ref->getMethod('getPersistentCache');
        $this->assertTrue($method->isStatic(), 'getPersistentCache() must be static');
        $this->assertTrue($method->isPrivate(), 'getPersistentCache() must be private');

        $params = $method->getParameters();
        $this->assertCount(1, $params, 'getPersistentCache() must accept one parameter (lifetime)');
        $this->assertEquals('lifetime', $params[0]->getName());
        $this->assertTrue($params[0]->isOptional(), 'lifetime must be optional');
        $this->assertEquals(900, $params[0]->getDefaultValue(), 'Default TTL must be 900 seconds');
    }

    public function testPersistentCacheReturnTypeIsCallbackController(): void
    {
        $ref    = new \ReflectionMethod(Cwmstats::class, 'getPersistentCache');
        $return = $ref->getReturnType();

        $this->assertNotNull($return, 'getPersistentCache() must declare a return type');
        $this->assertStringContainsString('CallbackController', (string) $return);
    }
}
