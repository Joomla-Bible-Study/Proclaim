<?php

/**
 * Unit tests for PopupCodeField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\PopupCodeField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1484: getInput() emitted its code-insertion script as
 * a raw `<script>` tag concatenated into the returned HTML, bypassing
 * WebAssetManager -- unlike VttUploadField.php's correct
 * $wa->useScript()/addScriptOptions() pattern. Now registered via
 * WebAssetManager::addInlineScript() instead.
 *
 * @since  __DEPLOY_VERSION__
 */
class PopupCodeFieldTest extends ProclaimTestCase
{
    private function getClassSource(): string
    {
        return file_get_contents((new \ReflectionClass(PopupCodeField::class))->getFileName());
    }

    public function testDoesNotEmitRawScriptTag(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/[\'"]<script>[\'"]/',
            $this->getClassSource(),
            'PopupCodeField must not concatenate a raw <script> tag into its returned HTML — see #1484'
        );
    }

    public function testRegistersScriptViaWebAssetManager(): void
    {
        $this->assertMatchesRegularExpression(
            '/->addInlineScript\(/',
            $this->getClassSource(),
            'PopupCodeField must register its script via WebAssetManager::addInlineScript() — see #1484'
        );
    }
}
