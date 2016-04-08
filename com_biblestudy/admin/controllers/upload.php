<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No direct access
defined('_JEXEC') or die();

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Class BiblestudyControllerUpload
 *
 * @package  BibleStudy.Admin
 * @since    9.0.0
 */
class BiblestudyControllerUpload extends JControllerLegacy
{
	/**
	 * File upload hanlder
	 * Controller adapted from COM_MEDIAMU
	 *
	 * @return string JSON response
	 */
	public function upload()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$contentType = null;

		// 5 minutes execution time
		@set_time_limit(5 * 60);

		// Enable valid json response when debugging is disabled
		if (!COM_BIBLESTUDY_DEBUG)
		{
			error_reporting(0);
		}

		$input   = new JInput;
		$params  = JComponentHelper::getParams('com_biblestudy');
		$session = JFactory::getSession();
		$user    = JFactory::getUser();

		$cleanupTargetDir = true; /* Remove old files */
		$maxFileAge       = 5 * 3600; /* Temp file age in seconds */

		// Directory for file upload
		$targetDirBase64  = $session->get('current_dir', null, 'com_biblestudy');
		$targetDirDecoded = base64_decode($targetDirBase64);
		$targetDirWithSep = $targetDirDecoded . DIRECTORY_SEPARATOR;

		// Check for snooping
		$targetDirCleaned = JPath::check($targetDirWithSep);

		// Finally
		$targetDir = $targetDirCleaned;

		// Get parameters
		$chunk  = $input->getInt('chunk', 0);
		$chunks = $input->getInt('chunks', 0);

		// Current file name
		$fileNameFromReq = $input->getString('name', '');

		// Clean the fileName for security reasons
		$fileName = JFile::makeSafe($fileNameFromReq);

		// Check file extension
		$ext_images = $params->get('image_file_extensions', null);
		$ext_other  = $params->get('other_files_extension', null);

		// Prepare extensions for validation
		$exts     = $ext_images . ',' . $ext_other;
		$exts_lc  = strtolower($exts);
		$exts_arr = explode(',', $exts_lc);

		// Check token
		if (!$session->checkToken('request'))
		{
			$this->_setResponse(400, JText::_('JINVALID_TOKEN'));
		}

		// Check user perms
		if (!$user->authorise('core.create', 'com_biblestudy'))
		{
			$this->_setResponse(400, JText::_('JBS_ERROR_PERM_DENIDED'));
		}

		// Directory check
		if (!file_exists($targetDir) && !is_dir($targetDir) && strpos(JUri::base(), $targetDir) !== false)
		{
			$this->_setResponse(100, JText::_('JBS_ERROR_UPLOAD_INVALID_PATH'));
		}

		// File type check
		if (!in_array(JFile::getExt($fileName), $exts_arr))
		{
			$this->_setResponse(100, JText::_('JBS_ERROR_UPLOAD_INVALID_FILE_EXTENSION'));
		}

		// Make sure the fileName is unique but only if chunk is disabled
		if ($chunks < 2 && file_exists($targetDir . '/' . $fileName))
		{
			$ext        = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);

			$count = 1;
			while (file_exists($targetDir . '/' . $fileName_a . '_' . $count . $fileName_b))
			{
				$count++;
			}

			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}

		$filePath = $targetDir . '/' . $fileName;

		// Remove old temp files
		if ($cleanupTargetDir && ($dir = opendir($targetDir)))
		{
			while (($file = readdir($dir)) !== false)
			{
				$tmpfilePath = $targetDir . '/' . $file;

				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part"))
				{
					JFile::delete($tmpfilePath);
				}
			}

			closedir($dir);
		}
		else
		{
			$this->_setResponse(100, 'Failed to open temp directory.');
		}

		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
		{
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
		}

		if (isset($_SERVER["CONTENT_TYPE"]))
		{
			$contentType = $_SERVER["CONTENT_TYPE"];
		}

		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false)
		{
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name']))
			{
				// Open temp file
				$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
				if ($out)
				{
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['file']['tmp_name'], "rb");

					if ($in)
					{
						while ($buff = fread($in, 4096))
						{
							fwrite($out, $buff);
						}
					}
					else
					{
						$this->_setResponse(101, "Failed to open input stream.");
					}

					fclose($in);
					fclose($out);
					JFile::delete($_FILES['file']['tmp_name']);
				}
				else
				{
					$this->_setResponse(102, "Failed to open output stream.");
				}
			}
			else
			{
				$this->_setResponse(103, "Failed to move uploaded file");
			}
		}
		else
		{
			// Open temp file
			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");

			if ($out)
			{
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");

				if ($in)
				{
					while ($buff = fread($in, 4096))
					{
						fwrite($out, $buff);
					}
				}
				else
				{
					$this->_setResponse(101, "Failed to open input stream.");
				}

				fclose($in);
				fclose($out);
			}
			else
			{
				$this->_setResponse(102, "Failed to open output stream.");
			}
		}

		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1)
		{
			// Strip the temp .part suffix off
			@rename("{$filePath}.part", $filePath);
		}

		$this->_setResponse(0, null, false);

	}

	/**
	 * Set the JSON response and exists script
	 *
	 * @param   int     $code   Error Code
	 * @param   string  $msg    Error Message
	 * @param   bool    $error  ?
	 *
	 * @return void
	 */
	private function _setResponse($code, $msg = null, $error = true)
	{

		if ($error)
		{
			$jsonrpc = array(
				"error" => 1,
				"code"  => $code,
				"msg"   => $msg
			);
		}
		else
		{
			$jsonrpc = array(
				"error" => 0,
				"code"  => $code,
				"msg"   => "File uploaded!"
			);
		}

		die(json_encode($jsonrpc));

	}

}
