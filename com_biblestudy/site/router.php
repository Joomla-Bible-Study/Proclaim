<?php

/**
 * Core Router for BibleStudy
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/**
 * BibleStudy Build Route
 * @param array $query
 * @return string
 */
function biblestudyBuildRoute(&$query) {
    $segments = array();

    // get a menu item based on Itemid or currently active
    $app = JFactory::getApplication();
    $menu = $app->getMenu();
    $params = JComponentHelper::getParams('com_biblestudy');
    $advanced = $params->get('sef_advanced_link', 0);

    if (isset($query['view'])) {

        $segments[] = $query['view'];
        unset($query['view']);
    }

    if (isset($query['id'])) {
        $segments[] = $query['id'];
        unset($query['id']);
    }

    if (isset($query['t'])) {
        $segments[] = $query['t'];
        unset($query['t']);
    }
    return $segments;
}

/**
 * BibleStudy Parse Route
 * @param object $segments
 * @return object
 */
function biblestudyParseRoute($segments) {
    $vars = array();
    //Get the active menu item.
    $app = JFactory::getApplication();
    $menu = $app->getMenu();
    $item = $menu->getActive();
    $params = JComponentHelper::getParams('com_biblestudy');
    $advanced = $params->get('sef_advanced_link', 0);


    // Count route segments
    $count = count($segments);



    if ($count == 3) {
        $vars['view'] = $segments[0];
        $vars['id'] = (int) $segments[$count - 2];
        $vars['t'] = $segments[$count - 1];
        return $vars;
    } elseif ($count == 2) {
        $vars['view'] = $segments[0];
        $vars['id'] = $segments[$count - 1];
        return $vars;
    } else {
        $vars['view'] = $segments[0];
        return $vars;
    }
}