<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmanalyticsController;
use CWM\Component\Proclaim\Administrator\Controller\CwmassetsController;
use CWM\Component\Proclaim\Administrator\Controller\CwmbackupController;
use CWM\Component\Proclaim\Administrator\Controller\CwmlocationwizardController;
use CWM\Component\Proclaim\Administrator\Controller\CwmsetupwizardController;
use CWM\Component\Proclaim\Administrator\Controller\Trait\CwmJsonResponseTrait;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1503 (partial — scope narrowed after investigation):
 * CwmassetsController, CwmlocationwizardController, CwmsetupwizardController,
 * CwmanalyticsController, and CwmbackupController each carried their own
 * private sendJsonResponse() implementation. All five already produced the
 * identical {success, message, data} envelope — they differed only in
 * plumbing (stray-output handling, header guards, termination method) — so
 * consolidating them into one trait is a pure deduplication with no
 * consumer-facing change.
 *
 * The trait body is CwmbackupController's prior implementation, the most
 * complete of the five (stray-output capture + logging, headers_sent()
 * guard, exit(0) rather than $app->close()). registerFatalErrorShutdownHandler()
 * stays Backup-only — it exists for that controller's long-running
 * import/export operations, not the other four's short AJAX actions.
 *
 * The other 3 JSON-response mechanisms in the codebase (Joomla\CMS\Response\JsonResponse,
 * hand-rolled json_encode()+header(), and CWMAddon::outputJson()) were
 * investigated and deliberately NOT touched — see the #1503 issue comment
 * for the evidence: they have inconsistent, endpoint-specific envelope
 * shapes with confirmed JS consumers depending on that specific shape
 * (e.g. build/media_source/js/delete-confirm.es6.js reads flat
 * `data.hasFiles`/`data.files`), so unifying them would be a breaking
 * change with no functional benefit.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmJsonResponseTraitTest extends ProclaimTestCase
{
    private const CONSUMERS = [
        CwmassetsController::class,
        CwmlocationwizardController::class,
        CwmsetupwizardController::class,
        CwmanalyticsController::class,
        CwmbackupController::class,
    ];

    public function testExactlyOneSendJsonResponseDeclarationExistsRepoWide(): void
    {
        $root = \dirname(__DIR__, 4);
        $hits = [];

        // api/ is the third controller tree -- omitting it (as the original
        // version did) let a duplicate sendJsonResponse() reappear there
        // undetected, re-creating exactly what #1503 removed.
        foreach (['admin', 'site', 'api'] as $subdir) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root . '/' . $subdir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php' || str_contains($file->getPathname(), '/vendor/')) {
                    continue;
                }

                if (str_contains(file_get_contents($file->getPathname()), 'function sendJsonResponse')) {
                    $hits[] = str_replace($root . '/', '', $file->getPathname());
                }
            }
        }

        $this->assertSame(
            ['admin/src/Controller/Trait/CwmJsonResponseTrait.php'],
            $hits,
            'sendJsonResponse() must be declared exactly once, in CwmJsonResponseTrait — found in: ' .
                implode(', ', $hits) . ' — see #1503'
        );
    }

    public function testAllFiveFormerlyDuplicatedControllersUseTheTrait(): void
    {
        foreach (self::CONSUMERS as $class) {
            $this->assertContains(
                CwmJsonResponseTrait::class,
                class_uses($class),
                $class . ' must use CwmJsonResponseTrait instead of declaring its own sendJsonResponse() — see #1503'
            );
        }
    }

    public function testTraitProducesTheEstablishedSuccessMessageDataEnvelope(): void
    {
        $reflection = new \ReflectionMethod(CwmJsonResponseTrait::class, 'sendJsonResponse');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        foreach (["'success' => \$success", "'message' => \$message", "'data'    => \$data"] as $key) {
            $this->assertStringContainsString(
                $key,
                $body,
                'The consolidated trait must preserve the exact envelope every prior implementation already ' .
                    'produced, since JS consumers depend on this shape — see #1503'
            );
        }
    }

    public function testCwmbackupControllerRetainsItsOwnFatalErrorShutdownHandler(): void
    {
        $reflection = new \ReflectionClass(CwmbackupController::class);

        $this->assertTrue(
            $reflection->hasMethod('registerFatalErrorShutdownHandler'),
            'registerFatalErrorShutdownHandler() is intentionally Backup-only (long-running import/export ' .
                'operations) and must not have been removed while consolidating sendJsonResponse() — see #1503'
        );
    }
}
