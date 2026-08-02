<?php

/**
 * Unit tests for CwmpodcastController::resolveRange() (#1424).
 *
 * A 302 redirect can never itself answer a Range request with 206 — Apple's
 * crawler/validators were found to test the enclosure URL directly rather
 * than following it first, so track() now proxies media instead of
 * redirecting. resolveRange() is the pure piece of that logic: it turns an
 * incoming Range header and a known content length into the byte window and
 * HTTP status to serve, and every edge case here is a real interoperability
 * risk if wrong (a player asking for the last N bytes to read ID3 tags, an
 * out-of-bounds seek after a scrub, a malformed header from some client).
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Controller;

use CWM\Component\Proclaim\Site\Controller\CwmpodcastController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @since __DEPLOY_VERSION__
 */
class CwmpodcastControllerTest extends ProclaimTestCase
{
    /**
     * @return \ReflectionMethod
     */
    private function resolveRangeMethod(): \ReflectionMethod
    {
        return new \ReflectionMethod(CwmpodcastController::class, 'resolveRange');
    }

    /**
     * No Range header at all — the ordinary full-file case — must still be
     * declared correctly so the response advertises Accept-Ranges from a
     * plain GET/HEAD, not only when a Range header happens to be present.
     *
     * @return void
     */
    public function testNoRangeHeaderReturnsWholeFileAs200(): void
    {
        [$start, $end, $status] = $this->resolveRangeMethod()->invoke(null, '', 1000);

        $this->assertSame(0, $start);
        $this->assertSame(999, $end);
        $this->assertSame(200, $status);
    }

    /**
     * @return iterable<string, array{0: string, 1: int, 2: array{0:int,1:int,2:int}}>
     */
    public static function rangeProvider(): iterable
    {
        yield 'first 1024 bytes' => ['bytes=0-1023', 43314624, [0, 1023, 206]];
        yield 'open-ended from an offset' => ['bytes=1000-', 2000, [1000, 1999, 206]];
        yield 'suffix range: last 500 bytes' => ['bytes=-500', 2000, [1500, 1999, 206]];
        yield 'suffix range larger than the file: clamps to 0' => ['bytes=-5000', 2000, [0, 1999, 206]];
        yield 'end beyond size clamps to the last byte' => ['bytes=1000-999999', 2000, [1000, 1999, 206]];
        yield 'bytes=0- covers the whole file' => ['bytes=0-', 2000, [0, 1999, 206]];
    }

    /**
     * @param   string  $rangeHeader  Raw incoming Range header
     * @param   int     $size         Known content length
     * @param   array   $expected     [start, end, status]
     *
     * @return void
     */
    #[DataProvider('rangeProvider')]
    public function testValidRangesResolveCorrectly(string $rangeHeader, int $size, array $expected): void
    {
        $result = $this->resolveRangeMethod()->invoke(null, $rangeHeader, $size);

        $this->assertSame($expected, $result);
    }

    /**
     * @return iterable<string, array{0: string, 1: int}>
     */
    public static function unsatisfiableRangeProvider(): iterable
    {
        yield 'start beyond the end of the file' => ['bytes=5000-6000', 2000];
        yield 'start equal to size (no valid last byte)' => ['bytes=2000-2000', 2000];
        yield 'inverted range (start after end)' => ['bytes=500-100', 2000];
    }

    /**
     * A Range that can't be satisfied must produce 416 with the start/end
     * reset to the whole file — RFC 7233 requires the accompanying
     * Content-Range to state the full size ("bytes *\/{size}"), and the
     * caller (streamLocalFile/streamRemoteFile) relies on status===416 alone
     * to decide that, not on start/end, but they must still be sane values
     * rather than something that would corrupt a fallback response.
     *
     * @param   string  $rangeHeader  Raw incoming Range header
     * @param   int     $size         Known content length
     *
     * @return void
     */
    #[DataProvider('unsatisfiableRangeProvider')]
    public function testUnsatisfiableRangesReturn416(string $rangeHeader, int $size): void
    {
        [, , $status] = $this->resolveRangeMethod()->invoke(null, $rangeHeader, $size);

        $this->assertSame(416, $status);
    }

    /**
     * A header that isn't a "bytes=" range at all (some other unit, or
     * garbage) must be treated as if there were no Range header — never
     * crash, never silently miscount bytes.
     *
     * @return void
     */
    public function testMalformedRangeHeaderFallsBackToWholeFile(): void
    {
        foreach (['items=0-5', 'bytes=', 'not-a-range', 'bytes=abc-def'] as $malformed) {
            [$start, $end, $status] = $this->resolveRangeMethod()->invoke(null, $malformed, 1000);

            $this->assertSame(0, $start, "malformed header \"$malformed\" must not shift the start");
            $this->assertSame(999, $end, "malformed header \"$malformed\" must not shift the end");
            $this->assertSame(200, $status, "malformed header \"$malformed\" must fall back to 200");
        }
    }

    /**
     * A zero-byte file (size metadata missing/wrong) must never produce a
     * negative end offset that would corrupt a Content-Range header.
     *
     * @return void
     */
    public function testZeroSizeNeverProducesANegativeEnd(): void
    {
        [$start, $end, $status] = $this->resolveRangeMethod()->invoke(null, '', 0);

        $this->assertSame(0, $start);
        $this->assertGreaterThanOrEqual(0, $end);
        $this->assertSame(200, $status);
    }

    // -------------------------------------------------------------------------
    // isLocalHost() — local vs. external-host detection
    // -------------------------------------------------------------------------

    /**
     * @return \ReflectionMethod
     */
    private function isLocalHostMethod(): \ReflectionMethod
    {
        return new \ReflectionMethod(CwmpodcastController::class, 'isLocalHost');
    }

    /**
     * Caught live against a real dev site running on a non-default port
     * (:8890): parse_url()'s PHP_URL_HOST never includes a port, but the raw
     * Host header does whenever the site isn't on the default port — so
     * comparing them as-is always mismatched, silently sending every local
     * file down the remote-proxy path (which then hit an unrelated curl/TLS
     * failure and fell back to the pre-#1424 redirect, masking the bug
     * behind seemingly-correct behavior). Any site not on :80/:443 hits this.
     *
     * @return void
     */
    public function testPortInHostHeaderDoesNotDefeatLocalDetection(): void
    {
        $isLocal = $this->isLocalHostMethod()->invoke(
            null,
            'j5-dev.local:8890',
            'https://j5-dev.local:8890/images/biblestudy/media/episode.mp3'
        );

        $this->assertTrue($isLocal, 'A matching host with a port must still be detected as local');
    }

    /**
     * @return iterable<string, array{0: string, 1: string, 2: bool}>
     */
    public static function localHostProvider(): iterable
    {
        yield 'same host, both plain' => ['example.com', 'https://example.com/media/ep.mp3', true];
        yield 'same host, request has a port, target does not' => ['example.com:8890', 'https://example.com/media/ep.mp3', true];
        yield 'same host, case-insensitive' => ['Example.COM', 'https://example.com/media/ep.mp3', true];
        yield 'different host is remote' => ['example.com', 'https://cdn.example.net/media/ep.mp3', false];
        yield 'subdomain is not the same host' => ['example.com', 'https://media.example.com/ep.mp3', false];
        yield 'empty request host' => ['', 'https://example.com/media/ep.mp3', false];
        yield 'unparseable target' => ['example.com', 'not a url', false];
    }

    /**
     * @param   string  $requestHost  Raw incoming Host header
     * @param   string  $target       Absolute URL of the live media
     * @param   bool    $expected     Expected local/remote classification
     *
     * @return void
     */
    #[DataProvider('localHostProvider')]
    public function testLocalHostDetection(string $requestHost, string $target, bool $expected): void
    {
        $this->assertSame($expected, $this->isLocalHostMethod()->invoke(null, $requestHost, $target));
    }

    // -------------------------------------------------------------------------
    // terminate() — every response-writing branch must stop Joomla's dispatch
    // -------------------------------------------------------------------------

    /**
     * Caught live, not by a unit test that existed before this one: headers
     * set via header() are only queued by PHP until the first byte of output
     * or script end. Any branch here that sets headers without ever echoing a
     * body (HEAD, a 304, a 416) sent nothing to the client until Joomla's own
     * later page render forced the flush — silently replacing the intended
     * response with Joomla's default page. A bare `return;` is exactly that
     * bug; every terminal branch in streamLocalFile()/streamRemoteFile() must
     * call terminate() (or redirect(), which already self-terminates)
     * instead. This test pins that invariant at the source level so a future
     * edit can't reintroduce a bare return by accident.
     *
     * @return void
     */
    public function testEveryStreamingBranchTerminatesInsteadOfReturning(): void
    {
        foreach (['streamLocalFile', 'streamRemoteFile'] as $methodName) {
            $method = new \ReflectionMethod(CwmpodcastController::class, $methodName);
            $source = file($method->getFileName());
            $body   = implode('', \array_slice(
                $source,
                $method->getStartLine() - 1,
                $method->getEndLine() - $method->getStartLine() + 1
            ));

            $this->assertDoesNotMatchRegularExpression(
                '/^\s*return;\s*$/m',
                $body,
                "{$methodName}() must call \$this->terminate() (or redirect(), which self-terminates) on every "
                . 'branch instead of a bare return — otherwise Joomla continues its normal render afterward and '
                . 'silently replaces the response.'
            );
        }
    }

    // -------------------------------------------------------------------------
    // curl availability — some hosts disable ext-curl entirely
    // -------------------------------------------------------------------------

    /**
     * Some hosts disable ext-curl outright, or block curl_init() specifically
     * via disable_functions in php.ini — common on restrictive shared hosting.
     * streamRemoteFile() is the only method that touches curl (local media
     * never does), and it must check availability before calling any curl_*
     * function, falling back to the pre-#1424 redirect rather than fataling.
     *
     * Source-level rather than behavioral: function_exists() isn't mockable
     * without changing the call convention this codebase uses consistently
     * (\function_exists, leading-backslash global resolution) purely to
     * accommodate one test, so this pins the guard's presence and position
     * instead of simulating curl's absence.
     *
     * @return void
     */
    public function testStreamRemoteFileGuardsCurlAvailability(): void
    {
        $method = new \ReflectionMethod(CwmpodcastController::class, 'streamRemoteFile');
        $source = file($method->getFileName());
        $body   = implode('', \array_slice(
            $source,
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        $guardPos = strpos($body, "function_exists('curl_init')");
        $this->assertNotFalse($guardPos, 'streamRemoteFile() must check curl_init availability before using it');

        $redirectPos = strpos($body, 'redirect(', $guardPos);
        $this->assertNotFalse($redirectPos, 'The curl-unavailable branch must fall back to redirect()');
        $this->assertLessThan(
            200,
            $redirectPos - $guardPos,
            'redirect() must be the immediate fallback for the curl-unavailable branch, not just present somewhere later in the method'
        );

        $firstCurlCallPos = strpos($body, 'curl_init($url)');
        $this->assertNotFalse($firstCurlCallPos);
        $this->assertGreaterThan(
            $redirectPos,
            $firstCurlCallPos,
            'The actual curl_init($url) call must come after the availability guard, never before it'
        );
    }
}
