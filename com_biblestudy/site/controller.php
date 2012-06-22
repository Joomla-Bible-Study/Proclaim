<?php

/**
 * @version $Id: controller.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport("joomla.application.component.controller");
require_once (JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'upload.php');

class biblestudyController extends JController {

    public function display($cachable = false, $urlparams = false) {
        $cachable = true;

        JHtml::_('behavior.caption');

        // Set the default view name and format from the Request.
        // Note we are using a_id to avoid collisions with the router and the return page.
        // Frontend is a bit messier than the backend.
        $vName = JRequest::getCmd('view', 'landingpage');
        JRequest::setVar('view', $vName);
        if ($vName == 'popup')
            $cachable = false;

        $user = JFactory::getUser();

        if ($user->get('id') ||
                ($_SERVER['REQUEST_METHOD'] == 'POST' &&
                ($vName == 'archive' ))) {
                    $cachable = false;
        }

        //attempt to change mysql for error in large select
        $db = JFactory::getDBO();
        $db->setQuery('SET SQL_BIG_SELECTS=1');
        $db->query();
        $t = JRequest::getInt('t', 'get');
        if (!$t) {
            $t = 1;
        }
        JRequest::setVar('t', $t, 'get');

        $safeurlparams = array('id' => 'INT', 'cid' => 'ARRAY', 'year' => 'INT', 'month' => 'INT', 'limit' => 'INT', 'limitstart' => 'INT',
            'showall' => 'INT', 'return' => 'BASE64', 'filter' => 'STRING', 'filter_order' => 'CMD', 'filter_order_Dir' => 'CMD', 'filter-search' => 'STRING', 'print' => 'BOOLEAN', 'lang' => 'CMD');

        parent::display($cachable, $safeurlparams);

        return $this;
    }

    function comment() {

        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');

        $model = $this->getModel('sermon');
        $menu = JSite::getMenu();
        $item = $menu->getActive();
        $params = $mainframe->getPageParameters();
        $t = $params->get('t');
        if (!$t) {
            $t = 1;
        }
        JRequest::setVar('t', $t, 'get');

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($model->_template[0]->params);
        $params = $registry;
        $cap = 1;

        if ($params->get('use_captcha') > 0) {
            //Begin reCaptcha
            require_once(JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'captcha' . DIRECTORY_SEPARATOR . 'recaptchalib.php');
            $privatekey = $params->get('private_key');
            $challenge = JRequest::getVar('recaptcha_challenge_field', '', 'post');
            $response = JRequest::getVar('recaptcha_response_field', '', 'post');
            $resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $challenge, $response);
            if (!$resp->is_valid) {
                // What happens when the CAPTCHA was entered incorrectly
                $mess = JText::_('JBS_STY_INCORRECT_KEY');
                echo "<script language='javascript' type='text/javascript'>alert('" . $mess . "')</script>";
                echo "<script language='javascript' type='text/javascript'>window.history.back()</script>";
                return;
                $cap = 0;
            } else {
                $cap = 1;
            }
        }

        if ($cap == 1) {
            if (JRequest::getInt('published', '', 'post') == 0) {
                $msg = JText::_('JBS_STY_COMMENT_UNPUBLISHED');
            } else {
                $msg = JText::_('JBS_STY_COMMENT_SUBMITTED');
            }
            if (!$model->storecomment()) {
                $msg = JText::_('JBS_STY_ERROR_SUBMITTING_COMMENT');
            }

            if ($params->get('email_comments') > 0) {
                $EmailResult = $this->commentsEmail($params);
            }
            $study_detail_id = JRequest::getVar('study_detail_id', 0, 'POST', 'INT');

            $mainframe->redirect('index.php?option=com_biblestudy&id=' . $study_detail_id . '&view=sermon&t=' . $t, $msg);
        } // End of $cap
    }

    function commentsEmail($params) {
        $mainframe = JFactory::getApplication();
        $menuitemid = JRequest::getInt('Itemid');
        if ($menuitemid) {
            $menu = JSite::getMenu();
            $menuparams = $menu->getParams($menuitemid);
        }
        $comment_author = JRequest::getVar('full_name', 'Anonymous', 'POST', 'WORD');
        $comment_study_id = JRequest::getVar('study_detail_id', 0, 'POST', 'INT');
        $comment_email = JRequest::getVar('user_email', 'No Email', 'POST', 'WORD');
        $comment_text = JRequest::getVar('comment_text', 'None', 'POST', 'WORD');
        $comment_published = JRequest::getVar('published', 0, 'POST', 'INT');
        $comment_date = JRequest::getVar('comment_date', 0, 'POST', 'INT');
        $comment_date = date('Y-m-d H:i:s');
        $config = JFactory::getConfig();
        $comment_abspath = JPATH_SITE;
        $comment_mailfrom = $config->getValue('config.mailfrom');
        $comment_fromname = $config->getValue('config.fromname');
        ;
        $comment_livesite = JURI::root();
        $db = JFactory::getDBO();
        $query = 'SELECT id, studytitle, studydate FROM #__bsms_studies WHERE id = ' . $comment_study_id;
        $db->setQuery($query);
        $comment_details = $db->loadObject();
        $comment_title = $comment_details->studytitle;
        $comment_study_date = $comment_details->studydate;
        $mail = JFactory::getMailer();
        $ToEmail = $params->get('recipient', '');
        $Subject = $params->get('subject', 'Comments');
        $FromName = $params->get('fromname', $comment_fromname);
        if (empty($ToEmail))
            $ToEmail = $comment_mailfrom;
        $Body = $comment_author . ' ' . JText::_('JBS_STY_HAS_ENTERED_COMMENT') . ': ' . $comment_title . ' - ' . $comment_study_date . ' ' . JText::_('JBS_STY_ON') . ': ' . $comment_date;
        if ($comment_published > 0) {
            $Body = $Body . ' ' . JText::_('JBS_STY_COMMENT_PUBLISHED');
        } else {
            $Body = $Body . ' ' . JText::_('JBS_STY_COMMENT_NOT_PUBLISHED');
        }
        $Body = $Body . ' ' . JText::_('JBS_STY_REVIEW_COMMENTS_LOGIN') . ': ' . $comment_livesite;
        $mail->addRecipient($ToEmail);
        $mail->setSubject($Subject . ' ' . $comment_livesite);
        $mail->setBody($Body);
        $mail->Send();
    }

    function download() {
        $abspath = JPATH_SITE;
        require_once($abspath . DIRECTORY_SEPARATOR . 'components/com_biblestudy/lib/biblestudy.download.class.php');
        $task = JRequest::getVar('task');
        if ($task == 'download') {
            $mid = JRequest::getInt('mid', '0');
            $downloader = new Dump_File();
            $downloader->download($mid);

            die;
        }
    }

    function avplayer() {
        $task = JRequest::getVar('task');
        if ($task == 'avplayer') {
            $mediacode = JRequest::getVar('code');
            $this->mediaCode = $mediacode;
            echo $mediacode;
            return;
        }
    }

    function playHit() {
        require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.media.class.php');
        $getMedia = new jbsMedia();
        $getMedia->hitPlay(JRequest::getInt('id'));
    }

    /**
     * @desc: This function is supposed to generate the Media Player that is requested via AJAX
     * from the sermons view "default.php". It has not been implemented yet, so its not used.
     * @return unknown_type
     */
    function inlinePlayer() {
        echo('{m4vremote}http://www.livingwatersweb.com/video/John_14_15-31.m4v{/m4vremote}');
    }

    function uploadflash() {

        JRequest::checktoken() or jexit('Invalid Token');
        $option = JRequest::getCmd('option');
        jimport('joomla.filesystem.file');
        //get the server and folder id from the request
        $serverid = JRequest::getInt('upload_server', '', 'post');
        $folderid = JRequest::getInt('upload_folder', '', 'post');
        $form = JRequest::getVar('jform', array(), 'post', 'array');
        $returnid = $form['id'];
        // get temp file details
        $temp = JBSUpload::gettempfile();
        $temp_folder = JBSUpload::gettempfolder();
        $tempfile = $temp_folder . $temp; //dump($tempfile);
        // get path and abort if none
        $url = 'index.php?option=' . $option . '&view=mediafile&task=edit&id=' . $returnid;
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
        $this->setRedirect('index.php?option=' . $option . '&view=mediafile&task=edit&id=' . $returnid, $uploadmsg);
    }

    function upflash() {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
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
        //get media details
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
        $app->setUserState('fname',$file['name']); 
        $app->setUserState('size', $file['size']);
        if ($layout = 'modal'){$this->setRedirect('index.php?option=' . $option . '&view=mediafile&task=edit&tmpl=component&layout=modal&id=' . $returnid, $uploadmsg);}
        else {$this->setRedirect('index.php?option=' . $option . '&view=mediafile&task=edit&id=' . $returnid, $uploadmsg);}
    }

}