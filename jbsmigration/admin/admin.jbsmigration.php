<?php

/**
 * @version $Id: admin.jbsmigration.php 1 $
 * @package COM_JBSMIGRATION
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

// no direct access
defined('_JEXEC') or die;

define('JSTART', '$j(document).ready( function() {');
define('JSTOP', '});');
addCSS();


// Require the base controller
jimport('joomla.application.component.controller');
$controller = JController::getInstance('jbsmigration');

// Perform the Request task
$controller->execute( JRequest::getWord('task'));
//Redirect if set by the controller

$controller->redirect();

/**
 * Global css
 *
 * @since   7.0
 */
function addCSS() {
	$doc = & JFactory::getDocument();
	$doc->addStyleSheet(JURI::base() . 'components/com_jbsmigration/css/icons.css');
}