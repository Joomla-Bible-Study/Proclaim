<?php
/**
 * Router for Remote website that have treble with downloader.
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
$file = $_GET['file'];
$size = $_GET['size'];

// Check url for "http://" prefix, and add it if it doesn't exist

if (!preg_match('/^http(s)?:\/\//', $file))
{
	$file = 'http://' . $file;
}
$new_size = getRemoteFileSize($file);
if ($size != $new_size)
{
	$size = $new_size;
}
header("Content-Length: " . $size);
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=" . basename($file));
header("Content-Type: application/mp3");
header("Content-Transfer-Encoding: binary");
readfile($file);

/**
 * Method to get file size
 *
 * @param   string  $url  URL
 *
 * @return  boolean
 */
function getRemoteFileSize($url)
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
