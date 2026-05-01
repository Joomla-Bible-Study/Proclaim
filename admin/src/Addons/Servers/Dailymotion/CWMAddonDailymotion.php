<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Dailymotion;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Helper\CwmserverMigrationHelper;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\Registry\Registry;

/**
 * Dailymotion Server Addon
 *
 * Provides integration with Dailymotion video platform.
 * Supports URL parsing and embed conversion for public videos.
 *
 * @since 10.1.0
 */
class CWMAddonDailymotion extends CWMAddon
{
    /**
     * Addon name
     *
     * @var string
     * @since 10.1.0
     */
    protected $name = 'Dailymotion';

    /**
     * Addon description
     *
     * @var string
     * @since 10.1.0
     */
    protected $description = 'Used for Dailymotion server access';

    /**
     * URL patterns that identify Dailymotion content.
     *
     * @return  string[]
     *
     * @since   10.1.0
     */
    public function getUrlPatterns(): array
    {
        return ['/(dailymotion\.com|dai\.ly)/i'];
    }

    /**
     * {@inheritdoc}
     *
     * @since   10.1.0
     */
    public function getMigrationPatterns(): array
    {
        return [
            'type'     => 'dailymotion',
            'label'    => 'Dailymotion',
            'patterns' => [
                '/dailymotion\.com/i',
                '/dai\.ly/i',
            ],
            'allVideosTags' => ['dailymotion'],
        ];
    }

    /**
     * Extract Dailymotion video ID from a URL.
     *
     * @param   string  $text  URL or text containing a Dailymotion URL
     *
     * @return  string|null  The video ID or null
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
            '/dailymotion\.com\/video\/([a-z0-9]+)/i',
            '/dailymotion\.com\/embed\/video\/([a-z0-9]+)/i',
            '/dai\.ly\/([a-z0-9]+)/i',
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
    public static function extractDailymotionVideoId(string $text): ?string
    {
        return static::extractMediaId($text);
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
        $videoId = self::extractDailymotionVideoId($combined);

        // Fall back to AllVideos bare ID: {dailymotion}x7tgad0{/dailymotion}
        if ($videoId === null && !empty($avContent) && preg_match('/^[a-z0-9]+$/i', $avContent)) {
            $videoId = $avContent;
        }

        if ($videoId) {
            $result['filename'] = '//www.dailymotion.com/embed/video/' . $videoId;
        } else {
            $result['filename'] = $filename;
        }

        // Map extracted URL params to embed option form fields
        $sourceQuery = CwmserverMigrationHelper::extractSourceUrlParams($combined, 'dailymotion');

        $dmParamMap = [
            'mute'      => 'dm_mute',
            'startTime' => 'dm_start',
            'loop'      => 'dm_loop',
            'scaleMode' => 'dm_scale',
        ];

        foreach ($dmParamMap as $urlParam => $formField) {
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
     * Build a Dailymotion embed URL with all form field params applied.
     *
     * @param   string    $filename     The raw Dailymotion URL
     * @param   Registry  $mediaParams  Merged template + media params
     *
     * @return  string  The embed-ready URL with query params
     *
     * @since   10.1.0
     */
    public function buildEmbedUrl(string $filename, Registry $mediaParams): string
    {
        $baseUrl = $this->convertDailymotion($filename);
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
            'dm_mute'  => 'mute',
            'dm_start' => 'startTime',
            'dm_loop'  => 'loop',
            'dm_scale' => 'scaleMode',
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
     * Render inline Dailymotion player (responsive 16:9 iframe).
     *
     * @param   string    $url          The raw Dailymotion URL
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
            . ' allow="autoplay; encrypted-media" allowfullscreen loading="lazy"'
            . ' style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"></iframe>'
            . '</div>';
    }

    /**
     * Convert Dailymotion URL to embed format
     *
     * Supports various Dailymotion URL formats:
     * - https://www.dailymotion.com/video/x8abc12
     * - https://dai.ly/x8abc12
     * - https://www.dailymotion.com/embed/video/x8abc12
     *
     * @param   string  $url  The Dailymotion URL to convert
     *
     * @return  string  The embed URL (//www.dailymotion.com/embed/video/{id})
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
        return $this->convertDailymotion($filename);
    }

    public function convertDailymotion(string $url = ''): string
    {
        if (empty($url)) {
            return '';
        }

        // Extract video ID from various Dailymotion URL formats
        $patterns = [
            // Standard: dailymotion.com/video/x8abc12
            '/dailymotion\.com\/video\/([a-z0-9]+)/i',
            // Embed: dailymotion.com/embed/video/x8abc12
            '/dailymotion\.com\/embed\/video\/([a-z0-9]+)/i',
            // Short URL: dai.ly/x8abc12
            '/dai\.ly\/([a-z0-9]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return '//www.dailymotion.com/embed/video/' . $matches[1];
            }
        }

        // If no match, return original URL
        return $url;
    }

    /**
     * Check if a URL is a Dailymotion URL
     *
     * @param   string  $url  The URL to check
     *
     * @return  bool  True if Dailymotion URL, false otherwise
     *
     * @since   10.1.0
     */
    public static function isDailymotionUrl(string $url): bool
    {
        return preg_match('/dailymotion\.com|dai\.ly/', $url) === 1;
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
        $html = '<div class="tab-pane" id="dailymotion">';
        $html .= $this->renderOptionsFields($media_form, $new);
        $html .= '</div>';

        return $html;
    }

    /**
     * Upload method (not supported for Dailymotion URLs)
     *
     * @param   array|null  $data  Data to upload
     *
     * @return  mixed
     *
     * @since   10.1.0
     */
    protected function upload(?array $data): mixed
    {
        // Dailymotion videos are referenced by URL, not uploaded
        return false;
    }

    /**
     * Detect metadata for a Dailymotion video (MIME default only).
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
     * Dailymotion supports video descriptions.
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
