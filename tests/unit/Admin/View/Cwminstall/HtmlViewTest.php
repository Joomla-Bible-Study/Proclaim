<?php

/**
 * Unit tests for Cwminstall HtmlView
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\View\Cwminstall;

use CWM\Component\Proclaim\Administrator\View\Cwminstall\HtmlView;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwminstall HtmlView
 *
 * #[CoversClass(HtmlView::class)]
 * @since  10.0.0
 */
class HtmlViewTest extends ProclaimTestCase
{
    /**
     * Test display method signature
     *
     * @return void
     * #[CoversClass(HtmlView::class)]::display
     */
    public function testDisplayMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(HtmlView::class, 'display');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('tpl', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test installSetup method signature
     *
     * @return void
     * #[CoversClass(HtmlView::class)]::installSetup
     */
    public function testInstallSetupMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(HtmlView::class, 'installSetup');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }

    /**
     * Test addToolbar method signature
     *
     * @return void
     * #[CoversClass(HtmlView::class)]::addToolbar
     */
    public function testAddToolbarMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(HtmlView::class, 'addToolbar');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }
}
