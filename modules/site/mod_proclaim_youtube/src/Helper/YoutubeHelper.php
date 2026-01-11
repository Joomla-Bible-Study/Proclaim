<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Module
 * @subpackage mod_proclaim_youtube
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Module\ProclaimYoutube\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\CWMAddonYoutube;
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
 * @since       10.0.0
 */
class YoutubeHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Get the video to display based on priority mode
     *
     * @param   Registry         $params  Module parameters
     * @param   SiteApplication  $app     Application instance
     *
     * @return  array|null  Video data or null if none found
     *
     * @since   10.0.0
     */
    public function getVideo(Registry $params, SiteApplication $app): ?array
    {
        $serverId      = (int) $params->get('server_id', 0);
        $priorityMode  = $params->get('priority_mode', 'live_first');
        $showUpcoming  = (bool) $params->get('show_upcoming', 1);
        $excludeVideos = $this->parseExcludedIds($params->get('exclude_videos', ''));

        if (!$serverId) {
            return null;
        }

        // Check cache first (include show_upcoming and excludes in cache key)
        $excludeHash = md5(implode(',', $excludeVideos));
        $cacheKey    = 'mod_proclaim_youtube_' . $serverId . '_' . $priorityMode . '_' . ($showUpcoming ? '1' : '0') . '_' . $excludeHash;
        $cacheTime   = (int) $params->get('cache_time', 300);

        try {
            $cache = Factory::getCache('mod_proclaim_youtube', 'output');
            $cache->setLifeTime((int) ceil($cacheTime / 60));

            $cachedVideo = $cache->get($cacheKey);

            if ($cachedVideo !== false) {
                $decoded = json_decode($cachedVideo, true);

                if ($decoded !== null) {
                    return $decoded;
                }
            }
        } catch (\Exception $e) {
            // Cache not available, continue without it
        }

        $youtube = new CWMAddonYoutube();
        $video   = null;

        // Priority mode logic
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
                // Only get latest uploaded
                $video = $this->fetchLatestVideo($youtube, $serverId);
                break;
        }

        // Cache the result
        if ($video) {
            try {
                $cache = Factory::getCache('mod_proclaim_youtube', 'output');
                $cache->setLifeTime((int) ceil($cacheTime / 60));
                $cache->store(json_encode($video), $cacheKey);
            } catch (\Exception $e) {
                // Cache not available, continue without it
            }
        }

        return $video;
    }

    /**
     * Verify and update the live status of a video in real-time
     *
     * This is called after getting cached video data to ensure the
     * isLive/isUpcoming status is accurate on page load.
     *
     * @param   array  $video     Video data array
     * @param   int    $serverId  Server ID for API access
     *
     * @return  array  Video data with updated status
     *
     * @since   10.0.0
     */
    public function verifyLiveStatus(array $video, int $serverId): array
    {
        // Only verify if video was marked as live or upcoming (to save API quota)
        // JavaScript polling handles real-time updates for displayed live/upcoming videos
        if (empty($video['videoId']) || (!($video['isLive'] ?? false) && !($video['isUpcoming'] ?? false))) {
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
     * @since   10.0.0
     */
    private function fetchLiveVideo(CWMAddonYoutube $youtube, int $serverId, bool $showUpcoming = true, array $excludeVideos = []): ?array
    {
        // Check for currently live - never exclude live videos
        $input = new Input([
            'server_id'   => $serverId,
            'max_results' => 10,
            'event_type'  => 'live',
        ]);

        $result = $youtube->fetchLiveVideos($input);

        if ($result['success'] && !empty($result['videos'])) {
            // Live videos are never excluded - return the first one
            $video               = $result['videos'][0];
            $video['isLive']     = true;
            $video['isUpcoming'] = false;

            return $video;
        }

        // Check for upcoming only if enabled
        if ($showUpcoming) {
            $input->set('event_type', 'upcoming');
            $input->set('max_results', 10);
            $result = $youtube->fetchLiveVideos($input);

            if ($result['success'] && !empty($result['videos'])) {
                // Filter out excluded videos for upcoming streams
                foreach ($result['videos'] as $video) {
                    if (!empty($video['videoId']) && !\in_array($video['videoId'], $excludeVideos, true)) {
                        $video['isLive']     = false;
                        $video['isUpcoming'] = true;

                        return $video;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Parse excluded video IDs from a comma-separated string
     *
     * @param   string  $excludeString  Comma-separated video IDs
     *
     * @return  array  Array of video IDs
     *
     * @since   10.0.0
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
     * Fetch latest uploaded video
     *
     * @param   CWMAddonYoutube  $youtube   YouTube addon instance
     * @param   int              $serverId  Server ID
     *
     * @return  array|null  Video data or null
     *
     * @since   10.0.0
     */
    private function fetchLatestVideo(CWMAddonYoutube $youtube, int $serverId): ?array
    {
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
     * Get YouTube embed URL for a video
     *
     * @param   string  $videoId  YouTube video ID
     * @param   bool    $autoplay Whether to autoplay
     * @param   bool    $isLive   Whether this is a live video
     *
     * @return  string  Embed URL
     *
     * @since   10.0.0
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
     * @since   10.0.0
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
     * This bypasses the cache to get real-time status from the YouTube API.
     * Called via com_ajax: index.php?option=com_ajax&module=mod_proclaim_youtube&method=getStatus&format=json
     *
     * If video_id is provided, checks the specific video's status using the Videos API.
     * Otherwise falls back to searching for live/upcoming videos on the channel.
     *
     * @return  array  Status data with isLive, isUpcoming, and videoId
     *
     * @throws \Exception
     * @since   10.0.0
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

        $youtube = new CWMAddonYoutube();

        // If we have a specific video ID, check its status directly
        // This is more reliable than the search API for status transitions
        if (!empty($videoId)) {
            $statusInput = new Input([
                'server_id' => $serverId,
                'video_id'  => $videoId,
            ]);

            $result = $youtube->getVideoStatus($statusInput);

            if ($result['success']) {
                return [
                    'success'    => true,
                    'isLive'     => $result['isLive'] ?? false,
                    'isUpcoming' => $result['isUpcoming'] ?? false,
                    'videoId'    => $videoId,
                ];
            }
        }

        // Fall back to searching for live/upcoming videos
        // Check for currently live videos
        $liveInput = new Input([
            'server_id'   => $serverId,
            'max_results' => 1,
            'event_type'  => 'live',
        ]);

        $result = $youtube->fetchLiveVideos($liveInput);

        if ($result['success'] && !empty($result['videos'])) {
            $video = $result['videos'][0];

            return [
                'success'    => true,
                'isLive'     => true,
                'isUpcoming' => false,
                'videoId'    => $video['videoId'] ?? '',
            ];
        }

        // Check for upcoming videos
        $upcomingInput = new Input([
            'server_id'   => $serverId,
            'max_results' => 1,
            'event_type'  => 'upcoming',
        ]);

        $result = $youtube->fetchLiveVideos($upcomingInput);

        if ($result['success'] && !empty($result['videos'])) {
            $video = $result['videos'][0];

            return [
                'success'    => true,
                'isLive'     => false,
                'isUpcoming' => true,
                'videoId'    => $video['videoId'] ?? '',
            ];
        }

        // No live or upcoming video
        return [
            'success'    => true,
            'isLive'     => false,
            'isUpcoming' => false,
            'videoId'    => '',
        ];
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
     * @since   10.0.0
     */
    public function findMatchingMessage(array $video): ?object
    {
        if (empty($video['title'])) {
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

        return \CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeHelper::matchVideoToMessage($video['title']);
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
     * @throws \Exception
     * @since   10.0.0
     */
    public static function generateStatusToken(int $serverId): string
    {
        $secret = Factory::getApplication()->get('secret', '');

        return hash('sha256', 'mod_proclaim_youtube_status_' . $serverId . '_' . $secret);
    }
}
