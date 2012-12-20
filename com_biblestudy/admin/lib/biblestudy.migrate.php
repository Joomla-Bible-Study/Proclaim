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
        // check to see if version is newer then 7.0.2
        foreach ($tables as $table) {
            $studies = $prefix . 'bsms_update';
            $currentversionexists = substr_count($table, $studies);
            if ($currentversionexists > 0) {
                $currentversion = true;
                $versiontype = 1;
            }
        }
        if ($versiontype !== 1):
            foreach ($tables as $table) {
                $studies = $prefix . 'bsms_version';
                $currentversionexists = substr_count($table, $studies);
                if ($currentversionexists > 0) {
                    $currentversion = true;
                    $versiontype = 2;
                }
            }
        endif;
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
                    $versiontype = 3;
                }
            }
            if (!$oldversion) {
                foreach ($tables as $table) {
                    $studies = $prefix . 'bsms_schemaversion';
                    $olderversionexists = substr_count($table, $studies);
                    if ($olderversionexists > 0) {
                        $oldversion = true;
                        $olderversiontype = 2;
                        $versiontype = 3;
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
                    $versiontype = 5;
                }
                if ($jbsexists > 0) {
                    $versiontype = 4;
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
                if (!$db->execute()) {
                    JError::raiseWarning(1, JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)));
                    return FALSE;
                }
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
                if (!$db->execute()) {
                    JError::raiseWarning(1, JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)));
                    return FALSE;
                }
            }
        }

        //Now we run a switch case on the versiontype and run an install routine accordingly
        // @todo need to rewright this like we do the sql update on allupdate.php but for now will work it this way.
        switch ($versiontype) {
            case 1:
                JBSMigrate::corectversions();
                /* Find Last updated Version in Update table */
                $query = 'SELECT * FROM #__bsms_update ORDER BY `version` DESC';
                $db->setQuery($query);
                $updates = $db->loadObject();
                $version = $updates->version;
                switch ($version):
                    case '7.0.1':
                        if (!$this->allupdate()) {
                            return FALSE;
                        }
                        if (!$this->update710()) {
                            return FALSE;
                        }
                        break;
                    case '7.0.1.1':
                        if (!$this->allupdate()) {
                            return FALSE;
                        }
                        if (!$this->update710()) {
                            return FALSE;
                        }
                        break;
                    case '7.0.2':
                        if (!$this->allupdate()) {
                            return FALSE;
                        }
                        if (!$this->update710()) {
                            return FALSE;
                        }
                        break;
                    case '7.0.3':
                        if (!$this->allupdate()) {
                            return FALSE;
                        }
                        if (!$this->update710()) {
                            return FALSE;
                        }
                        break;
                    case '7.0.4':
                        if (!$this->allupdate()) {
                            return FALSE;
                        }
                        if (!$this->update710()) {
                            return FALSE;
                        }
                        break;
                endswitch;
                break;
            case 2:
                //This is a current database version so we check to see which version. We query to get the highest build in the version table
                $query = 'SELECT * FROM #__bsms_version ORDER BY `build` DESC';
                $db->setQuery($query);
                $db->query();
                $version = $db->loadObject();
                switch ($version->build) {
                    case '700':
                        if (!$this->update701()) {
                            return FALSE;
                        }
                        if (!$this->allupdate()) {
                            return FALSE;
                        }
                        if (!$this->update710()) {
                            return FALSE;
                        }
                        break;

                    case '624':
                        if (!$this->update700()) {
                            return FALSE;
                        }
                        if (!$this->update701()) {
                            return FALSE;
                        }
                        if (!$this->allupdate()) {
                            return FALSE;
                        }
                        if (!$this->update710()) {
                            return FALSE;
                        }
                        break;

                    case '623':
                        if (!$this->update623()) {
                            return FALSE;
                        }
                        if (!$this->update700()) {
                            return FALSE;
                        }
                        if (!$this->update701()) {
                            return FALSE;
                        }
                        if (!$this->allupdate()) {
                            return FALSE;
                        }
                        if (!$this->update710()) {
                            return FALSE;
                        }
                        break;

                    case '622':
                        if (!$this->update622()) {
                            return FALSE;
                        }
                        if (!$this->update623()) {
                            return FALSE;
                        }
                        if (!$this->update700()) {
                            return FALSE;
                        }
                        if (!$this->update701()) {
                            return FALSE;
                        }
                        if (!$this->allupdate()) {
                            return FALSE;
                        }
                        if (!$this->update710()) {
                            return FALSE;
                        }
                        break;

                    case '615':
                        if (!$this->update622()) {
                            return FALSE;
                        }
                        if (!$this->update623()) {
                            return FALSE;
                        }
                        if (!$this->update700()) {
                            return FALSE;
                        }
                        if (!$this->update701()) {
                            return FALSE;
                        }
                        if (!$this->allupdate()) {
                            return FALSE;
                        }
                        if (!$this->update710()) {
                            return FALSE;
                        }
                        break;

                    case '614':
                        if (!$this->update614()) {
                            return FALSE;
                        }
                        if (!$this->update622()) {
                            return FALSE;
                        }
                        if (!$this->update623()) {
                            return FALSE;
                        }
                        if (!$this->update700()) {
                            return FALSE;
                        }
                        if (!$this->update701()) {
                            return FALSE;
                        }
                        if (!$this->allupdate()) {
                            return FALSE;
                        }
                        if (!$this->update710()) {
                            return FALSE;
                        }
                        break;
                    case NULL:
                        JError::raiseNotice('SOME_ERROR_CODE', 'Bad DB Upload');
                        return FALSE;
                        break;
                }

                break;

            case 3:
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
                        if (!$this->update611()) {
                            return FALSE;
                        }
                        if (!$this->update613()) {
                            return FALSE;
                        }
                        if (!$this->update614()) {
                            return FALSE;
                        }
                        if (!$this->update622()) {
                            return FALSE;
                        }
                        if (!$this->update623()) {
                            return FALSE;
                        }
                        if (!$this->update700()) {
                            return FALSE;
                        }
                        if (!$this->update701()) {
                            return FALSE;
                        }
                        if (!$this->allupdate()) {
                            return FALSE;
                        }
                        if (!$this->update710()) {
                            return FALSE;
                        }
                        break;

                    case '611':
                        if (!$this->update613()) {
                            return FALSE;
                        }
                        if (!$this->update614()) {
                            return FALSE;
                        }
                        if (!$this->update622()) {
                            return FALSE;
                        }
                        if (!$this->update623()) {
                            return FALSE;
                        }
                        if (!$this->update700()) {
                            return FALSE;
                        }
                        if (!$this->update701()) {
                            return FALSE;
                        }
                        if (!$this->allupdate()) {
                            return FALSE;
                        }
                        if (!$this->update710()) {
                            return FALSE;
                        }
                        break;

                    case '613':
                        if (!$this->update614()) {
                            return FALSE;
                        }
                        if (!$this->update622()) {
                            return FALSE;
                        }
                        if (!$this->update623()) {
                            return FALSE;
                        }
                        if (!$this->update700()) {
                            return FALSE;
                        }
                        if (!$this->update701()) {
                            return FALSE;
                        }
                        if (!$this->allupdate()) {
                            return FALSE;
                        }
                        if (!$this->update710()) {
                            return FALSE;
                        }
                        break;
                }
                break;

            case 4:
                //There is a version installed, but it is older than 6.0.8 and we can't upgrade it
                JError::raiseNotice('SOME_ERROR_CODE', 'JBS_IBM_VERSION_TOO_OLD');
                return false;
                break;
        }
        return TRUE;
    }

    /**
     * Update for 6.1.1
     * @return string
     */
    function update611() {
        //require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'biblestudy.611.upgrade.php');
        JLoader::register('jbs611Install', dirname(__FILE__) . '/migration/biblestudy.611.upgrade.php');
        $install = new jbs611Install();
        if (!$install->upgrade611()) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Update for 6.1.3
     * @return string
     */
    function update613() {
        //require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'biblestudy.613.upgrade.php');
        JLoader::register('jbs613Install', dirname(__FILE__) . '/migration/biblestudy.613.upgrade.php');
        $install = new jbs613Install();
        if (!$install->upgrade613()) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Update for 6.1.4
     * @return string
     */
    function update614() {
        //require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'biblestudy.614.upgrade.php');
        JLoader::register('jbs614Install', dirname(__FILE__) . '/migration/biblestudy.614.upgrade.php');
        $install = new jbs614Install();
        if (!$install->upgrade614()) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Update for 6.2.2
     * @return string
     */
    function update622() {
        //require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'biblestudy.622.upgrade.php');
        JLoader::register('jbs622Install', dirname(__FILE__) . '/migration/biblestudy.622.upgrade.php');
        $install = new jbs622Install();
        if (!$install->upgrade622()) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Update for 6.2.3
     * @return string
     */
    function update623() {
       // require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'biblestudy.623.upgrade.php');
        JLoader::register('jbs623Install', dirname(__FILE__) . '/migration/biblestudy.623.upgrade.php');
        $install = new jbs623Install();
        if (!$install->upgrade623()) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Update for 7.0.0
     * @return string
     */
    function update700() {
       // require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'biblestudy.700.upgrade.php');
        JLoader::register('jbs700Install', dirname(__FILE__) . '/migration/biblestudy.700.upgrade.php');
        $install = new jbs700Install();
        if (!$install->upgrade700()) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Update for 7.0.1
     * @return string
     */
    function update701() {
        //require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'update701.php');
        JLoader::register('updatejbs701', dirname(__FILE__) . '/migration/update701.php');
        $install = new updatejbs701();
        if (!$install->do701update()) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Update for 7.1.0
     * @return string|boolean
     */
    function update710() {
        //require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . 'update710.php');
        JLoader::register('JBS710Update', dirname(__FILE__) . '/install/updates/update710.php');
        $migrate = new JBS710Update();
        if (!$migrate->update710()) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Update that is applyed to all migrations
     * @return string
     */
    function allupdate() {
        //require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . 'updateALL.php');
        JLoader::register('updatejbsALL', dirname(__FILE__) . '/migration/updateALL.php');
        $install = new updatejbsALL();
        if (!$install->doALLupdate()) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Correct problem in are update table under 7.0.2 systems
     */
    static function corectversions() {
        $db = JFactory::getDBO();
        /* Find Last updated Version in Update table */
        $query = 'SELECT * FROM #__bsms_update';
        $db->setQuery($query);
        $updates = $db->loadObjectlist();
        foreach ($updates AS $value):
            /* Check to see if Bad version is in key 3 */
            if (($value->id === '3') && ($value->version !== '7.0.1.1')):
                /* Find Last updated Version in Update table */
                $query = "INSERT INTO `#__bsms_update` (id,version) VALUES (3,'7.0.1.1')
                            ON DUPLICATE KEY UPDATE version= '7.0.1.1';
                            ";
                $db->setQuery($query);
                if (!$db->execute()) {
                    JError::raiseWarning('1', JText::_('JBS_CMN_OPERATION_FAILED'));
                    return FALSE;
                }
            endif;
        endforeach;

        return TRUE;
    }

}