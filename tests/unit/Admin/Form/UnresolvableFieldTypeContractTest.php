<?php

/**
 * Contract test: no form field uses a type that resolves to nothing
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Form;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for type="int": not a real Joomla field type. Neither core
 * (checked 5.4.7/6.1.3/6.2.0-beta3) nor Proclaim registers an IntField class
 * under any prefix FormHelper::loadClass() tries, nor a file named int.php
 * on any registered field path. Form::loadField() silently falls back to a
 * plain text field rather than failing (libraries/src/Form/Form.php ~1478),
 * so the bug was invisible: 17 fields across admin.xml, archive.xml and
 * template.xml rendered as unconstrained text inputs instead of numeric
 * ones, with their min/max attributes silently ignored.
 *
 * This doesn't re-derive the full list of valid Joomla type names (that
 * would just be a second copy of core's own field directory) -- it guards
 * against the specific failure mode found: a bare, lowercase lightweight
 * type name that reads like a PHP/SQL type rather than a Joomla field type,
 * which is exactly the shape that silently degrades instead of erroring.
 *
 * @since  __DEPLOY_VERSION__
 */
class UnresolvableFieldTypeContractTest extends ProclaimTestCase
{
    /**
     * Type names that look like a field type but are not one -- confirmed by
     * reading FormHelper::loadClass()'s resolution order and finding no
     * matching class or file anywhere in core or Proclaim.
     *
     * @return array<string, array{0: string}>
     */
    public static function knownBadTypeProvider(): array
    {
        return [
            // "integer" deliberately NOT included here: Joomla\CMS\Form\Field\
            // IntegerField is a real core class (confirmed present in both
            // J5.4.7 and J6.1.3) -- it's the "first"/"last"/"step" sequential
            // dropdown core itself uses for e.g. com_categories' level filter.
            // "int" is the trap: it reads like a valid abbreviation of
            // "integer" but has no backing class under any name.
            '"int" (use "number")'    => ['int'],
            '"string" (use "text")'   => ['string'],
            '"bool" (use "radio")'    => ['bool'],
            '"boolean" (use "radio")' => ['boolean'],
            '"float" (use "number")'  => ['float'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('knownBadTypeProvider')]
    public function testNoFormUsesThisUnresolvableType(string $badType): void
    {
        $root  = \dirname(__DIR__, 4);
        $files = array_merge(
            glob($root . '/admin/forms/*.xml'),
            glob($root . '/admin/forms/**/*.xml'),
            glob($root . '/site/forms/*.xml'),
            glob($root . '/modules/*/*/*.xml'),
            glob($root . '/plugins/*/*/forms/*.xml'),
            glob($root . '/admin/src/Addons/**/*.xml'),
        );

        $offenders = [];

        foreach ($files as $file) {
            $xml = (string) file_get_contents($file);

            if (preg_match('/<field\b[^>]*\btype="' . preg_quote($badType, '/') . '"/i', $xml)) {
                $offenders[] = basename($file);
            }
        }

        $this->assertSame(
            [],
            array_unique($offenders),
            \sprintf(
                'These forms use type="%s", which resolves to nothing and silently falls back to a plain '
                    . 'text field (Form::loadField()): %s',
                $badType,
                implode(', ', array_unique($offenders))
            )
        );
    }
}
