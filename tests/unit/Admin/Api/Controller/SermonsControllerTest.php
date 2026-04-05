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
use Joomla\CMS\MVC\Controller\ApiController;

/**
 * Test class for SermonsController
 *
 * @since  10.3.0
 */
class SermonsControllerTest extends ProclaimTestCase
{
    /**
     * Test that the class extends ApiController
     *
     * @return void
     */
    public function testExtendsApiController(): void
    {
        $this->assertTrue(
            is_subclass_of(SermonsController::class, ApiController::class),
            'SermonsController must extend Joomla ApiController'
        );
    }

    /**
     * Test contentType property is set correctly
     *
     * @return void
     */
    public function testContentType(): void
    {
        $ref = new \ReflectionClass(SermonsController::class);
        $prop = $ref->getProperty('contentType');
        $this->assertEquals('sermons', $prop->getDefaultValue());
    }

    /**
     * Test default_view property is set correctly
     *
     * @return void
     */
    public function testDefaultView(): void
    {
        $ref = new \ReflectionClass(SermonsController::class);
        $prop = $ref->getProperty('default_view');
        $this->assertEquals('sermons', $prop->getDefaultValue());
    }

    /**
     * Test displayList method exists and is public
     *
     * @return void
     */
    public function testDisplayListMethodExists(): void
    {
        $ref = new \ReflectionMethod(SermonsController::class, 'displayList');
        $this->assertTrue($ref->isPublic(), 'displayList() should be public');
    }

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
        $ref = new \ReflectionMethod(SermonsController::class, 'getModel');
        $source = file_get_contents($ref->getFileName());

        $this->assertStringContainsString("'sermons' => 'Cwmmessages'", $source);
        $this->assertStringContainsString("'sermon'  => 'Cwmmessage'", $source);
    }

    /**
     * Test displayList sets published filter to include published and archived
     *
     * @return void
     */
    public function testDisplayListSetsPublishedFilter(): void
    {
        $ref = new \ReflectionMethod(SermonsController::class, 'displayList');
        $source = file_get_contents($ref->getFileName());

        // Verify the filter sets published to [1, 2] (published + archived)
        $this->assertStringContainsString("'filter.published', [1, 2]", $source);
    }
}
