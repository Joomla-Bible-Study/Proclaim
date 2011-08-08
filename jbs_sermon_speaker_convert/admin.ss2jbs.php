<?php

/**
 * @version $Id: admin.ss2jbs.php 1 $
 * @package BibleStudy SermonSpeaker Converter
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

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