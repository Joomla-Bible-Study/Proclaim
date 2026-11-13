<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Module
 * @subpackage mod_proclaim_youtube
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Module\ProclaimYoutube\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\CWMAddonYoutube;
use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeFileCache;
use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeLogHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeQuota;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * YouTube module helper — thin orchestration layer.
 *
 * All YouTube API calls, quota tracking, file caching, and embed URL building
 * are delegated to the server addon (CWMAddonYoutube) and its supporting
 * helpers (CwmyoutubeQuota, CwmyoutubeFileCache, CwmyoutubeLogHelper).
 *
 * This class only handles module-specific concerns: caching, priority mode
 * orchestration, AJAX endpoints, and Proclaim model queries.
 *
 * The name is the one com_ajax asks the module's helper factory for: for
 * module=proclaim_youtube it looks up ProclaimYoutubeHelper and calls its
 * <method>Ajax() on an instance. Under any other name the status poll gets an
 * error instead of an answer.
 *
 * @package     Proclaim.Module
 * @subpackage  mod_proclaim_youtube
 * @since       10.1.0
 */
class ProclaimYoutubeHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Seconds a video's live or upcoming status is cached: the browser's poll
     * interval, so all of a page's visitors share one YouTube API call.
     *
     * @var    int
     * @since  10.7.4
     */
    public const int STATUS_CACHE_TTL = 120;

    /**
     * Whether the last getVideo() call fetched fresh data from the API
     *
     * @var    bool
     * @since  10.1.0
     */
    private bool $freshFetch = false;

    /**
     * Get the video to display based on priority mode
     *
     * @param   Registry         $params  Module parameters
     * @param   SiteApplication  $app     Application instance
     *
     * @return  array|null  Video data or null if none found
     *
     * @since   10.1.0
     */
    public function getVideo(Registry $params, SiteApplication $app): ?array
    {
        $this->freshFetch = false;
        $serverId         = (int) $params->get('server_id', 0);
        $priorityMode     = $params->get('priority_mode', 'live_first');
        $showUpcoming     = (bool) $params->get('show_upcoming', 1);
        $excludeVideos    = $this->parseExcludedIds($params->get('exclude_videos', ''));

        if (!$serverId) {
            return null;
        }

        // Check cache first (include show_upcoming and excludes in cache key)
        $excludeHash = md5(implode(',', $excludeVideos));
        $cacheKey    = 'mod_proclaim_youtube_' . $serverId . '_' . $priorityMode . '_' . ($showUpcoming ? '1' : '0') . '_' . $excludeHash;
        $cacheTime   = (int) $params->get('cache_time', 300);

        // File-based, so it works even with Joomla caching disabled
        $fileCached = CwmyoutubeFileCache::getVideoCache($cacheKey);

        if ($fileCached !== null) {
            if (!empty($fileCached['_noVideo'])) {
                return null;
            }

            // The poll reloads the page when the stream goes live, so the page
            // must render what the poll found, or each poll reloads it again.
            return $this->withPolledStatus($fileCached, $serverId);
        }

        $this->freshFetch = true;
        $youtube          = new CWMAddonYoutube();
        $video            = null;

        // Smart pre-check: if we have a last-known live/upcoming video,
        // verify its status with videos.list (1 unit) instead of search.list (100 units)
        if ($priorityMode !== 'latest_only') {
            $lastKnown = CwmyoutubeFileCache::getLastKnownVideo($serverId);

            if (
                $lastKnown
                && !empty($lastKnown['videoId'])
                && (($lastKnown['isLive'] ?? false) || ($lastKnown['isUpcoming'] ?? false))
                && CwmyoutubeQuota::hasQuota($serverId, CwmyoutubeQuota::COST_VIDEOS)
            ) {
                $statusInput = new Input([
                    'server_id' => $serverId,
                    'video_id'  => $lastKnown['videoId'],
                ]);

                $status = $youtube->getVideoStatus($statusInput);

                if ($status['success'] && (($status['isLive'] ?? false) || ($status['isUpcoming'] ?? false))) {
                    $lastKnown['isLive']     = $status['isLive'] ?? false;
                    $lastKnown['isUpcoming'] = $status['isUpcoming'] ?? false;

                    if (!empty($status['scheduledStartTime'])) {
                        $lastKnown['scheduledStartTime'] = $status['scheduledStartTime'];
                        CwmyoutubeFileCache::storeScheduledStart($serverId, $lastKnown['videoId'], $status['scheduledStartTime']);
                    }

                    $video = $lastKnown;

                    CwmyoutubeLogHelper::log(
                        CwmyoutubeLogHelper::LEVEL_INFO,
                        'Pre-check: last-known video still active (saved search.list quota)',
                        ['server_id' => $serverId, 'video_id' => $lastKnown['videoId'], 'isLive' => $video['isLive']]
                    );
                }
            }
        }

        // Priority mode logic (skip if pre-check already found an active video)
        if (!$video) {
            switch ($priorityMode) {
                case 'live_first':
                    // First check for live videos
                    $video = $this->fetchLiveVideo($youtube, $serverId, $showUpcoming, $excludeVideos);

                    // If no live video, get latest uploaded
                    if (!$video) {
                        $video = $this->fetchLatestVideo($youtube, $serverId);
                    }
                    break;

                case 'live_only':
                    // Only get live videos
                    $video = $this->fetchLiveVideo($youtube, $serverId, $showUpcoming, $excludeVideos);
                    break;

                case 'latest_only':
                default:
                    // Only get the latest uploaded
                    $video = $this->fetchLatestVideo($youtube, $serverId);
                    break;
            }
        }

        // All fetch methods returned null — fall back to last known good video
        if (!$video) {
            $video = CwmyoutubeFileCache::getLastKnownVideo($serverId);

            if ($video) {
                // Clear live/upcoming flags — this is a fallback, not real-time
                $video['isLive']     = false;
                $video['isUpcoming'] = false;
                $video['isFallback'] = true;

                CwmyoutubeLogHelper::log(
                    CwmyoutubeLogHelper::LEVEL_INFO,
                    'Serving last known video (quota exhausted or API unavailable)',
                    ['server_id' => $serverId, 'video_id' => $video['videoId'] ?? '']
                );
            }
        }

        // Persist as last known good — survives Joomla cache clears and quota exhaustion
        if ($video && empty($video['isFallback'])) {
            CwmyoutubeFileCache::storeLastKnownVideo($serverId, $video);
        }

        // Cache the result (including null) to prevent repeated API calls.
        // Null results get the full cache_time as well: a short TTL costs about
        // 200 quota units a minute while no stream is active, at search.list x 2
        // per miss. Live transitions are picked up by JS polling
        // (getStatusAjax), not by page loads.
        $cacheData = $video ?? ['_noVideo' => true];

        // File-based, so it works even with Joomla caching off
        CwmyoutubeFileCache::storeVideoCache($cacheKey, $cacheData, $cacheTime);

        return $video;
    }

    /**
     * The video with the live status the browser's poll last found for it
     *
     * Read from the poll's own cache, so no API call is made. A status kept
     * only because the quota ran out is a guess, and is not applied.
     *
     * @param   array  $video     Video data from the video cache
     * @param   int    $serverId  Server ID
     *
     * @return  array
     *
     * @since   10.7.4
     */
    private function withPolledStatus(array $video, int $serverId): array
    {
        if (empty($video['videoId'])) {
            return $video;
        }

        $status = CwmyoutubeFileCache::getVideoCache(self::statusCacheKey($serverId, $video['videoId']));

        if ($status === null || empty($status['success']) || !empty($status['quotaExceeded'])) {
            return $video;
        }

        $video['isLive']     = (bool) ($status['isLive'] ?? false);
        $video['isUpcoming'] = (bool) ($status['isUpcoming'] ?? false);

        if (!empty($status['scheduledStartTime'])) {
            $video['scheduledStartTime'] = $status['scheduledStartTime'];
        }

        return $video;
    }

    /**
     * The file-cache key for a video's live status, written by the status
     * poll and read when the page renders
     *
     * @param   int     $serverId  Server ID
     * @param   string  $videoId   YouTube video ID; empty when the page shows none
     *
     * @return  string
     *
     * @since   10.7.4
     */
    private static function statusCacheKey(int $serverId, string $videoId): string
    {
        return 'mod_proclaim_youtube_status_' . $serverId . '_' . $videoId;
    }

    /**
     * Whether the last getVideo() call fetched fresh data from the YouTube API
     *
     * When true, the caller can skip verifyLiveStatus() since the data is already current.
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public function wasFreshFetch(): bool
    {
        return $this->freshFetch;
    }

    /**
     * Verify and update the live status of a video using a short-lived cache
     *
     * This is called after getting cached video data to ensure the
     * isLive/isUpcoming status is accurate on page load. Results are cached
     * for 2 minutes to avoid excessive API calls from multiple page loads.
     *
     * @param   array  $video     Video data array
     * @param   int    $serverId  Server ID for API access
     *
     * @return  array  Video data with updated status
     *
     * @since   10.1.0
     */
    public function verifyLiveStatus(array $video, int $serverId): array
    {
        // Only verify if a video was marked as live or upcoming (to save API quota)
        // JavaScript polling handles real-time updates for displayed live/upcoming videos
        if (empty($video['videoId']) || (!($video['isLive'] ?? false) && !($video['isUpcoming'] ?? false))) {
            return $video;
        }

        // Check cache first to avoid redundant API calls across page loads. The
        // file cache works even with Joomla caching off, which many sites leave.
        $cacheKey = 'mod_proclaim_youtube_verify_' . $serverId . '_' . $video['videoId'];
        $cached   = CwmyoutubeFileCache::getVideoCache($cacheKey);

        if ($cached !== null) {
            $video['isLive']     = $cached['isLive'] ?? false;
            $video['isUpcoming'] = $cached['isUpcoming'] ?? false;

            if (!empty($cached['scheduledStartTime'])) {
                $video['scheduledStartTime'] = $cached['scheduledStartTime'];
            }

            return $video;
        }

        // Quota gate: videos.list costs 1 unit
        if (!CwmyoutubeQuota::hasQuota($serverId, CwmyoutubeQuota::COST_VIDEOS)) {
            return $video;
        }

        $youtube     = new CWMAddonYoutube();
        $statusInput = new Input([
            'server_id' => $serverId,
            'video_id'  => $video['videoId'],
        ]);

        $result = $youtube->getVideoStatus($statusInput);

        if ($result['success']) {
            $video['isLive']     = $result['isLive'] ?? false;
            $video['isUpcoming'] = $result['isUpcoming'] ?? false;

            if (!empty($result['scheduledStartTime'])) {
                $video['scheduledStartTime'] = $result['scheduledStartTime'];
                CwmyoutubeFileCache::storeScheduledStart($serverId, $video['videoId'], $result['scheduledStartTime']);
            } elseif (!empty($video['videoId'])) {
                // Try persistent file cache if API didn't return it
                $stored = CwmyoutubeFileCache::getStoredScheduledStart($serverId, $video['videoId']);

                if ($stored !== '') {
                    $video['scheduledStartTime'] = $stored;
                }
            }

            // Cache the status for 2 minutes, the poll interval
            $cacheData = ['isLive' => $video['isLive'], 'isUpcoming' => $video['isUpcoming']];

            if (!empty($video['scheduledStartTime'])) {
                $cacheData['scheduledStartTime'] = $video['scheduledStartTime'];
            }

            CwmyoutubeFileCache::storeVideoCache($cacheKey, $cacheData, self::STATUS_CACHE_TTL);
        }

        return $video;
    }

    /**
     * Fetch currently live or upcoming video
     *
     * Uses a search throttle to avoid burning 200 quota units (2 x search.list)
     * every few minutes when no stream is active. After a search returns empty,
     * subsequent calls within the throttle window (15 min) are skipped. The
     * throttle resets immediately when a stream is found.
     *
     * @param   CWMAddonYoutube  $youtube        YouTube addon instance
     * @param   int              $serverId       Server ID
     * @param   bool             $showUpcoming   Whether to include upcoming streams
     * @param   array            $excludeVideos  Video IDs to exclude (unless live)
     *
     * @return  array|null  Video data or null
     *
     * @since   10.1.0
     */
    private function fetchLiveVideo(CWMAddonYoutube $youtube, int $serverId, bool $showUpcoming = true, array $excludeVideos = []): ?array
    {
        // Search throttle: search.list costs 100 units per call (200 for live + upcoming).
        // When no stream is active, repeating this every cache miss wastes quota fast.
        // Throttle to once per 15 minutes when the last search returned empty.
        $throttleData = CwmyoutubeFileCache::getSearchThrottle($serverId);

        if ($throttleData !== null) {
            $age = time() - ($throttleData['timestamp'] ?? 0);
            $ttl = (int) ($throttleData['ttl'] ?? 900);

            if ($age < $ttl) {
                CwmyoutubeLogHelper::log(
                    CwmyoutubeLogHelper::LEVEL_INFO,
                    'Search throttled — last empty search was ' . $age . 's ago (TTL ' . $ttl . 's)',
                    ['server_id' => $serverId, 'remaining' => CwmyoutubeQuota::getRemaining($serverId)]
                );

                return null;
            }
        }

        // Quota gate: search.list costs 100 units
        if (!CwmyoutubeQuota::hasQuota($serverId, CwmyoutubeQuota::COST_SEARCH)) {
            CwmyoutubeLogHelper::log(
                CwmyoutubeLogHelper::LEVEL_WARNING,
                'Quota exhausted — skipped live video search',
                ['server_id' => $serverId, 'remaining' => CwmyoutubeQuota::getRemaining($serverId)]
            );

            return null;
        }

        // Check for currently live - never exclude live videos
        $input = new Input([
            'server_id'   => $serverId,
            'max_results' => 10,
            'event_type'  => 'live',
        ]);

        $result = $youtube->fetchLiveVideos($input);

        if ($result['success'] && !empty($result['videos'])) {
            // Found a live stream — clear throttle so future checks stay responsive
            CwmyoutubeFileCache::clearSearchThrottle($serverId);

            $video               = $result['videos'][0];
            $video['isLive']     = true;
            $video['isUpcoming'] = false;

            return $video;
        }

        // Check for upcoming only if enabled
        if ($showUpcoming) {
            // Second search call — check quota again
            if (!CwmyoutubeQuota::hasQuota($serverId, CwmyoutubeQuota::COST_SEARCH)) {
                // Record throttle for the live search we already did
                CwmyoutubeFileCache::setSearchThrottle($serverId);

                return null;
            }

            $input->set('event_type', 'upcoming');
            $input->set('max_results', 10);
            $result = $youtube->fetchLiveVideos($input);

            if ($result['success'] && !empty($result['videos'])) {
                // Found an upcoming stream — clear throttle
                CwmyoutubeFileCache::clearSearchThrottle($serverId);

                // Filter out excluded videos for upcoming streams
                foreach ($result['videos'] as $video) {
                    if (!empty($video['videoId']) && !\in_array($video['videoId'], $excludeVideos, true)) {
                        $video['isLive']     = false;
                        $video['isUpcoming'] = true;

                        // Try persistent file cache first to avoid an API call
                        $stored = CwmyoutubeFileCache::getStoredScheduledStart($serverId, $video['videoId']);

                        if ($stored !== '') {
                            $video['scheduledStartTime'] = $stored;
                        } elseif (CwmyoutubeQuota::hasQuota($serverId, CwmyoutubeQuota::COST_VIDEOS)) {
                            // Fetch scheduledStartTime via Videos API for poll-window gating
                            $statusInput = new Input([
                                'server_id' => $serverId,
                                'video_id'  => $video['videoId'],
                            ]);

                            $statusResult = $youtube->getVideoStatus($statusInput);

                            if (!empty($statusResult['scheduledStartTime'])) {
                                $video['scheduledStartTime'] = $statusResult['scheduledStartTime'];
                                CwmyoutubeFileCache::storeScheduledStart($serverId, $video['videoId'], $statusResult['scheduledStartTime']);
                            }
                        }

                        return $video;
                    }
                }
            }
        }

        // Both searches returned empty — throttle future searches for 15 minutes.
        // This prevents burning 200 units every 5 minutes when no stream is active.
        CwmyoutubeFileCache::setSearchThrottle($serverId);

        CwmyoutubeLogHelper::log(
            CwmyoutubeLogHelper::LEVEL_INFO,
            'No live/upcoming stream found — throttling search.list for 1 hour',
            ['server_id' => $serverId, 'used' => CwmyoutubeQuota::getUsedToday($serverId)]
        );

        return null;
    }

    /**
     * Parse excluded video IDs from a comma-separated string
     *
     * @param   string  $excludeString  Comma-separated video IDs
     *
     * @return  array  Array of video IDs
     *
     * @since   10.1.0
     */
    private function parseExcludedIds(string $excludeString): array
    {
        if (empty($excludeString)) {
            return [];
        }

        $ids = explode(',', $excludeString);
        $ids = array_map('trim', $ids);
        $ids = array_filter($ids);

        return $ids;
    }

    /**
     * Fetch the latest video
     *
     * @param   CWMAddonYoutube  $youtube   YouTube addon instance
     * @param   int              $serverId  Server ID
     *
     * @return  array|null  Video data or null
     *
     * @since   10.1.0
     */
    private function fetchLatestVideo(CWMAddonYoutube $youtube, int $serverId): ?array
    {
        // Quota gate: channels.list + playlistItems.list = 2 units
        $cost = CwmyoutubeQuota::COST_CHANNELS + CwmyoutubeQuota::COST_PLAYLIST_ITEMS;

        if (!CwmyoutubeQuota::hasQuota($serverId, $cost)) {
            CwmyoutubeLogHelper::log(
                CwmyoutubeLogHelper::LEVEL_WARNING,
                'Quota exhausted — skipped latest video fetch',
                ['server_id' => $serverId, 'remaining' => CwmyoutubeQuota::getRemaining($serverId)]
            );

            return null;
        }

        $input = new Input([
            'server_id'   => $serverId,
            'max_results' => 1,
        ]);

        $result = $youtube->fetchChannelVideos($input);

        if ($result['success'] && !empty($result['videos'])) {
            $video               = $result['videos'][0];
            $video['isLive']     = false;
            $video['isUpcoming'] = false;

            return $video;
        }

        return null;
    }

    /**
     * Get recent sermons from Proclaim as a fallback when no YouTube video is available.
     *
     * Reuses the same CwmsermonsModel that mod_proclaim uses, so sermons include
     * batch-loaded teachers, scriptures, and media stats.
     *
     * @param   SiteApplication  $app    Application instance
     * @param   int              $limit  Number of sermons to return
     *
     * @return  array  Array of sermon objects, or empty array on failure
     *
     * @since   10.1.0
     */
    public function getRecentSermons(SiteApplication $app, int $limit = 6): array
    {
        try {
            $component = $app->bootComponent('com_proclaim');

            /** @var \CWM\Component\Proclaim\Site\Model\CwmsermonsModel $model */
            $model = $component->getMVCFactory()
                ->createModel('Cwmsermons', 'Site', ['ignore_request' => true]);

            // Set minimal state: latest sermons by date, limited to $limit
            $params = new Registry();
            $params->set('moduleitems', $limit);
            $model->setModuleState($params);

            // Exclude studytext (full HTML body) — not needed for fallback cards
            // and can be very large, saving significant memory per sermon.
            // This replaces the default list.select in getListQuery() which
            // includes studytext; other selects (series_id, thumbnailm, etc.)
            // are added separately and still run.
            $db = $this->getDatabase();
            $model->setState('list.select', implode(', ', $db->quoteName([
                'study.id', 'study.published', 'study.studydate', 'study.studytitle',
                'study.hits', 'study.alias',
                'study.studyintro', 'study.secondary_reference',
                'study.location_id', 'study.params',
                ])));

            return $model->getItems();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get YouTube embed URL for a video.
     *
     * Built inline to avoid autoloading CWMAddonYoutube (and its 7MB Google
     * SDK vendor tree) on cache-hit requests that don't need the API.
     *
     * @param   string  $videoId  YouTube video ID
     * @param   bool    $autoplay Whether to autoplay
     * @param   bool    $isLive   Whether this is a live video
     *
     * @return  string  Embed URL
     *
     * @since   10.1.0
     */
    public function getEmbedUrl(string $videoId, bool $autoplay = false, bool $isLive = false): string
    {
        $url    = 'https://www.youtube.com/embed/' . htmlspecialchars($videoId, ENT_QUOTES, 'UTF-8');
        $params = ['rel' => '0', 'enablejsapi' => '1'];

        if ($autoplay && $isLive) {
            $params['autoplay'] = '1';
            $params['mute']     = '1';
        }

        return $url . '?' . http_build_query($params);
    }

    /**
     * Truncate description to specified length
     *
     * @param   string  $description  Full description
     * @param   int     $maxLength    Maximum length
     *
     * @return  string  Truncated description
     *
     * @since   10.1.0
     */
    public function truncateDescription(string $description, int $maxLength = 200): string
    {
        if (\strlen($description) <= $maxLength) {
            return $description;
        }

        $truncated = substr($description, 0, $maxLength);
        $lastSpace = strrpos($truncated, ' ');

        if ($lastSpace !== false) {
            $truncated = substr($truncated, 0, $lastSpace);
        }

        return $truncated . '...';
    }

    /**
     * AJAX method to get current video status (live/upcoming/none)
     *
     * Uses a short-lived server-side cache (2 min) to prevent excessive YouTube API calls
     * when multiple module instances or rapid polling would otherwise exhaust the daily quota.
     *
     * Called via com_ajax: index.php?option=com_ajax&module=proclaim_youtube&method=getStatus&format=json,
     * which returns this array as the data of a JsonResponse.
     *
     * If video_id is provided, checks the specific video's status using the Videos API.
     * Otherwise returns "no stream" without any API call.
     *
     * @return  array  Status data with isLive, isUpcoming, and videoId
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public function getStatusAjax(): array
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();
        $serverId = $input->getInt('server_id', 0);
        $videoId  = $input->getString('video_id', '');
        $token    = $input->getString('token', '');

        // Validate token to prevent external abuse
        // Token is generated from server_id + a site secret
        $expectedToken = self::generateStatusToken($serverId, $videoId);

        if (empty($token) || !hash_equals($expectedToken, $token)) {
            return [
                'success' => false,
                'error'   => 'Invalid token',
            ];
        }

        if (!$serverId) {
            return [
                'success' => false,
                'error'   => 'No server ID provided',
            ];
        }

        // Check server-side cache to avoid redundant API calls (2-minute TTL)
        $statusCacheKey = self::statusCacheKey($serverId, $videoId);
        $cachedStatus   = CwmyoutubeFileCache::getVideoCache($statusCacheKey);

        if ($cachedStatus !== null) {
            return $cachedStatus;
        }

        // A com_ajax request reaches this helper without the module's
        // dispatcher, which is what loads the paths the server addon needs.
        self::loadComponentApi();

        $youtube      = new CWMAddonYoutube();
        $statusResult = null;

        // If we have a specific video ID, check its status directly (1 unit)
        if (!empty($videoId) && CwmyoutubeQuota::hasQuota($serverId, CwmyoutubeQuota::COST_VIDEOS)) {
            $statusInput = new Input([
                'server_id' => $serverId,
                'video_id'  => $videoId,
            ]);

            $result = $youtube->getVideoStatus($statusInput);

            if (!$result['success']) {
                CwmyoutubeLogHelper::log(
                    CwmyoutubeLogHelper::LEVEL_ERROR,
                    'Video status check failed',
                    ['server_id' => $serverId, 'video_id' => $videoId, 'error' => $result['error'] ?? 'Unknown']
                );
            }

            if ($result['success']) {
                $statusResult = [
                    'success'    => true,
                    'isLive'     => $result['isLive'] ?? false,
                    'isUpcoming' => $result['isUpcoming'] ?? false,
                    'videoId'    => $videoId,
                ];

                if (!empty($result['scheduledStartTime'])) {
                    $statusResult['scheduledStartTime'] = $result['scheduledStartTime'];
                    CwmyoutubeFileCache::storeScheduledStart($serverId, $videoId, $result['scheduledStartTime']);
                } else {
                    // Try persistent file cache
                    $stored = CwmyoutubeFileCache::getStoredScheduledStart($serverId, $videoId);

                    if ($stored !== '') {
                        $statusResult['scheduledStartTime'] = $stored;
                    }
                }
            }
        } elseif (!empty($videoId)) {
            // Quota exhausted — return last known status without API call
            $stored = CwmyoutubeFileCache::getStoredScheduledStart($serverId, $videoId);

            $statusResult = [
                'success'       => true,
                'isLive'        => false,
                'isUpcoming'    => true,
                'videoId'       => $videoId,
                'quotaExceeded' => true,
            ];

            if ($stored !== '') {
                $statusResult['scheduledStartTime'] = $stored;
            }
        }

        // No video_id provided and no result yet — return "no stream" without
        // using search.list. The AJAX polling path is called every 2 minutes by
        // JavaScript; using search.list here (100 units each) would drain the
        // daily quota within an hour. Live detection via search.list is handled
        // by getVideo() on page loads with a 15-minute throttle.
        if ($statusResult === null) {
            $statusResult = [
                'success'    => true,
                'isLive'     => false,
                'isUpcoming' => false,
                'videoId'    => '',
            ];
        }

        // Include remaining quota so JS can decide whether to continue polling
        $statusResult['quotaRemaining'] = CwmyoutubeQuota::getRemaining($serverId);

        // Cache the result for 2 minutes to avoid redundant API calls
        CwmyoutubeFileCache::storeVideoCache($statusCacheKey, $statusResult, self::STATUS_CACHE_TTL);

        return $statusResult;
    }

    /**
     * Find a matching Proclaim message for a YouTube video
     *
     * Parses the video title to extract the message title and the teacher's name,
     * then searches the Proclaim database for a matching message.
     *
     * @param   array  $video  Video data array with 'title' key
     *
     * @return  object|null  Matched message object or null if no match found
     *
     * @since   10.1.0
     */
    public function findMatchingMessage(array $video): ?object
    {
        if (empty($video['title']) && empty($video['videoId'])) {
            return null;
        }

        // Use the admin helper for matching logic
        if (!class_exists('CWM\\Component\\Proclaim\\Administrator\\Helper\\CwmyoutubeHelper')) {
            $helperFile = JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Helper/CwmyoutubeHelper.php';

            if (file_exists($helperFile)) {
                require_once $helperFile;
            } else {
                return null;
            }
        }

        return \CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeHelper::matchVideoToMessage(
            $video['title'] ?? '',
            $video['videoId'] ?? ''
        );
    }

    /**
     * Generate a secure token for AJAX status requests
     *
     * An HMAC of the server and the video under Joomla's secret, so only a
     * page that rendered the module for that video can ask about it. A poll
     * cannot name a video of its own: each one would be a new status cache
     * file and, while quota lasted, a YouTube API call.
     *
     * @param   int     $serverId  Server ID
     * @param   string  $videoId   The video the page shows; empty when it shows none
     *
     * @return  string  Token hash
     *
     * @since   10.1.0
     */
    public static function generateStatusToken(int $serverId, string $videoId): string
    {
        try {
            $secret = (string) Factory::getApplication()->get('secret', '');

            return hash_hmac('sha256', 'mod_proclaim_youtube_status_' . $serverId . '_' . $videoId, $secret);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Load Proclaim's API, which defines the paths and constants the server
     * addons rely on, unless it is loaded already.
     *
     * @return  void
     *
     * @since   10.7.4
     */
    public static function loadComponentApi(): void
    {
        if (\defined('CWM_LOADED')) {
            return;
        }

        $apiFile = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

        if (is_file($apiFile)) {
            require_once $apiFile;
        }
    }
}
