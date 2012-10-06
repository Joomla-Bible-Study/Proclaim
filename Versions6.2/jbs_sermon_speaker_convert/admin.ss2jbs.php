<?php
// no direct access
defined('_JEXEC') or die('Restricted access');


// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');

// Require specific controller if requested

$classname	= 'ss2jbsController';

$controller = new $classname( );


// Perform the Request task
$controller->execute( JRequest::getWord('task'));
//Redirect if set by the controller

$controller->redirect();
?>
