<?php

/**
 * Unit tests for IntegrationToggleField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\IntegrationToggleField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for IntegrationToggleField
 *
 * #[CoversClass(IntegrationToggleField::class)]
 * @since  10.2.0
 */
class IntegrationToggleFieldTest extends ProclaimTestCase
{
    /**
     * Test setup method signature
     *
     * @return void
     * #[CoversClass(IntegrationToggleField::class)]::setup
     */
    public function testSetupMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(IntegrationToggleField::class, 'setup');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('element', $params[0]->getName());
        $this->assertParamTypeName('SimpleXMLElement', $params[0]);

        $this->assertEquals('value', $params[1]->getName());
        $this->assertParamTypeName('mixed', $params[1]);

        $this->assertEquals('group', $params[2]->getName());
        $this->assertParamTypeName('string', $params[2]);
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test isExtensionInstalled method signature
     *
     * @return void
     * #[CoversClass(IntegrationToggleField::class)]::isExtensionInstalled
     */
    public function testIsExtensionInstalledMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(IntegrationToggleField::class, 'isExtensionInstalled');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('bool', $reflection);
    }

    /**
     * Test getInput method signature
     *
     * @return void
     * #[CoversClass(IntegrationToggleField::class)]::getInput
     */
    public function testGetInputMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(IntegrationToggleField::class, 'getInput');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);
    }

    /**
     * Test getLabel method signature
     *
     * @return void
     * #[CoversClass(IntegrationToggleField::class)]::getLabel
     */
    public function testGetLabelMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(IntegrationToggleField::class, 'getLabel');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);
    }
}
