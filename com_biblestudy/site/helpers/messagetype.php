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
    $mainframe = & JFactory::getApplication();
    $option = JRequest::getCmd('option');
    $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
    include_once($path1 . 'image.php');
    include_once($path1 . 'helper.php');
    $addItemid = JRequest::getInt('Itemid', '', '');
    $messagetype = null;
    $teacherid = null;
    $template = $params->get('studieslisttemplateid', 1);
    $limit = $params->get('landingmessagetypeslimit');
    if (!$limit) {
        $limit = 10000;
    }
    $messagetypeuselimit = $params->get('landingmessagetypeuselimit', 0);
    $menu = $JSite = new JSite();
    $menu = $JSite->getMenu();
    $item = $menu->getActive();
    $registry = new JRegistry;
    $registry->loadJSON($item->params);
    $m_params = $registry;
    $language = $m_params->get('language');
    if ($language == '*' || !$language) {
        $langlink = '';
    } elseif ($language != '*') {
        $langlink = '&filter.languages=' . $language;
    }
    $menu_order = $m_params->get('messagetypes_order');
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
    if (!$t) {
        $t = JRequest::getVar('t', 1, 'get', 'int');
    }


    $db = & JFactory::getDBO();
    $query = 'select distinct a.* from #__bsms_message_type a inner join #__bsms_studies b on a.id = b.messagetype order by a.message_type ' . $order;
    if ($language != '*' && $language) {
        $query = 'select distinct a.* from #__bsms_message_type a inner join #__bsms_studies b on a.id = b.messagetype where b.language LIKE "' . $language . '" order by a.message_type ' . $order;
    }
    $db->setQuery($query);

    $tresult = $db->loadObjectList();
    foreach ($tresult as $key => $value) {
        if (!$value->landing_show) {
            unset($tresult[$key]);
        }
    }
    switch ($messagetypeuselimit) {
        case 0:
            $t = 0;
            $i = 0;
            $messagetype = "\n" . '<table id="landing_table" width="100%">';
            $messagetype .= "\n\t" . '<tr>';
            $showdiv = 0;
            foreach ($tresult as &$b) {

                if ($t >= $limit) {
                    if ($showdiv < 1) {
                        if ($i == 1) {
                            $messagetype .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
                            $messagetype .= "\n\t" . '</tr>';
                        };
                        if ($i == 2) {
                            $messagetype .= "\n\t\t" . '<td  id="landing_td"></td>';
                            $messagetype .= "\n\t" . '</tr>';
                        };

                        $messagetype .= "\n" . '</table>';
                        $messagetype .= "\n\t" . '<div id="showhidemessagetypes" style="display:none;"> <!-- start show/hide messagetype div-->';
                        $messagetype .= "\n" . '<table width = "100%" id="landing_table">';

                        $i = 0;
                        $showdiv = 1;
                    }
                }

                if ($i == 0) {
                    $messagetype .= "\n\t" . '<tr>';
                }
                $messagetype .= "\n\t\t" . '<td id="landing_td">';

                $messagetype .= '<a href="index.php?option=com_biblestudy&view=sermons&filter_messagetype=' . $b->id . $langlink . '&filter_book=0&filter_teacher=0&filter_series=0&filter_topic=0&filter_location=0&filter_year=0&t=' . $template . '">';

                $messagetype .= $b->message_type;

                $messagetype .='</a>';

                $messagetype .= '</td>';

                $i++;
                $t++;
                if ($i == 3) {
                    $messagetype .= "\n\t" . '</tr>';
                    $i = 0;
                }
            }
            if ($i == 1) {
                $messagetype .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
            };
            if ($i == 2) {
                $messagetype .= "\n\t\t" . '<td  id="landing_td"></td>';
            };

            $messagetype .= "\n" . '</table>' . "\n";

            if ($showdiv == 1) {

                $messagetype .= "\n\t" . '</div> <!-- close show/hide messagetype div-->';
                $showdiv = 2;
            }
            $messagetype .= '<div id="landing_separator"></div>';
            break;

        case 1:
            $messagetype = '<div class="landingtable" style="display:inline;">';

            foreach ($tresult as $b) {
                if ($b->landing_show == 1) {
                    $messagetype .= '<div class="landingrow">';
                    $messagetype .= '<div class="landingcell"><a class="landinglink" href="index.php?option=com_biblestudy&view=sermons&filter_messagetype=' . $b->id . $langlink . '&filter_book=0&filter_teacher=0&filter_series=0&filter_topic=0&filter_location=0&filter_year=0&t=' . $template . '">';
                    $messagetype .= $b->message_type;
                    $messagetype .='</div></a>';
                    $messagetype .= '</div>';
                }
            }

            $messagetype .= '</div>';
            $messagetype .= '<div id="showhidemessagetypes" style="display:none;">';

            foreach ($tresult as $b) {
                if ($b->landing_show == 2) {
                    $messagetype .= '<div class="landingrow">';
                    $messagetype .= '<div class="landingcell"><a class="landinglink" href="index.php?option=com_biblestudy&view=sermons&filter_messagetype=' . $b->id . $langlink . '&filter_book=0&filter_teacher=0&filter_series=0&filter_topic=0&filter_location=0&filter_year=0&t=' . $template . '">';
                    $messagetype .= $b->message_type;
                    $messagetype .='</div></a>';
                    $messagetype .= '</div>';
                }
            }

            $messagetype .= '</div>';
            $messagetype .= '<div id="landing_separator"></div>';
            break;
    }


    return $messagetype;
}