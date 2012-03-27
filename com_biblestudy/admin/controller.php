<?php

/**
 * @version $Id: controller.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_biblestudy')) {
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

require_once (JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'upload.php');
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.defines.php';
//dump($_FILES, '$_FILES');
class biblestudyController extends JController {

    protected $default_view = 'cpanel';

    public function display($cachable = false, $urlparams = false) {

        //attempt to change mysql for error in large select
        $db = JFactory::getDBO();
        $db->setQuery('SET SQL_BIG_SELECTS=1');
        $db->query();

        require_once JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php';
        BiblestudyHelper::addSubmenu(JRequest::getWord('view', 'cpanel'));

        $view = JRequest::getWord('view', 'cpanel');
        $layout = JRequest::getWord('layout', 'default');
        $id = JRequest::getInt('id');

        $type = JRequest::getWord('view');
        if (!$type) {
            JRequest::setVar('view', 'cpanel');
        }
        if ($type == 'admin') {
            $tool = JRequest::getVar('tooltype', '', 'post');
            if ($tool) {
                switch ($tool) {
                    case 'players':
                        $player = $this->changePlayers();
                        $this->setRedirect('index.php?option=com_biblestudy&view=admin', $player);
                        break;

                    case 'popups':
                        $popups = $this->changePopup();
                        $this->setRedirect('index.php?option=com_biblestudy&view=admin', $popups);
                        break;
                }
            }
        }

        if (JRequest::getCmd('view') == 'study') {
            $model = $this->getModel('study');
        }
        $fixassets = JRequest::getWord('task', '', 'get');
        if ($fixassets == 'fixassetid') {
            $dofix = $this->fixAsset_id();
            if (!$dofix) {
                JError::raiseNotice('SOME_ERROR_CODE', 'Fix Asset Function not successful');
            } else {
                JError::raiseNotice('SOME_ERROR_CODE', 'Fix assets successful');
            }
        }
        parent::display();
    }

    function AjaxTags() {
        die();
    }

    
    /**
     * @desc Looks up a topic for the auto-complete. Used by jquery.tag-it.js
     * @since 7.0.1
     * @return JSON object containing the results
     */
    function lookup_topic() {
        die();
    }

    function getFileList() {

        $serverId = JRequest::getVar('server');
        $folderId = JRequest::getVar('path');

        $path1 = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
        include_once($path1 . 'server.php');

        $server = getServer($serverId);
        $folder = getFolder($folderId);

        $type = $server->server_type;

        switch ($type) {
            case 'ftp':

                //ToDo -
                $ftp_server = $server->server_path;
                $conn_id = ftp_connect($ftp_server);

                // login with username and password
                $ftp_user_name = $server->ftp_username;
                $ftp_user_pass = $server->ftp_password;
                $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

                // get contents of the current directory
                $files = ftp_nlist($conn_id, $folder->folderpath);

                //ftp_quit();

                break;
            case 'local':
                $searchpath = JPATH_ROOT . $folder->folderpath;
                $files = JFolder::files($searchpath);
                break;
        }

        // output $contents
        echo json_encode($files);
    }

    function changePlayers() {

        $db = JFactory::getDBO();
        $msg = null;
        $data = JRequest::getVar('jform', array(), 'post', 'array');
        $from = $data['params']['from'];
        $to = $data['params']['to'];
        switch ($from) {
            case '100':
                $query = "UPDATE #__bsms_mediafiles SET `player` = '$to' WHERE `player` IS NULL";
                break;

            default:
                $query = "UPDATE #__bsms_mediafiles SET `player` = '$to' WHERE `player` = '$from'";
                break;
        }
        $db->setQuery($query);
        $db->query();
        $num_rows = $db->getAffectedRows();
        if ($db->getErrorNum() > 0) {
            $msg = JText::_('JBS_ADM_ERROR_OCCURED') . ' ' . $db->getErrorMsg();
        } else {

            $msg = JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . '<br /> ' . JText::_('JBS_ADM_AFFECTED_ROWS') . ': ' . $num_rows;
        }

        return $msg;
    }

    function changePopup() {

        $db = JFactory::getDBO();
        $msg = null;
        $data = JRequest::getVar('jform', array(), 'post', 'array');
        $from = $data['params']['pFrom'];
        $to = $data['params']['pTo'];

        $query = "UPDATE #__bsms_mediafiles SET `popup` = '$to' WHERE `popup` = '$from'";
        $db->setQuery($query);
        $db->query();
        $num_rows = $db->getAffectedRows();
        if ($db->getErrorNum() > 0) {
            $msg = JText::_('JBS_ADM_ERROR_OCCURED') . ' ' . $db->getErrorMsg();
        } else {

            $msg = JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . '<br /> ' . JText::_('JBS_ADM_AFFECTED_ROWS') . ': ' . $num_rows;
        }

        return $msg;
    }

    function writeXMLFile() {

        $path1 = JPATH_SITE . '/components/com_biblestudy/lib/';
        require_once($path1 . 'biblestudy.podcast.class.php');
        $podcasts = new JBSPodcast();
        $result = $podcasts->makePodcasts();

        $this->setRedirect('index.php?option=com_biblestudy&view=podcasts', $result);
    }

    function resetHits() {
        $msg = null;
        $id = JRequest::getInt('id', 0, 'get'); //dump ($cid, 'cid: ');
        $db = JFactory::getDBO();
        $db->setQuery("UPDATE #__bsms_studies SET hits='0' WHERE id = " . $id);
        $reset = $db->query();
        if ($db->getErrorNum() > 0) {
            $error = $db->getErrorMsg();
            $msg = JText::_('JBS_CMN_ERROR_RESETTING_HITS') . ' ' . $error;
            $this->setRedirect('index.php?option=com_biblestudy&view=message&layout=edit&id=' . $id, $msg);
        } else {
            $updated = $db->getAffectedRows();
            $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
            $this->setRedirect('index.php?option=com_biblestudy&view=message&layout=edit&id=' . $id, $msg);
        }
    }

    function resetDownloads() {
        $msg = null;
        $id = JRequest::getInt('id', 0, 'get'); //dump ($cid, 'cid: ');
        $db = JFactory::getDBO();
        $db->setQuery("UPDATE #__bsms_mediafiles SET downloads='0' WHERE id = " . $id);
        $reset = $db->query();
        if ($db->getErrorNum() > 0) {
            $error = $db->getErrorMsg();
            $msg = JText::_('JBS_CMN_ERROR_RESETTING_DOWNLOADS') . ' ' . $error;
            $this->setRedirect('index.php?option=com_biblestudy&view=mediafile&layout=edit&id=' . $id, $msg);
        } else {
            $updated = $db->getAffectedRows();
            $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
            $this->setRedirect('index.php?option=com_biblestudy&view=mediafile&layout=edit&id=' . $id, $msg);
        }
    }

    function resetPlays() {
        $msg = null;
        $id = JRequest::getInt('id', 0, 'get'); //dump ($cid, 'cid: ');
        $db = JFactory::getDBO();
        $db->setQuery("UPDATE #__bsms_mediafiles SET plays='0' WHERE id = " . $id);
        $reset = $db->query();
        if ($db->getErrorNum() > 0) {
            $error = $db->getErrorMsg();
            $msg = JText::_('JBS_CMN_ERROR_RESETTING_PLAYS') . ' ' . $error;
            $this->setRedirect('index.php?option=com_biblestudy&view=mediafile&layout=edit&id=' . $id, $msg);
        } else {
            $updated = $db->getAffectedRows();
            $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
            $this->setRedirect('index.php?option=com_biblestudy&view=mediafile&layout=edit&id=' . $id, $msg);
        }
    }

    
   
    function uploadflash()
    {
        
        JRequest::checktoken() or jexit( 'Invalid Token' );
        $option = JRequest::getCmd('option');
        jimport('joomla.filesystem.file'); 
        //get the server and folder id from the request
        $serverid = JRequest::getInt('upload_server','','post');
        $folderid = JRequest::getInt('upload_folder','','post');
        $form = JRequest::getVar('jform',array(),'post','array'); 
        $returnid = $form['id']; 
        // get temp file details
        $temp = JBSUpload::gettempfile();
        $temp_folder = JBSUpload::gettempfolder();
        $tempfile = $temp_folder.$temp;	//dump($tempfile);
        // get path and abort if none
        $url = 'index.php?option=' . $option . '&view=mediafile&task=edit&id=' . $returnid;
        $path = JBSUpload::getpath($url, $tempfile);
        
        // check filetype is allowed
        $allow = JBSUpload::checkfile($temp);
        if ($allow)
        {
        $filename = JBSUpload::buildpath($temp, 1, $serverid, $folderid, $path, 1);
    
        
        // process file
        $uploadmsg = JBSUpload::processflashfile($tempfile, $filename);
        if (!$uploadmsg) 
                { 
                // set folder and link entries
              
                $uploadmsg = JText::_('JBS_MED_FILE_UPLOADED');
                }	
       
        }
        else {$uploadmsg = JText::_('JBS_MED_NOT_UPLOAD_THIS_FILE_EXT');}
      //  $podmsg = PIHelperadmin::setpods($row);
     
        // delete temp file
        JBSUpload::deletetempfile($tempfile);
        $mediafileid = JRequest::getInt('id','','post');
        $this->setRedirect('index.php?option=' . $option . '&view=mediafile&task=edit&id=' . $returnid, $uploadmsg);	
    }

    function upflash()
    {dump($_FILES);
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
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
                echo JText::_( 'JBS_MED_FILE_TOO_LARGE_THAN_PHP_INI_ALLOWS' );
                return;

                case 2:
                echo JText::_( 'JBS_MED_FILE_TO_LARGE_THAN_HTML_FORM_ALLOWS' );
                return;

                case 3:
                echo JText::_( 'JBS_MED_ERROR_PARTIAL_UPLOAD' );
                return;

                case 4:
                echo JText::_( 'JBS_MED_ERROR_NO_FILE' );
                return;
                }
        }

        //check for filesize
        $fileSize = $_FILES[$fieldName]['size'];
        if($fileSize > 500000000)
        {
            echo JText::_( 'JBS_MED_FILE_BIGGER_THAN').' 500MB';
        }

        //check the file extension is ok
        $fileName = $_FILES[$fieldName]['name'];
        $extOk = JBSUpload::checkfile($fileName);

        if ($extOk == false) 
        {
                echo JText::_( 'JBS_MED_NOT_UPLOAD_THIS_FILE_EXT' );
                return;
        }

        //the name of the file in PHP's temp directory that we are going to move to our folder
        $fileTemp = $_FILES[$fieldName]['tmp_name'];

        //always use constants when making file paths, to avoid the possibilty of remote file inclusion
        //$uploadPath = JURI::root().DS.'media'.DS.'com_biblestudy'.DS.'js'.DS.'swfupload'.DS.'tmp'.DS.$fileName;
        $uploadPath = $abspath.DS.'media'.DS.'com_biblestudy'.DS.'js'.DS.'swfupload'.DS.'tmp'.DS.$fileName;

        if(!JFile::upload($fileTemp, $uploadPath)) 
        {
                echo JText::_( 'JBS_MED_ERROR_MOVING_FILE' );
                return;
        }
        else
        {
              
        // success, exit with code 0 for Mac users, otherwise they receive an IO Error
        exit(0);
        }
    }
   function upload()
    {
        JRequest::checktoken() or jexit( 'Invalid Token' );
        $option = JRequest::getCmd('option');
        
    
        $uploadmsg = '';
        $serverid = JRequest::getInt('upload_server','','post');
        $folderid = JRequest::getInt('upload_folder','','post');
        $form = JRequest::getVar('jform',array(),'post','array'); 
        $returnid = $form['id']; 
       
        
        $url = 'index.php?option=com_biblestudy&view=mediafile&id=' . $returnid;
        $path = JBSUpload::getpath($url, '');
        //get media details
        
        $file = JRequest::getVar( 'uploadfile', '', 'files', 'array' ); //dump($file, '$file: ');
      
       
        // check filetype allowed
        $allow = JBSUpload::checkfile($file['name']);
        
        if ($allow)
        {
            $filename = JBSUpload::buildpath($file, 1, $serverid, $folderid, $path); //dump($filename, '$filename: ');
            // process file
            $uploadmsg = JBSUpload::processuploadfile($file, $filename);
            
                if (!$uploadmsg) 
                { 
                    $uploadmsg = JText::_('JBS_MED_FILE_UPLOADED');
                }	
       
        }
      //  $uploadmsg = JText::_('JBS_MED_ERROR_MOVING_FILE');
            
       
        $this->setRedirect('index.php?option=' . $option . '&view=mediafile&task=edit&id=' . $returnid, $uploadmsg);
    }

    
}