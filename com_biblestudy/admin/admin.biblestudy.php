<?php

/**
 * @version     $Id: admin.biblestudy.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();


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
    $doc->addScript(JURI::base() . 'components/com_biblestudy/js/ui/jquery-ui.js');
}