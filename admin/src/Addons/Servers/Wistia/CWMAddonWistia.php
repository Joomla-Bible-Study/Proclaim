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
                return '//fast.wistia.net/embed/iframe/' . $matches[1];
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
        try {
            $videoUrl = 'https://home.wistia.com/medias/' . $mediaHash;
            $url      = 'https://fast.wistia.com/oembed?url=' . urlencode($videoUrl);
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
     * Tests the Wistia API connection using the configured API token
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
                'error'   => Text::_('JBS_ADDON_WISTIA_NO_SERVER_ID'),
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
                    'error'   => Text::_('JBS_ADDON_WISTIA_SERVER_NOT_FOUND'),
                ];
            }

            $params   = json_decode($paramsJson, true);
            $apiToken = $params['api_token'] ?? '';

            if (empty($apiToken)) {
                return [
                    'success' => false,
                    'error'   => Text::_('JBS_ADDON_WISTIA_NO_API_TOKEN'),
                ];
            }

            // Test API connection by getting account info
            $http    = $app->getHttpFactory()->getHttp();
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

            $data = json_decode($response->body, true);

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
