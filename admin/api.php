<?php
/**
 * Core Admin BibleStudy file
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

defined('_JEXEC') or die;

if (defined('JBSM_LOADED'))
{
	return;
}

// Manually enable code profiling by setting value to 1
define('JBSM_PROFILER', 0);

// Version information
define('BIBLESTUDY_VERSION', '9.2.8');
define('BIBLESTUDY_VERSION_UPDATEFILE', 'JBS Version ' . BIBLESTUDY_VERSION);

// Default values
define('BIBLESTUDY_COMPONENT_NAME', 'com_biblestudy');
define('BIBLESTUDY_LANGUAGE_DEFAULT', 'english');
define('BIBLESTUDY_TEMPLATE_DEFAULT', 'default');

// File system paths
define('BIBLESTUDY_COMPONENT_RELPATH', 'components' . DIRECTORY_SEPARATOR . BIBLESTUDY_COMPONENT_NAME);

// Root system paths
define('BIBLESTUDY_ROOT_PATH', JPATH_ROOT);
define('BIBLESTUDY_ROOT_PATH_ADMIN', JPATH_ADMINISTRATOR);
define('BIBLESTUDY_MEDIA_PATH', JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy');
define('BIBLESTUDY_PATH_IMAGES', BIBLESTUDY_MEDIA_PATH . DIRECTORY_SEPARATOR . 'images');

// Site Component paths
define('BIBLESTUDY_PATH', JPATH_SITE . DIRECTORY_SEPARATOR . BIBLESTUDY_COMPONENT_RELPATH);
define('BIBLESTUDY_PATH_LIB', BIBLESTUDY_PATH . DIRECTORY_SEPARATOR . 'lib');
define('BIBLESTUDY_PATH_TEMPLATE', BIBLESTUDY_PATH . DIRECTORY_SEPARATOR . 'template');
define('BIBLESTUDY_PATH_TEMPLATE_DEFAULT', BIBLESTUDY_PATH_TEMPLATE . DIRECTORY_SEPARATOR . BIBLESTUDY_TEMPLATE_DEFAULT);
define('BIBLESTUDY_PATH_HELPERS', BIBLESTUDY_PATH . DIRECTORY_SEPARATOR . 'helpers');
define('BIBLESTUDY_PATH_MODELS', BIBLESTUDY_PATH . DIRECTORY_SEPARATOR . 'models');
define('BIBLESTUDY_PATH_TABLES', BIBLESTUDY_PATH . DIRECTORY_SEPARATOR . 'tables');

// Admin Component paths
define('BIBLESTUDY_PATH_ADMIN', BIBLESTUDY_ROOT_PATH_ADMIN . DIRECTORY_SEPARATOR . BIBLESTUDY_COMPONENT_RELPATH);
define('BIBLESTUDY_PATH_ADMIN_LIB', BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'lib');
define('BIBLESTUDY_PATH_ADMIN_HELPERS', BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'helpers');
define('BIBLESTUDY_PATH_ADMIN_LANGUAGE', BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'language');
define('BIBLESTUDY_PATH_ADMIN_INSTALL', BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'install');
define('BIBLESTUDY_PATH_ADMIN_IMAGES', BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'images');
define('BIBLESTUDY_PATH_ADMIN_MODELS', BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'models');
define('BIBLESTUDY_PATH_ADMIN_TABLES', BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'tables');

// Addons paths
define('BIBLESTUDY_PATH_ADMIN_ADDON', BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'addons');

define('BIBLESTUDY_FILE_INSTALL', BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'biblestudy.xml');

// Mod Biblestudy
define('BIBLESTUDY_PATH_MOD', BIBLESTUDY_ROOT_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'mod_biblestudy');

// Minimum version requirements
define('BIBLESTUDY_MIN_PHP', '7.4.1');
define('BIBLESTUDY_MIN_MYSQL', '5.1');

// Time related
define('BIBLESTUDY_SECONDS_IN_HOUR', 3600);
define('BIBLESTUDY_SECONDS_IN_YEAR', 31536000);

// Database defines
define('BIBLESTUDY_DB_MISSING_COLUMN', 1054);

// Load JBSM Class
JLoader::discover('JBSM', BIBLESTUDY_PATH_LIB, 'true', 'true');
JLoader::discover('JBSM', BIBLESTUDY_PATH_ADMIN_LIB, 'true', 'true');
JLoader::discover('JBSM', BIBLESTUDY_PATH_HELPERS, 'false', 'true');
JLoader::discover('Table', BIBLESTUDY_PATH_TABLES, 'false', 'true');
JLoader::discover('JBSM', BIBLESTUDY_PATH_ADMIN_HELPERS, 'false', 'true');
JLoader::discover('JBSM', BIBLESTUDY_PATH_ADMIN_ADDON, 'false', 'true');
JLoader::discover('Table', BIBLESTUDY_PATH_ADMIN_TABLES, 'false', 'true');
JHtml::addIncludePath(BIBLESTUDY_PATH_ADMIN_HELPERS . '/html/');

// Fixes Router overrider.
JLoader::register('JBSMHelperRoute', BIBLESTUDY_PATH_HELPERS . '/route.php', true);

// If phrase is not found in specific language file, load english language file:
$language = Factory::getLanguage();
$language->load('com_biblestudy', BIBLESTUDY_PATH_ADMIN, 'en-GB', true);
$language->load('com_biblestudy', BIBLESTUDY_PATH_ADMIN, null, true);

// Component debugging
//if (BibleStudyHelper::debug() === '1' || Factory::getApplication()->input->getInt('jbsmdbg', '0') === '1')
//{
//	define('JBSMDEBUG', 1);
//}
//else
//{
//	define('JBSMDEBUG', 0);
//}

// Include the JLog class.
jimport('joomla.log.log');
Log::addLogger(
	array(
		'text_file' => 'com_biblestudy.errors.php'
	),
	Log::ALL,
	'com_biblestudy'
);

// JBSM has been initialized
define('JBSM_LOADED', 1);

/**
 * Method to discover classes of a given type in a given path.
 *
 * @param   string   $classPrefix  The class name prefix to use for discovery.
 * @param   string   $parentPath   Full path to the parent folder for the classes to discover.
 * @param   boolean  $force        True to overwrite the autoload path value for the class if it already exists.
 * @param   boolean  $recurse      Recurse through all child directories as well as the parent path.
 *
 * @return  void
 *
 * @since   1.7.0
 */
function discover($classPrefix, $parentPath, $force = true, $recurse = false)
{
	try
	{
		if ($recurse)
		{
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($parentPath),
				RecursiveIteratorIterator::SELF_FIRST
			);
		}
		else
		{
			$iterator = new DirectoryIterator($parentPath);
		}

		/** @type  $file  DirectoryIterator */
		foreach ($iterator as $file)
		{
			$fileName = $file->getFilename();
			var_dump($fileName, true);

			// Only load for php files.
			if ($file->isFile() && $file->getExtension() === 'php')
			{
				// Get the class name and full path for each file.
				$class = $classPrefix . preg_replace('#\.php$#', '', $fileName);

					JLoader::register($class, $file->getPath() . '/' . $fileName);
			}
		}
	}
	catch (UnexpectedValueException $e)
	{
		// Exception will be thrown if the path is not a directory. Ignore it.
	}
}
