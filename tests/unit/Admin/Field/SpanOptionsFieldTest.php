<?php

/**
 * Unit tests for SpanOptionsField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\SpanOptionsField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1467: the "None" option was built as
 * HTMLHelper::_('select.option', 'None', 0) -- value/text reversed relative
 * to every other row in the same array (e.g. ('1', 1) is value='1',
 * text=1). Submitting "None" posted the literal string "None" instead of 0,
 * which downstream consumers (e.g. Cwmlisting.php's colspan handling) treat
 * as a truthy, non-numeric value rather than "no span override".
 *
 * @since  __DEPLOY_VERSION__
 */
class SpanOptionsFieldTest extends ProclaimTestCase
{
    public function testNoneOptionHasNumericZeroValue(): void
    {
        $field = (new \ReflectionClass(SpanOptionsField::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty($field, 'element'))->setValue($field, new \SimpleXMLElement('<field/>'));
        (new \ReflectionProperty($field, 'fieldname'))->setValue($field, 'test');

        $options = (new \ReflectionMethod(SpanOptionsField::class, 'getOptions'))->invoke($field);

        $none = null;

        foreach ($options as $option) {
            if ($option->text === 'None') {
                $none = $option;
                break;
            }
        }

        $this->assertNotNull($none, 'Expected a "None" option to be present');
        $this->assertSame(0, $none->value, '"None" must submit the numeric value 0, not the string "None" — see #1467');
    }
}
