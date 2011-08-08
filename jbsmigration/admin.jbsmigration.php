<?php

/**
 * @version $Id: admin.jbsmigration.php 1 $
 * @package COM_JBSMIGRATION
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

// no direct access
defined('_JEXEC') or die('Restricted access');

define('JSTART', '$j(document).ready( function() {');
define('JSTOP', '});');


// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');



$controller = JRequest::getVar( 'controller' );

		
		$classname	= 'jbsmigrationController'.$controller;
		
		$controller = new $classname( );
		

		// Perform the Request task 
		$controller->execute( JRequest::getWord('task'));
		//Redirect if set by the controller 
		
		$controller->redirect();
