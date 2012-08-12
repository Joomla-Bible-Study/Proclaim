<?php

/**
 * TeacherDisplay JTable
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Table Class for TeacherDisplay
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class Tableteacherdisplay extends JTable {

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
    var $published = null;

    /**
     * TeacherName
     * @var string
     */
    var $teachername = null;

    /**
     * Title
     * @var string
     */
    var $title = null;

    /**
     * Phone
     * @var string
     */
    var $phone = null;

    /**
     * Email
     * @var string
     */
    var $email = null;

    /**
     * Website
     * @var string
     */
    var $website = null;

    /**
     * Information
     * @var string
     */
    var $information = null;

    /**
     * Image
     * @var string
     */
    var $image = null;

    /**
     * Image hight
     * @var string
     */
    var $imageh = null;

    /**
     * Image Width
     * @var string
     */
    var $imagew = null;

    /**
     * Thumbnail
     * @var string
     */
    var $thumb = null;

    /**
     * Thumbnail Hight
     * @var string
     */
    var $thumbh = null;

    /**
     * Thumbnail Width
     * @var string
     */
    var $thumbw = null;

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function Tableteacherdisplay(& $db) {
        parent::__construct('#__bsms_teachers', 'id', $db);
    }

}
