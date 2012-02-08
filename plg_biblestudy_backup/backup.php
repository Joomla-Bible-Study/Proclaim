<?php

/**
 * @version $Id: backup.php 1 $
 * @package PLG_JBSBACKUP
 * @Copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * @since 7.0.2
 * */
defined('_JEXEC') or die;

class JBSExport {

    function exportdb() {
        jimport('joomla.filesystem.folder');
        //$return = false;
        $localfilename = 'jbs-db-backup-' . time() . '.sql';
        $objects = $this->getObjects();
        foreach ($objects as $object) {
            $tables[] = $this->getExportTable($object['name']);
        }
        $export = implode('\n', $tables);

        jimport('joomla.filesystem.file');
        $file = JPATH_SITE . DIRECTORY_SEPARATOR . 'media'. DIRECTORY_SEPARATOR . 'com_biblestudy'. DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $localfilename;
        JFile::write($file, $export);
        if (!JFile::exist($file))
        {
            JError::raiseError(500, 'There was a problem with the ');
			return false;
        }
        $returnfile = array('serverfile' => $file, 'localfilename' => $localfilename);
        return $returnfile;
    }

    function getExportTable($table) {
        if (!$table){return false;}
        @set_time_limit(300);

        $data = array();
        $export = '';
        $return = array();

        $db = JFactory::getDBO();
        //Get the prefix
        $prefix = $db->getPrefix();
        $export = "--\n-- Table structure for table `" . $table . "`\n--\n\n";
        //Drop the existing table
        $export .= 'DROP TABLE IF EXISTS `' . $table . "`;\n";
        //Create a new table defintion based on the incoming database
        $query = 'SHOW CREATE TABLE `' . $table . '`';
        $db->setQuery($query);
        $db->query();
        $table_def = $db->loadObject();
        foreach ($table_def as $key => $value) {
            if (substr_count($value, 'CREATE')) {
                $export .= str_replace($prefix, '#__', $value) . ";\n";
                $export = str_replace('TYPE=', 'ENGINE=', $export);
            }
        }
        $export .= "\n\n--\n-- Dumping data for table `" . $table . "`\n--\n\n";
        //Get the table rows and create insert statements from them
        $query = 'SELECT * FROM ' . $table;
        $db->setQuery($query);
        $db->query();
        $results = $db->loadObjectList(); if (!$results){return false;}
        foreach ($results as $result) {
            $data = array();
            $export .= 'INSERT INTO ' . $table . ' SET ';
            foreach ($result as $key => $value) {
                $data[] = "`" . $key . "`='" . $db->getEscaped($value) . "'";
            }
            $export .= implode(',', $data);
            $export .= ";\n";
        }
        $export .= "\n-- --------------------------------------------------------\n\n";

        return $export;
    }

    function getObjects() {
        $objects = array(array('name' => '#__bsms_servers', 'titlefield' => 'server_name', 'assetname' => 'serversedit', 'realname' => 'JBS_CMN_SERVERS'),
            array('name' => '#__bsms_folders', 'titlefield' => 'foldername', 'assetname' => 'foldersedit', 'realname' => 'JBS_CMN_FOLDERS'),
            array('name' => '#__bsms_studies', 'titlefield' => 'studytitle', 'assetname' => 'studiesedit', 'realname' => 'JBS_CMN_STUDIES'),
            array('name' => '#__bsms_comments', 'titlefield' => 'comment_date', 'assetname' => 'commentsedit', 'realname' => 'JBS_CMN_COMMENTS'),
            array('name' => '#__bsms_locations', 'titlefield' => 'location_text', 'assetname' => 'locationsedit', 'realname' => 'JBS_CMN_LOCATIONS'),
            array('name' => '#__bsms_media', 'titlefield' => 'media_text', 'assetname' => 'mediaedit', 'realname' => 'JBS_CMN_MEDIAIMAGES'),
            array('name' => '#__bsms_mediafiles', 'titlefield' => 'filename', 'assetname' => 'mediafilesedit', 'realname' => 'JBS_CMN_MEDIA_FILES'),
            array('name' => '#__bsms_message_type', 'titlefield' => 'message_type', 'assetname' => 'messagetypeedit', 'realname' => 'JBS_CMN_MESSAGE_TYPES'),
            array('name' => '#__bsms_mimetype', 'titlefield' => 'mimetext', 'assetname' => 'mimetypeedit', 'realname' => 'JBS_CMN_MIME_TYPES'),
            array('name' => '#__bsms_podcast', 'titlefield' => 'title', 'assetname' => 'podcastedit', 'realname' => 'JBS_CMN_PODCASTS'),
            array('name' => '#__bsms_series', 'titlefield' => 'series_text', 'assetname' => 'seriesedit', 'realname' => 'JBS_CMN_SERIES'),
            array('name' => '#__bsms_share', 'titlefield' => 'name', 'assetname' => 'shareedit', 'realname' => 'JBS_CMN_SOCIAL_NETWORKING_LINKS'),
            array('name' => '#__bsms_teachers', 'titlefield' => 'teachername', 'assetname' => 'teacheredit', 'realname' => 'JBS_CMN_TEACHERS'),
            array('name' => '#__bsms_templates', 'titlefield' => 'title', 'assetname' => 'templateedit', 'realname' => 'JBS_CMN_TEMPLATES'),
            array('name' => '#__bsms_topics', 'titlefield' => 'topic_text', 'assetname' => 'topicsedit', 'realname' => 'JBS_CMN_TOPICS'),
            array('name' => '#__bsms_admin', 'titlefield' => 'id', 'assetname' => 'admin', 'realname' => 'JBS_CMN_ADMINISTRATION'),
            array('name' => '#__bsms_studytopics', 'titlefield' => '', 'assetname' => '', 'realname' => ''),
            array('name' => '#__bsms_timeset', 'titlefield' => '', 'assetname' => '', 'realname' => ''),
            array('name' => '#__bsms_search', 'titlefield' => '', 'assetname' => '', 'realname' => ''),
            array('name' => '#__bsms_books', 'titlefield' => '', 'assetname' => '', 'realname' => ''),
            array('name' => '#__bsms_update', 'titlefield' => '', 'assetname' => '', 'realname' => ''),
            array('name' => '#__bsms_order', 'titlefield' => '', 'assetname' => '', 'realname' => '')
        );
        return $objects;
    }


}

// end of class