<?php

/**
 * Unit test for LocationListField's getInput() auto-default ordering
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\LocationListField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test: for a multi-campus user, the dropdown path fell through to
 * ListField::getInput(), which calls collectLayoutData() (snapshotting
 * $this->value) BEFORE getOptions() -- but the auto-default-to-the-user's-
 * campus assignment is a side effect inside getOptions(). So the promised
 * auto-default was computed but never visibly pre-selected: collectLayoutData()
 * had already captured the stale (empty) value.
 *
 * A live render isn't practical in this harness (getOptions() needs a bound
 * Form, a real identity, and campus/location fixtures), so this follows the
 * file's own established pattern for an ordering bug: assert the call order
 * directly in the method body.
 *
 * @since  __DEPLOY_VERSION__
 */
class LocationListFieldOrderingTest extends ProclaimTestCase
{
    public function testOptionsAreResolvedBeforeLayoutDataIsCollected(): void
    {
        $ref   = new \ReflectionMethod(LocationListField::class, 'getInput');
        $lines = file((string) $ref->getFileName());
        $body  = implode(
            '',
            \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1)
        );

        $getOptionsAt     = strpos($body, '$this->getOptions()');
        $collectDataAt    = strpos($body, '$this->collectLayoutData()');

        $this->assertNotFalse($getOptionsAt, 'Expected a $this->getOptions() call in getInput()');
        $this->assertNotFalse($collectDataAt, 'Expected a $this->collectLayoutData() call in getInput()');
        $this->assertLessThan(
            $collectDataAt,
            $getOptionsAt,
            'getOptions() must run before collectLayoutData() snapshots $this->value, or the multi-campus '
                . 'auto-default (a side effect inside getOptions()) never shows as pre-selected'
        );
    }

    public function testReadOnlyBranchHasAnIdForItsLabel(): void
    {
        $source = (string) file_get_contents((new \ReflectionClass(LocationListField::class))->getFileName());

        $this->assertStringContainsString(
            'id="\' . $this->id . \'"',
            $source,
            'The read-only single-campus <input> must carry an id, or the field\'s own <label for="..."> '
                . 'points at a nonexistent element'
        );
    }
}
