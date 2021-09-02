<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No direct access
defined("_JEXEC") or die();

use Joomla\Event;

/**
 * Class UploadScript
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class JBSMUploadScript
{
	/**
	 * @var string
	 * @since 9.0.0
	 */
	protected string $folder = '';

	/**
	 * Upload media files
	 *
	 * @param   JInput|array  $data  Data to upload
	 *
	 * @return array|boolean
	 *
	 * @since 9.0.0
	 */
	public function upload($data)
	{
		JSession::checkToken('get') or die('Invalid Token');
		$params = JComponentHelper::getParams('com_biblestudy');

		// Get some data from the request
		$file         = $_FILES['file'];
		$this->folder = $data->get('path', '', 'path');

		// Total length of post back data in bytes.
		$contentLength = (int) $_SERVER['CONTENT_LENGTH'];

		// Instantiate the media helper
		$mediaHelper = new JHelperMedia;

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

		$file['name']     = JFile::makeSafe($file['name']);
		$file['name']     = str_replace(' ', '-', $file['name']);
		$file['filepath'] = JPath::clean(implode(DIRECTORY_SEPARATOR, array(JPATH_ROOT, $this->folder, $file['name'])));

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
		JClientHelper::setCredentialsFromRequest('ftp');
		JPluginHelper::importPlugin('content');
		$dispatcher = JEventDispatcher::getInstance();

		if (!$mediaHelper->canUpload($file, 'com_biblestudy'))
		{
			// The file can't be uploaded
			return ['data' => '', 'error' => 'The file can\'t be uploaded by types allowed'];
		}

		// Trigger the onContentBeforeSave event.
		$object_file = (object) $file;
		$result      = $dispatcher->trigger('onContentBeforeSave', array('com_biblestudy.file', &$object_file, true));

		if (in_array(false, $result, true))
		{
			// There are some errors in the plugins
			return ['data' => '', 'error' => 'Plugin errors on upload'];
		}

		if (!JFile::upload($object_file->tmp_name, $object_file->filepath))
		{
			return ['data' => '', 'error' => 'Could not upload'];
		}

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger('onContentAfterSave', array('com_biblestudy.file', &$object_file, true));

		// Return Success
		return array(
			'data' => array(
				'filename' => $object_file->filepath,
				'size'     => $_FILES['file']['size']
			)
		);
	}
}
