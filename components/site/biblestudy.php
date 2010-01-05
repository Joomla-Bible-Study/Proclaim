<?php


// no direct access
defined('_JEXEC') or die('Restricted access');

define('JSTART', '$j(document).ready( function() {');
define('JSTOP', '});');

// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');


// Require specific controller if requested
	if($controller = JRequest::getWord('controller')) 
	{
	/*	if ($controller == 'studielist' || $controller == 'studydetails' || $controller == 'serieslist' || $controller == 'seriesdetail' 
	|| $controller == 'teacherlist' || $controller == 'teacheredit' || $controller == 'teacherdisplay' || $controller == 'commentsedit' 
	|| $controller == 'commentslist' || $controller == 'landingpage' || $controller == 'mediafilesedit' || $controller == 'podcastedit'
	 || $controller == 'studiesedit')
	 {
	 	$controller = $controller;
	 	
	 }
	 else
	 {
	 	$controller = 'studieslist';
	 }*/
	 $controllercheck = array('studieslist','studydetails','serieslist','seriesdetail','teacherlist', 'teacheredit', 'teacherdisplay', 'commentsedit', 'commentslist', 'landingpage', 'mediafilesedit', 'podcastedit', 'studiesedit');
	//dump ($controllercheck, 'controllercheck: ');
	$success = 0;
	foreach ($controllercheck as $c)
	{
		$checkit = strcmp($controller,$c); //dump ($c, 'checkit: ');
			if ($checkit == 0)
			{
				$success = 1;
			}
			else
			{
				$view = JRequest::getWord('view');
				$checkview = strcmp ($view, $c);
				
					if ($checkview == 0)
					{
						$controller = $view;
					}
					else
					{
						$controller = 'studieslist';
					}
			}
	}
	if ($success == 1)
	{
		$controller = $controller;
	}
	
	require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
	//dump ($controller, 'controller: ');
}	


// Create the controller
//
	$classname	= 'biblestudyController'.$controller;
//	dump ($controller, 'controller: ');
	$controller = new $classname( );
	
	// Perform the Request task
	$controller->execute( JRequest::getWord('task'));
	
	// Redirect if set by the controller
	$controller->redirect();

?>
