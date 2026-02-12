<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Service\HTML;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Proclaim Component HTML Helper
 *
 * @package    Proclaim.Admin
 * @since      10.0.0
 */
class CWMFancyBox
{
    /**
     * @var    array  Array containing information for loaded files
     * @since  9.0.0
     */
    protected static array $loaded = [];

    /**
     * Method to load the fancybox JavaScript framework into the document head
     *
     * If debugging mode is on, an uncompressed version of jQuery is included for easier debugging.
     *
     * @param   bool  $option     Optional looks [optional]
     * @param   bool  $mouseweel  To add mouse Wheel to display [optional]
     *
     * @return  void
     *
     * @since      10.0.0
     */
    public static function framework(bool $option = false, bool $mouseweel = false): void
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        try {
            $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
            $wa->getRegistry()->addExtensionRegistryFile('com_proclaim');

            // Loads vendor UMD + custom handler via dependency chain in joomla.asset.json
            $wa->useScript('com_proclaim.fancybox');
        } catch (\Exception $e) {
            return;
        }

        self::loadCss($option);

        self::$loaded[__METHOD__] = true;
    }

    /**
     * Loads CSS files needed by fancybox
     *
     * @param   bool  $option  Optional add helpers - button, thumbnail, and/or media
     *
     * @return  void
     *
     * @since      10.0.0
     */
    public static function loadCss(bool $option = false): void
    {
        try {
            $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
            $wa->getRegistry()->addExtensionRegistryFile('com_proclaim');

            // Loads vendor CSS + custom overrides via dependency chain in joomla.asset.json
            $wa->useStyle('com_proclaim.cwm-fancybox');
        } catch (\Exception $e) {
            return;
        }
    }
}
