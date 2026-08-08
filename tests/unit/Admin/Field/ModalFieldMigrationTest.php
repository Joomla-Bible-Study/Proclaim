<?php

/**
 * Unit tests for the Modal/StudyField and Modal/TeacherDisplayField migration
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\Modal\StudyField;
use CWM\Component\Proclaim\Administrator\Field\Modal\TeacherDisplayField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Form\Field\ModalSelectField;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regression test for #1465: StudyField and TeacherDisplayField hand-rolled
 * ~300 lines each of manual modal HTML/JS via a fully overridden getInput(),
 * the same pattern that produced the XSS in ServerTypeField (#1457). Both
 * now extend Joomla core's ModalSelectField, matching Modal/SeriesField.php.
 *
 * @since  __DEPLOY_VERSION__
 */
class ModalFieldMigrationTest extends ProclaimTestCase
{
    /**
     * @return array<string, array{0: class-string, 1: string, 2: string, 3: string, 4: string}>
     */
    public static function fieldProvider(): array
    {
        return [
            'StudyField' => [
                StudyField::class, 'view=cwmmessages', 'view=cwmmessage', 'task=cwmmessage.edit', 'task=cwmmessage.add',
            ],
            'TeacherDisplayField' => [
                TeacherDisplayField::class, 'view=cwmteachers', 'view=cwmteacher', 'task=cwmteacher.edit', 'task=cwmteacher.add',
            ],
        ];
    }

    /**
     * @param   class-string  $fieldClass    The field class under test.
     * @param   string        $selectView    Expected view= in the select URL.
     * @param   string        $editView      Expected view= in the edit/new URLs.
     * @param   string        $editTask      Expected task= in the edit URL.
     * @param   string        $newTask       Expected task= in the new URL.
     */
    #[DataProvider('fieldProvider')]
    public function testFieldExtendsModalSelectFieldAndBuildsCorrectUrls(
        string $fieldClass,
        string $selectView,
        string $editView,
        string $editTask,
        string $newTask
    ): void {
        $this->assertTrue(
            is_subclass_of($fieldClass, ModalSelectField::class),
            $fieldClass . ' must extend ModalSelectField — see #1465'
        );

        $field = (new \ReflectionClass($fieldClass))->newInstanceWithoutConstructor();
        (new \ReflectionProperty($field, 'fieldname'))->setValue($field, 'test');

        $xml = new \SimpleXMLElement('<field name="test" />');

        (new \ReflectionMethod($fieldClass, 'setup'))->invoke($field, $xml, 0);

        $urls = (new \ReflectionProperty($field, 'urls'))->getValue($field);

        $this->assertStringContainsString($selectView, $urls['select'], "$fieldClass select URL must target $selectView");
        $this->assertStringContainsString($editView, $urls['edit'], "$fieldClass edit URL must target $editView");
        $this->assertStringContainsString($editTask, $urls['edit'], "$fieldClass edit URL must use $editTask");
        $this->assertStringContainsString($newTask, $urls['new'], "$fieldClass new URL must use $newTask");

        // Legacy "no selection" sentinel (0) must normalize to empty, not literal "0".
        $value = (new \ReflectionProperty($field, 'value'))->getValue($field);
        $this->assertSame('', $value, "$fieldClass must normalize a 0 value to empty string — see #1465");
    }
}
