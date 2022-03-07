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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;

defined('_JEXEC') or die;

if (defined('JBSM_LOADED'))
{
	return;
}

$app = Factory::getApplication();

// Manually enable code profiling by setting value to 1
const JBSM_PROFILER = 0;

// Version information
const BIBLESTUDY_VERSION = '10.0.8';
const BIBLESTUDY_VERSION_UPDATEFILE = 'JBS Version ' . BIBLESTUDY_VERSION;

// Default values
const BIBLESTUDY_COMPONENT_NAME   = 'com_proclaim';
const BIBLESTUDY_LANGUAGE_DEFAULT = 'english';
const BIBLESTUDY_TEMPLATE_DEFAULT = 'default';

// File system paths
const BIBLESTUDY_COMPONENT_RELPATH = 'components' . DIRECTORY_SEPARATOR . BIBLESTUDY_COMPONENT_NAME;

// Root system paths
const BIBLESTUDY_ROOT_PATH       = JPATH_ROOT;
const BIBLESTUDY_ROOT_PATH_ADMIN = JPATH_ADMINISTRATOR;
const BIBLESTUDY_MEDIA_PATH  = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_proclaim';
const BIBLESTUDY_PATH_IMAGES = BIBLESTUDY_MEDIA_PATH . DIRECTORY_SEPARATOR . 'images';

// Site Component paths
const BIBLESTUDY_PATH = JPATH_SITE . DIRECTORY_SEPARATOR . BIBLESTUDY_COMPONENT_RELPATH;
const BIBLESTUDY_PATH_LIB = BIBLESTUDY_PATH . DIRECTORY_SEPARATOR . 'lib';
const BIBLESTUDY_PATH_TEMPLATE = BIBLESTUDY_PATH . DIRECTORY_SEPARATOR . 'template';
const BIBLESTUDY_PATH_TEMPLATE_DEFAULT = BIBLESTUDY_PATH_TEMPLATE . DIRECTORY_SEPARATOR . BIBLESTUDY_TEMPLATE_DEFAULT;
const BIBLESTUDY_PATH_HELPERS = BIBLESTUDY_PATH . DIRECTORY_SEPARATOR . 'helpers';
const BIBLESTUDY_PATH_MODELS = BIBLESTUDY_PATH . DIRECTORY_SEPARATOR . 'models';
const BIBLESTUDY_PATH_TABLES = BIBLESTUDY_PATH . DIRECTORY_SEPARATOR . 'tables';

// Admin Component paths
const BIBLESTUDY_PATH_ADMIN = BIBLESTUDY_ROOT_PATH_ADMIN . DIRECTORY_SEPARATOR . BIBLESTUDY_COMPONENT_RELPATH;
const BIBLESTUDY_PATH_ADMIN_LIB = BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'scr' . DIRECTORY_SEPARATOR . 'lib';
const BIBLESTUDY_PATH_ADMIN_HELPERS = BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'helpers';
const BIBLESTUDY_PATH_ADMIN_LANGUAGE = BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'language';
const BIBLESTUDY_PATH_ADMIN_INSTALL = BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'install';
const BIBLESTUDY_PATH_ADMIN_IMAGES = BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'images';
const BIBLESTUDY_PATH_ADMIN_MODELS = BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'scr' . DIRECTORY_SEPARATOR . 'model';
const BIBLESTUDY_PATH_ADMIN_TABLES = BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'scr' . DIRECTORY_SEPARATOR . 'table';

// Addons paths
const BIBLESTUDY_PATH_ADMIN_ADDON = BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'scr' . DIRECTORY_SEPARATOR . 'addons';

const BIBLESTUDY_FILE_INSTALL = BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'proclaim.xml';

// Mod Biblestudy
define('BIBLESTUDY_PATH_MOD', BIBLESTUDY_ROOT_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'mod_biblestudy');

// Minimum version requirements
const BIBLESTUDY_MIN_PHP = '7.4.1';
const BIBLESTUDY_MIN_MYSQL = '5.1';

// Time related
const BIBLESTUDY_SECONDS_IN_HOUR = 3600;
const BIBLESTUDY_SECONDS_IN_YEAR = 31536000;

// Database defines
const BIBLESTUDY_DB_MISSING_COLUMN = 1054;

HTMLHelper::addIncludePath(BIBLESTUDY_PATH_ADMIN_HELPERS . '/html');

// If phrase is not found in specific language file, load english language file:
$language = $app->getLanguage();
$language->load('com_proclaim', BIBLESTUDY_PATH_ADMIN, 'en-GB', true);
$language->load('com_proclaim', BIBLESTUDY_PATH_ADMIN, null, true);

// Component debugging
try
{
	if (CWMProclaimHelper::debug() === '1' || Factory::getApplication()->input->getInt('jbsmdbg', '0') === '1')
	{
		define('JBSMDEBUG', 1);
	}
	else
	{
		define('JBSMDEBUG', 0);
	}
}
catch (Exception $e)
{
}

// Include the JLog class.
jimport('joomla.log.log');
Log::addLogger(
	array(
		'text_file' => 'com_proclaim.errors.php'
	),
	Log::ALL,
	'com_proclaim'
);

// JBSM has been initialized
const JBSM_LOADED = 1;

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
