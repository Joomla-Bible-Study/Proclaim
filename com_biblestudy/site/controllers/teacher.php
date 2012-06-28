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
 * studies Edit Controller
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class BiblestudyControllerTeacher extends JController {

    /**
     * constructor (registers additional tasks to methods)
     * @return void
     */
    function __construct() {
        parent::__construct();

        // Register Extra tasks
    }

    /**
     * display the edit form
     * @return void
     */
    function view() {
        JRequest::setVar('view', 'teacher');
        JRequest::setVar('layout', 'default');

        parent::display();
    }

}