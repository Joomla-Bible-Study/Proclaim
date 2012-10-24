<?php

/**
 * Migration for 6.2.3
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

JLoader::register('jbsDBhelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');

/**
 * Upgrade class for 6.2.3
 * @package BibleStudy.Admin
 * @since 7.0.2
 */
class jbs623Install {

    /**
     * Upgrade funtion
     * @return string
     */
    function upgrade623() {

        $db = JFactory::getDBO();

        //We adjust those rows that have internal_popup set to 0 and we change it to 2
        $query = 'SELECT id, params FROM #__bsms_mediafiles';
        $db->setQuery($query);
        $db->query();
        $results = $db->loadObjectList();
        if ($results) {
            foreach ($results AS $result) {
                $isplayertype = substr_count($result->params, 'internal_popup=0');
                if ($isplayertype) {
                    $oldparams = $result->params;
                    $newparams = str_replace('internal_popup=0', 'internal_popup=2', $oldparams);
                    $query = "UPDATE #__bsms_mediafiles SET `params` = " . $db->quote($newparams) . " WHERE id = " . (int) $db->quote($result->id);
                    $db->setQuery($query);
                    if (!$db->query()) {
                        JError::raiseWarning(1, "Build 623: " . JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)));
                        return FALSE;
                    }
                }
            }
        }

        $query = "INSERT INTO #__bsms_version SET `version` = '6.2.3', `installdate`='2010-11-03', `build`='623', `versionname`='1Samuel', `versiondate`='2010-11-03'";
        if (!jbsDBhelper::performdb($query, "Build 623: ")) {
            return FALSE;
        }

        $query = "INSERT INTO #__bsms_version SET `version` = '6.2.4', `installdate`='2010-11-09', `build`='623', `versionname`='2Samuel', `versiondate`='2010-11-09'";
        if (!jbsDBhelper::performdb($query, "Build 623: ")) {
            return FALSE;
        }

        return TRUE;
    }

}