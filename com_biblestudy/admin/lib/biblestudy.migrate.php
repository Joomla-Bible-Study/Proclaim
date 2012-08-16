<?php

/**
 * Migrate System
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/**
 * Migration Class
 * @package BibleStudy.Admin
 * @since 7.1.0
 */
class JBSMigrate {

    /**
     * Migrate versions
     * @return boolean
     */
    function migrate() {
        //$result = false;
        //$msg2 = '';
        $message = array();
        $application = JFactory::getApplication();
        $db = JFactory::getDBO();

        //First we check to see if there is a current version database installed. This will have a #__bsms_version table so we check for it's existence.
        //check to be sure a really early version is not installed $versiontype: 1 = current version type 2 = older version type 3 = no version
        //
	$tables = $db->getTableList();
        $prefix = $db->getPrefix();
        $versiontype = '';
        $currentversion = false;
        $oldversion = false;
        $jbsexists = false;
        foreach ($tables as $table) {
            $studies = $prefix . 'bsms_version';
            $currentversionexists = substr_count($table, $studies);
            if ($currentversionexists > 0) {
                $currentversion = true;
                $versiontype = 1;
            }
        }
        //Only move forward if a current version type is not found
        if (!$currentversion) {
            //Now let's check to see if there is an older database type (prior to 6.2)
            $oldversion = false;
            foreach ($tables as $table) {
                $studies = $prefix . 'bsms_schemaVersion';
                $oldversionexists = substr_count($table, $studies);
                if ($oldversionexists > 0) {
                    $oldversion = true;
                    $olderversiontype = 1;
                    $versiontype = 2;
                }
            }
            if (!$oldversion) {
                foreach ($tables as $table) {
                    $studies = $prefix . 'bsms_schemaversion';
                    $olderversionexists = substr_count($table, $studies);
                    if ($olderversionexists > 0) {
                        $oldversion = true;
                        $olderversiontype = 2;
                        $versiontype = 2;
                    }
                }
            }
        }
        //Finally if both current version and old version are false, we double check to make sure there are no JBS tables in the database.
        if (!$currentversion && !$oldversion) {
            foreach ($tables as $table) {
                $studies = $prefix . 'bsms_studies';
                $jbsexists = substr_count($table, $studies);
                if (!$jbsexists) {
                    $versiontype = 4;
                }
                if ($jbsexists > 0) {
                    $versiontype = 3;
                }
            }
        }
        //Since we are going to install something, let's check to see if there is a version table, and create it if it isn't there
        foreach ($tables as $table) {
            $studies = $prefix . 'bsms_version';
            $jbsexists1 = substr_count($table, $studies);
            if (!$jbsexists1) {
                $query = "CREATE TABLE IF NOT EXISTS `#__bsms_version` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `version` varchar(20) NOT NULL,
                      `versiondate` date NOT NULL,
                      `installdate` date NOT NULL,
                      `build` varchar(20) NOT NULL,
                      `versionname` varchar(40) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ";
                $db->setQuery($query);
                $db->query();
            }
            $update = $prefix . 'bsms_update';
            $jbsexists2 = substr_count($table, $update);
            if ($jbsexists2 === 0) {
                $query = "CREATE TABLE IF NOT EXISTS `#__bsms_update` (
                        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `version` VARCHAR(255) DEFAULT NULL,
                        PRIMARY KEY (id)
                        ) DEFAULT CHARSET=utf8";
                $db->setQuery($query);
                $db->query();
            }
        }
        //Now we run a switch case on the versiontype and run an install routine accordingly
        switch ($versiontype) {
            case 1:
                //This is a current database version so we check to see which version. We query to get the highest build in the version table
                $query = 'SELECT * FROM #__bsms_version ORDER BY `build` DESC';
                $db->setQuery($query);
                $db->query();
                $version = $db->loadObject();
                switch ($version->build) {
                    case '700':
                        $message[] = $this->update701();
                        $message[] = $this->allupdate();
                        $message[] = $this->update710();
                        break;

                    case '624':
                        $message[] = $this->update700();
                        $message[] = $this->update701();
                        $message[] = $this->allupdate();
                        $message[] = $this->update710();
                        break;

                    case '623':
                        $message[] = $this->update623();
                        $message[] = $this->update700();
                        $message[] = $this->update701();
                        $message[] = $this->allupdate();
                        $message[] = $this->update710();
                        break;

                    case '622':
                        $message[] = $this->update622();
                        $message[] = $this->update623();
                        $message[] = $this->update700();
                        $message[] = $this->update701();
                        $message[] = $this->allupdate();
                        $message[] = $this->update710();
                        break;

                    case '615':
                        $message[] = $this->update622();
                        $message[] = $this->update623();
                        $message[] = $this->update700();
                        $message[] = $this->update701();
                        $message[] = $this->allupdate();
                        $message[] = $this->update710();
                        break;

                    case '614':
                        $message[] = $this->update614();
                        $message[] = $this->update622();
                        $message[] = $this->update623();
                        $message[] = $this->update700();
                        $message[] = $this->update701();
                        $message[] = $this->allupdate();
                        $message[] = $this->update710();
                        break;
                    case NULL:
                        JError::raiseNotice('SOME_ERROR_CODE', 'Bad DB Upload');
                        return FALSE;
                        break;
                }

                break;

            case 2:
                //This is an older version of the software so we check it's version
                if ($olderversiontype == 1) {
                    $db->setQuery("SELECT schemaVersion  FROM #__bsms_schemaVersion");
                } else {
                    $db->setQuery("SELECT schemaVersion FROM #__bsms_schemaversion");
                }
                $schema = $db->loadResult();
                switch ($schema) {
                    case '600':
                        $application->enqueueMessage('' . JText::_('JBS_IBM_VERSION_TOO_OLD') . '');
                        return false;
                        break;

                    case '608':

                        $message[] = $this->update611();
                        $message[] = $this->update613();
                        $message[] = $this->update614();
                        $message[] = $this->update622();
                        $message[] = $this->update623();
                        $message[] = $this->update700();
                        $message[] = $this->update701();
                        $message[] = $this->allupdate();
                        $message[] = $this->update710();
                        break;

                    case '611':

                        $message[] = $this->update613();
                        $message[] = $this->update614();
                        $message[] = $this->update622();
                        $message[] = $this->update623();
                        $message[] = $this->update700();
                        $message[] = $this->update701();
                        $message[] = $this->allupdate();
                        $message[] = $this->update710();
                        break;

                    case '613':

                        $message[] = $this->update614();
                        $message[] = $this->update622();
                        $message[] = $this->update623();
                        $message[] = $this->update700();
                        $message[] = $this->update701();
                        $message[] = $this->allupdate();
                        $message[] = $this->update710();
                        break;
                }
                break;

            case 3:
                //There is a version installed, but it is older than 6.0.8 and we can't upgrade it
                JError::raiseNotice('SOME_ERROR_CODE', 'JBS_IBM_VERSION_TOO_OLD');
                return false;
                break;
        }
        $jbsmessages = $message;
        JRequest::setVar('jbsmessages', $jbsmessages, 'get', 'array');
        return true;
    }

    /**
     * Update for 6.1.1
     * @return string
     */
    function update611() {
        require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'biblestudy.611.upgrade.php');
        $install = new jbs611Install();
        $message = $install->upgrade611();
        return $message;
    }

    /**
     * Update for 6.1.3
     * @return string
     */
    function update613() {
        require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'biblestudy.613.upgrade.php');
        $install = new jbs613Install();
        $message = $install->upgrade613();
        return $message;
    }

    /**
     * Update for 6.1.4
     * @return string
     */
    function update614() {
        require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'biblestudy.614.upgrade.php');
        $install = new jbs614Install();
        $message = $install->upgrade614();
        return $message;
    }

    /**
     * Update for 6.2.2
     * @return string
     */
    function update622() {
        require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'biblestudy.622.upgrade.php');
        $install = new jbs622Install();
        $message = $install->upgrade622();
        return $message;
    }

    /**
     * Update for 6.2.3
     * @return string
     */
    function update623() {
        require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'biblestudy.623.upgrade.php');
        $install = new jbs623Install();
        $message = $install->upgrade623();
        return $message;
    }

    /**
     * Update for 7.0.0
     * @return string
     */
    function update700() {
        require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'biblestudy.700.upgrade.php');
        $install = new jbs700Install();
        $message = $install->upgrade700();
        return $message;
    }

    /**
     * Update for 7.0.1
     * @return string
     */
    function update701() {
        require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'update701.php');
        $install = new updatejbs701();
        $message = $install->do701update();
        return $message;
    }

    /**
     * Update for 7.1.0
     * @return string|boolean
     */
    function update710() {
        require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . 'update710.php');
        $migrate = new JBS710Update();
        $update710php = $migrate->update710();
        if (!$update710php) {
            $errors[] = 'Problem with 7.1.0 update';
        }

        if (!empty($errors)) {
            return $errors;
        } else {
            return true;
        }
    }

    /**
     * Update that is applyed to all migrations
     * @return string
     */
    function allupdate() {
        require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'updateALL.php');
        $install = new updatejbsALL();
        $message = $install->doALLupdate();
        return $message;
    }

}