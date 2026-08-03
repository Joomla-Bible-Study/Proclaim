<?php

/**
 * Unit tests for the HTTP range/streaming transport logic exercised by
 * CwmpodcastController::track() (#1424).
 *
 * As of #1446, this logic (resolveRange, isLocalHost, streamLocalFile,
 * streamRemoteFile, resolveSafeRemoteIp, terminate) lives in
 * CwmpodcastTrackHelper (admin/src/Helper), not the controller — a pure
 * extraction, no behavior change (verified: the SSRF guard, curl options,
 * and range-math method bodies are byte-identical before/after the move,
 * modulo `private function` -> `private static function`). This file stays
 * in tests/unit/Site/Controller/ rather than merging into the existing
 * tests/unit/Admin/Helper/CwmpodcastTrackHelperTest.php so the #1446 diff
 * stays reviewable as "code moved, tests repointed" — merging the files is
 * left as a follow-up.
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

use CWM\Component\Proclaim\Administrator\Helper\CwmpodcastTrackHelper;
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
        return new \ReflectionMethod(CwmpodcastTrackHelper::class, 'resolveRange');
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
        return new \ReflectionMethod(CwmpodcastTrackHelper::class, 'isLocalHost');
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
     * Deliberately excludes serveMedia(): its one `return;` (#1446) is a
     * dispatcher hand-off after delegating to streamLocalFile() — which
     * itself always terminates — not a terminal branch that skips
     * terminate(). Widening this list to include it would fail on correct
     * code.
     *
     * @return void
     */
    public function testEveryStreamingBranchTerminatesInsteadOfReturning(): void
    {
        foreach (['streamLocalFile', 'streamRemoteFile'] as $methodName) {
            $method = new \ReflectionMethod(CwmpodcastTrackHelper::class, $methodName);
            $source = file($method->getFileName());
            $body   = implode('', \array_slice(
                $source,
                $method->getStartLine() - 1,
                $method->getEndLine() - $method->getStartLine() + 1
            ));

            $this->assertDoesNotMatchRegularExpression(
                '/^\s*return;\s*$/m',
                $body,
                "{$methodName}() must call self::terminate() (or redirect(), which self-terminates) on every "
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
        $method = new \ReflectionMethod(CwmpodcastTrackHelper::class, 'streamRemoteFile');
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

    // -------------------------------------------------------------------------
    // resolveSafeRemoteIp() — SSRF guard (#1426)
    // -------------------------------------------------------------------------

    /**
     * @return \ReflectionMethod
     */
    private function resolveSafeRemoteIpMethod(): \ReflectionMethod
    {
        return new \ReflectionMethod(CwmpodcastTrackHelper::class, 'resolveSafeRemoteIp');
    }

    /**
     * streamRemoteFile() makes the request itself and relays the response to
     * an unauthenticated caller — unlike the redirect it replaces, which only
     * ever sent the client's own browser/app to fetch the URL. $url is
     * admin-configured (server.params.path), not request-supplied, but a
     * compromised or misconfigured admin account pointing it at an internal
     * host would otherwise turn this endpoint into a standing, public,
     * unauthenticated gateway into the internal network — localhost
     * services, and cloud metadata endpoints in particular (AWS/GCP/Azure
     * all use 169.254.169.254, a link-local address).
     *
     * @return iterable<string, array{0: string}>
     */
    public static function unsafeRemoteHostProvider(): iterable
    {
        yield 'IPv4 loopback' => ['127.0.0.1'];
        yield 'IPv4 loopback, alternate form' => ['127.1.2.3'];
        yield 'RFC1918 10/8' => ['10.0.0.1'];
        yield 'RFC1918 172.16/12' => ['172.16.5.1'];
        yield 'RFC1918 192.168/16' => ['192.168.1.1'];
        yield 'link-local / cloud metadata (AWS/GCP/Azure)' => ['169.254.169.254'];
        yield 'IPv6 loopback' => ['::1'];
        yield 'unresolvable hostname' => ['this-host-does-not-exist.invalid'];
    }

    /**
     * @param   string  $host  A host that must never be fetched
     *
     * @return void
     */
    #[DataProvider('unsafeRemoteHostProvider')]
    public function testUnsafeRemoteHostsAreRejected(string $host): void
    {
        $result = $this->resolveSafeRemoteIpMethod()->invoke(null, $host);

        $this->assertNull($result, "\"{$host}\" must be refused, not resolved to a fetchable IP");
    }

    /**
     * A genuine public IP literal must resolve to itself unchanged — the
     * guard must not be so aggressive it breaks the actual, intended use
     * case (an admin-configured external media host).
     *
     * @return void
     */
    public function testPublicIpLiteralIsAccepted(): void
    {
        // 8.8.8.8 — Google Public DNS. Stable, well-known, unambiguously
        // public; used here only as a fixed literal, never actually
        // contacted by this test.
        $result = $this->resolveSafeRemoteIpMethod()->invoke(null, '8.8.8.8');

        $this->assertSame('8.8.8.8', $result);
    }

    /**
     * streamRemoteFile() must run this check before doing anything curl-
     * related, and must refuse (not fall back to anything) when it fails —
     * an internal host has nothing safe to fall back to. Source-level rather
     * than behavioral: exercising the live-network resolution path in a unit
     * test would make it non-deterministic and environment-dependent.
     *
     * @return void
     */
    public function testStreamRemoteFileGuardsAgainstUnsafeHostsBeforeCurl(): void
    {
        $method = new \ReflectionMethod(CwmpodcastTrackHelper::class, 'streamRemoteFile');
        $source = file($method->getFileName());
        $body   = implode('', \array_slice(
            $source,
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        $guardPos = strpos($body, 'resolveSafeRemoteIp(');
        $this->assertNotFalse($guardPos, 'streamRemoteFile() must call resolveSafeRemoteIp() before fetching $url');

        $failPos = strpos($body, 'fail(', $guardPos);
        $this->assertNotFalse($failPos, 'An unsafe host must be refused via fail(), not redirected or silently allowed');

        $firstCurlCallPos = strpos($body, 'curl_init($url)');
        $this->assertNotFalse($firstCurlCallPos);
        $this->assertGreaterThan(
            $guardPos,
            $firstCurlCallPos,
            'curl_init($url) must never run before the SSRF guard has had a chance to refuse the request'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/CURLOPT_FOLLOWLOCATION\s*=>\s*true/',
            $body,
            'Following redirects would fetch a host this guard never validated — a malicious origin could '
            . '302 to an internal address and bypass the check entirely'
        );
    }
}
