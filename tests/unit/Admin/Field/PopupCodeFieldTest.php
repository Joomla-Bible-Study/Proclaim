<?php

/**
 * Unit tests for PopupCodeField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\PopupCodeField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for PopupCodeField
 *
 * #[CoversClass(PopupCodeField::class)]
 * @since  10.2.0
 */
class PopupCodeFieldTest extends ProclaimTestCase
{
    /**
     * Test getInput method signature
     *
     * @return void
     * #[CoversClass(PopupCodeField::class)]::getInput
     */
    public function testGetInputMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(PopupCodeField::class, 'getInput');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);
    }
}
