<?php

/**
 * Location Helper
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Get Locations for Landing Page
 * @param object $params
 * @param int $id
 * @param object $admin_params
 * @return string
 */
function getLocationsLandingPage($params, $id, $admin_params) {
    $mainframe = JFactory::getApplication();
    $db = JFactory::getDBO();
    $option = JRequest::getCmd('option');
    $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
    include_once($path1 . 'image.php');
    include_once($path1 . 'helper.php');
    $location = null;
    $teacherid = null;
    $template = $params->get('studieslisttemplateid', 1);
    $limit = $params->get('landinglocationslimit');
    if (!$limit) {
        $limit = 10000;
    }
    $locationuselimit = $params->get('landinglocationsuselimit', 0);
    $menu = $mainframe->getMenu();
    $item = $menu->getActive();
    $registry = new JRegistry;
    if (isset($item->prams)) {
        $registry->loadJSON($item->params);
        $m_params = $registry;
        $menu_order = $m_params->get('locations_order');
    } else {
        $menu_order = null;
    }
    if ($menu_order) {
        switch ($menu_order) {
            case 2:
                $order = 'ASC';
                break;
            case 1:
                $order = 'DESC';
                break;
        }
    } else {
        $order = $params->get('landing_default_order', 'ASC');
    }
    // Compute view access permissions.
    $user = JFactory::getUser();
    $groups = $user->getAuthorisedViewLevels();

    $query = $db->getQuery(true);
    $query->select('distinct a.*')
            ->from('#__bsms_locations a')
            ->select('b.access AS study_access')
            ->innerJoin('#__bsms_studies b on a.id = b.location_id')
            ->where('a.published = 1')
            ->order('a.location_text ' . $order);
    $db->setQuery($query);

    $tresult = $db->loadObjectList();
    foreach ($tresult as $key => $value) {
        if (!$value->landing_show && !in_array($value->study_access, $groups)) {
            unset($tresult[$key]);
        }
    }

    switch ($locationuselimit) {
        case 0:
            $t = 0;
            $i = 0;
            $location = "\n" . '<table id="landing_table" width=100%>';
            $location .= "\n\t" . '<tr>';
            $showdiv = 0;
            foreach ($tresult as $b) {

                if ($t >= $limit) {
                    if ($showdiv < 1) {
                        if ($i == 1) {
                            $location .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
                            $location .= "\n\t" . '</tr>';
                        };
                        if ($i == 2) {
                            $location .= "\n\t\t" . '<td  id="landing_td"></td>';
                            $location .= "\n\t" . '</tr>';
                        };

                        $location .= "\n" . '</table>';
                        $location .= "\n\t" . '<div id="showhidelocations" style="display:none;"> <!-- start show/hide locations div-->';
                        $location .= "\n" . '<table width = "100%" id="landing_table">';

                        $i = 0;
                        $showdiv = 1;
                    }
                }
                if ($i == 0) {
                    $location .= "\n\t" . '<tr>';
                }
                $location .= "\n\t\t" . '<td id="landing_td">';
                $location .= '<a href="index.php?option=com_biblestudy&view=sermons&filter_location=' . $b->id . '&filter_teacher=0&filter_series=0&filter_topic=0&filter_book=0&filter_year=0&filter_messagetype=0&t=' . $template . '">';

                $location .= $b->location_text;

                $location .='</a>';

                $location .= '</td>';
                $i++;
                $t++;
                if ($i == 3) {
                    $location .= "\n\t" . '</tr>';
                    $i = 0;
                }
            }
            if ($i == 1) {
                $location .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
            };
            if ($i == 2) {
                $location .= "\n\t\t" . '<td  id="landing_td"></td>';
            };

            $location .= "\n" . '</table>' . "\n";

            if ($showdiv == 1) {

                $location .= "\n\t" . '</div> <!-- close show/hide locations div-->';
                $showdiv = 2;
            }
            $location .= '<div id="landing_separator"></div>';
            break;

        case 1:

            $location = '<div class="landingtable" style="display:inline;">';

            foreach ($tresult as $b) {
                if ($b->landing_show == 1) {
                    $location .= '<div class="landingrow">';
                    $location .= '<div class="landingcell"><a class="landinglink" href="index.php?option=com_biblestudy&view=sermons&filter_location=' . $b->id . $langlink . '&filter_teacher=0&filter_series=0&filter_topic=0&filter_book=0&filter_year=0&filter_messagetype=0&t=' . $template . '">';
                    $location .= $b->location_text;
                    $location .='</div></a>';
                    $location .= '</div>';
                }
            }

            $location .= '</div>';
            $location .= '<div id="showhidelocations" style="display:none;">';

            foreach ($tresult as $b) {
                if ($b->landing_show == 2) {
                    $location .= '<div class="landingrow">';
                    $location .= '<div class="landingcell"><a class="landinglink" href="index.php?option=com_biblestudy&view=sermons&filter_location=' . $b->id . $langlink . '&filter_teacher=0&filter_series=0&filter_topic=0&filter_book=0&filter_year=0&filter_messagetype=0&t=' . $template . '">';
                    $location .= $b->location_text;
                    $location .='</div></a>';
                    $location .= '</div>';
                }
            }

            $location .= '</div>';
            $location .= '<div id="landing_separator"></div>';
            break;
    }

    return $location;
}