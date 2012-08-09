<?php

/**
 * Core Admin BibleStudy file
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_biblestudy')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

require_once(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'liveupdate' . DIRECTORY_SEPARATOR . 'liveupdate.php');
if (JRequest::getCmd('view', '') == 'liveupdate') {
    LiveUpdate::handleRequest();
    return;
}

require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.defines.php');

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

// Register helper class
JLoader::register('BibleStudyHelper', dirname(__FILE__) . '/helpers/biblestudy.php');

//define('JSTART', '$j(document).ready( function() {');
//define('JSTOP', '});');
addCSS();
addJS();

// Include dependencies
jimport('joomla.application.component.controller');

$controller = JController::getInstance('Biblestudy');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

/**
 * Global css
 *
 * @since   1.7.0
 */
function addCSS() {
    JHTML::stylesheet('general.css', JURI::root() . 'media/com_biblestudy/css/');
    JHTML::stylesheet('icons.css', JURI::root() . 'media/com_biblestudy/css/');
}

/**
 * Global JS
 *
 * @since   7.0
 */
function addJS() {
    JHTML::script('jquery.js', JURI::root() . 'media/com_biblestudy/js/');
    JHTML::script('noconflict.js', JURI::root() . 'media/com_biblestudy/js/');
    JHTML::script('jquery-ui.js', JURI::root() . 'media/com_biblestudy/js/ui/');
}