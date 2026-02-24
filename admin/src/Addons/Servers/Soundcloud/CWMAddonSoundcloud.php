<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Soundcloud;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\Registry\Registry;

/**
 * SoundCloud Server Addon
 *
 * Provides integration with SoundCloud audio hosting platform.
 * Supports embed URLs for tracks and playlists.
 *
 * @since 10.1.0
 */
class CWMAddonSoundcloud extends CWMAddon
{
    /**
     * Addon name
     *
     * @var string
     * @since 10.1.0
     */
    protected $name = 'Soundcloud';

    /**
     * Addon description
     *
     * @var string
     * @since 10.1.0
     */
    protected $description = 'Used for SoundCloud server access';

    /**
     * URL patterns that identify SoundCloud content.
     *
     * @return  string[]
     *
     * @since   10.1.0
     */
    public function getUrlPatterns(): array
    {
        return ['/(soundcloud\.com)/i'];
    }

    /**
     * Build a SoundCloud embed URL with all form field params applied.
     *
     * @param   string    $filename     The raw SoundCloud URL
     * @param   Registry  $mediaParams  Merged template + media params
     *
     * @return  string  The embed-ready URL with query params
     *
     * @since   10.1.0
     */
    public function buildEmbedUrl(string $filename, Registry $mediaParams): string
    {
        $baseUrl = $this->convertSoundcloud($filename);
        $parts   = parse_url($baseUrl);
        $query   = [];

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        // Map autostart → SoundCloud auto_play
        $autostart = $mediaParams->get('autostart', '');

        if ($autostart === 'true') {
            $query['auto_play'] = 'true';
        } elseif ($autostart === 'false') {
            $query['auto_play'] = 'false';
        }

        $fieldMap = [
            'sc_color'         => 'color',
            'sc_hide_related'  => 'hide_related',
            'sc_show_comments' => 'show_comments',
            'sc_show_user'     => 'show_user',
            'sc_visual'        => 'visual',
        ];

        foreach ($fieldMap as $formField => $urlParam) {
            $val = $mediaParams->get($formField, '');

            if ($val !== '') {
                $query[$urlParam] = $val;
            }
        }

        $base = strtok($baseUrl, '?');

        return $base . (!empty($query) ? '?' . http_build_query($query) : '');
    }

    /**
     * Render inline SoundCloud player (horizontal audio widget, non-16:9).
     *
     * @param   string    $url          The raw SoundCloud URL
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
        $height   = $mediaParams->get('playerheight', '166');

        if (empty($height)) {
            $height = '166';
        }

        return '<div class="proclaim-audio-wrap" style="max-width:100%;">'
            . '<iframe class="playhit" data-id="' . $mediaId . '" src="' . htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8') . '"'
            . ' width="100%" height="' . (int) $height . '" scrolling="no" frameborder="no"'
            . ' allow="autoplay"></iframe>'
            . '</div>';
    }

    /**
     * Render popup player for SoundCloud (fixed-height audio widget).
     *
     * @param   string    $url          The raw SoundCloud URL
     * @param   Registry  $mediaParams  Merged template + media params
     * @param   string    $width        Player width
     * @param   string    $height       Player height
     *
     * @return  string  Complete player HTML
     *
     * @since   10.1.0
     */
    public function renderPopupPlayer(string $url, Registry $mediaParams, string $width, string $height): string
    {
        $embedUrl = $this->buildEmbedUrl($url, $mediaParams);

        // SoundCloud uses fixed height, not responsive video ratio
        $scHeight = !empty($height) ? $height : '166';

        return '<iframe width="' . $width . '" height="' . $scHeight
            . '" src="' . htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8')
            . '" scrolling="no" frameborder="no" allow="autoplay"></iframe>';
    }

    /**
     * Convert SoundCloud URL to embed format
     *
     * @param   string  $url  The SoundCloud URL to convert
     *
     * @return  string  The embed URL
     *
     * @since   10.1.0
     */
    public function convertSoundcloud(string $url = ''): string
    {
        if (empty($url)) {
            return '';
        }

        // Already an embed URL
        if (str_contains($url, 'w.soundcloud.com/player')) {
            return $url;
        }

        // Convert track/playlist URL to embed
        if (preg_match('/soundcloud\.com\/[\w-]+\/[\w-]+/', $url)) {
            return '//w.soundcloud.com/player/?url=' . urlencode($url)
                . '&color=%23ff5500&auto_play=false&hide_related=true&show_comments=false&show_user=true&show_reposts=false&show_teaser=false';
        }

        return $url;
    }

    /**
     * Check if a URL is a SoundCloud URL
     *
     * @param   string  $url  The URL to check
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public static function isSoundcloudUrl(string $url): bool
    {
        return preg_match('/soundcloud\.com/', $url) === 1;
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
        $html = '<div class="tab-pane" id="soundcloud">';
        $html .= $this->renderOptionsFields($media_form, $new);
        $html .= '</div>';

        return $html;
    }

    /**
     * Upload method (not supported for SoundCloud URLs)
     *
     * @param   array|null  $data  Data to upload
     *
     * @return  mixed
     *
     * @since   10.1.0
     */
    protected function upload(?array $data): mixed
    {
        return false;
    }

    /**
     * Detect metadata for a SoundCloud track (MIME default only).
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
            $params->set('mime_type', 'audio/mpeg');
        }
    }
}
