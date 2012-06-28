<?php

/**
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyControllercommentsedit extends JControllerForm {

    /**
     *
     * @var type
     */
    protected $view_list = 'commentslist';

    /**
     * constructor (registers additional tasks to methods)
     * @return void
     */
    function __construct() {
        parent::__construct();

        // Register Extra tasks
    }

}