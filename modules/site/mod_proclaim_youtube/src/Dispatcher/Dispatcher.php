<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Module
 * @subpackage mod_proclaim_youtube
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Module\ProclaimYoutube\Site\Dispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Module\ProclaimYoutube\Site\Helper\YoutubeHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

/**
 * Dispatcher class for mod_proclaim_youtube
 *
 * @since  10.0.0
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
     * @since   10.0.0
     */
    protected function getLayoutData(): array
    {
        // Load component API
        if (!\defined('BIBLESTUDY_COMPONENT_NAME')) {
            $apiFile = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

            if (file_exists($apiFile)) {
                require_once $apiFile;
            }
        }

        $data = parent::getLayoutData();

        /** @var YoutubeHelper $helper */
        $helper = $this->getHelperFactory()->getHelper('YoutubeHelper');

        // Get video data
        $video = $helper->getVideo($data['params'], $this->getApplication());

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

        $data['video']        = $video;
        $data['embedUrl']     = $embedUrl;
        $data['responsive']   = $responsive;
        $data['playerWidth']  = $playerWidth;
        $data['playerHeight'] = $playerHeight;
        $data['aspectRatio']  = $aspectRatio;
        $data['helper']       = $helper;

        return $data;
    }
}
