<?php
/**
 * BibleStudy Download Class
 *
 * @package    Proclaim.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CWMHelper;
use Joomla\CMS\Factory;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * BibleStudy Download Class
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CWMDownload
{
	/**
	 * Method to send file to browser
	 *
	 * @param   int  $mid  ID of media
	 *
	 * @since 6.1.2
	 * @return void
	 */
	public function download($mid): void
	{
		// Clears file status cache
		clearstatcache();

		$this->hitDownloads((int) $mid);
		$input    = new Input;
		$template = $input->get('t', '1', 'int');
        $db = Factory::getContainer()->get('DatabaseDriver');
        $mid = $input->get('mid', '1', 'int');

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
			. ' #__bsms_servers.id AS ssid, #__bsms_servers.params AS sparams'
		)
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

			if (isset($sparams->path))
			{
				$media->spath = $sparams->path;
			}
			else
			{
				$media->spath = '';
			}
		}

		$registry = new Registry;
		$registry->loadString($media->params);
		$params->merge($registry);

		$download_file = CWMHelper::MediaBuildUrl($media->spath, $params->get('filename'), $params, true);

		if ((int) $params->get('size', 0) === 0)
		{
			$getsize       = CWMHelper::getRemoteFileSize($download_file);
		}
		else
		{
			$getsize = $params->get('size', 0);
		}

		@set_time_limit(0);
		ignore_user_abort(false);
		ini_set('output_buffering', 0);
		ini_set('zlib.output_compression', 0);

		// Bytes per chunk (10 MB)
		$chunk = 10 * 1024 * 1024;

		$fh = @fopen($download_file, "rb");

		if (!$fh)
		{
			if (JBSMDEBUG)
			{
				echo "<pre>" . $download_file . "</pre>";
			}

			jexit("Unable to open file");
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
	protected function hitDownloads(int $mid): bool
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
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
