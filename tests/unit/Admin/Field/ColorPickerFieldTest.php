<?php

/**
 * Unit tests for ColorPickerField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\ColorPickerField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for ColorPickerField
 *
 * @since  __DEPLOY_VERSION__
 */
class ColorPickerFieldTest extends ProclaimTestCase
{
    /**
     * Regression test for #1458: when the stored value isn't a recognized
     * CSS named color, $hexValue was derived from the raw field value and
     * interpolated unescaped into two value="..." attributes (the
     * <input type="color"> and the hex text input).
     *
     * @return void
     */
    public function testHexValueIsEscapedBeforeInterpolation(): void
    {
        $reflection = new \ReflectionMethod(ColorPickerField::class, 'getInput');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertMatchesRegularExpression(
            '/htmlspecialchars\(\$hexValue/',
            $body,
            'getInput() must escape $hexValue before interpolating into value="..." — see #1458'
        );
    }

    /**
     * Regression test for #1484: buildStyles()/buildJavaScript() returned
     * raw <style>/<script> tags concatenated into getInput()'s HTML,
     * bypassing WebAssetManager. Now registered via
     * WebAssetManager::addInlineStyle()/addInlineScript() instead.
     *
     * @return void
     */
    public function testStylesAndScriptAreRegisteredViaWebAssetManagerNotRawTags(): void
    {
        $reflection = new \ReflectionClass(ColorPickerField::class);
        $source     = file_get_contents($reflection->getFileName());

        $this->assertDoesNotMatchRegularExpression(
            '/[\'"]<style>/',
            $source,
            'ColorPickerField must not concatenate a raw <style> tag — see #1484'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/[\'"]<script>[\'"]/',
            $source,
            'ColorPickerField must not concatenate a raw <script> tag — see #1484'
        );

        $this->assertMatchesRegularExpression(
            '/->addInlineStyle\(/',
            $source,
            'ColorPickerField must register its styles via WebAssetManager::addInlineStyle() — see #1484'
        );

        $this->assertMatchesRegularExpression(
            '/->addInlineScript\(/',
            $source,
            'ColorPickerField must register its script via WebAssetManager::addInlineScript() — see #1484'
        );
    }

    /**
     * Confirms the registered CSS/JS content (selectors, function names,
     * the $id interpolation) survived the WebAssetManager migration
     * unchanged -- checked against the method source directly, since the
     * test suite's CLI application has no Document/WebAssetManager to
     * invoke the real registration against.
     *
     * @return void
     */
    public function testStylesAndScriptContentIsUnchanged(): void
    {
        $stylesReflection = new \ReflectionMethod(ColorPickerField::class, 'registerStyles');
        $stylesLines      = file($stylesReflection->getFileName());
        $stylesBody       = implode(
            '',
            \array_slice($stylesLines, $stylesReflection->getStartLine() - 1, $stylesReflection->getEndLine() - $stylesReflection->getStartLine() + 1)
        );

        $this->assertStringContainsString('.colorpicker-swatch:hover', $stylesBody);
        $this->assertStringContainsString('.colorpicker-selected', $stylesBody);

        $jsReflection = new \ReflectionMethod(ColorPickerField::class, 'registerJavaScript');
        $jsLines      = file($jsReflection->getFileName());
        $jsBody       = implode(
            '',
            \array_slice($jsLines, $jsReflection->getStartLine() - 1, $jsReflection->getEndLine() - $jsReflection->getStartLine() + 1)
        );

        $this->assertStringContainsString('function toggleColorPalette_{$id}()', $jsBody);
        $this->assertStringContainsString('function selectColor_{$id}(name, hex)', $jsBody);
        $this->assertStringContainsString('function filterColors_{$id}(query)', $jsBody);
    }
}
