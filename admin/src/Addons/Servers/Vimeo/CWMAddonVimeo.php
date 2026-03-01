<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Vimeo;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Helper\CwmserverMigrationHelper;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Http\HttpFactory;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * Vimeo Server Addon
 *
 * Provides integration with Vimeo video platform including:
 * - URL parsing and embed conversion
 * - Video metadata retrieval via Vimeo API
 * - Support for Vimeo Pro features
 *
 * @since 10.1.0
 */
class CWMAddonVimeo extends CWMAddon
{
    /**
     * Addon name
     *
     * @var string
     * @since 10.1.0
     */
    protected $name = 'Vimeo';

    /**
     * Addon description
     *
     * @var string
     * @since 10.1.0
     */
    protected $description = 'Used for Vimeo server access';

    /**
     * URL patterns that identify Vimeo content.
     *
     * @return  string[]
     *
     * @since   10.1.0
     */
    public function getUrlPatterns(): array
    {
        return ['/(vimeo\.com)/i'];
    }

    /**
     * {@inheritdoc}
     *
     * @since   10.1.0
     */
    public function getMigrationPatterns(): array
    {
        return [
            'type'     => 'vimeo',
            'label'    => 'Vimeo',
            'patterns' => [
                '/vimeo\.com/i',
                '/player\.vimeo\.com/i',
            ],
            'allVideosTags' => ['vimeo'],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since   10.1.0
     */
    public function transformMigrationParams(
        array $params,
        string $mediacode,
        string $filename,
        string $avContent,
        string $combined,
        array $legacyServerParams = []
    ): array {
        $result  = [];
        $videoId = $this->extractVimeoVideoId($combined);

        // Fall back to AllVideos bare ID: {vimeo}123456789{/vimeo}
        if ($videoId === null && !empty($avContent) && preg_match('/^\d+$/', $avContent)) {
            $videoId = $avContent;
        }

        if ($videoId) {
            $result['filename'] = '//player.vimeo.com/video/' . $videoId;
        } else {
            $result['filename'] = $filename;
        }

        // Map extracted URL params to embed option form fields
        $sourceQuery = CwmserverMigrationHelper::extractSourceUrlParams($combined, 'vimeo');

        $vmParamMap = [
            'muted'      => 'vm_muted',
            'loop'       => 'vm_loop',
            'controls'   => 'vm_controls',
            'color'      => 'vm_color',
            'title'      => 'vm_title',
            'byline'     => 'vm_byline',
            'portrait'   => 'vm_portrait',
            'dnt'        => 'vm_dnt',
            'background' => 'vm_background',
            'speed'      => 'vm_speed',
        ];

        foreach ($vmParamMap as $urlParam => $formField) {
            if (isset($sourceQuery[$urlParam]) && $sourceQuery[$urlParam] !== '') {
                $result[$formField] = $sourceQuery[$urlParam];
            }
        }

        if (isset($sourceQuery['autoplay']) && $sourceQuery['autoplay'] === '1') {
            $result['autostart'] = 'true';
        }

        $result['player']    = '1';
        $result['mediacode'] = '';

        return $result;
    }

    /**
     * Build a Vimeo embed URL with all form field params applied.
     *
     * @param   string    $filename     The raw Vimeo URL
     * @param   Registry  $mediaParams  Merged template + media params
     *
     * @return  string  The embed-ready URL with query params
     *
     * @since   10.1.0
     */
    public function buildEmbedUrl(string $filename, Registry $mediaParams): string
    {
        $baseUrl = $this->convertVimeo($filename);
        $parts   = parse_url($baseUrl);
        $query   = [];

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $autostart = $mediaParams->get('autostart', '');

        if ($autostart === 'true') {
            $query['autoplay'] = '1';
        } elseif ($autostart === 'false') {
            $query['autoplay'] = '0';
        }

        $fieldMap = [
            'vm_muted'      => 'muted',
            'vm_loop'       => 'loop',
            'vm_controls'   => 'controls',
            'vm_color'      => 'color',
            'vm_title'      => 'title',
            'vm_byline'     => 'byline',
            'vm_portrait'   => 'portrait',
            'vm_dnt'        => 'dnt',
            'vm_background' => 'background',
            'vm_speed'      => 'speed',
        ];

        foreach ($fieldMap as $formField => $urlParam) {
            $val = $mediaParams->get($formField, '');

            if ($val !== '') {
                $query[$urlParam] = $val;
            }
        }

        return strtok($baseUrl, '?') . (!empty($query) ? '?' . http_build_query($query) : '');
    }

    /**
     * Render inline Vimeo player (responsive 16:9 iframe).
     *
     * @param   string    $url          The raw Vimeo URL
     * @param   Registry  $mediaParams  Merged template + media params
     * @param   int       $mediaId      The media file ID
     *
     * @return  string  Complete player HTML
     *
     * @since   10.1.0
     */
    public function renderInlinePlayer(string $url, Registry $mediaParams, int $mediaId): string
    {
        $embedUrl = $this->buildEmbedUrl($url, $mediaParams);

        return '<div class="proclaim-video-wrap" style="position:relative;padding-bottom:56.25%;overflow:hidden;max-width:100%;">'
            . '<iframe class="playhit" data-id="' . $mediaId . '" src="' . htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8') . '"'
            . ' webkitallowfullscreen mozallowfullscreen allowfullscreen'
            . ' style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"></iframe>'
            . '</div>';
    }

    /**
     * Convert Vimeo URL to embed format
     *
     * Supports various Vimeo URL formats:
     * - https://vimeo.com/123456789
     * - https://player.vimeo.com/video/123456789
     * - https://vimeo.com/channels/staffpicks/123456789
     *
     * @param   string  $url  The Vimeo URL to convert
     *
     * @return  string  The embed URL (//player.vimeo.com/video/{id})
     *
     * @since   10.1.0
     */
    public function convertVimeo(string $url = ''): string
    {
        if (empty($url)) {
            return '';
        }

        // Extract video ID from various Vimeo URL formats
        $patterns = [
            // Standard: vimeo.com/123456789
            '/vimeo\.com\/(\d+)/',
            // Player: player.vimeo.com/video/123456789
            '/player\.vimeo\.com\/video\/(\d+)/',
            // Channels: vimeo.com/channels/name/123456789
            '/vimeo\.com\/channels\/[^\/]+\/(\d+)/',
            // Groups: vimeo.com/groups/name/videos/123456789
            '/vimeo\.com\/groups\/[^\/]+\/videos\/(\d+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return 'https://player.vimeo.com/video/' . $matches[1];
            }
        }

        // If no match, return original URL
        return $url;
    }

    /**
     * {@inheritdoc}
     *
     * @since   10.1.0
     */
    #[\Override]
    public static function extractMediaId(string $text): ?string
    {
        $patterns = [
            '/vimeo\.com\/(\d+)/',
            '/player\.vimeo\.com\/video\/(\d+)/',
            '/vimeo\.com\/channels\/[^\/]+\/(\d+)/',
            '/vimeo\.com\/groups\/[^\/]+\/videos\/(\d+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * @deprecated Use extractMediaId() instead
     */
    public function extractVimeoVideoId(string $url): ?string
    {
        return static::extractMediaId($url);
    }

    /**
     * Check if a URL is a Vimeo URL
     *
     * @param   string  $url  The URL to check
     *
     * @return  bool  True if Vimeo URL, false otherwise
     *
     * @since   10.1.0
     */
    public static function isVimeoUrl(string $url): bool
    {
        return preg_match('/vimeo\.com/', $url) === 1;
    }

    /**
     * Get Vimeo video metadata via oEmbed API (no auth required)
     *
     * @param   string  $videoId  The Vimeo video ID
     *
     * @return  array  Metadata array with title, description, thumbnail, duration
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function getVideoMetadata(string $videoId): array
    {
        try {
            $url      = 'https://vimeo.com/api/oembed.json?url=https://vimeo.com/' . $videoId;
            $factory  = new HttpFactory();
            $http     = $factory->getHttp();
            $headers  = [
                'Accept' => 'application/json',
            ];

            try {
                $response = $http->get($url, $headers);

                if ($response->getStatusCode() !== 200) {
                    throw new \RuntimeException('Vimeo oEmbed error: HTTP ' . $response->getStatusCode());
                }

                $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            } catch (\Exception $e) {
                return ['success' => false, 'error' => $e->getMessage()];
            }

            return [
                'title'       => $data['title'] ?? '',
                'description' => $data['description'] ?? '',
                'thumbnail'   => $data['thumbnail_url'] ?? '',
                'duration'    => $data['duration'] ?? 0,
                'author'      => $data['author_name'] ?? '',
                'width'       => $data['width'] ?? 0,
                'height'      => $data['height'] ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'title'       => '',
                'description' => '',
                'thumbnail'   => '',
                'duration'    => 0,
            ];
        }
    }

    /**
     * Vimeo supports description sync via PATCH API.
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public function supportsDescriptionSync(): bool
    {
        return true;
    }

    /**
     * Push a description to a Vimeo video via PATCH API.
     *
     * @param   int     $mediaId      The media file ID
     * @param   string  $description  The description text
     *
     * @return  array{success: bool, error?: string}
     *
     * @since   10.1.0
     */
    public function syncDescription(int $mediaId, string $description): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select([$db->quoteName('params'), $db->quoteName('server_id')])
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('id') . ' = ' . (int) $mediaId);
        $db->setQuery($query);
        $row = $db->loadObject();

        if (!$row) {
            return ['success' => false, 'error' => 'Media file not found'];
        }

        try {
            $params = json_decode($row->params ?? '{}', true, 512, JSON_THROW_ON_ERROR) ?: [];
        } catch (\JsonException) {
            return ['success' => false, 'error' => 'Invalid media params JSON'];
        }
        $videoId = $this->extractVimeoVideoId($params['filename'] ?? '');

        if (!$videoId) {
            return ['success' => false, 'error' => 'Could not extract Vimeo video ID'];
        }

        $accessToken = $this->getServerAccessToken((int) $row->server_id);

        if (empty($accessToken)) {
            return ['success' => false, 'error' => 'No Vimeo access token configured'];
        }

        try {
            $factory  = new HttpFactory();
            $http     = $factory->getHttp();
            $headers  = [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/vnd.vimeo.*+json;version=3.4',
            ];

            $body     = json_encode(['description' => $description], JSON_THROW_ON_ERROR);
            $response = $http->patch('https://api.vimeo.com/videos/' . $videoId, $body, $headers);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                return ['success' => true];
            }

            return ['success' => false, 'error' => 'Vimeo API error: HTTP ' . $response->getStatusCode()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vimeo supports platform stats via the Vimeo API.
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public function supportsStats(): bool
    {
        return true;
    }

    /**
     * Fetch video statistics from Vimeo API for media linked to this server.
     * Batches up to 100 video IDs per API call. When $batchLimit > 0, only the
     * least-recently synced videos are processed (never-synced first).
     *
     * @param   int  $serverId    The server record ID
     * @param   int  $batchLimit  Max unique videos to sync (0 = unlimited)
     *
     * @return  array{success: bool, synced: int, remaining: int, errors: string[]}
     *
     * @throws \JsonException
     * @since   10.1.0
     */
    public function fetchPlatformStats(int $serverId, int $batchLimit = 0): array
    {
        $accessToken = $this->getServerAccessToken($serverId);

        if (empty($accessToken)) {
            return ['success' => false, 'synced' => 0, 'remaining' => 0, 'errors' => ['Vimeo: No access token configured']];
        }

        $totalMedia = static::getMediaVideoCount($serverId);
        $mediaRows  = static::getMediaVideoIds($serverId, 'filename', $batchLimit, 'vimeo');

        if (empty($mediaRows)) {
            return ['success' => true, 'synced' => 0, 'remaining' => 0, 'errors' => []];
        }

        // Extract Vimeo video IDs from embed URLs
        $videoMap = [];

        foreach ($mediaRows as $row) {
            $videoId = $this->extractVimeoVideoId($row['video_id']);

            if ($videoId !== null) {
                $videoMap[$videoId][] = $row['media_id'];
            }
        }

        // Apply batch limit to unique videos
        if ($batchLimit > 0 && \count($videoMap) > $batchLimit) {
            $videoMap = \array_slice($videoMap, 0, $batchLimit, true);
        }

        if (empty($videoMap)) {
            return ['success' => true, 'synced' => 0, 'remaining' => 0, 'errors' => []];
        }

        $synced  = 0;
        $errors  = [];
        $factory = new HttpFactory();
        $http    = $factory->getHttp();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept'        => 'application/vnd.vimeo.*+json;version=3.4',
        ];

        // Vimeo batch: filter by URIs (up to 100 per call)
        $chunks = array_chunk(array_keys($videoMap), 100);

        foreach ($chunks as $idBatch) {
            $uris = implode(',', array_map(fn ($id) => '/videos/' . $id, $idBatch));

            try {
                $params = http_build_query([
                    'uris'   => $uris,
                    'fields' => 'uri,stats',
                ]);

                $response = $http->get('https://api.vimeo.com/videos?' . $params, $headers);

                if ($response->getStatusCode() !== 200) {
                    $errors[] = 'Vimeo API error: HTTP ' . $response->getStatusCode();

                    continue;
                }

                $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

                foreach ($data['data'] ?? [] as $video) {
                    $vid = null;

                    if (preg_match('/\/videos\/(\d+)/', $video['uri'] ?? '', $m)) {
                        $vid = $m[1];
                    }

                    if ($vid === null || !isset($videoMap[$vid])) {
                        continue;
                    }

                    $plays = (int) ($video['stats']['plays'] ?? 0);

                    foreach ($videoMap[$vid] as $mediaId) {
                        static::upsertPlatformStats(
                            $mediaId,
                            $serverId,
                            'vimeo',
                            $vid,
                            [
                                'view_count' => $plays,
                                'play_count' => $plays,
                            ]
                        );
                        $synced++;
                    }
                }
            } catch (\Exception $e) {
                $errors[] = 'Vimeo batch: ' . $e->getMessage();
            }
        }

        if ($synced > 0) {
            static::updateServerSyncTimestamp($serverId);
        }

        $remaining = max(0, $totalMedia - $synced);

        return ['success' => empty($errors), 'synced' => $synced, 'remaining' => $remaining, 'errors' => $errors];
    }

    /**
     * Get available AJAX actions for this addon
     *
     * @return  array  List of available action names
     *
     * @since   10.1.0
     */
    public function getAjaxActions(): array
    {
        return [
            'testApi',
            'getMetadata',
            'fetchVideos',
            'fetchFolders',
        ];
    }

    /**
     * Get the Vimeo access token for a server by ID
     *
     * @param   int  $serverId  The server record ID
     *
     * @return  string  The access token or empty string if not found
     *
     * @throws \JsonException
     * @since   10.1.0
     */
    private function getServerAccessToken(int $serverId): string
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('id') . ' = ' . $serverId);
        $db->setQuery($query);
        $paramsJson = $db->loadResult();

        if (!$paramsJson) {
            return '';
        }

        try {
            $params = json_decode($paramsJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return '';
        }

        return $params['access_token'] ?? '';
    }

    /**
     * Handle testApi AJAX action
     *
     * Tests the Vimeo API connection using the configured access token
     *
     * @return  array  Response with success status
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function handleTestApiAction(): array
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();
        $serverId = $input->getInt('server_id', 0);

        if (!$serverId) {
            return [
                'success' => false,
                'error'   => Text::_('JBS_ADDON_VIMEO_NO_SERVER_ID'),
            ];
        }

        try {
            $accessToken = $this->getServerAccessToken($serverId);

            if (empty($accessToken)) {
                return [
                    'success' => false,
                    'error'   => Text::_('JBS_ADDON_VIMEO_NO_ACCESS_TOKEN'),
                ];
            }

            // Test API connection by getting user info
            $factory  = new HttpFactory();
            $http     = $factory->getHttp();
            $headers  = [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept'        => 'application/vnd.vimeo.*+json;version=3.4',
            ];

            $response = $http->get('https://api.vimeo.com/me', $headers);

            if ($response->getStatusCode() !== 200) {
                return [
                    'success' => false,
                    'error'   => Text::sprintf('JBS_ADDON_VIMEO_API_ERROR', $response->getStatusCode()),
                ];
            }

            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            return [
                'success' => true,
                'message' => Text::sprintf('JBS_ADDON_VIMEO_CONNECTION_SUCCESS', $data['name'] ?? 'User'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle getMetadata AJAX action
     *
     * Retrieves metadata for a Vimeo video
     *
     * @return  array  Response with video metadata
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function handleGetMetadataAction(): array
    {
        $app     = Factory::getApplication();
        $input   = $app->getInput();
        $videoId = $input->getString('video_id', '');

        if (empty($videoId)) {
            return [
                'success' => false,
                'error'   => Text::_('JBS_ADDON_VIMEO_NO_VIDEO_ID'),
            ];
        }

        try {
            $metadata = $this->getVideoMetadata($videoId);

            return [
                'success'  => true,
                'metadata' => $metadata,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * AJAX handler: retrieve Vimeo video metadata via oEmbed (no auth required)
     *
     * Called by the controller via cwmmediafile.xhr&type=vimeo&handler=getMetadata
     *
     * @param   Input  $input  Request input
     *
     * @return  array  Response with success flag and metadata
     *
     * @since   10.1.0
     */
    public function getMetadata(Input $input): array
    {
        $videoId = $input->getString('video_id', '');

        if (empty($videoId)) {
            return [
                'success' => false,
                'error'   => Text::_('JBS_ADDON_VIMEO_NO_VIDEO_ID'),
            ];
        }

        try {
            $metadata = $this->getVideoMetadata($videoId);

            return [
                'success'  => true,
                'metadata' => $metadata,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * AJAX handler: fetch paginated videos from the authenticated user's Vimeo account
     *
     * @param   Input  $input  Request input
     *
     * @return  array  Response with videos array and pagination metadata
     *
     * @since   10.1.0
     */
    public function fetchVideos(Input $input): array
    {
        $serverId = $input->getInt('server_id', 0);

        if (!$serverId) {
            return ['success' => false, 'error' => 'No server ID provided'];
        }

        try {
            $accessToken = $this->getServerAccessToken($serverId);
        } catch (\JsonException $e) {
            return ['success' => false, 'error' => 'no access_token'. $e->getMessage()];
        }

        $page     = max(1, $input->getInt('page', 1));
        $query    = $input->getString('query', '');
        $folderId = $input->getString('folder_id', '');

        $factory  = new HttpFactory();
        $http     = $factory->getHttp();
        $headers  = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept'        => 'application/vnd.vimeo.*+json;version=3.4',
        ];

        $params = [
            'per_page'  => 12,
            'page'      => $page,
            'sort'      => 'date',
            'direction' => 'desc',
            'fields'    => 'uri,name,duration,pictures,link,created_time',
        ];

        if (!empty($query)) {
            $params['query'] = $query;
        }

        $endpoint = empty($folderId)
            ? 'https://api.vimeo.com/me/videos'
            : 'https://api.vimeo.com/me/projects/' . rawurlencode($folderId) . '/videos';

        try {
            $response = $http->get($endpoint . '?' . http_build_query($params), $headers);

            if ($response->getStatusCode() !== 200) {
                return ['success' => false, 'error' => 'Vimeo API error (HTTP ' . $response->getStatusCode() . ')'];
            }

            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            if (!$data) {
                return ['success' => false, 'error' => 'Invalid API response'];
            }

            $videos = [];

            foreach ($data['data'] ?? [] as $video) {
                // Extract video ID from URI (e.g., /videos/123456789)
                $videoId = '';

                if (preg_match('/\/videos\/(\d+)/', $video['uri'] ?? '', $m)) {
                    $videoId = $m[1];
                }

                // Pick thumbnail: first size with width >= 295px
                $thumbnail = '';

                foreach ($video['pictures']['sizes'] ?? [] as $size) {
                    if (($size['width'] ?? 0) >= 295) {
                        // Strip query string from thumbnail URL
                        $thumb = $size['link'] ?? '';
                        $pos   = strpos($thumb, '?');

                        if ($pos !== false) {
                            $thumb = substr($thumb, 0, $pos);
                        }

                        $thumbnail = $thumb;
                        break;
                    }
                }

                $videos[] = [
                    'videoId'   => $videoId,
                    'title'     => $video['name'] ?? '',
                    'thumbnail' => $thumbnail,
                    'duration'  => $video['duration'] ?? 0,
                    'link'      => $video['link'] ?? '',
                    'createdAt' => $video['created_time'] ?? '',
                ];
            }

            $total      = $data['total'] ?? 0;
            $perPage    = 12;
            $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 1;

            return [
                'success'    => true,
                'videos'     => $videos,
                'total'      => $total,
                'page'       => $page,
                'perPage'    => $perPage,
                'totalPages' => $totalPages,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * AJAX handler: fetch folders (projects) from the authenticated user's Vimeo account
     *
     * @param   Input  $input  Request input
     *
     * @return  array  Response with folders array
     *
     * @throws \JsonException
     * @since   10.1.0
     */
    public function fetchFolders(Input $input): array
    {
        $serverId = $input->getInt('server_id', 0);

        if (!$serverId) {
            return ['success' => false, 'error' => 'No server ID provided'];
        }

        try {
            $accessToken = $this->getServerAccessToken($serverId);
        } catch (\JsonException $e) {
            return ['success' => false, 'error' => 'no access_token'. $e->getMessage()];
        }

        $factory = new HttpFactory();
        $http    = $factory->getHttp();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept'        => 'application/vnd.vimeo.*+json;version=3.4',
        ];

        try {
            $response = $http->get(
                'https://api.vimeo.com/me/projects?per_page=100&fields=uri,name',
                $headers
            );

            if ($response->getStatusCode() !== 200) {
                return ['success' => false, 'error' => 'Vimeo API error (HTTP ' . $response->getStatusCode() . ')'];
            }

            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            if (!$data) {
                return ['success' => false, 'error' => 'Invalid API response'];
            }

            $folders = [];

            foreach ($data['data'] ?? [] as $project) {
                // Extract folder ID from URI (e.g., /users/12345/projects/67890)
                $folderId = '';

                if (preg_match('/\/projects\/(\d+)/', $project['uri'] ?? '', $m)) {
                    $folderId = $m[1];
                }

                if ($folderId) {
                    $folders[] = [
                        'folderId' => $folderId,
                        'title'    => $project['name'] ?? '',
                    ];
                }
            }

            return [
                'success' => true,
                'folders' => $folders,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Render general fieldset fields
     *
     * @param   object  $media_form  Media files form
     * @param   bool    $new         If media is new
     *
     * @return  string  Rendered HTML
     *
     * @since   10.1.0
     */
    public function renderGeneral(object $media_form, bool $new): string
    {
        $html = '';

        foreach ($media_form->getFieldset('general') as $field) {
            if ($new && isset($media_form->s_params[$field->fieldname])) {
                $field->setValue($media_form->s_params[$field->fieldname]);
            }

            $html .= $field->renderField();
        }

        return $html;
    }

    /**
     * Render full tab with addTab/endTab wrappers
     *
     * @param   object  $media_form  Media files form
     * @param   bool    $new         If media is new
     *
     * @return  string  Rendered HTML
     *
     * @since   10.1.0
     */
    public function render(object $media_form, bool $new): string
    {
        $html = '<div class="tab-pane" id="vimeo">';
        $html .= $this->renderOptionsFields($media_form, $new);
        $html .= '</div>';

        return $html;
    }

    /**
     * Upload method (not supported for Vimeo URLs)
     *
     * @param   array|null  $data  Data to upload
     *
     * @return  mixed
     *
     * @since   10.1.0
     */
    protected function upload(?array $data): mixed
    {
        // Vimeo videos are referenced by URL, not uploaded
        return false;
    }

    /**
     * Detect metadata for a Vimeo video via oEmbed (no auth required).
     *
     * @param   Registry    $params      Media params (modified in place)
     * @param   object      $server      Server object
     * @param   string      $set_path    Server path prefix
     * @param   Registry    $path        Server params
     * @param   Cwmpodcast  $jbspodcast  Podcast helper
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[\Override]
    public function detectMetadata(Registry $params, object $server, string $set_path, Registry $path, Cwmpodcast $jbspodcast): void
    {
        $filename = $params->get('filename');

        if (empty($filename)) {
            return;
        }

        ['needsMime' => $needsMime, 'needsDuration' => $needsDuration] = $this->needsDetection($params);

        if ($needsMime) {
            $params->set('mime_type', 'video/mp4');
        }

        // Only fetch oEmbed if we still need duration or title
        if (!$needsDuration && !empty($params->get('title'))) {
            return;
        }

        $videoId = $this->extractVimeoVideoId($filename);

        if (!$videoId) {
            return;
        }

        try {
            $metadata = $this->getVideoMetadata($videoId);

            if (!empty($metadata['duration']) && $needsDuration) {
                $duration = $jbspodcast->formatTime((int) $metadata['duration']);
                $params->set('media_hours', str_pad((string) $duration->hours, 2, '0', STR_PAD_LEFT));
                $params->set('media_minutes', str_pad((string) $duration->minutes, 2, '0', STR_PAD_LEFT));
                $params->set('media_seconds', str_pad((string) $duration->seconds, 2, '0', STR_PAD_LEFT));
            }

            if (empty($params->get('title')) && !empty($metadata['title'])) {
                $params->set('title', $metadata['title']);
            }
        } catch (\Exception $e) {
            // oEmbed failure is non-fatal
        }
    }
}
