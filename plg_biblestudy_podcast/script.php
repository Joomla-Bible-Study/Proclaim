<?php

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Script file of jbspodcast component
 */
class plgSystemjbspodcastInstallerScript {

    /**
     * method to install the component
     *
     * @return void
     */
    function install($parent) {

    }

    /**
     * method to uninstall the component
     *
     * @return void
     */
    function uninstall($parent) {
        // $parent is the class calling this method
        echo '<p>' . JText::_('PLG_PODCAST_UNINSTALL_TEXT') . '</p>';
    }

    /**
     * method to update the component
     *
     * @return void
     */
    function update($parent) {
        // check to see if we are dealing with version 7.0.0 and create the update table if needed
        $db = JFactory::getDBO();
        // First see if there is an update table
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();
        $updatetable = $prefix . 'jbspodcast_timeset';
        $updatefound = false;
        $this->is700 = false;
        foreach ($tables as $table) {
            if ($table == $updatetable) {
                $updatefound = true;
            }
        }
        if (!$updatefound) {
            //Do the query here to create the table. This will tell Joomla to update the db from this version on
            $query = "CREATE TABLE IF NOT EXISTS `#__jbspodcast_timeset` (
	`timeset` varchar(14) NOT NULL DEFAULT '',
	`backup` varchar(14) DEFAULT NULL,
	PRIMARY KEY (`timeset`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8";
            $db->setQuery($query);
            $db->query();
            $query = "INSERT INTO `#__jbspodcast_timeset` (`timeset`, `backup`) VALUES
	( '1281646339', '1281646339')";
            $db->setQuery($query);
            $db->query();
        }
        // $parent is the class calling this method
        echo '<p>' . JText::_('PLG_PODCAST_UPDATE_TEXT') . '</p>';
    }

    /**
     * method to run before an install/update/uninstall method
     *
     * @return void
     */
    function preflight($type, $parent) {
        // check to see if we are dealing with version 7.0.0 and create the update table if needed
        $db = JFactory::getDBO();
        // First see if there is an update table
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();
        $updatetable = $prefix . 'jbspodcast_update';
        $updatefound = false;
        $this->is700 = false;
        foreach ($tables as $table) {
            if ($table == $updatetable) {
                $updatefound = true;
            }
        }
        if (!$updatefound) {
            //Do the query here to create the table. This will tell Joomla to update the db from this version on
            $query = 'CREATE TABLE IF NOT EXISTS #__jbspodcast_update (
                              id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                              version VARCHAR(255) DEFAULT NULL,
                              PRIMARY KEY (id)
                            ) DEFAULT CHARSET=utf8';
            $db->setQuery($query);
            $db->query();
            $query = "INSERT INTO #__jbspodcast_update (id,version) VALUES(1,'7.0.0')";
            $db->setQuery($query);
            $db->query();
        }
        // $parent is the class calling this method
        // $type is the type of change (install, update or discover_install)
        echo '<p>' . JText::_('PLG_PODCAST_PREFLIGHT_' . $type . '_TEXT') . '</p>';
    }

    /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    function postflight($type, $parent) {
        // $parent is the class calling this method
        // $type is the type of change (install, update or discover_install)
        echo '<p>' . JText::_('PLG_PODCAST_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
    }

}
