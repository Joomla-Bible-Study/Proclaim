<?php

/**
 * Migration for 6.1.1
 * @package BibleStudy.Admin
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

JLoader::register('JBSMDbHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');

/**
 * Upgrade class for 6.1.1
 * @package BibleStudy.Admin
 * @since 7.0.2
 */
class jbs611Install {

    /**
     * Upgrade function
     * @return array
     */
    function upgrade611() {
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_locations` (
					`id` INT NOT NULL AUTO_INCREMENT,
					`location_text` VARCHAR(250) NULL,
					`published` TINYINT(1) NOT NULL DEFAULT '1',
					PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`";
        if (!JBSMDbHelper::performdb($query, "Build 611: ")) {
            return FALSE;
        }

        $query = "ALTER TABLE #__bsms_studies ADD COLUMN show_level varchar(100) NOT NULL default '0' AFTER user_name";
        if (!JBSMDbHelper::performdb($query, "Build 611: ")) {
            return FALSE;
        }

        $query = "ALTER TABLE #__bsms_studies ADD COLUMN location_id INT(3) NULL AFTER show_level";
        if (!JBSMDbHelper::performdb($query, "Build 611: ")) {
            return FALSE;
        }

        $query = "INSERT INTO #__bsms_version SET `version` = '6.0.11', `installdate`='2008-10-22', `build`='611', `versionname`='Leviticus', `versiondate`='2008-10-22'";
        if (!JBSMDbHelper::performdb($query, "Build 611: ")) {
            return FALSE;
        }

        return TRUE;
    }

}