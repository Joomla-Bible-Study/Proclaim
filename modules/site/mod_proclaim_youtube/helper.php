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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Module\ProclaimYoutube\Site\Helper\YoutubeHelper;

/**
 * Legacy helper class for com_ajax compatibility
 *
 * Joomla's com_ajax module handler looks for a helper.php file with
 * a class named Mod{Modulename}Helper containing the AJAX method.
 * This class forwards AJAX calls to the namespaced helper.
 *
 * @since  10.0.0
 */
class ModProclaimYoutubeHelper
{
    /**
     * AJAX method to get current video status
     *
     * Called via: index.php?option=com_ajax&module=mod_proclaim_youtube&method=getStatus&format=json
     *
     * @return  array  Status data
     *
     * @since   10.0.0
     */
    public static function getStatusAjax(): array
    {
        // Load component API if needed
        if (!\defined('BIBLESTUDY_COMPONENT_NAME')) {
            $apiFile = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

            if (file_exists($apiFile)) {
                require_once $apiFile;
            }
        }

        return YoutubeHelper::getStatusAjax();
    }
}
