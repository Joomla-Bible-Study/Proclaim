<?php

/**
 * Unit tests for BibleVersionField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\BibleVersionField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;

/**
 * Regression test for #1484: getOptions() had ~120 lines of DB query,
 * provider-filtering, and language grouping/sorting/labelling logic inline
 * in the field class. That logic moved to CwmbibleVersionHelper (see
 * CwmbibleVersionHelperTest); this field should now just be a thin adapter.
 *
 * @since  __DEPLOY_VERSION__
 */
class BibleVersionFieldTest extends ProclaimTestCase
{
    /**
     * getOptions() detects the current language via
     * Factory::getApplication()->getLanguage()->getTag(), which returns
     * null when the CLI test app's language has no metadata loaded —
     * accessing that null inside Joomla core's Language::getTag() prints a
     * warning that fails the test under phpunit.xml's
     * beStrictAboutOutputDuringTests/failOnWarning. Force a tag onto the
     * language's metadata via reflection (no public setter exists). See
     * memory note integration-test-getdate-language-warning.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $lang = Factory::getApplication()->getLanguage();
        $prop = new \ReflectionProperty($lang, 'metadata');
        $meta = $prop->getValue($lang);

        if (!\is_array($meta) || ($meta['tag'] ?? null) === null) {
            $prop->setValue($lang, array_merge(\is_array($meta) ? $meta : [], ['tag' => 'en-GB']));
        }
    }

    private function makeField(): BibleVersionField
    {
        $field = (new \ReflectionClass(BibleVersionField::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty($field, 'fieldname'))->setValue($field, 'test');

        return $field;
    }

    public function testSetupSetsDefaultValueWhenNoneProvided(): void
    {
        $field = $this->makeField();

        $xml = new \SimpleXMLElement('<field name="test" />');
        $field->setup($xml, '');

        $value = (new \ReflectionProperty($field, 'value'))->getValue($field);

        $this->assertNotEmpty($value, 'setup() must set a default version when none is provided — see #1484');
    }

    public function testSetupPreservesExplicitValue(): void
    {
        $field = $this->makeField();

        $xml = new \SimpleXMLElement('<field name="test" />');
        $field->setup($xml, 'esv');

        $value = (new \ReflectionProperty($field, 'value'))->getValue($field);

        $this->assertSame('esv', $value, 'setup() must not overwrite an explicitly provided value');
    }

    public function testSetupAddsSearchableCssClass(): void
    {
        $field = $this->makeField();

        $xml = new \SimpleXMLElement('<field name="test" />');
        $field->setup($xml, 'esv');

        $class = (new \ReflectionProperty($field, 'class'))->getValue($field);

        $this->assertStringContainsString('bible-version-searchable', $class);
    }

    public function testGetOptionsDelegatesToHelper(): void
    {
        $field = $this->makeField();

        $xml = new \SimpleXMLElement('<field name="test" />');
        $field->setup($xml, 'kjv');

        $options = (new \ReflectionMethod(BibleVersionField::class, 'getOptions'))->invoke($field);

        $this->assertNotEmpty($options, 'getOptions() must return at least the fallback versions via the helper');
        $this->assertIsObject($options[array_key_last($options)] ?? $options[0], 'Options must be objects (HTMLHelper option shape), not raw arrays');

        // Shape assertions alone can't tell delegation from re-inlined query
        // logic -- the #1484-era regression this test's name guards. Pin the
        // delegation call itself.
        $ref   = new \ReflectionMethod(BibleVersionField::class, 'getOptions');
        $lines = file($ref->getFileName());
        $body  = implode(
            '',
            \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1)
        );

        $this->assertStringContainsString(
            'CwmbibleVersionHelper::getVersionOptions(',
            $body,
            'getOptions() must delegate to CwmbibleVersionHelper, not re-inline the version query/grouping logic'
        );
    }
}
