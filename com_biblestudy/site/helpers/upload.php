<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * JBS Upload class
 *
 * @package  BibleStudy.Site
 * @since    7.1.0
 *
 * @deprecate 9.0.0 No longer used.
 */
class JBSMUpload
{
	/**
	 * Method to get temp file name from database
	 *
	 * @return    string
	 *
	 * @since     7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function gettempfile()
	{
		$input = new JInput;
		$temp  = $input->get('flupfile', '', 'string');

		return $temp;
	}

	/**
	 * Method to get path variable
	 *
	 * @param   array   $url       message details.
	 * @param   string  $tempfile  Temp file path.
	 * @param   string  $front     Front info
	 *
	 * @return    string
	 *
	 * @since 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function getpath($url, $tempfile, $front = '')
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.controller.legacy');
		$jclagacy = new JControllerLegacy;
		$input    = new JInput;
		$path     = $input->get('upload_folder', '', 'int');
		$server   = $input->get('upload_server', '', 'int');

		if ($server == '')
		{
			if ($tempfile)
			{
				JFile::delete($tempfile);
			}

			$msg = JText::_('JBS_MED_UPLOAD_FAILED_NO_FOLDER');

			if ($front)
			{
				$jclagacy->setRedirect($url . $msg);
			}
			else
			{
				$jclagacy->setRedirect($url, $msg);
			}
		}

		$returnpath = $server . $path;

		return $returnpath;
	}

	/**
	 * Method to delete temp file
	 *
	 * @param   string  $tempfile  Temp file path.
	 *
	 * @return    boolean
	 *
	 * @since 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function deletetempfile($tempfile)
	{
		jimport('joomla.filesystem.file');

		// Delete file
		JFile::delete($tempfile);

		return true;
	}

	/**
	 * Method to check upload file to see if it is allowed
	 *
	 * @param   array  $file  File info
	 *
	 * @return    boolean
	 *
	 * @since 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function checkfile($file)
	{
		$allow     = true;
		$blacklist = array(".php", ".phtml", ".php3", ".php4", ".js", ".shtml", ".pl", ".py");

		foreach ($blacklist as $ext)
		{
			if (preg_match("/$ext\$/i", $file['name']))
			{
				$allow = false;
			}
		}

		return $allow;
	}

	/**
	 * Method to process flash uploaded file
	 *
	 * @param   string  $tempfile  tempfile location
	 * @param   object  $filename  File info
	 *
	 * @return    string
	 *
	 * @sicne 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function processflashfile($tempfile, $filename)
	{
		jimport('joomla.filesystem.file');
		$uploadmsg = '';

		if ($filename->type == 1)
		{
			$uploadmsg = JText::_('JBS_MED_UPLOAD_FAILED_NOT_UPLOAD_THIS_FOLDER');
		}
		elseif ($filename->type == 2)
		{
			if (!$copy = self::ftp($tempfile, $filename, 1))
			{
				$uploadmsg = JText::_('JBS_MED_FILE_NO_UPLOADED_FTP');
			}
		}
		elseif ($filename->type == 3)
		{
			if (!$copy = self::aws($tempfile, $filename, 1))
			{
				$uploadmsg = JText::_('JBS_MED_FILE_NO_UPLOADED_AWS');
			}
		}
		else
		{
			if (!$copy = JFile::copy($tempfile, $filename->path))
			{
				$uploadmsg = JText::_('JBS_MED_FILE_NO_UPLOADED');
			}
		}

		return $uploadmsg;
	}

	/**
	 * Method to upload the file over ftp
	 *
	 * @param   array   $file      Source File details.
	 * @param   object  $filename  Destination file details.
	 * @param   int     $admin     Sets whether call is from Joomla admin or site.
	 *
	 * @return  boolean
	 *
	 * @since 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function ftp($file, $filename, $admin = 0)
	{
		$app         = JFactory::getApplication();
		$ftpsuccess  = true;
		$ftpsuccess1 = true;
		$ftpsuccess2 = true;
		$ftpsuccess3 = true;
		$ftpsuccess4 = true;

		// FTP access parameters
		$host = $filename->ftphost;
		$usr  = $filename->ftpuser;
		$pwd  = $filename->ftppassword;
		$port = $filename->ftpport;

		// File to move:
		$local_file = $file;
		$ftp_path   = $filename->path;

		// Connect to FTP server (port 21)
		if (!$conn_id = ftp_connect($host, $port))
		{
			if ($admin == 0)
			{
				$app->enqueueMessage(JText::_('JBS_MED_FTP_NO_CONNECT'), 'error');
			}

			$ftpsuccess1 = false;
		}

		// Send access parameters
		if (!ftp_login($conn_id, $usr, $pwd))
		{
			if ($admin == 0)
			{
				$app->enqueueMessage(JText::_('JBS_MED_FTP_NO_LOGIN'), 'error');
			}

			$ftpsuccess2 = false;
		}

		/* Turn on passive mode transfers (some servers need this)
		 ftp_pasv ($conn_id, true);
		 perform file upload */
		if (!$upload = ftp_put($conn_id, $ftp_path, $local_file, FTP_BINARY))
		{
			$stop = 'stopped at ftp_put';

			if ($admin == 0)
			{
				$app->enqueueMessage(JText::_('JBS_MED_FTP_NO_UPLOAD'), 'error');
			}

			$ftpsuccess3 = false;
		}

		// Chmod the file (just as example)

		// Try to chmod the new file to 666 (writable)
		if (ftp_chmod($conn_id, 0755, $ftp_path) == false)
		{
			if ($admin == 0)
			{
				$app->enqueueMessage(JText::_('JBS_MED_FTP_NO_CHMOD'), 'error');
			}

			$ftpsuccess4 = false;
		}

		// Close the FTP stream
		ftp_close($conn_id);

		if (!$ftpsuccess1 || !$ftpsuccess2 || !$ftpsuccess3 || !$ftpsuccess4)
		{
			$ftpsuccess = false;
		}

		return $ftpsuccess;
	}

	/**
	 * AWS
	 *
	 * @param   string  $file      File
	 * @param   object  $filename  FileName
	 * @param   int     $admin     Admin
	 *
	 * @return boolean
	 *
	 * @since 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function aws($file, $filename, $admin = 0)
	{
		$app         = JFactory::getApplication();
		$awssuccess  = true;
		$awssuccess5 = true;
		$awssuccess1 = true;
		$awssuccess2 = true;
		$awssuccess3 = true;
		$awssuccess4 = true;
		$aws_key     = $filename->aws_key;
		$aws_secret  = $filename->aws_secret;
		$file_type   = null;

		// File to upload to S3
		$source_file = $file;

		jimport('joomla.filesystem.file');
		$ext = JFile::getExt($filename->file);

		if ($ext == 'jpg')
		{
			$file_type = 'image/jpg';
		}
		elseif ($ext == 'png')
		{
			$file_type = 'image/png';
		}
		elseif ($ext == 'gif')
		{
			$file_type = 'image/gif';
		}
		else
		{
			if (!$file_type)
			{
				$file_type = 'binary/octet-stream';
			}
		}

		// AWS bucket
		$aws_bucket = $filename->aws_bucket;

		// AWS object name (file name)
		$aws_object = $filename->file;

		if (strlen($aws_secret) != 40)
		{
			if ($admin == 0)
			{
				$app->enqueueMessage(JText::_('JBS_MED_AWS_SECRET_WRONG_LENGTH'), 'error');
			}

			$awssuccess1 = false;
		}
		else
		{
			$file_data = file_get_contents($source_file);

			if ($file_data == false)
			{
				if ($admin == 0)
				{
					$app->enqueueMessage(JText::_('JBS_MED_AWS_FAILED_READ_FILE'), 'error');
				}

				$awssuccess2 = false;
			}
			else
			{
				// Opening HTTP connection to Amazon S3
				$fp = fsockopen("s3.amazonaws.com", 80, $errno, $errstr, 30);

				if (!$fp)
				{
					if ($admin == 0)
					{
						$app->enqueueMessage(JText::_('JBW_MED_AWS_NOT_OPEN_SOCKET'), 'error');
					}

					$awssuccess3 = false;
				}
				else
				{
					// Creating or updating bucket

					// GMT based timestamp
					$dt = gmdate('r');

					// Preparing String to Sign    (see AWS S3 Developer Guide)
					$string2sign = "PUT


        {$dt}
        /{$aws_bucket}";

					// Preparing HTTP PUT query
					$query = "PUT /{$aws_bucket} HTTP/1.1
        Host: s3.amazonaws.com
        Connection: keep-alive
        Date: $dt
        Authorization: AWS {$aws_key}:" . self::amazon_hmac($string2sign, $aws_secret) . "\n\n";

					$resp = self::sendREST($fp, $query);

					if (strpos($resp, '<Error>') !== false)
					{
						if ($admin == 0)
						{
							$app->enqueueMessage(JText::_('JBS_MED_AWS_CANNOT_CREATE_BUCKET'), 'error');
						}

						$awssuccess4 = false;
					}
					else
					{
						// Done, Uploading object
						$file_length = strlen($file_data); // for Content-Length HTTP field

						// GMT based timestamp
						$dt = gmdate('r');

						// Preparing String to Sign    (see AWS S3 Developer Guide)
						$string2sign = "PUT

        {$file_type}
        {$dt}
        x-amz-acl:public-read
        /{$aws_bucket}/{$aws_object}";

						// Preparing HTTP PUT query
						$query = "PUT /{$aws_bucket}/{$aws_object} HTTP/1.1
        Host: s3.amazonaws.com
        x-amz-acl: public-read
        Connection: keep-alive
        Content-Type: {$file_type}
        Content-Length: {$file_length}
        Date: $dt
        Authorization: AWS {$aws_key}:" . self::amazon_hmac($string2sign, $aws_secret) . "\n\n";
						$query .= $file_data;

						$resp = self::sendREST($fp, $query);

						if (strpos($resp, '<Error>') !== false)
						{
							if ($admin == 0)
							{
								$app->enqueueMessage(JText::_('JBS_MED_AWS_CANNOT_CREATE_FILE'), 'error');
							}

							$awssuccess5 = false;
						}

						fclose($fp);
					}
				}
			}
		}

		if (!$awssuccess1 || !$awssuccess2 || !$awssuccess3 || !$awssuccess4 || !$awssuccess5)
		{
			$awssuccess = false;
		}

		return $awssuccess;
	}

	/**
	 * Amazon HMAC
	 *
	 * @param   string  $stringToSign  Sign
	 * @param   string  $aws_secret    Secret
	 *
	 * @return string
	 *
	 * @since 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function amazon_hmac($stringToSign, $aws_secret)
	{
		if (strlen($aws_secret) == 40)
		{
			$aws_secret = $aws_secret . str_repeat(chr(0), 24);
		}

		$ipad = str_repeat(chr(0x36), 64);
		$opad = str_repeat(chr(0x5c), 64);

		$hmac = self::binsha1(($aws_secret ^ $opad) . self::binsha1(($aws_secret ^ $ipad) . $stringToSign));

		return base64_encode($hmac);
	}

	/**
	 * BinSha1
	 *
	 * @param   string  $d  String to hash
	 *
	 * @return string
	 *
	 * @since 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function binsha1($d)
	{
		if (version_compare(phpversion(), "5.0.0", ">="))
		{
			return sha1($d, true);
		}
		else
		{
			return pack('H*', sha1($d));
		}
	}

	/**
	 * Send Rest
	 *
	 * @param   resource  $fp     ?
	 * @param   string    $q      ?
	 * @param   boolean   $debug  debug
	 *
	 * @return string
	 *
	 * @since 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function sendREST($fp, $q, $debug = false)
	{
		if ($debug)
		{
			echo "\nQUERY<<{$q}>>\n";
		}

		fwrite($fp, $q);
		$r            = '';
		$check_header = true;

		while (!feof($fp))
		{
			$tr = fgets($fp, 256);

			if ($debug)
			{
				echo "\nRESPONSE<<{$tr}>>";
			}

			$r .= $tr;

			if (($check_header) && (strpos($r, "\r\n\r\n") !== false))
			{
				// If content-length == 0, return query result
				if (strpos($r, 'Content-Length: 0') !== false)
				{
					return $r;
				}
			}

			// Keep-alive responses does not return EOF
			// they end with \r\n0\r\n\r\n string
			if (substr($r, -7) == "\r\n0\r\n\r\n")
			{
				return $r;
			}
		}

		return $r;
	}

	/**
	 * Method to process flash uploaded file
	 *
	 * @param   array   $file      Temp file location
	 * @param   object  $filename  File info
	 *
	 * @return    string
	 *
	 * @since 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function processuploadfile($file, $filename)
	{
		jimport('joomla.filesystem.file');
		$uploadmsg = '';

		if ($filename->type == 1)
		{
			$uploadmsg = JText::_('JBS_MED_UPLOAD_FAILED_NOT_UPLOAD_THIS_FOLDER');
		}
		elseif ($filename->type == 2)
		{
			$temp_folder = self::gettempfolder();
			$tempfile    = $temp_folder . $file['name'];
			$uploadmsg   = self::uploadftp($tempfile, $file);

			if (!$uploadmsg)
			{
				if (!$copy = self::ftp($tempfile, $filename, 0))
				{
					$uploadmsg = JText::_('JBS_MED_FILE_NO_UPLOADED_FTP');
				}

				JFile::delete($tempfile);
			}
		}
		elseif ($filename->type == 3)
		{
			$temp_folder = self::gettempfolder();
			$tempfile    = $temp_folder . $file['name'];
			$uploadmsg   = self::uploadftp($tempfile, $file);

			if (!$uploadmsg)
			{
				if (!$copy = self::aws($tempfile, $filename, 1))
				{
					$uploadmsg = JText::_('JBS_MED_FILE_NO_UPLOADED_AWS');
				}

				JFile::delete($tempfile);
			}
		}
		else
		{
			if (!JFile::upload($file['tmp_name'], $filename->path))
			{
				$uploadmsg = JText::_('JBS_MED_UPLOAD_FAILED_CHECK_PATH');
			}
		}

		return $uploadmsg;
	}

	/**
	 * Method to build temp folder
	 *
	 * @return    string
	 *
	 * @since 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function gettempfolder()
	{
		$abspath     = JPATH_SITE;
		$temp_folder = $abspath . DIRECTORY_SEPARATOR . 'media/com_biblestudy/tmp/';

		return $temp_folder;
	}

	/**
	 * Method to upload the file for ftp upload
	 *
	 * @param   string  $filename  Destination file details.
	 * @param   array   $file      Source File details.
	 *
	 * @return    string
	 *
	 * @since 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function uploadftp($filename, $file)
	{
		$msg = '';
		jimport('joomla.filesystem.file');

		if (!JFile::upload($file['tmp_name'], $filename))
		{
			$msg = JText::_('JBS_MED_UPLOAD_FAILED_CHECK_PATH') . ' ' . $filename . ' ' . JText::_('JBS_MED_UPLOAD_EXISTS');
		}

		return $msg;
	}

	/**
	 * Method to upload the file
	 *
	 * @param   object  $filename  Destination file details.
	 * @param   array   $file      Source File details.
	 *
	 * @return    string
	 *
	 * @since 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function upload($filename, $file)
	{
		$msg = '';
		jimport('joomla.filesystem.file');

		if (!JFile::upload($file['tmp_name'], $filename->path))
		{
			$msg = JText::_('JBS_MED_UPLOAD_FAILED_CHECK_PATH') . ' ' . $filename->path . ' ' . JText::_('JBS_MED_UPLOAD_EXISTS');
		}

		return $msg;
	}

	/**
	 * Method to build filepath
	 *
	 * @param   array   $file      File details.
	 * @param   string  $type      Type
	 * @param   int     $serverid  Server ID
	 * @param   int     $folderid  Folder Id
	 * @param   int     $path      The path id.
	 * @param   int     $flash     Sets whether this is a flash upload or normal php upload and chooses right path through function.
	 *
	 * @return    object
	 *
	 * @since 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public static function buildpath($file, $type, $serverid, $folderid, $path, $flash = 0)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components/com_biblestudy/tables');
		/** @type TableServer $filepath */
		$filepath = JTable::getInstance('Server', 'Table');
		$filepath->load($serverid);

		$reg = new \Joomla\Registry\Registry;
		$filepath->params = $reg->loadString($filepath->params);
		$filename = new stdClass;

		$filename->type        = $filepath->type;
		$filename->ftphost     = $filepath->params->get('ftphost');
		$filename->ftpuser     = $filepath->params->get('ftpuser');
		$filename->ftppassword = $filepath->params->get('ftppassword');
		$filename->ftpport     = $filepath->params->get('ftpport');
		$filename->aws_key     = $filepath->params->get('aws_key');
		$filename->aws_secret  = $filepath->params->get('aws_secret');
		$filename->aws_bucket  = $filepath->params->get('server_path');

		// This removes any characters that might cause headaches to browsers. This also does the same thing in the model
		$badchars = array(
			' ',
			'\'',
			'"',
			'`',
			'@',
			'^',
			'!',
			'#',
			'$',
			'%',
			'*',
			'(',
			')',
			'[',
			']',
			'{',
			'}',
			'~',
			'?',
			'>',
			'<',
			',',
			'|',
			'\\',
			';',
			'&',
			'_and_'
		);

		if ($flash == 0)
		{
			$file['name']   = str_replace($badchars, '_', $file['name']);
			$filename->file = JFile::makeSafe($file['name']);
		}

		if ($flash == 1)
		{
			$file           = str_replace($badchars, '_', $file);
			$filename->file = JFile::makeSafe($file);
		}

		if ($filename->type == 2)
		{
			$filename->path = $filename->file;
		}
		else
		{
			$filename->path = JPATH_SITE . DIRECTORY_SEPARATOR . $filename->file;
		}

		return $filename;
	}

	/**
	 * Method to load javascript for squeezebox modal
	 *
	 * @param   string  $host   the site base url
	 * @param   string  $admin  String to add to url
	 *
	 * @return    string
	 *
	 * @since 7.0
	 * @deprecate 9.0.0 No longer used.
	 */
	public function Uploadjs($host, $admin)
	{
		// When we send the files for upload, we have to tell Joomla our session, or we will get logged out
		$session = JFactory::getSession();

		$val  = ini_get('upload_max_filesize');
		$val  = trim($val);
		$last = strtolower($val[strlen($val) - 1]);

		switch ($last)
		{
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
				break;
			case 'm':
				$val *= 1024;
				break;
			case 'k':
				$val *= 1024;
				break;
		}

		$valk            = $val / 1024;
		$valm            = $valk / 1024;
		$maxupload       = $valm . ' MB';
		$swfUploadHeadJs = '
    var swfu;

    window.onload = function()
    {

    var settings =
    {
            //this is the path to the flash file, you need to put your components name into it
            flash_url : "' . $host . 'media/com_biblestudy/js/swfupload/swfupload.swf",

            //we can not put any vars into the url for complicated reasons, but we can put them into the post...
            upload_url: "' . $host . $admin . 'index.php?option=com_biblestudy&view=mediafile&task=uploadflash",
            post_params: {
            		"option" : "com_biblestudy",
           		"controller" : "Mediafile",
            		"task" : "upflash",
            		"' . $session->getName() . '" : "' . $session->getId() . '",
           		"format" : "raw"
           	},
            //you need to put the session and the "format raw" in there, the other ones are what you would normally put in the url
            file_size_limit : "' . $maxupload . '",
            //client side file checking is for usability only, you need to check server side for security
            file_types : "",
            file_types_description : "All Files",
            file_upload_limit : 100,
            file_queue_limit : 10,
            custom_settings :
            {
	    progressTarget : "fsUploadProgress",
                    cancelButtonId : "btnCancel"
            },
            debug: false,

           // Button settings
            button_image_url: "' . $host . 'media/com_biblestudy/js/swfupload/images/uploadbutton.png",
          button_width: "86",
          button_height: "33",
            button_placeholder_id: "spanButtonPlaceHolder",
            button_text: \'<span class="upbutton">' . JText::_('JBS_CMN_BROWSE') . '</span>\',
            button_text_style: ".upbutton { font-size: 14px; margin-left: 15px;}",
            button_text_left_padding: 5,
            button_text_top_padding: 5,

            // The event handler functions are defined in handlers.js
            file_queued_handler : fileQueued,
            file_queue_error_handler : fileQueueError,
            file_dialog_complete_handler : fileDialogComplete,
            upload_start_handler : uploadStart,
            upload_progress_handler : uploadProgress,
            upload_error_handler : uploadError,
            upload_success_handler : uploadSuccess,
            upload_complete_handler : uploadComplete,
            queue_complete_handler : queueComplete     // Queue plugin event
    };
    swfu = new SWFUpload(settings);
    };

    ';

		return $swfUploadHeadJs;
	}
}
