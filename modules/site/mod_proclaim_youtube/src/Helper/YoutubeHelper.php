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
        $serverId     = (int) $params->get('server_id', 0);
        $priorityMode = $params->get('priority_mode', 'live_first');

        if (!$serverId) {
            return null;
        }

        // Check cache first
        $cacheKey  = 'mod_proclaim_youtube_' . $serverId . '_' . $priorityMode;
        $cacheTime = (int) $params->get('cache_time', 300);

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
                $video = $this->fetchLiveVideo($youtube, $serverId);

                // If no live video, get latest uploaded
                if (!$video) {
                    $video = $this->fetchLatestVideo($youtube, $serverId);
                }
                break;

            case 'live_only':
                // Only get live videos
                $video = $this->fetchLiveVideo($youtube, $serverId);
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
     * Fetch currently live or upcoming video
     *
     * @param   CWMAddonYoutube  $youtube   YouTube addon instance
     * @param   int              $serverId  Server ID
     *
     * @return  array|null  Video data or null
     *
     * @since   10.0.0
     */
    private function fetchLiveVideo(CWMAddonYoutube $youtube, int $serverId): ?array
    {
        // Check for currently live
        $input = new Input([
            'server_id'   => $serverId,
            'max_results' => 1,
            'event_type'  => 'live',
        ]);

        $result = $youtube->fetchLiveVideos($input);

        if ($result['success'] && !empty($result['videos'])) {
            $video               = $result['videos'][0];
            $video['isLive']     = true;
            $video['isUpcoming'] = false;

            return $video;
        }

        // Check for upcoming
        $input->set('event_type', 'upcoming');
        $result = $youtube->fetchLiveVideos($input);

        if ($result['success'] && !empty($result['videos'])) {
            $video               = $result['videos'][0];
            $video['isLive']     = false;
            $video['isUpcoming'] = true;

            return $video;
        }

        return null;
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
}
