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

use CWM\Component\Proclaim\Administrator\Controller\CwmbackupController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regression test for #1450: finalizeExportXHR(), postRestoreStepXHR(), and
 * finalizeImportXHR() each inlined an identical register_shutdown_function()
 * fatal-error-catching block. Extracted into a single private
 * registerFatalErrorShutdownHandler(), called from all three. Also pins the
 * FormController → BaseController base-class change (this controller is a
 * pure AJAX method bag; it never calls save/edit/cancel/getForm()).
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmbackupControllerTest extends ProclaimTestCase
{
    private static function fileSource(): string
    {
        $reflection = new \ReflectionClass(CwmbackupController::class);

        return file_get_contents($reflection->getFileName());
    }

    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(CwmbackupController::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    public function testExtendsBaseControllerNotFormController(): void
    {
        $reflection = new \ReflectionClass(CwmbackupController::class);

        $this->assertSame(
            \Joomla\CMS\MVC\Controller\BaseController::class,
            $reflection->getParentClass()->getName(),
            'CwmbackupController is a pure AJAX method bag (no save/edit/cancel/getForm) — see #1450'
        );
    }

    public function testRegisterShutdownFunctionAppearsExactlyOnce(): void
    {
        $count = substr_count(self::fileSource(), 'register_shutdown_function(');

        $this->assertSame(
            1,
            $count,
            'register_shutdown_function() must be called from a single shared helper, not duplicated per XHR method — see #1450'
        );
    }

    #[DataProvider('xhrMethodsProvider')]
    public function testXhrMethodDelegatesToSharedShutdownHandler(string $method): void
    {
        $body = self::methodBody($method);

        $this->assertStringContainsString(
            '$this->registerFatalErrorShutdownHandler();',
            $body,
            $method . '() must register the fatal-error handler via the shared helper — see #1450'
        );
        $this->assertStringNotContainsString('register_shutdown_function(', $body);
    }

    public static function xhrMethodsProvider(): array
    {
        return [
            'finalizeExportXHR'  => ['finalizeExportXHR'],
            'postRestoreStepXHR' => ['postRestoreStepXHR'],
            'finalizeImportXHR'  => ['finalizeImportXHR'],
        ];
    }
}
