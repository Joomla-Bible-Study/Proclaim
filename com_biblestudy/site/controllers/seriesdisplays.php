<?php

/**
 * Controller for SeriesDisplay
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');

/**
 * Controller for SeriesDisplay
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class BiblestudyControllerSeriesdisplays extends JControllerLegacy {

    /**
     * Set the var for mediaCode
     * @var string
     */
    var $mediaCode;

    /**
     * Method to display the view
     * @access public
     */
    public function display() {
        parent::display();
    }

    /**
     * Download funtion
     */
    public function download() {
        $abspath = JPATH_SITE;
        require_once($abspath . DIRECTORY_SEPARATOR . 'components/com_biblestudy/lib/biblestudy.download.class.php');
        $task = JRequest::getVar('task');
        if ($task == 'download') {
            $downloader = new Dump_File();
            $downloader->download();
            die;
        }
    }

    /**
     * AVPlyer Return system
     * @return string
     */
    public function avplayer() {
        $task = JRequest::getVar('task');
        if ($task == 'avplayer') {
            $mediacode = JRequest::getVar('code');
            $this->mediaCode = $mediacode;
            echo $mediacode;
            return;
        }
    }

    /**
     * This function is supposed to generate the Media Player that is requested via AJAX
     * from the studiesList view "default.php". It has not been implemented yet, so its not used.
     * @return unknown_type
     * @deprecated since version 7.0.4
     */
    public function inlinePlayer() {
        echo('{m4vremote}http://www.livingwatersweb.com/video/John_14_15-31.m4v{/m4vremote}');
    }

}