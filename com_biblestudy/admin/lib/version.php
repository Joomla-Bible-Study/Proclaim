<?php
/**
 * Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 *
 * */
// No Direct Access
defined('_JEXEC') or die;

// Use default translations if none are available
if (!defined('_BIBLESTUDY_INSTALLED_VERSION'))
{
	define('_BIBLESTUDY_INSTALLED_VERSION', 'Installed version');
}

if (!defined('_BIBLESTUDY_COPYRIGHT'))
{
	define('_BIBLESTUDY_COPYRIGHT', 'Copyright');
}

if (!defined('_BIBLESTUDY_LICENSE'))
{
	define('_BIBLESTUDY_LICENSE', 'License');
}

/**
 * BibleStudy Version Class
 *
 * @package  Proclaim.Admin
 * @since    7.0.2
 *
 * @use      This is used by biblestudy.debug.php
 */
class JBSMBiblestudyVersion
{
	/**
	 * Retrieve Bible Study version from manifest.xml
	 *
	 * @return string version
	 *
	 * @since 9.0.0
	 */
	public function versionXML()
	{
		$data = JInstaller::parseXMLInstallFile(BIBLESTUDY_FILE_INSTALL);

		if ($data)
		{
			return $data['version'];
		}

		return 'ERROR';
	}

	/**
	 * Retrieve installed Bible Study version as string.
	 *
	 * @return string "X.Y.Z | YYYY-MM-DD | BUILDNUMBER [versionname]"
	 *
	 * @since 9.0.0
	 */
	public function version()
	{
		$version = self::versionObject();

		return '<p><strong>' . JText::_('JBS_CMN_JOOMLA_BIBLE_STUDY') . '</strong><br>' . JText::_('JBS_CPL_CURRENT_VERSION')
		. ': ' . $version->version . '<br>' . JText::_('JBS_CPL_DATE') . ': ' . $version->versiondate . '</p>';
	}

	/**
	 * Retrieve installed Biblestudy version as array.
	 *
	 * @return object Contains fields: version, versiondate, build, versionname
	 *
	 * @since 9.0.0
	 */
	public function versionObject()
	{
		static $biblestudyversion;
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__extensions')->where('element = ' . $db->q('com_biblestudy'));
		$db->setQuery($query, 0, 1);
		$extension                      = $db->loadObject();
		$manifestvariable               = json_decode($extension->manifest_cache);
		$biblestudyversion->version     = $manifestvariable->version;
		$biblestudyversion->versiondate = $manifestvariable->creationDate;

		return $biblestudyversion;
	}

	/**
	 * Retrieve MySQL Server version.
	 *
	 * @return string MySQL version
	 *
	 * @since 9.0.0
	 */
	public function MySQLVersion()
	{
		static $mysqlversion;

		if (!$mysqlversion)
		{
			$biblestudy_db = JFactory::getDbo();
			$biblestudy_db->setQuery("SELECT VERSION() AS mysql_version");
			$mysqlversion = $biblestudy_db->loadResult();

			if (!$mysqlversion)
			{
				$mysqlversion = 'unknown';
			}
		}

		return $mysqlversion;
	}

	/**
	 * Retrieve PHP Server version.
	 *
	 * @return string PHP version
	 *
	 * @since 9.0.0
	 */
	public function PHPVersion()
	{
		return phpversion();
	}
}
