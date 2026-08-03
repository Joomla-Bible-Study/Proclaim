<?php

/**
 * Unit tests for Modal/ServerTypeField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\Modal\ServerTypeField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for Modal/ServerTypeField
 *
 * @since  __DEPLOY_VERSION__
 */
class ServerTypeFieldTest extends ProclaimTestCase
{
    /**
     * Regression test for #1457: getInput() interpolated the reverse-lookup
     * display name ($text) and the raw field value ($value) directly into
     * HTML value="..." attributes with no escaping. Every sibling field in
     * Modal/ already htmlspecialchars()'s this exact pattern (or, since
     * #1465, no longer hand-rolls HTML at all) — ServerTypeField was the
     * one that dropped it.
     *
     * @return void
     */
    public function testGetInputEscapesInterpolatedValues(): void
    {
        $reflection = new \ReflectionMethod(ServerTypeField::class, 'getInput');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertMatchesRegularExpression(
            '/htmlspecialchars\(\$text/',
            $body,
            'getInput() must escape $text before interpolating into value="..." — see #1457'
        );

        $this->assertMatchesRegularExpression(
            '/htmlspecialchars\(\$value/',
            $body,
            'getInput() must escape $value before interpolating into value="..." — see #1457'
        );
    }
}
