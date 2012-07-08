<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

// This is the popup window for the teachings.  We could put anything in this window.
//TODO Need to Clean this up and rework to be proper Joomla calls bcc
/**
 * Popup Jview
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyViewpopup extends JView {

    /**
     * Dispay function
     * @param string $tpl
     */
    function display($tpl = null) {

        parent::display($tpl);
    }

//end of display function

    /**
     * Set Titles
     * @param string $text
     * @param string $media
     * @param string $scripture
     * @param string $date
     * @param string $length
     * @return object
     */
    function titles($text, $media, $scripture, $date, $length) {
        if (isset($media->teachername)) {
            $text = str_replace('{{teacher}}', $media->teachername, $text);
        }
        if (isset($date)) {
            $text = str_replace('{{studydate}}', $date, $text);
        }
        if (isset($media->filename)) {
            $text = str_replace('{{filename}}', $media->filename, $text);
        }
        if (isset($media->studyintro)) {
            $text = str_replace('{{description}}', $media->studyintro, $text);
        }
        if (isset($length)) {
            $text = str_replace('{{length}}', $length, $text);
        }
        if (isset($media->studytitle)) {
            $text = str_replace('{{title}}', $media->studytitle, $text);
        }
        if (isset($scripture)) {
            $text = str_replace('{{scripture}}', $scripture, $text);
        }
        if (isset($this->teacherimage)) {
            $text = str_replace('{{teacherimage}}', $this->teacherimage, $text);
        }
        if (isset($media->series_text)) {
            $text = str_replace('{{series}}', $media->series_text, $text);
        }
        if (isset($media->series_thumbnail)) {
            $text = str_replace('{{series_thumbnail}}', $this->series_thumbnail, $text);
        }
        return $text;
    }

}

//end of class
