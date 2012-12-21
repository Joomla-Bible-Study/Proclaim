<?php

/**
 * Filesize Helper
 * @package BibleStudy.Site
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Function to get File Size
 * @param string $file_size
 * @return null|string
 */
function getFilesize($file_size) {
    if (!$file_size) {
        $file_size = null;
        return $file_size;
    }
    switch ($file_size) {
        case $file_size < 1024 :
            $file_size = $file_size . ' ' . 'Bytes';
            break;
        case $file_size < 1048576 :
            $file_size = $file_size / 1024;
            $file_size = number_format($file_size, 0);
            $file_size = $file_size . ' ' . 'KB';
            break;
        case $file_size < 1073741824 :
            $file_size = $file_size / 1024;
            $file_size = $file_size / 1024;
            $file_size = number_format($file_size, 1);
            $file_size = $file_size . ' ' . 'MB';
            break;
        case $file_size > 1073741824 :
            $file_size = $file_size / 1024;
            $file_size = $file_size / 1024;
            $file_size = $file_size / 1024;
            $file_size = number_format($file_size, 1);
            $file_size = $file_size . ' ' . 'GB';
            break;
    }
    return $file_size;
}

//End of function