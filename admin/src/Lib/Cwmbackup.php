<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Lib;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;
use RuntimeException;
use ZipArchive;

/**
 * JBS Export class
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class Cwmbackup
{
    /*
    **********************************************************************
    * File handling fields
    **********************************************************************
     */

    /** @var string Absolute path to dump file; must be writable (optional; if left blank it is automatically calculated)
     *
     * @since 9.0.0
     */
    protected string $dumpFile = '';

    /** @var string Data cache, used to cache data before being written to disk
     *
     * @since 9.0.0
     */
    protected string $data_cache = '';

    /** @var string Relative path of how the file should be saved in the archive
     *
     * @since 9.0.0
     */
    protected string $saveAsName = '';

    /**
     * Export DB//
     *
     * @param   int  $run  ID
     *
     * @return bool
     *
     * @throws Exception
     * @since 9.0.0
     */
    public function exportdb(int $run): bool
    {
        $date             = date('Y_F_j');
        $site             = Uri::root();
        $this->saveAsName = strtolower(
            trim(preg_replace('#\W+#', '_', $site), '_')
        ) . '_jbs-db-backup_' . $date . '_' . time() . '.sql';
        $objects          = CwmdbHelper::getObjects();
        $config           = Factory::getApplication()->getConfig();
        $path             = $config->get('tmp_path') . '/' . $this->saveAsName;
        $path1            = '';

        foreach ($objects as $object) {
            $this->getExportTable($object['name']);
        }

        switch ($run) {
            case 1:
                $this->dumpFile = $path;

                if (!$this->writeln($this->data_cache)) {
                    return false;
                }

                $mime_type = 'text/x-sql';

                if (Factory::getApplication()->input->getInt('jbs_compress', 1)) {
                    $mime_type = 'application/zip';
                    $path = $this->compress();
                }

                $this->outputFile($path, basename($path), $mime_type);

                break;
            case 2:
                $this->dumpFile = JPATH_SITE . '/media/com_proclaim/backup/' . $this->saveAsName;

                if (!$this->writeln($this->data_cache)) {
                    return false;
                }

                if (Factory::getApplication()->input->getInt('jbs_compress', 1)) {
                    $path = $this->compress();
                }

                Factory::getApplication()->enqueueMessage('Backup File Stored at: ' . $path, 'notice');

                break;
        }

        // Clean up files for only set amount. Files to keep (Default 5)
        $this->updatefiles(Cwmparams::getAdmin()->params);

        return true;
    }

    /**
     * Function to compress a backup file.
     *
     * @return string Zip File Path
     * @since 10.0.0
     */
    private function compress(): string
    {
        $files = (array)$this->dumpFile;
        $path1 = $this->dumpFile . '.zip';
        $zip   = new \ZipArchive();

        //create the file and throw the error if unsuccessful
        if ($zip->open($path1, ZipArchive::CREATE) !== true) {
            throw new RuntimeException("cannot open <$path1>\n", 'error');
        }

        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();
        File::delete($this->dumpFile);

        return $path1;
    }

    /**
     * Get Export Table
     *
     * @param   string  $table  Table name
     *
     * @return bool
     *
     * @since 9.0.0
     */
    public function getExportTable(string $table): bool
    {
        if (!$table) {
            return false;
        }

        /**
         * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
         */
        if (\function_exists('set_time_limit')) {
            set_time_limit(ini_get('max_execution_time'));
        }

        $db = Factory::getContainer()->get('DatabaseDriver');

        // Get the prefix
        $prefix = $db->getPrefix();
        $export = '';

        // Start of Tables
        $export .= "--\n-- Table structure for table " . $db->qn($table) . "\n--\n\n";

        // Drop the existing table
        $export .= 'DROP TABLE IF EXISTS ' . $db->qn($table) . ";\n";

        // Create a new table definition based on the incoming database
        $query = 'SHOW CREATE TABLE ' . $db->qn($table);
        $db->setQuery($query);
        $table_def = $db->loadObject();

        foreach ($table_def as $value) {
            if (substr_count($value, 'CREATE')) {
                $export .= str_replace($prefix, '#__', $value) . ";\n";
                $export = str_replace('TYPE=', 'ENGINE=', $export);
            }
        }

        $export .= "\n\n--\n-- Dumping data for table " . $db->qn($table) . "\n--\n\n";

        // Get the table rows and create insert statements from them
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->qn($table));
        $db->setQuery($query);
        $results = $db->loadObjectList();

        if ($results) {
            foreach ($results as $result) {
                $data   = array();
                $export .= 'INSERT INTO ' . $db->qn($table) . ' SET ';

                foreach ($result as $key => $value) {
                    if ($value === null) {
                        $data[] = $db->qn($key) . "=NULL";
                    } else {
                        $data[] = $db->qn($key) . "=" . $db->q(trim(str_replace(array("\r\n", "\r"), "\n", $value)));
                    }
                }

                $export .= implode(',', $data);
                $export .= ";\n";
            }
        }

        $export .= "\n-- --------------------------------------------------------\n\n";

        $this->data_cache .= $export;

        return true;
    }

    /**
     * Saves the string in $fileData to the file.
     *
     * @param   string  $fileData  Data to write. Set to null to close the file handle.
     *
     * @return bool TRUE if saving to the file succeeded
     *
     * @throws Exception
     * @since 9.0.0
     */
    protected function writeln(string $fileData): bool
    {
        if (file_put_contents($this->dumpFile, $fileData, FILE_APPEND)) {
            return true;
        }

        return false;
    }

    /**
     * File output
     *
     * @param   string  $file       File Name
     * @param   string  $name       Name of File
     * @param   string  $mime_type  Meme_Type
     *
     * @return bool
     *
     * @throws Exception
     * @since 9.0.0
     */
    public function outputFile(string $file, string $name, string $mime_type = ''): bool
    {
        if (!is_readable($file)) {
            throw new RuntimeException('File not found or inaccessible!');
        }

        // Clears file status cache
        clearstatcache();

        // Turn off output buffering to decrease cpu usage
        @ob_end_clean();

        // Verify MimeType or Extract the MimeType
        $mime_type = $this->verifyMimeType($mime_type, $file);

        /**
         * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
         */
        if (\function_exists('set_time_limit')) {
            set_time_limit(ini_get('max_execution_time'));
        }
        // Decode URL-encoded strings
        $name = rawurldecode($name);

        header("Cache-Control: public, must-revalidate");
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header('Pragma: no-cache');
        header("Expires: 0");
        header('Content-Transfer-Encoding: none');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header("Accept-Ranges:  bytes");

        // Set File Size Header
        $this->fileSizeHeader($file);

        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Transfer-Encoding: binary\n');

        ob_end_flush();
        $fp = fopen($file, 'rb');

        ob_start();
        $chunkSize = 1024 * 1024;

        if ($fp !== false) {
            while (!feof($fp)) {
                $buffer = fread($fp, $chunkSize);
                // Now will push to the browser the church of data using the buffer.
                echo $buffer;
                ob_flush();
                flush();
            }
            fclose($fp);
        } else {
            @readfile($file);
            ob_flush();
            flush();
        }
        return true;
    }

    /**
     * Verify MimeType
     *
     * @param   string  $mime_type  MimeType (optional)
     * @param   string  $file       File with a full path
     *
     * @return string Return correct MimeType (ex. application/zip)
     *
     * @since 10.0.0
     * @todo may need to move this out into a helper file.
     */
    private function verifyMimeType(string $mime_type = '', string $file = ''): string
    {
        /* Figure out the MIME type (if not specified) */
        $known_mime_types = array(
            "pdf"  => "application/pdf",
            "txt"  => "text/plain",
            "html" => "text/html",
            "htm"  => "text/html",
            "exe"  => "application/octet-stream",
            "zip"  => "application/zip",
            "doc"  => "application/msword",
            "xls"  => "application/vnd.ms-excel",
            "ppt"  => "application/vnd.ms-powerpoint",
            "gif"  => "image/gif",
            "png"  => "image/png",
            "jpeg" => "image/jpg",
            "jpg"  => "image/jpg",
            "php"  => "text/plain",
            "sql"  => "text/x-sql"
        );

        if ($mime_type === '') {
            $file_extension = strtolower(substr(strrchr($file, "."), 1));

            if (array_key_exists($file_extension, $known_mime_types)) {
                return $known_mime_types[$file_extension];
            }

            return "application/force-download";
        }

        return $mime_type;
    }

    /**
     * Build File Size Header
     *
     * @param $file string File with full Path
     *
     * @since 10.0.0
     */
    private function fileSizeHeader(string $file): void
    {
        // Get File Size
        $size = filesize($file);

        // Modified by Rene
        // HTTP Range - see RFC2616 for more information's (http://www.ietf.org/rfc/rfc2616.txt)
        $newFileSize = $size - 1;

        // Default values! Will be overridden if a valid range header field was detected!
        $resultLength = (string)$size;
        $resultRange  = "0-" . $newFileSize;

        // Workaround for int overflow
        if ($size < 0) {
            $size = exec('ls -al "' . $file . '" | awk \'BEGIN {FS=" "}{print $5}\'');
        }

        /* We support requests for a single range only.
                 * So we check if we have a range field.
                 * If yes, ensure that it is valid.
                 * If it is not valid, we ignore it and send the whole file.
                 * */
        if (isset($_SERVER['HTTP_RANGE']) && preg_match('%^bytes=\d*\-\d*$%', $_SERVER['HTTP_RANGE'])) {
            // Let's take the right side
            [$a, $httpRange] = explode('=', $_SERVER['HTTP_RANGE']);

            // And get the two values (as strings!)
            $httpRange = explode('-', $httpRange);

            // Check if we have values! If not, we have nothing to do!
            if (!empty($httpRange[0]) || !empty($httpRange[1])) {
                // We need the new content length ...
                $resultLength = $size - $httpRange[0] - $httpRange[1];

                // ... and we can add the 206 Status.
                header("HTTP/1.1 206 Partial Content");

                // Now we need the content-range, so we have to build it depending on the given range!
                // ex.: -500 -> the last 500 bytes
                if (empty($httpRange[0])) {
                    $resultRange = $resultLength . '-' . $size;
                } elseif (empty($httpRange[1])) {
                    // Ex.: 500- -> from 500 bytes to file size
                    $resultRange = $httpRange[0] . '-' . $size;
                } else {
                    // Ex.: 500-1000 -> from 500 to 1000 bytes
                    $resultRange = $httpRange[0] . '-' . $httpRange[1];
                }
            }
        }

        header('Content-Length: ' . $resultLength);
        header('Content-Range: bytes ' . $resultRange . '/' . $size);
    }

    /**
     * Update files
     *
     * @param  ?Registry  $params  Proclaim Params
     *
     * @return void
     *
     * @since 7.1.0
     */
    public function updatefiles(?Registry $params): void
    {
        $path = JPATH_SITE . '/media/com_proclaim/backup';

        if (!is_dir($path)) {
            Folder::create($path);
        }

        $exclude       = array('.git', '.svn', 'CVS', '.DS_Store', '__MACOSX', '.html');
        $excludefilter = array('^\..*', '.*~');
        $files         = Folder::files($path, '.', 'false', 'true', $exclude, $excludefilter);
        arsort($files, SORT_STRING);
        $parts       = array();
        $numfiles    = count($files);
        $totalnumber = $params->get('filestokeep', '5');

        for ($counter = $numfiles; $counter > $totalnumber; $counter--) {
            $parts[] = array_pop($files);
        }

        foreach ($parts as $part) {
            File::delete($part);
        }
    }
}
