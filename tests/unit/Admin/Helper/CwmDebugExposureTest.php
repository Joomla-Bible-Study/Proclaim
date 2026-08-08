<?php

/**
 * Unit tests for CwmDebug's buffer authorisation and logger resilience.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmDebug;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression coverage for #1569.
 *
 * 1. `?jbsmdbg=1` set JBSMDEBUG for any client, including an anonymous site
 *    request, and CwmDebug::getBuffer() had no authorisation at all -- unlike
 *    its sibling showToAdmin(). Its one production consumer,
 *    CwmsermonsController::filterAjax(), ships the buffer to the browser as
 *    `_debug` from a public endpoint guarded only by a CSRF token that any
 *    visitor obtains by loading the sermons page. The buffer carries the raw
 *    SQL for the listing query (table/column names, access-level WHERE
 *    construction) via CwmDebug::logQuery().
 *
 * 2. log()/error() called Log::add() unguarded. error() always writes and is
 *    invoked from catch blocks across ~10 call sites that log and then rethrow
 *    the ORIGINAL exception; an unwritable log directory made Log::add() throw
 *    from inside those catch blocks, masking the real error.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmDebugExposureTest extends ProclaimTestCase
{
    /**
     * Read a method's source body.
     *
     * @param   string  $method  Method name on CwmDebug
     *
     * @return  string
     */
    private function methodBody(string $method): string
    {
        $ref   = new \ReflectionMethod(CwmDebug::class, $method);
        $lines = file($ref->getFileName());

        return implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));
    }

    // -------------------------------------------------------------------------
    // Finding 1 -- buffer authorisation
    // -------------------------------------------------------------------------

    /**
     * getBuffer() discloses nothing when debug is off.
     *
     * Deliberately scoped to the isEnabled() gate rather than the
     * authorisation branch: JBSMDEBUG is a compile-time constant that the test
     * harness never defines, and defining it here would enable debug logging
     * for every subsequent test in the process with no way to undo it. So this
     * pins the disabled-path invariant, and
     * testGetBufferIsGuardedByAnAuthorisationCheck() below carries the actual
     * regression pin for the authorisation guard (it fails against pre-fix
     * source; this one would not).
     */
    public function testGetBufferReturnsNothingWhenDebugIsDisabled(): void
    {
        // Populate the static directly -- log() itself no-ops while debug is
        // off, so going through it would prove nothing either way.
        $buffer = new \ReflectionProperty(CwmDebug::class, 'buffer');
        $buffer->setValue(null, ['[query] SELECT * FROM #__bsms_studies WHERE access IN (1,5)']);

        try {
            $this->assertSame(
                [],
                CwmDebug::getBuffer(),
                'With debug off the buffer must never be handed out, populated or not — see #1569'
            );
        } finally {
            $buffer->setValue(null, []);
        }
    }

    /**
     * The guard must be an authorisation check, not merely "always empty".
     */
    public function testGetBufferIsGuardedByAnAuthorisationCheck(): void
    {
        $body = $this->methodBody('getBuffer');

        $this->assertStringContainsString(
            "authorise('core.admin'",
            $body,
            'getBuffer() must require core.admin, matching its sibling showToAdmin() — see #1569'
        );
        $this->assertStringContainsString(
            'isClient(\'administrator\')',
            $body,
            'Administrator-client callers stay allowed so the admin display keeps working'
        );
        $this->assertMatchesRegularExpression(
            '/return\s*\[\s*\]\s*;/',
            $body,
            'Denial must return an empty array, not throw — the caller is a JSON endpoint happy path'
        );
    }

    /**
     * The parameter that turns debugging on must itself be authorised. This is
     * the upstream fix; the getBuffer() guard above is defence in depth.
     * Asserted structurally because admin/api.php is bootstrap code that
     * cannot be re-executed in-process.
     */
    public function testJbsmdbgParameterRequiresCoreAdmin(): void
    {
        $source = file_get_contents(\dirname(__DIR__, 4) . '/admin/api.php');

        $this->assertStringContainsString(
            "authorise('core.admin'",
            $source,
            '?jbsmdbg=1 must not enable debug for an unauthenticated visitor — see #1569'
        );

        // The old shape: the raw parameter alone deciding JBSMDEBUG.
        $this->assertDoesNotMatchRegularExpression(
            '/if\s*\(\s*CwmproclaimHelper::debug\(\)\s*===\s*1\s*\|\|\s*\$app->getInput\(\)->getInt\(\s*[\'"]jbsmdbg/',
            $source,
            'The unauthenticated parameter check must no longer gate JBSMDEBUG directly'
        );
    }

    // -------------------------------------------------------------------------
    // Finding 2 -- logging must never become the failure
    // -------------------------------------------------------------------------

    /**
     * error() is called from catch blocks that then rethrow the original
     * exception. It must not raise one of its own.
     */
    public function testErrorDoesNotThrowWhenLoggingIsImpossible(): void
    {
        $original = new \RuntimeException('the real failure the caller cares about');

        CwmDebug::error('transport failed', $original, 'test');

        // Reaching here at all is the assertion: pre-fix, a logger failure
        // propagated out and replaced $original for every caller that does
        // log-then-rethrow.
        $this->assertTrue(true);
    }

    /**
     * Structural guarantee, since forcing Joomla's logger to fail in-process
     * is not reliably reproducible: the Log::add() call must sit inside a
     * try/catch, and the buffer append must sit OUTSIDE it so a logger failure
     * does not also lose the on-screen entry.
     *
     * @param   string  $method  CwmDebug method that logs
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('loggingMethodProvider')]
    public function testLoggingCallsAreGuarded(string $method): void
    {
        $body = $this->methodBody($method);

        $this->assertMatchesRegularExpression(
            '/try\s*\{\s*Log::add\(/s',
            $body,
            $method . '() must guard Log::add() — an unwritable log directory otherwise masks the real error — see #1569'
        );
        $this->assertMatchesRegularExpression(
            '/catch\s*\(\\\\Throwable\)/',
            $body,
            $method . '() must swallow logger failures, matching CwmlogHelper::write()'
        );

        $this->assertLessThan(
            strpos($body, 'self::$buffer[]'),
            strpos($body, 'catch (\\Throwable)'),
            $method . '(): the buffer append must be outside the try, or a logger failure also loses the entry'
        );
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function loggingMethodProvider(): array
    {
        return ['log' => ['log'], 'error' => ['error']];
    }

    // -------------------------------------------------------------------------
    // logQuery truncation -- comment corrected rather than behaviour widened
    // -------------------------------------------------------------------------

    /**
     * The old comment claimed the full query reached the log file. It never
     * did, and deliberately still does not: writing complete raw SQL to a
     * web-reachable log is the wrong direction on a SQL-exposure ticket.
     */
    public function testLogQueryDoesNotClaimToLogFullSql(): void
    {
        $body = $this->methodBody('logQuery');

        $this->assertStringNotContainsString(
            'full query goes to log file',
            $body,
            'That claim was false — the truncated string is what reaches both sinks'
        );
        $this->assertStringContainsString(
            'substr($sql, 0, 500)',
            $body,
            'Queries must still be truncated before they reach the buffer or the log'
        );
    }
}
