<?php
/**
 * BibleStudy Component
 *
 * @package       JBSM.Framework
 * @subpackage    JBSM
 *
 * @copyright (C) 2008 - 2015 BibleStudy Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          http://www.joomlabiblestudy.org
 **/
defined('_JEXEC') or die ();

use Joomla\Registry\Registry;

/**
 * class JBSM
 *
 * Main class for JBSM Forum which is always present if JBSM framework has been installed.
 *
 * This class can be used to detect and initialize JBSM framework and to make sure that your extension
 * is compatible with the current version.
 */
abstract class JBSM
{
	protected static $version = false;
	protected static $version_major = false;
	protected static $version_date = false;
	protected static $version_name = false;

	const PUBLISHED = 0;
	const UNAPPROVED = 1;
	const DELETED = 2;
	const TOPIC_DELETED = 3;
	const TOPIC_CREATION = 4;

	const MODERATOR = 1;
	const ADMINISTRATOR = 2;

	/**
	 * Check if JBSM is safe to be used.
	 *
	 * If installer is running, it's unsafe to use our framework. Files may be currently replaced with
	 * new ones and the database structure might be inconsistent. Using forum during installation will
	 * likely cause fatal errors and data corruption if you attempt to update objects in the database.
	 *
	 * Always detect JBSM in your code before you start using the framework:
	 *
	 * <code>
	 *    // Check if JBSM Forum has been installed and compatible with your code
	 *    if (class_exists('JBSM') && JBSM::installed() && JBSM::isCompatible('2.0.0')) {
	 *        // Initialize the framework (new in 2.0.0)
	 *        JBSM::setup();
	 *        // Start using the framework
	 *    }
	 * </code>
	 *
	 * @see JBSM::enabled()
	 * @see JBSM::isCompatible()
	 * @see JBSM::setup()
	 *
	 * @return boolean True if JBSM has been fully installed.
	 */
	public static function installed()
	{
		return !is_file(BIBLESTUDY_PATH_ADMIN . '/install.php') || self::isDev();
	}

	/**
	 * Checks if JBSM is safe to be used and online.
	 *
	 * It is a good practice to check if JBSM is online before displaying
	 * forum content to the user. It's even more important if you allow user to post
	 * or manipulate forum! By following this practice administrator can have single
	 * point which he can use to be sure that nobody has access to any data inside
	 * his forum.
	 *
	 * Use case: Administrator is upgrading his forum to the next major version and wants
	 * to be sure that everything works before putting forum back to online. He logs in
	 * and can see everything. For everyone else no forum related information is shown.
	 *
	 * <code>
	 * // Check if JBSM has been installed, online and compatible with your code
	 *    if (class_exists('JBSM') && JBSM::enabled() && JBSM::isCompatible('2.0.0')) {
	 *        // Initialize the framework (new in 2.0.0)
	 *        JBSM::setup();
	 *        // It's now safe to display something or to save Kunena objects
	 *}
	 * </code>
	 *
	 * @see JBSM::installed()
	 * @see JBSM::isCompatible()
	 * @see JBSM::setup()
	 *
	 * @param   boolean  $checkAdmin  True if administrator is considered as a special case.
	 *
	 * @return boolean True if online.
	 */
	public static function enabled($checkAdmin = true)
	{
		if (!JComponentHelper::isEnabled('com_biblestudy', true))
		{
			return false;
		}

		$config = JFactory::getConfig();

		return ($checkAdmin && self::installed() && JFactory::getUser()->id);
	}

	/**
	 * Initialize Kunena Framework.
	 *
	 * This function initializes Kunena Framework. Main purpose of this
	 * function right now is to make sure all the translations have been loaded,
	 * but later it may contain other initialization tasks.
	 *
	 * Following code gives an example how to create backwards compatible code.
	 * Normally I wouldn't bother supporting deprecated unstable releases.
	 *
	 * <code>
	 *    // We have already checked that Kunena 2.0+ has been installed and is online
	 *
	 *    if (JBSM::isCompatible('2.0.0')) {
	 *        JBSM::setup();
	 *    } else {
	 *        KunenaFactory::loadLanguage();
	 *    }
	 * </code>
	 *
	 * @see   JBSM::installed()
	 *
	 * Alternatively you could use method_exists() to check that the new API is in there.
	 *
	 * @since 9.0.0-BETA2
	 */
	public static function setup()
	{
		$config = JFactory::getConfig();

		// Setup output caching.
		$cache = JFactory::getCache('com_kunena', 'output');

		if (!$config->get('cache'))
		{
			$cache->setCaching(0);
		}

		$cache->setLifeTime($config->get('cache_time', 60));

		// Setup error logging.
		jimport('joomla.error.log');
		$options    = array('logger' => 'w3c', 'text_file' => 'kunena.php');
		$categories = array('kunena');
		$levels     = JDEBUG || $config->debug ? JLog::ALL :
			JLog::EMERGENCY | JLog::ALERT | JLog::CRITICAL | JLog::ERROR;
		JLog::addLogger($options, $levels, $categories);
	}

	/**
	 * Check if JBSM is compatible with your code.
	 *
	 * This function can be used to make sure that user has installed BibleStudy version
	 * that has been tested to work with your extension. All existing functions should
	 * be backwards compatible, but each release can add some new functionality, which
	 * you may want to use.
	 *
	 * <code>
	 *    if (JBSM::isCompatible('2.0.1')) {
	 *        // We can do it in the new way
	 *    } else {
	 *        // Use the old code instead
	 *    }
	 * </code>
	 *
	 * @see JBSM::installed()
	 *
	 * @param string $version Minimum required version.
	 *
	 * @return boolean Yes, if it is safe to use Kunena Framework.
	 */
	public static function isCompatible($version)
	{
		// If requested version is smaller than 2.0, it's not compatible
		if (version_compare($version, '2.0', '<'))
		{
			return false;
		}

		// Development version support.
		if ($version == '4.0')
		{
			return true;
		}

		// Check if future version is needed (remove GIT and DEVn from the current version)
		if (version_compare($version, preg_replace('/(-DEV\d*)?(-GIT)?/i', '', self::version()), '>'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Check if JBSM is running from a Git repository.
	 *
	 * Developers tend to do their work directly in the Git repositories instead of
	 * creating and installing new builds after every change. This function can be
	 * used to check the condition and make sure we do not break users repository
	 * by replacing files during upgrade.
	 *
	 * @return boolean True if Git repository is detected.
	 */
	public static function isDev()
	{
		if ('4.0.7' == '@' . 'jbsmversion' . '@')
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns the exact version from JBSM.
	 *
	 * @return string Version number.
	 */
	public static function version()
	{
		if (self::$version === false)
		{
			self::buildVersion();
		}

		return self::$version;
	}

	/**
	 * Returns major version number (2.0, 3.0, 3.1 and so on).
	 *
	 * @return string Major version in xxx.yyy format.
	 */
	public static function versionMajor()
	{
		if (self::$version_major === false)
		{
			self::buildVersion();
		}

		return self::$version_major;
	}

	/**
	 * Returns build date from JBSM (for Git today).
	 *
	 * @return string Date in yyyy-mm-dd format.
	 */
	public static function versionDate()
	{
		if (self::$version_date === false)
		{
			self::buildVersion();
		}

		return self::$version_date;
	}

	/**
	 * Returns codename from JBSM release.
	 *
	 * @return string Codename.
	 */
	public static function versionName()
	{
		if (self::$version_name === false)
		{
			self::buildVersion();
		}

		return self::$version_name;
	}

	/**
	 * Returns all version information together.
	 *
	 * @return object stdClass containing (version, major, date, name).
	 */
	public static function getVersionInfo()
	{
		$version          = new stdClass();
		$version->version = self::version();
		$version->major   = self::versionMajor(); // New in K2.0.0-BETA2
		$version->date    = self::versionDate();
		$version->name    = self::versionName();

		return $version;
	}

	/**
	 * Displays JBSM view/layout inside your extension.
	 *
	 * <code>
	 *
	 * </code>
	 *
	 * @param string         $viewName Name of the view.
	 * @param string         $layout   Name of the layout.
	 * @param null|string    $template Name of the template file.
	 * @param array|Registry $params   Extra parameters to control the model.
	 */
	public static function display($viewName, $layout = 'default', $template = null, $params = array())
	{
		// Filter input
		$viewName = preg_replace('/[^A-Z0-9_]/i', '', $viewName);
		$layout   = preg_replace('/[^A-Z0-9_]/i', '', $layout);
		$template = preg_replace('/[^A-Z0-9_]/i', '', $template);
		$template = $template ? $template : null;

		$view  = "JBSMView{$viewName}";
		$model = "JBSMModel{$viewName}";

		require_once BIBLESTUDY_PATH . '/views/landing/view.html.php';
		require_once BIBLESTUDY_PATH . '/models/landing.php';

		if (!class_exists($view))
		{
			$vpath = BIBLESTUDY_PATH . '/views/' . $viewName . '/view.html.php';

			if (!is_file($vpath))
			{
				return;
			}

			require_once $vpath;
		}

		if ($viewName != 'common' && !class_exists($model))
		{
			$mpath = BIBLESTUDY_PATH . '/models/' . $viewName . '.php';

			if (!is_file($mpath))
			{
				return;
			}

			require_once $mpath;
		}

		$view = new $view (array('base_path' => BIBLESTUDY_PATH));
		/** @var JBSMView $view */

		if ($params instanceof Registry)
		{
			// Do nothing
		}
		else
		{
			$params = new Registry($params);
		}

		$params->set('layout', $layout);

		// Push the model into the view (as default).
		$model = new $model();
		/** @var BibleStudyModel $model */
		$model->initialize($params);
		$view->setModel($model, true);

		// Flag view as being embedded
		$view->embedded = true;

		// Flag view as being teaser
		$view->teaser = $params->get('teaser', 0);

		// Render the view.
		$view->displayLayout($layout, $template);
	}

	// Internal functions

	/**
	 * Build Version info for display
	 */
	protected static function buildVersion()
	{
		if ('4.0.7' == '@' . 'jbsmversion' . '@')
		{
			$file          = JPATH_MANIFESTS . '/packages/pkg_biblestudy.xml';
			$manifest      = simplexml_load_file($file);
			self::$version = (string) $manifest->version . '-GIT';
		}
		else
		{
			self::$version = strtoupper('4.0.7');
		}

		self::$version_major = substr(self::$version, 0, 3);
		self::$version_date  = ('2015-11-16' == '@' . 'jbsmversiondate' . '@') ? JFactory::getDate()->format('Y-m-d') : '2015-11-16';
		self::$version_name  = ('Albareto' == '@' . 'jbsmversionname' . '@') ? 'Git Repository' : 'Albareto';
	}
}
