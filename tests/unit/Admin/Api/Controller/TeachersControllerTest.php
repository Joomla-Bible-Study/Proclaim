<?php

/**
 * Unit tests for TeachersController (REST API)
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Api\Controller\TeachersController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\MVC\Controller\ApiController;

/**
 * Test class for TeachersController
 *
 * @since  10.3.0
 */
class TeachersControllerTest extends ProclaimTestCase
{
    public function testExtendsApiController(): void
    {
        $this->assertTrue(
            is_subclass_of(TeachersController::class, ApiController::class),
            'TeachersController must extend Joomla ApiController'
        );
    }

    public function testContentType(): void
    {
        $ref = new \ReflectionClass(TeachersController::class);
        $prop = $ref->getProperty('contentType');
        $this->assertEquals('teachers', $prop->getDefaultValue());
    }

    public function testDefaultView(): void
    {
        $ref = new \ReflectionClass(TeachersController::class);
        $prop = $ref->getProperty('default_view');
        $this->assertEquals('teachers', $prop->getDefaultValue());
    }

    public function testDisplayListMethodExists(): void
    {
        $ref = new \ReflectionMethod(TeachersController::class, 'displayList');
        $this->assertTrue($ref->isPublic());
    }

    public function testGetModelNameMapping(): void
    {
        $ref = new \ReflectionMethod(TeachersController::class, 'getModel');
        $source = file_get_contents($ref->getFileName());

        $this->assertStringContainsString("'teachers' => 'Cwmteachers'", $source);
        $this->assertStringContainsString("'teacher'  => 'Cwmteacher'", $source);
    }

    public function testDisplayListSetsPublishedFilter(): void
    {
        $ref = new \ReflectionMethod(TeachersController::class, 'displayList');
        $source = file_get_contents($ref->getFileName());

        $this->assertStringContainsString("'filter.published', [1, 2]", $source);
    }
}
