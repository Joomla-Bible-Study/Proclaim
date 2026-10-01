<?php

/**
 * Contract test: every Yes/No radio field uses the modern switcher layout
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Form;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * `class="btn-group btn-group-yesno"` is a pre-switcher idiom. Proclaim's own
 * radio fields already standardized on `layout="joomla.form.field.radio.
 * switcher"` everywhere except its newest module at the time of a field-XML
 * audit (mod_proclaim_youtube.xml, created 2026-09-24, 12 fields) and one
 * plugin form (analytics.xml, 3 fields) -- a regression against Proclaim's
 * own established convention, not a core-vs-Proclaim gap. Guards against it
 * happening again in a future form.
 *
 * @since  __DEPLOY_VERSION__
 */
class RadioSwitcherLayoutContractTest extends ProclaimTestCase
{
    public function testNoFormUsesTheLegacyYesNoClassWithoutTheSwitcherLayout(): void
    {
        $root  = \dirname(__DIR__, 4);
        $files = array_merge(
            glob($root . '/admin/forms/*.xml'),
            glob($root . '/admin/forms/**/*.xml'),
            glob($root . '/site/forms/*.xml'),
            glob($root . '/modules/*/*/*.xml'),
            glob($root . '/plugins/*/*/forms/*.xml'),
        );

        $offenders = [];

        foreach ($files as $file) {
            $xml = (string) file_get_contents($file);

            // A field using the legacy class without ALSO declaring the
            // modern layout on the same element. Field elements can span
            // multiple lines, so check per <field ...> tag rather than
            // per line.
            if (!preg_match_all('/<field\b[^>]*?\/?>/s', $xml, $matches)) {
                continue;
            }

            foreach ($matches[0] as $fieldTag) {
                if (
                    str_contains($fieldTag, 'btn-group-yesno')
                    && !str_contains($fieldTag, 'joomla.form.field.radio.switcher')
                ) {
                    $offenders[] = basename($file);
                }
            }
        }

        $this->assertSame(
            [],
            array_unique($offenders),
            'These forms use the legacy btn-group-yesno class without the modern switcher layout: '
                . implode(', ', array_unique($offenders))
        );
    }
}
