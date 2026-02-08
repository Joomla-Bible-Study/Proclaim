<?php

/**
 * Unit tests for Cwmsermon HtmlView
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\View\Cwmsermon;

use CWM\Component\Proclaim\Site\View\Cwmsermon\HtmlView;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmsermon HtmlView
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
     * Test displayPageBrake method signature
     *
     * @return void
     * #[CoversClass(HtmlView::class)]::displayPageBrake
     */
    public function testDisplayPageBrakeMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(HtmlView::class, 'displayPageBrake');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('tpl', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
    }

    /**
     * Test prepareDocument method signature
     *
     * @return void
     * #[CoversClass(HtmlView::class)]::prepareDocument
     */
    public function testPrepareDocumentMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(HtmlView::class, 'prepareDocument');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('void', $reflection);
    }
}
