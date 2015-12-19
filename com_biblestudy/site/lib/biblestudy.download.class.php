<?php
/**
 * BibleStudy Download Class
 *
 * @package        BibleStudy.Site
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.environment.response');

/**
 * BibleStudy Download Class
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class Dump_File
{

	/**
	 * Method to send file to browser
	 *
	 * @param   int $mid ID of media
	 *
	 * @since 6.1.2
	 * @return null
	 * @throws string
	 */
	public function download($mid)
	{
		// Clears file status cache
		clearstatcache();

		$this->hitDownloads((int) $mid);
		$input    = new JInput;
		$template = $input->get('t', '1', 'int');
		$db       = JFactory::getDBO();

		// Get the template so we can find a protocol
		$query = $db->getQuery(true);
		$query->select('id, params')->from('#__bsms_templates')->where('id = ' . $template);
		$db->setQuery($query);
		$template = $db->loadObject();

		// Convert parameter fields to objects.
		$registry = new JRegistry;
		$registry->loadString($template->params);
		$params = $registry;

		$protocol = $params->get('protocol', '//');
		$query    = $db->getQuery(true);
		$query->select('#__bsms_mediafiles.*,'
			. ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
			. ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath,'
			. ' #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetype')
			->from('#__bsms_mediafiles')
			->leftJoin('#__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)')
			->leftJoin('#__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)')
			->leftJoin('#__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type)')
			->where('#__bsms_mediafiles.id = ' . $mid);
		$db->setQuery($query, 0, 1);

		$media = $db->LoadObject();
        JResponse::clearHeaders();
		$server        = $media->spath;
		$path          = $media->fpath;
		$filename      = $media->filename;
		$size          = $media->size;
		$download_file = $protocol . $server . $path . $filename;
		$mimeType      = $media->mimetype;
		/** @var $download_file object */
		$getsize = $this->getRemoteFileSize($download_file);

		if (!$size || ($size != $getsize && $getsize != false))
		{
				$size = $getsize;
		}

		// Clean the output buffer
		@ob_end_clean();

		// Test for protocol and set the appropriate headers
		jimport('joomla.environment.uri');
		$_tmp_uri      = JURI::getInstance(JURI::current());
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


		// Modified by Rene
		// HTTP Range - see RFC2616 for more information's (http://www.ietf.org/rfc/rfc2616.txt)
		$newFileSize = $size - 1;

		// Default values! Will be overridden if a valid range header field was detected!
		$resultLenght = (string) $size;
		$resultRange  = "0-" . $newFileSize;

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
					$resultRange = $resultLenght . '-' . $newFileSize;
				}

				// Ex.: 500- -> from 500 bytes to file size
				elseif (empty($httpRange[1]))
				{
					$resultRange = $httpRange[0] . '-' . $newFileSize;
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

		header('Content-Type: ' . $mimeType);
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Transfer-Encoding: binary\n');

		// Try to deliver in chunks
		@set_time_limit(0);
		$fp = @fopen($download_file, 'rb');

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
			@readfile($download_file);
		}
		flush();
		exit;
	}

	/**
	 * Method tho track Downloads
	 *
	 * @param   int $mid Media ID
	 *
	 * @return  boolean
	 * @throws  string
	 *
	 * @since   7.0.0
	 */
	protected function hitDownloads($mid)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')->set('downloads = downloads + 1')->where('id = ' . $mid);
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Method to get file size
	 *
	 * @param   object $url URL
	 *
	 * @return  boolean
	 */
	protected function getRemoteFileSize($url)
	{
		$parsed = parse_url($url);
		$host   = $parsed["host"];
		$fp     = null;

		if (function_exists('fsockopen'))
		{
			$fp = @fsockopen($host, 80, $errno, $errstr, 20);
		}
		if (!$fp)
		{
			return false;
		}
		else
		{
			@fputs($fp, "HEAD $url HTTP/1.1\r\n");
			@fputs($fp, "HOST: $host\r\n");
			@fputs($fp, "Connection: close\r\n\r\n");
			$headers = "";

			while (!@feof($fp))
			{
				$headers .= @fgets($fp, 128);
			}
		}
		@fclose($fp);
		$return      = false;
		$arr_headers = explode("\n", $headers);

		foreach ($arr_headers as $header)
		{
			$s = "Content-Length: ";

			if (substr(strtolower($header), 0, strlen($s)) == strtolower($s))
			{
				$return = trim(substr($header, strlen($s)));
				break;
			}
		}

		return $return;
	}

}
