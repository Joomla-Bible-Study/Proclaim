<?php


// no direct access
defined('_JEXEC') or die('Restricted access');

define('JSTART', '$j(document).ready( function() {');
define('JSTOP', '});');

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
'landingpage'
);  //santon 2010-12-08: some obsolete?

if ( ! in_array($controller, $approvedControllers)) {
$controller = 'studieslist';

}

require_once JPATH_COMPONENT . DS . 'controllers' . DS . $controller . '.php';
}
/*
// Require specific controller if requested
	if($controller = JRequest::getWord('controller')) 
	{
	
	 $controllercheck = array('studieslist','studydetails','serieslist','seriesdetail','teacherlist', 'teacheredit', 'teacherdisplay', 'commentsedit', 'commentslist', 'landingpage', 'mediafilesedit', 'podcastedit', 'studiesedit');  //santon 2010-12-08: some obsolete?
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

*/
// Create the controller
//

	//$classname	= 'biblestudyController'.$controller;
	//dump ($classname, 'controller');
//	
	//$controller = new $classname( );
	//dump ($controller, 'controller: ');
	// Perform the Request task
	//$controller->execute( JRequest::getWord('task'));
	
	// Redirect if set by the controller
	//$controller->redirect();

?>
