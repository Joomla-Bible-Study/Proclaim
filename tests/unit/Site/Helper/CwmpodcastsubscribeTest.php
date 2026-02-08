<?php

/**
 * Unit tests for Cwmpodcastsubscribe Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmpodcastsubscribe;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmpodcastsubscribe helper
 *
 * #[CoversClass(Cwmpodcastsubscribe::class)]
 * @since  10.0.0
 */
class CwmpodcastsubscribeTest extends ProclaimTestCase
{
    /**
     * Test buildSubscribeTable method signature
     *
     * @return void
     * #[CoversClass(Cwmpodcastsubscribe::class)]::buildSubscribeTable
     */
    public function testBuildSubscribeTableMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmpodcastsubscribe::class, 'buildSubscribeTable');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('introtext', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test getPodcasts method signature
     *
     * @return void
     * #[CoversClass(Cwmpodcastsubscribe::class)]::getPodcasts
     */
    public function testGetPodcastsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmpodcastsubscribe::class, 'getPodcasts');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test buildStandardPodcast method signature
     *
     * @return void
     * #[CoversClass(Cwmpodcastsubscribe::class)]::buildStandardPodcast
     */
    public function testBuildStandardPodcastMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmpodcastsubscribe::class, 'buildStandardPodcast');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('podcast', $params[0]->getName());
        $this->assertParamTypeName('object', $params[0]);
    }

    /**
     * Test buildPodcastImage method signature
     *
     * @return void
     * #[CoversClass(Cwmpodcastsubscribe::class)]::buildPodcastImage
     */
    public function testBuildPodcastImageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmpodcastsubscribe::class, 'buildPodcastImage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);
        $this->assertTrue($reflection->getReturnType()->allowsNull());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('podcastimagefromdb', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());

        $this->assertEquals('words', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->allowsNull());
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test buildAlternatePodcast method signature
     *
     * @return void
     * #[CoversClass(Cwmpodcastsubscribe::class)]::buildAlternatePodcast
     */
    public function testBuildAlternatePodcastMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmpodcastsubscribe::class, 'buildAlternatePodcast');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('podcast', $params[0]->getName());
        $this->assertParamTypeName('object', $params[0]);
    }
}
