<?php
/**
* @version $Id: biblestudy.defines.php 1 $
* Bible Study Component
* @package Bible Study
* @Copyright (C) 2010 Joomla Bible Study All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.JoomlaBibleStudy.org
**/

// Version information
define ('BIBLESTUDY_VERSION', '6.2.0');
define ('BIBLESTUDY_VERSION_DATE', '2010-02-23');
define ('BIBLESTUDY_VERSION_NAME', 'Genesis');
define ('BIBLESTUDY_VERSION_BUILD', '769');

// Default values
define('BIBLESTUDY_COMPONENT_NAME', 'com_biblestudy');
define('BIBLESTUDY_LANGUAGE_DEFAULT', 'english');
define('BIBLESTUDY_TEMPLATE_DEFAULT', 'default');

$language =& JFactory::getLanguage();
$lang = $language->getBackwardLang();

define('BIBLESTUDY_LANGUAGE', $lang);

// File system paths
define('BIBLESTUDY_COMPONENT_RELPATH', 'components' .DS. BIBLESTUDY_COMPONENT_NAME);

define('BIBLESTUDY_ROOT_PATH', JPATH_ROOT);
define('BIBLESTUDY_ROOT_PATH_ADMIN', JPATH_ADMINISTRATOR);

define('BIBLESTUDY_PATH', BIBLESTUDY_ROOT_PATH .DS. BIBLESTUDY_COMPONENT_RELPATH);
define('BIBLESTUDY_PATH_LIB', BIBLESTUDY_PATH .DS. 'lib');
define('BIBLESTUDY_PATH_TEMPLATE', BIBLESTUDY_PATH .DS. 'template');
define('BIBLESTUDY_PATH_TEMPLATE_DEFAULT', BIBLESTUDY_PATH_TEMPLATE .DS. BIBLESTUDY_TEMPLATE_DEFAULT);

define('BIBLESTUDY_PATH_ADMIN', BIBLESTUDY_ROOT_PATH_ADMIN .DS. BIBLESTUDY_COMPONENT_RELPATH);
define('BIBLESTUDY_PATH_ADMIN_LIB', BIBLESTUDY_PATH_ADMIN .DS. 'lib');
define('BIBLESTUDY_PATH_ADMIN_LANGUAGE', BIBLESTUDY_PATH_ADMIN .DS. 'language');
define('BIBLESTUDY_PATH_ADMIN_INSTALL', BIBLESTUDY_PATH_ADMIN .DS. 'install');
define('BIBLESTUDY_PATH_ADMIN_IMAGES', BIBLESTUDY_PATH_ADMIN .DS. 'images');

// Kunena uploaded files directory
define('BIBLESTUDY_PATH_UPLOADED', BIBLESTUDY_ROOT_PATH . '/images');

// Files
define('BIBLESTUDY_FILE_LANGUAGE_DEFAULT', BIBLESTUDY_PATH_ADMIN_LANGUAGE .DS. 'biblestudy.' . BIBLESTUDY_LANGUAGE_DEFAULT . '.php');
define('BIBLESTUDY_FILE_LANGUAGE', BIBLESTUDY_PATH_ADMIN_LANGUAGE .DS. 'biblestudy.' . BIBLESTUDY_LANGUAGE . '.php');
define('BIBLESTUDY_FILE_INSTALL', BIBLESTUDY_PATH_ADMIN .DS. 'manifest.xml');

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
