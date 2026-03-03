<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Module
 * @subpackage mod_proclaim_youtube
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Module\ProclaimYoutube\Site\Dispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Module\ProclaimYoutube\Site\Helper\YoutubeHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

/**
 * Dispatcher class for mod_proclaim_youtube
 *
 * @since  10.1.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    #[\Override]
    protected function getLayoutData(): array
    {
        // Load component API
        if (!\defined('CWM_LOADED')) {
            $apiFile = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

            if (file_exists($apiFile)) {
                require_once $apiFile;
            }
        }

        $data = parent::getLayoutData();

        /** @var YoutubeHelper $helper */
        $helper = $this->getHelperFactory()->getHelper('YoutubeHelper');

        // Get video data
        $video    = $helper->getVideo($data['params'], $this->getApplication());
        $serverId = (int) $data['params']->get('server_id', 0);

        // Verify live status in real-time (bypasses cache for status only)
        if ($video && $serverId) {
            $video = $helper->verifyLiveStatus($video, $serverId);
        }

        // Build embed URL if video found
        $embedUrl = null;

        if ($video && !empty($video['videoId'])) {
            $autoplay = (bool) $data['params']->get('autoplay_live', 0);
            $isLive   = $video['isLive'] ?? false;
            $embedUrl = $helper->getEmbedUrl($video['videoId'], $autoplay, $isLive);
        }

        // Process description if needed
        if ($video && $data['params']->get('show_description', 0)) {
            $descLength                    = (int) $data['params']->get('desc_length', 200);
            $video['truncatedDescription'] = $helper->truncateDescription(
                $video['description'] ?? '',
                $descLength
            );
        }

        // Get player dimensions
        $responsive   = (bool) $data['params']->get('responsive', 1);
        $playerWidth  = (int) $data['params']->get('player_width', 560);
        $playerHeight = (int) $data['params']->get('player_height', 315);

        // Calculate aspect ratio for responsive
        $aspectRatio = ($playerWidth > 0) ? ($playerHeight / $playerWidth * 100) : 56.25;

        // Try to match video to a Proclaim message
        $matchedMessage = null;

        if ($video && $data['params']->get('auto_link_message', 1)) {
            $matchedMessage = $helper->findMatchingMessage($video);
        }

        // Fallback: show recent sermons when no video is available
        $fallbackSermons  = [];
        $fallbackTemplate = null;

        if (!($video && $embedUrl) && (bool) $data['params']->get('fallback_sermons', 1)) {
            $limit           = (int) $data['params']->get('fallback_count', 6);
            $fallbackSermons = $helper->getRecentSermons($this->getApplication(), $limit);

            $templateId = (int) $data['params']->get('fallback_template', 1) ?: 1;

            if (!empty($fallbackSermons)) {
                $fallbackTemplate = Cwmparams::getTemplateparams($templateId);

                // Merge admin → template params (same pattern as mod_proclaim Dispatcher)
                // Clone to avoid mutating the static cache in Cwmparams
                $mergedParams = clone Cwmparams::getAdmin()->params;
                $mergedParams->merge($fallbackTemplate->params);
                $data['fallbackParams'] = $mergedParams;

                // Register Proclaim CSS assets for card styling
                $wa = $this->getApplication()->getDocument()->getWebAssetManager();
                $wa->useStyle('com_proclaim.cwmcore');
                $wa->useStyle('com_proclaim.general');
            }
        }

        $data['video']            = $video;
        $data['embedUrl']         = $embedUrl;
        $data['responsive']       = $responsive;
        $data['playerWidth']      = $playerWidth;
        $data['playerHeight']     = $playerHeight;
        $data['aspectRatio']      = $aspectRatio;
        $data['helper']           = $helper;
        $data['matchedMessage']   = $matchedMessage;
        $data['serverId']         = (int) $data['params']->get('server_id', 0);
        $data['app']              = $this->getApplication();
        $data['fallbackSermons']  = $fallbackSermons;
        $data['fallbackTemplate'] = $fallbackTemplate;
        $data['fallbackParams']   = $data['fallbackParams'] ?? null;

        return $data;
    }
}
