<?php

/**
 * Migration for 7.0.1
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

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
                    $msg = $this->performdb($query);
                    if (!$msg) {
                        $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
                    } else {
                        $messages[] = $msg;
                    }
                } elseif (substr_count($key, 'params')) {
                    $paramstag = 1;
                }
            }
            if (!$languagetag && !$paramstag) {
                $query = 'ALTER TABLE #__bsms_topics ADD `params` varchar(511) NULL';
                $msg = $this->performdb($query);
                if (!$msg) {
                    $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
                } else {
                    $messages[] = $msg;
                }
            }
        }
        $query = 'ALTER TABLE `#__bsms_studytopics` DROP INDEX id, DROP INDEX id_2;';
        $msg = $this->performdb($query);
        if (!$msg) {
            $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
        } else {
            $messages[] = $msg;
        }
        $messages[] = $this->updatetopics();
        $messages[] = $this->updateUpdatedb();


        $results = array('build' => '701', 'messages' => $messages);

        return $results;
    }

    /**
     * Update Topics
     *
     * @return array
     */
    function updatetopics() {
        $query = 'INSERT INTO #__bsms_studytopics (study_id, topic_id) SELECT #__bsms_studies.id, #__bsms_studies.topics_id FROM #__bsms_studies WHERE #__bsms_studies.topics_id > 0';
        $msg = $this->performdb($query);
        if (!$msg) {
            $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
        } else {
            $messages[] = $msg;
        }
        return $messages;
    }

    /**
     * Update the Database
     *
     * @return array
     */
    function updateUpdatedb() {
        $query = "INSERT INTO `#__bsms_update` (id,version) VALUES (1, '7.0.0'), (2, '7.0.1'), (3,'7.0.1.1')";
        $query = "DELETE FROM `#__assets` WHERE name LIKE '%com_biblestudy.%'";
        $msg = $this->performdb($query);
        if (!$msg) {
            $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
        } else {
            $messages[] = $msg;
        }
        return $messages;
    }

    /**
     * Perform DB Query
     *
     * @param array $query
     * @return string|boolean
     */
    function performdb($query) {
        $db = JFactory::getDBO();
        $results = false;
        $db->setQuery($query);
        $db->execute();
        if ($db->getErrorNum() != 0) {
            $results = JText::_('JBS_IBM_DB_ERROR') . ': ' . $db->getErrorNum() . "<br /><font color=\"red\">";
            $results .= $db->stderr(true);
            $results .= "</font>";
            return $results;
        } else {
            $results = true;
            return $results;
        }
    }

}