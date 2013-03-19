<?php

/**
 * Core JBSMigration file
 *
 * @package    BibleStudy
 * @subpackage JBSMigration.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// no direct access
defined('_JEXEC') or die;

define('JSTART', '$j(document).ready( function() {');
define('JSTOP', '});');
addCSS();

// Joomla! 1.6 detection
if (!defined('JBSMIGRATION_JVERSION'))
{
	if (!version_compare(JVERSION, '1.6.0', 'ge'))
	{
		define('JBSMIGRATION_JVERSION', '15');
	}
	else
	{
		define('JBSMIGRATION_JVERSION', '16');
	}
}

if (JBSMIGRATION_JVERSION == '15')
{
// Require the base controller

	require_once(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'controller.php');

// Require specific controller if requested
	if ($controller = JRequest::getWord('controller'))
	{
		$path = JPATH_COMPONENT . DIRECTORY_SEPARATOR . $controller . '.php';
		if (file_exists($path))
		{
			require_once $path;
		}
		else
		{
			$controller = '';
		}
	}

// Create the controller
	$classname  = 'jbsmigrationController' . $controller;
	$controller = new $classname();
}
else
{
// Require the base controller
	jimport('joomla.application.component.controller');
	$controller = JController::getInstance('jbsmigration');
}
// Perform the Request task
$controller->execute(JRequest::getWord('task'));
//Redirect if set by the controller

$controller->redirect();

/**
 * Global css
 *
 * @since   7.0
 */
function addCSS()
{
	$doc = JFactory::getDocument();
	$doc->addStyleSheet(JURI::base() . 'components/com_jbsmigration/css/icons.css');
}