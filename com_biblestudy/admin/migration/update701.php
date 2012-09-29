<?php

/**
 * Migration for 7.0.1
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

JLoader::register('jbsDBhelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');

/**
 * Update class for version 7.0.1
 *
 * @package BibleStudy.Admin
 * @since 7.0.1
 */
class updatejbs701 {

    /**
     * Do the 7.0.1 Update
     * @return array
     */
    function do701update() {
        $db = JFactory::getDBO();
        $tables = $db->getTableFields('#__bsms_topics');
        $languagetag = 0;
        $paramstag = 0;
        foreach ($tables as $table) {
            foreach ($table as $key => $value) {
                if (substr_count($key, 'languages')) {
                    $languagetag = 1;
                    $query = 'ALTER TABLE #__bsms_topics CHANGE `languages` `params` varchar(511) NULL';
                    if (!jbsDBhelper::performdb($query, "Build 701: ")) {
                        return FALSE;
                    }
                } elseif (substr_count($key, 'params')) {
                    $paramstag = 1;
                }
            }
            if (!$languagetag && !$paramstag) {
                $query = 'ALTER TABLE #__bsms_topics ADD `params` varchar(511) NULL';
                if (!jbsDBhelper::performdb($query, "Build 701: ")) {
                    return FALSE;
                }
            }
        }
        $query = 'ALTER TABLE `#__bsms_studytopics` DROP INDEX id, DROP INDEX id_2;';
        if (!jbsDBhelper::performdb($query, "Build 701: ")) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Update Topics
     *
     * @return array
     */
    function updatetopics() {
        $query = 'INSERT INTO #__bsms_studytopics (study_id, topic_id) SELECT #__bsms_studies.id, #__bsms_studies.topics_id FROM #__bsms_studies WHERE #__bsms_studies.topics_id > 0';
        if (!jbsDBhelper::performdb($query, "Build 701: ")) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Update the Database
     *
     * @return array
     */
    function updateUpdatedb() {
        $query = "INSERT INTO `#__bsms_update` (id,version) VALUES (1, '7.0.0'), (2, '7.0.1'), (3,'7.0.1.1')";
        $query = "DELETE FROM `#__assets` WHERE name LIKE '%com_biblestudy.%'";
        if (!jbsDBhelper::performdb($query, "Build 701: ")) {
            return FALSE;
        }
        return TRUE;
    }

}