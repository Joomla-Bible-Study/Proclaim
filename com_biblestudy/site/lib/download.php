<?php
/**
 * BibleStudy Download Class
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * BibleStudy Download Class
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class JBSMDownload
{

	/**
	 * Method to send file to browser
	 *
	 * @param   int  $mid  ID of media
	 *
	 * @since 6.1.2
	 * @return null
	 */
	public function download($mid)
	{
		// Clears file status cache
		clearstatcache();

		$this->hitDownloads($mid);
		$input    = new JInput;
		$template = $input->get('t', '1', 'int');
		$db       = JFactory::getDbo();

		// Get the template so we can find a protocol
		$query = $db->getQuery(true);
		$query->select('id, params')
			->from('#__bsms_templates')
			->where('id = ' . (int) $template);
		$db->setQuery($query);
		$template = $db->loadObject();

		// Convert parameter fields to objects.
		$registry = new Registry;
		$registry->loadString($template->params);
		$params = $registry;

		$query    = $db->getQuery(true);
		$query->select('#__bsms_mediafiles.*,'
			. ' #__bsms_servers.id AS ssid, #__bsms_servers.params AS sparams')
			->from('#__bsms_mediafiles')
			->leftJoin('#__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server_id)')
			->where('#__bsms_mediafiles.id = ' . (int) $mid);
		$db->setQuery($query, 0, 1);

		$media = $db->loadObject();

		if ($media)
		{
			$reg = new Registry;
			$reg->loadString($media->sparams);
			$sparams = $reg->toObject();

			if ($sparams->path)
			{
				$media->spath = $sparams->path;
			}
			else
			{
				($media->spath = '');
			}
		}

		$jweb = new JApplicationWeb;
		$jweb->clearHeaders();

		$registry = new Registry;
		$registry->loadString($media->params);
		$params->merge($registry);

		$download_file = JBSMHelper::MediaBuildUrl($media->spath, $params->get('filename'), $params, true);

		/** @var $download_file object */
		$getsize = JBSMHelper::getRemoteFileSize($download_file);

		@set_time_limit(0);
		ignore_user_abort(false);
		ini_set('output_buffering', 0);
		ini_set('zlib.output_compression', 0);

		// Bytes per chunk (10 MB)
		$chunk = 10 * 1024 * 1024;

		$fh = fopen($download_file, "rb");

		if (is_bool($fh))
		{
			echo "Unable to open file";
			fclose($fh);
			exit;
		}

		// Clean the output buffer, Added to fix ZIP file corruption
		@ob_end_clean();
		@ob_start();

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . basename($params->get('filename')) . '"');
		header('Expires: 0');
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header('Pragma: public');
		header("Content-Transfer-Encoding: binary");
		header('Content-Length: ' . $getsize);

		// Repeat reading until EOF
		while (!feof($fh))
		{
			echo fread($fh, $chunk);
			@ob_flush();
			@flush();
		}

		fclose($fh);
		exit;
	}

	/**
	 * Method tho track Downloads
	 *
	 * @param   int  $mid  Media ID
	 *
	 * @return  boolean True if hit makes it or False if failed to query
	 *
	 * @since   7.0.0
	 */
	protected function hitDownloads($mid)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('downloads = downloads + 1 ')
			->where('id = ' . $mid);
		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		return true;
	}
}
