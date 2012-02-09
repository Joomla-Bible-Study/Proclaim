<?php

/**
 * @version $Id: biblestudy.622.upgrade.php 1 $
 * @package COM_JBSMIGRATION
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

class jbs622Install {

    function upgrade622() {
        $messages = array();
        $msg = false;
        $db = JFactory::getDBO();
        $query = "SELECT count(`id`) FROM  #__bsms_mediafiles WHERE `params` LIKE '%podcast1%' GROUP BY `id`";
        $db->setQuery($query);
        $db->query();
        $rows = $db->loadResult();
        $query = "SELECT `id`, `params` FROM #__bsms_mediafiles WHERE `params` LIKE '%podcast1%'";
        $db->setQuery($query);
        $db->query();
        $results = $db->loadObjectList();
        if ($results) {
            $count = 0;
            foreach ($results AS $result) {
                $oldparams = $result->params;
                $newparams = str_replace('podcast1', 'podcasts', $oldparams);
                $query = "UPDATE #__bsms_mediafiles SET `params` = '" . $newparams . "' WHERE `id` = " . $result->id;
                $msg = $this->performdb($query);
                if (!$msg) {
                    $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
                } else {
                    $messages[] = $msg;
                }
            }
        }
        $query = "INSERT INTO #__bsms_version SET `version` = '6.2.2', `installdate`='2010-10-25', `build`='622', `versionname`='Judges', `versiondate`='2010-10-25'";
        $msg = $this->performdb($query);
        if (!$msg) {
            $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
        } else {
            $messages[] = $msg;
        }
        $results = array('build' => '622', 'messages' => $messages);

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