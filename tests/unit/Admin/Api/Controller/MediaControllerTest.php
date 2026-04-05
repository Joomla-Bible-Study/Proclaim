<?php

/**
 * Unit tests for MediaController (REST API)
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Api\Controller\MediaController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\MVC\Controller\ApiController;

/**
 * Test class for MediaController
 *
 * @since  10.3.0
 */
class MediaControllerTest extends ProclaimTestCase
{
    public function testExtendsApiController(): void
    {
        $this->assertTrue(
            is_subclass_of(MediaController::class, ApiController::class),
            'MediaController must extend Joomla ApiController'
        );
    }

    public function testContentType(): void
    {
        $ref = new \ReflectionClass(MediaController::class);
        $prop = $ref->getProperty('contentType');
        $this->assertEquals('media', $prop->getDefaultValue());
    }

    public function testDefaultView(): void
    {
        $ref = new \ReflectionClass(MediaController::class);
        $prop = $ref->getProperty('default_view');
        $this->assertEquals('media', $prop->getDefaultValue());
    }

    public function testDisplayListMethodExists(): void
    {
        $ref = new \ReflectionMethod(MediaController::class, 'displayList');
        $this->assertTrue($ref->isPublic());
    }

    public function testGetModelNameMapping(): void
    {
        $ref = new \ReflectionMethod(MediaController::class, 'getModel');
        $source = file_get_contents($ref->getFileName());

        $this->assertStringContainsString("'media'      => 'Cwmmediafiles'", $source);
        $this->assertStringContainsString("'medium'     => 'Cwmmediafile'", $source);
        $this->assertStringContainsString("'mediafile'  => 'Cwmmediafile'", $source);
        $this->assertStringContainsString("'mediafiles' => 'Cwmmediafiles'", $source);
    }

    public function testDisplayListSetsPublishedFilter(): void
    {
        $ref = new \ReflectionMethod(MediaController::class, 'displayList');
        $source = file_get_contents($ref->getFileName());

        $this->assertStringContainsString("'filter.published', [1, 2]", $source);
    }
}
