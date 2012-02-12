<?php

/**
 * CSS Edit Controller for Bible Study Component
 * @version     $Id: cssedit.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * Series Edit Controller
 *
 */
class biblestudyControllercssedit extends JController {

    /**
     * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanisim from kicking in
     *
     * @since 7.0
     */
    protected $view_list = 'cpanel';

    /**
     * constructor (registers additional tasks to methods)
     * @return void
     */
    function __construct() {

        parent::__construct();

        // Register Extra tasks
        $this->registerTask('apply', 'save');
    }

    function cancel() {
        $msg = JText::_('JBS_CMN_OPERATION_CANCELLED');
        $this->setRedirect('index.php?option=com_biblestudy&view=cpanel', $msg);
    }

    /**
     * save a record (and redirect to main page)
     * @return void
     */
    function resetcss() {
        $mainframe = JFactory::getApplication();
        // Set FTP credentials, if given
        jimport('joomla.client.helper');
        JClientHelper::setCredentialsFromRequest('ftp');
        $ftp = JClientHelper::getCredentials('ftp');
        $filename = 'biblestudy.css.dist';
        $src = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . $filename;
        $dest = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'biblestudy.css';

        // Try to make the css file writeable

        jimport('joomla.filesystem.file');
        $return = JFile::copy($src, $dest);
        if ($return) {
            $mainframe->redirect('index.php?option=com_biblestudy&view=cpanel', JText::_('JBS_CSS_RESET'));
        } else {
            $mainframe->redirect('index.php?option=com_biblestudy&view=cpanel', JText::_('JBS_CMN_OPERATION_FAILED') . ': ' . JText::_('JBS_CMN_FAILED_OPEN_FOR_WRITE'));
        }
    }

    function save() {
        $mainframe = JFactory::getApplication();


        // Initialize some variables
        $option = JRequest::getCmd('option');
        $client = JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
        $filename = 'biblestudy.css';
        $filecontent = JRequest::getVar('filecontent', '', '', 'string', JREQUEST_ALLOWRAW);



        // Set FTP credentials, if given
        jimport('joomla.client.helper');
        JClientHelper::setCredentialsFromRequest('ftp');
        $ftp = JClientHelper::getCredentials('ftp');

        $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . $filename;
        // Try to make the css file writeable

        jimport('joomla.filesystem.file');
        $return = JFile::write($file, $filecontent);


        if ($return) {
            if (JRequest::getWord('task', '', '') == 'apply') {
                $mainframe->redirect('index.php?option=com_biblestudy&view=cssedit', JText::_('JBS_CSS_FILE_SAVED'));
            } else {
                $mainframe->redirect('index.php?option=com_biblestudy&view=cpanel', JText::_('JBS_CSS_FILE_SAVED'));
            }
        } else {
            $mainframe->redirect('index.php?option=com_biblestudy&view=cpanel', JText::_('JBS_CMN_OPERATION_FAILED') . ': ' . JText::_('JBS_CMN_FAILED_OPEN_FOR_WRITE'));
        }
    }

    function backup() {
        $mainframe = JFactory::getApplication();
        //Check for existence of com_biblestudy folder in media and create if it doesn't exist
        jimport('joomla.filesystem.folder');
        $mediafolderpath = JFolder::exists(JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'backup');
        if (!$mediafolderpath) {
            $createmediafolder = JFolder::create(JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'backup');
            if (!$createmediafolder) {
                $mainframe->redirect('index.php?option=com_biblestudy&view=cpanel', JText::_('JBS_CMN_OPERATION_FAILED') . ': ' . JText::_('JBS_CMN_FAILED_CREATE_FOLDER'));
            }
        }

        // Set FTP credentials, if given
        jimport('joomla.client.helper');
        JClientHelper::setCredentialsFromRequest('ftp');
        $ftp = JClientHelper::getCredentials('ftp');
        $filename = 'biblestudy.css';
        $src = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . $filename;
        $dest = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'biblestudy.css';

        // Try to make the css file writeable

        jimport('joomla.filesystem.file');
        $return = JFile::copy($src, $dest);
        if ($return) {
            $mainframe->redirect('index.php?option=com_biblestudy&view=cpanel', JText::_('JBS_CSS_BACKUP_SAVED'));
        } else {
            $mainframe->redirect('index.php?option=com_biblestudy&view=cpanel', JText::_('JBS_CMN_OPERATION_FAILED') . ': ' . JText::_('JBS_CMN_FAILED_OPEN_FOR_WRITE') . ': ' . $file);
        }
    }

    function copycss() {
        $mainframe = JFactory::getApplication();
        // Set FTP credentials, if given
        jimport('joomla.client.helper');
        JClientHelper::setCredentialsFromRequest('ftp');
        $ftp = JClientHelper::getCredentials('ftp');
        $filename = 'biblestudy.css';
        $dest = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . $filename;
        $src = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'biblestudy.css';

        // Try to make the css file writeable

        jimport('joomla.filesystem.file');
        $return = JFile::copy($src, $dest);
        if ($return) {
            $mainframe->redirect('index.php?option=com_biblestudy&view=cpanel', JText::_('JBS_CSS_BACKUP_RESTORED'));
        } else {
            $mainframe->redirect('index.php?option=com_biblestudy&view=cpanel', JText::_('JBS_CMN_OPERATION_FAILED') . ': ' . JText::_('JBS_CMN_FAILED_OPEN_FOR_WRITE') . ': ' . $file);
        }
    }

    function restorecss() {
        $mainframe = JFactory::getApplication();
        jimport('joomla.client.helper');
        JClientHelper::setCredentialsFromRequest('ftp');
        $ftp = JClientHelper::getCredentials('ftp');
        $filename = 'biblestudy.css';
        $dest = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . $filename;
        $src = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'biblestudy.css';
        jimport('joomla.filesystem.file');
        $backupexists = JFile::exists($src);
        if (!$backupexists) {
            $mainframe->redirect('index.php?option=com_biblestudy&view=cpanel', JText::_('JBS_CMN_OPERATION_FAILED'));
        }
        $return = JFile::copy($src, $dest);
        if ($return) {
            $mainframe->redirect('index.php?option=com_biblestudy&view=cpanel', JText::_('JBS_CSS_BACKUP_RESTORED'));
        } else {
            $mainframe->redirect('index.php?option=com_biblestudy&view=cpanel', JText::_('JBS_CMN_OPERATION_FAILED') . ': ' . JText::_('JBS_CMN_FAILED_OPEN_FOR_WRITE') . ': ' . $file);
        }
    }

}
