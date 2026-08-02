<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Site\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmmime;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\CwmpodcastTrackHelper;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Podcast controller — serves JSON endpoints for Podcasting 2.0.
 *
 * @since  10.3.0
 */
class CwmpodcastController extends BaseController
{
    /**
     * Serve JSON chapters for a media file.
     *
     * URL: index.php?option=com_proclaim&task=cwmpodcast.chapters&media_id={id}
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function chapters(): void
    {
        $mediaId = $this->input->getInt('media_id', 0);

        if ($mediaId <= 0) {
            $this->sendJson(['version' => '1.2.0', 'chapters' => []], 400);

            return;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('id') . ' = ' . $mediaId)
            ->where($db->quoteName('published') . ' = 1');

        $db->setQuery($query);
        $rawParams = $db->loadResult();

        if (empty($rawParams)) {
            $this->sendJson(['version' => '1.2.0', 'chapters' => []], 404);

            return;
        }

        $params   = new Registry($rawParams);
        $chapters = $params->get('chapters', []);

        $output = ['version' => '1.2.0', 'chapters' => []];

        foreach ($chapters as $chapter) {
            $chapter = (object) $chapter;
            $time    = $chapter->time ?? '';
            $label   = $chapter->label ?? '';

            if (empty($time) || empty($label)) {
                continue;
            }

            $output['chapters'][] = [
                'startTime' => self::timeToSeconds($time),
                'title'     => $label,
            ];
        }

        $this->sendJson($output);
    }

    /**
     * Download tracking endpoint (#1281, byte-range support #1424).
     *
     * The podcast feed points <enclosure> URLs here (when the podcast has
     * track_downloads enabled) instead of at the live media. This counts the
     * download IAB-style (one per client per 24h, bots excluded), then serves
     * the live media — which may be on this server OR an external host (the
     * common case, given storage limits). Counting is best-effort and never
     * blocks playback.
     *
     * A 302 redirect can never itself answer a Range request with 206 —
     * redirects have no body — and Apple's crawler/validators were found to
     * test the enclosure URL directly rather than following it first. So this
     * proxies the response (honoring Range/HEAD/If-Modified-Since) instead of
     * redirecting, for both local files and external hosts.
     *
     * URL: index.php?option=com_proclaim&task=cwmpodcast.track&media_id={id}
     * URL: /component/proclaim/podcast-download/{media_id}/{any-name}.{ext}
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.3.3
     */
    public function track(): void
    {
        $app     = Factory::getApplication();
        $mediaId = $this->input->getInt('media_id', 0);

        if ($mediaId <= 0) {
            $this->fail($app, 400);
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $media = CwmpodcastTrackHelper::findPublishedMedia($db, $mediaId);

        if (!$media) {
            $this->fail($app, 404);
        }

        // Reconstruct the exact live URL the enclosure would have used, so the
        // served target matches (local OR external host). Derived server-side
        // from the media id only — the target URL is never taken from the
        // request (that would be an open redirect/proxy).
        $config = new Registry();
        $config->loadString(Cwmparams::getAdmin()->params);
        $config->merge(Cwmparams::getTemplateparams()->params);
        $protocol = (string) $config->get('protocol', 'https://');

        $sreg = new Registry($media->sparams);
        $mreg = new Registry($media->params);
        $file = str_replace(' ', '%20', (string) $mreg->get('filename'));
        $path = Cwmhelper::mediaBuildUrl($sreg->get('path'), $file, $config, false, false, true);

        if (empty($path)) {
            $this->fail($app, 404);
        }

        $target = $protocol . $path;

        // Count the download — best-effort; a failure must never block playback.
        try {
            $userAgent = (string) $this->input->server->getString('HTTP_USER_AGENT', '');

            if (!CwmpodcastTrackHelper::isBot($userAgent)) {
                $ip   = (string) $this->input->server->getString('REMOTE_ADDR', '');
                $hash = CwmpodcastTrackHelper::clientHash($ip, $userAgent);

                CwmpodcastTrackHelper::record(
                    $db,
                    $mediaId,
                    $hash,
                    Factory::getDate()->toSql(),
                    Factory::getDate('-24 hours')->toSql()
                );
            }
        } catch (\Exception $e) {
            // Swallow — counting is non-critical.
        }

        $mimeType = Cwmmime::fromExtension($path);

        if ($mimeType === null) {
            $storedMime = (string) $mreg->get('mime_type');
            $mimeType   = $storedMime !== '' ? $storedMime : 'application/octet-stream';
        }

        $this->serveMedia($target, $mimeType);
    }

    /**
     * Serve the tracked media directly (local disk or a proxied fetch from an
     * external host), honoring Range/HEAD/If-Modified-Since so the URL Apple's
     * crawler sees answers correctly without needing to follow a redirect
     * first (#1424).
     *
     * @param   string  $target    Absolute URL of the live media
     * @param   string  $mimeType  Content-Type to declare
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.5.4
     */
    private function serveMedia(string $target, string $mimeType): void
    {
        @set_time_limit(0);

        $host    = (string) $this->input->server->getString('HTTP_HOST', '');
        $isLocal = self::isLocalHost($host, $target);

        $range           = (string) $this->input->server->getString('HTTP_RANGE', '');
        $ifModifiedSince = (string) $this->input->server->getString('HTTP_IF_MODIFIED_SINCE', '');
        $ifNoneMatch     = (string) $this->input->server->getString('HTTP_IF_NONE_MATCH', '');
        $headOnly        = strtoupper((string) $this->input->getMethod()) === 'HEAD';

        if ($isLocal) {
            $localPath = $this->resolveLocalPath($target);

            if ($localPath !== null) {
                $this->streamLocalFile($localPath, $mimeType, $range, $ifModifiedSince, $headOnly);

                return;
            }
        }

        $this->streamRemoteFile($target, $range, $ifModifiedSince, $ifNoneMatch, $headOnly);
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
     * @since   10.5.4
     */
    private function resolveLocalPath(string $target): ?string
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
     * @since   10.5.4
     */
    private function streamLocalFile(
        string $filePath,
        string $mimeType,
        string $rangeHeader,
        string $ifModifiedSince,
        bool $headOnly
    ): void {
        $size  = filesize($filePath);
        $mtime = filemtime($filePath);

        if ($size === false || $mtime === false) {
            $this->fail(Factory::getApplication(), 404);
        }

        $this->flushBuffers();

        if ($ifModifiedSince !== '') {
            $since = strtotime($ifModifiedSince);

            if ($since !== false && $since >= $mtime) {
                http_response_code(304);
                $this->terminate();
            }
        }

        [$start, $end, $status] = self::resolveRange($rangeHeader, $size);

        header('Accept-Ranges: bytes');
        header('Content-Type: ' . $mimeType);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');

        if ($status === 416) {
            header('Content-Range: bytes */' . $size);
            http_response_code(416);
            $this->terminate();
        }

        http_response_code($status);
        header('Content-Length: ' . ($end - $start + 1));

        if ($status === 206) {
            header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
        }

        if ($headOnly) {
            $this->terminate();
        }

        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            $this->terminate();
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
        $this->terminate();
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
     * @since   10.5.4
     */
    private function streamRemoteFile(
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
            $this->fail(Factory::getApplication(), 404);
        }

        $this->flushBuffers();

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
            // internal host this check never sees — the live nfsda.org
            // target this was built for is a direct file, not a redirect,
            // so not following one costs nothing real here.
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
            CURLOPT_HEADERFUNCTION => static function ($curlHandle, string $headerLine): int {
                $trimmed = trim($headerLine);

                if ($trimmed === '') {
                    return \strlen($headerLine);
                }

                if (preg_match('#^HTTP/\S+\s+(\d{3})#', $trimmed, $statusMatch)) {
                    http_response_code((int) $statusMatch[1]);

                    return \strlen($headerLine);
                }

                if (preg_match('/^(Content-Type|Content-Length|Content-Range|Accept-Ranges|Last-Modified|ETag|Cache-Control|Expires):/i', $trimmed)) {
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

        $this->terminate();
    }

    /**
     * Stop Joomla's normal dispatch/render cycle from continuing after this
     * controller has already written a response directly (headers and,
     * usually, streamed body) via raw PHP functions.
     *
     * Every method in this class that answers HTTP requests itself instead of
     * returning control to Joomla's MVC render pipeline — fail(), sendJson(),
     * and the streaming methods here — must call this (or redirect(), which
     * already does) before returning. Skipping it lets Joomla continue on to
     * render its own page on top of/after the real response: headers set via
     * header() are only queued by PHP until the first byte of output or
     * script end, so any code path that sets headers without ever echoing a
     * body (a HEAD request, a 304, a 416) sends nothing to the client until
     * Joomla's own later render forces the flush — silently replacing the
     * intended response with Joomla's default page. This was caught live,
     * not by a unit test: it's a whole-request-lifecycle interaction no
     * PHPUnit test in this suite exercises directly (see
     * testEveryStreamingBranchTerminatesInsteadOfReturning for the source-
     * level regression guard).
     *
     * @return  never
     *
     * @since   10.5.4
     */
    private function terminate(): never
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
     * @since   10.5.4
     */
    private function flushBuffers(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    /**
     * Is the media target on this same site, so it can be streamed directly
     * off local disk rather than proxied from an external host?
     *
     * parse_url()'s PHP_URL_HOST never includes a port, but the raw Host
     * header does whenever the site isn't on the default port (every local
     * dev site this was tested against runs on :8890) — comparing them
     * as-is always mismatched on those sites and silently sent every local
     * file down the remote-proxy path instead. Strip the port from both
     * sides before comparing.
     *
     * @param   string  $requestHost  Raw incoming Host header (may include a port)
     * @param   string  $target       Absolute URL of the live media
     *
     * @return  bool
     *
     * @since   10.5.4
     */
    private static function isLocalHost(string $requestHost, string $target): bool
    {
        $hostOnly   = strtolower(explode(':', $requestHost, 2)[0]);
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
     * @param   CMSApplication  $app   Application.
     * @param   int                                     $code  HTTP status code.
     *
     * @return  never
     *
     * @since   10.3.3
     */
    private function fail($app, int $code): never
    {
        $app->setHeader('Status', (string) $code, true);
        $app->sendHeaders();
        $app->close();
        exit;
    }

    /**
     * Convert a time string (M:SS or H:MM:SS) to seconds.
     *
     * @param   string  $time  Time string
     *
     * @return  float  Seconds
     *
     * @since   10.3.0
     */
    private static function timeToSeconds(string $time): float
    {
        $parts = array_reverse(explode(':', $time));

        $seconds = (float) ($parts[0] ?? 0);
        $seconds += ((int) ($parts[1] ?? 0)) * 60;
        $seconds += ((int) ($parts[2] ?? 0)) * 3600;

        return $seconds;
    }

    /**
     * Send a JSON response and terminate.
     *
     * @param   array  $data    Data to encode
     * @param   int    $status  HTTP status code
     *
     * @return  void
     *
     * @since   10.3.0
     */
    private function sendJson(array $data, int $status = 200): void
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json; charset=utf-8');
        $app->setHeader('Status', (string) $status);

        try {
            echo json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } catch (\JsonException) {
            echo '{"version":"1.2.0","chapters":[]}';
        }

        $app->close();
    }
}
