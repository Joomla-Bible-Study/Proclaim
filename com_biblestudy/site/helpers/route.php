<?php

/**
 * Route Helper
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');

/**
 * Biblestudy Component Route Helper
 *
 * @static
 * @package BibleStudy.Site
 * @since 7.2
 */
abstract class BiblestudyHelperRoute {

    /**
     * Lookup
     * @var string
     */
    protected static $lookup;

    /**
     * Get Artical Rout
     * @fixme This need to be fixed
     * @param	int	The route of the study item
     */
    public static function getArticleRoute($id) {
        $needles = array(
            'article' => array((int) $id)
        );
        //Create the link
        $link = 'index.php?option=com_biblestudy&view=sermon&id=' . $id;

        return $link;
    }

    /**
     * Get Teacher Route
     * @param	int	The route of the teacher item
     */
    public static function getTeacherRoute($id) {
        $needles = array(
            'article' => array((int) $id)
        );
        //Create the link
        $link = 'index.php?option=com_biblestudy&view=teacher&id=' . $id;

        return $link;
    }

    /**
     * Get Series Route
     * @param int $id
     * @return string
     */
    public static function getSeriesRoute($id) {
        $needles = array(
            'article' => array((int) $id)
        );
        //Create the link
        $link = 'index.php?option=com_biblestudy&view=seriesdisplay&id=' . $id;

        return $link;
    }

    /**
     * Find Item
     * @param string $needles
     * @return null
     */
    protected static function _findItem($needles = null) {
        $app = JFactory::getApplication();
        $menus = $app->getMenu('site');

        // Prepare the reverse lookup array.
        if (self::$lookup === null) {
            self::$lookup = array();

            $component = JComponentHelper::getComponent('com_content');
            $items = $menus->getItems('component_id', $component->id);
            foreach ($items as $item) {
                if (isset($item->query) && isset($item->query['view'])) {
                    $view = $item->query['view'];
                    if (!isset(self::$lookup[$view])) {
                        self::$lookup[$view] = array();
                    }
                    if (isset($item->query['id'])) {
                        self::$lookup[$view][$item->query['id']] = $item->id;
                    }
                }
            }
        }

        if ($needles) {
            foreach ($needles as $view => $ids) {
                if (isset(self::$lookup[$view])) {
                    foreach ($ids as $id) {
                        if (isset(self::$lookup[$view][(int) $id])) {
                            return self::$lookup[$view][(int) $id];
                        }
                    }
                }
            }
        } else {
            $active = $menus->getActive();
            if ($active && $active->component == 'com_content') {
                return $active->id;
            }
        }

        return null;
    }

}
