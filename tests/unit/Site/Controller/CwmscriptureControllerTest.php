<?php

/**
 * Unit tests for CwmscriptureController
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Controller;

use CWM\Component\Proclaim\Site\Controller\CwmscriptureController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1445: getPassageXHR() built a ~100-line, three-tier
 * provider fallback chain (remote → local same version → local default
 * version → hardcoded KJV) directly in the controller action. That logic
 * moved into CwmscriptureLookupHelper::lookupPassage() — see
 * CwmscriptureLookupHelperTest. getPassageXHR() now only handles the
 * CSRF/reference-presence checks and serializes the helper's result.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmscriptureControllerTest extends ProclaimTestCase
{
    /**
     * Get the source body of a CwmscriptureController method for structural assertions.
     *
     * @param   string  $method
     *
     * @return  string
     */
    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(CwmscriptureController::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    public function testGetPassageXHRHasNoProviderFallbackChain(): void
    {
        $body = self::methodBody('getPassageXHR');

        $this->assertDoesNotMatchRegularExpression('/BibleProviderFactory::/', $body, 'getPassageXHR() must not call the provider factory directly — see #1445');
        $this->assertDoesNotMatchRegularExpression('/Fallback \d:/', $body, 'getPassageXHR() must not contain the old fallback-chain comments/logic — see #1445');
        $this->assertDoesNotMatchRegularExpression('/hasText\(\)/', $body, 'getPassageXHR() must not inspect provider results directly — see #1445');
    }

    public function testGetPassageXHRDelegatesToLookupHelper(): void
    {
        $body = self::methodBody('getPassageXHR');

        $this->assertStringContainsString(
            'CwmscriptureLookupHelper::lookupPassage(',
            $body,
            'getPassageXHR() must delegate the passage lookup to CwmscriptureLookupHelper — see #1445'
        );
    }

    public function testGetPassageXHRStillChecksTokenAndReference(): void
    {
        $body = self::methodBody('getPassageXHR');

        $this->assertStringContainsString('Session::checkToken(', $body, 'getPassageXHR() must still guard against CSRF — see #1445');
        $this->assertStringContainsString('empty($reference)', $body, 'getPassageXHR() must still reject an empty reference — see #1445');
    }
}
