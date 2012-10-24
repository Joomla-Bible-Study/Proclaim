<?php

/**
 * Migration for 6.2.2
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

JLoader::register('jbsDBhelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');

/**
 * Update from 6.2.2
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class jbs622Install {

    /**
     * Upgrade Function
     * @return string
     */
    function upgrade622() {
        $db = JFactory::getDBO();
        $query = "SELECT `id`, `params` FROM #__bsms_mediafiles WHERE `params` LIKE '%podcast1%'";
        $db->setQuery($query);
        $db->query();
        $results = $db->loadObjectList();
        if ($results) {
            foreach ($results AS $result) {
                $oldparams = $result->params;
                $newparams = str_replace('podcast1', 'podcasts', $oldparams);
                $query = "UPDATE #__bsms_mediafiles SET `params` = " . $db->quote($newparams) . " WHERE `id` = " . (int) $db->quote($result->id);
                if (!jbsDBhelper::performdb($query, "Build 622: ")) {
                    return FALSE;
                }
            }
        }
        $query = "INSERT INTO #__bsms_version SET `version` = '6.2.2', `installdate`='2010-10-25', `build`='622', `versionname`='Judges', `versiondate`='2010-10-25'";
        if (!jbsDBhelper::performdb($query, "Build 622: ")) {
            return FALSE;
        }

        return TRUE;
    }

}