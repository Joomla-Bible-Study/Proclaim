<?php

/**
 * Backup System
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * @since 7.0.2
 * */
defined('_JEXEC') or die;
require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.defines.php');
/**
 * JBS Export class
 * @package BibleStudy.Admin
 * @since 7.1.0
 */
class JBSExport {

    /**
     * Export DB//
     * @param int $run
     * @return boolean
     */
    public function exportdb($run) {
        $date = date('Y_F_j');
        $localfilename = 'jbs-db-backup_' . $date . '_' . time() . '.sql';
        $objects = $this->getObjects();
        foreach ($objects as $object) {
            $tables[] = $this->getExportTable($object['name']);
        }
        $export = implode(' ', $tables);

        switch ($run) {
            case 1:
                $file = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $localfilename;
                if (!JFile::write($file, $export)) {
                    return false;
                } else {
                    $downloadfile = $this->output_file($file, $localfilename, $mime_type = 'text/x-sql');
                    return $downloadfile;
                }
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

    /**
     * Get Export Table
     * @param string $table
     * @return boolean|string
     */
    public function getExportTable($table) {
        if (!$table) {
            return false;
        }
        /**
         * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
         */
        if (!ini_get('safe_mode')) {
            set_time_limit(300);
        }

        $data = array();
        $export = '';

        $db = JFactory::getDBO();
        //Get the prefix
        $prefix = $db->getPrefix();

        // Used for Checking file is from the correct version of biblestudy component
        $export = "\n--\n-- " . BIBLESTUDY_VERSION_UPDATEFILE . "\n--\n\n";

        //Start of Tables
        $export .= "--\n-- Table structure for table `" . $table . "`\n--\n\n";
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
                $export .= str_replace('TYPE=', 'ENGINE=', $export);
            }
        }
        $export .= "\n\n--\n-- Dumping data for table `" . $table . "`\n--\n\n";
        //Get the table rows and create insert statements from them
        $query = 'SELECT * FROM ' . $table;
        $db->setQuery($query);
        $db->query();
        $results = $db->loadObjectList();
        if ($results) {
            foreach ($results as $result) {
                $data = array();
                $export .= 'INSERT INTO ' . $table . ' SET ';
                foreach ($result as $key => $value) {
                    if ($value === NULL):
                        $data[] = "`" . $key . "`=NULL";
                    else:
                        $data[] = "`" . $key . "`='" . $db->getEscaped($value) . "'";
                    endif;
                }
                $export .= implode(',', $data);
                $export .= ";\n";
            }
        }
        $export .= "\n-- --------------------------------------------------------\n\n";
        return $export;
    }

    /**
     * File output
     * @param string $file
     * @param string $name
     * @param string $mime_type
     */
    public function output_file($file, $name, $mime_type = '') {
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

    /**
     * Get Opjects for tables
     * @return array
     */
    public function getObjects() {
        $db = JFactory::getDBO();
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();
        $prelength = strlen($prefix);
        $prefix . $bsms = 'bsms_';
        $objects = array();
        foreach ($tables as $table) {
            if (substr_count($table, $bsms)) {
                $table = substr_replace($table, '#__', 0, $prelength);
                $objects[] = array('name' => $table);
            }
        }
        return $objects;
    }

}

// end of class