<?php

/**
 * Unit tests for the schemaorg/proclaim plugin's script-embedding sanitizer.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Plugin\Schemaorg;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression coverage for #1562 finding 1 (stored XSS via JSON-LD injection).
 *
 * inject() in CwmschemaorgHelper is skipped for any item that already has a
 * #__schemaorg row (see hasJoomlaSchema()) -- which is every item ever saved
 * through the admin form. For those, structured data is rendered entirely by
 * Joomla core's plugins/system/schemaorg, which serializes the @graph with
 * JSON_UNESCAPED_SLASHES and no JSON_HEX_TAG before embedding it directly in
 * a <script type="application/ld+json"> tag -- the same vulnerability class
 * as finding 1, just in code this repo doesn't own. Since the schemaorg/
 * proclaim plugin's onSchemaBeforeCompileHead() runs immediately before that
 * serialization, it is the last point this codebase can intercept the data,
 * so sanitizeForScriptEmbedding() neutralizes '<'/'>' there as defense in
 * depth for the common (stored-schema) render path.
 *
 * The plugin class lives outside composer's autoload map (it's loaded via
 * Joomla's plugin system at runtime, not PSR-4), so it's require_once'd
 * directly here and reached via reflection since sanitizeForScriptEmbedding()
 * is a private static pure function with no Joomla application dependency.
 *
 * @since  __DEPLOY_VERSION__
 */
class ProclaimScriptEmbeddingTest extends ProclaimTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(\CWM\Plugin\Schemaorg\Proclaim\Extension\Proclaim::class)) {
            require_once \dirname(__DIR__, 4) . '/plugins/schemaorg/proclaim/src/Extension/Proclaim.php';
        }
    }

    /**
     * @param   mixed  $value  Value to sanitize.
     *
     * @return  mixed
     */
    private function sanitize(mixed $value): mixed
    {
        $method = new \ReflectionMethod(\CWM\Plugin\Schemaorg\Proclaim\Extension\Proclaim::class, 'sanitizeForScriptEmbedding');

        return $method->invoke(null, $value);
    }

    /**
     * @return void
     */
    public function testNeutralizesScriptBreakoutInString(): void
    {
        $malicious = 'Evil</script><script>alert(document.cookie)</script>';

        $result = $this->sanitize($malicious);

        $this->assertStringNotContainsStringIgnoringCase('</script', $result);
        $this->assertStringContainsString('&lt;/script&gt;', $result);
    }

    /**
     * @return void
     */
    public function testRecursivelySanitizesNestedArrays(): void
    {
        $entry = [
            '@type'        => 'CreativeWork',
            'headline'     => 'Fine title',
            'author'       => ['@type' => 'Person', 'name' => '</script><script>alert(1)</script>'],
            'genericField' => [
                ['genericTitle' => 'about', 'genericValue' => '<img src=x onerror=alert(1)>'],
            ],
        ];

        $result = $this->sanitize($entry);

        $this->assertStringNotContainsStringIgnoringCase('</script', $result['author']['name']);
        $this->assertStringNotContainsString('<img', $result['genericField'][0]['genericValue']);
        // Untouched values pass through unchanged.
        $this->assertSame('Fine title', $result['headline']);
        $this->assertSame('CreativeWork', $result['@type']);
    }

    /**
     * @return void
     */
    public function testNonStringScalarsAndNullPassThroughUnchanged(): void
    {
        $this->assertSame(42, $this->sanitize(42));
        $this->assertNull($this->sanitize(null));
        $this->assertTrue($this->sanitize(true));
    }
}
