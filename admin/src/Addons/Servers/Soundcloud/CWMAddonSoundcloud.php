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
}
