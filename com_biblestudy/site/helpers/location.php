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
    $user = JFactory::getUser();
    $db = JFactory::getDBO();
    $input = new JInput;
    $option = $input->get('option','','cmd');
    $JViewLegacy = new JViewLegacy();
    $JViewLegacy->loadHelper('image');
    $JViewLegacy->loadHelper('helper');
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
        $registry->loadString($item->params);
        $m_params = $registry;
        $language = $db->quote($item->language). ',' . $db->quote('*');
        $menu_order = $m_params->get('locations_order');
    } else {
        $language = $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*');
        $menu_order = null;
    }
    if ($language == '*' || !$language) {
        $langlink = '';
    } elseif ($language != '*') {
        $langlink = '&amp;filter.languages=' . $item->language;
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
    $groups = implode(',', $user->getAuthorisedViewLevels());

    $query = $db->getQuery(true);
    $query->select('distinct a.*')
            ->from('#__bsms_locations a')
            ->select('b.access AS study_access')
            ->innerJoin('#__bsms_studies b on a.id = b.location_id')
            ->innerJoin('#__bsms_series s on b.series_id = s.id')
            ->where('b.location_id > 0')
            ->where('a.published = 1')
            ->where('b.language in (' . $language . ')')
            ->where('b.access IN (' . $groups . ')')
            ->where('s.access IN (' . $groups . ')')
            ->where('a.landing_show > 0')
            ->order('a.location_text ' . $order);
    $db->setQuery($query);

    $tresult = $db->loadObjectList();
    $count = count($tresult);
    if ($count > 0):
        switch ($locationuselimit) {
            case 0:
                $t = 0;
                $i = 0;

                $location = "\n" . '<table class="landing_table" width="100%"><tr>';
                $showdiv = 0;
                foreach ($tresult as $b) {

                    if ($t >= $limit) {
                        if ($showdiv < 1) {
                            if ($i == 1) {
                                $location .= "\n\t\t" . '<td  class="landing_td"></td>' . "\n\t\t" . '<td class="landing_td"></td>';
                                $location .= "\n\t" . '</tr>';
                            };
                            if ($i == 2) {
                                $location .= "\n\t\t" . '<td  class="landing_td"></td>';
                                $location .= "\n\t" . '</tr>';
                            };

                            $location .= "\n" . '</table>';
                            $location .= "\n\t" . '<div id="showhidelocations" style="display:none;"> <!-- start show/hide locations div-->';
                            $location .= "\n" . '<table width = "100%" class="landing_table"><tr>';

                            $i = 0;
                            $showdiv = 1;
                        }
                    }
                    if ($i == 0) {
                        $location .= "\n\t" . '<tr>';
                    }
                    $location .= "\n\t\t" . '<td class="landing_td">';
                    $location .= '<a href="index.php?option=com_biblestudy&amp;view=sermons&amp;filter_location=' . $b->id . '&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_book=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t=' . $template . '">';

                    $location .= $b->location_text;

                    $location .='</a>';

                    $location .= '</td>';
                    $i++;
                    $t++;
                    if ($i == 3 && $t != $limit && $t != $count) {
                        $location .= "\n\t" . '</tr><tr>';
                        $i = 0;
                    } elseif ($i == 3 || $t == $count || $t == $limit) {
                        $location .= "\n\t" . '</tr>';
                        $i = 0;
                    }
                }
                if ($i == 1) {
                    $location .= "\n\t\t" . '<td  class="landing_td"></td>' . "\n\t\t" . '<td class="landing_td"></td>';
                };
                if ($i == 2) {
                    $location .= "\n\t\t" . '<td  class="landing_td"></td>';
                };

                $location .= "\n" . '</table>' . "\n";

                if ($showdiv == 1) {

                    $location .= "\n\t" . '</div> <!-- close show/hide locations div-->';
                    $showdiv = 2;
                }
                $location .= '<div class="landing_separator"></div>';
                break;

            case 1:

                $location = '<div class="landingtable" style="display:inline;">';

                foreach ($tresult as $b) {
                    if ($b->landing_show == 1) {
                        $location .= '<div class="landingrow">';
                        $location .= '<div class="landingcell"><a class="landinglink" href="index.php?option=com_biblestudy&amp;view=sermons&amp;filter_location=' . $b->id . '&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_book=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t=' . $template . '">';
                        $location .= $b->location_text;
                        $location .='</a></div>';
                        $location .= '</div>';
                    }
                }

                $location .= '</div>';
                $location .= '<div id="showhidelocations" style="display:none;">';

                foreach ($tresult as $b) {
                    if ($b->landing_show == 2) {
                        $location .= '<div class="landingrow">';
                        $location .= '<div class="landingcell"><a class="landinglink" href="index.php?option=com_biblestudy&amp;view=sermons&amp;filter_location=' . $b->id . '&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_book=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t=' . $template . '">';
                        $location .= $b->location_text;
                        $location .='</a></div>';
                        $location .= '</div>';
                    }
                }

                $location .= '</div>';
                $location .= '<div class="landing_separator"></div>';
                break;
        }
    else:
        $location = '<div class="landing_separator"></div>';
    endif;
    return $location;
}