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
 * @param int $id
 * @param array $admin_params
 * @return string
 */
function getBooksLandingPage($params, $id, $admin_params) {
    $mainframe = & JFactory::getApplication();
    $option = JRequest::getCmd('option');
    $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
    include_once($path1 . 'image.php');
    include_once($path1 . 'helper.php');
    $book = null;
    $teacherid = null;
    $template = $params->get('studieslisttemplateid');
    $limit = $params->get('landingbookslimit');
    if (!$limit) {
        $limit = 10000;
    }
    $menu = JSite::getMenu();
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
    $menu_order = $m_params->get('books_order');
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
    $book = "\n" . '<table id="landing_table" width=100%>';
    $db = & JFactory::getDBO();
    $query = 'select distinct a.* from #__bsms_books a inner join #__bsms_studies b on a.booknumber = b.booknumber order by a.booknumber ' . $order;
    if ($language != '*' && $language) {
        $query = 'select distinct a.* from #__bsms_books a inner join #__bsms_studies b on a.booknumber = b.booknumber where b.language LIKE "' . $language . '" order by a.booknumber ' . $order;
    }
    $db->setQuery($query);

    $tresult = $db->loadObjectList();
    $t = 0;
    $i = 0;

    $book .= "\n\t" . '<tr>';
    $showdiv = 0;
    foreach ($tresult as &$b) {

        if ($t >= $limit) {
            if ($showdiv < 1) {
                if ($i == 1) {
                    $book .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
                    $book .= "\n\t" . '</tr>';
                };
                if ($i == 2) {
                    $book .= "\n\t\t" . '<td  id="landing_td"></td>';
                    $book .= "\n\t" . '</tr>';
                };


                $book .= "\n" . '</table>';
                $book .= "\n\t" . '<div id="showhidebooks" style="display:none;"> <!-- start show/hide book div-->';
                $book .= "\n" . '<table width = "100%" id="landing_table">';

                $i = 0;
                $showdiv = 1;
            }
        }

        if ($i == 0) {
            $book .= "\n\t" . '<tr>';
        }
        $book .= "\n\t\t" . '<td id="landing_td">';
        $book .= '<a href="index.php?option=com_biblestudy&view=sermons&filter_book=' . $b->booknumber . $langlink . '&filter_teacher=0&filter_series=0&filter_topic=0&filter_location=0&filter_year=0&filter_messagetype=0&t=' . $template . '">';
        ##$book .= '<a href="dummy">'; ## can uncomment this line and use instead of above line when bug-fixing for simpler code

        $book .= $numRows;
        $book .= JText::sprintf($b->bookname);

        $book .='</a>';

        $book .= '</td>';
        $i++;
        $t++;
        if ($i == 3) {
            $book .= "\n\t" . '</tr>';
            $i = 0;
        }
    }
    if ($i == 1) {
        $book .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
    };
    if ($i == 2) {
        $book .= "\n\t\t" . '<td  id="landing_td"></td>';
    };

    $book .= "\n" . '</table>' . "\n";

    if ($showdiv == 1) {

        $book .= "\n\t" . '</div> <!-- close show/hide books div-->';
        $showdiv = 2;
    }
    $book .= '<div id="landing_separator"></div>';

    return $book;
}