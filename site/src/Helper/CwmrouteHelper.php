<?php

/**
 * @package        Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Multilanguage;

/**
 * Proclaim Component Route Helper.
 *
 * @since  1.5
 */
abstract class CwmrouteHelper
{
    /**
     * Get the sermon route.
     *
     * @param   integer     $id        The route of the content item.
     * @param   int|string  $language  The language code.
     * @param   ?string     $layout    The layout value.
     *
     * @return  string  The sermon route.
     *
     * @since   1.5
     */
    public static function getMessageRoute(int $id, int|string $language = '*', ?string $layout = null): string
    {
        // Create the link
        $link = 'index.php?option=com_proclaim&view=cwmsermon&id=' . $id;

        if ($language && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        if ($layout) {
            $link .= '&layout=' . $layout;
        }

        return $link;
    }

    /**
     * Get the Series route.
     *
     * @param   integer     $seriesid  The Series ID.
     * @param   int|string  $language  The language code.
     * @param   ?string     $layout    The layout value.
     *
     * @return  string  The article route.
     *
     * @since   1.5
     */
    public static function getSeriesRoute(int $seriesid, int|string $language = '*', ?string $layout = null): string
    {
        if ($seriesid < 1) {
            return '';
        }

        $link = 'index.php?option=com_proclaim&view=cwmseriesdisplay&id=' . $seriesid;

        if ($language && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        if ($layout) {
            $link .= '&layout=' . $layout;
        }

        return $link;
    }

    /**
     * Get the Series route.
     *
     * @param   integer     $seriesid  The Series ID.
     * @param   int|string  $language  The language code.
     * @param   ?string     $layout    The layout value.
     *
     * @return  string  The article route.
     *
     * @since   1.5
     */
    public static function getLocationsRoute(int $seriesid, int|string $language = '*', ?string $layout = null): string
    {
        if ($seriesid < 1) {
            return '';
        }

        $link = 'index.php?option=com_proclaim&view=cwmlocations&id=' . $seriesid;

        if ($language && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        if ($layout) {
            $link .= '&layout=' . $layout;
        }

        return $link;
    }

    /**
     * Get the Teacher route.
     *
     * @param   int         $id        ID of the Teacher record
     * @param   int|string  $language  The language code.
     * @param   ?string     $layout    The layout value.
     *
     * @return  string  The article route.
     *
     * @since   1.5
     */
    public static function getTeachersRoute(int $id, int|string $language = '*', ?string $layout = null): string
    {
        if ($id < 1) {
            return '';
        }

        $link = 'index.php?option=com_proclaim&view=cwmteacher&id=' . $id;

        if ($language && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        if ($layout) {
            $link .= '&layout=' . $layout;
        }

        return $link;
    }

    /**
     * Get the form route.
     *
     * @param   integer  $id  The form ID.
     *
     * @return  string  The article route.
     *
     * @since   1.5
     */
    public static function getFormRoute(int $id): string
    {
        return 'index.php?option=com_proclaim&task=cwmmessageform.edit&a_id=' . (int)$id;
    }

    /**
     * Get the Teacher route.
     *
     * @param   string      $type      Type of server offered
     * @param   int|string  $language  The language code.
     * @param   ?string     $layout    The layout value.
     *
     * @return  string  The article route.
     *
     * @since   1.5
     */
    public static function getTypeRoute(string $type, int|string $language = '*', ?string $layout = null): string
    {
        if (!empty($type)) {
            return '';
        }

        return $type;
    }
}
