<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
defined('_JEXEC') or die;

/**
 * JBS Export class
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */
class JBSMBackup
{
	/*
    **********************************************************************
	* File handling fields
	**********************************************************************
	 */

	/** @var string Absolute path to dump file; must be writable (optional; if left blank it is automatically calculated)
	 *
	 * @since 9.0.0 */
	protected $dumpFile = '';

	/** @var string Data cache, used to cache data before being written to disk
	 *
	 * @since 9.0.0 */
	protected $data_cache = '';

	/** @var string Absolute path to the temp file
	 *
	 * @since 9.0.0
	 * */
	protected $tempFile = '';

	/** @var string Relative path of how the file should be saved in the archive
	 *
	 * @since 9.0.0
	 */
	protected $saveAsName = '';

	/** @var resource Filepointer to the current dump part
	 *
	 * @since 9.0.0 */
	private $fp = null;

	/**
	 * Export DB//
	 *
	 * @param   int  $run  ID
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	public function exportdb($run)
	{
		$date             = date('Y_F_j');
		$site             = JUri::root(true);
		$this->saveAsName = $site . '_jbs-db-backup_' . $date . '_' . time() . '.sql';
		$objects          = JBSMDbHelper::getObjects();
		$tables           = null;
		$config           = JFactory::getConfig();
		$path             = $config->get('tmp_path') . $this->saveAsName;
		$path1            = '';

		foreach ($objects as $object)
		{
			$this->getExportTable($object['name']);
		}

		switch ($run)
		{
			case 1:
				$this->dumpFile = $path;

				if (!$this->writeline($this->data_cache))
				{
					return false;
				}
				else
				{
					$mime_type = 'text/x-sql';

					if (JFactory::getApplication()->input->getInt('jbs_compress', 1))
					{
						$mime_type = 'application/zip';
						$files = (array) $this->dumpFile;
						$path1 = $path . '.zip';
						$zip = new ZipArchive;
						$zip->open($path1, ZipArchive::CREATE);

						foreach ($files as $file)
						{
							$zip->addFile($file, basename($file));
						}

						$zip->close();
						JFile::delete($path);
					}

					$this->output_file($path1, basename($path1), $mime_type);

					return true;
				}
				break;

			case 2:
				$this->dumpFile = JPATH_SITE . '/media/com_biblestudy/backup' . $this->saveAsName;

				if (!$this->writeline($this->data_cache))
				{
					return false;
				}

				if (JFactory::getApplication()->input->getInt('jbs_compress', 1))
				{
					$files = (array) $this->dumpFile;
					$path1 = $this->dumpFile . '.zip';
					$zip = new ZipArchive;
					$zip->open($path1, ZipArchive::CREATE);

					foreach ($files as $file)
					{
						$zip->addFile($file, basename($file));
					}

					$zip->close();
					JFile::delete($this->dumpFile);
				}

				JFactory::getApplication()
					->enqueueMessage('Backup File Stored at: ' . $path1, 'notice');

				return true;
				break;
		}

		return true;
	}

	/**
	 * Get Export Table
	 *
	 * @param   string  $table  Table name
	 *
	 * @return boolean|string
	 *
	 * @since 9.0.0
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
			set_time_limit(3000);
		}

		$db = JFactory::getDbo();

		// Get the prefix
		$prefix = $db->getPrefix();
		$export = '';

		// Used for Checking file is from the correct version of biblestudy component
		if (strpos($table, 'bsms_admin'))
		{
			$export .= "\n--\n-- " . BIBLESTUDY_VERSION_UPDATEFILE . "\n--\n\n";
		}

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
	 * Saves the string in $fileData to the file $backupfile. Returns TRUE. If saving
	 * failed, return value is FALSE.
	 *
	 * @param   string  &$fileData  Data to write. Set to null to close the file handle.
	 *
	 * @return boolean TRUE is saving to the file succeeded
	 *
	 * @since 9.0.0
	 */
	protected function writeline(&$fileData)
	{
		$app = JFactory::getApplication();

		if (!$this->fp)
		{
			$this->fp = @fopen($this->dumpFile, 'a');

			if ($this->fp === false)
			{
				$app->enqueueMessage('Could not open ' . $this->dumpFile . ' for append, in DB dump.', 'error');

				return false;
			}
		}

		if (is_null($fileData))
		{
			if (is_resource($this->fp))
			{
				@fclose($this->fp);
			}

			$this->fp = null;

			return true;
		}
		else
		{
			if ($this->fp)
			{
				$ret = fwrite($this->fp, $fileData);
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
	 * @param   string  $file       File Name
	 * @param   string  $name       Name output
	 * @param   string  $mime_type  Meme_Type
	 *
	 * @return void
	 *
	 * @since 9.0.0
	 */
	public function output_file($file, $name, $mime_type = '')
	{
		// Clears file status cache
		clearstatcache();
		$jweb = new JApplicationWeb;
		$jweb->clearHeaders();

		// Turn off output buffering to decrease cpu usage
		@ob_end_clean();

		/**
		 * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
		 */
		if (!ini_get('safe_mode'))
		{
			set_time_limit(3000);
		}

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

		// Test for protocol and set the appropriate headers
		jimport('joomla.environment.uri');
		$_tmp_uri      = JUri::getInstance(JUri::current());
		$_tmp_protocol = $_tmp_uri->getScheme();

		if ($_tmp_protocol == "https")
		{
			// SSL Support
			header('Cache-Control:  private, max-age=0, must-revalidate, no-store');
		}
		else
		{
			header("Cache-Control: public, must-revalidate");
			header('Cache-Control: pre-check=0, post-check=0, max-age=0');
			header('Pragma: no-cache');
			header("Expires: 0");
		} /* end if protocol https */
		header('Content-Transfer-Encoding: none');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header("Accept-Ranges:  bytes");

		$size = filesize($file);

		// Modified by Rene
		// HTTP Range - see RFC2616 for more information's (http://www.ietf.org/rfc/rfc2616.txt)
		$newFileSize = $size - 1;

		// Default values! Will be overridden if a valid range header field was detected!
		$resultLenght = (string) $size;
		$resultRange  = "0-" . $newFileSize;

		// Workaround for int overflow
		if ($size < 0)
		{
			$size = exec('ls -al "' . $file . '" | awk \'BEGIN {FS=" "}{print $5}\'');
		}

		/* We support requests for a single range only.
				 * So we check if we have a range field. If yes ensure that it is a valid one.
				 * If it is not valid we ignore it and sending the whole file.
				 * */
		if (isset($_SERVER['HTTP_RANGE']) && preg_match('%^bytes=\d*\-\d*$%', $_SERVER['HTTP_RANGE']))
		{
			// Let's take the right side
			list($a, $httpRange) = explode('=', $_SERVER['HTTP_RANGE']);

			// And get the two values (as strings!)
			$httpRange = explode('-', $httpRange);

			// Check if we have values! If not we have nothing to do!
			if (!empty($httpRange[0]) || !empty($httpRange[1]))
			{
				// We need the new content length ...
				$resultLenght = $size - $httpRange[0] - $httpRange[1];

				// ... and we can add the 206 Status.
				header("HTTP/1.1 206 Partial Content");

				// Now we need the content-range, so we have to build it depending on the given range!
				// ex.: -500 -> the last 500 bytes
				if (empty($httpRange[0]))
				{
					$resultRange = $resultLenght . '-' . $size;
				}

				// Ex.: 500- -> from 500 bytes to file size
				elseif (empty($httpRange[1]))
				{
					$resultRange = $httpRange[0] . '-' . $size;
				}

				// Ex.: 500-1000 -> from 500 to 1000 bytes
				else
				{
					$resultRange = $httpRange[0] . '-' . $httpRange[1];
				}
			}
		}

		header('Content-Length: ' . $resultLenght);
		header('Content-Range: bytes ' . $resultRange . '/' . $size);

		header('Content-Type: ' . $mime_type);
		header('Content-Disposition: attachment; filename="' . $name . '"');
		header('Content-Transfer-Encoding: binary\n');

		// Try to deliver in chunks
		@set_time_limit(0);
		$fp = @fopen($file, 'rb');

		if ($fp !== false)
		{
			while (!feof($fp))
			{
				echo fread($fp, 8192);
			}

			fclose($fp);
		}
		else
		{
			@readfile($file);
		}

		flush();
		exit;
	}
}
