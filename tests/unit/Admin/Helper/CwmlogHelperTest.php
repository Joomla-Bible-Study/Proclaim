<?php

/**
 * Unit tests for Proclaim's operational logging registration and severities.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmlogHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Log\Log;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @since  __DEPLOY_VERSION__
 */
class CwmlogHelperTest extends ProclaimTestCase
{
    /**
     * @return array<string, array{0: string, 1: int}>
     */
    public static function levelProvider(): array
    {
        return [
            'debug'   => ['debug', Log::DEBUG],
            'info'    => ['info', Log::INFO],
            'warning' => ['warning', Log::WARNING],
            'error'   => ['error', Log::ERROR],
        ];
    }

    /**
     * Each helper maps to a distinct core severity, so operators can filter on it.
     */
    #[DataProvider('levelProvider')]
    public function testSeverityMethodExists(string $method, int $priority): void
    {
        $this->assertTrue(
            method_exists(CwmlogHelper::class, $method),
            "CwmlogHelper::{$method}() should exist"
        );

        $ref    = new \ReflectionMethod(CwmlogHelper::class, $method);
        $lines  = \array_slice(
            file($ref->getFileName()),
            $ref->getStartLine() - 1,
            $ref->getEndLine() - $ref->getStartLine() + 1
        );
        $source = implode('', $lines);

        $constant = array_search($priority, [
            'Log::DEBUG'   => Log::DEBUG,
            'Log::INFO'    => Log::INFO,
            'Log::WARNING' => Log::WARNING,
            'Log::ERROR'   => Log::ERROR,
        ], true);

        $this->assertStringContainsString(
            $constant,
            $source,
            "CwmlogHelper::{$method}() should log at {$constant}"
        );
    }

    /**
     * Writing must register a logger first.
     *
     * Log::addLogEntry() dispatches only to loggers matching the entry's category,
     * so an unregistered category is silently discarded — the entry is built and
     * dropped. Without this call the whole helper would be a no-op that looks like
     * it works.
     */
    public function testWriteRegistersALogger(): void
    {
        $ref    = new \ReflectionMethod(CwmlogHelper::class, 'write');
        $lines  = \array_slice(
            file($ref->getFileName()),
            $ref->getStartLine() - 1,
            $ref->getEndLine() - $ref->getStartLine() + 1
        );
        $source = implode('', $lines);

        $this->assertStringContainsString('self::register()', $source);
    }

    /**
     * Registration happens once per request, not per entry.
     */
    public function testRegistrationIsGuarded(): void
    {
        $ref    = new \ReflectionMethod(CwmlogHelper::class, 'register');
        $lines  = \array_slice(
            file($ref->getFileName()),
            $ref->getStartLine() - 1,
            $ref->getEndLine() - $ref->getStartLine() + 1
        );
        $source = implode('', $lines);

        $this->assertStringContainsString('self::$registered', $source);
        $this->assertStringContainsString('Log::addLogger', $source);
    }

    /**
     * The category is namespaced so a site can route it to its own file.
     */
    public function testCategoryAndFileAreNamespaced(): void
    {
        $this->assertSame('com_proclaim', CwmlogHelper::CATEGORY_GENERAL);
        $this->assertSame('com_proclaim.api', CwmlogHelper::CATEGORY_API);
        $this->assertSame('com_proclaim.bible', CwmlogHelper::CATEGORY_BIBLE);
        $this->assertSame('com_proclaim.debug', CwmlogHelper::CATEGORY_DEBUG);
    }

    /**
     * Logging must never break a request — every write is guarded.
     */
    public function testFailuresAreSwallowed(): void
    {
        foreach (['write', 'register'] as $method) {
            $ref    = new \ReflectionMethod(CwmlogHelper::class, $method);
            $lines  = \array_slice(
                file($ref->getFileName()),
                $ref->getStartLine() - 1,
                $ref->getEndLine() - $ref->getStartLine() + 1
            );
            $source = implode('', $lines);

            $this->assertStringContainsString(
                'catch (\Throwable)',
                $source,
                "CwmlogHelper::{$method}() must not let a logging failure escape"
            );
        }
    }

    /**
     * Registration must live in exactly one place.
     *
     * admin/api.php used to call Log::addLogger() itself. Registering a category
     * twice gives it two loggers and writes every entry twice, so that file now
     * delegates here. If it ever registers its own again, this fails.
     */
    public function testApiPhpDelegatesRegistration(): void
    {
        $apiPhp = (string) file_get_contents(\dirname(__DIR__, 4) . '/admin/api.php');

        $this->assertStringNotContainsString(
            'Log::addLogger',
            $apiPhp,
            'admin/api.php must delegate to CwmlogHelper, not register its own loggers'
        );
        $this->assertStringContainsString('CwmlogHelper::register()', $apiPhp);
    }

    /**
     * Only the helper registers loggers for the component.
     *
     * ⚠️ One exception, and it has to stay one. `proclaim.script.php` registers
     * its own install logger because it runs in preflight, where the component's
     * classes are not reliably autoloadable — the same reason that method
     * registers the scripture namespace by hand a few lines earlier. Reaching
     * for CwmlogHelper there would be a bet on load order.
     *
     * Anything else that appears in this list is centralisation leaking away.
     */
    public function testHelperIsTheOnlyRegistrar(): void
    {
        $root  = \dirname(__DIR__, 4);
        $hits  = [];

        foreach (['admin', 'site', 'api', 'modules'] as $dir) {
            $path = $root . '/' . $dir;

            if (!is_dir($path)) {
                continue;
            }

            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

            foreach ($it as $file) {
                if ($file->getExtension() !== 'php' || str_contains($file->getPathname(), '/vendor/')) {
                    continue;
                }

                if (str_contains((string) file_get_contents($file->getPathname()), 'Log::addLogger')) {
                    $hits[] = str_replace($root . '/', '', $file->getPathname());
                }
            }
        }

        sort($hits);

        $this->assertSame(
            ['admin/proclaim.script.php', 'admin/src/Helper/CwmlogHelper.php'],
            $hits,
            'Logger registration belongs in CwmlogHelper. The manifest script is the one exception, because it '
            . 'runs before the component is autoloadable; anything else here should move into the helper.'
        );
    }

    /**
     * Routine severities are gated, so the log does not grow with traffic.
     *
     * The API plugin emits a DEBUG line on every request. Ungated, that is one
     * line per request saying the same thing — measured at 10 lines per 10
     * requests before this guard existed.
     */
    public function testRoutineLevelsAreGatedBehindDebugMode(): void
    {
        foreach (['debug', 'info'] as $method) {
            $ref    = new \ReflectionMethod(CwmlogHelper::class, $method);
            $lines  = \array_slice(
                file($ref->getFileName()),
                $ref->getStartLine() - 1,
                $ref->getEndLine() - $ref->getStartLine() + 1
            );
            $source = implode('', $lines);

            $this->assertStringContainsString(
                'isDebugEnabled()',
                $source,
                "CwmlogHelper::{$method}() must be gated behind debug mode"
            );
        }
    }

    /**
     * Problems are never gated — an error must record whether or not anyone
     * switched debug on.
     */
    public function testProblemLevelsAreNotGated(): void
    {
        foreach (['warning', 'error'] as $method) {
            $ref    = new \ReflectionMethod(CwmlogHelper::class, $method);
            $lines  = \array_slice(
                file($ref->getFileName()),
                $ref->getStartLine() - 1,
                $ref->getEndLine() - $ref->getStartLine() + 1
            );
            $source = implode('', $lines);

            $this->assertStringNotContainsString(
                'isDebugEnabled()',
                $source,
                "CwmlogHelper::{$method}() must record regardless of debug mode"
            );
        }
    }

    /**
     * The production priority mask records problems and nothing routine.
     */
    public function testProductionPrioritiesExcludeRoutineLevels(): void
    {
        $mask = (new \ReflectionClass(CwmlogHelper::class))->getConstant('PRODUCTION_PRIORITIES');

        foreach (['ERROR' => Log::ERROR, 'WARNING' => Log::WARNING, 'CRITICAL' => Log::CRITICAL] as $name => $bit) {
            $this->assertSame($bit, $mask & $bit, "Production logging must include {$name}");
        }

        foreach (['DEBUG' => Log::DEBUG, 'INFO' => Log::INFO, 'NOTICE' => Log::NOTICE] as $name => $bit) {
            $this->assertSame(0, $mask & $bit, "Production logging must exclude {$name} — it grows with traffic");
        }
    }
}
