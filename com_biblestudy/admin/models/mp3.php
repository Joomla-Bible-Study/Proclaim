<?php

/**
 * @package     BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * MP3 model class
 * @package BibleStudy.Admin
 * @since 7.1.0
 */
class biblestudyModelmp3 extends JModel {

    /**
     * 
     * @var string
     */
    var $db;

    /**
     *
     * @var string
     */
    var $id3Sample;

    /**
     * ID3 Data Varible
     * @var string
     */
    var $id3Data;

    /**
     * Construct
     */
    function __construct() {
        parent::__construct();
        $this->db = $this->getDBO();
    }

    /**
     * Get Teachers
     * @return object
     */
    function getTeachers() {
        $query = 'SELECT DISTINCT id, teachername FROM #__bsms_teachers';
        $this->db->setQuery($query);
        return $this->db->loadAssocList();
    }

    /**
     * Get Locations
     * @return object
     */
    function getLocations() {
        $query = 'SELECT DISTINCT id, location_text FROM #__bsms_locations';
        $this->db->setQuery($query);
        return $this->db->loadAssocList();
    }

    /**
     * Get Series
     * @return object
     */
    function getSeries() {
        $query = 'SELECT DISTINCT id, series_text FROM #__bsms_series';
        $this->db->setQuery($query);
        return $this->db->loadAssocList();
    }

    /**
     * Get Topics
     * @return object
     */
    function getTopics() {
        $query = 'SELECT DISTINCT id, topic_text FROM #__bsms_topics';
        $this->db->setQuery($query);
        return $this->db->loadAssocList();
    }

    /**
     * Get Types
     * @return object
     */
    function getTypes() {
        $query = 'SELECT DISTINCT id, message_type FROM #__bsms_message_type';
        $this->db->setQuery($query);
        return $this->db->loadAssocList();
    }

    /**
     * Get Servers
     * @return object
     */
    function getServers() {
        $query = 'SELECT DISTINCT id, server_name FROM #__bsms_servers';
        $this->db->setQuery($query);
        return $this->db->loadAssocList();
    }

    /**
     * Get Folders
     * @return object
     */
    function getFolders() {
        $query = 'SELECT DISTINCT id, foldername FROM #__bsms_folders';
        $this->db->setQuery($query);
        return $this->db->loadAssocList();
    }

    /**
     * Get Podcast
     * @return object
     */
    function getPodcast() {
        $query = 'SELECT DISTINCT id, title FROM #__bsms_podcast';
        $this->db->setQuery($query);
        return $this->db->loadAssocList();
    }

    /**
     * Get MimeTypes
     * @return object
     */
    function getMimeTypes() {
        $query = 'SELECT DISTINCT id, mimetext FROM #__bsms_mimetype';
        $this->db->setQuery($query);
        return $this->db->loadAssocList();
    }

    /**
     * Get ID# of MP3
     * @return object
     */
    function getId3Info() {
        if (isset($this->id3Data)) {
            return $this->id3Data;
        }
        // @todo change folders
        require_once(JPATH_SITE . DS . 'media' . DS . 'com_biblestudy' . DS . 'classes' . DS . 'getid3.php');
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');


        $id3 = new getID3();
        //$files = new JFolder();
        $ext = new JFile();

        $folder = JRequest::getVar('directoryname');

        $files = JFolder::files($folder, '.', true, true);

        $allowedExtensions = array('mp3');
        if ($files != false) {
            foreach ($files as $file) {
                if (in_array($ext->getExt($file), $allowedExtensions)) {
                    $analysis = $id3->analyze($file);
                    if (!isset($id3Sample)) {
                        $this->id3Sample = $analysis;
                    }
                    $rows[] = $analysis;
                }
            }
        }
        $this->id3Data = $rows;
        //var_dump($rows);
        return $this->id3Data;
    }

    /**
     * Get ID# Sample
     * @return object
     */
    function getId3Sample() {
        $this->id3Sample = $this->getId3Info();
        return $this->id3Sample;
    }

}