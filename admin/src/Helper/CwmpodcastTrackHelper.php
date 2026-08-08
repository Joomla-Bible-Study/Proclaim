<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Counting/dedupe logic for the podcast download tracking redirect (#1281).
 *
 * The redirect endpoint (cwmpodcast.track) calls record() to count a download,
 * then 302-redirects to the live media. Counting is IAB-style: a given client
 * (IP + User-Agent) counts at most once per rolling 24-hour window, and obvious
 * bots/crawlers are excluded. All logic is best-effort — a counting failure must
 * never block the redirect.
 *
 * @since  10.3.3
 */
class CwmpodcastTrackHelper
{
    /**
     * User-Agent substrings that identify a non-listener (crawler, monitor,
     * link-preview, scripted fetch). Podcast clients/prefetchers — Apple, Spotify,
     * Overcast, AppleCoreMedia, "podcast", etc. — are deliberately NOT listed, so
     * genuine app downloads are counted.
     *
     * @var  string[]
     * @since 10.3.3
     */
    private const BOT_SIGNATURES = [
        'bot', 'crawl', 'spider', 'slurp', 'headless', 'preview',
        'facebookexternalhit', 'facebot', 'embedly', 'quora link preview',
        'curl', 'wget', 'python-requests', 'python-urllib', 'go-http-client',
        'scrapy', 'axios', 'okhttp', 'apache-httpclient',
        'monitor', 'uptime', 'pingdom', 'statuscake', 'ahrefs', 'semrush', 'dataminr',
    ];

    /**
     * Is this User-Agent an excluded bot/crawler rather than a real listener?
     *
     * @param   string  $userAgent  Raw User-Agent header.
     *
     * @return  bool
     *
     * @since   10.3.3
     */
    public static function isBot(string $userAgent): bool
    {
        $ua = strtolower(trim($userAgent));

        if ($ua === '') {
            // Empty UA is a common crawler signature; exclude it.
            return true;
        }

        foreach (self::BOT_SIGNATURES as $needle) {
            if (str_contains($ua, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Stable, non-reversible client fingerprint for 24h dedupe.
     *
     * @param   string  $ip         Client IP address.
     * @param   string  $userAgent  Client User-Agent.
     *
     * @return  string  40-char sha1 hash.
     *
     * @since   10.3.3
     */
    public static function clientHash(string $ip, string $userAgent): string
    {
        return sha1(trim($ip) . '|' . trim($userAgent));
    }

    /**
     * Resolve an episode's permanent RSS <guid>, freezing it on first use.
     *
     * Podcast apps key episode identity off the <guid> string, so it must never
     * change once subscribers have seen it -- and a guid derived from the media
     * URL changes whenever that URL does. This freezes it: the first feed build
     * stores the item's then-current value ($legacyGuid, so existing
     * subscribers see no change) and every later build returns the stored one.
     * Best-effort -- a write failure falls back to emitting $legacyGuid.
     *
     * @param   DatabaseInterface  $db          Database driver.
     * @param   int            $mediaId     Media file ID.
     * @param   string|null        $stored      Current #__bsms_mediafiles.podcast_guid (null if unstamped).
     * @param   string             $legacyGuid  The guid the feed would emit today (direct-URL value).
     *
     * @return  string  The permanent guid to emit.
     *
     * @since   10.3.3
     */
    public static function resolveGuid(DatabaseInterface $db, int $mediaId, ?string $stored, string $legacyGuid): string
    {
        // Already frozen — identity is fixed.
        if ($stored !== null && $stored !== '') {
            return $stored;
        }

        // Nothing to stamp against; emit the legacy value without persisting.
        if ($mediaId <= 0 || $legacyGuid === '') {
            return $legacyGuid;
        }

        // First build for this episode: freeze today's value so future URL /
        // enclosure changes can never move its identity.
        try {
            $db->setQuery(
                $db->createQuery()
                    ->update($db->quoteName('#__bsms_mediafiles'))
                    ->set($db->quoteName('podcast_guid') . ' = :guid')
                    ->where($db->quoteName('id') . ' = :mid')
                    ->where($db->quoteName('podcast_guid') . ' IS NULL')
                    ->bind(':guid', $legacyGuid, ParameterType::STRING)
                    ->bind(':mid', $mediaId, ParameterType::INTEGER)
            )->execute();
        } catch (\Exception $e) {
            // Non-fatal — the feed still emits $legacyGuid this build.
        }

        return $legacyGuid;
    }

    /**
     * Look up a published media file's params for the tracking endpoint, joined
     * with its server's params (needed to resolve the live media URL).
     *
     * Kept here rather than inline in CwmpodcastController so the controller
     * stays limited to HTTP concerns — request parsing, headers, streaming —
     * with data access alongside the rest of this feature's DB logic.
     *
     * @param   DatabaseInterface  $db       Database driver.
     * @param   int            $mediaId  Media file ID.
     *
     * @return  ?object  Row with ->params and ->sparams, or null if not found/unpublished.
     *
     * @since   10.5.4
     */
    public static function findPublishedMedia(DatabaseInterface $db, int $mediaId): ?object
    {
        $query = $db->createQuery()
            ->select($db->quoteName('mf.params'))
            ->select($db->quoteName('sr.params', 'sparams'))
            ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
            ->leftJoin(
                $db->quoteName('#__bsms_servers', 'sr')
                . ' ON ' . $db->quoteName('sr.id') . ' = ' . $db->quoteName('mf.server_id')
            )
            ->where($db->quoteName('mf.id') . ' = :mid')
            ->where($db->quoteName('mf.published') . ' = 1')
            ->bind(':mid', $mediaId, ParameterType::INTEGER);

        return $db->setQuery($query)->loadObject();
    }

    /**
     * Count a download unless this client already counted within the 24h window.
     *
     * Slides the client's window to $nowSql, increments the per-episode counter,
     * and prunes this media's rows older than $cutoffSql (rolling-window cleanup).
     *
     * @param   DatabaseInterface  $db         Database driver.
     * @param   int            $mediaId    Media file ID.
     * @param   string             $clientHash Result of clientHash().
     * @param   string             $nowSql     Current time as an SQL datetime.
     * @param   string             $cutoffSql  now minus 24h as an SQL datetime.
     *
     * @return  bool  True if this hit was counted; false if deduped.
     *
     * @since   10.3.3
     */
    public static function record(
        DatabaseInterface $db,
        int $mediaId,
        string $clientHash,
        string $nowSql,
        string $cutoffSql
    ): bool {
        $logged = $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName('logged'))
                ->from($db->quoteName('#__bsms_podcast_download_log'))
                ->where($db->quoteName('media_id') . ' = :mid')
                ->where($db->quoteName('client_hash') . ' = :hash')
                ->bind(':mid', $mediaId, ParameterType::INTEGER)
                ->bind(':hash', $clientHash, ParameterType::STRING)
        )->loadResult();

        // Already counted within the rolling 24h window → do not count again.
        if ($logged !== null && $logged >= $cutoffSql) {
            return false;
        }

        $row = (object) [
            'media_id'    => $mediaId,
            'client_hash' => $clientHash,
            'logged'      => $nowSql,
        ];

        if ($logged === null) {
            $db->insertObject('#__bsms_podcast_download_log', $row);
        } else {
            // Stale row exists — slide its window forward.
            $db->updateObject('#__bsms_podcast_download_log', $row, ['media_id', 'client_hash']);
        }

        $db->setQuery(
            $db->createQuery()
                ->update($db->quoteName('#__bsms_mediafiles'))
                ->set($db->quoteName('podcast_downloads') . ' = ' . $db->quoteName('podcast_downloads') . ' + 1')
                ->where($db->quoteName('id') . ' = :mid')
                ->bind(':mid', $mediaId, ParameterType::INTEGER)
        )->execute();

        // Bounded-growth cleanup: drop this media's expired dedupe rows.
        $db->setQuery(
            $db->createQuery()
                ->delete($db->quoteName('#__bsms_podcast_download_log'))
                ->where($db->quoteName('media_id') . ' = :mid')
                ->where($db->quoteName('logged') . ' < :cutoff')
                ->bind(':mid', $mediaId, ParameterType::INTEGER)
                ->bind(':cutoff', $cutoffSql, ParameterType::STRING)
        )->execute();

        return true;
    }

    /**
     * Serve the tracked media directly (local disk or a proxied fetch from an
     * external host), honoring Range/HEAD/If-Modified-Since so the URL Apple's
     * crawler sees answers correctly without needing to follow a redirect
     * first (#1424).
     *
     * @param   string  $target           Absolute URL of the live media
     * @param   string  $mimeType         Content-Type to declare
     * @param   string  $host             This site's own configured hostname (from
     *                                    Uri::root(), NOT the incoming Host header --
     *                                    see isLocalHost()), may include a port
     * @param   string  $range            Raw incoming Range header, if any
     * @param   string  $ifModifiedSince  Raw incoming If-Modified-Since header, if any
     * @param   string  $ifNoneMatch      Raw incoming If-None-Match header, if any
     * @param   bool    $headOnly         True for a HEAD request
     *
     * @return  void
     *
     * @throws \Exception
     * @since   __DEPLOY_VERSION__
     */
    public static function serveMedia(
        string $target,
        string $mimeType,
        string $host,
        string $range,
        string $ifModifiedSince,
        string $ifNoneMatch,
        bool $headOnly
    ): void {
        @set_time_limit(0);

        $isLocal = self::isLocalHost($host, $target);

        if ($isLocal) {
            $localPath = self::resolveLocalPath($target);

            if ($localPath !== null) {
                self::streamLocalFile($localPath, $mimeType, $range, $ifModifiedSince, $headOnly);

                return;
            }
        }

        self::streamRemoteFile($target, $range, $ifModifiedSince, $ifNoneMatch, $headOnly);
    }

    /**
     * Map a same-host target URL back to a filesystem path, refusing anything
     * that resolves outside the web root (defense in depth — $target is
     * server-derived, never request-supplied, but this keeps that guarantee
     * even if a future caller changes that).
     *
     * @param   string  $target  Absolute URL known to share this request's host
     *
     * @return  ?string  Absolute filesystem path, or null if it can't be served locally
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function resolveLocalPath(string $target): ?string
    {
        $urlPath = parse_url($target, PHP_URL_PATH);

        if (empty($urlPath)) {
            return null;
        }

        $webRoot = rtrim(JPATH_ROOT, '/');
        $real    = realpath($webRoot . '/' . ltrim(rawurldecode($urlPath), '/'));

        if ($real === false || !str_starts_with($real, $webRoot . '/') || !is_readable($real)) {
            return null;
        }

        return $real;
    }

    /**
     * Stream a local file, honoring Range/If-Modified-Since/HEAD.
     *
     * @param   string  $filePath         Absolute filesystem path (already validated)
     * @param   string  $mimeType         Content-Type to declare
     * @param   string  $rangeHeader      Raw incoming Range header, if any
     * @param   string  $ifModifiedSince  Raw incoming If-Modified-Since header, if any
     * @param   bool    $headOnly         True for a HEAD request
     *
     * @return  void
     *
     * @throws \Exception
     * @since   __DEPLOY_VERSION__
     */
    private static function streamLocalFile(
        string $filePath,
        string $mimeType,
        string $rangeHeader,
        string $ifModifiedSince,
        bool $headOnly
    ): void {
        $size  = filesize($filePath);
        $mtime = filemtime($filePath);

        if ($size === false || $mtime === false) {
            self::fail(404);
        }

        self::flushBuffers();

        if ($ifModifiedSince !== '') {
            $since = strtotime($ifModifiedSince);

            if ($since !== false && $since >= $mtime) {
                http_response_code(304);
                self::terminate();
            }
        }

        [$start, $end, $status] = self::resolveRange($rangeHeader, $size);

        header('Accept-Ranges: bytes');
        header('Content-Type: ' . $mimeType);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');

        if ($status === 416) {
            header('Content-Range: bytes */' . $size);
            http_response_code(416);
            self::terminate();
        }

        http_response_code($status);
        header('Content-Length: ' . ($end - $start + 1));

        if ($status === 206) {
            header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
        }

        if ($headOnly) {
            self::terminate();
        }

        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            self::terminate();
        }

        fseek($handle, $start);
        $remaining = $end - $start + 1;

        while ($remaining > 0 && !feof($handle) && !connection_aborted()) {
            $read = (int) min(8192, $remaining);
            echo fread($handle, $read);
            flush();
            $remaining -= $read;
        }

        fclose($handle);
        self::terminate();
    }

    /**
     * Proxy a fetch from an external media host, relaying its status, the
     * caching/range-relevant headers, and (unless HEAD) its body — streamed
     * in chunks rather than buffered whole, so this stays memory-safe for
     * arbitrarily large episodes.
     *
     * Falls back to a plain redirect if curl isn't available, or if the
     * upstream fetch fails before any response reached the client — matches
     * this endpoint's pre-#1424 behavior in both cases rather than hanging.
     *
     * @param   string  $url              Absolute URL of the external media
     * @param   string  $rangeHeader      Raw incoming Range header, if any
     * @param   string  $ifModifiedSince  Raw incoming If-Modified-Since header, if any
     * @param   string  $ifNoneMatch      Raw incoming If-None-Match header, if any
     * @param   bool    $headOnly         True for a HEAD request
     *
     * @return  void
     *
     * @throws \Exception
     * @since   __DEPLOY_VERSION__
     */
    private static function streamRemoteFile(
        string $url,
        string $rangeHeader,
        string $ifModifiedSince,
        string $ifNoneMatch,
        bool $headOnly
    ): void {
        if (!\function_exists('curl_init')) {
            Factory::getApplication()->redirect($url, 302);
        }

        // SSRF guard (#1426): this method makes the request itself and relays
        // the response to an unauthenticated caller — unlike the redirect it
        // replaces, which only ever sent the *client's* browser/app to fetch
        // $url. $url is admin-configured (server.params.path), not
        // request-supplied, but a compromised/misconfigured admin account
        // could still point it at an internal host (localhost services,
        // cloud metadata, etc.) and turn this endpoint into a standing,
        // public, unauthenticated proxy into the internal network. Refuse
        // outright rather than falling back to anything — an internal host
        // has nothing safe to fall back to.
        $host   = (string) (parse_url($url, PHP_URL_HOST) ?? '');
        $safeIp = $host !== '' ? self::resolveSafeRemoteIp($host) : null;

        if ($safeIp === null) {
            self::fail(404);
        }

        self::flushBuffers();

        $requestHeaders = [];

        if ($rangeHeader !== '') {
            $requestHeaders[] = 'Range: ' . $rangeHeader;
        }

        if ($ifModifiedSince !== '') {
            $requestHeaders[] = 'If-Modified-Since: ' . $ifModifiedSince;
        }

        if ($ifNoneMatch !== '') {
            $requestHeaders[] = 'If-None-Match: ' . $ifNoneMatch;
        }

        $port = parse_url($url, PHP_URL_PORT) ?? (str_starts_with($url, 'https://') ? 443 : 80);

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_NOBODY     => $headOnly,
            // A redirect chain could point anywhere, including back at an
            // internal host this check never sees. Podcast enclosure targets
            // are direct files, so refusing to follow one costs nothing.
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            // Pin resolution to the IP already validated above — curl would
            // otherwise re-resolve the hostname itself at connect time, and
            // DNS could answer differently between the check and the fetch
            // (DNS rebinding) and land on an internal address anyway. The
            // hostname is kept in the URL/Host header/TLS SNI for virtual
            // hosting and certificate validation to keep working correctly.
            CURLOPT_RESOLVE        => [$host . ':' . $port . ':' . $safeIp],
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT        => 0,
            // This endpoint is unauthenticated and unrate-limited, so a
            // slow/stalled upstream host is a resource-exhaustion vector --
            // set_time_limit(0) above plus TIMEOUT=0 mean nothing else
            // bounds how long a worker stays pinned. A LOW_SPEED guard
            // (not a hard total-time cap) aborts only a genuinely stalled
            // transfer, so legitimately large episode files can still
            // stream for as long as they need to. See #1552.
            CURLOPT_LOW_SPEED_LIMIT => 1,
            CURLOPT_LOW_SPEED_TIME  => 60,
            CURLOPT_HEADERFUNCTION  => static function ($curlHandle, string $headerLine): int {
                $trimmed = trim($headerLine);

                if ($trimmed === '') {
                    return \strlen($headerLine);
                }

                if (preg_match('#^HTTP/\S+\s+(\d{3})#', $trimmed, $statusMatch)) {
                    http_response_code((int) $statusMatch[1]);

                    return \strlen($headerLine);
                }

                // Location is relayed too: CURLOPT_FOLLOWLOCATION is off (see
                // above), so without this a redirect from upstream (e.g. an
                // http->https upgrade, or a CDN 301/302-ing to a signed URL --
                // the common case, given the "external host" storage model
                // this method exists for) reached the client as a bare 3xx
                // status with no Location and no body. This restores the
                // pre-#1424 trust model for that case: the *client* follows
                // the redirect itself, same as it always did, not curl -- no
                // new SSRF surface. See #1552.
                if (preg_match('/^(Content-Type|Content-Length|Content-Range|Accept-Ranges|Last-Modified|ETag|Cache-Control|Expires|Location):/i', $trimmed)) {
                    header($trimmed);
                }

                return \strlen($headerLine);
            },
        ]);

        if (!$headOnly) {
            curl_setopt(
                $ch,
                CURLOPT_WRITEFUNCTION,
                static function ($curlHandle, string $data): int {
                    echo $data;
                    flush();

                    return \strlen($data);
                }
            );
        }

        curl_exec($ch);
        $failed = curl_errno($ch) !== 0;
        curl_close($ch);

        // redirect() always terminates the script itself on success, so this
        // only actually returns when curl succeeded (nothing left to do but
        // stop Joomla's own dispatch from continuing below) or failed after
        // already sending part of a response (too late to redirect).
        if ($failed && !headers_sent()) {
            Factory::getApplication()->redirect($url, 302);
        }

        self::terminate();
    }

    /**
     * Stop Joomla's normal dispatch/render cycle from continuing after this
     * helper has already written a response directly (headers and, usually,
     * streamed body) via raw PHP functions.
     *
     * Every method here that answers HTTP requests itself instead of
     * returning control to Joomla's MVC render pipeline — fail() and the
     * streaming methods — must call this (or redirect(), which already
     * does) before returning. Skipping it lets Joomla continue on to render
     * its own page on top of/after the real response: headers set via
     * header() are only queued by PHP until the first byte of output or
     * script end, so any code path that sets headers without ever echoing a
     * body (a HEAD request, a 304, a 416) sends nothing to the client until
     * Joomla's own later render forces the flush — silently replacing the
     * intended response with Joomla's default page. This is a
     * whole-request-lifecycle interaction that no PHPUnit test exercises
     * directly; testEveryStreamingBranchTerminatesInsteadOfReturning in
     * CwmpodcastControllerTest guards it at source level instead.
     *
     * @return  never
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function terminate(): never
    {
        Factory::getApplication()->close();
        exit;
    }

    /**
     * Discard any active output buffers before taking manual control of
     * headers/body — Joomla's own response body abstraction buffers a
     * complete string rather than streaming, which defeats the point here.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function flushBuffers(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    /**
     * Is the media target on this same site, so it can be streamed directly
     * off local disk rather than proxied from an external host?
     *
     * $siteHost must be this site's own configured hostname (Uri::root()),
     * NOT the incoming request's Host header. The Host header is
     * client-supplied and rewritable by any reverse proxy or CDN in front of
     * the site, so it can legitimately differ from the site's real hostname in
     * form (e.g. "site.com" vs "www.site.com") with no attacker involved.
     * Comparing against it misroutes local media down the remote-proxy path,
     * which then self-proxies or 404s depending on what that hostname
     * resolves to.
     *
     * parse_url()'s PHP_URL_HOST never includes a port but $siteHost may, so
     * strip the port from both sides before comparing.
     *
     * @param   string  $siteHost  This site's own configured hostname (may include a port)
     * @param   string  $target    Absolute URL of the live media
     *
     * @return  bool
     *
     * @since   10.5.4
     */
    private static function isLocalHost(string $siteHost, string $target): bool
    {
        $hostOnly   = strtolower(explode(':', $siteHost, 2)[0]);
        $targetHost = strtolower((string) (parse_url($target, PHP_URL_HOST) ?: ''));

        return $hostOnly !== '' && $targetHost !== '' && $hostOnly === $targetHost;
    }

    /**
     * SSRF guard for streamRemoteFile() (#1426): resolve a hostname to an IP
     * that is safe to fetch on this server's behalf, or null if it isn't.
     *
     * "Safe" means a public, routable address — not loopback (127.0.0.1,
     * ::1), not a private/RFC1918 range (10/8, 172.16/12, 192.168/16), and
     * not link-local (169.254.0.0/16, which is where cloud metadata
     * endpoints like AWS's 169.254.169.254 live). streamRemoteFile() proxies
     * the fetch to an unauthenticated caller, so an admin-configured server
     * path pointing here — whether by mistake or a compromised account —
     * would otherwise turn this endpoint into a standing, public gateway
     * into the internal network.
     *
     * Only IPv4 is resolved (gethostbyname()); a host that only has an AAAA
     * record fails safe (returns null) rather than being fetched unchecked.
     *
     * @param   string  $host  Hostname or IP literal to validate
     *
     * @return  ?string  A safe IP to connect to, or null to refuse the fetch entirely
     *
     * @since   10.5.5
     */
    private static function resolveSafeRemoteIp(string $host): ?string
    {
        $flags = \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE;

        if (filter_var($host, \FILTER_VALIDATE_IP) !== false) {
            // A literal IP was given directly — validate it as-is.
            return filter_var($host, \FILTER_VALIDATE_IP, $flags) !== false ? $host : null;
        }

        $ip = gethostbyname($host);

        // gethostbyname() returns its input unchanged when resolution fails.
        if ($ip === $host) {
            return null;
        }

        return filter_var($ip, \FILTER_VALIDATE_IP, $flags) !== false ? $ip : null;
    }

    /**
     * Resolve a Range header against a known content length.
     *
     * @param   string  $rangeHeader  Raw incoming Range header, e.g. "bytes=0-1023"
     * @param   int     $size         Total content length in bytes
     *
     * @return  array{0:int,1:int,2:int}  [start, end, status] — status is 200, 206, or 416
     *
     * @since   10.5.4
     */
    private static function resolveRange(string $rangeHeader, int $size): array
    {
        if ($size <= 0 || $rangeHeader === '' || !preg_match('/^bytes=(\d*)-(\d*)$/', trim($rangeHeader), $matches)) {
            return [0, max(0, $size - 1), 200];
        }

        $rawStart = $matches[1];
        $rawEnd   = $matches[2];

        if ($rawStart === '' && $rawEnd === '') {
            return [0, $size - 1, 200];
        }

        if ($rawStart === '') {
            // Suffix range: the last N bytes.
            $length = (int) $rawEnd;
            $start  = max(0, $size - $length);
            $end    = $size - 1;
        } else {
            $start = (int) $rawStart;
            $end   = $rawEnd === '' ? $size - 1 : min((int) $rawEnd, $size - 1);
        }

        if ($start < 0 || $start > $end || $start >= $size) {
            return [0, $size - 1, 416];
        }

        return [$start, $end, 206];
    }

    /**
     * Send a bare HTTP error status and terminate.
     *
     * @param   int  $code  HTTP status code.
     *
     * @return  never
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function fail(int $code): never
    {
        $app = Factory::getApplication();
        $app->setHeader('Status', (string) $code, true);
        $app->sendHeaders();
        $app->close();
        exit;
    }
}
