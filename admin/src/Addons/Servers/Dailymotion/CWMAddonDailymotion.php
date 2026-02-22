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
}
