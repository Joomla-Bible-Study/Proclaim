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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Http\HttpFactory;
use Joomla\Input\Input;

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
    public function extractWistiaMediaHash(string $url): ?string
    {
        $patterns = [
            '/wistia\.com\/medias\/([a-z0-9]+)/i',
            '/fast\.wistia\.net\/embed\/iframe\/([a-z0-9]+)/i',
            '/wistia\.net\/medias\/([a-z0-9]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
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

            if ($response->code !== 200) {
                throw new \RuntimeException('Wistia oEmbed error: HTTP ' . $response->code);
            }

            $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
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

                if ($response->code !== 200) {
                    $errors[] = 'Wistia stats for ' . $hash . ': HTTP ' . $response->code;

                    continue;
                }

                $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);

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
        $db    = Factory::getContainer()->get('DatabaseDriver');
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

            if ($response->code !== 200) {
                return [
                    'success' => false,
                    'error'   => Text::sprintf('JBS_ADDON_WISTIA_API_ERROR', $response->code),
                ];
            }

            $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);

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

            if ($response->code !== 200) {
                return ['success' => false, 'error' => 'Wistia API error (HTTP ' . $response->code . ')'];
            }

            $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);

            if (!\is_array($data)) {
                return ['success' => false, 'error' => 'Invalid API response'];
            }

            // Total count is in the WTotal-Count response header
            $total = 0;

            foreach ((array) $response->headers as $headerName => $headerValue) {
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

            if ($response->code !== 200) {
                return ['success' => false, 'error' => 'Wistia API error (HTTP ' . $response->code . ')'];
            }

            $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);

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
    public function renderGeneral($media_form, bool $new): string
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
    public function render($media_form, bool $new): string
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
}
