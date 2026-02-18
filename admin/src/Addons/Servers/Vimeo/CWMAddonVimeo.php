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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

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
                return '//player.vimeo.com/video/' . $matches[1];
            }
        }

        // If no match, return original URL
        return $url;
    }

    /**
     * Extract Vimeo video ID from URL
     *
     * @param   string  $url  The Vimeo URL
     *
     * @return  string|null  The video ID or null if not found
     *
     * @since   10.1.0
     */
    public function extractVimeoVideoId(string $url): ?string
    {
        $patterns = [
            '/vimeo\.com\/(\d+)/',
            '/player\.vimeo\.com\/video\/(\d+)/',
            '/vimeo\.com\/channels\/[^\/]+\/(\d+)/',
            '/vimeo\.com\/groups\/[^\/]+\/videos\/(\d+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
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
            $http     = Factory::getApplication()->getHttpFactory()->getHttp();
            $response = $http->get($url);

            if ($response->code !== 200) {
                return [
                    'title'       => '',
                    'description' => '',
                    'thumbnail'   => '',
                    'duration'    => 0,
                ];
            }

            $data = json_decode($response->body, true);

            if (!$data) {
                return [
                    'title'       => '',
                    'description' => '',
                    'thumbnail'   => '',
                    'duration'    => 0,
                ];
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
        ];
    }

    /**
     * Handle testApi AJAX action
     *
     * Tests the Vimeo API connection using the configured access token
     *
     * @return  array  Response with success status
     *
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

        // Load server params
        try {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_servers'))
                ->where($db->quoteName('id') . ' = ' . (int) $serverId);
            $db->setQuery($query);
            $paramsJson = $db->loadResult();

            if (!$paramsJson) {
                return [
                    'success' => false,
                    'error'   => Text::_('JBS_ADDON_VIMEO_SERVER_NOT_FOUND'),
                ];
            }

            $params      = json_decode($paramsJson, true);
            $accessToken = $params['access_token'] ?? '';

            if (empty($accessToken)) {
                return [
                    'success' => false,
                    'error'   => Text::_('JBS_ADDON_VIMEO_NO_ACCESS_TOKEN'),
                ];
            }

            // Test API connection by getting user info
            $http    = $app->getHttpFactory()->getHttp();
            $headers = [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept'        => 'application/vnd.vimeo.*+json;version=3.4',
            ];

            $response = $http->get('https://api.vimeo.com/me', $headers);

            if ($response->code !== 200) {
                return [
                    'success' => false,
                    'error'   => Text::sprintf('JBS_ADDON_VIMEO_API_ERROR', $response->code),
                ];
            }

            $data = json_decode($response->body, true);

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

        foreach ($media_form->getFieldset('files_settings') as $field) {
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
}
