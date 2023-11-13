<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// No direct access
defined("_JEXEC") or die();

use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\Input\Input;

/**
 * Class UploadScript
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class Cwmuploadscript
{
	public $runtimeScript;
	public $runtime;

	/**
	 * @var string
	 * @since 9.0.0
	 */
	protected string $folder = '';

	/**
	 * Upload media files
	 *
	 * @param   Input|array  $data  Data to upload
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 9.0.0
	 */
	public function upload($data): array
	{
		Session::checkToken('get') or die('Invalid Token');
		$params = ComponentHelper::getParams('com_proclaim');

		// Get some data from the request
		$file         = $_FILES['file'];
		$this->folder = $data->get('path', '', 'path');

		// Total length of post back data in bytes.
		$contentLength = (int) $_SERVER['CONTENT_LENGTH'];

		// Instantiate the media helper
		$mediaHelper = new MediaHelper;

		// Maximum allowed size of post back data in MB.
		$postMaxSize = $mediaHelper->toBytes(ini_get('post_max_size'));

		// Maximum allowed size of script execution in MB.
		$memoryLimit = $mediaHelper->toBytes(ini_get('memory_limit'));

		// Check for the total size of post back data.
		if (($postMaxSize > 0 && $contentLength > $postMaxSize)
			|| ($memoryLimit != -1 && $contentLength > $memoryLimit))
		{
			return ['data' => '', 'error' => ''];
		}

		$uploadMaxSize     = $params->get('upload_maxsize', 0) * 1024 * 1024;
		$uploadMaxFileSize = $mediaHelper->toBytes(ini_get('upload_max_filesize'));

		$file['name']     = File::makeSafe($file['name']);
		$file['name']     = str_replace(' ', '-', $file['name']);
		$file['filepath'] = Path::clean(implode(DIRECTORY_SEPARATOR, array(JPATH_ROOT, $this->folder, $file['name'])));

		if (($file['error'] == 1)
			|| ($uploadMaxSize > 0 && $file['size'] > $uploadMaxSize)
			|| ($uploadMaxFileSize > 0 && $file['size'] > $uploadMaxFileSize))
		{
			// File size exceed either 'upload_max_filesize' or 'upload_maxsize'.
			return ['data' => '', 'error' => 'File size exceed either \'upload_max_filesize\' or \'upload_maxsize\''];
		}

		if (!isset($file['name']))
		{
			// No filename (after the name was cleaned by JFile::makeSafe)
			return ['data' => '', 'error' => 'No filename'];
		}

		// Set FTP credentials, if given
		ClientHelper::setCredentialsFromRequest('ftp');
		PluginHelper::importPlugin('content');
		$app = Factory::getApplication();

		if (!$mediaHelper->canUpload($file, 'com_proclaim'))
		{
			// The file can't be uploaded
			return ['data' => '', 'error' => 'The file can\'t be uploaded by types allowed'];
		}

		// Trigger the onContentBeforeSave event.
		$object_file = (object) $file;
		$result      = $app->triggerEvent('onContentBeforeSave', array('com_proclaim.file', &$object_file, true));

		if (in_array(false, $result, true))
		{
			// There are some errors in the plugins
			return ['data' => '', 'error' => 'Plugin errors on upload'];
		}

		if (!File::upload($object_file->tmp_name, $object_file->filepath))
		{
			return ['data' => '', 'error' => 'Could not upload'];
		}

		// Trigger the onContentAfterSave event.
		$app->triggerEvent('onContentAfterSave', array('com_proclaim.file', &$object_file, true));

		// Return Success
		return array(
			'data' => array(
				'filename' => $object_file->filepath,
				'size'     => $_FILES['file']['size']
			)
		);
	}

	/**
	 * Upload media files
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 9.0.0
	 */
	public function UIScript(): string
	{
		return '';
	}
}
