<?php
/**
* @version $Id: biblestudy.defines.php 1339 2011-01-07 04:42:20Z bcordis $
* Bible Study Component
* @package Bible Study
* @Copyright (C) 2010 Joomla Bible Study All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.JoomlaBibleStudy.org
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
// Version information
define ('BIBLESTUDY_VERSION', '7.0.0');
define ('BIBLESTUDY_VERSION_DATE', '2010-08-04');
define ('BIBLESTUDY_VERSION_NAME', '2Samuel');
define ('BIBLESTUDY_VERSION_BUILD', '700');

// Default values
define('BIBLESTUDY_COMPONENT_NAME', 'com_biblestudy');
define('BIBLESTUDY_LANGUAGE_DEFAULT', 'english');
define('BIBLESTUDY_TEMPLATE_DEFAULT', 'default');

//Joomla Version
$joomlaversion = JVERSION;
$j16 = '1.6';
$is16 = substr_count(JVERSION,'1.6');
if (!$is16)
{
    define('JOOMLA_VERSION','5');
}
else
{
    define('JOOMLA_VERSION','6');
}

// File system paths
define('BIBLESTUDY_COMPONENT_RELPATH', 'components' .DS. BIBLESTUDY_COMPONENT_NAME);

define('BIBLESTUDY_ROOT_PATH', JPATH_ROOT);
define('BIBLESTUDY_ROOT_PATH_ADMIN', JPATH_ADMINISTRATOR);

define('BIBLESTUDY_PATH', JPATH_SITE .DS. BIBLESTUDY_COMPONENT_RELPATH);
define('BIBLESTUDY_PATH_LIB', BIBLESTUDY_PATH .DS. 'lib');
define('BIBLESTUDY_PATH_TEMPLATE', BIBLESTUDY_PATH .DS. 'template');
define('BIBLESTUDY_PATH_TEMPLATE_DEFAULT', BIBLESTUDY_PATH_TEMPLATE .DS. BIBLESTUDY_TEMPLATE_DEFAULT);

define('BIBLESTUDY_PATH_ADMIN', BIBLESTUDY_ROOT_PATH_ADMIN .DS. BIBLESTUDY_COMPONENT_RELPATH);
define('BIBLESTUDY_PATH_ADMIN_LIB', BIBLESTUDY_PATH_ADMIN .DS. 'lib');
define('BIBLESTUDY_PATH_ADMIN_HELPERS', BIBLESTUDY_PATH_ADMIN .DS. 'helpers');
define('BIBLESTUDY_PATH_ADMIN_LANGUAGE', BIBLESTUDY_PATH_ADMIN .DS. 'language');
define('BIBLESTUDY_PATH_ADMIN_INSTALL', BIBLESTUDY_PATH_ADMIN .DS. 'install');
define('BIBLESTUDY_PATH_ADMIN_IMAGES', BIBLESTUDY_PATH_ADMIN .DS. 'images');


define('BIBLESTUDY_FILE_INSTALL', BIBLESTUDY_PATH_ADMIN .DS. 'biblestudy.xml');

// URLs


// Constants

// Minimum version requirements
DEFINE('BIBLESTUDY_MIN_PHP',   '5.0.3');
DEFINE('BIBLESTUDY_MIN_MYSQL', '4.1.19');

// Time related
define ('BIBLESTUDY_SECONDS_IN_HOUR', 3600);
define ('BIBLESTUDY_SECONDS_IN_YEAR', 31536000);

// Database defines
define ('BIBLESTUDY_DB_MISSING_COLUMN', 1054);

?>
