<?php

/**
 * Unit tests for CwmmediafileController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmmediafileController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmmediafileController
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmmediafileControllerTest extends ProclaimTestCase
{
    /**
     * Regression test for #1440: saveChapters() used to run a raw SELECT then
     * UPDATE directly against #__bsms_mediafiles, bypassing Table::store()
     * (and its checkout/check() validation) and the action-log entry every
     * other media file edit produces.
     *
     * @return void
     */
    public function testSaveChaptersRoutesThroughTableSave(): void
    {
        $body = $this->getMethodSource(CwmmediafileController::class, 'saveChapters');

        $this->assertStringNotContainsString(
            'DatabaseInterface::class',
            $body,
            'saveChapters() must not build raw SQL — see #1440'
        );

        $this->assertStringContainsString(
            '->store()',
            $body,
            'saveChapters() must save through Table::store() — see #1440'
        );

        $this->assertStringContainsString(
            '->check()',
            $body,
            'saveChapters() must validate through Table::check() — see #1440'
        );

        $this->assertStringContainsString(
            'isCheckedOut(',
            $body,
            'saveChapters() must respect an existing checkout — see #1440'
        );

        $this->assertStringContainsString(
            'allowEdit(',
            $body,
            'saveChapters() must perform an ACL check — see #1440'
        );

        $this->assertStringContainsString(
            'CwmactionlogHelper::log(',
            $body,
            'saveChapters() must record an action-log entry, matching every other media file edit — see #1440'
        );
    }

    /**
     * Extract a reflected method's source, including its body.
     *
     * @param   class-string  $class   The class to reflect.
     * @param   string        $method  The method name.
     *
     * @return  string
     */
    private function getMethodSource(string $class, string $method): string
    {
        $reflection = new \ReflectionMethod($class, $method);
        $lines      = file($reflection->getFileName());
        $startLine  = $reflection->getStartLine() - 1;
        $endLine    = $reflection->getEndLine();

        return implode('', \array_slice($lines, $startLine, $endLine - $startLine));
    }
}
