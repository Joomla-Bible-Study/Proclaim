<?php

/**
 * WebAssetManager script-registration currency tests for admin form fields.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regression test for #1484: several fields built their JS as a raw
 * `<script>...</script>` string concatenated into getInput()'s returned HTML,
 * bypassing WebAssetManager -- unlike VttUploadField.php's correct
 * $wa->useScript()/addScriptOptions() pattern. All are now registered via
 * WebAssetManager::addInlineScript() instead.
 *
 * Consolidates what were four near-identical two-assertion test files
 * (PopupCodeFieldTest, DescriptionFormatFieldTest, CustomFieldTest, and part
 * of ColorPickerFieldTest) into one data-provider test, following the same
 * pattern DarkModeCurrencyTest already uses for a different #1469 sweep.
 *
 * ColorPickerField's own test also covers its `<style>` registration (not
 * just `<script>`) and LocationGroupMappingField's scopes the check to one
 * specific method rather than the whole class -- both stayed in their own
 * files since folding them in here would either drop or weaken what they
 * check.
 *
 * @since  __DEPLOY_VERSION__
 */
class ScriptRegistrationCurrencyTest extends ProclaimTestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function fieldClassProvider(): array
    {
        return [
            'PopupCodeField'         => [\CWM\Component\Proclaim\Administrator\Field\PopupCodeField::class],
            'DescriptionFormatField' => [\CWM\Component\Proclaim\Administrator\Field\DescriptionFormatField::class],
            'CustomField'            => [\CWM\Component\Proclaim\Administrator\Field\CustomField::class],
        ];
    }

    #[DataProvider('fieldClassProvider')]
    public function testDoesNotEmitRawScriptTag(string $class): void
    {
        $source = file_get_contents((new \ReflectionClass($class))->getFileName());

        // Broader than the original quote-delimited '<script>' literal check:
        // '<script' followed by whitespace or '>' also catches a raw tag
        // reintroduced with attributes ('<script type="module">') or via a
        // differently-quoted string, which the tighter pattern waved through.
        $this->assertDoesNotMatchRegularExpression(
            '/[\'"]<script[\s>]/',
            $source,
            $class . ' must not concatenate a raw <script> tag into its returned HTML — see #1484'
        );
    }

    #[DataProvider('fieldClassProvider')]
    public function testRegistersScriptViaWebAssetManager(string $class): void
    {
        $source = file_get_contents((new \ReflectionClass($class))->getFileName());

        $this->assertMatchesRegularExpression(
            '/->addInlineScript\(/',
            $source,
            $class . ' must register its script via WebAssetManager::addInlineScript() — see #1484'
        );
    }

    /**
     * The two checks above prove a registration method exists -- not that
     * getInput() still calls it. Without this wiring assertion, deleting the
     * `$this->registerJavaScript();` line ships the field with no JS at all
     * while every other test stays green.
     */
    #[DataProvider('fieldClassProvider')]
    public function testGetInputWiresUpTheScriptRegistration(string $class): void
    {
        $ref   = new \ReflectionMethod($class, 'getInput');
        $lines = file($ref->getFileName());
        $body  = implode(
            '',
            \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1)
        );

        $this->assertStringContainsString(
            '$this->registerJavaScript();',
            $body,
            $class . '::getInput() must call registerJavaScript() so the script is actually registered — see #1484'
        );
    }
}
