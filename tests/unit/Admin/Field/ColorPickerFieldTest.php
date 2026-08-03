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

/**
 * Test class for ColorPickerField
 *
 * @since  __DEPLOY_VERSION__
 */
class ColorPickerFieldTest extends ProclaimTestCase
{
    /**
     * Regression test for #1458: when the stored value isn't a recognized
     * CSS named color, $hexValue was derived from the raw field value and
     * interpolated unescaped into two value="..." attributes (the
     * <input type="color"> and the hex text input).
     *
     * @return void
     */
    public function testHexValueIsEscapedBeforeInterpolation(): void
    {
        $reflection = new \ReflectionMethod(ColorPickerField::class, 'getInput');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertMatchesRegularExpression(
            '/htmlspecialchars\(\$hexValue/',
            $body,
            'getInput() must escape $hexValue before interpolating into value="..." — see #1458'
        );
    }
}
