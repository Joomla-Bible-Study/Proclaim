<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Rumble;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\Registry\Registry;

/**
 * Rumble Server Addon
 *
 * Provides integration with Rumble video platform.
 * Supports URL parsing and embed conversion for public videos.
 *
 * @since 10.1.0
 */
class CWMAddonRumble extends CWMAddon
{
    /**
     * Addon name
     *
     * @var string
     * @since 10.1.0
     */
    protected $name = 'Rumble';

    /**
     * Addon description
     *
     * @var string
     * @since 10.1.0
     */
    protected $description = 'Used for Rumble server access';

    /**
     * URL patterns that identify Rumble content.
     *
     * @return  string[]
     *
     * @since   10.1.0
     */
    public function getUrlPatterns(): array
    {
        return ['/(rumble\.com)/i'];
    }

    /**
     * Build a Rumble embed URL with all form field params applied.
     *
     * @param   string    $filename     The raw Rumble URL
     * @param   Registry  $mediaParams  Merged template + media params
     *
     * @return  string  The embed-ready URL with query params
     *
     * @since   10.1.0
     */
    public function buildEmbedUrl(string $filename, Registry $mediaParams): string
    {
        $baseUrl = $this->convertRumble($filename);
        $parts   = parse_url($baseUrl);
        $query   = [];

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        // Rumble uses autoplay=2 for autoplay
        $autostart = $mediaParams->get('autostart', '');

        if ($autostart === 'true') {
            $query['autoplay'] = '2';
        } elseif ($autostart === 'false') {
            $query['autoplay'] = '0';
        }

        $fieldMap = [
            'rb_rel' => 'rel',
            'rb_pub' => 'pub',
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
     * Render inline Rumble player (responsive 16:9 iframe).
     *
     * @param   string    $url          The raw Rumble URL
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
            . '<iframe class="playhit rumble" data-id="' . $mediaId . '" src="' . htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8') . '"'
            . ' allow="autoplay; encrypted-media" allowfullscreen'
            . ' style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"></iframe>'
            . '</div>';
    }

    /**
     * Convert Rumble URL to embed format
     *
     * Supports various Rumble URL formats:
     * - https://rumble.com/embed/v1abc23/
     * - https://rumble.com/v1abc23-video-title.html
     *
     * @param   string  $url  The Rumble URL to convert
     *
     * @return  string  The embed URL (//rumble.com/embed/{id}/)
     *
     * @since   10.1.0
     */
    public function convertRumble(string $url = ''): string
    {
        if (empty($url)) {
            return '';
        }

        // Already an embed URL
        if (preg_match('/rumble\.com\/embed\/(v[a-z0-9]+)/i', $url, $matches)) {
            return '//rumble.com/embed/' . $matches[1] . '/';
        }

        // Standard URL: rumble.com/v1abc23-video-title.html
        if (preg_match('/rumble\.com\/(v[a-z0-9]+)(?:-[^\/]*)?\.html/i', $url, $matches)) {
            return '//rumble.com/embed/' . $matches[1] . '/';
        }

        // If no match, return original URL
        return $url;
    }

    /**
     * Check if a URL is a Rumble URL
     *
     * @param   string  $url  The URL to check
     *
     * @return  bool  True if Rumble URL, false otherwise
     *
     * @since   10.1.0
     */
    public static function isRumbleUrl(string $url): bool
    {
        return preg_match('/rumble\.com/', $url) === 1;
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
        $html = '<div class="tab-pane" id="rumble">';
        $html .= $this->renderOptionsFields($media_form, $new);
        $html .= '</div>';

        return $html;
    }

    /**
     * Upload method (not supported for Rumble URLs)
     *
     * @param   array|null  $data  Data to upload
     *
     * @return  mixed
     *
     * @since   10.1.0
     */
    protected function upload(?array $data): mixed
    {
        // Rumble videos are referenced by URL, not uploaded
        return false;
    }

    /**
     * Detect metadata for a Rumble video (MIME default only).
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
