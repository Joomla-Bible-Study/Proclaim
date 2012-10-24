<?php

/**
 * Year Helper
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Get Years for Landing Page
 * @param object $params
 * @param int $id
 * @param object $admin_params
 * @return string
 */
function getYearsLandingPage($params, $id, $admin_params) {
    $mainframe = JFactory::getApplication();
    $db = JFactory::getDBO();
    $user = JFactory::getUser();
    $option = JRequest::getCmd('option');
    $JView = new JView();
    $JView->loadHelper('image');
    $JView->loadHelper('helper');
    $year = null;
    $teacherid = null;
    $template = $params->get('studieslisttemplateid');
    $limit = $params->get('landingyearslimit');
    if (!$limit) {
        $limit = 10000;
    }
    $menu = $mainframe->getMenu();
    $item = $menu->getActive();
    $registry = new JRegistry;
    if (isset($item->params)) {
        $registry->loadJSON($item->params);
        $m_params = $registry;
        $language = $db->quote($item->language) . ',' . $db->quote('*');
        $menu_order = $m_params->get('years_order');
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
    $query->select('distinct year(studydate) as theYear')
            ->from('#__bsms_studies')
            ->where('language in (' . $language . ')')
            ->where('access IN (' . $groups . ')')
            ->order('year(studydate) ' . $order);
    $db->setQuery($query);

    $tresult = $db->loadObjectList();
    $count = count($tresult);
    $t = 0;
    $i = 0;
    
    if ($count > 0):
        $year = "\n" . '<table class="landing_table" width="100%"><tr>';
        $showdiv = 0;
        foreach ($tresult as &$b) {
            if ($t >= $limit) {
                if ($showdiv < 1) {
                    if ($i == 1) {
                        $year .= "\n\t\t" . '<td class="landing_td"></td>' . "\n\t\t" . '<td class="landing_td"></td>';
                        $year .= "\n\t" . '</tr>';
                    };
                    if ($i == 2) {
                        $year .= "\n\t\t" . '<td  class="landing_td"></td>';
                        $year .= "\n\t" . '</tr>';
                    };

                    $year .= "\n" . '</table>';
                    $year .= "\n\t" . '<div id="showhideyears" style="display:none;"> <!-- start show/hide years div-->';
                    $year .= "\n" . '<table width = "100%" class="landing_table"><tr>';

                    $i = 0;
                    $showdiv = 1;
                }
            }
            $year .= "\n\t\t" . '<td class="landing_td">';

            $year .= '<a href="index.php?option=com_biblestudy&amp;view=sermons&amp;filter_year=' . $b->theYear . '&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_location=0&amp;filter_book=0&amp;filter_messagetype=0&amp;t=' . $template . '">';

            $year .= $b->theYear;

            $year .='</a>';

            $year .= '</td>';
            $i++;
            $t++;
            if ($i == 3 && $t != $limit && $t != $count) {
                $year .= "\n\t" . '</tr><tr>';
                $i = 0;
            } elseif ($i == 3 || $t == $count || $t == $limit) {
                $year .= "\n\t" . '</tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $year .= "\n\t\t" . '<td  class="landing_td"></td>' . "\n\t\t" . '<td class="landing_td"></td>';
        };
        if ($i == 2) {
            $year .= "\n\t\t" . '<td  class="landing_td"></td>';
        };

        $year .= "\n" . '</table>' . "\n";

        if ($showdiv == 1) {

            $year .= "\n\t" . '</div> <!-- close show/hide years div-->';
            $showdiv = 2;
        }
        $year .= '<div class="landing_separator"></div>';
    else:
        $year = '';
    endif;
    return $year;
}
