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


jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.defines.php';

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

        if (JRequest::getCmd('view') == 'studydetails') {
            $model = $this->getModel('studydetails');
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

        $this->setRedirect('index.php?option=com_biblestudy&view=podcastlist', $result);
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
            $this->setRedirect('index.php?option=com_biblestudy&view=studiesedit&layout=edit&id=' . $id, $msg);
        } else {
            $updated = $db->getAffectedRows();
            $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
            $this->setRedirect('index.php?option=com_biblestudy&view=studiesedit&layout=edit&id=' . $id, $msg);
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
            $this->setRedirect('index.php?option=com_biblestudy&view=mediafilesedit&layout=edit&id=' . $id, $msg);
        } else {
            $updated = $db->getAffectedRows();
            $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
            $this->setRedirect('index.php?option=com_biblestudy&view=mediafilesedit&layout=edit&id=' . $id, $msg);
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
            $this->setRedirect('index.php?option=com_biblestudy&view=mediafilesedit&layout=edit&id=' . $id, $msg);
        } else {
            $updated = $db->getAffectedRows();
            $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
            $this->setRedirect('index.php?option=com_biblestudy&view=mediafilesedit&layout=edit&id=' . $id, $msg);
        }
    }

}