<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

// Check for PHP4
if(defined('PHP_VERSION')) {
	$version = PHP_VERSION;
} elseif(function_exists('phpversion')) {
	$version = phpversion();
} else {
	// No version info. I'll lie and hope for the best.
	$version = '5.0.0';
}

// Old PHP version detected. EJECT! EJECT! EJECT!
if(!version_compare($version, '5.0.0', '>='))
{
	return JError::raise(E_ERROR, 500, 'PHP 4 is not supported by Joomla Bible Study');
}

define('JSTART', '$j(document).ready( function() {');
define('JSTOP', '});');
addLoadingDiv();
addCSS();
addJS();

// Joomla! 1.6 detection
jimport('joomla.filesystem.file');
if(!version_compare( JVERSION, '1.6.0', 'ge' )) {
	define('BIBLESTUDY_JVERSION','15');
} else {
	define('BIBLESTUDY_JVERSION','16');
}

if(!defined('BIBLESTUDYENGINE')) {
	define('BIBLESTUDYENGINE', 1); // Required for accessing Akeeba Engine's factory class
	define('BIBLESTUDYPLATFORM', 'joomla15'); // So that platform-specific stuff can get done!
}

// Setup Akeeba's ACLs, honoring laxed permissions in component's parameters, if set
if(BIBLESTUDY_JVERSION == '15')
{
// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');
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
}
else
{
jimport('joomla.application.component.controller');
$controller = JController::getInstance('biblestudy');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
}

/**
 * Adds a loading div for any ajax requests via JQuery
 * This will become obsolete if we move away from JQuery
 * 
 * @since   7.0
 */
function addLoadingDiv() {
    echo '
                <div id="loading">
                    <img src="' . JURI::base() . 'components/com_biblestudy/images/loading.gif."/>
                    <span id="loadingMsg">Loading...</span>
                </div>
                ';
}

/**
 * Global css
 *
 * @since   7.0
 */
function addCSS() {
    $doc = & JFactory::getDocument();
    $doc->addStyleSheet(JURI::base() . 'components/com_biblestudy/css/general.css');
    $doc->addStyleSheet(JURI::base() . 'components/com_biblestudy/css/icons.css');
}

/**
 * Global JS
 * 
 * @since   7.0
 */
function addJS() {
    $doc = & JFactory::getDocument();
    $doc->addScript(JURI::base() . 'components/com_biblestudy/js/jquery.js');
    $doc->addScript(JURI::base() . 'components/com_biblestudy/js/noconflict.js');
    $doc->addScript(JURI::base() . 'components/com_biblestudy/js/plugins/jquery.selectboxes.js');
}

?>