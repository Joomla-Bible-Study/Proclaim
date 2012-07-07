<?php

/**
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'upload.php');
jimport('joomla.application.component.controllerform');

/**
 * Controller For MediaFile
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyControllerMediafile extends JControllerForm {

    /**
     * Class constructor.
     *
     * @param   array  $config  A named array of configuration variables.
     *
     * @since	7.0.0
     */
    function __construct($config = array()) {
        parent::__construct($config);
    }

    /*
     * Adds the ability to uploade with flash
     * @since 7.1.0
     */

    function uploadflash() {

        JRequest::checktoken() or jexit('Invalid Token');
        $option = JRequest::getCmd('option');
        jimport('joomla.filesystem.file');
        //get the server and folder id from the request
        $serverid = JRequest::getInt('upload_server', '', 'post');
        $folderid = JRequest::getInt('upload_folder', '', 'post');
        $app = JFactory::getApplication();
        $app->setUserState($option, 'serverid', $serverid);
        $app->setUserState($option . 'folderid', $folderid);
        $form = JRequest::getVar('jform', array(), 'post', 'array');
        $returnid = $form['id'];
        // get temp file details
        $temp = JBSUpload::gettempfile();
        $temp_folder = JBSUpload::gettempfolder();
        $tempfile = $temp_folder . $temp;
        // get path and abort if none
        $layout = JRequest::getWord('layout', '');
        if ($layout == 'modal') {
            $url = 'index.php?option=' . $option . '&view=mediafile&task=edit&tmpl=component&layout=modal&id=' . $returnid;
        } else {
            $url = 'index.php?option=' . $option . '&view=mediafile&task=edit&id=' . $returnid;
        }
        $path = JBSUpload::getpath($url, $tempfile);

        // check filetype is allowed
        $allow = JBSUpload::checkfile($temp);
        if ($allow) {
            $filename = JBSUpload::buildpath($temp, 1, $serverid, $folderid, $path, 1);


            // process file
            $uploadmsg = JBSUpload::processflashfile($tempfile, $filename);
            if (!$uploadmsg) {
                // set folder and link entries

                $uploadmsg = JText::_('JBS_MED_FILE_UPLOADED');
            }
        } else {
            $uploadmsg = JText::_('JBS_MED_NOT_UPLOAD_THIS_FILE_EXT');
        }
        //  $podmsg = PIHelperadmin::setpods($row);
        // delete temp file

        JBSUpload::deletetempfile($tempfile);
        $mediafileid = JRequest::getInt('id', '', 'post');
        if ($layout = 'modal') {
            $this->setRedirect('index.php?option=' . $option . '&view=mediafile&task=edit&tmpl=component&layout=modal&id=' . $returnid, $uploadmsg);
        } else {
            $this->setRedirect('index.php?option=' . $option . '&view=mediafile&task=edit&id=' . $returnid, $uploadmsg);
        }
    }

    /**
     * Upload Flash System
     * @return text
     */
    function upflash() {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        $serverid = JRequest::getInt('upload_server', '', 'post');
        $folderid = JRequest::getInt('upload_folder', '', 'post');
        //import joomla filesystem functions, we will do all the filewriting with joomlas functions,
        //so if the ftp layer is on, joomla will write with that, not the apache user, which might
        //not have the correct permissions
        $abspath = JPATH_SITE;
        //this is the name of the field in the html form, filedata is the default name for swfupload
        //so we will leave it as that
        $fieldName = 'Filedata';
        //any errors the server registered on uploading
        $fileError = $_FILES[$fieldName]['error'];
        if ($fileError > 0) {
            switch ($fileError) {
                case 1:
                    echo JText::_('JBS_MED_FILE_TOO_LARGE_THAN_PHP_INI_ALLOWS');
                    return;

                case 2:
                    echo JText::_('JBS_MED_FILE_TO_LARGE_THAN_HTML_FORM_ALLOWS');
                    return;

                case 3:
                    echo JText::_('JBS_MED_ERROR_PARTIAL_UPLOAD');
                    return;

                case 4:
                    echo JText::_('JBS_MED_ERROR_NO_FILE');
                    return;
            }
        }

        //check for filesize
        $fileSize = $_FILES[$fieldName]['size'];
        if ($fileSize > 500000000) {
            echo JText::_('JBS_MED_FILE_BIGGER_THAN') . ' 500MB';
        }

        //check the file extension is ok
        $fileName = $_FILES[$fieldName]['name'];
        $extOk = JBSUpload::checkfile($fileName);
        $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $app->setUserState($option . 'fname', $_FILES[$fieldName]['name']);
        $app->setUserState($option . 'size', $_FILES[$fieldName]['size']);
        $app->setUserState($option . 'serverid', $serverid);
        $app->setUserState($option . 'folderid', $folderid);
        if ($extOk == false) {
            echo JText::_('JBS_MED_NOT_UPLOAD_THIS_FILE_EXT');
            return;
        }

        //the name of the file in PHP's temp directory that we are going to move to our folder
        $fileTemp = $_FILES[$fieldName]['tmp_name'];

        //always use constants when making file paths, to avoid the possibilty of remote file inclusion

        $uploadPath = $abspath . DS . 'media' . DS . 'com_biblestudy' . DS . 'js' . DS . 'swfupload' . DS . 'tmp' . DS . $fileName;


        if (!JFile::upload($fileTemp, $uploadPath)) {
            echo JText::_('JBS_MED_ERROR_MOVING_FILE');
            return;
        } else {

            // success, exit with code 0 for Mac users, otherwise they receive an IO Error
            exit(0);
        }
    }

    /**
     * Upload function
     *
     */
    function upload() {
        JRequest::checktoken() or jexit('Invalid Token');
        $option = JRequest::getCmd('option');
        $uploadmsg = '';
        $serverid = JRequest::getInt('upload_server', '', 'post');
        $folderid = JRequest::getInt('upload_folder', '', 'post');
        $form = JRequest::getVar('jform', array(), 'post', 'array');
        $returnid = $form['id'];
        $url = 'index.php?option=com_biblestudy&view=mediafile&id=' . $returnid;
        $path = JBSUpload::getpath($url, '');
        $file = JRequest::getVar('uploadfile', '', 'files', 'array'); //dump($file, '$file: ');
        // check filetype allowed
        $allow = JBSUpload::checkfile($file['name']);
        if ($allow) {
            $filename = JBSUpload::buildpath($file, 1, $serverid, $folderid, $path); //dump($filename, '$filename: ');
            // process file
            $uploadmsg = JBSUpload::processuploadfile($file, $filename);

            if (!$uploadmsg) {
                $uploadmsg = JText::_('JBS_MED_FILE_UPLOADED');
            }
        }
        $mediafileid = JRequest::getInt('id', '', 'post');
        $app = JFactory::getApplication();
        $app->setUserState($option . 'fname', $file['name']);
        $app->setUserState($option . 'size', $file['size']);
        $app->setUserState($option . 'serverid', $serverid);
        $app->setUserState($option . 'folderid', $folderid);
        if ($layout = 'modal') {
            $this->setRedirect('index.php?option=' . $option . '&view=mediafile&task=edit&tmpl=component&layout=modal&id=' . $returnid, $uploadmsg);
        } else {
            $this->setRedirect('index.php?option=' . $option . '&view=mediafile&task=edit&id=' . $returnid, $uploadmsg);
        }
    }

}

