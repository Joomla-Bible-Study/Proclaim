<?php

/**
 * StudyDetails JTable
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Table class for StudyDetails
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class Tablestudydetails extends JTable {

    /**
     * Primary Key
     *
     * @var int
     */
    var $id = null;

    /**
     * Published
     * @var int
     */
    var $published = 1;

    /**
     * Teacher ID
     * @var string
     */
    var $teacher_id = null;

    /**
     * Study Date
     * @var string
     */
    var $studydate = null;

    /**
     * Study Number
     * @var string
     */
    var $studynumber = null;

    /**
     * Book Number
     * @var string
     */
    var $booknumber = null;

    /**
     * Chapter Begin
     * @var string
     */
    var $chapter_begin = null;

    /**
     * Chapter End
     * @var string
     */
    var $chapter_end = null;

    /**
     * Verse Begin
     * @var string
     */
    var $verse_begin = null;

    /**
     * Verse End
     * @var string
     */
    var $verse_end = null;

    /**
     * Study Title
     * @var string
     */
    var $studytitle = null;

    /**
     * Study Intro
     * @var string
     */
    var $studyintro = null;

    /**
     * Message Type
     * @var string
     */
    var $messagetype = null;

    /**
     * Series ID
     * @var string
     */
    var $series_id = null;

    /**
     * Study Text
     * @var string
     */
    var $studytext = null;

    /**
     * Topics ID
     * @var string
     */
    var $topics_id = null;

    /**
     * Secandary Reference
     * @var string
     */
    var $secondary_reference = null;

    /**
     * Media Hours
     * @var string
     */
    var $media_hours = null;

    /**
     * Media Minutes
     * @var string
     */
    var $media_minutes = null;

    /**
     * Media Seconds
     * @var string
     */
    var $media_seconds = null;

    /**
     * Book Number2
     * @var string
     */
    var $booknumber2 = null;

    /**
     * Chapter Begin2
     * @var string
     */
    var $chapter_begin2 = null;

    /**
     * Chapter End2
     * @var string
     */
    var $chapter_end2 = null;

    /**
     * Verse Begin2
     * @var string
     */
    var $verse_begin2 = null;

    /**
     * Verse End2
     * @var string
     */
    var $verse_end2 = null;

    /**
     * Comments
     * @var string
     */
    var $comments = null;

    /**
     * Hits
     * @var string
     */
    var $hits = null;

    /**
     * User ID
     * @var string
     */
    var $user_id = null;

    /**
     * User Name
     * @var string
     */
    var $user_name = null;

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    public function Tablestudydetails(& $db) {
        parent::__construct('#__bsms_studies', 'id', $db);
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form `table_name.id`
     * where id is the value of the primary key of the table.
     *
     * @return      string
     * @since       1.6
     */
    protected function _getAssetName() {
        $k = $this->_tbl_key;
        return 'com_biblestudy.message.' . (int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return      string
     * @since       1.6
     */
    protected function _getAssetTitle() {
        $title = 'JBS Message: ' . $this->studytitle;
        return $title;
    }

    /**
     * Method to get the parent asset under which to register this one.
     * By default, all assets are registered to the ROOT node with ID 1.
     * The extended class can define a table and id to lookup.  If the
     * asset does not exist it will be created.
     *
     * @param   JTable   $table  A JTable object for the asset parent.
     * @param   integer  $id     Id to look up
     *
     * @return  integer
     *
     * @since   11.1
     */
    protected function _getAssetParentId($table = null, $id = null) {
        $asset = JTable::getInstance('Asset');
        $asset->loadByName('com_biblestudy');
        return $asset->id;
    }

}