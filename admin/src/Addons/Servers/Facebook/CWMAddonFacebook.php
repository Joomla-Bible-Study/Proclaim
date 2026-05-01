<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Facebook;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Helper\CwmserverMigrationHelper;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\Registry\Registry;

/**
 * Facebook Video Server Addon
 *
 * Provides integration with Facebook video embeds.
 * Supports page videos, watch URLs, fb.watch short links, and reels.
 *
 * @since 10.1.0
 */
class CWMAddonFacebook extends CWMAddon
{
    /**
     * Addon name
     *
     * @var string
     * @since 10.1.0
     */
    protected $name = 'Facebook';

    /**
     * Addon description
     *
     * @var string
     * @since 10.1.0
     */
    protected $description = 'Used for Facebook video server access';

    /**
     * URL patterns that identify Facebook video content.
     *
     * @return  string[]
     *
     * @since   10.1.0
     */
    public function getUrlPatterns(): array
    {
        return ['/(facebook\.com|fb\.watch)/i'];
    }

    /**
     * {@inheritdoc}
     *
     * @since   10.1.0
     */
    public function getMigrationPatterns(): array
    {
        return [
            'type'     => 'facebook',
            'label'    => 'Facebook',
            'patterns' => [
                '/facebook\.com/i',
                '/fb\.watch/i',
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
        $result      = [];
        $sourceQuery = CwmserverMigrationHelper::extractSourceUrlParams($combined, 'facebook');

        // If already an embed URL, extract the href param as the source URL
        if (str_contains(strtolower($filename), 'facebook.com/plugins/video.php')) {
            $embedParts = parse_url($filename);
            $embedQuery = [];

            if (!empty($embedParts['query'])) {
                parse_str($embedParts['query'], $embedQuery);
            }

            $sourceUrl          = !empty($embedQuery['href']) ? urldecode($embedQuery['href']) : $filename;
            $result['filename'] = 'https://www.facebook.com/plugins/video.php?href='
                . urlencode($sourceUrl) . '&show_text=false';

            $sourceQuery = $embedQuery;
        } else {
            // Convert any Facebook URL to embed format
            $result['filename'] = 'https://www.facebook.com/plugins/video.php?href='
                . urlencode($filename) . '&show_text=false';
        }

        // Map extracted URL params to embed option form fields
        $fbParamMap = [
            'show_text' => 'fb_show_text',
            'muted'     => 'fb_muted',
            'lazy'      => 'fb_lazy',
            't'         => 'fb_t',
        ];

        foreach ($fbParamMap as $urlParam => $formField) {
            if (isset($sourceQuery[$urlParam]) && $sourceQuery[$urlParam] !== '') {
                $result[$formField] = $sourceQuery[$urlParam];
            }
        }

        if (isset($sourceQuery['autoplay']) && $sourceQuery['autoplay'] === 'true') {
            $result['autostart'] = 'true';
        }

        $result['player']    = '1';
        $result['mediacode'] = '';

        return $result;
    }

    /**
     * Build a Facebook embed URL with all form field params applied.
     *
     * @param   string    $filename     The raw Facebook video URL
     * @param   Registry  $mediaParams  Merged template + media params
     *
     * @return  string  The embed-ready URL with query params
     *
     * @since   10.1.0
     */
    public function buildEmbedUrl(string $filename, Registry $mediaParams): string
    {
        $embedUrl = $this->convertFacebook($filename);
        $parts    = parse_url($embedUrl);
        $query    = [];

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        // Map autostart → Facebook autoplay
        $autostart = $mediaParams->get('autostart', '');

        if ($autostart === 'true') {
            $query['autoplay'] = 'true';
        } elseif ($autostart === 'false') {
            $query['autoplay'] = 'false';
        }

        $fieldMap = [
            'fb_show_text' => 'show_text',
            'fb_muted'     => 'muted',
            'fb_lazy'      => 'lazy',
            'fb_t'         => 't',
        ];

        foreach ($fieldMap as $formField => $urlParam) {
            $val = $mediaParams->get($formField, '');

            if ($val !== '') {
                $query[$urlParam] = $val;
            }
        }

        $base = strtok($embedUrl, '?');

        return $base . (!empty($query) ? '?' . http_build_query($query) : '');
    }

    /**
     * Render inline Facebook player (responsive 16:9 iframe).
     *
     * @param   string    $url          The raw Facebook video URL
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
            . ' allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowfullscreen'
            . ' scrolling="no" loading="lazy"'
            . ' style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;overflow:hidden;"></iframe>'
            . '</div>';
    }

    /**
     * Render popup player for Facebook video.
     *
     * @param   string    $url          The raw Facebook video URL
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

        return '<iframe width="' . $width . '" height="' . $height
            . '" src="' . htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8')
            . '" style="border:none;overflow:hidden;" scrolling="no"'
            . ' allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"'
            . ' allowfullscreen></iframe>';
    }

    /**
     * Convert Facebook video URL to embed format.
     *
     * Supports various Facebook URL formats:
     * - https://www.facebook.com/plugins/video.php?href=... (already embed)
     * - https://www.facebook.com/PageName/videos/123456789/
     * - https://www.facebook.com/watch?v=123456789
     * - https://www.facebook.com/reel/123456789
     * - https://www.facebook.com/events/123456789/
     * - https://fb.watch/abc123/
     *
     * @param   string  $url  The Facebook URL to convert
     *
     * @return  string  The embed URL
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
        return $this->convertFacebook($filename);
    }

    public function convertFacebook(string $url = ''): string
    {
        if (empty($url)) {
            return '';
        }

        // Already an embed URL
        if (str_contains($url, 'facebook.com/plugins/video.php')) {
            return $url;
        }

        // Any recognizable Facebook video URL → embed via plugins/video.php
        if (preg_match('/facebook\.com\/.+\/videos\/\d+/i', $url)
            || preg_match('/facebook\.com\/watch\?v=/i', $url)
            || preg_match('/facebook\.com\/reel\/\d+/i', $url)
            || preg_match('/fb\.watch\//i', $url)
        ) {
            return 'https://www.facebook.com/plugins/video.php?href=' . urlencode($url)
                . '&show_text=false';
        }

        // Generic facebook.com URL — try embed anyway
        if (str_contains($url, 'facebook.com')) {
            return 'https://www.facebook.com/plugins/video.php?href=' . urlencode($url)
                . '&show_text=false';
        }

        return $url;
    }

    /**
     * Check if a URL is a Facebook video URL
     *
     * @param   string  $url  The URL to check
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public static function isFacebookUrl(string $url): bool
    {
        return preg_match('/facebook\.com|fb\.watch/i', $url) === 1;
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
        $html = '<div class="tab-pane" id="facebook">';
        $html .= $this->renderOptionsFields($media_form, $new);
        $html .= '</div>';

        return $html;
    }

    /**
     * Upload method (not supported for Facebook URLs)
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
     * Detect metadata for a Facebook video (MIME default only).
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

    /**
     * Facebook supports video descriptions.
     *
     * @return  bool
     *
     * @since   10.2.0
     */
    #[\Override]
    public function supportsDescriptionSync(): bool
    {
        return true;
    }
}
