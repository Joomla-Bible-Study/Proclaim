<?php

/**
 * Dark-mode class currency tests for admin form fields.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regression test for #1469: Joomla 5+ admin uses Bootstrap 5.3 dark mode via
 * data-bs-theme="dark". `btn-outline-*` has very low contrast against the
 * dark theme (should be a solid `btn-*` variant), and `text-muted` is a fixed
 * gray that doesn't adapt to the theme (should be `text-body-secondary`).
 *
 * @since  __DEPLOY_VERSION__
 */
class DarkModeCurrencyTest extends ProclaimTestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function fieldFileProvider(): array
    {
        $base = \dirname(__DIR__, 4) . '/admin/src/Field/';

        return [
            'PopupCodeField'            => [$base . 'PopupCodeField.php'],
            'VttUploadField'            => [$base . 'VttUploadField.php'],
            'DescriptionFormatField'    => [$base . 'DescriptionFormatField.php'],
            'IntegrationToggleField'    => [$base . 'IntegrationToggleField.php'],
            'LocationGroupMappingField' => [$base . 'LocationGroupMappingField.php'],
        ];
    }

    #[DataProvider('fieldFileProvider')]
    public function testFieldDoesNotUseDarkModeUnsafeClasses(string $file): void
    {
        $this->assertFileExists($file);

        $source = file_get_contents($file);

        $this->assertStringNotContainsString(
            'btn-outline-',
            $source,
            basename($file) . ' must not use btn-outline-* (low contrast in dark mode) — see #1469'
        );

        $this->assertStringNotContainsString(
            'text-muted',
            $source,
            basename($file) . ' must not use text-muted (not theme-adaptive) — see #1469'
        );
    }
}
