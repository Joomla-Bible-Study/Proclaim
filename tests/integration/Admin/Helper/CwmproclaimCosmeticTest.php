<?php

/**
 * Integration tests for the #1590 cosmetic fixes.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1590.
 *
 * Four catch blocks enqueued their message with the type 'worning'. Joomla maps
 * that string to an alert-{type} class, so the message rendered unstyled rather
 * than as a warning. The sibling methods in the same file already used
 * 'warning'.
 *
 * Backup filenames carried Version.php's hardcoded constants, which the release
 * tooling does not touch -- 10.3.1 against a 10.5.5 release.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmproclaimCosmeticTest extends IntegrationTestCase
{
    #[TestDox('No enqueued message uses the misspelled type')]
    public function testNoMisspelledMessageType(): void
    {
        $files = glob(JPATH_ROOT . '/admin/src/**/*.php') ?: [];
        $found = [];

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(JPATH_ROOT . '/admin/src')
        ) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            if (str_contains((string) file_get_contents($file->getPathname()), "'worning'")) {
                $found[] = $file->getPathname();
            }
        }

        $this->assertSame(
            [],
            $found,
            "'worning' matches no Bootstrap alert class, so the message renders unstyled — see #1590"
        );
    }

    #[TestDox('Backup filenames use the installed version, not a hardcoded one')]
    public function testBackupFilenameUsesInstalledVersion(): void
    {
        $source = (string) file_get_contents(JPATH_ROOT . '/admin/src/Lib/Cwmbackup.php');

        $this->assertStringContainsString(
            'CwmproclaimHelper::getVersion()',
            $source,
            'Version.php is not maintained by the release tooling and drifts — see #1590'
        );
        $this->assertStringNotContainsString(
            '$versionHelper->getShortVersion()',
            $source,
            'The hardcoded constants put a stale version into every backup filename'
        );
    }

    #[TestDox('The installed version is reported as a version string')]
    public function testInstalledVersionIsAVersionString(): void
    {
        $this->assertMatchesRegularExpression(
            '/^\d+\.\d+\.\d+/',
            CwmproclaimHelper::getVersion(),
            'A backup filename is built from this'
        );
    }
}
