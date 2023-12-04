<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */
namespace CWM\Component\Proclaim\Administrator\Helper;

use Joomla\CMS\HTML\HTMLHelper;


// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class for Fancybox JavaScript behaviors
 *
 * @package    Proclaim.Admin
 * @since      9.0.0
 */
class Cwmfancybox
{
    /**
     * @var    array  Array containing information for loaded files
     * @since  9.0.0
     */
    protected static $loaded = array();

    /**
     * Method to load the fancybox JavaScript framework into the document head
     *
     * If debugging mode is on an uncompressed version of jQuery is included for easier debugging.
     *
     * @param   bool  $option     Optional looks [optional]
     * @param   bool  $mouseweel  To add mouse Well to display [optional]
     *
     * @return  void
     *
     * @throws \Exception
     * @since      9.0.0
     */
    public static function framework($option = false, $mouseweel = false): void
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        HTMLHelper::_('jquery.framework');
        Cwmjwplayer::framework();
        HTMLHelper::script('media/com_proclaim/fancybox/jquery.fancybox.min.js');
        HTMLHelper::script('media/com_proclaim/js/fancybox.min.js');

        if ($mouseweel) {
            HTMLHelper::script('media/com_proclaim/js/jquery.mousewheel.pack.min.js');
        }

        self::loadCss($option);

        self::$loaded[__METHOD__] = true;
    }

    /**
     * Loads CSS files needed by Bootstrap
     *
     * @param   boolean  $option  Optional add helpers - button, thumbnail and/or media
     *
     * @return  void
     *
     * @since      3.0
     * @deprecated 10.0.0
     */
    public static function loadCss($option = false): void
    {
        HTMLHelper::stylesheet('media/com_proclaim/fancybox/jquery.fancybox.min.css');
        HTMLHelper::stylesheet('media/com_proclaim/css/bsms.fancybox.min.css');
    }
}
