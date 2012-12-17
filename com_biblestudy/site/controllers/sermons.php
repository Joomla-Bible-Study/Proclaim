<?php

/**
 * Controller Sermons
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');

/**
 * Controller class for Sermons
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class BiblestudyControllerSermons extends JControllerLegacy {

    /**
     * Media Code
     * @var string
     */
    var $mediaCode;

    /**
     * Method to display the view
     * @access public
     */
    public function __construct() {
        parent::__construct();

        // Register Extra tasks
    }

    /**
     * Download?
	 * @todo Fix ASAP Broken
     */
    public function download() {
        $abspath = JPATH_SITE;
        require_once($abspath . DIRECTORY_SEPARATOR . 'components/com_biblestudy/lib/biblestudy.download.class.php');
        $input = new JInput;
        $task = $input->get('task');
        if ($task == 'download') {

            $downloader = new Dump_File();
            $downloader->download();

            die;
        }
    }

    /**
     * Avplayer
     * @return null
     */
    public function avplayer() {
        $input = new JInput;
        $task = $input->get('task');
        if ($task == 'avplayer') {
            $mediacode = $input->get('code','','string');
            $this->mediaCode = $mediacode;
            echo $mediacode;
            return null;
        }
    }

    /**
     * Add hits to the play count.
     */
    public function playHit() {
        require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.media.class.php');
        $getMedia = new jbsMedia();
        $input = new JInput;
        $getMedia->hitPlay($input->get('id','','int'));
    }

    /**
     * This function is supposed to generate the Media Player that is requested via AJAX
     * from the studiesList view "default.php". It has not been implemented yet, so its not used.
     * @return null
     * @deprecated since version 7.0.4
     */
    public function inlinePlayer() {
        echo('{m4vremote}http://www.livingwatersweb.com/video/John_14_15-31.m4v{/m4vremote}');
    }

}