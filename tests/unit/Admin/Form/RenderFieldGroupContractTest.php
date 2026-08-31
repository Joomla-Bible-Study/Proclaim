<?php

/**
 * A template may only render a field its form actually declares.
 *
 * `Form::renderField($name, $group)` returns an empty string when the field is
 * not in that group. Nothing throws, nothing is logged, the page renders and
 * the form still validates — so the setting is simply absent from the screen
 * and there is no way to notice from the code.
 *
 * Three of these were live at once:
 *
 *   - `custom_css` declared in a top-level `<fieldset name="customcss">`
 *     rather than under `<fields name="params">`, so the Administration
 *     screen's Custom CSS tab showed a description and no editor. It shipped
 *     in 10.5.8 and no site ever stored a value.
 *   - `access` rendered by the comment form, never declared, so a comment's
 *     access level could not be set even though the column exists.
 *   - `text` rendered by the template form, referring to a column
 *     `#__bsms_templates` does not have.
 *
 * ⚠️ Reads the templates and forms rather than rendering them. A rendering
 * test needs a booted application and a database, and would only cover the
 * views it happened to visit; the mismatch is a static fact about two files.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Form;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class RenderFieldGroupContractTest extends ProclaimTestCase
{
    /**
     * Views whose template legitimately renders fields from another form.
     *
     * ⚠️ Keep this empty unless there is a real reason. Every entry is a view
     * this test no longer covers.
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    private const EXEMPT_VIEWS = [];

    /**
     * Map every field a form declares to the groups it appears in.
     *
     * An empty string means the field sits outside any `<fields>` wrapper,
     * which is what `renderField($name)` with no group asks for.
     *
     * @param   string  $path  Absolute path to the form XML.
     *
     * @return  array<string, string[]>|null  Null when the file will not parse.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function fieldGroups(string $path): ?array
    {
        $xml = @simplexml_load_file($path);

        if ($xml === false) {
            return null;
        }

        $found = [];

        $walk = static function (\SimpleXMLElement $el, string $group) use (&$walk, &$found): void {
            foreach ($el->children() as $child) {
                $tag = $child->getName();

                if ($tag === 'fields') {
                    $walk($child, (string) ($child['name'] ?? ''));
                } elseif ($tag === 'fieldset') {
                    // A fieldset groups for display only; it does not change
                    // the name a field posts under.
                    $walk($child, $group);
                } elseif ($tag === 'field') {
                    $name = (string) ($child['name'] ?? '');

                    if ($name !== '') {
                        $found[$name][] = $group;
                    }
                }
            }
        };

        $walk($xml, '');

        return array_map('array_unique', $found);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Every renderField() call names a field its own form declares')]
    public function testRenderedFieldsExistInTheirForm(): void
    {
        $root      = \dirname(__DIR__, 4);
        $offenders = [];
        $checked   = 0;

        foreach (glob($root . '/admin/tmpl/cwm*/*.php') ?: [] as $template) {
            // admin/tmpl/cwmadmin/edit.php -> admin/forms/admin.xml
            $view = substr(basename(\dirname($template)), 3);

            if (\in_array($view, self::EXEMPT_VIEWS, true)) {
                continue;
            }

            $form = $root . '/admin/forms/' . $view . '.xml';

            if (!is_file($form)) {
                continue;
            }

            $source = (string) file_get_contents($template);

            if (!preg_match_all(
                "/renderField\(\s*'([a-zA-Z0-9_]+)'\s*(?:,\s*'([a-zA-Z0-9_]*)')?\s*\)/",
                $source,
                $matches,
                PREG_SET_ORDER
            )) {
                continue;
            }

            $groups = $this->fieldGroups($form);

            if ($groups === null) {
                $offenders[] = basename($form) . ' will not parse as XML';
                continue;
            }

            foreach ($matches as $match) {
                $name  = $match[1];
                $group = $match[2] ?? '';
                $checked++;

                if (!isset($groups[$name])) {
                    $offenders[] = \sprintf(
                        '%s renders "%s", which %s does not declare',
                        $view . '/' . basename($template),
                        $name,
                        basename($form)
                    );

                    continue;
                }

                if (!\in_array($group, $groups[$name], true)) {
                    $offenders[] = \sprintf(
                        '%s renders "%s" from group "%s", but %s declares it in [%s]',
                        $view . '/' . basename($template),
                        $name,
                        $group,
                        basename($form),
                        implode(', ', array_map(static fn ($g) => $g === '' ? '(no group)' : $g, $groups[$name]))
                    );
                }
            }
        }

        // ⚠️ Positive control. The pattern above is doing real work only while
        // it keeps matching; a rename or a moved directory would otherwise turn
        // this into a test that passes on an empty set.
        $this->assertGreaterThan(
            100,
            $checked,
            'Far fewer renderField() calls were found than the admin templates contain. '
            . 'The detection has stopped working, so this test is no longer guarding anything.'
        );

        $this->assertSame(
            [],
            $offenders,
            "A template renders a field its form does not declare, which produces nothing at all:\n  "
            . implode("\n  ", $offenders)
        );
    }
}
