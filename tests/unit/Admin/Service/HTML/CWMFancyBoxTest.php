<?php

/**
 * Unit tests for CWMFancyBox
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Service\HTML;

use CWM\Component\Proclaim\Administrator\Service\HTML\CWMFancyBox;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CWMFancyBox
 *
 * #[CoversClass(CWMFancyBox::class)]
 * @since  10.0.0
 */
class CWMFancyBoxTest extends ProclaimTestCase
{
    /**
     * Reset static properties between tests
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Reset static properties using reflection
        $reflection = new \ReflectionClass(CWMFancyBox::class);

        if ($reflection->hasProperty('loaded')) {
            $prop = $reflection->getProperty('loaded');
            $prop->setAccessible(true);
            $prop->setValue(null, []);
        }

        parent::tearDown();
    }

    /**
     * Test framework method signature
     *
     * @return void
     * #[CoversClass(CWMFancyBox::class)]::framework
     */
    public function testFrameworkMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CWMFancyBox::class, 'framework');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('option', $params[0]->getName());
        $this->assertParamTypeName('bool', $params[0]);
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('mouseweel', $params[1]->getName());
        $this->assertParamTypeName('bool', $params[1]);
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test loadCss method signature
     *
     * @return void
     * #[CoversClass(CWMFancyBox::class)]::loadCss
     */
    public function testLoadCssMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CWMFancyBox::class, 'loadCss');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('option', $params[0]->getName());
        $this->assertParamTypeName('bool', $params[0]);
        $this->assertTrue($params[0]->isOptional());
    }
}
