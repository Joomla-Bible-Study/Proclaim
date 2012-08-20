<?php

/**
 * Install Script
 * @package BibleStudy
 * @subpackage Plugin.JBSBackup
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * Script file of JBSBACKUP component
 * @package BibleStudy
 * @subpackage Plugin.JBSBackup
 * @since 7.1.0
 */
class plgSystemjbsbackupInstallerScript {

    /**
     * method to install the component
     * @param string $parent
     * @return void
     */
    function install($parent) {

    }

    /**
     * method to uninstall the component
     * @param string $parent
     * @return void
     */
    function uninstall($parent) {
        // $parent is the class calling this method
        echo '<p>' . JText::_('PLG_JBSBACKUP_UNINSTALL_TEXT') . '</p>';
    }

    /**
     * method to update the component
     * @param string $parent
     * @return void
     */
    function update($parent) {
        $db = JFactory::getDBO();
        // First see if there is an update table
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();
        $updatetable = $prefix . 'jbsbackup_timeset';
        $updatefound = false;
        $this->is700 = false;
        foreach ($tables as $table) {
            if ($table == $updatetable) {
                $updatefound = true;
            }
        }
        if (!$updatefound) {
            //Do the query here to create the table. This will tell Joomla to update the db from this version on
            $query = "CREATE TABLE IF NOT EXISTS `#__jbsbackup_timeset` (
	`timeset` varchar(14) NOT NULL DEFAULT '',
	`backup` varchar(14) DEFAULT NULL,
	PRIMARY KEY (`timeset`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8";
            $db->setQuery($query);
            $db->query();
            $query = "INSERT INTO `#__jbsbackup_timeset` (`timeset`, `backup`) VALUES
	( '1281646339', '1281646339')";
            $db->setQuery($query);
            $db->query();
        }
        // $parent is the class calling this method
        echo '<p>' . JText::_('PLG_JBSBACKUP_UPDATE_TEXT') . '</p>';
    }

    /**
     * method to run before an install/update/uninstall method
     * @param string $type
     * @param string $parent
     * @return void
     */
    function preflight($type, $parent) {
        // $type is the type of change (install, update or discover_install)
        //echo '<p>' . JText::_('PLG_JBSBACKUP_PREFLIGHT_' . $type . '_TEXT') . '</p>';
    }

    /**
     * method to run after an install/update/uninstall method
     * @param string $type
     * @param string $parent
     * @return void
     */
    function postflight($type, $parent) {
        // $parent is the class calling this method
        // $type is the type of change (install, update or discover_install)
        //echo '<p>' . JText::_('PLG_JBSBACKUP_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
    }

}
