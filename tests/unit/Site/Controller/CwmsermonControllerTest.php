<?php

/**
 * Unit tests for CwmsermonController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Controller;

use CWM\Component\Proclaim\Site\Controller\CwmsermonController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmsermonController
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmsermonControllerTest extends ProclaimTestCase
{
    /**
     * allowEdit() must perform a real ACL check, not unconditionally allow
     * editing. Regression test for #1437: this method used to `return true;`
     * unconditionally, letting any caller edit any sermon regardless of ACL.
     *
     * @return void
     */
    public function testAllowEditPerformsRealAclCheck(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'allowEdit');
        $body       = $this->getMethodSource($reflection);

        $this->assertStringContainsString(
            'authorise(',
            $body,
            'allowEdit() must check permissions via authorise() — see #1437'
        );

        // The bug was a body of exactly `{ return true; }` — no conditional logic
        // at all. Requiring an `if (` proves the authorise() call actually gates
        // something, rather than the method just happening to mention the word.
        $this->assertMatchesRegularExpression(
            '/\bif\s*\(/',
            $body,
            'allowEdit() must contain conditional ACL logic, not an unconditional return — see #1437'
        );
    }

    /**
     * @return void
     */
    public function testAllowEditReturnType(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'allowEdit');
        $this->assertReturnTypeName('bool', $reflection);
    }

    /**
     * Regression test for #1441: commentsEmail() was public, making
     * task=cwmsermon.commentsEmail a directly dispatchable Joomla task with
     * no CSRF token check of its own. It's only meant to be called
     * internally from comment().
     *
     * @return void
     */
    public function testCommentsEmailIsNotPubliclyDispatchable(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'commentsEmail');

        $this->assertFalse(
            $reflection->isPublic(),
            'commentsEmail() must not be public — see #1441'
        );
    }

    /**
     * Extract a reflected method's source, including its body.
     *
     * @param   \ReflectionMethod  $reflection  The method to read.
     *
     * @return  string
     */
    private function getMethodSource(\ReflectionMethod $reflection): string
    {
        $lines     = file($reflection->getFileName());
        $startLine = $reflection->getStartLine() - 1;
        $endLine   = $reflection->getEndLine();

        return implode('', \array_slice($lines, $startLine, $endLine - $startLine));
    }
}
