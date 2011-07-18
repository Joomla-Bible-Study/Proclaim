<?php

/**
 * @version     $Id: controller.php 1330 2011-01-06 08:01:38Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_biblestudy')) {
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}


jimport('joomla.application.component.controller');
require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_biblestudy' . DS . 'lib' . DS . 'biblestudy.defines.php');

class biblestudyController extends JController {

    protected $default_view = 'cpanel';

    function display() {

        //attempt to change mysql for error in large select
        $db = JFactory::getDBO();
        $db->setQuery('SET SQL_BIG_SELECTS=1');
        $db->query();

        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'biblestudy.php');
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
            $model = & $this->getModel('studydetails');
        }
        $fixassets = JRequest::getWord('fixassetid', '', 'get');
        if ($fixassets == 'fixassetid') {
            $dofix = $this->fixAsset_id();
        }
        parent::display();
    }

    function AjaxTags() {
        header('Content-type: text/javascript');
        $q = JRequest::getVar('q');

        $db = & JFactory::getDBO();
        $query = "select '0_" . $q . "' as id, '" . $q . "' as 'name' from dual union select distinct id, cast(topic_text as char) as 'name' from #__bsms_topics where topic_text like '%" . $q . "%' order by 'name' desc limit 10";
        $db->setQuery($query);

        $tresult = $db->loadObjectList();

        if (empty($tresult)) {

            $query = "select distinct '0_" . $q . "' as id, '" . $q . "' as 'name' from dual";

            $db->setQuery($query);

            $tresult = $db->loadObjectList();
        }

        foreach ($tresult as $item) {
            if ($tresult[0]->name == $item->name && $tresult[0]->id != $item->id) {
                unset($tresult[0]);
            }
        }
        echo json_encode($tresult);
        //  dump ($tresult,'AjaxTags');
    }

    /**
     * @desc Looks up a topic for the auto-complete. Used by jquery.tag-it.js
     * @since 7.0.1
     * @return JSON object containing the results
     */
    function lookup_topic() {
        $search = JRequest::getVar('q');

        $db = & JFactory::getDBO();
        $query = "SELECT topic.id, topic.topic_text AS name FROM #__bsms_topics AS topic WHERE topic.topic_text LIKE '%" . $search . "%'";

        $db->setQuery($query);

        $result = $db->loadObjectList();

        echo json_encode($result);
    }

    function getFileList() {

        $serverId = JRequest::getVar('server');
        $folderId = JRequest::getVar('path');

        $path1 = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_biblestudy' . DS . 'helpers' . DS;
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

            $msg = JText::_('JBS_ADM_OPERATION_SUCCESSFUL') . '<br /> ' . JText::_('JBS_ADM_AFFECTED_ROWS') . ': ' . $num_rows;
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

            $msg = JText::_('JBS_ADM_OPERATION_SUCCESSFUL') . '<br /> ' . JText::_('JBS_ADM_AFFECTED_ROWS') . ': ' . $num_rows;
        }

        return $msg;
    }

    function writeXMLFile() {

        $mainframe = & JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $path1 = JPATH_SITE . '/components/com_biblestudy/helpers/';
        include_once($path1 . 'writexml.php');


        $result = writeXML();
        if ($result) {

            $msg = JText::_('JBS_PDC_XML_FILES_WRITTEN');
        } else {

            $msg = JText::_('JBS_PDC_XML_FILES_ERROR');
        }
        $this->setRedirect('index.php?option=com_biblestudy&view=podcastlist', $msg);
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

    function fixAsset_id() {
        $db = JFactory::getDBO();
        //We see if one of the records matches the parent_id, if not, we need to reset them
        $query = "SELECT id FROM #__assets WHERE name = 'com_biblestudy'";
        $db->setQuery($query);
        $parent_id = $db->loadResult();

        // Check to see if assets have been fixed and if they match the parent_id
        $query = 'SELECT t.asset_id, a.parent_id FROM #__bsms_templates AS t LEFT JOIN #__assets AS a ON (t.asset_id = a.id) WHERE t.id = 1';
        $db->setQuery($query);
        $asset = $db->loadObject();

        if ($parent_id != $asset->parent_id && $asset->asset_id) {
            $query = 'UPDATE #__assets SET parent_id = ' . $parent_id . ' WHERE parent_id = ' . $asset->parent_id;
            $db->setQuery($query);
            if ($result = $db->query()) {
                return true;
            } else {
                return false;
            }
        } else {
            require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_biblestudy' . DS . 'install' . DS . 'biblestudy.assets.php');
            $assetfix = new fixJBSAssets();
            $assetdofix = $assetfix->AssetEntry();
            if ($assetdofix) {
                return true;
            } else {
                return false;
            }
        }
    }

}