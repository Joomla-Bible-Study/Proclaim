<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/**
 * JBS Export class
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 *
 * @todo     Look like we are duplicating the Class
 */
class JBSMBackup
{
	/*
    **********************************************************************
	* File handling fields
	**********************************************************************
	 */

	/** @var string Absolute path to dump file; must be writable (optional; if left blank it is automatically calculated) */
	protected $dumpFile = '';

	/** @var string Data cache, used to cache data before being written to disk */
	protected $data_cache = '';

	/** @var resource Filepointer to the current dump part */
	private $_fp = null;

	/** @var string Absolute path to the temp file */
	protected $tempFile = '';

	/** @var string Relative path of how the file should be saved in the archive */
	protected $saveAsName = '';

	/**
	 * Export DB//
	 *
	 * @param   int $run  ID
	 *
	 * @return boolean
	 */
	public function exportdb($run)
	{
		$date             = date('Y_F_j');
		$this->saveAsName = 'jbs-db-backup_' . $date . '_' . time() . '.sql';
		$objects          = JBSMDbHelper::getObjects();
		$tables           = null;

		foreach ($objects as $object)
		{
			$this->getExportTable($object['name']);
		}

		switch ($run)
		{
			case 1:
				$this->dumpFile = JPATH_SITE . '/tmp/' . 'jbs-db-backup_' . $date . '_' . time() . '.sql';

				if (!$this->writeline($this->data_cache))
				{
					return false;
				}
				else
				{
					$this->output_file($this->dumpFile, $this->saveAsName, $mime_type = 'text/x-sql');

					return true;
				}
				break;

			case 2:
				$this->dumpFile = JPATH_SITE . '/media/com_biblestudy/database/' . $this->saveAsName;

				if (!$this->writeline($this->data_cache))
				{
					return false;
				}

				return $this->dumpFile;
				break;
		}

		return true;
	}

	/**
	 * Get Export Table
	 *
	 * @param   string $table  Table name
	 *
	 * @return boolean|string
	 */
	public function getExportTable($table)
	{
		if (!$table)
		{
			return false;
		}
		/**
		 * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
		 */
		if (!ini_get('safe_mode'))
		{
			set_time_limit(300);
		}

		$db = JFactory::getDBO();

		// Get the prefix
		$prefix = $db->getPrefix();

		// Used for Checking file is from the correct version of biblestudy component
		$export = "\n--\n-- " . BIBLESTUDY_VERSION_UPDATEFILE . "\n--\n\n";

		// Start of Tables
		$export .= "--\n-- Table structure for table " . $db->qn($table) . "\n--\n\n";

		// Drop the existing table
		$export .= 'DROP TABLE IF EXISTS ' . $db->qn($table) . ";\n";

		// Create a new table defintion based on the incoming database
		$query = 'SHOW CREATE TABLE ' . $db->qn($table);
		$db->setQuery($query);
		$table_def = $db->loadObject();

		foreach ($table_def as $value)
		{
			if (substr_count($value, 'CREATE'))
			{
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

		if ($results)
		{
			foreach ($results as $result)
			{
				$data = array();
				$export .= 'INSERT INTO ' . $db->qn($table) . ' SET ';

				foreach ($result as $key => $value)
				{
					if ($value === null)
					{
						$data[] = $db->qn($key) . "=NULL";
					}
					else
					{
						$data[] = $db->qn($key) . "=" . $db->q($db->escape($value));
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
	 * Saves the string in $fileData to the file $backupfile. Returns TRUE. If saving
	 * failed, return value is FALSE.
	 *
	 * @param   string $fileData  Data to write. Set to null to close the file handle.
	 *
	 * @return boolean TRUE is saving to the file succeeded
	 */
	protected function writeline(&$fileData)
	{
		$app = JFactory::getApplication();

		if (!$this->_fp)
		{
			$this->_fp = @fopen($this->dumpFile, 'a');

			if ($this->_fp === false)
			{
				$app->enqueueMessage('Could not open ' . $this->dumpFile . ' for append, in DB dump.', 'error');

				return false;
			}
		}

		if (is_null($fileData))
		{
			if (is_resource($this->_fp)) @fclose($this->_fp);
			$this->_fp = null;

			return true;
		}
		else
		{
			if ($this->_fp)
			{
				$ret = fwrite($this->_fp, $fileData);
				@clearstatcache();

				// Make sure that all data was written to disk
				return ($ret == strlen($fileData));
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * File output
	 *
	 * @param   string $file       File Name
	 * @param   string $name       Name output
	 * @param   string $mime_type  Meme_Type
	 *
	 * @return void
	 */
	public function output_file($file, $name, $mime_type = '')
	{
		// Disable caching
		header("Pragma: public");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private");

		// Turn off output buffering to decrease cpu usage
		@ob_end_clean();

		// Disable execution time limit
		set_time_limit(0);

		// Required for IE, otherwise Content-Disposition may be ignored
		if (ini_get('zlib.output_compression'))
		{
			ini_set('zlib.output_compression', 'Off');
		}

		if (!is_readable($file))
		{
			die('File not found or inaccessible!');
		}

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

		if ($mime_type == '')
		{
			$file_extension = strtolower(substr(strrchr($file, "."), 1));

			if (array_key_exists($file_extension, $known_mime_types))
			{
				$mime_type = $known_mime_types[$file_extension];
			}
			else
			{
				$mime_type = "application/force-download";
			}
		}

		$name = rawurldecode($name);

		// File specific headers
		header('Accept-Ranges: bytes');
		header("Content-Description: File Transfer");
		header("Content-Type: $mime_type");
		header('Content-Disposition: attachment; filename="' . $name . '"');
		header("Content-Transfer-Encoding: binary");

		$size = filesize($file);

		// Workaround for int overflow
		if ($size < 0)
		{
			$size = exec('ls -al "' . $file . '" | awk \'BEGIN {FS=" "}{print $5}\'');
		}

		// Multipart-download and download resuming support
		if (isset($_SERVER['HTTP_RANGE']))
		{
			list($a, $range) = explode("=", $_SERVER['HTTP_RANGE'], 2);
			list($range) = explode(",", $range, 2);
			list($range, $range_end) = explode("=", $range);
			$range = round(floatval($range), 0);

			if (!$range_end)
			{
				$range_end = $size - 1;
			}
			else
			{
				$range_end = round(floatval($range_end), 0);
			}

			$partial_length = $range_end - $range + 1;
			header("HTTP/1.1 206 Partial Content");
			header("Content-Length: $partial_length");
			header("Content-Range: bytes $range-$range_end/$size");
		}
		else
		{
			$partial_length = $size;
			header("Content-Length: $partial_length");
		}

		/* output the file itself */
		// You may want to change this
		$chunksize  = 1 * (1024 * 1024);
		$bytes_sent = 0;
		$fp         = fopen($file, 'r');

		if ($fp)
		{
			// Fast forward within file, if requested
			if (isset($_SERVER['HTTP_RANGE']))
			{
				fseek($fp, $range);
			}

			// Read and output the file in chunks
			while (!feof($fp) AND (!connection_aborted()) AND ($bytes_sent < $partial_length))
			{
				$buffer = fread($fp, $chunksize);

				// Is also possible
				print($buffer);
				flush();
				$bytes_sent += strlen($buffer);
			}
			fclose($fp);
		}
		else
		{
			die('Unable to open file.');
		}

		// Must have die() in to return proper file.
		die();
	}

}
