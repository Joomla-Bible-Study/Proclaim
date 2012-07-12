<?php

/**
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/**
 * Restor class
 * @package BibleStudy.Admin
 * @since 7.0.4
 */
class JBSImport {

    /**
     * Import DB
     * @return boolean
     */
    function importdb() {
        $result = FALSE;
        jimport('joomla.filesystem.file');
        // Attempt to increase the maximum execution time for php scripts
        @set_time_limit(300);
        $installtype = JRequest::getString('install_directory', '', 'post');
        $backuprestore = JRequest::getString('backuprestore', '', 'post');
        if (substr_count($backuprestore, '.sql')) {
            if ($restored = $this->restoreDB($backuprestore)) {
                $result = true;
                return $result;
            }
        }
        if (substr_count($installtype, 'sql')) {
            $uploadresults = $this->_getPackageFromFolder();
            if ($uploadresults) {
                $result = true;
            }
        } else {
            $uploadresults = $this->_getPackageFromUpload();
            $result = true;
        }
        if ($result == true) {
            $result = $this->installdb($uploadresults);
            $userfile = JRequest::getVar('importdb', null, 'files', 'array');
            if (JFile::exists(JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $userfile['name'])) {
                @unlink(JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $userfile['name']);
            }
        }
        return $result;
    }

    /**
     * Get Package form Upload
     * @return boolean
     */
    function _getPackageFromUpload() {
        // Get the uploaded file information
        $userfile = JRequest::getVar('importdb', null, 'files', 'array');

        // Make sure that file uploads are enabled in php
        if (!(bool) ini_get('file_uploads')) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLFILE'));
            return false;
        }


        // If there is no uploaded file, we have a problem...
        if (!is_array($userfile)) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('No file selected'));
            return false;
        }

        // Check if there was a problem uploading the file.
        if ($userfile['error'] || $userfile['size'] < 1) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLUPLOADERROR'));
            return false;
        }

        // Build the appropriate paths
        $config = JFactory::getConfig();
        $tmp_dest = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $userfile['name'];

        $tmp_src = $userfile['tmp_name'];

        // Move uploaded file
        jimport('joomla.filesystem.file');
        $uploaded = @move_uploaded_file($tmp_src, $tmp_dest);

        if ($uploaded) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Install DB
     * @param string $tmp_src Temp info
     * @return boolean If db installed corectrly.
     */
    function installdb($tmp_src) {
        jimport('joomla.filesystem.file');
        @set_time_limit(300);
        $error = '';
        $errors = array();
        $result = false;
        $userfile = JRequest::getVar('importdb', null, 'files', 'array');
        $db = JFactory::getDBO();

        $query = @file_get_contents(JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $userfile['name']);
        $queries = $db->splitSql($query);
        foreach ($queries as $querie) {
            $db->setQuery($querie);
            $db->query();
            if ($db->getErrorNum() != 0) {
                $error = "DB function failed with error number " . $db->getErrorNum() . "<br /><font color=\"red\">";
                $error .= $db->stderr(true);
                $error .= "</font>";
                print_r($error);
                $errors[] = $error;
                //return false;
            }
        }
        if (!empty($errors)){return $errors;}
        else
        {return true;}
    }

    /**
     * Restor DB
     * @param string $backuprestroe
     * @return boolean See if we restore worked.
     */
    function restoreDB($backuprestore) {
        $result = false;
        $db = JFactory::getDBO();
        @set_time_limit(300);
        $query = @file_get_contents(JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $backuprestore);
        //Check to see if this is a backup from an old db and not a migration
        $isold = substr_compare('#__bsms_admin_genesis', $query);
        if ($isold){JError::raiseWarning('SOME_ERROR_CODE', JText::_('This is a database from an old version. Migrate first!'));
            return false;}
        $queries = $db->splitSql($query);
        foreach ($queries as $querie) {
            $db->setQuery($querie);
            $db->query();
            if ($db->getErrorNum() != 0) {
                $error = "DB function failed with error number " . $db->getErrorNum() . "<br /><font color=\"red\">";
                $error .= $db->stderr(true);
                $error .= "</font>";
                print_r($error);
                //return false;
            }
        }
        return true;
    }

    /**
     * Get Packege from Folder
     * @return boolean
     */
    function _getPackageFromFolder() {
        $result = false;

        $p_dir = JRequest::getString('install_directory', '', 'post');

        $config = & JFactory::getConfig();

        $p_dir = JPath::clean($p_dir);

        $db = JFactory::getDBO();
        $query = file_get_contents($p_dir);
        @set_time_limit(300);
        $db->setQuery($query);
        $db->queryBatch();
        if ($db->getErrorNum() != 0) {
            $error = JText::_("JBS_IBM_DB_ERROR") . ": " . $db->getErrorNum() . "<br /><font color=\"red\">";
            $error .= $db->stderr(true);
            $error .= "</font>";
            echo $error;
        } else {
            $result = true;
        }
        //To do: delete uploaded file
        return $result;
    }

    /**
     * Funtion to present array of tables
     * @return array
     */
    function getObjects() {
        $objects = array(array('name' => '#__bsms_servers', 'titlefield' => 'server_name', 'assetname' => 'serversedit', 'realname' => 'JBS_CMN_SERVERS'),
            array('name' => '#__bsms_folders', 'titlefield' => 'foldername', 'assetname' => 'foldersedit', 'realname' => 'JBS_CMN_FOLDERS'),
            array('name' => '#__bsms_studies', 'titlefield' => 'studytitle', 'assetname' => 'studiesedit', 'realname' => 'JBS_CMN_STUDIES'),
            array('name' => '#__bsms_comments', 'titlefield' => 'comment_date', 'assetname' => 'commentsedit', 'realname' => 'JBS_CMN_COMMENTS'),
            array('name' => '#__bsms_locations', 'titlefield' => 'location_text', 'assetname' => 'locationsedit', 'realname' => 'JBS_CMN_LOCATIONS'),
            array('name' => '#__bsms_media', 'titlefield' => 'media_text', 'assetname' => 'mediaedit', 'realname' => 'JBS_CMN_MEDIAIMAGES'),
            array('name' => '#__bsms_mediafiles', 'titlefield' => 'filename', 'assetname' => 'mediafilesedit', 'realname' => 'JBS_CMN_MEDIA_FILES'),
            array('name' => '#__bsms_message_type', 'titlefield' => 'message_type', 'assetname' => 'messagetypeedit', 'realname' => 'JBS_CMN_MESSAGE_TYPES'),
            array('name' => '#__bsms_mimetype', 'titlefield' => 'mimetext', 'assetname' => 'mimetypeedit', 'realname' => 'JBS_CMN_MIME_TYPES'),
            array('name' => '#__bsms_podcast', 'titlefield' => 'title', 'assetname' => 'podcastedit', 'realname' => 'JBS_CMN_PODCASTS'),
            array('name' => '#__bsms_series', 'titlefield' => 'series_text', 'assetname' => 'seriesedit', 'realname' => 'JBS_CMN_SERIES'),
            array('name' => '#__bsms_share', 'titlefield' => 'name', 'assetname' => 'shareedit', 'realname' => 'JBS_CMN_SOCIAL_NETWORKING_LINKS'),
            array('name' => '#__bsms_teachers', 'titlefield' => 'teachername', 'assetname' => 'teacheredit', 'realname' => 'JBS_CMN_TEACHERS'),
            array('name' => '#__bsms_templates', 'titlefield' => 'title', 'assetname' => 'templateedit', 'realname' => 'JBS_CMN_TEMPLATES'),
            array('name' => '#__bsms_topics', 'titlefield' => 'topic_text', 'assetname' => 'topicsedit', 'realname' => 'JBS_CMN_TOPICS'),
            array('name' => '#__bsms_admin', 'titlefield' => 'id', 'assetname' => 'admin', 'realname' => 'JBS_CMN_ADMINISTRATION'),
            array('name' => '#__bsms_studytopics', 'titlefield' => '', 'assetname' => '', 'realname' => ''),
            array('name' => '#__bsms_timeset', 'titlefield' => '', 'assetname' => '', 'realname' => ''),
            array('name' => '#__bsms_search', 'titlefield' => '', 'assetname' => '', 'realname' => ''),
            array('name' => '#__bsms_books', 'titlefield' => '', 'assetname' => '', 'realname' => ''),
            array('name' => '#__bsms_styles', 'titlefield' => '', 'assetname' => '', 'realname' => ''),
            array('name' => '#__bsms_order', 'titlefield' => '', 'assetname' => '', 'realname' => '')
        );
        return $objects;
    }

    /**
     * Alter tables for Blob
     * @return boolean
     */
    function TablestoBlob() {
        $backuptables = $this->getObjects();

        $db = JFactory::getDBO();
        foreach ($backuptables AS $backuptable) {

            if (substr_count($backuptable['name'], 'studies')) {
                $query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY studytext BLOB';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() != 0) {
                    print_r($db->stderr(true));
                }

                $query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY studytext2 BLOB';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() != 0) {
                    print_r($db->stderr(true));
                }
            }
            if (substr_count($backuptable['name'], 'podcast')) {
                $query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY description BLOB';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() != 0) {
                    print_r($db->stderr(true));
                }
            }
            if (substr_count($backuptable['name'], 'series')) {
                $query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY description BLOB';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() != 0) {
                    print_r($db->stderr(true));
                }
            }
            if (substr_count($backuptable['name'], 'teachers')) {
                $query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY information BLOB';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() != 0) {
                    print_r($db->stderr(true));
                }
            }
        }
        return true;
    }

    /**
     * Modify tables to Text
     * @return boolean
     */
    function TablestoText() {
        $backuptables = $this->getObjects();

        $db = JFactory::getDBO();
        foreach ($backuptables AS $backuptable) {

            if (substr_count($backuptable['name'], 'studies')) {
                $query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY studytext TEXT';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() != 0) {
                    print_r($db->stderr(true));
                }

                $query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY studytext2 TEXT';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() != 0) {
                    print_r($db->stderr(true));
                }
            }
            if (substr_count($backuptable['name'], 'podcast')) {
                $query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY description TEXT';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() != 0) {
                    print_r($db->stderr(true));
                }
            }
            if (substr_count($backuptable['name'], 'series')) {
                $query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY description TEXT';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() != 0) {
                    print_r($db->stderr(true));
                }
            }
            if (substr_count($backuptable['name'], 'teachers')) {
                $query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY information TEXT';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() != 0) {
                    print_r($db->stderr(true));
                }
            }
        }
        return true;
    }

}