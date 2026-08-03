<?php

/**
 * Unit tests for LayoutEditorField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\LayoutEditorField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for LayoutEditorField's params-prefix computation.
 *
 * Regression test for #1461: getInput() hardcoded
 * data-params-prefix="jform[params]" instead of deriving it from the
 * field's own form-control/group state — breaking silently outside that
 * one hardcoded form/group.
 *
 * @since  __DEPLOY_VERSION__
 */
class LayoutEditorFieldTest extends ProclaimTestCase
{
    /**
     * Build a field instance with given formControl/group state, without
     * going through the constructor (which needs a full Form object).
     */
    private function makeField(string $formControl, ?string $group): LayoutEditorField
    {
        $field = (new \ReflectionClass(LayoutEditorField::class))->newInstanceWithoutConstructor();

        (new \ReflectionProperty($field, 'formControl'))->setValue($field, $formControl);
        (new \ReflectionProperty($field, 'group'))->setValue($field, $group);

        return $field;
    }

    /**
     * Invoke the field's protected prefix computation.
     */
    private function computePrefix(LayoutEditorField $field): string
    {
        return (new \ReflectionMethod(LayoutEditorField::class, 'computeParamsPrefix'))->invoke($field);
    }

    /**
     * @return void
     */
    public function testCommonCaseMatchesTheOldHardcodedValue(): void
    {
        $field = $this->makeField('jform', 'params');

        $this->assertSame('jform[params]', $this->computePrefix($field));
    }

    /**
     * @return void
     */
    public function testDifferentGroupIsNotForcedToParams(): void
    {
        $field = $this->makeField('jform', 'settings');

        $this->assertSame(
            'jform[settings]',
            $this->computePrefix($field),
            'A field outside the "params" group must not be hardcoded to jform[params] — see #1461'
        );
    }

    /**
     * @return void
     */
    public function testNestedGroupProducesEachLevel(): void
    {
        $field = $this->makeField('jform', 'params.layout');

        $this->assertSame('jform[params][layout]', $this->computePrefix($field));
    }

    /**
     * @return void
     */
    public function testNoGroupIsJustTheFormControl(): void
    {
        $field = $this->makeField('jform', null);

        $this->assertSame('jform', $this->computePrefix($field));
    }
}
