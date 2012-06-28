<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
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
     * @var string
     */
    var $published = 1;
    var $teacher_id = null;
    var $studydate = null;
    var $studynumber = null;
    var $booknumber = null;
    var $chapter_begin = null;
    var $chapter_end = null;
    var $verse_begin = null;
    var $verse_end = null;
    var $studytitle = null;
    var $studyintro = null;
    var $messagetype = null;
    var $series_id = null;
    var $studytext = null;
    var $topics_id = null;
    var $secondary_reference = null;
    var $media_hours = null;
    var $media_minutes = null;
    var $media_seconds = null;
    var $prod_cd = null;
    var $prod_dvd = null;
    var $server_cd = null;
    var $server_dvd = null;
    var $image_cd = null;
    var $image_dvd = null;
    var $booknumber2 = null;
    var $chapter_begin2 = null;
    var $chapter_end2 = null;
    var $verse_begin2 = null;
    var $verse_end2 = null;
    var $comments = null;
    var $hits = null;
    var $user_id = null;
    var $user_name = null;

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function Tablestudydetails(& $db) {
        parent::__construct('#__bsms_studies', 'id', $db);
    }

}