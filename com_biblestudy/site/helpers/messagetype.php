<?php

/**
 * MessageType Helper
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Get MessageType for Landing Page
 * @param object $params
 * @param int $id
 * @param object $admin_params
 * @return string
 */
function getMessageTypesLandingPage($params, $id, $admin_params) {
    $mainframe = JFactory::getApplication();
    $db = JFactory::getDBO();
    $user = JFactory::getUser();
    $option = JRequest::getCmd('option');
    $JView = new JView();
    $JView->loadHelper('image');
    $JView->loadHelper('helper');
    $addItemid = JRequest::getInt('Itemid', '', '');
    $messagetype = null;
    $teacherid = null;
    $template = $params->get('studieslisttemplateid', 1);
    $limit = $params->get('landingmessagetypeslimit');
    if (!$limit) {
        $limit = 10000;
    }
    $messagetypeuselimit = $params->get('landingmessagetypeuselimit', 0);
    $menu = $mainframe->getMenu();
    $item = $menu->getActive();
    $registry = new JRegistry;
    if (isset($item->prams)) {
        $registry->loadString($item->params);
        $m_params = $registry;
        $language = $db->quote($item->language). ',' . $db->quote('*');
        $menu_order = $m_params->get('messagetypes_order');
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
            ->from('#__bsms_message_type a')
            ->select('b.access AS study_access')
            ->innerJoin('#__bsms_studies b on a.id = b.messagetype')
            ->where('b.language in (' . $language . ')')
            ->where('b.access IN (' . $groups . ')')
            ->where('a.landing_show > 0')
            ->order('a.message_type ' . $order);
    $db->setQuery($query);

    $tresult = $db->loadObjectList(); 
    $count = count($tresult);
    $t = 0;
    $i = 0;

    if ($count > 0):
    switch ($messagetypeuselimit) {
        case 0:
            $messagetype = "\n" . '<table class="landing_table" width="100%"><tr>';
            $showdiv = 0;
            foreach ($tresult as &$b) {
                if ($t >= $limit) {
                    if ($showdiv < 1) {
                        if ($i == 1) {
                            $messagetype .= "\n\t\t" . '<td  class="landing_td"></td>' . "\n\t\t" . '<td class="landing_td"></td>';
                            $messagetype .= "\n\t" . '</tr>';
                        };
                        if ($i == 2) {
                            $messagetype .= "\n\t\t" . '<td  class="landing_td"></td>';
                            $messagetype .= "\n\t" . '</tr>';
                        };

                        $messagetype .= "\n" . '</table>';
                        $messagetype .= "\n\t" . '<div id="showhidemessagetypes" style="display:none;"> <!-- start show/hide messagetype div-->';
                        $messagetype .= "\n" . '<table width = "100%" class="landing_table"><tr>';

                        $i = 0;
                        $showdiv = 1;
                    }
                }
                $messagetype .= "\n\t\t" . '<td class="landing_td">';

                $messagetype .= '<a href="index.php?option=com_biblestudy&amp;view=sermons&amp;filter_messagetype=' . $b->id .  '&amp;filter_book=0&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;t=' . $template . '">';

                $messagetype .= $b->message_type;

                $messagetype .='</a>';

                $messagetype .= '</td>';

                $i++;
                $t++;
                if ($i == 3 && $t != $limit && $t != $count) {
                    $messagetype .= "\n\t" . '</tr><tr>';
                    $i = 0;
                } elseif($i == 3 || $t == $count || $t == $limit) {
                    $messagetype .= "\n\t" . '</tr>';
                    $i = 0;
                }
            }
            if ($i == 1) {
                $messagetype .= "\n\t\t" . '<td  class="landing_td"></td>' . "\n\t\t" . '<td class="landing_td"></td>';
            };
            if ($i == 2) {
                $messagetype .= "\n\t\t" . '<td  class="landing_td"></td>';
            };

            $messagetype .= "\n" . '</table>' . "\n";

            if ($showdiv == 1) {

                $messagetype .= "\n\t" . '</div> <!-- close show/hide messagetype div-->';
                $showdiv = 2;
            }
            $messagetype .= '<div class="landing_separator"></div>';
            break;

        case 1:
            $messagetype = '<div class="landingtable" style="display:inline;">';

            foreach ($tresult as $b) {
                if ($b->landing_show == 1) {
                    $messagetype .= '<div class="landingrow">';
                    $messagetype .= '<div class="landingcell"><a class="landinglink" href="index.php?option=com_biblestudy&amp;view=sermons&amp;filter_messagetype=' . $b->id . '&amp;filter_book=0&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;t=' . $template . '">';
                    $messagetype .= $b->message_type;
                    $messagetype .='</a></div>';
                    $messagetype .= '</div>';
                }
            }

            $messagetype .= '</div>';
            $messagetype .= '<div id="showhidemessagetypes" style="display:none;">';

            foreach ($tresult as $b) {
                if ($b->landing_show == 2) {
                    $messagetype .= '<div class="landingrow">';
                    $messagetype .= '<div class="landingcell"><a class="landinglink" href="index.php?option=com_biblestudy&amp;view=sermons&amp;filter_messagetype=' . $b->id . '&amp;filter_book=0&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;t=' . $template . '">';
                    $messagetype .= $b->message_type;
                    $messagetype .='</a></div>';
                    $messagetype .= '</div>';
                }
            }

            $messagetype .= '</div>';
            $messagetype .= '<div class="landing_separator"></div>';
            break;
    }
    else:
        $messagetype  = '<div class="landing_separator"></div>';
    endif;
    return $messagetype;
}