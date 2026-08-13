<?php

/**
 * Proclaim protected media storage
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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Folder;

/**
 * The directory Proclaim keeps restricted media in, and whether the web server
 * actually refuses to serve it.
 *
 * ⚠️ **This directory is for self-hosted files.** Only a file we serve can be
 * moved into it, decided by `CwmmediaProtectionHelper::isServedByWebServer()`
 * rather than by a list of server types, which would go stale the first time an
 * addon is added.
 *
 * That is not the same as saying remote media cannot be protected. A private S3
 * object — or any origin that refuses anonymous reads — is already protected at
 * source, and `CwmmediaStreamer::streamRemoteFile()` proxies it to an
 * authorised user with Range and conditional requests intact, so the listener
 * cannot tell the difference. That is a *stronger* arrangement than this
 * directory, because the origin does the refusing rather than a `.htaccess` we
 * have to keep verifying.
 *
 * The rule that actually matters across all of them is the same: **Proclaim
 * must never hand out a URL that works without permission.** Proclaim already
 * proxies media rather than redirecting to it — the exception being embedded
 * platforms like YouTube, which are players rather than files and were never
 * protectable by anyone. The feed no longer lists restricted items either
 * (#1781), so the origin URL of a private remote file is never published.
 *
 * What this directory adds is the remaining case: a file that has nowhere
 * private to live, because the site hosts it itself.
 *
 * ⚠️ **This is verified deny, not unreachability.** The directory sits inside
 * the web root, because most hosts Proclaim supports cannot write above it. It
 * carries the same Apache/IIS guards Proclaim already ships elsewhere, and the
 * canary probe says whether they are doing anything. On a server that refuses
 * the probe this is a real boundary; on one that does not, Proclaim reports
 * that rather than implying protection it does not have (#1774).
 *
 * @package  Proclaim.Admin
 * @since    __DEPLOY_VERSION__
 */
class CwmprotectedStorage
{
    /**
     * Where restricted media lives, relative to the site root.
     *
     * Under `images/biblestudy/` with everything else rather than off in its
     * own tree: it is still user content, it still wants to be visible to a
     * backup, and a site that has to move hosts should not have to discover a
     * second location.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const RELATIVE_PATH = 'images/biblestudy/protected';

    /**
     * Component param holding the last probe verdict.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const STATUS_PARAM = 'protected_media_status';

    /**
     * Component param holding when that verdict was taken, as a Unix time.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const CHECKED_PARAM = 'protected_media_checked';

    /**
     * How long a verdict is trusted before it is taken again.
     *
     * A week. The thing being watched for is a host migration, a control-panel
     * change or a switch to nginx — none of which announce themselves, and all
     * of which silently undo the guards. Probing per request would be absurd;
     * never re-probing is how a stale green light outlives the thing it
     * described.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const RECHECK_SECONDS = 604800;

    /**
     * Absolute filesystem path of the protected directory.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function path(): string
    {
        return rtrim(JPATH_ROOT, '/\\') . '/' . self::RELATIVE_PATH;
    }

    /**
     * The URL that directory would be served from, if the server let it.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function url(): string
    {
        return rtrim(Uri::root(), '/') . '/' . self::RELATIVE_PATH;
    }

    /**
     * Create the directory if it is missing and make sure the guards are in it.
     *
     * Safe to call repeatedly: the guards are only written when absent, so an
     * administrator's hand-edited rules are never overwritten.
     *
     * @return  bool  False when the directory could not be created.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function ensure(): bool
    {
        $path = self::path();

        if (!is_dir($path) && !Folder::create($path)) {
            return false;
        }

        CwmmediaProtectionHelper::writeDenyFiles($path);

        return true;
    }

    /**
     * Is a stored verdict old enough to be taken again?
     *
     * Split out and pure so the staleness rule can be tested without waiting a
     * week. A verdict that was never taken is always due.
     *
     * @param   int|null  $checkedAt  Unix time the verdict was taken, or null if never.
     * @param   int       $now        Unix time to compare against.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function isRecheckDue(?int $checkedAt, int $now): bool
    {
        if ($checkedAt === null || $checkedAt <= 0) {
            return true;
        }

        // A timestamp in the future means the clock moved or the value was
        // tampered with. Re-check rather than trusting it until it catches up.
        if ($checkedAt > $now) {
            return true;
        }

        return ($now - $checkedAt) >= self::RECHECK_SECONDS;
    }

    /**
     * What the web server does with this directory.
     *
     * Returns the cached verdict unless it is stale or a fresh one is asked
     * for, because the probe is an HTTP round trip to our own site and has no
     * business happening on an ordinary page load.
     *
     * ⚠️ Anything other than PROTECTED means restricted media is not safe here.
     * That includes UNVERIFIED — see `CwmmediaProtectionHelper::interpretProbe()`
     * for why an unanswered probe is never read as success.
     *
     * @param   bool  $force  Take a fresh reading regardless of the cache.
     *
     * @return  string  One of the CwmmediaProtectionHelper constants.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function status(bool $force = false): string
    {
        $params  = ComponentHelper::getParams('com_proclaim');
        $stored  = (string) $params->get(self::STATUS_PARAM, '');
        $checked = (int) $params->get(self::CHECKED_PARAM, 0);

        if (!$force && $stored !== '' && !self::isRecheckDue($checked, time())) {
            return $stored;
        }

        if (!self::ensure()) {
            return CwmmediaProtectionHelper::UNVERIFIED;
        }

        $verdict = CwmmediaProtectionHelper::probeDirectory(self::path(), self::url());

        Cwmparams::setCompParams([
            self::STATUS_PARAM  => $verdict,
            self::CHECKED_PARAM => (string) time(),
        ]);

        return $verdict;
    }

    /**
     * Could this media file be moved into protected storage?
     *
     * Only if Proclaim's own web server is the one handing it out — a file on
     * YouTube, Vimeo or S3 is not ours to relocate.
     *
     * ⚠️ A false answer here means "cannot be moved here", **not** "cannot be
     * protected". A private remote origin that Proclaim proxies is protected
     * already, and better than this directory: the origin refuses anonymous
     * reads itself, with no guard for us to verify. Callers deciding whether a
     * file is *safe* must ask that question separately.
     *
     * @param   string  $resolvedUrl  The URL the media file resolves to.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function canHold(string $resolvedUrl): bool
    {
        return CwmmediaProtectionHelper::isServedByWebServer($resolvedUrl);
    }
}
