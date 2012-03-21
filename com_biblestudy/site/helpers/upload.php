<?php

/**
 * @author Tom Fuller
 * @copyright 2012
 */

/**
	 * Method to load javascript for squeezebox modal
	 *
	 * $param string $host the site base url
	 *
	 * @return	string
	 */
class JBSUpload{
    
    function uploadjs($host)
    {
    //when we send the files for upload, we have to tell Joomla our session, or we will get logged out 
    $session = & JFactory::getSession();
    
    $val = ini_get('upload_max_filesize');
    $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
    $valk = $val/1024;
    $valm = $valk/1024;
    $maxupload = $valm. ' MB';
    
    $swfUploadHeadJs ='
    var swfu;
     
    window.onload = function()
    {
     
    var settings = 
    {
            //this is the path to the flash file, you need to put your components name into it
            flash_url : "'.JURI::root().'media/com_biblestudy/js/swfupload/swfupload.swf",
     
            //we can not put any vars into the url for complicated reasons, but we can put them into the post...
            upload_url: "'.$host.'index.php",
            post_params: {
            		"option" : "com_biblestudy",
           		"controller" : "mediafile",
            		"task" : "upflash",
            		"'.$session->getName().'" : "'.$session->getId().'",
           		"format" : "raw"
           	},
            //you need to put the session and the "format raw" in there, the other ones are what you would normally put in the url
            file_size_limit : "'.$maxupload.'",
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
            button_image_url: "'.JURI::root().'media/com_biblestudy/js/swfupload/images/uploadbutton.png",
            button_width: "86",
            button_height: "33",
            button_placeholder_id: "spanButtonPlaceHolder",
            button_text: \'<span class="upbutton">'.JText::_('JBS_CMN_BROWSE').'</span>\',
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

    /**
	 * Method to get temp file name from database
	 *
	 * @return	string
	 */

function gettempfile()
{
	$temp = JRequest::getVar ( 'flupfile', '', 'POST', 'STRING');
	return $temp;
}

/**
	 * Method to build temp folder
	 *
	 * @return	string
	 */

function gettempfolder()
{
	$abspath    = JPATH_SITE;
	$temp_folder = $abspath.DS.'media/com_biblestudy/js/swfupload/tmp/';
	
	return $temp_folder;
}

/**
	 * Method to get path variable
	 *
	 * @param	array $row  message details.
	 * @param	string $tempfile  Temp file path.	 
	 * @return	string
	 */

function getpath($url, $tempfile, $front = '')
{
	jimport('joomla.filesystem.file');
	$path = JRequest::getVar('upload_folder', '', 'POST', 'INT');
        $server = JRequest::getVar('upload_server', '', 'POST', 'INT');
	if ($server == '')
	{
		if ($tempfile)
		{JFile::delete($tempfile);}
		$msg = JText::_('JBS_MED_UPLOAD_FAILED_NO_FOLDER');
		if ($front)
		{$this->setRedirect($url.$msg);}
		else
		{$this->setRedirect($url, $msg);}
	}
        $returnpath = $server.$path;
	return $returnpath;
}

/**
	 * Method to delete temp file
	 *
	 * @param	string $tempfile  Temp file path.
	 *
	 * @return	bolean
	 */

function deletetempfile($tempfile)
{
	$db = & JFactory::getDBO();
	jimport('joomla.filesystem.file');
	
	// delete file
	JFile::delete($tempfile);

	
	
	return true;
}

/**
	 * Method to check upload file to see if it is allowed
	 *
	 * @param	array $file  File info
	 *
	 * @return	bolean
	 */

function checkfile($file)
{
	$allow = true;	
	$blacklist = array(".php", ".phtml", ".php3", ".php4", ".js", ".shtml", ".pl" ,".py");

	foreach ($blacklist as $ext)
	{
		if(preg_match("/$ext\$/i", $file))
		{$allow = false; }
	}
	
	return $allow;
}

/**
	 * Method to process flash uploaded file
	 *
	 * @param string $tempfile tempfile location
	 * @param	array $filename File info
	 *
	 * @return	string
	 */

function processflashfile($tempfile, $filename)
{
	jimport('joomla.filesystem.file');
	$uploadmsg = '';	
	if ($filename->type == 1)
	{
	$uploadmsg = JText::_('JBS_MED_UPLOAD_FAILED_NOT_UPLOAD_THIS_FOLDER');
	}
	elseif ($filename->type == 2)
	{
	if (!$copy = $this->ftp($tempfile, $filename, 1))
	{$uploadmsg = JText::_('JBS_MED_FILE_NO_UPLOADED_FTP');}
	}
	elseif ($filename->type == 3)
	{
	if (!$copy = $this->aws($tempfile, $filename, 1))
	{$uploadmsg = JText::_('JBS_MED_FILE_NO_UPLOADED_AWS');}
	}

	else {
	if (!$copy = JFile::copy($tempfile, $filename->path))
	{$uploadmsg = JText::_('JBS_MED_FILE_NO_UPLOADED');}
	}
	
	return $uploadmsg;
}

/**
	 * Method to process flash uploaded file
	 *
	 * @param array $file tempfile location
	 * @param	array $filename File info
	 *
	 * @return	string
	 */

function processuploadfile($file, $filename)
{
	jimport('joomla.filesystem.file');
	$uploadmsg = '';	
	
	if ($filename->type == 1)
	{
	$uploadmsg = JText::_('JBS_MED_UPLOAD_FAILED_NOT_UPLOAD_THIS_FOLDER');
	}
	elseif ($filename->type == 2)
	{
	$temp_folder = $this->gettempfolder();
	$tempfile = $temp_folder.$file['name'];	
	$uploadmsg = $this->uploadftp($tempfile, $file);
	if (!$uploadmsg)
		{
			if (!$copy = $this->ftp($tempfile, $filename, 1))
			{$uploadmsg = JText::_('JBS_MED_FILE_NO_UPLOADED_FTP');}

	JFile::delete($tempfile);	
		}
	}
	elseif ($filename->type == 3)
	{
	$temp_folder = $this->gettempfolder();
	$tempfile = $temp_folder.$file['name'];	
	$uploadmsg = $this->uploadftp($tempfile, $file);
	if (!$uploadmsg)
		{
			if (!$copy = $this->aws($tempfile, $filename, 1))
			{$uploadmsg = JText::_('JBS_MED_FILE_NO_UPLOADED_AWS');}

		JFile::delete($tempfile);
		}	
	}
	else
	{
		$uploadmsg = $this->upload($filename, $file);
	}
	
	return $uploadmsg;
}

/**
	 * Method to upload the file
	 *
	 * @param	array $file  Source File details.
	 * @param	array $filename Destination file details.
	 *
	 * @return	string
	 */

function upload($filename, $file)
{$msg = '';
jimport('joomla.filesystem.file');
if (!JFILE::upload($file['tmp_name'], $filename->path))
{ $msg = JText::_('JBS_MED_UPLOAD_FAILED_CHECK_PATH').' '. $filename->path .' '.JText::_('JBS_MED_UPLOAD_EXISTS');}

return $msg;
}

/**
	 * Method to upload the file for ftp upload
	 *
	 * @param	array $file  Source File details.
	 * @param	array $filename Destination file details.
	 *
	 * @return	string
	 */

function uploadftp($filename, $file)
{$msg = '';
jimport('joomla.filesystem.file');

if (!JFILE::upload($file['tmp_name'], $filename))
{ $msg = JText::_('JBS_MED_UPLOAD_FAILED_CHECK_PATH').' '. $filename->path .' '.JText::_('JBS_MED_UPLOAD_EXISTS');}
return $msg;
}

/**
	 * Method to upload the file over ftp
	 *
	 * @param	array $file  Source File details.
	 * @param	array $filename Destination file details.
	 * @param	bolean $admin Sets whether call is from Joomla admin or site.
	 *
	 * @return	bolean
	 */

function ftp($file, $filename, $admin = 0)
{
$app = JFactory::getApplication();
$ftpsuccess = true;
$ftpsuccess1 = true;
$ftpsuccess2 = true;
$ftpsuccess3 = true;
$ftpsuccess4 = true;
	// FTP access parameters
$host = $filename->ftphost;
$usr = $filename->ftpuser;
$pwd = $filename->ftppassword;
$port = $filename->ftpport;
 
// file to move:
$local_file = $file;
$ftp_path = $filename->path;
// connect to FTP server (port 21)
if (!$conn_id = ftp_connect($host, $port))
{
 if ($admin == 0)
 {
 $app->enqueueMessage ( JText::_('JBS_MED_FTP_NO_CONNECT'), 'error' );}
 $ftpsuccess1 = false;
}


// send access parameters
if (!ftp_login($conn_id, $usr, $pwd))
{
 if ($admin == 0)
 {
 $app->enqueueMessage ( JText::_('JBS_MED_FTP_NO_LOGIN'), 'error' );}
 $ftpsuccess2 = false;
}


 
// turn on passive mode transfers (some servers need this)
// ftp_pasv ($conn_id, true);
 
// perform file upload
if (!$upload = ftp_put($conn_id, $ftp_path, $local_file, FTP_BINARY))
{
 if ($admin == 0)
 {
 $app->enqueueMessage ( JText::_('JBS_MED_FTP_NO_UPLOAD'), 'error' );}
 $ftpsuccess3 = false;
}


 
/*
** Chmod the file (just as example)
*/
 
// If you are using PHP4 then you need to use this code:
// (because the "ftp_chmod" command is just available in PHP5+)
if (!function_exists('ftp_chmod')) {
   function ftp_chmod($ftp_stream, $mode, $filename){
        return ftp_site($ftp_stream, sprintf('CHMOD %o %s', $mode, $filename));
   }
}
 
// try to chmod the new file to 666 (writeable)
if (ftp_chmod($conn_id, 0755, $ftp_path) == false)
{
	if ($admin == 0)
 	{
    $app->enqueueMessage ( JText::_('JBS_MED_FTP_NO_CHMOD'), 'error' );}
 	$ftpsuccess4 = false;
}
 
// close the FTP stream
ftp_close($conn_id);

if (!$ftpsuccess1 || !$ftpsuccess2 || !$ftpsuccess3)
{$ftpsuccess = false;}

return $ftpsuccess;
}

}
?>