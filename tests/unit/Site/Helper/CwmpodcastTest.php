<?php

/**
 * Unit tests for Cwmpodcast
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for Cwmpodcast
 *
 * @since  10.0.0
 */
class CwmpodcastTest extends ProclaimTestCase
{
    /**
     * Test class file exists
     *
     * @return void
     */
    public function testClassFileExists(): void
    {
        $filePath = JPATH_ROOT . '/site/src/Helper/Cwmpodcast.php';
        $this->assertFileExists($filePath);
    }

    /**
     * Test class has correct namespace
     *
     * @return void
     */
    public function testClassHasCorrectNamespace(): void
    {
        $filePath = JPATH_ROOT . '/site/src/Helper/Cwmpodcast.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString(
            'namespace CWM\Component\Proclaim\Site\Helper;',
            $content
        );
    }

    /**
     * Test makePodcasts method exists
     *
     * @return void
     */
    public function testMakePodcastsMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmpodcast::class, 'makePodcasts'));
    }

    /**
     * Test makePodcasts method signature
     *
     * @return void
     */
    public function testMakePodcastsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmpodcast::class, 'makePodcasts');

        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);
    }

    /**
     * Test getEpisodes method exists
     *
     * @return void
     */
    public function testGetEpisodesMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmpodcast::class, 'getEpisodes'));
    }

    /**
     * Test class can be instantiated
     *
     * @return void
     */
    public function testClassCanBeInstantiated(): void
    {
        $reflection = new \ReflectionClass(Cwmpodcast::class);
        $this->assertTrue($reflection->isInstantiable());
    }
}
