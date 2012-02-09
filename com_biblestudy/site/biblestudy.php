<?php

/**
 * @version $Id: biblestudy.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

define('JSTART', '$j(document).ready( function() {');
define('JSTOP', '});');

require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'toolbar.php');

// Require the base controller
jimport("joomla.application.component.controller");
$controller = JController::getInstance('biblestudy');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

//The Below is not required anymore, because joomla will handle this for us
//This is here because of a security flaw and was added as an extra measure of security
if ($controller = JRequest::getWord('controller')) {
    $approvedControllers = array(
        'studieslist',
        'studydetails',
        'serieslist',
        'seriesdetail',
        'teacherlist',
        'teacheredit',
        'teacherdisplay',
        'commentsedit',
        'commentslist',
        'landingpage',
        'mediafilesedit',
        'podcastedit',
        'studiesedit',
        'landingpage',
        'messages',
        'message'
    );

    if (!in_array($controller, $approvedControllers)) {
        $controller = 'studieslist';
    }

    require_once JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $controller . '.php';
    require_once JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'route.php';
}