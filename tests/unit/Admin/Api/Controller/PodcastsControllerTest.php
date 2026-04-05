<?php

/**
 * Unit tests for PodcastsController (REST API)
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Api\Controller\PodcastsController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\MVC\Controller\ApiController;

/**
 * Test class for PodcastsController
 *
 * @since  10.3.0
 */
class PodcastsControllerTest extends ProclaimTestCase
{
    public function testExtendsApiController(): void
    {
        $this->assertTrue(
            is_subclass_of(PodcastsController::class, ApiController::class),
            'PodcastsController must extend Joomla ApiController'
        );
    }

    public function testContentType(): void
    {
        $ref = new \ReflectionClass(PodcastsController::class);
        $prop = $ref->getProperty('contentType');
        $this->assertEquals('podcasts', $prop->getDefaultValue());
    }

    public function testDefaultView(): void
    {
        $ref = new \ReflectionClass(PodcastsController::class);
        $prop = $ref->getProperty('default_view');
        $this->assertEquals('podcasts', $prop->getDefaultValue());
    }

    public function testDisplayListMethodExists(): void
    {
        $ref = new \ReflectionMethod(PodcastsController::class, 'displayList');
        $this->assertTrue($ref->isPublic());
    }

    public function testGetModelNameMapping(): void
    {
        $ref = new \ReflectionMethod(PodcastsController::class, 'getModel');
        $source = file_get_contents($ref->getFileName());

        $this->assertStringContainsString("'podcasts' => 'Cwmpodcasts'", $source);
        $this->assertStringContainsString("'podcast'  => 'Cwmpodcast'", $source);
    }

    public function testDisplayListSetsPublishedFilter(): void
    {
        $ref = new \ReflectionMethod(PodcastsController::class, 'displayList');
        $source = file_get_contents($ref->getFileName());

        $this->assertStringContainsString("'filter.published', [1, 2]", $source);
    }
}
