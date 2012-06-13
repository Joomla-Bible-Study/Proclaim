<?php

/**
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Controller for MimeType
 * @package BibleStudy.Admin
 */
class BiblestudyControllerMimetype extends JControllerForm {

    /**
     * constructor (registers additional tasks to methods)
     * @return void
     */
    protected $view_list = 'mimetypes';

    function __construct() {
        parent::__construct();
    }

}