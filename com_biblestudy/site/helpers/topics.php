<?php

/**
 * Topics Helper
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Get Topics for LandingPage
 * @param object $params
 * @param int $id
 * @param object $admin_params
 * @return string
 */
function getTopicsLandingPage($params, $id, $admin_params) {
    $mainframe = JFactory::getApplication();
    $user = JFactory::getUser();
    $db = JFactory::getDBO();
    $option = JRequest::getCmd('option');
    $JView = new JView();
    $JView->loadHelper('image');
    $JView->loadHelper('helper');
    $topic = null;
    $teacherid = null;
    $template = $params->get('studieslisttemplateid');
    $limit = $params->get('landingtopicslimit');
    if (!$limit) {
        $limit = 10000;
    }
    $t = JRequest::getVar('t', 1, 'get', 'int');
    $menu = $mainframe->getMenu();
    $item = $menu->getActive();
    $registry = new JRegistry;
    if (isset($item->prams)) {
        $registry->loadJSON($item->params);
        $m_params = $registry;
        $language = $db->quote($item->language) . ',' . $db->quote('*');
        $menu_order = $m_params->get('topics_order');
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

    $query = $db->getQuery('true');
    $query->select('DISTINCT #__bsms_studies.access as access, #__bsms_topics.id, #__bsms_topics.topic_text, #__bsms_topics.params AS topic_params')
            ->from('#__bsms_studies')
            ->join('LEFT', '#__bsms_studytopics ON #__bsms_studies.id = #__bsms_studytopics.study_id')
            ->join('LEFT', '#__bsms_topics ON #__bsms_topics.id = #__bsms_studytopics.topic_id')
            ->where('#__bsms_topics.published = 1')
            ->order('#__bsms_topics.topic_text ' . $order)
            ->where('#__bsms_studies.language in (' . $language . ')')
            ->where('#__bsms_studies.access IN (' . $groups . ')');

    $db->setQuery($query);

    $tresult = $db->loadObjectList();
    $count = count($tresult);
    $t = 0;
    $i = 0;

    if ($count > 0):
        $topic = "\n" . '<table class="landing_table" width="100%"><tr>';
        $showdiv = 0;
        foreach ($tresult as &$b) {
            if ($t >= $limit) {
                if ($showdiv < 1) {
                    if ($i == 1) {
                        $topic .= "\n\t\t" . '<td  class="landing_td"></td>' . "\n\t\t" . '<td class="landing_td"></td>';
                        $topic .= "\n\t" . '</tr>';
                    };
                    if ($i == 2) {
                        $topic .= "\n\t\t" . '<td  class="landing_td"></td>';
                        $topic .= "\n\t" . '</tr>';
                    };


                    $topic .= "\n" . '</table>';
                    $topic .= "\n\t" . '<div id="showhidetopics" style="display:none;"> <!-- start show/hide topics div-->';
                    $topic .= "\n" . '<table width = "100%" class="landing_table"><tr>';

                    $i = 0;
                    $showdiv = 1;
                }
            }
            $topic .= "\n\t\t" . '<td class="landing_td">';
            $topic .= '<a href="index.php?option=com_biblestudy&amp;view=sermons&amp;filter_topic=' . $b->id . '&amp;filter_teacher=0' . $langlink . '&amp;filter_series=0&amp;filter_location=0&amp;filter_book=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t=' . $template . '">';

            $topic .= getTopicItemTranslated($b);

            $topic .='</a>';

            $topic .= '</td>';
            $i++;
            $t++;
            if ($i == 3 && $t != $limit && $t != $count) {
                $topic .= "\n\t" . '</tr><tr>';
                $i = 0;
            } elseif ($i == 3 || $t == $count || $t == $limit) {
                $topic .= "\n\t" . '</tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $topic .= "\n\t\t" . '<td  class="landing_td"></td>' . "\n\t\t" . '<td class="landing_td"></td>';
        };
        if ($i == 2) {
            $topic .= "\n\t\t" . '<td  class="landing_td"></td>';
        };

        $topic .= "\n" . '</table>' . "\n";

        if ($showdiv == 1) {

            $topic .= "\n\t" . '</div> <!-- close show/hide topics div-->';
            $showdiv = 2;
        }
        $topic .= '<div class="landing_separator"></div>';
    else:
        $topic = '<div class="landing_separator"></div>';
    endif;
    return $topic;
}