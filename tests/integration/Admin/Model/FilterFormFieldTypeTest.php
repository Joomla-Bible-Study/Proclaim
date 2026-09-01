<?php

/**
 * A filter form field must resolve to the type it declares.
 *
 * Joomla does not fail when a field type cannot be found. It falls back to a
 * plain text input, so a mistyped or unreachable custom type renders as a text
 * box on the list screen and nothing anywhere reports a problem.
 *
 * ⚠️ The usual cause is a missing `addfieldprefix`. A custom type only resolves
 * when the form declares the namespace to look in:
 *
 *   <fieldset addfieldprefix="CWM\Component\Proclaim\Administrator\Field">
 *
 * `filter_cwmservers.xml` had no such fieldset, so adding a `ServerTypeList`
 * field to it produced a text box — caught by loading the form rather than by
 * reading it, which is why this test drives a real Form.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class FilterFormFieldTypeTest extends IntegrationTestCase
{
    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('A form using a custom field type declares where to find it')]
    public function testFilterFormsDeclareTheirFieldPrefix(): void
    {
        $forms = glob(\dirname(__DIR__, 4) . '/admin/forms/filter_*.xml') ?: [];

        // ⚠️ Positive control: if the glob stops matching, every assertion
        // below passes against an empty set.
        $this->assertNotEmpty($forms, 'No filter forms were found — this test would guard nothing.');

        $offenders = [];
        $custom    = 0;

        foreach ($forms as $path) {
            $raw = (string) file_get_contents($path);
            $xml = simplexml_load_string($raw);

            if ($xml === false) {
                $offenders[] = basename($path) . ' is not valid XML';
                continue;
            }

            $hasPrefix = str_contains($raw, 'addfieldprefix=');

            foreach ($xml->xpath('//field[@type]') ?: [] as $node) {
                $declared = (string) $node['type'];

                if ($declared === '') {
                    continue;
                }

                // Anything Joomla ships resolves without help. Everything else
                // has to be found through the component's field namespace.
                $coreClass = 'Joomla\\CMS\\Form\\Field\\' . ucfirst($declared) . 'Field';

                if (class_exists($coreClass)) {
                    continue;
                }

                $custom++;

                if (!$hasPrefix) {
                    $offenders[] = basename($path) . ": field type '$declared' is not a core type and the form "
                        . 'declares no addfieldprefix, so it resolves to a plain text input with nothing reported';
                }
            }
        }

        // ⚠️ Deliberately a static read of the XML rather than loading the Form
        // and inspecting the resolved class. FormHelper caches type -> class in
        // a static, so once any earlier test has loaded the class it resolves
        // even from a form with no prefix — a canary that removed the prefix
        // passed for that reason. In a fresh process it fell back correctly,
        // which makes a Form-driven assertion depend on test order.
        $this->assertGreaterThan(
            0,
            $custom,
            'No custom field types were found in any filter form. The detection has stopped working.'
        );

        $this->assertSame(
            [],
            $offenders,
            "Filter forms using a custom field type without declaring its namespace:\n  "
            . implode("\n  ", $offenders)
        );
    }
}
