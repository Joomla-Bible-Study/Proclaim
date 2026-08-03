<?php

/**
 * Unit tests for TopicsFormField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\TopicsFormField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for TopicsFormField.
 *
 * Regression test for #1461: buildSelectHtml() hardcoded
 * name="jform[topic_ids]" on its hidden input instead of deriving it from
 * the field's own computed name — breaking silently outside that one
 * hardcoded form/group (e.g. a subform).
 *
 * @since  __DEPLOY_VERSION__
 */
class TopicsFormFieldTest extends ProclaimTestCase
{
    /**
     * @return void
     */
    public function testHiddenInputUsesFieldsOwnComputedName(): void
    {
        $field = (new \ReflectionClass(TopicsFormField::class))->newInstanceWithoutConstructor();

        // A name a subform context would produce — deliberately not
        // "jform[topic_ids]", to prove the hidden input isn't hardcoded.
        $distinctName = 'jform[subform][0][topic_ids]';

        (new \ReflectionProperty($field, 'name'))->setValue($field, $distinctName);
        (new \ReflectionProperty($field, 'id'))->setValue($field, 'jform_subform_0_topic_ids');

        $method = new \ReflectionMethod(TopicsFormField::class, 'buildSelectHtml');
        $html   = $method->invoke($field, [['id' => 5, 'text' => 'Grace']], [5]);

        $this->assertStringContainsString(
            'name="' . $distinctName . '"',
            $html,
            'The hidden input must use the field\'s own computed name — see #1461'
        );

        $this->assertStringNotContainsString(
            'name="jform[topic_ids]"',
            $html,
            'The hidden input must not hardcode name="jform[topic_ids]" — see #1461'
        );
    }
}
