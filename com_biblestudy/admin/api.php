<?php
/**
 * JBSM Component
 *
 * @package       JBSM.Framework
 *
 * @copyright (C) 2008 - 2015 JBSM Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          http://www.joomlabiblestudy.org
 **/
defined('_JEXEC') or die ();

if (defined('JBSM_LOADED'))
{
	return;
}

// Manually enable code profiling by setting value to 1
define ('JBSM_PROFILER', 0);

// Component name amd database prefix
define ('JBSM_COMPONENT_NAME', 'com_biblestudy');
define ('JBSM_COMPONENT_LOCATION', 'components');
define ('JBSM_NAME', substr(JBSM_COMPONENT_NAME, 4));

// Component paths
define ('JBSMPATH_COMPONENT_RELATIVE', JBSM_COMPONENT_LOCATION . '/' . JBSM_COMPONENT_NAME);
define ('JBSMPATH_SITE', JPATH_ROOT . '/' . JBSMPATH_COMPONENT_RELATIVE);
define ('JBSMPATH_ADMIN', JPATH_ADMINISTRATOR . '/' . JBSMPATH_COMPONENT_RELATIVE);
define ('JBSMPATH_MEDIA', JPATH_ROOT . '/media/' . JBSM_NAME);

// URLs
define ('KURL_COMPONENT', 'index.php?option=' . JBSM_COMPONENT_NAME);
define ('KURL_SITE', JUri::Root() . JBSMPATH_COMPONENT_RELATIVE . '/');
define ('KURL_MEDIA', JUri::Root() . 'media/' . JBSM_NAME . '/');

//$libraryFile = JPATH_PLATFORM . '/JBSM/bootstrap.php';
//
//if (is_file($libraryFile))
//{
//	require_once $libraryFile;
//}

// JBSM has been initialized
define ('JBSM_LOADED', 1);
