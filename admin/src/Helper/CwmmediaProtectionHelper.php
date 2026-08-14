<?php

/**
 * Proclaim media protection helper
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

use Joomla\CMS\Uri\Uri;
use Joomla\Http\HttpFactory;

/**
 * Answers whether a media file is restricted in Proclaim while still being
 * fetchable straight off the web server.
 *
 * Setting a non-Public access level on a media file — or on the message or
 * series it belongs to — stops Proclaim serving it, and nothing else. Files
 * under the web root are handed out by the web server, which never enters PHP
 * and so never sees a view level. The `access` field therefore reads like a
 * lock while the door is open, and that is exactly the combination an
 * administrator building a subscriber-only series would set and trust.
 *
 * This exists so the admin can be told, at the moment of setting it, rather
 * than discovering it later. It is a diagnosis, not a fix: real protection
 * needs the file stored outside the web root.
 *
 * @package  Proclaim.Admin
 * @since    10.5.8
 */
class CwmmediaProtectionHelper
{
    /**
     * Is this URL one the web server will answer without Joomla's involvement?
     *
     * Compared against `Uri::root()` rather than the request's Host header:
     * the Host header is client-supplied and rewritable by any proxy in front
     * of the site, so it can differ from the site's configured hostname with
     * no attacker involved.
     *
     * A URL pointing at another host is somebody else's to protect, and a
     * relative or empty one tells us nothing — both answer false, because
     * neither is a case where *we* are leaking the file.
     *
     * @param   string  $url  Resolved media URL.
     *
     * @return  bool
     *
     * @since   10.5.8
     */
    public static function isServedByWebServer(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        $root = Uri::root();

        return $root !== '' && str_starts_with($url, $root);
    }

    /**
     * Would a visitor with no account be refused this media file by Proclaim?
     *
     * Deliberately expressed as "refused to a guest" rather than "access is not
     * Public": the levels a logged-out visitor holds are whatever the site says
     * they are, and asking the same question the download route asks keeps the
     * two from drifting apart. The chain — file, message, series — is the rule
     * from `Cwmdownload::isAccessible()`.
     *
     * @param   object      $media        Media row carrying access, study_access and series_access.
     * @param   array<int>  $guestLevels  View levels a logged-out visitor holds.
     *
     * @return  bool
     *
     * @since   10.5.8
     */
    public static function isRestrictedFromGuests(object $media, array $guestLevels): bool
    {
        foreach ([$media->access ?? null, $media->study_access ?? null, $media->series_access ?? null] as $level) {
            if ($level !== null && !\in_array((int) $level, $guestLevels, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is this media file restricted in Proclaim but still public on disk?
     *
     * The combination that matters. Either half alone is fine: a restricted
     * file stored off the web root is genuinely protected, and a public file
     * served by the web server is behaving as intended.
     *
     * @param   object      $media        Media row carrying access, study_access and series_access.
     * @param   string      $resolvedUrl  The URL the media resolves to.
     * @param   array<int>  $guestLevels  View levels a logged-out visitor holds.
     *
     * @return  bool
     *
     * @since   10.5.8
     */
    public static function isRestrictedButReachable(object $media, string $resolvedUrl, array $guestLevels): bool
    {
        return self::isRestrictedFromGuests($media, $guestLevels)
            && self::isServedByWebServer($resolvedUrl);
    }

    /**
     * The directory is genuinely refused by the web server.
     *
     * @since  10.5.8
     */
    public const PROTECTED = 'protected';

    /**
     * The web server handed the file back. Whatever is in here is public.
     *
     * @since  10.5.8
     */
    public const EXPOSED = 'exposed';

    /**
     * We could not find out. ⚠️ Never treat this as protected: an unanswered
     * probe is exactly what a broken deny rule looks like from here.
     *
     * @since  10.5.8
     */
    public const UNVERIFIED = 'unverified';

    /**
     * Seconds to wait for the probe. The request is to this same site, so a
     * slow answer means something is wrong rather than far away.
     *
     * @since  10.5.8
     */
    private const PROBE_TIMEOUT = 10;

    /**
     * Decide what a probe response means.
     *
     * Split from the request itself so the reasoning is testable without a web
     * server. The rule that matters is the last one: anything we cannot
     * positively read as a refusal is UNVERIFIED, never PROTECTED. A deny rule
     * that silently does nothing and a network hiccup look identical from
     * here, and only one of them is safe to assume.
     *
     * @param   int|null  $status  HTTP status, or null if the request itself failed.
     * @param   string    $body    Response body, empty when there was none.
     * @param   string    $token   The canary's contents.
     *
     * @return  string  One of the class constants.
     *
     * @since   10.5.8
     */
    public static function interpretProbe(?int $status, string $body, string $token): string
    {
        if ($status === null) {
            return self::UNVERIFIED;
        }

        // A 200 that returns the canary is unambiguous: the file is public.
        if ($status >= 200 && $status < 300) {
            return str_contains($body, $token)
                ? self::EXPOSED
                // Success, but not our file — a login page, an error document,
                // a CDN interstitial. Not served, but not a refusal either.
                : self::UNVERIFIED;
        }

        // Only a client error is the web server actually declining. 403 is the
        // usual answer and 404 is what some configurations return instead of
        // admitting the file exists; both are refusals.
        if ($status >= 400 && $status < 500) {
            return self::PROTECTED;
        }

        // 3xx and 5xx say nothing useful. A redirect might be a login wall or
        // might be a canonical-host hop that would have served the file; a 500
        // means something broke, which is not the same as being refused.
        // Neither is evidence of protection.
        return self::UNVERIFIED;
    }

    /**
     * Ask the web server whether it will serve a file from this directory.
     *
     * Writes a file with known contents, requests it over HTTP the way any
     * visitor would, and removes it again. This tests the *outcome* rather
     * than the mechanism, so it is equally valid behind Apache, nginx, IIS or
     * anything else — which matters because `.htaccess` is silently inert on
     * nginx, and on Apache with AllowOverride off.
     *
     * The canary is removed in a finally block: leaving a readable file behind
     * in a directory that just proved itself readable would be its own small
     * hole.
     *
     * @param   string  $absoluteDir  Directory to test, absolute filesystem path.
     * @param   string  $dirUrl       The URL that directory is served from, trailing slash optional.
     *
     * @return  string  One of the class constants.
     *
     * @since   10.5.8
     */
    public static function probeDirectory(string $absoluteDir, string $dirUrl): string
    {
        if (!is_dir($absoluteDir) || !is_writable($absoluteDir)) {
            return self::UNVERIFIED;
        }

        $token = 'proclaim-canary-' . bin2hex(random_bytes(16));
        $name  = 'proclaim-canary-' . bin2hex(random_bytes(8)) . '.txt';
        $path  = rtrim($absoluteDir, '/\\') . '/' . $name;

        if (@file_put_contents($path, $token) === false) {
            return self::UNVERIFIED;
        }

        try {
            $url = rtrim($dirUrl, '/') . '/' . $name;

            try {
                $response = (new HttpFactory())->getHttp()->get($url, [], self::PROBE_TIMEOUT);
            } catch (\Throwable) {
                // No answer at all — could not verify, must not assume safe.
                return self::UNVERIFIED;
            }

            return self::interpretProbe(
                $response->getStatusCode(),
                (string) $response->getBody(),
                $token
            );
        } finally {
            @unlink($path);
        }
    }

    /**
     * Write deny-all guards into a directory.
     *
     * Apache 2.2 and 2.4 both, plus IIS via request filtering — the same pair
     * Proclaim already ships statically for `media/backup/` and writes at
     * runtime for the YouTube cache. Shared here so there is one copy rather
     * than a third.
     *
     * ⚠️ Writing these proves nothing. `.htaccess` is inert on nginx and on
     * Apache with AllowOverride off, and this method cannot tell. Follow it
     * with probeDirectory() before describing anything as protected.
     *
     * @param   string  $dir  Directory to guard, absolute filesystem path.
     *
     * @return  void
     *
     * @since   10.5.8
     */
    public static function writeDenyFiles(string $dir): void
    {
        $htaccess = $dir . '/.htaccess';

        if (!file_exists($htaccess)) {
            @file_put_contents(
                $htaccess,
                "<IfModule !mod_authz_core.c>\n"
                . "Order deny,allow\n"
                . "Deny from all\n"
                . "</IfModule>\n"
                . "<IfModule mod_authz_core.c>\n"
                . "  <RequireAll>\n"
                . "    Require all denied\n"
                . "  </RequireAll>\n"
                . "</IfModule>\n"
            );
        }

        $webConfig = $dir . '/web.config';

        if (!file_exists($webConfig)) {
            @file_put_contents(
                $webConfig,
                "<?xml version=\"1.0\"?>\n"
                . "<configuration>\n"
                . "    <system.webServer>\n"
                . "        <security>\n"
                . "            <requestFiltering>\n"
                . "                <fileExtensions allowUnlisted=\"false\" />\n"
                . "            </requestFiltering>\n"
                . "        </security>\n"
                . "    </system.webServer>\n"
                . "</configuration>\n"
            );
        }
    }
}
