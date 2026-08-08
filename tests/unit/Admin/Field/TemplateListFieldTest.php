<?php

/**
 * Unit tests for TemplateListField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\TemplateListField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for TemplateListField's option caching.
 *
 * Regression test for #1460: self::$templates cached the full merged result
 * of parent::getOptions() (this instance's own XML <option> children) plus
 * the DB-derived list. A second field instance with different XML options in
 * the same request silently got the first instance's stale merged options,
 * because the early return skipped calling parent::getOptions() again.
 *
 * @since  __DEPLOY_VERSION__
 */
class TemplateListFieldTest extends ProclaimTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Isolate each test from whatever a previous test (or a real request)
        // left in the shared static cache.
        $reflection = new \ReflectionProperty(TemplateListField::class, 'templates');
        $reflection->setValue(null, []);
    }

    /**
     * Build a field instance with a given XML <option> child, without going
     * through the constructor (which needs a full Form object) or the
     * DB-backed half of getOptions() (pre-seeded directly below instead).
     */
    private function makeField(string $optionValue, string $optionLabel): TemplateListField
    {
        $field = (new \ReflectionClass(TemplateListField::class))->newInstanceWithoutConstructor();

        $xml = new \SimpleXMLElement(
            '<field name="template_id"><option value="' . $optionValue . '">' . $optionLabel . '</option></field>'
        );

        $elementProperty = new \ReflectionProperty($field, 'element');
        $elementProperty->setValue($field, $xml);

        // ListField::getOptions() reads $this->fieldname; unset (null) since
        // the constructor never ran, which triggers a preg_replace()
        // deprecation notice in Joomla core on PHP 8.1+.
        $fieldnameProperty = new \ReflectionProperty($field, 'fieldname');
        $fieldnameProperty->setValue($field, 'template_id');

        return $field;
    }

    /**
     * @return void
     */
    public function testEachInstanceKeepsItsOwnXmlOptions(): void
    {
        // Pre-seed the DB-derived half of the cache directly, so getOptions()
        // takes the "already cached" branch without needing a real database.
        $templatesProperty = new \ReflectionProperty(TemplateListField::class, 'templates');
        $dbOption          = (object) ['value' => '5', 'text' => 'Shared DB Template'];
        $templatesProperty->setValue(null, [$dbOption]);

        $getOptions = new \ReflectionMethod(TemplateListField::class, 'getOptions');

        $first  = $getOptions->invoke($this->makeField('a', 'First Instance Option'));
        $second = $getOptions->invoke($this->makeField('b', 'Second Instance Option'));

        $firstValues  = array_map(static fn ($o) => $o->value, $first);
        $secondValues = array_map(static fn ($o) => $o->value, $second);

        $this->assertContains('a', $firstValues, 'First instance must keep its own XML option');
        $this->assertNotContains('b', $firstValues, 'First instance must not see the second instance\'s XML option — see #1460');

        $this->assertContains('b', $secondValues, 'Second instance must keep its own XML option');
        $this->assertNotContains('a', $secondValues, 'Second instance must not see the first instance\'s XML option — see #1460');

        // Both still share the same DB-derived option — that part is
        // correctly cacheable across instances.
        $this->assertContains('5', $firstValues);
        $this->assertContains('5', $secondValues);
    }
}
