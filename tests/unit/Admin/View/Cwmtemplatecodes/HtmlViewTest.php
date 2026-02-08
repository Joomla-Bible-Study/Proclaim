<?php

/**
 * Unit tests for Cwmtemplatecodes HtmlView
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\View\Cwmtemplatecodes;

use CWM\Component\Proclaim\Administrator\View\Cwmtemplatecodes\HtmlView;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmtemplatecodes HtmlView
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
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('tpl', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertTrue($params[0]->isOptional());
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
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test getSortFields method signature
     *
     * @return void
     * #[CoversClass(HtmlView::class)]::getSortFields
     */
    public function testGetSortFieldsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(HtmlView::class, 'getSortFields');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('array', $reflection);
    }
}
