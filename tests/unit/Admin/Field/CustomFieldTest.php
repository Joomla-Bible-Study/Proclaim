<?php

/**
 * Unit tests for CustomField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\CustomField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1484: buildJavaScript() returned a raw
 * <script>...</script> string concatenated into getInput()'s HTML,
 * bypassing WebAssetManager. Now registered via
 * WebAssetManager::addInlineScript() instead.
 *
 * @since  __DEPLOY_VERSION__
 */
class CustomFieldTest extends ProclaimTestCase
{
    public function testDoesNotEmitRawScriptTag(): void
    {
        $source = file_get_contents((new \ReflectionClass(CustomField::class))->getFileName());

        $this->assertDoesNotMatchRegularExpression(
            '/[\'"]<script>[\'"]/',
            $source,
            'CustomField must not concatenate a raw <script> tag — see #1484'
        );
    }

    public function testRegistersScriptViaWebAssetManager(): void
    {
        $source = file_get_contents((new \ReflectionClass(CustomField::class))->getFileName());

        $this->assertMatchesRegularExpression(
            '/->addInlineScript\(/',
            $source,
            'CustomField must register its script via WebAssetManager::addInlineScript() — see #1484'
        );
    }

    /**
     * Confirms the actual JS content (event delegation, code insertion,
     * modal close logic) survived the WebAssetManager migration unchanged.
     *
     * @return void
     */
    public function testScriptContentIsUnchanged(): void
    {
        $reflection = new \ReflectionMethod(CustomField::class, 'registerJavaScript');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertStringContainsString("closest('.custom-code-btn')", $body);
        $this->assertStringContainsString("closest('.custom-code-insert')", $body);
        $this->assertStringContainsString('bootstrap.Modal.getInstance(modal)', $body);
    }
}
