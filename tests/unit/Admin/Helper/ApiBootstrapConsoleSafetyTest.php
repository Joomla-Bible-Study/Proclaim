<?php

/**
 * Contract test: admin/api.php must survive being required off the web
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use PHPUnit\Framework\TestCase;

/**
 * `admin/api.php` is required from plugin constructors — plg_task_proclaim and
 * plg_finder_proclaim both do it — and plugins are imported in every
 * application, including the console.
 *
 * com_scheduler imports every task plugin merely to list the routines it
 * offers, so `php cli/joomla.php scheduler:run` executed this file with a
 * ConsoleApplication. It called `getDocument()`, which ConsoleApplication does
 * not have, and the process died before any task ran — Joomla's own tasks
 * included, on any site with Proclaim installed (#1787).
 *
 * Asserted at source level because the failure needs a console application to
 * reproduce, which a unit test has no way to stand up.
 *
 * @since  __DEPLOY_VERSION__
 */
class ApiBootstrapConsoleSafetyTest extends TestCase
{
    /**
     * Does this line call getDocument() unconditionally?
     *
     * api.php is a procedural bootstrap, so column zero means "runs in every
     * application". Comments are excluded — the explanation of this very rule
     * names the method, and flagging prose would make the test unmaintainable.
     *
     * @param   string  $line  A line of source.
     *
     * @return  bool
     */
    private static function isUnguardedCall(string $line): bool
    {
        $trimmed = ltrim($line);

        if ($trimmed === '' || str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*')) {
            return false;
        }

        return str_contains($line, 'getDocument()') && !str_starts_with($line, ' ');
    }

    /**
     * @return string
     */
    private static function source(): string
    {
        return (string) file_get_contents(\dirname(__DIR__, 4) . '/admin/api.php');
    }

    /**
     * Every web-only call has to sit inside a guard.
     *
     * Checked by indentation: this file is a procedural bootstrap, so anything
     * at column zero runs unconditionally. A `getDocument()` there is the bug.
     */
    public function testNoUnconditionalDocumentAccess(): void
    {
        $offenders = [];

        foreach (explode("\n", self::source()) as $number => $line) {
            if (self::isUnguardedCall($line)) {
                $offenders[] = \sprintf('api.php:%d  %s', $number + 1, trim($line));
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "getDocument() at the top level of api.php runs in every application. ConsoleApplication does not "
            . 'have it, and this file is reached from plugin constructors, so an unguarded call breaks '
            . '`joomla.php scheduler:run` for every task on the site.'
        );
    }

    /** And the guard has to be the one that actually implies a document. */
    public function testTheGuardIsTheInterfaceThatDeclaresGetDocument(): void
    {
        $this->assertStringContainsString(
            'instanceof CMSWebApplicationInterface',
            self::source(),
            'CMSWebApplicationInterface is what declares getDocument(). ConsoleApplication implements '
            . 'CMSApplicationInterface but not that one, so checking the wrong interface would still break.'
        );
    }

    /**
     * Guards the scan above. A test that finds nothing proves nothing unless it
     * is known to find something.
     */
    public function testTheDetectorWorks(): void
    {
        $this->assertTrue(
            self::isUnguardedCall('$wa = $app->getDocument()->getWebAssetManager();'),
            'The detector no longer recognises the unguarded call it was written for.'
        );
        $this->assertFalse(
            self::isUnguardedCall('    $wa = $app->getDocument()->getWebAssetManager();'),
            'The detector reports a guarded call as an offender.'
        );
        $this->assertFalse(
            self::isUnguardedCall('// ... is what declares getDocument();'),
            'The detector must not flag prose. The explanation of this very rule mentions the call.'
        );
    }
}
