<?php


// no direct access
defined('_JEXEC') or die('Restricted access');

define('JSTART', '$j(document).ready( function() {');
define('JSTOP', '});');

require_once(JPATH_ADMINISTRATOR.DS.'includes'.DS.'toolbar.php');
$doc =& JFactory::getDocument();
$doc->addScript("includes" .DS. "js" .DS. "joomla.javascript.js");

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
);

if ( ! in_array($controller, $approvedControllers)) {
$controller = 'studieslist';

}

require_once JPATH_COMPONENT . DS . 'controllers' . DS . $controller . '.php';
}