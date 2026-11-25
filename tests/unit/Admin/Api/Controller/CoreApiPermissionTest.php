<?php

/**
 * Contract tests for Proclaim's Access Web Services permission
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Joomla 5.4.11 and 6.2.1 refuse every API request that is not public unless the
 * caller has core.api on the component. Proclaim declares the action so it can
 * be granted on Proclaim alone, and so saving Proclaim's Options keeps a grant:
 * com_config rebuilds the component's rules from the actions its form shows.
 *
 * @since  10.7.9
 */
class CoreApiPermissionTest extends ProclaimTestCase
{
    /**
     * The repository root.
     *
     * @return  string  The absolute path of the repository root
     *
     * @since   10.7.9
     */
    private static function root(): string
    {
        return \dirname(__DIR__, 5);
    }

    /**
     * Every core.api action access.xml declares, in any section.
     *
     * @return  \SimpleXMLElement[]  The action elements
     *
     * @since   10.7.9
     */
    private static function coreApiActions(): array
    {
        $xml = simplexml_load_string((string) file_get_contents(self::root() . '/admin/access.xml'));

        self::assertNotFalse($xml, 'access.xml should parse');

        return $xml->xpath('//action[@name="core.api"]') ?: [];
    }

    /**
     * access.xml declares core.api exactly once, in its component section, where
     * Joomla checks it.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    public function testCoreApiIsDeclaredOnceInTheComponentSection(): void
    {
        $actions = self::coreApiActions();

        $this->assertCount(1, $actions, 'core.api should be declared exactly once');

        $section = $actions[0]->xpath('..');

        $this->assertSame(
            'component',
            (string) ($section[0]['name'] ?? ''),
            'Joomla checks core.api on the component, so it belongs in the component section'
        );
    }

    /**
     * The action's title and description are Proclaim keys defined in en-GB.
     * Core's own key for the action exists only from Joomla 5.4.11 and 6.2.1, and
     * Proclaim supports 5.4.0, where it would show as a raw key.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    public function testCoreApiLabelsAreProclaimKeysDefinedInEnGb(): void
    {
        $actions = self::coreApiActions();

        $this->assertNotEmpty($actions, 'core.api should be declared');

        $ini = (string) file_get_contents(self::root() . '/admin/language/en-GB/en-GB.com_proclaim.ini');

        foreach (['title', 'description'] as $attribute) {
            $key = (string) $actions[0][$attribute];

            $this->assertStringStartsWith('JBS_', $key, "The {$attribute} should be a Proclaim key, not core's");
            $this->assertMatchesRegularExpression(
                '/^' . preg_quote($key, '/') . '\s*=\s*"[^"]+"/m',
                $ini,
                "{$key} should be defined in en-GB"
            );
        }
    }

    /**
     * The component asset Proclaim creates on its first run grants core.api to no
     * one, as Joomla's own install does for its components, so API access stays
     * a deliberate grant.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \JsonException
     */
    public function testFirstRunAssetDoesNotGrantCoreApi(): void
    {
        $source = (string) file_get_contents(self::root() . '/admin/src/Lib/Cwmassets.php');

        $this->assertSame(
            1,
            preg_match('/\$defaultRules\s*=\s*\'([^\']+)\'/', $source, $match),
            'The first-run default rules this test reads have moved'
        );

        $rules = json_decode($match[1], true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('core.manage', $rules, 'The rules read here are not the first-run default rules');
        $this->assertArrayNotHasKey('core.api', $rules);
    }
}
