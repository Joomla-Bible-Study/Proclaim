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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

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

        // Extract account ID and video ID from various Resi URL formats
        $patterns = [
            // Player URL: player.resi.io/account-id/video-id
            '/player\.resi\.io\/([^\/]+)\/([^\/?\s]+)/i',
            // Watch URL: resi.io/watch/account-id/video-id
            '/resi\.io\/watch\/([^\/]+)\/([^\/?\s]+)/i',
            // App library: app.resi.io/library/account-id/video-id
            '/app\.resi\.io\/library\/([^\/]+)\/([^\/?\s]+)/i',
            // Live stream: live.resi.io/account-id
            '/live\.resi\.io\/([^\/?\s]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                $accountId = $matches[1];

                // Check if this is a live stream (only account ID) or VOD (account + video ID)
                if (isset($matches[2])) {
                    // VOD with video ID
                    $videoId = $matches[2];
                    return '//player.resi.io/' . $accountId . '/' . $videoId;
                }
                // Live stream with only account ID
                return '//player.resi.io/' . $accountId;

            }
        }

        // If no match, check if it's already an embed URL
        if (str_contains($url, 'player.resi.io')) {
            // Remove protocol if present
            return preg_replace('#^https?:#', '', $url);
        }

        // Return original URL if no conversion possible
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
        $patterns = [
            '/player\.resi\.io\/([^\/]+)/i',
            '/live\.resi\.io\/([^\/?\s]+)/i',
            '/resi\.io\/watch\/([^\/]+)/i',
            '/app\.resi\.io\/library\/([^\/]+)/i',
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
        $patterns = [
            '/player\.resi\.io\/[^\/]+\/([^\/?\s]+)/i',
            '/resi\.io\/watch\/[^\/]+\/([^\/?\s]+)/i',
            '/app\.resi\.io\/library\/[^\/]+\/([^\/?\s]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
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

            // Test by constructing a valid player URL
            $testUrl = 'https://player.resi.io/' . $accountId;

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
}
