<?php

/**
 * TextLink Helper
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');

/**
 * Get Textlink
 * @param object $params
 * @param object $row
 * @param string $textorpdf
 * @param object $admin_params
 * @param string $template
 * @return string
 */
function getTextlink($params, $row, $textorpdf, $admin_params, $template) {
    $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
    include_once($path1 . 'scripture.php');
    $JView = new JView();
    $JView->loadHelper('image');
    $JView->loadHelper('helper');
    $scripturerow = 1;
    $scripture1 = getScripture($params, $row, $esv = null, $scripturerow);
    $intro = str_replace('"', '', $row->studyintro);

    $images = new jbsImages();
    $t = JRequest::getVar('t', 1, 'get', 'int');
    if (!$template->text || !substr_count($template->text, '/')) {
        $i_path = 'media/com_biblestudy/images/textfile24.png';
        $textimage = $images->getImagePath($i_path);
        $src = JURI::base() . $textimage->path;
        $height = $textimage->height;
        $width = $textimage->width;
    } elseif (substr_count($template->text, 'http://')) {
        $src = $template->text;
        $height = '24';
        $width = '24';
    } else {
        $i_path = $template->text;
        $textimage = $images->getImagePath($i_path);
        $src = JURI::base() . $textimage->path;
        $height = $textimage->height;
        $width = $textimage->width;
    }

    $link = JRoute::_('index.php?option=com_biblestudy&view=sermon' . '&id=' . $row->id . '&t=' . $t) . JHTML::_('behavior.tooltip');
    $details_text = $params->get('details_text');


    if ($params->get('tooltip') > 0) {
        $linktext = getTooltip($row->id, $row, $params, $admin_params, $template);
    } //end of is show tooltip


    $linktext .= '
	<a href="' . $link . '"><img src="' . $src . '" alt="' . $details_text . '" width="' . $width . '" height="' . $height . '" border="0" />';

    if ($params->get('tooltip') > 0) {
        $linktext .= '</span>';
    }
    $linktext .= '</a></span>';

    return $linktext;
}