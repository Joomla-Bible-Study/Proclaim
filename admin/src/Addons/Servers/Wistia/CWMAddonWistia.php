<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Wistia;

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
 * Wistia Server Addon
 *
 * Provides integration with Wistia video platform including:
 * - URL parsing and embed conversion
 * - Video metadata retrieval via Wistia API
 * - Support for Wistia Pro features
 *
 * @since 10.1.0
 */
class CWMAddonWistia extends CWMAddon
{
    /**
     * Addon name
     *
     * @var string
     * @since 10.1.0
     */
    protected $name = 'Wistia';

    /**
     * Addon description
     *
     * @var string
     * @since 10.1.0
     */
    protected $description = 'Used for Wistia server access';

    /**
     * URL patterns that identify Wistia content.
     *
     * @return  string[]
     *
     * @since   10.1.0
     */
    public function getUrlPatterns(): array
    {
        return ['/(wistia\.com|wistia\.net)/i'];
    }

    /**
     * {@inheritdoc}
     *
     * @since   10.1.0
     */
    public function getMigrationPatterns(): array
    {
        return [
            'type'     => 'wistia',
            'label'    => 'Wistia',
            'patterns' => [
                '/wistia\.(com|net)/i',
                '/fast\.wistia/i',
            ],
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
        $result = [];
        $hash   = $this->extractWistiaMediaHash($combined);

        if ($hash) {
            $result['filename'] = 'https://fast.wistia.net/embed/iframe/' . $hash;
        } else {
            $result['filename'] = $filename;
        }

        // Map extracted URL params to embed option form fields
        $sourceQuery = CwmserverMigrationHelper::extractSourceUrlParams($combined, 'wistia');

        $wsParamMap = [
            'muted'                 => 'ws_muted',
            'playerColor'           => 'ws_player_color',
            'controlsVisibleOnLoad' => 'ws_controls_visible',
            'playbar'               => 'ws_playbar',
            'endVideoBehavior'      => 'ws_end_behavior',
            'doNotTrack'            => 'ws_dnt',
            'time'                  => 'ws_time',
            'resumable'             => 'ws_resumable',
            'playbackRateControl'   => 'ws_speed',
        ];

        foreach ($wsParamMap as $urlParam => $formField) {
            if (isset($sourceQuery[$urlParam]) && $sourceQuery[$urlParam] !== '') {
                $result[$formField] = $sourceQuery[$urlParam];
            }
        }

        if (isset($sourceQuery['autoPlay']) && $sourceQuery['autoPlay'] === 'true') {
            $result['autostart'] = 'true';
        }

        $result['player']    = '1';
        $result['mediacode'] = '';

        return $result;
    }

    /**
     * Build a Wistia embed URL with all form field params applied.
     *
     * @param   string    $filename     The raw Wistia URL
     * @param   Registry  $mediaParams  Merged template + media params
     *
     * @return  string  The embed-ready URL with query params
     *
     * @since   10.1.0
     */
    public function buildEmbedUrl(string $filename, Registry $mediaParams): string
    {
        $baseUrl = $this->convertWistia($filename);
        $parts   = parse_url($baseUrl);
        $query   = [];

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $autostart = $mediaParams->get('autostart', '');

        if ($autostart === 'true') {
            $query['autoPlay'] = 'true';
        } elseif ($autostart === 'false') {
            $query['autoPlay'] = 'false';
        }

        $fieldMap = [
            'ws_muted'            => 'muted',
            'ws_player_color'     => 'playerColor',
            'ws_controls_visible' => 'controlsVisibleOnLoad',
            'ws_playbar'          => 'playbar',
            'ws_end_behavior'     => 'endVideoBehavior',
            'ws_dnt'              => 'doNotTrack',
            'ws_time'             => 'time',
            'ws_resumable'        => 'resumable',
            'ws_speed'            => 'playbackRateControl',
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
     * Render inline Wistia player (responsive 16:9 iframe).
     *
     * @param   string    $url          The raw Wistia URL
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
            . ' allow="autoplay; fullscreen" allowtransparency="true" frameborder="0" loading="lazy"'
            . ' style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"></iframe>'
            . '</div>';
    }

    /**
     * Convert Wistia URL to embed format
     *
     * Supports various Wistia URL formats:
     * - https://home.wistia.com/medias/abc123xyz
     * - https://youraccount.wistia.com/medias/abc123xyz
     * - https://fast.wistia.net/embed/iframe/abc123xyz
     *
     * @param   string  $url  The Wistia URL to convert
     *
     * @return  string  The embed URL (//fast.wistia.net/embed/iframe/{id})
     *
     * @since   10.1.0
     */
    /**
     * {@inheritdoc}
     *
     * @since   10.1.0
     */
    #[\Override]
    public function normalizeFilename(string $filename): string
    {
        return $this->convertWistia($filename);
    }

    public function convertWistia(string $url = ''): string
    {
        if (empty($url)) {
            return '';
        }

        // Extract media hash/ID from various Wistia URL formats
        $patterns = [
            // Standard: wistia.com/medias/abc123xyz
            '/wistia\.com\/medias\/([a-z0-9]+)/i',
            // Embed: fast.wistia.net/embed/iframe/abc123xyz
            '/fast\.wistia\.net\/embed\/iframe\/([a-z0-9]+)/i',
            // Direct media: wistia.net/medias/abc123xyz
            '/wistia\.net\/medias\/([a-z0-9]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return 'https://fast.wistia.net/embed/iframe/' . $matches[1];
            }
        }

        // If no match, return original URL
        return $url;
    }

    /**
     * Extract Wistia media hash from URL
     *
     * @param   string  $url  The Wistia URL
     *
     * @return  string|null  The media hash or null if not found
     *
     * @since   10.1.0
     */
    /**
     * {@inheritdoc}
     *
     * @since   10.1.0
     */
    #[\Override]
    public static function extractMediaId(string $text): ?string
    {
        $patterns = [
            '/wistia\.com\/medias\/([a-z0-9]+)/i',
            '/fast\.wistia\.net\/embed\/iframe\/([a-z0-9]+)/i',
            '/wistia\.net\/medias\/([a-z0-9]+)/i',
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
    public function extractWistiaMediaHash(string $url): ?string
    {
        return static::extractMediaId($url);
    }

    /**
     * Check if a URL is a Wistia URL
     *
     * @param   string  $url  The URL to check
     *
     * @return  bool  True if Wistia URL, false otherwise
     *
     * @since   10.1.0
     */
    public static function isWistiaUrl(string $url): bool
    {
        return preg_match('/wistia\.(com|net)/', $url) === 1;
    }

    /**
     * Get Wistia video metadata via oEmbed API (no auth required)
     *
     * @param   string  $mediaHash  The Wistia media hash
     *
     * @return  array  Metadata array with title, description, thumbnail, duration
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function getVideoMetadata(string $mediaHash): array
    {
        $videoUrl = 'https://home.wistia.com/medias/' . $mediaHash;
        $url      = 'https://fast.wistia.com/oembed?url=' . urlencode($videoUrl);
        $factory  = new HttpFactory();
        $http     = $factory->getHttp();
        $headers  = [
            'Accept' => 'application/json',
        ];

        try {
            $response = $http->get($url, $headers);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException('Wistia oEmbed error: HTTP ' . $response->getStatusCode());
            }

            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new \RuntimeException('Wistia oEmbed error: ' . $e->getMessage());
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
    }

    /**
     * Wistia supports platform stats via the Wistia Stats API.
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
     * Wistia supports description sync via PUT API.
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
     * Push a description to a Wistia video via PUT API.
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
        $hashedId = trim($params['filename'] ?? '');

        if (empty($hashedId)) {
            return ['success' => false, 'error' => 'Could not extract Wistia hashed ID'];
        }

        $apiToken = $this->getServerApiToken((int) $row->server_id);

        if (empty($apiToken)) {
            return ['success' => false, 'error' => 'No Wistia API token configured'];
        }

        try {
            $http     = (new \Joomla\Http\HttpFactory())->getHttp();
            $headers  = [
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ];

            $body     = http_build_query(['description' => $description]);
            $response = $http->put(
                'https://api.wistia.com/v1/medias/' . rawurlencode($hashedId) . '.json',
                $body,
                $headers
            );

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                return ['success' => true];
            }

            return ['success' => false, 'error' => 'Wistia API error: HTTP ' . $response->getStatusCode()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Fetch video statistics from Wistia Stats API for media linked to this server.
     * Per-video endpoint (no batch); rate-limited to ~600/min.
     * When $batchLimit > 0, only the least-recently-synced videos are processed.
     *
     * @param   int  $serverId    The server record ID
     * @param   int  $batchLimit  Max unique videos to sync (0 = unlimited)
     *
     * @return  array{success: bool, synced: int, remaining: int, errors: string[]}
     *
     * @since   10.1.0
     */
    public function fetchPlatformStats(int $serverId, int $batchLimit = 0): array
    {
        $apiToken = $this->getServerApiToken($serverId);

        if (empty($apiToken)) {
            return ['success' => false, 'synced' => 0, 'remaining' => 0, 'errors' => ['Wistia: No API token configured']];
        }

        $totalMedia = static::getMediaVideoCount($serverId);
        $mediaRows  = static::getMediaVideoIds($serverId, 'filename', $batchLimit, 'wistia');

        if (empty($mediaRows)) {
            return ['success' => true, 'synced' => 0, 'remaining' => 0, 'errors' => []];
        }

        // Extract Wistia media hashes from embed URLs
        $videoMap = [];

        foreach ($mediaRows as $row) {
            $hash = $this->extractWistiaMediaHash($row['video_id']);

            if ($hash !== null) {
                $videoMap[$hash][] = $row['media_id'];
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
            'Authorization' => 'Bearer ' . $apiToken,
        ];

        foreach ($videoMap as $hash => $mediaIds) {
            try {
                $response = $http->get(
                    'https://api.wistia.com/v1/medias/' . rawurlencode($hash) . '/stats.json',
                    $headers
                );

                if ($response->getStatusCode() !== 200) {
                    $errors[] = 'Wistia stats for ' . $hash . ': HTTP ' . $response->getStatusCode();

                    continue;
                }

                $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

                foreach ($mediaIds as $mediaId) {
                    static::upsertPlatformStats(
                        $mediaId,
                        $serverId,
                        'wistia',
                        $hash,
                        [
                            'play_count'    => (int) ($data['play_count'] ?? 0),
                            'load_count'    => (int) ($data['load_count'] ?? 0),
                            'hours_watched' => (float) ($data['hours_watched'] ?? 0.0),
                            'engagement'    => isset($data['load_count']) && $data['load_count'] > 0
                                ? round(($data['play_count'] ?? 0) / $data['load_count'] * 100, 2)
                                : null,
                        ]
                    );
                    $synced++;
                }

                // Rate limit: ~100ms between calls (stays well within 600/min)
                usleep(100000);
            } catch (\Exception $e) {
                $errors[] = 'Wistia ' . $hash . ': ' . $e->getMessage();
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
            'fetchProjects',
        ];
    }

    /**
     * Get the Wistia API token for a server by ID
     *
     * @param   int  $serverId  The server record ID
     *
     * @return  string  The API token, or empty string if not found
     *
     * @since   10.1.0
     */
    private function getServerApiToken(int $serverId): string
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

        return $params['api_token'] ?? '';
    }

    /**
     * Handle testApi AJAX action
     *
     * Tests the Wistia API connection using the configured API token
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
                'error'   => Text::_('JBS_ADDON_WISTIA_NO_SERVER_ID'),
            ];
        }

        try {
            $apiToken = $this->getServerApiToken($serverId);

            if (empty($apiToken)) {
                return [
                    'success' => false,
                    'error'   => Text::_('JBS_ADDON_WISTIA_NO_API_TOKEN'),
                ];
            }

            // Test API connection by getting account info
            $factory = new HttpFactory();
            $http    = $factory->getHttp();
            $headers = [
                'Authorization' => 'Bearer ' . $apiToken,
            ];

            $response = $http->get('https://api.wistia.com/v1/account.json', $headers);

            if ($response->getStatusCode() !== 200) {
                return [
                    'success' => false,
                    'error'   => Text::sprintf('JBS_ADDON_WISTIA_API_ERROR', $response->getStatusCode()),
                ];
            }

            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            return [
                'success' => true,
                'message' => Text::sprintf('JBS_ADDON_WISTIA_CONNECTION_SUCCESS', $data['name'] ?? 'Account'),
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
     * Retrieves metadata for a Wistia video
     *
     * @return  array  Response with video metadata
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function handleGetMetadataAction(): array
    {
        $app       = Factory::getApplication();
        $input     = $app->getInput();
        $mediaHash = $input->getString('media_hash', '');

        if (empty($mediaHash)) {
            return [
                'success' => false,
                'error'   => Text::_('JBS_ADDON_WISTIA_NO_MEDIA_HASH'),
            ];
        }

        try {
            $metadata = $this->getVideoMetadata($mediaHash);

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
     * AJAX handler: retrieve Wistia video metadata via oEmbed (no auth required)
     *
     * Called by the controller via cwmmediafile.xhr&type=wistia&handler=getMetadata
     *
     * @param   Input  $input  Request input
     *
     * @return  array  Response with success flag and metadata
     *
     * @since   10.1.0
     */
    public function getMetadata(Input $input): array
    {
        $mediaHash = $input->getString('media_hash', '');

        if (empty($mediaHash)) {
            return [
                'success' => false,
                'error'   => Text::_('JBS_ADDON_WISTIA_NO_MEDIA_HASH'),
            ];
        }

        try {
            $metadata = $this->getVideoMetadata($mediaHash);

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
     * AJAX handler: fetch paginated videos from the authenticated Wistia account
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

        $apiToken = $this->getServerApiToken($serverId);

        if (empty($apiToken)) {
            return ['success' => false, 'error' => 'no api_token'];
        }

        $page      = max(1, $input->getInt('page', 1));
        $name      = $input->getString('name', '');
        $projectId = $input->getInt('project_id', 0);

        $params = [
            'page'           => $page,
            'per_page'       => 12,
            'sort_by'        => 'updated',
            'sort_direction' => 'desc',
            'type'           => 'Video',
        ];

        if (!empty($name)) {
            $params['name'] = $name;
        }

        if ($projectId) {
            $params['project_id'] = $projectId;
        }

        $factory = new HttpFactory();
        $http    = $factory->getHttp();
        $headers = [
            'Authorization' => 'Bearer ' . $apiToken,
        ];

        try {
            $response = $http->get(
                'https://api.wistia.com/v1/medias.json?' . http_build_query($params),
                $headers
            );

            if ($response->getStatusCode() !== 200) {
                return ['success' => false, 'error' => 'Wistia API error (HTTP ' . $response->getStatusCode() . ')'];
            }

            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            if (!\is_array($data)) {
                return ['success' => false, 'error' => 'Invalid API response'];
            }

            // Total count is in the WTotal-Count response header
            $total = 0;

            foreach ($response->getHeaders() as $headerName => $headerValue) {
                if (strtolower((string) $headerName) === 'wtotal-count') {
                    $total = (int) (\is_array($headerValue) ? $headerValue[0] : $headerValue);
                    break;
                }
            }

            // Fallback if header not present
            if ($total === 0) {
                $total = \count($data);
            }

            $videos = [];

            foreach ($data as $media) {
                $thumbnail = $media['thumbnail']['url'] ?? '';

                $videos[] = [
                    'hashedId'  => $media['hashed_id'] ?? '',
                    'title'     => $media['name'] ?? '',
                    'thumbnail' => $thumbnail,
                    'duration'  => (int) round((float) ($media['duration'] ?? 0)),
                    'link'      => 'https://home.wistia.com/medias/' . ($media['hashed_id'] ?? ''),
                    'createdAt' => $media['updated'] ?? '',
                ];
            }

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
     * AJAX handler: fetch projects from the authenticated Wistia account
     *
     * @param   Input  $input  Request input
     *
     * @return  array  Response with projects array
     *
     * @since   10.1.0
     */
    public function fetchProjects(Input $input): array
    {
        $serverId = $input->getInt('server_id', 0);

        if (!$serverId) {
            return ['success' => false, 'error' => 'No server ID provided'];
        }

        $apiToken = $this->getServerApiToken($serverId);

        if (empty($apiToken)) {
            return ['success' => false, 'error' => 'no api_token'];
        }

        $factory = new HttpFactory();
        $http    = $factory->getHttp();
        $headers = [
            'Authorization' => 'Bearer ' . $apiToken,
        ];

        try {
            $response = $http->get(
                'https://api.wistia.com/v1/projects.json?per_page=100&sort_by=name&sort_direction=asc',
                $headers
            );

            if ($response->getStatusCode() !== 200) {
                return ['success' => false, 'error' => 'Wistia API error (HTTP ' . $response->getStatusCode() . ')'];
            }

            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            if (!\is_array($data)) {
                return ['success' => false, 'error' => 'Invalid API response'];
            }

            $projects = [];

            foreach ($data as $project) {
                if (isset($project['id'])) {
                    $projects[] = [
                        'projectId' => (int) $project['id'],
                        'title'     => $project['name'] ?? '',
                    ];
                }
            }

            return [
                'success'  => true,
                'projects' => $projects,
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
        $html = '<div class="tab-pane" id="wistia">';
        $html .= $this->renderOptionsFields($media_form, $new);
        $html .= '</div>';

        return $html;
    }

    /**
     * Upload method (not supported for Wistia URLs)
     *
     * @param   array|null  $data  Data to upload
     *
     * @return  mixed
     *
     * @since   10.1.0
     */
    protected function upload(?array $data): mixed
    {
        // Wistia videos are referenced by URL, not uploaded
        return false;
    }

    /**
     * Detect metadata for a Wistia video via oEmbed (no auth required).
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

        if (!$needsDuration && !empty($params->get('title'))) {
            return;
        }

        $mediaHash = $this->extractWistiaMediaHash($filename);

        if (!$mediaHash) {
            return;
        }

        try {
            $metadata = $this->getVideoMetadata($mediaHash);

            if (!empty($metadata['duration']) && $needsDuration) {
                $duration = $jbspodcast->formatTime((int) round((float) $metadata['duration']));
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
