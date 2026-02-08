<?php

/**
 * Unit tests for LayoutEditorField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\LayoutEditorField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for LayoutEditorField
 *
 * #[CoversClass(LayoutEditorField::class)]
 * @since  10.1.0
 */
class LayoutEditorFieldTest extends ProclaimTestCase
{
    /**
     * Test getInput method signature
     *
     * @return void
     * #[CoversClass(LayoutEditorField::class)]::getInput
     */
    public function testGetInputMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(LayoutEditorField::class, 'getInput');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);
    }

    /**
     * Test loadLanguageStrings method signature
     *
     * @return void
     * #[CoversClass(LayoutEditorField::class)]::loadLanguageStrings
     */
    public function testLoadLanguageStringsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(LayoutEditorField::class, 'loadLanguageStrings');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test getLayoutParams method signature
     *
     * @return void
     * #[CoversClass(LayoutEditorField::class)]::getLayoutParams
     */
    public function testGetLayoutParamsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(LayoutEditorField::class, 'getLayoutParams');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test buildElementDefinitions method signature
     *
     * @return void
     * #[CoversClass(LayoutEditorField::class)]::buildElementDefinitions
     */
    public function testBuildElementDefinitionsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(LayoutEditorField::class, 'buildElementDefinitions');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('array', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('context', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
    }

    /**
     * Test setJavaScriptOptions method signature
     *
     * @return void
     * #[CoversClass(LayoutEditorField::class)]::setJavaScriptOptions
     */
    public function testSetJavaScriptOptionsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(LayoutEditorField::class, 'setJavaScriptOptions');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('elementDefs', $params[0]->getName());
        $this->assertParamTypeName('array', $params[0]);

        $this->assertEquals('params', $params[1]->getName());
        $this->assertParamTypeName('array', $params[1]);
    }
}
