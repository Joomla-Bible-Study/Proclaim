<?php

/**
 * @version $Id: biblestudy.611.upgrade.php 1 $
 * @package COM_JBSMIGRATION
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

class jbs611Install {

    function upgrade611() {
        $messages = array();
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_locations` (
					`id` INT NOT NULL AUTO_INCREMENT,
					`location_text` VARCHAR(250) NULL,
					`published` TINYINT(1) NOT NULL DEFAULT '1',
					PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`";
        $msg = $this->performdb($query);
        if (!$msg) {
            $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
        } else {
            $messages[] = $msg;
        }

        $query = "ALTER TABLE #__bsms_studies ADD COLUMN show_level varchar(100) NOT NULL default '0' AFTER user_name";
        $msg = $this->performdb($query);
        if (!$msg) {
            $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
        } else {
            $messages[] = $msg;
        }

        $query = "ALTER TABLE #__bsms_studies ADD COLUMN location_id INT(3) NULL AFTER show_level";
        $msg = $this->performdb($query);
        if (!$msg) {
            $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
        } else {
            $messages[] = $msg;
        }

        $query = "INSERT INTO #__bsms_version SET `version` = '6.0.11', `installdate`='2008-10-22', `build`='611', `versionname`='Leviticus', `versiondate`='2008-10-22'";
        $msg = $this->performdb($query);
        if (!$msg) {
            $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
        } else {
            $messages[] = $msg;
        }

        $results = array('build' => '611', 'messages' => $messages);

        return $results;
    }

    function performdb($query) {
        $db = JFactory::getDBO();
        $results = false;
        $db->setQuery($query);
        $db->query();
        if ($db->getErrorNum() != 0) {
            $results = JText::_('JBS_IBM_DB_ERROR') . ': ' . $db->getErrorNum() . "<br /><font color=\"red\">";
            $results .= $db->stderr(true);
            $results .= "</font>";
            return $results;
        } else {
            $results = false;
            return $results;
        }
    }

}