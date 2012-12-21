<?php

/**
 * Date Helper
 * @package BibleStudy.Site
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
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
            $date = JHTML::_('date', $studydate, "M j, Y");
            break;
        case 1:
            $date = JHTML::_('date', $studydate, "M J");
            break;
        case 2:
            $date = JHTML::_('date', $studydate, "n/j/Y");
            break;
        case 3:
            $date = JHTML::_('date', $studydate, "n/j");
            break;
        case 4:
            $date = JHTML::_('date', $studydate, "l, F j, Y");
            break;
        case 5:
            $date = JHTML::_('date', $studydate, "F j, Y");
            break;
        case 6:
            $date = JHTML::_('date', $studydate, "j F Y");
            break;
        case 7:
            $date = JHTML::_('date', $studydate, "j/n/Y");
            break;
        case 8:
            $date = JHTML::_('date', $studydate, JText::_('DATE_FORMAT_LC'));
            break;
        case 9:
            $date = JHTML::_('date', $studydate, "Y/M/D");
            break;
        default:
            $date = JHTML::_('date', $studydate, "n/j");
            break;
    }

    $customDate = $params->get('custom_date_format');
    if ($customDate != '') {
        $date = JHTML::_('date', $studydate, $customDate);
    }

    return $date;
}