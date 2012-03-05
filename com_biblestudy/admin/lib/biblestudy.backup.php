<?php

/**
 * @version $Id: biblestudy.backup.php 1 $
 * @package COM_JBSMIGRATION
 * @Copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * @since 7.0.2
 * */
defined('_JEXEC') or die;

class JBSExport {

    function exportdb($run = '1') {
        $return = false;

        $localfilename = 'jbs-db-backup-' . time() . '.sql';
        $mainframe = JFactory::getApplication();
        $objects = $this->getObjects();
        foreach ($objects as $object) {
            $tables[] = $this->getExportTable($object['name']);
        }
        $export = implode('\n', $tables);

        switch ($run) {
            case 1:
                $file = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $localfilename;
                if (!JFile::write($file, $export)) {
                    return false;
                }
                if (!$downloadfile = $this->output_file($file, $localfilename, $mime_type = 'text/x-sql')) {
                    return false;
                }
                return true;
                break;

            case 2:
                $file = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $localfilename;
                if (!JFile::write($file, $export)) {
                    return false;
                }
                return true;
                break;
        }
    }

    function getExportTable($table) {
        if (!$table) {
            return false;
        }
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
        $results = $db->loadObjectList();
        if (!$results) {
            return false;
        }
        foreach ($results as $result) {
            $data = array();
            $export .= 'INSERT INTO ' . $table . ' SET ';
            foreach ($result as $key => $value) {
                $data[] = "`" . $key . "`='" . $db->getEscaped($value) . "'";
            }
            $export .= implode(',', $data);
            $export .= ";\n";
        }
        
        return $export;
    }

    function output_file($file, $name, $mime_type = '') {
        /*
          This function takes a path to a file to output ($file),
          the filename that the browser will see ($name) and
          the MIME type of the file ($mime_type, optional).

          If you want to do something on download abort/finish,
          register_shutdown_function('function_name');
         */
        if (!is_readable($file))
            die('File not found or inaccessible!');

        $size = filesize($file);
        $name = rawurldecode($name);

        /* Figure out the MIME type (if not specified) */
        $known_mime_types = array(
            "pdf" => "application/pdf",
            "txt" => "text/plain",
            "html" => "text/html",
            "htm" => "text/html",
            "exe" => "application/octet-stream",
            "zip" => "application/zip",
            "doc" => "application/msword",
            "xls" => "application/vnd.ms-excel",
            "ppt" => "application/vnd.ms-powerpoint",
            "gif" => "image/gif",
            "png" => "image/png",
            "jpeg" => "image/jpg",
            "jpg" => "image/jpg",
            "php" => "text/plain",
            "sql" => "text/x-sql"
        );

        if ($mime_type == '') {
            $file_extension = strtolower(substr(strrchr($file, "."), 1));
            if (array_key_exists($file_extension, $known_mime_types)) {
                $mime_type = $known_mime_types[$file_extension];
            } else {
                $mime_type = "application/force-download";
            };
        };

        @ob_end_clean(); //turn off output buffering to decrease cpu usage
        // required for IE, otherwise Content-Disposition may be ignored
        if (ini_get('zlib.output_compression'))
            ini_set('zlib.output_compression', 'Off');

        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header("Content-Transfer-Encoding: binary");
        header('Accept-Ranges: bytes');

        /* The three lines below basically make the
          download non-cacheable */
        header("Cache-control: private");
        header('Pragma: private');
        header("Expires: Mon, 26 Jul 2014 05:00:00 GMT");

        // multipart-download and download resuming support
        if (isset($_SERVER['HTTP_RANGE'])) {
            list($a, $range) = explode("=", $_SERVER['HTTP_RANGE'], 2);
            list($range) = explode(",", $range, 2);
            list($range, $range_end) = explode("-", $range);
            $range = intval($range);
            if (!$range_end) {
                $range_end = $size - 1;
            } else {
                $range_end = intval($range_end);
            }

            $new_length = $range_end - $range + 1;
            header("HTTP/1.1 206 Partial Content");
            header("Content-Length: $new_length");
            header("Content-Range: bytes $range-$range_end/$size");
        } else {
            $new_length = $size;
            header("Content-Length: " . $size);
        }

        /* output the file itself */
        $chunksize = 1 * (1024 * 1024); //you may want to change this
        $bytes_send = 0;
        if ($file = fopen($file, 'r')) {
            if (isset($_SERVER['HTTP_RANGE']))
                fseek($file, $range);

            while (!feof($file) &&
            (!connection_aborted()) &&
            ($bytes_send < $new_length)
            ) {
                $buffer = fread($file, $chunksize);
                print($buffer); //echo($buffer); // is also possible
                flush();
                $bytes_send += strlen($buffer);
            }
            fclose($file);
        } else
            die('Error - can not open file.');

        die();
        unlink($file);
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
            array('name' => '#__bsms_styles', 'titlefield' => '', 'assetname' => '', 'realname' => ''),
            array('name' => '#__bsms_order', 'titlefield' => '', 'assetname' => '', 'realname' => '')
        );
        return $objects;
    }

}

// end of class