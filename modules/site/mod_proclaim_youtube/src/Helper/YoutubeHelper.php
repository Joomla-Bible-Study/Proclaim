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
use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeLogHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeQuota;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * YouTube module helper
 *
 * @package     Proclaim.Module
 * @subpackage  mod_proclaim_youtube
 * @since       10.1.0
 */
class YoutubeHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

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

        try {
            $cache = Factory::getContainer()->get('cache.output');
            $cache->setLifeTime((int) ceil($cacheTime / 60));

            $cachedVideo = $cache->get($cacheKey, 'mod_proclaim_youtube');

            if ($cachedVideo !== false) {
                $decoded = json_decode($cachedVideo, true, 512, JSON_THROW_ON_ERROR);

                if ($decoded !== null) {
                    // Sentinel: cached "no video available" result
                    if (!empty($decoded['_noVideo'])) {
                        return null;
                    }

                    return $decoded;
                }
            }
        } catch (\Exception $e) {
            // Cache not available, continue without it
        }

        $this->freshFetch = true;
        $youtube          = new CWMAddonYoutube();
        $video            = null;

        // Smart pre-check: if we have a last-known live/upcoming video,
        // verify its status with videos.list (1 unit) instead of search.list (100 units)
        if ($priorityMode !== 'latest_only') {
            $lastKnown = self::getLastKnownVideo($serverId);

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
                        self::storeScheduledStart($serverId, $lastKnown['videoId'], $status['scheduledStartTime']);
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
            $video = self::getLastKnownVideo($serverId);

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
            self::storeLastKnownVideo($serverId, $video);
        }

        // Cache the result (including null) to prevent repeated API calls
        // FIX #1: Use full cache_time for null results too — 60s TTL was burning
        // ~200 quota units/minute in live_only mode when no stream is active
        try {
            $cache = Factory::getContainer()->get('cache.output');
            $cache->setLifeTime((int) ceil($cacheTime / 60));
            $cacheData = $video ?? ['_noVideo' => true];
            $cache->store(json_encode($cacheData), $cacheKey, 'mod_proclaim_youtube');
        } catch (\Exception $e) {
            // Cache not available, continue without it
        }

        return $video;
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

        // Check cache first to avoid redundant API calls across page loads
        $cacheKey = 'mod_proclaim_youtube_verify_' . $serverId . '_' . $video['videoId'];

        try {
            $cache = Factory::getContainer()->get('cache.output');
            $cache->setLifeTime(2); // 2 minutes (aligned with poll interval)

            $cachedResult = $cache->get($cacheKey, 'mod_proclaim_youtube_status');

            if ($cachedResult !== false) {
                $decoded = json_decode($cachedResult, true, 512, JSON_THROW_ON_ERROR);

                if ($decoded !== null) {
                    $video['isLive']     = $decoded['isLive'] ?? false;
                    $video['isUpcoming'] = $decoded['isUpcoming'] ?? false;

                    if (!empty($decoded['scheduledStartTime'])) {
                        $video['scheduledStartTime'] = $decoded['scheduledStartTime'];
                    }

                    return $video;
                }
            }
        } catch (\Exception $e) {
            // Cache not available, continue without it
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
                self::storeScheduledStart($serverId, $video['videoId'], $result['scheduledStartTime']);
            } elseif (!empty($video['videoId'])) {
                // Try persistent file cache if API didn't return it
                $stored = self::getStoredScheduledStart($serverId, $video['videoId']);

                if ($stored !== '') {
                    $video['scheduledStartTime'] = $stored;
                }
            }

            // Cache the status result for 2 minutes
            $cacheData = ['isLive' => $video['isLive'], 'isUpcoming' => $video['isUpcoming']];

            if (!empty($video['scheduledStartTime'])) {
                $cacheData['scheduledStartTime'] = $video['scheduledStartTime'];
            }

            try {
                $cache = Factory::getContainer()->get('cache.output');
                $cache->setLifeTime(2); // 2 minutes (aligned with poll interval)
                $cache->store(
                    json_encode($cacheData),
                    $cacheKey,
                    'mod_proclaim_youtube_status'
                );
            } catch (\Exception $e) {
                // Cache not available, continue without it
            }
        }

        return $video;
    }

    /**
     * Fetch currently live or upcoming video
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
        // FIX #2: 15-minute search throttle — prevents repeated expensive search.list
        // calls (100 units each) when no live/upcoming stream is active.
        // Saves ~200 units per throttle hit (live + upcoming searches).
        $throttle = self::getSearchThrottle($serverId);

        if ($throttle !== null) {
            $elapsed = time() - $throttle['timestamp'];

            if ($elapsed < ($throttle['ttl'] ?? 900)) {
                CwmyoutubeLogHelper::log(
                    CwmyoutubeLogHelper::LEVEL_INFO,
                    'Search throttled — skipping live/upcoming search',
                    ['server_id' => $serverId, 'seconds_remaining' => ($throttle['ttl'] ?? 900) - $elapsed]
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
            // Live stream found — clear any existing throttle
            self::clearSearchThrottle($serverId);

            // Live videos are never excluded - return the first one
            $video               = $result['videos'][0];
            $video['isLive']     = true;
            $video['isUpcoming'] = false;

            return $video;
        }

        // Check for upcoming only if enabled
        if ($showUpcoming) {
            // Second search call — check quota again
            if (!CwmyoutubeQuota::hasQuota($serverId, CwmyoutubeQuota::COST_SEARCH)) {
                // Set throttle since at least one search returned no results
                self::setSearchThrottle($serverId);

                return null;
            }

            $input->set('event_type', 'upcoming');
            $input->set('max_results', 10);
            $result = $youtube->fetchLiveVideos($input);

            if ($result['success'] && !empty($result['videos'])) {
                // Upcoming stream found — clear any existing throttle
                self::clearSearchThrottle($serverId);

                // Filter out excluded videos for upcoming streams
                foreach ($result['videos'] as $video) {
                    if (!empty($video['videoId']) && !\in_array($video['videoId'], $excludeVideos, true)) {
                        $video['isLive']     = false;
                        $video['isUpcoming'] = true;

                        // Try persistent file cache first to avoid an API call
                        $stored = self::getStoredScheduledStart($serverId, $video['videoId']);

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
                                self::storeScheduledStart($serverId, $video['videoId'], $statusResult['scheduledStartTime']);
                            }
                        }

                        return $video;
                    }
                }
            }
        }

        // No live or upcoming found — set 15-minute search throttle
        self::setSearchThrottle($serverId);

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
     * Store the last successfully fetched video in a persistent file cache.
     *
     * This survives Joomla cache clears and quota exhaustion, ensuring the
     * module can always show a video instead of "no video available."
     *
     * @param   int    $serverId  Server ID
     * @param   array  $video     Video data array
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private static function storeLastKnownVideo(int $serverId, array $video): void
    {
        $dir = JPATH_CACHE . '/mod_proclaim_youtube';

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $file = $dir . '/last_video_' . $serverId . '.json';
        $data = [
            'video'    => $video,
            'storedAt' => time(),
        ];

        @file_put_contents($file, json_encode($data, JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    /**
     * Retrieve the last known good video from the persistent file cache.
     *
     * No TTL — the file persists until overwritten by a newer successful fetch.
     *
     * @param   int  $serverId  Server ID
     *
     * @return  array|null  Video data array or null if not found
     *
     * @since   10.1.0
     */
    private static function getLastKnownVideo(int $serverId): ?array
    {
        $file = JPATH_CACHE . '/mod_proclaim_youtube/last_video_' . $serverId . '.json';

        if (!file_exists($file)) {
            return null;
        }

        $raw = @file_get_contents($file);

        if ($raw === false) {
            return null;
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return null;
        }

        return $data['video'] ?? null;
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

            return $model->getItems();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get YouTube embed URL for a video
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
        $url = 'https://www.youtube.com/embed/' . htmlspecialchars($videoId, ENT_QUOTES, 'UTF-8');

        $params = [];

        if ($autoplay && $isLive) {
            $params['autoplay'] = '1';
            $params['mute']     = '1';
        }

        // Don't show related videos from other channels
        $params['rel'] = '0';

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
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
     * Called via com_ajax: index.php?option=com_ajax&module=mod_proclaim_youtube&method=getStatus&format=json
     *
     * FIX #3: Only checks specific video ID status (1 unit). No longer falls back to
     * search.list (100 units each) — the initial page load handles discovery.
     *
     * @return  array  Status data with isLive, isUpcoming, and videoId
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public static function getStatusAjax(): array
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();
        $serverId = $input->getInt('server_id', 0);
        $videoId  = $input->getString('video_id', '');
        $token    = $input->getString('token', '');

        // Validate token to prevent external abuse
        // Token is generated from server_id + a site secret
        $expectedToken = self::generateStatusToken($serverId);

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
        $statusCacheKey = 'mod_proclaim_youtube_status_' . $serverId . '_' . $videoId;

        try {
            $cache = Factory::getContainer()->get('cache.output');
            $cache->setLifeTime(2); // 2 minutes (aligned with poll interval)

            $cachedStatus = $cache->get($statusCacheKey, 'mod_proclaim_youtube_status');

            if ($cachedStatus !== false) {
                $decoded = json_decode($cachedStatus, true, 512, JSON_THROW_ON_ERROR);

                if ($decoded !== null) {
                    return $decoded;
                }
            }
        } catch (\Exception $e) {
            // Cache not available, continue without it
        }

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
                    self::storeScheduledStart($serverId, $videoId, $result['scheduledStartTime']);
                } else {
                    // Try persistent file cache
                    $stored = self::getStoredScheduledStart($serverId, $videoId);

                    if ($stored !== '') {
                        $statusResult['scheduledStartTime'] = $stored;
                    }
                }
            }
        } elseif (!empty($videoId)) {
            // Quota exhausted — return last known status without API call
            $stored = self::getStoredScheduledStart($serverId, $videoId);

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

        // FIX #3: Removed search.list fallback — AJAX polling should only check
        // a known video ID (1 unit), not run expensive searches (200 units).
        // The initial page load via getVideo() handles live/upcoming discovery.

        // No video ID provided or quota exhausted
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
        try {
            $cache = Factory::getContainer()->get('cache.output');
            $cache->setLifeTime(2); // 2 minutes (aligned with poll interval)
            $cache->store(json_encode($statusResult), $statusCacheKey, 'mod_proclaim_youtube_status');
        } catch (\Exception $e) {
            // Cache not available, continue without it
        }

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
     * Token is based on server_id and Joomla's secret key, ensuring
     * Only requests from pages that rendered the module can call the API.
     *
     * @param   int  $serverId  Server ID
     *
     * @return  string  Token hash
     *
     * @since   10.1.0
     */
    public static function generateStatusToken(int $serverId): string
    {
        try {
            $secret = Factory::getApplication()->get('secret', '');

            return hash('sha256', 'mod_proclaim_youtube_status_' . $serverId . '_' . $secret);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Store a scheduled start time in a persistent file cache.
     *
     * Joomla's output cache can be flushed at any time, and there may not be
     * a linked Proclaim media record when an upcoming stream is first detected.
     * This file-based store survives cache clears and persists for 24 hours.
     *
     * @param   int     $serverId           Server ID
     * @param   string  $videoId            YouTube video ID
     * @param   string  $scheduledStartTime ISO 8601 scheduled start time
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function storeScheduledStart(int $serverId, string $videoId, string $scheduledStartTime): void
    {
        $dir = JPATH_CACHE . '/mod_proclaim_youtube';

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $file = $dir . '/schedule_' . $serverId . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $videoId) . '.json';
        $data = [
            'scheduledStartTime' => $scheduledStartTime,
            'storedAt'           => time(),
        ];

        @file_put_contents($file, json_encode($data), LOCK_EX);
    }

    /**
     * Retrieve a cached scheduled start time from the persistent file store.
     *
     * Returns the ISO 8601 time string if found and not expired (24h TTL),
     * or an empty string if missing/expired.
     *
     * @param   int     $serverId  Server ID
     * @param   string  $videoId   YouTube video ID
     *
     * @return  string  Scheduled start time or empty string
     *
     * @since   10.1.0
     */
    public static function getStoredScheduledStart(int $serverId, string $videoId): string
    {
        $file = JPATH_CACHE . '/mod_proclaim_youtube/schedule_'
            . $serverId . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $videoId) . '.json';

        if (!file_exists($file)) {
            return '';
        }

        $raw = @file_get_contents($file);

        if ($raw === false) {
            return '';
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            @unlink($file);

            return '';
        }

        if (empty($data['scheduledStartTime'])) {
            return '';
        }

        // 24-hour TTL
        if (isset($data['storedAt']) && (time() - $data['storedAt']) > 86400) {
            @unlink($file);

            return '';
        }

        return $data['scheduledStartTime'];
    }

    /**
     * Set a search throttle to prevent repeated expensive search.list calls.
     *
     * When search.list returns no live/upcoming streams, we record the time
     * so subsequent calls within the TTL window skip the search entirely.
     * This saves 200 quota units (2 x search.list) per throttle hit.
     *
     * @param   int  $serverId  Server ID
     * @param   int  $ttl       Throttle duration in seconds (default 900 = 15 min)
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private static function setSearchThrottle(int $serverId, int $ttl = 900): void
    {
        $dir = JPATH_CACHE . '/mod_proclaim_youtube';

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $file = $dir . '/search_throttle_' . $serverId . '.json';
        $data = [
            'timestamp' => time(),
            'ttl'       => $ttl,
        ];

        @file_put_contents($file, json_encode($data), LOCK_EX);
    }

    /**
     * Get the current search throttle data for a server.
     *
     * @param   int  $serverId  Server ID
     *
     * @return  array|null  Throttle data with 'timestamp' and 'ttl', or null if not throttled
     *
     * @since   10.1.0
     */
    private static function getSearchThrottle(int $serverId): ?array
    {
        $file = JPATH_CACHE . '/mod_proclaim_youtube/search_throttle_' . $serverId . '.json';

        if (!file_exists($file)) {
            return null;
        }

        $raw = @file_get_contents($file);

        if ($raw === false) {
            return null;
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            @unlink($file);

            return null;
        }

        if (!\is_array($data) || empty($data['timestamp'])) {
            return null;
        }

        return $data;
    }

    /**
     * Clear the search throttle (e.g. when a live/upcoming stream is found).
     *
     * @param   int  $serverId  Server ID
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private static function clearSearchThrottle(int $serverId): void
    {
        $file = JPATH_CACHE . '/mod_proclaim_youtube/search_throttle_' . $serverId . '.json';

        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
