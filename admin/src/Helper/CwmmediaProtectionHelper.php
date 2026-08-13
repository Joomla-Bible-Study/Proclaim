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

/**
 * Answers whether a media file is restricted in Proclaim while still being
 * fetchable straight off the web server.
 *
 * Setting a non-Public access level on a media file — or on the message or
 * series it belongs to — stops Proclaim serving it, and nothing else. Files
 * under the web root are handed out by the web server, which never enters PHP
 * and so never sees a view level. The `access` field therefore reads like a
 * lock while the door is open, and that is exactly the combination an
 * administrator building a subscriber-only series would set and trust (#1774).
 *
 * This exists so the admin can be told, at the moment of setting it, rather
 * than discovering it later. It is a diagnosis, not a fix: real protection
 * needs the file stored outside the web root.
 *
 * @package  Proclaim.Admin
 * @since    __DEPLOY_VERSION__
 */
class CwmmediaProtectionHelper
{
    /**
     * Is this URL one the web server will answer without Joomla's involvement?
     *
     * Compared against `Uri::root()` rather than the request's Host header:
     * the Host header is client-supplied and rewritable by any proxy in front
     * of the site, so it can differ from the site's configured hostname with
     * no attacker involved (#1552).
     *
     * A URL pointing at another host is somebody else's to protect, and a
     * relative or empty one tells us nothing — both answer false, because
     * neither is a case where *we* are leaking the file.
     *
     * @param   string  $url  Resolved media URL.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
     */
    public static function isRestrictedButReachable(object $media, string $resolvedUrl, array $guestLevels): bool
    {
        return self::isRestrictedFromGuests($media, $guestLevels)
            && self::isServedByWebServer($resolvedUrl);
    }
}
