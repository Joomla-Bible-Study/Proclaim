<?php

/**
 * Unit test for IconTypeField's getInput() value-normalization ordering
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\IconTypeField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test: getInput() snapshotted the field's layout data via
 * getLayoutData() BEFORE normalizing a legacy (pre-FA6) stored icon class, so
 * the stale value was what got rendered -- a legacy icon selection never
 * showed as selected on the edit screen, even though $this->value itself was
 * correctly normalized for every other purpose afterward.
 *
 * A live render isn't practical in this harness (getLayoutData()/getOptions()
 * need a bound Form and a real Cwmmedia icon catalog), so this follows the
 * file's own established pattern for an ordering bug: assert the call order
 * directly in the method body, as CwmactionlogFailOpenTest::testModelIsChecked
 * BeforeUse() already does for the same class of bug.
 *
 * @since  __DEPLOY_VERSION__
 */
class IconTypeFieldOrderingTest extends ProclaimTestCase
{
    public function testValueIsNormalizedBeforeLayoutDataIsCollected(): void
    {
        $ref   = new \ReflectionMethod(IconTypeField::class, 'getInput');
        $lines = file((string) $ref->getFileName());
        $body  = implode(
            '',
            \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1)
        );

        $normalizeAt    = strpos($body, 'normalizeIconClass');
        $layoutDataAt   = strpos($body, 'getLayoutData()');

        $this->assertNotFalse($normalizeAt, 'Expected a normalizeIconClass() call in getInput()');
        $this->assertNotFalse($layoutDataAt, 'Expected a getLayoutData() call in getInput()');
        $this->assertLessThan(
            $layoutDataAt,
            $normalizeAt,
            'normalizeIconClass() must run before getLayoutData() snapshots $this->value, or a legacy '
                . 'icon selection renders as unselected on the edit screen'
        );
    }
}
