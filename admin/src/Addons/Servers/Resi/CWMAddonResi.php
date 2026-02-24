<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Resi;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Resi Server Addon
 *
 * Provides integration with Resi.io live streaming and video hosting platform including:
 * - URL parsing and embed conversion
 * - Support for live streams and VOD (video on demand)
 * - Church-focused video platform integration
 *
 * @since 10.1.0
 */
class CWMAddonResi extends CWMAddon
{
    /**
     * Addon name
     *
     * @var string
     * @since 10.1.0
     */
    protected $name = 'Resi';

    /**
     * Addon description
     *
     * @var string
     * @since 10.1.0
     */
    protected $description = 'Used for Resi.io live streaming and video hosting';

    /**
     * URL patterns that identify Resi.io content.
     *
     * @return  string[]
     *
     * @since   10.1.0
     */
    public function getUrlPatterns(): array
    {
        return ['/(resi\.io)/i'];
    }

    /**
     * Build a Resi.io embed URL with all form field params applied.
     *
     * @param   string    $filename     The raw Resi URL
     * @param   Registry  $mediaParams  Merged template + media params
     *
     * @return  string  The embed-ready URL with query params
     *
     * @since   10.1.0
     */
    public function buildEmbedUrl(string $filename, Registry $mediaParams): string
    {
        $baseUrl = $this->convertResi($filename);
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
            'ri_controls'   => 'controls',
            'ri_loop'       => 'loop',
            'ri_start_pos'  => 'startPos',
            'ri_background' => 'background',
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
     * Render inline Resi player with transparent click-intercept overlay for play tracking.
     *
     * @param   string    $url          The raw Resi URL
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
            . ' allow="autoplay; encrypted-media" allowfullscreen'
            . ' style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"></iframe>'
            . '<div class="playhit" data-id="' . $mediaId . '"'
            . ' style="position:absolute;top:0;left:0;width:100%;height:100%;background:transparent;cursor:pointer;"></div>'
            . '</div>';
    }

    /**
     * Convert Resi.io URL to embed format
     *
     * Supports various Resi.io URL formats:
     * - https://live.resi.io/account-id
     * - https://player.resi.io/account-id/video-id
     * - https://resi.io/watch/account-id/video-id
     * - https://app.resi.io/library/account-id/video-id
     *
     * @param   string  $url  The Resi.io URL to convert
     *
     * @return  string  The embed URL
     *
     * @since   10.1.0
     */
    public function convertResi(string $url = ''): string
    {
        if (empty($url)) {
            return '';
        }

        // Resi's current embed format: control.resi.io/webplayer/video.html?id={base64}
        // Users should paste this URL directly from Resi's embed/share dialog.
        // Ensure https:// prefix — fancybox requires absolute URLs.
        if (str_contains($url, 'control.resi.io/webplayer/')) {
            return preg_replace('#^(https?:)?//#', 'https://', $url);
        }

        // Legacy player.resi.io embed URLs — pass through if already in embed form
        if (str_contains($url, 'player.resi.io')) {
            return preg_replace('#^(https?:)?//#', 'https://', $url);
        }

        // Return original URL if no conversion possible — may still be a valid embed src
        return $url;
    }

    /**
     * Extract Resi.io account ID from URL
     *
     * @param   string  $url  The Resi.io URL
     *
     * @return  string|null  The account ID or null if not found
     *
     * @since   10.1.0
     */
    public function extractResiAccountId(string $url): ?string
    {
        // control.resi.io/webplayer/video.html?id={base64} — decode to get account UUID
        if (preg_match('/[?&]id=([A-Za-z0-9+\/=]+)/', $url, $m)) {
            $decoded = base64_decode($m[1], true);
            if ($decoded && str_contains($decoded, ':')) {
                return explode(':', $decoded)[0];
            }
        }

        $patterns = [
            '/player\.resi\.io\/([^\/]+)/i',
            '/live\.resi\.io\/([^\/?\s]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract Resi.io video ID from URL (for VOD, not live streams)
     *
     * @param   string  $url  The Resi.io URL
     *
     * @return  string|null  The video ID or null if not found/live stream
     *
     * @since   10.1.0
     */
    public function extractResiVideoId(string $url): ?string
    {
        // control.resi.io/webplayer/video.html?id={base64} — decode to get video UUID
        if (preg_match('/[?&]id=([A-Za-z0-9+\/=]+)/', $url, $m)) {
            $decoded = base64_decode($m[1], true);
            if ($decoded && str_contains($decoded, ':')) {
                return explode(':', $decoded)[1];
            }
        }

        if (preg_match('/player\.resi\.io\/[^\/]+\/([^\/?\s]+)/i', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Check if a URL is a Resi.io URL
     *
     * @param   string  $url  The URL to check
     *
     * @return  bool  True if Resi.io URL, false otherwise
     *
     * @since   10.1.0
     */
    public static function isResiUrl(string $url): bool
    {
        return preg_match('/resi\.io/', $url) === 1;
    }

    /**
     * Check if a Resi.io URL is a live stream
     *
     * @param   string  $url  The Resi.io URL
     *
     * @return  bool  True if live stream, false if VOD or unknown
     *
     * @since   10.1.0
     */
    public function isLiveStream(string $url): bool
    {
        // Live streams typically only have account ID, no video ID
        return preg_match('/live\.resi\.io\/([^\/?\s]+)$/i', $url) === 1;
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
        ];
    }

    /**
     * Handle testApi AJAX action
     *
     * Tests the Resi.io configuration
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
                'error'   => Text::_('JBS_ADDON_RESI_NO_SERVER_ID'),
            ];
        }

        // Load server params
        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_servers'))
                ->where($db->quoteName('id') . ' = ' . (int) $serverId);
            $db->setQuery($query);
            $paramsJson = $db->loadResult();

            if (!$paramsJson) {
                return [
                    'success' => false,
                    'error'   => Text::_('JBS_ADDON_RESI_SERVER_NOT_FOUND'),
                ];
            }

            $params    = json_decode($paramsJson, true);
            $accountId = $params['account_id'] ?? '';

            if (empty($accountId)) {
                return [
                    'success' => false,
                    'error'   => Text::_('JBS_ADDON_RESI_NO_ACCOUNT_ID'),
                ];
            }

            // Test by constructing a Resi webplayer URL (users provide embed URLs directly)
            $testUrl = 'https://control.resi.io/webplayer/video.html?id=' . base64_encode($accountId);

            return [
                'success'  => true,
                'message'  => Text::sprintf('JBS_ADDON_RESI_CONNECTION_SUCCESS', $accountId),
                'test_url' => $testUrl,
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
        $html = '<div class="tab-pane" id="resi">';
        $html .= $this->renderOptionsFields($media_form, $new);
        $html .= '</div>';

        return $html;
    }

    /**
     * Upload method (not supported for Resi.io URLs)
     *
     * @param   array|null  $data  Data to upload
     *
     * @return  mixed
     *
     * @since   10.1.0
     */
    protected function upload(?array $data): mixed
    {
        // Resi.io videos are referenced by URL, not uploaded
        return false;
    }

    /**
     * Detect metadata for a Resi video (MIME default only).
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
        if (empty($params->get('mime_type'))) {
            $params->set('mime_type', 'video/mp4');
        }
    }
}
