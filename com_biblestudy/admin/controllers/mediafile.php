<?php

/**
 * @version     $Id: mediafile.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'upload.php');
jimport('joomla.application.component.controllerform');

abstract class controllerClass extends JControllerForm {

}

class BiblestudyControllerMediafile extends controllerClass {

	/*
	 * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanisim from kicking in
	*
	* @todo bcc   We should rename this controler to "mediafile" and the list view controller
	* to "mediafiles" so that the pluralization in 1.6 would work properly
	*
	* @since 7.0
	*/
	protected $view_list = 'mediafiles';

	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
		// Register Extra tasks
	}

	function docmanCategoryItems() {
		//hide errors and warnings
		error_reporting(0);
		$catId = JRequest::getVar('catId');

		$model =& $this->getModel('mediafile');
		$items =& $model->getdocManCategoryItems($catId);
		echo $items;
	}

	function articlesSectionCategories() {
		error_reporting(0);
		$secId = JRequest::getVar('secId');

		$model =& $this->getModel('mediafile');
		$items =& $model->getArticlesSectionCategories($secId);
		echo $items;

	}

	function articlesCategoryItems() {
		error_reporting(0);
		$catId = JRequest::getVar('catId');

		$model =& $this->getModel('mediafile');
		$items =& $model->getCategoryItems($catId);
		echo $items;
	}
	function virtueMartItems(){
		error_reporting(0);
		$catId = JRequest::getVar('catId');

		$model =& $this->getModel('mediafile');
		$items =& $model->getVirtueMartItems($catId);
		echo $items;
	}


	function resetDownloads()
	{
		$msg = null;
		$id 	= JRequest::getInt( 'id', 0, 'post');
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__bsms_mediafiles SET downloads='0' WHERE id = ".$id);
		$reset = $db->query();
		if ($db->getErrorNum() > 0)
		{
			$error = $db->getErrorMsg();
			$msg = JText::_('JBS_CMN_ERROR_RESETTING_DOWNLOADS').' '.$error;
			$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafile&controller=admin&layout=form&cid[]='.$id, $msg );
		}
		else
		{
			$updated = $db->getAffectedRows();
			$msg = JText::_('JBS_CMN_RESET_SUCCESSFUL').' '.$updated.' '.JText::_('JBS_CMN_ROWS_RESET');
			$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafile&controller=message&layout=form&cid[]='.$id, $msg );
		}
	}

	function resetPlays()
	{
		$msg = null;
		$id 	= JRequest::getInt( 'id', 0, 'post');
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__bsms_mediafiles SET plays='0' WHERE id = ".$id);
		$reset = $db->query();
		if ($db->getErrorNum() > 0)
		{
			$error = $db->getErrorMsg();
			$msg = JText::_('JBS_CMN_ERROR_RESETTING_PLAYS').' '.$error;
			$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafile&controller=admin&layout=form&cid[]='.$id, $msg );
		}
		else
		{
			$updated = $db->getAffectedRows();
			$msg = JText::_('JBS_CMN_RESET_SUCCESSFUL').' '.$updated.' '.JText::_('JBS_CMN_ROWS_RESET');
			$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafile&controller=message&layout=form&cid[]='.$id, $msg );
		}
	}
        
    function uploadflash()
    {
        JRequest::checktoken() or jexit( 'Invalid Token' );
        $option = JRequest::getCmd('option');
        $db = & JFactory::getDBO();
        jimport('joomla.filesystem.file');
        //get the server and folder id from the request
       
        $bind = JRequest::getVar('jform', array(), 'post', 'array');
        $row =& JTable::getInstance('Mediafile', 'Table');
        if (!$row->bind($bind))
        {JError::raiseError(500, $row->getError() );}
        $serverid = $row->upload_server;
        $folderid = $row->upload_folder;
     /*   $admin =& JTable::getInstance('Bckadmin', 'Table');
        $adminid = '1';
        $admin->load($adminid);
        $uploadmsg = '';
        // get table, bind and store data
        $row =PIAdminfunctions::getformdetails('Studies'); 
        // sanitise and allow raw entries
        $row = PIHelperadmin::sanitisestudyrow($row);
        if (!$row->store())
        {JError::raiseError(500, $row->getError() );}
      
      */
        // get temp file details
        $temp = JBSUpload::gettempfile();
        $temp_folder = JBSUpload::gettempfolder();
        $tempfile = $temp_folder.$temp;	
        // get path and abort if none
        $url = 'index.php?option=' . $option . '&view=mediafiles';
        $path = JBSUpload::getpath($url, $tempfile);
        // get media type
      //  $media = JRequest::getVar ( 'mediaselector', '', 'POST', 'INT');
        $media = null;
        // check filetype is allowed
        $allow = JBSUpload::checkfile($temp);
        if ($allow)
        {
        $filename = JBSUpload::buildpath($temp, 1, $serverid, $folderid, $path, 1);
    
        // resize image if needed
     //   $resize = JBSUpload::resizemesimage($tempfile, $media);
        // get id3 info if available available before moving file
     //   $data = JBSUpload::getid3($tempfile, 1);	
        // process file
        $uploadmsg = JBSUpload::processflashfile($tempfile, $filename);
        if (!$uploadmsg) 
                { 
                // set folder and link entries
              //  $row = PIHelperadmin::setstudylist($row, $data, $filename, $path, $media);
                $uploadmsg = JText::_('JBS_MED_FILE_UPLOADED');
                }	
        // get filesizes if needed
      //  $row = PIHelperadmin::getstudydates($row);	
        //set saccess
     //   $row = PIHelperadmin::getsaccess($row);
        // set minaccess
      //  $row = PIHelperadmin::getminaccess($row);
        // get filesizes if needed
      //  $row = PIHelperadmin::getfilesize($row);
      //  if (!$row->store())
     //   {JError::raiseError(500, $row->getError() );}
        }
        else {$uploadmsg = JText::_('JBS_MED_NOT_UPLOAD_THIS_FILE_EXT');}
      //  $podmsg = PIHelperadmin::setpods($row);
     
        // delete temp file
        JBSUpload::deletetempfile($tempfile);
        $mediafileid = JRequest::getInt('id','','post');
        $this->setRedirect('index.php?option=' . $option . '&view=mediafile&task=edit&cid[]=' . $mediafileid, $uploadmsg);	
    }



//New File Size System Should work on all server now.
function getSizeFile ($url){
	$head = "";
	$url_p = @parse_url($url);
	$host = $url_p["host"];
	if(!preg_match("/[0-9]*\.[0-9]*\.[0-9]*\.[0-9]*/",$host)){
		// a domain name was given, not an IP
		$ip=gethostbyname($host);
		if(!preg_match("/[0-9]*\.[0-9]*\.[0-9]*\.[0-9]*/",$ip)){
			//domain could not be resolved
			return -1;
		}
	}
	$port = intval($url_p["port"]);
	if(!$port) $port=80;
	$path = $url_p["path"];

	$fp = fsockopen($host, $port, $errno, $errstr, 20);
	if(!$fp) {
		return false;
	} else {
		fputs($fp, "HEAD "  . $url  . " HTTP/1.1\r\n");
		fputs($fp, "HOST: " . $host . "\r\n");
		fputs($fp, "User-Agent: http://www.example.com/my_application\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		$headers = "";
		while (!feof($fp)) {
			$headers .= fgets ($fp, 128);
		}
	}
	fclose ($fp);
	$return = -2;
	$arr_headers = explode("\n", $headers);
	foreach($arr_headers as $header) {
		$s1 = "HTTP/1.1";
		$s2 = "Content-Length: ";
		$s3 = "Location: ";
		if(substr(strtolower ($header), 0, strlen($s1)) == strtolower($s1)) $status = substr($header, strlen($s1));
		if(substr(strtolower ($header), 0, strlen($s2)) == strtolower($s2)) $size   = substr($header, strlen($s2));
		if(substr(strtolower ($header), 0, strlen($s3)) == strtolower($s3)) $newurl = substr($header, strlen($s3));
	}
	if(intval($size) > 0) {
		$return=strval($size);
	} else {
		$return=$status;
	}
	if (intval($status)==302 && strlen($newurl) > 0) {
		// 302 redirect: get HTTP HEAD of new URL
		$return=getSizeFile($newurl);
	}
	return $return;
}

function upflash()
    {
        
//import joomla filesystem functions, we will do all the filewriting with joomlas functions,
        //so if the ftp layer is on, joomla will write with that, not the apache user, which might
        //not have the correct permissions
        $abspath    = JPATH_SITE;
        //this is the name of the field in the html form, filedata is the default name for swfupload
        //so we will leave it as that
        $fieldName = 'Filedata';
        //any errors the server registered on uploading
        $fileError = $_FILES[$fieldName]['error'];
        if ($fileError > 0) 
        {
                switch ($fileError) 
                {
                case 1:
                echo JText::_( 'COM_PREACHIT_ADMIN_MESSAGE_FILE_TOO_LARGE_THAN_PHP_INI_ALLOWS' );
                return;

                case 2:
                echo JText::_( 'COM_PREACHIT_ADMIN_MESSAGE_FILE_TO_LARGE_THAN_HTML_FORM_ALLOWS' );
                return;

                case 3:
                echo JText::_( 'COM_PREACHIT_ADMIN_MESSAGE_ERROR_PARTIAL_UPLOAD' );
                return;

                case 4:
                echo JText::_( 'COM_PREACHIT_ADMIN_MESSAGE_ERROR_NO_FILE' );
                return;
                }
        }

        //check for filesize
        $fileSize = $_FILES[$fieldName]['size'];
        if($fileSize > 500000000)
        {
            echo JText::_( 'COM_PREACHIT_ADMIN_MESSAGE_FILE_BIGGER_THAN').' 500MB';
        }

        //check the file extension is ok
        $fileName = $_FILES[$fieldName]['name'];
        $extOk = JBSUpload::checkfile($fileName);

        if ($extOk == false) 
        {
                echo JText::_( 'COM_PREACHIT_ADMIN_MESSAGE_NOT_UPLOAD_THIS_FILE_EXT' );
                return;
        }

        //the name of the file in PHP's temp directory that we are going to move to our folder
        $fileTemp = $_FILES[$fieldName]['tmp_name'];

        //always use constants when making file paths, to avoid the possibilty of remote file inclusion
        $uploadPath = JURI::root().DS.'media'.DS.'com_biblestudy'.DS.'js'.DS.'swfupload'.DS.'tmp'.DS.$fileName;

        if(!JFile::upload($fileTemp, $uploadPath)) 
        {
                echo JText::_( 'COM_PREACHIT_ADMIN_MESSAGE_ERROR_MOVING_FILE' );
                return;
        }
        else
        {
              //  $id = 1;
              //  $db =& JFactory::getDBO();
              //  $db->setQuery ("UPDATE #__pibckadmin SET uploadfile = '".$fileName."' WHERE id = '{$id}' ;"); 
               // $db->query();
        // success, exit with code 0 for Mac users, otherwise they receive an IO Error
        exit(0);
        }
    }

}

