<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Controller for a MediaFile
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerMediafile extends JControllerForm
{

	/**
	 * Class constructor.
	 *
	 * @param   array $config  A named array of configuration variables.
	 *
	 * @since    7.0.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * New File Size System Should work on all server now.
	 *
	 * @param   string $url  URL
	 *
	 * @return boolean
	 *
	 * @since 7.1.0
	 */
	public function getSizeFile($url)
	{
		$head  = "";
		$url_p = parse_url($url);
		$host  = $url_p["host"];

		if (!preg_match("/[0-9]*\.[0-9]*\.[0-9]*\.[0-9]*/", $host))
		{
			// A domain name was given, not an IP
			$ip = gethostbyname($host);

			if (!preg_match("/[0-9]*\.[0-9]*\.[0-9]*\.[0-9]*/", $ip))
			{
				// Domain could not be resolved
				return -1;
			}
		}
		$port = intval($url_p["port"]);

		if (!$port)
		{
			$port = 80;
		}
		$path = $url_p["path"];

		$fp = fsockopen($host, $port, $errno, $errstr, 20);

		if (!$fp)
		{
			return false;
		}
		else
		{
			fputs($fp, "HEAD " . $url . " HTTP/1.1\r\n");
			fputs($fp, "HOST: " . $host . "\r\n");
			fputs($fp, "User-Agent: //www.example.com/my_application\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			$headers = "";

			while (!feof($fp))
			{
				$headers .= fgets($fp, 128);
			}
		}
		fclose($fp);
		$return      = -2;
		$arr_headers = explode("\n", $headers);

		foreach ($arr_headers as $header)
		{
			$s1 = "HTTP/1.1";
			$s2 = "Content-Length: ";
			$s3 = "Location: ";

			if (substr(strtolower($header), 0, strlen($s1)) == strtolower($s1))
			{
				$status = substr($header, strlen($s1));
			}
			if (substr(strtolower($header), 0, strlen($s2)) == strtolower($s2))
			{
				$size = substr($header, strlen($s2));
			}
			if (substr(strtolower($header), 0, strlen($s3)) == strtolower($s3))
			{
				$newurl = substr($header, strlen($s3));
			}
		}
		if (intval($size) > 0)
		{
			$return = strval($size);
		}
		else
		{
			$return = $status;
		}
		if (intval($status) == 302 && strlen($newurl) > 0)
		{
			// 302 redirect: get HTTP HEAD of new URL
			$return = getSizeFile($newurl);
		}

		return $return;
	}

}
