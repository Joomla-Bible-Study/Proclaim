<?php

/**
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

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

        $messages = array();
        $db = JFactory::getDBO();
        $before = 0;
        $after = 0;
        //First we find out how many rows have the internal_popup set to 0
        $query = "SELECT count(`id`) FROM #__bsms_mediafiles WHERE `params` LIKE '%internal_popup=0%' GROUP BY id";
        $db->setQuery($query);
        $db->query();
        $before = $db->loadResult();

        //Now we adjust those rows that have internal_popup set to 0 and we change it to 2
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
                    $query = "UPDATE #__bsms_mediafiles SET `params` = '" . $newparams . "' WHERE id = " . $result->id;
                    $db->setQuery($query);
                    $db->query();
                }
            }
        }

        $query = "INSERT INTO #__bsms_version SET `version` = '6.2.3', `installdate`='2010-11-03', `build`='623', `versionname`='1Samuel', `versiondate`='2010-11-03'";
        $msg = $this->performdb($query);
        if (!$msg) {
            $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
        } else {
            $messages[] = $msg;
        }
        $query = "INSERT INTO #__bsms_version SET `version` = '6.2.4', `installdate`='2010-11-09', `build`='623', `versionname`='2Samuel', `versiondate`='2010-11-09'";
        $msg = $this->performdb($query);
        if (!$msg) {
            $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
        } else {
            $messages[] = $msg;
        }
        $results = array('build' => '623', 'messages' => $messages);

        return $results;
    }

    /**
     * Perform DB Query
     * @param string $query
     * @return string|boolean
     */
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