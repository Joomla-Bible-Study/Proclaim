<?php

/**
 * @version     $Id: admin.biblestudy.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

//No Direct Access
defined('_JEXEC') or die;


// Check for PHP4
if (defined('PHP_VERSION')) {
	$version = PHP_VERSION;
} elseif (function_exists('phpversion')) {
	$version = phpversion();
} else {
	// No version info. I'll lie and hope for the best.
	$version = '5.0.0';
}

// Old PHP version detected. EJECT! EJECT! EJECT!
if (!version_compare($version, '5.0.0', '>=')) {
	return JError::raise(E_ERROR, 500, 'PHP 4 is not supported by Joomla Bible Study');
}

define('JSTART', '$j(document).ready( function() {');
define('JSTOP', '});');
addCSS();
addJS();

jimport('joomla.application.component.controller');
$controller = JController::getInstance('biblestudy');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

/**
 * Global css
 *
 * @since   1.7.0
 */
function addCSS() {
	JHTML::stylesheet('general.css', JURI::base() . 'media/com_biblestudy/css/');
	JHTML::stylesheet('icons.css', JURI::base() . 'media/com_biblestudy/css/');
}

/**
 * Global JS
 *
 * @since   7.0
 */
function addJS() {
	JHTML::script('jquery.js', JURI::base() . 'media/com_biblestudy/js/');
	JHTML::script('noconflict.js', JURI::base() . 'media/com_biblestudy/js/');
	JHTML::script('jquery-ui.js', JURI::base() . 'media/com_biblestudy/js/ui/');
}