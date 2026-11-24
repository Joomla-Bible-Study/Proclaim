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
 * @since  10.5.6
 */
class LayoutEditorFieldTest extends ProclaimTestCase
{
    /**
     * Build a field instance with given formControl/group state, without
     * going through the constructor (which needs a full Form object).
     *
     * @param   string       $formControl  The form control name, such as jform
     * @param   string|null  $group        The field group, such as params, or null for none
     *
     * @return  LayoutEditorField  The field
     *
     * @since   10.5.6
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
     *
     * @param   LayoutEditorField  $field  The field
     *
     * @return  string  The computed params prefix
     *
     * @since   10.5.6
     */
    private function computePrefix(LayoutEditorField $field): string
    {
        return (new \ReflectionMethod(LayoutEditorField::class, 'computeParamsPrefix'))->invoke($field);
    }

    /**
     * The common case gives jform[params], as the old hard-coded value did.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testCommonCaseMatchesTheOldHardcodedValue(): void
    {
        $field = $this->makeField('jform', 'params');

        $this->assertSame('jform[params]', $this->computePrefix($field));
    }

    /**
     * A different group is kept, not forced to params.
     *
     * @return  void
     *
     * @since   10.5.6
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
     * A nested group produces a bracket for each level.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testNestedGroupProducesEachLevel(): void
    {
        $field = $this->makeField('jform', 'params.layout');

        $this->assertSame('jform[params][layout]', $this->computePrefix($field));
    }

    /**
     * With no group, the prefix is just the form control.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testNoGroupIsJustTheFormControl(): void
    {
        $field = $this->makeField('jform', null);

        $this->assertSame('jform', $this->computePrefix($field));
    }
}
