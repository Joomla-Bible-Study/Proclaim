<?php

/**
 * Server Helper
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Get Server
 *
 * @param type $serverid
 * @return type
 */
function getServer($serverid) {
    $mainframe = & JFactory::getApplication();
    $option = JRequest::getCmd('option');
    $db = & JFactory::getDBO();
    $query = 'select distinct * from #__bsms_servers where id = ' . $serverid;

    $db->setQuery($query);

    $tresult = $db->loadObject();

    $i = 0;

    return $tresult;
}

/**
 * Get Folder
 *
 * @param int $folderId
 * @return object
 */
function getFolder($folderId) {
    $mainframe = & JFactory::getApplication();
    $option = JRequest::getCmd('option');

    $db = & JFactory::getDBO();
    $query = 'select distinct * from #__bsms_folders where id = ' . $folderId;

    $db->setQuery($query);

    $tresult = $db->loadObject();

    $i = 0;

    return $tresult;
}