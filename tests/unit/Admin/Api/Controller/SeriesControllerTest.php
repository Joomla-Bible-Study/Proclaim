<?php

/**
 * Unit tests for SeriesController (REST API)
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Api\Controller\SeriesController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for SeriesController.
 *
 * Shared base-class boilerplate (extends ApiController, contentType/
 * default_view, displayList() visibility + published filter) is covered
 * once for all five entity controllers in ApiControllerBoilerplateTest.
 *
 * @since  10.3.0
 */
class SeriesControllerTest extends ProclaimTestCase
{
    public function testDisplayItemMethodExists(): void
    {
        $ref = new \ReflectionMethod(SeriesController::class, 'displayItem');
        $this->assertTrue($ref->isPublic());
    }

    /**
     * Series is special: singular and plural are both "series", so the
     * controller tracks context via $itemModelRequested flag.
     */
    public function testItemModelRequestedFlagExists(): void
    {
        $ref = new \ReflectionClass(SeriesController::class);
        $this->assertTrue($ref->hasProperty('itemModelRequested'));

        $prop = $ref->getProperty('itemModelRequested');
        $this->assertEquals(false, $prop->getDefaultValue());
    }

    public function testGetModelNameMapping(): void
    {
        $ref    = new \ReflectionMethod(SeriesController::class, 'getModel');
        $source = file_get_contents($ref->getFileName());

        // List context: series → Cwmseries
        $this->assertStringContainsString("'Cwmseries'", $source);
        // Item context: series → Cwmserie (singular)
        $this->assertStringContainsString("'Cwmserie'", $source);
    }

    /**
     * Write operations (add/edit/delete) must set itemModelRequested so
     * getModel() returns the singular CwmserieModel. Each check slices the
     * specific method's body -- asserting the override merely EXISTS (the
     * prior version) stayed green if the flag assignment was deleted from
     * inside it, which breaks every series write.
     *
     * @param   string  $method  Overridden write method name
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('writeMethodProvider')]
    public function testWriteMethodSetsItemModelFlag(string $method): void
    {
        $ref = new \ReflectionMethod(SeriesController::class, $method);
        $this->assertEquals(SeriesController::class, $ref->getDeclaringClass()->getName());

        $lines = file($ref->getFileName());
        $body  = implode(
            '',
            \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1)
        );

        $this->assertStringContainsString(
            '$this->itemModelRequested = true;',
            $body,
            $method . '() must set itemModelRequested so getModel() resolves the singular CwmserieModel'
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public static function writeMethodProvider(): array
    {
        return [
            'add'    => ['add'],
            'edit'   => ['edit'],
            'delete' => ['delete'],
        ];
    }
}
