<?php

/**
 * Date Helper
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Get StudyDate
 * @param object $params
 * @param string $studydate
 * @return string
 */
function getstudyDate($params, $studydate) {
    switch ($params->get('date_format')) {
        case 0:
            $date = JHTML::_('date', $studydate, JText::_('JBS_DATE_FORMAT_0'));
            break;
        case 1:
            $date = JHTML::_('date', $studydate, JText::_('JBS_DATE_FORMAT_1'));
            break;
        case 2:
            $date = JHTML::_('date', $studydate, JText::_('JBS_DATE_FORMAT_2'));
            break;
        case 3:
            $date = JHTML::_('date', $studydate, JText::_('JBS_DATE_FORMAT_3'));
            break;
        case 4:
            $date = JHTML::_('date', $studydate, JText::_('JBS_DATE_FORMAT_4'));
            break;
        case 5:
            $date = JHTML::_('date', $studydate, JText::_('JBS_DATE_FORMAT_5'));
            break;
        case 6:
            $date = JHTML::_('date', $studydate, JText::_('JBS_DATE_FORMAT_6'));
            break;
        case 7:
            $date = JHTML::_('date', $studydate, JText::_('JBS_DATE_FORMAT_7'));
            break;
        case 8:
            $date = JHTML::_('date', $studydate, JText::_('DATE_FORMAT_LC'));
            break;
        case 9:
            $date = JHTML::_('date', $studydate, JText::_('JBS_DATE_FORMAT_9'));
            break;
        default:
            $date = JHTML::_('date', $studydate, JText::_('JBS_DATE_FORMAT_DEFAULT'));
            break;
    }

    $customDate = $params->get('custom_date_format');
    if ($customDate != '') {
        $date = JHTML::_('date', $studydate, $customDate);
    }

    return $date;
}