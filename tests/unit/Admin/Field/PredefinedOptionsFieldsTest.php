<?php

/**
 * Unit tests for the fields converted from ListField to PredefinedlistField.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\DateFormatField;
use CWM\Component\Proclaim\Administrator\Field\ElementOptionsField;
use CWM\Component\Proclaim\Administrator\Field\FilesizeField;
use CWM\Component\Proclaim\Administrator\Field\LinkOptionsField;
use CWM\Component\Proclaim\Administrator\Field\RowOptionsField;
use CWM\Component\Proclaim\Administrator\Field\ScriptureSeparatorField;
use CWM\Component\Proclaim\Administrator\Field\SeriesLinkOptionsField;
use CWM\Component\Proclaim\Administrator\Field\ShowVersesField;
use CWM\Component\Proclaim\Administrator\Field\TeacherLinkOptionsField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Form\Field\PredefinedlistField;
use Joomla\CMS\Form\FormField;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regression test for #1464: 8 fields extended ListField with a getOptions()
 * that never queried the DB — always the same hardcoded array — which is
 * exactly what PredefinedlistField is for. FilesizeField went the other
 * direction: it extended ListField but overrode getInput() entirely,
 * never calling getOptions() or rendering a <select> — it should extend
 * FormField instead.
 *
 * @since  __DEPLOY_VERSION__
 */
class PredefinedOptionsFieldsTest extends ProclaimTestCase
{
    /**
     * @return array<string, array{0: class-string, 1: array<string, string>}>
     */
    public static function fieldProvider(): array
    {
        return [
            'LinkOptionsField' => [LinkOptionsField::class, [
                '0' => 'JBS_TPL_NO_LINK', '10' => 'JBS_TPL_LINK_TO_SERIES',
            ]],
            'RowOptionsField' => [RowOptionsField::class, [
                '0' => 'JBS_CMN_HIDE', '6' => 'JBS_TPL_ROW6',
            ]],
            'TeacherLinkOptionsField' => [TeacherLinkOptionsField::class, [
                '0' => 'JBS_TPL_NO_LINK', '3' => 'JBS_TPL_LINK_TO_TEACHERS_PROFILE',
            ]],
            'DateFormatField' => [DateFormatField::class, [
                '0' => 'JBS_TPL_DATE_FORMAT_MMM_D_YYYY', '9' => 'JBS_TPL_DATE_FORMAT_YYYY_MM_DD',
            ]],
            'ScriptureSeparatorField' => [ScriptureSeparatorField::class, [
                'newline' => 'JBS_TPL_SEPARATOR_STACKED', 'semicolon' => 'JBS_TPL_SEPARATOR_SEMICOLON',
            ]],
            'ShowVersesField' => [ShowVersesField::class, [
                '0' => 'JBS_TPL_SHOW_ONLY_CHAPTERS', '2' => 'JBS_TPL_SHOW_ONLY_BOOKS',
            ]],
            'ElementOptionsField' => [ElementOptionsField::class, [
                '0' => 'JBS_CMN_NONE', '8' => 'JBS_TPL_DIV',
            ]],
            'SeriesLinkOptionsField' => [SeriesLinkOptionsField::class, [
                '0' => 'JBS_TPL_NO_LINK', '1' => 'JBS_TPL_LINK_TO_DETAILS',
            ]],
        ];
    }

    /**
     * @param   class-string           $fieldClass       The field class under test.
     * @param   array<string, string>  $expectedOptions  A representative sample of value => language-key pairs.
     */
    #[DataProvider('fieldProvider')]
    public function testFieldExtendsPredefinedlistFieldAndKeepsItsOptions(string $fieldClass, array $expectedOptions): void
    {
        $this->assertTrue(
            is_subclass_of($fieldClass, PredefinedlistField::class),
            $fieldClass . ' must extend PredefinedlistField — see #1464'
        );

        $field = (new \ReflectionClass($fieldClass))->newInstanceWithoutConstructor();
        (new \ReflectionProperty($field, 'element'))->setValue($field, new \SimpleXMLElement('<field/>'));
        (new \ReflectionProperty($field, 'fieldname'))->setValue($field, 'test');

        $options = (new \ReflectionMethod($fieldClass, 'getOptions'))->invoke($field);
        $byValue = [];

        foreach ($options as $option) {
            $byValue[(string) $option->value] = $option;
        }

        foreach ($expectedOptions as $value => $langKey) {
            $this->assertArrayHasKey($value, $byValue, "$fieldClass must still offer option value \"$value\"");
        }
    }

    /**
     * @return void
     */
    public function testFilesizeFieldExtendsFormFieldNotListField(): void
    {
        $this->assertTrue(
            is_subclass_of(FilesizeField::class, FormField::class),
            'FilesizeField must extend FormField — see #1464'
        );

        $this->assertFalse(
            is_subclass_of(FilesizeField::class, PredefinedlistField::class)
                || is_a(FilesizeField::class, \Joomla\CMS\Form\Field\ListField::class, true),
            'FilesizeField must not extend ListField — it never renders a <select> — see #1464'
        );
    }
}
