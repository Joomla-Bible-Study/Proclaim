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
use CWM\Component\Proclaim\Administrator\Field\ShowVersesField;
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

        // Check the value => language-key pairing against the untranslated
        // $predefinedOptions map, not the rendered option text -- getOptions()
        // runs the text through Text::_(), whose output depends on which
        // language files the test bootstrap happened to load. The prior
        // version only asserted the value keys existed, so swapping every
        // label (e.g. "No link" showing the link-to-details string) stayed
        // green.
        $declared = (new \ReflectionProperty($fieldClass, 'predefinedOptions'))->getValue($field);

        foreach ($expectedOptions as $value => $langKey) {
            $this->assertArrayHasKey($value, $byValue, "$fieldClass must still offer option value \"$value\"");
            $this->assertSame(
                $langKey,
                $declared[$value] ?? null,
                "$fieldClass option \"$value\" must keep language key \"$langKey\""
            );
        }
    }

    /**
     * TeacherLinkOptionsField and SeriesLinkOptionsField were exact key-for-key
     * subsets of LinkOptionsField's own option set, each wired as its own
     * duplicate field class. Retired in favor of LinkOptionsField's existing
     * `optionsFilter` attribute (already used elsewhere in Proclaim's XML,
     * e.g. layout-element-settings.xml), which restricts the rendered
     * options to an allowlist of keys -- core's PredefinedlistField has
     * supported it since 4.0.0.
     *
     * @return array<string, array{0: array<int, string>, 1: array<string, string>}>
     */
    public static function optionsFilterProvider(): array
    {
        return [
            'TeacherLinkOptions subset (0,3)' => [
                ['0', '3'],
                ['0' => 'JBS_TPL_NO_LINK', '3' => 'JBS_TPL_LINK_TO_TEACHERS_PROFILE'],
            ],
            'SeriesLinkOptions subset (0,1)' => [
                ['0', '1'],
                ['0' => 'JBS_TPL_NO_LINK', '1' => 'JBS_TPL_LINK_TO_DETAILS'],
            ],
        ];
    }

    /**
     * @param   array<int, string>     $filter           The optionsFilter allowlist.
     * @param   array<string, string>  $expectedOptions  The exact value => language-key set the filter must produce.
     */
    #[DataProvider('optionsFilterProvider')]
    public function testOptionsFilterReproducesTheRetiredFieldsSubsets(array $filter, array $expectedOptions): void
    {
        // getOptions() caches by md5($this->element->asXML()) -- the element
        // must encode the filter too, or this collides with the unfiltered
        // LinkOptionsField coverage above (both using a bare <field/>) and
        // silently returns its cached, unfiltered result instead.
        $field   = (new \ReflectionClass(LinkOptionsField::class))->newInstanceWithoutConstructor();
        $element = new \SimpleXMLElement('<field optionsFilter="' . implode(',', $filter) . '"/>');
        (new \ReflectionProperty($field, 'element'))->setValue($field, $element);
        (new \ReflectionProperty($field, 'fieldname'))->setValue($field, 'test');
        (new \ReflectionProperty($field, 'optionsFilter'))->setValue($field, $filter);

        $options = (new \ReflectionMethod(LinkOptionsField::class, 'getOptions'))->invoke($field);
        $byValue = [];

        foreach ($options as $option) {
            $byValue[(string) $option->value] = $option;
        }

        $this->assertSame(
            array_keys($expectedOptions),
            array_keys($byValue),
            'optionsFilter must restrict LinkOptionsField to exactly the retired field\'s option set, nothing more'
        );

        $declared = (new \ReflectionProperty(LinkOptionsField::class, 'predefinedOptions'))->getValue($field);

        foreach ($expectedOptions as $value => $langKey) {
            $this->assertSame($langKey, $declared[$value] ?? null);
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
