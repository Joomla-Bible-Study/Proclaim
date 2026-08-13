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
     * Serve the tracked media, honouring Range/HEAD/If-Modified-Since.
     *
     * The streaming itself moved to CwmmediaStreamer so the front-end download
     * route can share one implementation instead of growing a second (#1774).
     * This wrapper stays because the podcast redirect is its own entry point
     * and reads better named for what it does here.
     *
     * @param   string  $target           Absolute URL of the live media
     * @param   string  $mimeType         Content-Type to declare
     * @param   string  $host             This site's own configured hostname (from Uri::root())
     * @param   string  $range            Raw incoming Range header, if any
     * @param   string  $ifModifiedSince  Raw incoming If-Modified-Since header, if any
     * @param   string  $ifNoneMatch      Raw incoming If-None-Match header, if any
     * @param   bool    $headOnly         True for a HEAD request
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.5.6
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
        CwmmediaStreamer::serve($target, $mimeType, $host, $range, $ifModifiedSince, $ifNoneMatch, $headOnly);
    }

}
