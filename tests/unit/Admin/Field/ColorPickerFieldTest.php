<?php

/**
 * Unit tests for ColorPickerField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\ColorPickerField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for ColorPickerField
 *
 * #[CoversClass(ColorPickerField::class)]
 * @since  10.2.0
 */
class ColorPickerFieldTest extends ProclaimTestCase
{
    /**
     * Test getInput method signature
     *
     * @return void
     * #[CoversClass(ColorPickerField::class)]::getInput
     */
    public function testGetInputMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(ColorPickerField::class, 'getInput');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);
    }

    /**
     * Test buildPreviewSwatch method signature
     *
     * @return void
     * #[CoversClass(ColorPickerField::class)]::buildPreviewSwatch
     */
    public function testBuildPreviewSwatchMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(ColorPickerField::class, 'buildPreviewSwatch');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('value', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);

        $this->assertEquals('isNamedColor', $params[1]->getName());
        $this->assertParamTypeName('bool', $params[1]);
    }

    /**
     * Test buildColorSwatch method signature
     *
     * @return void
     * #[CoversClass(ColorPickerField::class)]::buildColorSwatch
     */
    public function testBuildColorSwatchMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(ColorPickerField::class, 'buildColorSwatch');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('name', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);

        $this->assertEquals('hex', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);

        $this->assertEquals('isSelected', $params[2]->getName());
        $this->assertParamTypeName('bool', $params[2]);
    }

    /**
     * Test buildStyles method signature
     *
     * @return void
     * #[CoversClass(ColorPickerField::class)]::buildStyles
     */
    public function testBuildStylesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(ColorPickerField::class, 'buildStyles');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);
    }

    /**
     * Test buildJavaScript method signature
     *
     * @return void
     * #[CoversClass(ColorPickerField::class)]::buildJavaScript
     */
    public function testBuildJavaScriptMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(ColorPickerField::class, 'buildJavaScript');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);
    }
}
