<?php

/**
 * Book Helper
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Get Books for Landing Page.
 *
 * @param array $params
 * @return string
 */
function getBooksLandingPage($params) {
    $user = JFactory::getUser();
    $db = JFactory::getDBO();
    $JView = new JView();
    $JView->loadHelper('image');
    $JView->loadHelper('helper');
    $book = null;
    $template = $params->get('studieslisttemplateid');
    $limit = $params->get('landingbookslimit');
    if (!$limit) {
        $limit = 10000;
    }
    $app = JFactory::getApplication();
    $menu = $app->getMenu();
    $item = $menu->getActive();
    $registry = new JRegistry;
    if (isset($item->prams)) {
        $registry->loadJSON($item->params);
        $m_params = $registry;
        $language = $db->quote($item->language). ',' . $db->quote('*');
        $menu_order = $m_params->get('books_order');
    } else {
        $language = $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*');
        $menu_order = null;
    }
    if ($language == '*' || !$language) {
        $langlink = '';
    } elseif ($language != '*') {
        $langlink = '&amp;filter.languages=' . $language;
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
            ->from('#__bsms_books a')
            ->select('b.access AS access')
            ->innerJoin('#__bsms_studies b on a.booknumber = b.booknumber')
            ->where('b.language in (' . $language . ')')
            ->where('b.access IN (' . $groups . ')')
            ->order('a.booknumber ' . $order);
    $db->setQuery($query);

    $tresult = $db->loadObjectList();
    $count = count($tresult);
    $t = 0;
    $i = 0;

    if ($count > 0):
        $book = "\n" . '<table class="landing_table" width="100%" ><tr>';
        $showdiv = 0;
        foreach ($tresult as &$b) {
            if ($t >= $limit) {
                if ($showdiv < 1) {
                    if ($i == 1) {
                        $book .= "\n\t\t" . '<td  class="landing_td"></td>' . "\n\t\t" . '<td class="landing_td"></td>';
                        $book .= "\n\t" . '</tr>';
                    };
                    if ($i == 2) {
                        $book .= "\n\t\t" . '<td  class="landing_td"></td>';
                        $book .= "\n\t" . '</tr>';
                    };


                    $book .= "\n" . '</table>';
                    $book .= "\n\t" . '<div id="showhidebooks" style="display:none;"> <!-- start show/hide book div-->';
                    $book .= "\n" . '<table width = "100%" class="landing_table"><tr>';

                    $i = 0;
                    $showdiv = 1;
                }
            }
            $book .= "\n\t\t" . '<td class="landing_td">';
            $book .= '<a href="index.php?option=com_biblestudy&amp;view=sermons&amp;filter_book=' . $b->booknumber . $langlink . '&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t=' . $template . '">';

            $book .= JText::sprintf($b->bookname);

            $book .='</a>';

            $book .= '</td>';
            $i++;
            $t++;
            if ($i == 3 && $t != $limit && $t != $count) {
                $book .= "\n\t" . '</tr><tr>';
                $i = 0;
            } elseif ($i == 3 || $t == $count || $t == $limit) {
                $book .= "\n\t" . '</tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $book .= "\n\t\t" . '<td  class="landing_td"></td>' . "\n\t\t" . '<td class="landing_td"></td>';
        };
        if ($i == 2) {
            $book .= "\n\t\t" . '<td  class="landing_td"></td>';
        };

        $book .= "\n" . '</table>' . "\n";

        if ($showdiv == 1) {

            $book .= "\n\t" . '</div> <!-- close show/hide books div-->';
            $showdiv = 2;
        }
        $book .= '<div class="landing_separator"></div>';
    else:
        $book = '';
    endif;

    return $book;
}