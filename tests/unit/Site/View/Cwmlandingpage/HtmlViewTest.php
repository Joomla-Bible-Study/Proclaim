<?php

/**
 * Unit tests for Cwmlandingpage HtmlView
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\View\Cwmlandingpage;

use CWM\Component\Proclaim\Site\View\Cwmlandingpage\HtmlView;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmlandingpage HtmlView
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
     * Test getShowHide method signature
     *
     * @return void
     * #[CoversClass(HtmlView::class)]::getShowHide
     */
    public function testGetShowHideMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(HtmlView::class, 'getShowHide');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('showIt', $params[0]->getName());
        // No type hint in method signature for showIt
        
        $this->assertEquals('showIt_phrase', $params[1]->getName());
        // No type hint in method signature for showIt_phrase
        
        $this->assertEquals('i', $params[2]->getName());
        // No type hint in method signature for i
    }
}
