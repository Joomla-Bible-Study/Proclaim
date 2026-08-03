<?php

/**
 * Unit tests for DescriptionFormatField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\DescriptionFormatField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1484: getInput() built its token-insertion script's
 * `<script>` tag as a raw string returned from buildJavaScript(), bypassing
 * WebAssetManager -- unlike VttUploadField.php's correct
 * $wa->useScript()/addScriptOptions() pattern. Now registered via
 * WebAssetManager::addInlineScript() instead.
 *
 * @since  __DEPLOY_VERSION__
 */
class DescriptionFormatFieldTest extends ProclaimTestCase
{
    private function getClassSource(): string
    {
        return file_get_contents((new \ReflectionClass(DescriptionFormatField::class))->getFileName());
    }

    public function testDoesNotEmitRawScriptTag(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/[\'"]<script>[\'"]/',
            $this->getClassSource(),
            'DescriptionFormatField must not return a raw <script> tag string — see #1484'
        );
    }

    public function testRegistersScriptViaWebAssetManager(): void
    {
        $this->assertMatchesRegularExpression(
            '/->addInlineScript\(/',
            $this->getClassSource(),
            'DescriptionFormatField must register its script via WebAssetManager::addInlineScript() — see #1484'
        );
    }
}
