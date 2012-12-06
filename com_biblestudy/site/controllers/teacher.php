<?php

/**
 * Teacher Controller
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Controller class for Teacher
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class BiblestudyControllerTeacher extends JControllerLegacy {

    /**
     * constructor (registers additional tasks to methods)
     * @return void
     */
    public function __construct() {
        parent::__construct();

        // Register Extra tasks
    }

    /**
     * display the edit form
     * @return void
     */
    public function view() {
        $input = new JInput;
        $input->set('view', 'teacher');
        $input->set('layout', 'default');

        parent::display();
    }

}