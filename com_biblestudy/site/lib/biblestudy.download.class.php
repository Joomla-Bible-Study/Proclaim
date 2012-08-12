<?php

/**
 * BibleStudy Download Class
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.environment.response');

/**
 * BibleStudy Download Class
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class Dump_File {

    /**
     * Method to send file to browser
     *
     * @param object $mid
     * @since 6.1.2
     */
    public function download($mid) {
        // Clears file status cache
        clearstatcache();

        //@todo test if this will work.
        $Hit = '';
        $Hit = $this->hitDownloads($mid);
        $template = JRequest::getInt('t', '1', 'get');
        $db = JFactory::getDBO();
        //Get the template so we can find a protocol
        $query = 'SELECT id, params FROM #__bsms_templates WHERE `id` = ' . $template;
        $db->setQuery($query);
       // $db->query();
        $template = $db->loadObject();

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($template->params);
        $params = $registry;

        $protocol = $params->get('protocol', 'http://');
        $query = 'SELECT #__bsms_mediafiles.*,'
                . ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
                . ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath,'
                . ' #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetype'
                . ' FROM #__bsms_mediafiles'
                . ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
                . ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
                . ' LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type)'
                . ' WHERE #__bsms_mediafiles.id = ' . $mid . ' LIMIT 1';
        $db->setQuery($query);

        $media = $db->LoadObject();
        JResponse::clearHeaders();
        $server = $media->spath;
        $path = $media->fpath;
        $filename = $media->filename;
        $size = $media->size;
        $download_file = $protocol . $server . $path . $filename;
        $mimeType = '';
        $mimeType = $media->mimetype;
        $getsize = $this->getRemoteFileSize($download_file);
        if ($size === '') {
            if ($size != $getsize) {
                if ($getsize != FALSE) :
                    $size = $getsize;
                endif;
            }
        }

        // Clean the output buffer
        @ob_end_clean();

        // test for protocol and set the appropriate headers
        jimport('joomla.environment.uri');
        $_tmp_uri = JURI::getInstance(JURI::current());
        $_tmp_protocol = $_tmp_uri->getScheme();
        if ($_tmp_protocol == "https") {
            // SSL Support
            header('Cache-Control:  private, max-age=0, must-revalidate, no-store');
        } else {
            header("Cache-Control: public, must-revalidate");
            header('Cache-Control: pre-check=0, post-check=0, max-age=0');
            header('Pragma: no-cache');
            header("Expires: 0");
        } /* end if protocol https */
        header('Content-Transfer-Encoding: none');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header("Accept-Ranges:  bytes");


        // Modified by Rene
        // HTTP Range - see RFC2616 for more informations (http://www.ietf.org/rfc/rfc2616.txt)
        $httpRange = 0;
        $newFileSize = $size - 1;
        // Default values! Will be overridden if a valid range header field was detected!
        $resultLenght = (string) $size;
        $resultRange = "0-" . $newFileSize;
        // We support requests for a single range only.
        // So we check if we have a range field. If yes ensure that it is a valid one.
        // If it is not valid we ignore it and sending the whole file.
        if (isset($_SERVER['HTTP_RANGE']) && preg_match('%^bytes=\d*\-\d*$%', $_SERVER['HTTP_RANGE'])) {
            // Let's take the right side
            list($a, $httpRange) = explode('=', $_SERVER['HTTP_RANGE']);
            // and get the two values (as strings!)
            $httpRange = explode('-', $httpRange);
            // Check if we have values! If not we have nothing to do!
            if (!empty($httpRange[0]) || !empty($httpRange[1])) {
                // We need the new content length ...
                $resultLenght = $size - $httpRange[0] - $httpRange[1];
                // ... and we can add the 206 Status.
                header("HTTP/1.1 206 Partial Content");
                // Now we need the content-range, so we have to build it depending on the given range!
                // ex.: -500 -> the last 500 bytes
                if (empty($httpRange[0]))
                    $resultRange = $resultLenght . '-' . $newFileSize;
                // ex.: 500- -> from 500 bytes to filesize
                elseif (empty($httpRange[1]))
                    $resultRange = $httpRange[0] . '-' . $newFileSize;
                // ex.: 500-1000 -> from 500 to 1000 bytes
                else
                    $resultRange = $httpRange[0] . '-' . $httpRange[1];
            }
        }
        header('Content-Length: ' . $resultLenght);
        header('Content-Range: bytes ' . $resultRange . '/' . $size);

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary\n');

        // Try to deliver in chunks
        @set_time_limit(0);
        $fp = @fopen($download_file, 'rb');
        if ($fp !== false) {
            while (!feof($fp)) {
                echo fread($fp, 8192);
            }
            fclose($fp);
        } else {
            @readfile($download_file);
        }
        flush();
        exit;
    }

    /**
     * Method tho track Downloads
     *
     * @param object $mid Media ID
     * @since 7.0.0
     */
    protected function hitDownloads($mid) {
        $db = JFactory::getDBO();
        $db->setQuery('UPDATE ' . $db->nameQuote('#__bsms_mediafiles') . 'SET ' . $db->nameQuote('downloads') . ' = ' . $db->nameQuote('downloads') . ' + 1 ' . ' WHERE id = ' . $mid);
        $db->query();
        return true;
        exit;
    }

    /**
     * Method to get file size
     *
     * @param object $url
     * @return boolean
     */
    protected function getRemoteFileSize($url) {
        $parsed = parse_url($url);
        $host = $parsed["host"];
        if (function_exists('fsockopen')) {
            $fp = @fsockopen($host, 80, $errno, $errstr, 20);
        }
        if (!$fp)
            return false;
        else {
            @fputs($fp, "HEAD $url HTTP/1.1\r\n");
            @fputs($fp, "HOST: $host\r\n");
            @fputs($fp, "Connection: close\r\n\r\n");
            $headers = "";
            while (!@feof($fp))
                $headers .= @fgets($fp, 128);
        }
        @fclose($fp);
        $return = false;
        $arr_headers = explode("\n", $headers);
        foreach ($arr_headers as $header) {
            $s = "Content-Length: ";
            if (substr(strtolower($header), 0, strlen($s)) == strtolower($s)) {
                $return = trim(substr($header, strlen($s)));
                break;
            }
        }
        return $return;
    }

}