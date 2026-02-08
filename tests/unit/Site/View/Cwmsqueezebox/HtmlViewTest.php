<?php

/**
 * Unit tests for Cwmsqueezebox HtmlView
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\View\Cwmsqueezebox;

use CWM\Component\Proclaim\Site\View\Cwmsqueezebox\HtmlView;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmsqueezebox HtmlView
 *
 * #[CoversClass(HtmlView::class)]
 * @since  10.0.0
 */
class HtmlViewTest extends ProclaimTestCase
{
    /**
     * Test class exists
     *
     * @return void
     */
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(HtmlView::class));
    }
}
