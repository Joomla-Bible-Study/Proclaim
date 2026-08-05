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
 * The raw-<script>-tag / WebAssetManager-registration checks for #1484 live
 * in ScriptRegistrationCurrencyTest (data-provider-driven across several
 * fields including this one); this file keeps only what's unique to
 * CustomField.
 *
 * @since  __DEPLOY_VERSION__
 */
class CustomFieldTest extends ProclaimTestCase
{
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
