<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/**
 * Update on All upgrades
 *
 * @package BibleStudy.Admin
 * @since 7.0.3
 */
class updatejbsALL {

    /**
     * Funtion to do updates
     *
     * @return array
     */
    private function doALLupdate() {

        $messages = array();
        $results = array();
        $db = JFactory::getDBO();
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        $path = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . 'mysql';
        $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX');
        $excludefilter = array('^\..*', '.*~');
        $files = JFolder::files($path, '', '', 'false', $exclude, $excludefilter);
        foreach ($files as $i => $value) {
            if (!substr_count($value, '.sql')) {
                unset($files[$i]);
            } elseif (substr_count($value, '7.0.0')) {
                unset($files[$i]);
            } elseif (substr_count($value, '7.0.1')) {
                unset($files[$i]);
            } elseif (substr_count($value, '7.0.1.1')) {
                unset($files[$i]);
            } else {
                $query = file_get_contents($value);
                $db->setQuery($query);
                $db->queryBatch();
                if ($db->getErrorNum() != 0)
                    $results = JText::_('JBS_IBM_DB_ERROR') . ': ' . $db->getErrorNum() . "<br /><font color=\"red\">";
                $results .= $db->stderr(true);
                $results .= "</font>";
                $messages[] = $results;
            }
        }

        $result = array('build' => 'ALL', 'messages' => $messages);

        return $result;
    }

}
