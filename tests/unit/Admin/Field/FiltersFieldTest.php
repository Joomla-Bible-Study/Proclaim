<?php

/**
 * Unit tests for the Text Filters field's value normalisation.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\FiltersField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The field renders one row per user group from a stored params value it does not
 * control — `filter=""` on the field definition means whatever is in the database
 * arrives untouched.
 *
 * A production site could not open Proclaim's settings at all:
 *
 *     0 Cannot access offset of type string on string
 *     FiltersField.php:108
 *
 * A group's stored entry was a string rather than an array. `isset()` passed,
 * indexing the string with the numeric group id returned a single character, and
 * reading `['filter_tags']` off that character was fatal.
 *
 * @since  __DEPLOY_VERSION__
 */
class FiltersFieldTest extends ProclaimTestCase
{
    /**
     * Call one of the field's protected static normalisers.
     */
    private static function normalise(string $method, mixed $input): array
    {
        $ref = new \ReflectionMethod(FiltersField::class, $method);

        return $ref->invoke(null, $input);
    }

    /**
     * Values the whole `filters` param has been seen or could plausibly arrive as.
     *
     * @return array<string, array{0: mixed, 1: array<array-key, mixed>}>
     */
    public static function storedValueProvider(): array
    {
        $healthy = [
            '1' => ['filter_type' => 'BL', 'filter_tags' => '', 'filter_attributes' => ''],
        ];

        return [
            'array passes through' => [$healthy, $healthy],
            'object becomes array' => [(object) $healthy, $healthy],
            'string becomes empty' => ['BL', []],
            'empty string'         => ['', []],
            'null'                 => [null, []],
            'integer'              => [0, []],
            'bool'                 => [false, []],
        ];
    }

    #[DataProvider('storedValueProvider')]
    public function testStoredValueIsCoercedToArray(mixed $input, array $expected): void
    {
        $this->assertSame($expected, self::normalise('normaliseStoredValue', $input));
    }

    /**
     * A stdClass from Registry must keep the site's saved filters, not reset them.
     */
    public function testObjectValueRetainsGroupKeys(): void
    {
        $stored = (object) [
            '1' => ['filter_type' => 'NONE', 'filter_tags' => '', 'filter_attributes' => ''],
            '9' => ['filter_type' => 'NH', 'filter_tags' => 'p', 'filter_attributes' => 'style'],
        ];

        $result = self::normalise('normaliseStoredValue', $stored);

        // get_object_vars() returns numeric property names as int keys. Harmless:
        // PHP normalises numeric-string array indices to ints, so the lookup in
        // getInput() ($this->value[$group->value], where value is "1") still hits.
        $this->assertSame([1, 9], array_keys($result));
        $this->assertSame('NONE', $result['1']['filter_type'], 'String index must still resolve');
        $this->assertSame('NH', $result[9]['filter_type']);
    }

    /**
     * Per-group entries, including the shape that caused the outage.
     *
     * @return array<string, array{0: mixed}>
     */
    public static function brokenGroupEntryProvider(): array
    {
        return [
            'string (the reported case)' => ['BL'],
            'empty string'               => [''],
            'null'                       => [null],
            'integer'                    => [1],
            'bool'                       => [true],
            'list instead of map'        => [['BL', '', '']],
        ];
    }

    /**
     * Whatever the entry is, the caller gets a complete array back — so the
     * named-key reads in getInput() cannot fatal.
     */
    #[DataProvider('brokenGroupEntryProvider')]
    public function testAnyGroupEntryYieldsACompleteFilterArray(mixed $entry): void
    {
        $result = self::normalise('normaliseGroupFilter', $entry);

        foreach (['filter_type', 'filter_tags', 'filter_attributes'] as $key) {
            $this->assertArrayHasKey($key, $result);
            $this->assertIsString($result[$key], "$key must be a string for htmlspecialchars()");
        }

        $this->assertSame('BL', $result['filter_type'], 'Unusable entries fall back to the default filter');
    }

    /**
     * A valid entry is preserved exactly — the fix must not flatten real settings.
     */
    public function testValidGroupEntryIsPreserved(): void
    {
        $entry = ['filter_type' => 'NH', 'filter_tags' => 'script,iframe', 'filter_attributes' => 'onclick'];

        $this->assertSame($entry, self::normalise('normaliseGroupFilter', $entry));
    }

    /**
     * An object entry keeps its settings rather than being discarded.
     */
    public function testObjectGroupEntryIsConvertedNotDiscarded(): void
    {
        $result = self::normalise('normaliseGroupFilter', (object) ['filter_type' => 'WL', 'filter_tags' => 'b']);

        $this->assertSame('WL', $result['filter_type']);
        $this->assertSame('b', $result['filter_tags']);
        $this->assertSame('', $result['filter_attributes'], 'Missing keys are defaulted');
    }

    /**
     * A partial entry is as safe as an absent one.
     */
    public function testPartialGroupEntryIsCompleted(): void
    {
        $result = self::normalise('normaliseGroupFilter', ['filter_type' => 'CBL']);

        $this->assertSame('CBL', $result['filter_type']);
        $this->assertSame('', $result['filter_tags']);
        $this->assertSame('', $result['filter_attributes']);
    }

    /**
     * Reproduces the outage end to end.
     *
     * Mirrors what getInput() does per group — the exact sequence that fatalled,
     * with the normaliser in place. Without it, `$value[$groupId]['filter_tags']`
     * throws "Cannot access offset of type string on string".
     */
    public function testReportedFatalNoLongerOccurs(): void
    {
        // A group entry stored as a string, as found on the affected site.
        $value   = self::normalise('normaliseStoredValue', ['1' => 'BL']);
        $groupId = '1';

        $groupFilter = self::normalise('normaliseGroupFilter', $value[$groupId] ?? null);

        // These reads are what threw. They must be safe for any stored shape.
        $this->assertIsString($groupFilter['filter_type']);
        $this->assertIsString(htmlspecialchars($groupFilter['filter_tags'], ENT_QUOTES));
        $this->assertIsString(htmlspecialchars($groupFilter['filter_attributes'], ENT_QUOTES));
    }

    /**
     * Regression test for #1456: getInput() interpolated $group->text (a
     * #__usergroups.title) directly into HTML with no escaping, unlike the
     * filter_tags/filter_attributes values two lines below it.
     *
     * @return void
     */
    public function testGroupTitleIsEscaped(): void
    {
        $reflection = new \ReflectionMethod(FiltersField::class, 'getInput');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertMatchesRegularExpression(
            '/htmlspecialchars\(\$group->text/',
            $body,
            'getInput() must escape $group->text — see #1456'
        );
    }
}
