<?php

/**
 * Unit tests for LocationGroupMappingField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\LocationGroupMappingField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1466: getInput() ran the main location query (which
 * already computes a per-location message_count via a LEFT JOIN + COUNT +
 * GROUP BY), then called CwmlocationHelper::getLocationUsage($locId) inside
 * the per-row loop -- an extra COUNT(*) query per location re-deriving the
 * exact same number the main query already had.
 *
 * @since  __DEPLOY_VERSION__
 */
class LocationGroupMappingFieldTest extends ProclaimTestCase
{
    /**
     * @return string
     */
    private function getInputMethodBody(): string
    {
        $reflection = new \ReflectionMethod(LocationGroupMappingField::class, 'getInput');
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    /**
     * @return void
     */
    public function testGetInputDoesNotRunPerRowUsageQuery(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/CwmlocationHelper::getLocationUsage/',
            $this->getInputMethodBody(),
            'getInput() must not re-query a count per location the main query already computed — see #1466'
        );
    }

    /**
     * @return void
     */
    public function testGetInputUsesMessageCountFromTheMainQuery(): void
    {
        $this->assertMatchesRegularExpression(
            '/\$location->message_count/',
            $this->getInputMethodBody(),
            'getInput() must read message_count from the already-computed main query result — see #1466'
        );
    }

    /**
     * Regression test for #1484: the serialisation script was returned as a
     * raw <script>...</script> string concatenated into getInput()'s HTML,
     * bypassing WebAssetManager. It's now registered via
     * WebAssetManager::addInlineScript() instead, with the JS body itself
     * unchanged (verified byte-for-byte against the pre-fix heredoc).
     *
     * @return void
     */
    public function testSerialisationScriptIsRegisteredViaWebAssetManagerNotRawTag(): void
    {
        $reflection = new \ReflectionMethod(LocationGroupMappingField::class, 'registerSerializationScript');
        $lines      = file($reflection->getFileName());
        $methodBody = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertDoesNotMatchRegularExpression(
            '/[\'"]<script>[\'"]/',
            $methodBody,
            'LocationGroupMappingField must not concatenate a raw <script> tag — see #1484'
        );

        $this->assertMatchesRegularExpression(
            '/->addInlineScript\(/',
            $methodBody,
            'LocationGroupMappingField must register its script via WebAssetManager::addInlineScript() — see #1484'
        );
    }

    /**
     * Confirms the JS body itself (field name/id interpolation via
     * json_encode(), the checkbox-array regex, the disable-on-submit step)
     * survived the WebAssetManager migration unchanged. The test suite's CLI
     * application has no Document/WebAssetManager to invoke
     * registerSerializationScript() against, so this asserts on the heredoc
     * as written in source rather than on a live registered asset -- still
     * enough to catch a mangled escape sequence or broken interpolation,
     * which a check on method names alone would not.
     *
     * @return void
     */
    public function testSerialisationScriptBodyMatchesExpectedContentExactly(): void
    {
        $reflection = new \ReflectionMethod(LocationGroupMappingField::class, 'registerSerializationScript');
        $lines      = file($reflection->getFileName());
        $methodBody = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        // Field-name/id interpolation via the JSON-encoded PHP variables.
        $this->assertStringContainsString('document.getElementById({$jsFieldId})', $methodBody);
        $this->assertStringContainsString('existing.name = {$jsFieldName};', $methodBody);

        // The checkbox-array parsing regex, byte-for-byte as before the migration.
        $this->assertStringContainsString('cb.name.match(/\[(\d+)\]\[\]$/)', $methodBody);

        // The submit-time serialisation/disable steps, unchanged.
        $this->assertStringContainsString("data-cwm-location-mapping", $methodBody);
        $this->assertStringContainsString('el.disabled = true;', $methodBody);
    }
}
