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
}
