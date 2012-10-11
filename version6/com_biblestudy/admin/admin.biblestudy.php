<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

define('JSTART', '$j(document).ready( function() {');
define('JSTOP', '});');


// Require the base controller
require_once (JPATH_COMPONENT . DS . 'controller.php');

// Require specific controller if requested
if (JRequest::getWord('controller')) {
    require_once (JPATH_COMPONENT . DS . 'controllers' . DS . $controller . '.php');
}

$controller = JRequest::getVar('controller');


$classname = 'biblestudyController' . $controller;

$controller = new $classname( );


// Perform the Request task
$controller->execute(JRequest::getWord('task'));
//Redirect if set by the controller

$controller->redirect();
?>
