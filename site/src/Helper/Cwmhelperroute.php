<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

use Joomla\CMS\Language\Multilanguage;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Proclaim Component Route Helper
 *
 * @static
 * @package  Procalaim.Site
 * @since    7.2
 */
abstract class Cwmhelperroute
{
    /**
     * Get Article Rout
     *
     * @param   string   $id         ID or ID:Alias for the route to build
     * @param   ?int     $series_id  The category ID.
     * @param   ?string  $language   The state of language
     * @param   string   $layout     The layout value.
     *
     * @return string
     *
     * @since    7.2
     */
    public static function getArticleRoute(string $id, ?int $series_id = 0, ?string $language = null, $layout = null): string
    {
        // Create the link
        $link = 'index.php?option=com_proclaim&view=cwmsermon&id=' . $id;

        if ((int) $series_id > 1) {
            $link .= '&series_id=' . $series_id;
        }

        if (!empty($language) && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        if ($layout) {
            $link .= '&layout=' . $layout;
        }

        return $link;
    }

    /**
     * Get Teacher Route
     *
     * @param   int  $id  The route of the teacher item
     *
     * @return string
     *
     * @since    7.2
     */
    public static function getTeacherRoute(int $id): string
    {
        // Create the link
        return 'index.php?option=com_proclaim&view=cwmteacher&id=' . $id;
    }

    /**
     * Get Series Route
     *
     * @param   int  $id  ID
     *
     * @return string
     *
     * @since    7.2
     */
    public static function getSeriesRoute($id): string
    {
        // Create the link
        return 'index.php?option=com_proclaim&view=cwmseriesdisplay&id=' . $id;
    }
}
