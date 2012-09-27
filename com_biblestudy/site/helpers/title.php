<?php

/**
 * Title Helper
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Get Title
 * @param object $params
 * @param object $row
 * @param object $admin_params
 * @param object $template
 * @return string
 */
function getTitle($params, $row, $admin_params, $template) {
    $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
    include_once($path1 . 'custom.php');

    if ($params->get('title_line_1') > 0) {
        $title = '<table id="titletable" cellspacing="0"><tbody><tr><td class="titlefirstline">';
        switch ($params->get('title_line_1')) {
            case 0:
                $title .= null;
                break;
            case 1:
                $title .= $row->studytitle;
                break;
            case 2:
                $title .= $row->teachername;
                break;
            case 3:
                $title .= $row->title . ' ' . $row->teachername;
                break;
            case 4:
                $esv = 0;
                $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
                include_once($path1 . 'scripture.php');
                $scripture = getScripture($params, $row, $esv, $scripturerow = 1);
                $title .= $scripture;
                break;
            case 5:
                $title .= $row->stext;
                break;
            case 6:
                $title .= $row->topics_text;
                break;
            case 7:
                $elementid = getCustom($rowid = null, $params->get('customtitle1'), $row, $params, $admin_params, $template);
                $title .= $elementid->element;
                break;
        }
        $title .= '</td></tr>';
    }

    if ($params->get('title_line_2') > 0) {
        $title .= '<tr><td class="titlesecondline" >';
        switch ($params->get('title_line_2')) {
            case 0:
                $title .= null;
                break;
            case 1:
                $title .= $row->studytitle;
                break;
            case 2:
                $title .= $row->teachername;
                break;
            case 3:
                $title .= $row->title . ' ' . $row->teachername;
                break;
            case 4:
                $esv = 0;
                $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
                include_once($path1 . 'scripture.php');
                $scripture = getScripture($params, $row, $esv, $scripturerow = 1);
                $title .= $scripture;
                break;
            case 5:
                $title .= $row->stext;
                break;
            case 6:
                $title .= $row->topics_text;
                break;
            case 7:
                $elementid = getCustom($rowid = null, $params->get('customtitle2'), $row, $params, $admin_params, $template);
                $title .= $elementid->element;
                break;
        }
        $title .= '</td></tr>';
    } // end of if title2
    $title .= '</tbody></table>';
    return $title;
}
