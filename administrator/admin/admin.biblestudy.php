<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

define('JSTART', '$j(document).ready( function() {');
define('JSTOP', '});');


// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');

// Require specific controller if requested
if($controller = JRequest::getWord('controller')) {
	require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
}

$controller = JRequest::getVar( 'controller' );

		
		$classname	= 'biblestudyController'.$controller;
		
		$controller = new $classname( );
		

		// Perform the Request task 
		$controller->execute( JRequest::getWord('task'));
		//Redirect if set by the controller 
		
		$controller->redirect();
?>
