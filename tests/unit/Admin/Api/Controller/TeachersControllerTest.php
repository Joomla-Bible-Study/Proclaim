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

/**
 * Test class for TeachersController.
 *
 * Shared base-class boilerplate (extends ApiController, contentType/
 * default_view, displayList() visibility + published filter) is covered
 * once for all five entity controllers in ApiControllerBoilerplateTest.
 *
 * @since  10.3.0
 */
class TeachersControllerTest extends ProclaimTestCase
{
    public function testGetModelNameMapping(): void
    {
        $ref    = new \ReflectionMethod(TeachersController::class, 'getModel');
        $source = file_get_contents($ref->getFileName());

        $this->assertStringContainsString("'teachers' => 'Cwmteachers'", $source);
        $this->assertStringContainsString("'teacher'  => 'Cwmteacher'", $source);
    }

    public function testPreprocessSaveDataExists(): void
    {
        $ref = new \ReflectionMethod(TeachersController::class, 'preprocessSaveData');
        $this->assertTrue($ref->isProtected());
    }

    public function testPreprocessDefaultsImage(): void
    {
        $source = file_get_contents((new \ReflectionClass(TeachersController::class))->getFileName());

        $this->assertStringContainsString("\$data['image'] = ''", $source);
    }
}
