<?php

/**
 * Filepath Helper
 * @package BibleStudy.Site
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Get File Path
 * @param string $id3
 * @param string $idfield
 * @param string $mime
 * @return string
 */
function getFilepath($id3, $idfield, $mime) {

    $mainframe = JFactory::getApplication();

    $database = JFactory::getDBO();
    $query = 'SELECT #__bsms_mediafiles.*,'
            . ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
            . ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath'
            . ' FROM #__bsms_mediafiles'
            . ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
            . ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
            . ' WHERE ' . $idfield . ' = ' . $id3 . ' AND #__bsms_mediafiles.published = 1 ' . $mime;
    $database->setQuery($query);
    $filepathresults = $database->loadObject();

    if ($filepathresults) {
        $filepath = $filepathresults->spath . $filepathresults->fpath . $filepathresults->filename;
        //Check url for "http://" prefix, and add it if it doesn't exist
        if (!preg_match('@^(?:http://)?([^/]+)@i', $filepath)) {
            $filepath = 'http://' . $filepath;
        }
    } elseif (isset($filepathresults->docMan_id)) {
        $filepath = '<a href="index.php?option=com_docman&task=doc_download&gid=' . $filepathresults->docMan_id . '"';
    } else {
        $filepath = '';
    }
    //$filepathresults->virtueMart_id;
    //$filepathresults->article_id;
    return $filepath;
}