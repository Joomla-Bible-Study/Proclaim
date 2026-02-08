<?php

/**
 * Unit tests for CustomField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\CustomField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CustomField
 *
 * #[CoversClass(CustomField::class)]
 * @since  10.2.0
 */
class CustomFieldTest extends ProclaimTestCase
{
    /**
     * Test getInput method signature
     *
     * @return void
     * #[CoversClass(CustomField::class)]::getInput
     */
    public function testGetInputMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CustomField::class, 'getInput');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);
    }

    /**
     * Test buildInputHtml method signature
     *
     * @return void
     * #[CoversClass(CustomField::class)]::buildInputHtml
     */
    public function testBuildInputHtmlMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CustomField::class, 'buildInputHtml');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('inputType', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
    }

    /**
     * Test buildButtonHtml method signature
     *
     * @return void
     * #[CoversClass(CustomField::class)]::buildButtonHtml
     */
    public function testBuildButtonHtmlMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CustomField::class, 'buildButtonHtml');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('modalId', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
    }

    /**
     * Test buildModalHtml method signature
     *
     * @return void
     * #[CoversClass(CustomField::class)]::buildModalHtml
     */
    public function testBuildModalHtmlMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CustomField::class, 'buildModalHtml');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('modalId', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);

        $this->assertEquals('codeset', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);

        $this->assertEquals('codes', $params[2]->getName());
        $this->assertParamTypeName('array', $params[2]);
    }

    /**
     * Test buildJavaScript method signature
     *
     * @return void
     * #[CoversClass(CustomField::class)]::buildJavaScript
     */
    public function testBuildJavaScriptMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CustomField::class, 'buildJavaScript');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);
    }
}
