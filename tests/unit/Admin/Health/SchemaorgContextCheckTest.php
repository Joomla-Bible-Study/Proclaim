<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Health;

use CWM\Component\Proclaim\Administrator\Health\Check\SchemaorgContextCheck;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The check's list of non-canonical contexts must match the plugin's.
 *
 * The schemaorg plugin maps each content-event context to the form context the
 * database is keyed on, in a private `CONTEXT_CANONICAL`. The check cannot read
 * a plugin constant at runtime, so it carries its own copy — and a copy is only
 * safe while something fails when the two drift.
 *
 * ⚠️ Drift is silent in the direction that matters. Add a third entity whose
 * model and form names differ, register it in the plugin, and the check keeps
 * reporting a clean site while rows pile up where no form reads them.
 *
 * @since __DEPLOY_VERSION__
 */
class SchemaorgContextCheckTest extends ProclaimTestCase
{
    /**
     * The plugin's canonical map, read from source.
     *
     * The plugin class is not autoloaded by the component's PSR-4 prefix, and
     * loading it would pull in the Joomla plugin stack for a constant, so the
     * declaration is parsed instead.
     *
     * @return  string[]  Non-canonical context names.
     *
     * @since __DEPLOY_VERSION__
     */
    private static function pluginNonCanonicalContexts(): array
    {
        $path = \dirname(__DIR__, 4) . '/plugins/schemaorg/proclaim/src/Extension/Proclaim.php';

        self::assertFileExists($path, 'The schemaorg plugin has moved; this test needs repointing.');

        $source = (string) file_get_contents($path);

        $matched = preg_match(
            '/private const CONTEXT_CANONICAL = \[(.*?)\];/s',
            $source,
            $block
        );

        self::assertSame(1, $matched, 'CONTEXT_CANONICAL was not found in the schemaorg plugin.');

        preg_match_all("/'([^']+)'\s*=>\s*'([^']+)'/", $block[1], $pairs, PREG_SET_ORDER);

        return array_map(static fn (array $pair): string => $pair[1], $pairs);
    }

    /**
     * The check's own list.
     *
     * @return  string[]
     *
     * @since __DEPLOY_VERSION__
     */
    private static function checkContexts(): array
    {
        $reflection = new \ReflectionClass(SchemaorgContextCheck::class);

        return (array) $reflection->getConstant('NON_CANONICAL');
    }

    #[TestDox('The health check knows every non-canonical context the plugin maps')]
    public function testCheckListMatchesThePlugin(): void
    {
        $plugin = self::pluginNonCanonicalContexts();
        $check  = self::checkContexts();

        sort($plugin);
        sort($check);

        // ⚠️ Not a silent pass: an empty parse would match an empty list.
        $this->assertNotEmpty($plugin, 'No contexts were parsed from the plugin; the parse is broken.');

        $this->assertSame(
            $plugin,
            $check,
            "SchemaorgContextCheck::NON_CANONICAL has drifted from the schemaorg plugin's\n"
            . 'CONTEXT_CANONICAL, so the check no longer looks where rows can be stranded.'
        );
    }

    #[TestDox('Every non-canonical context names a component Proclaim owns')]
    public function testContextsAreProclaimContexts(): void
    {
        $contexts = self::checkContexts();

        $this->assertNotEmpty($contexts);

        foreach ($contexts as $context) {
            $this->assertStringStartsWith('com_proclaim.', $context, "Not a Proclaim context: {$context}");
        }
    }
}
