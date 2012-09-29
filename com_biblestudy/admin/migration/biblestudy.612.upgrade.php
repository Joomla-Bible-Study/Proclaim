<?php

/**
 * Migration for 6.1.2
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

JLoader::register('jbsDBhelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');

/**
 * Upgrade class for 6.1.2
 * @package BibleStudy.Admin
 * @since 7.0.2
 */
class JBS612Install {

    /**
     * Upgrade Function
     * @return string
     */
    function upgrade612() {
        $query = "UPDATE #__bsms_mediafiles SET params = 'player=2', internal_viewer = '0' WHERE internal_viewer = '1' AND params IS NULL";
        if (!jbsDBhelper::performdb($query, "Build 612: ")) {
            return FALSE;
        }

        return TRUE;
    }

}