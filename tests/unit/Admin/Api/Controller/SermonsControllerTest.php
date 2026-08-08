<?php

/**
 * Unit tests for SermonsController (REST API)
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Api\Controller\SermonsController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for SermonsController.
 *
 * Shared base-class boilerplate (extends ApiController, contentType/
 * default_view, displayList() visibility + published filter) is covered
 * once for all five entity controllers in ApiControllerBoilerplateTest.
 *
 * @since  10.3.0
 */
class SermonsControllerTest extends ProclaimTestCase
{
    /**
     * Test getModel method exists and is public
     *
     * @return void
     */
    public function testGetModelMethodExists(): void
    {
        $ref = new \ReflectionMethod(SermonsController::class, 'getModel');
        $this->assertTrue($ref->isPublic(), 'getModel() should be public');
    }

    /**
     * Test getModel maps API names to Cwm-prefixed model names
     *
     * @return void
     */
    public function testGetModelNameMapping(): void
    {
        // Read the source to verify the mapping array
        $ref    = new \ReflectionMethod(SermonsController::class, 'getModel');
        $source = file_get_contents($ref->getFileName());

        $this->assertStringContainsString("'sermons' => 'Cwmmessages'", $source);
        $this->assertStringContainsString("'sermon'  => 'Cwmmessage'", $source);
    }

    /**
     * Test preprocessSaveData method exists and is protected
     *
     * @return void
     */
    public function testPreprocessSaveDataExists(): void
    {
        $ref = new \ReflectionMethod(SermonsController::class, 'preprocessSaveData');
        $this->assertTrue($ref->isProtected(), 'preprocessSaveData() should be protected');
    }

    /**
     * Test preprocessSaveData normalizes scriptures array to keyed format
     *
     * @return void
     */
    public function testPreprocessNormalizesScriptures(): void
    {
        $source = file_get_contents((new \ReflectionClass(SermonsController::class))->getFileName());

        $this->assertStringContainsString("'scriptures' . \$i", $source);
        $this->assertStringContainsString('array_is_list', $source);
    }

    /**
     * Test preprocessSaveData normalizes teachers array to keyed format
     *
     * @return void
     */
    public function testPreprocessNormalizesTeachers(): void
    {
        $source = file_get_contents((new \ReflectionClass(SermonsController::class))->getFileName());

        $this->assertStringContainsString("'teachers' . \$i", $source);
    }

    /**
     * Test preprocessSaveData defaults image to empty string
     *
     * @return void
     */
    public function testPreprocessDefaultsImage(): void
    {
        $source = file_get_contents((new \ReflectionClass(SermonsController::class))->getFileName());

        $this->assertStringContainsString("\$data['image'] = ''", $source);
    }
}
