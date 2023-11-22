<?php

/**
 * Router for Remote website that have treble with downloader.
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://www.christianwebministries.org
 * */

$file = $_GET['file'];
$size = $_GET['size'];

// Check url for "http://" prefix, and add it if it doesn't exist

if (!preg_match('/^http(s)?:\/\//', $file)) {
    $file = 'http://' . $file;
}

$new_size = getRemoteFileSize($file);

if ($size != $new_size) {
    $size = $new_size;
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
header('Expires: 0');
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private", false);
header('Pragma: public');
header("Content-Transfer-Encoding: binary");
header('Content-Length: ' . $size);
readfile($file);

/**
 * Method to get file size
 *
 * @param   string  $url  URL
 *
 * @return  boolean
 *
 * @since 8.0.0
 */
function getRemoteFileSize($url): bool|int
{
    if (empty($url)) {
        return 0;
    }

    // Removes a bad url problem in some DB's
    if (substr_count($url, '/http')) {
        $url = ltrim($url, '/');
    }

    if (!substr_count($url, 'http://') && !substr_count($url, 'https://')) {
        if (substr_count($url, '//')) {
            $url = 'http:' . $url;
        } elseif (!substr_count($url, '//')) {
            $url = 'http://' . $url;
        }
    }

    $head = array_change_key_case(get_headers($url, true));

    if (isset($head['content-length'])) {
        return $head['content-length'];
    }

    return 0;
}
