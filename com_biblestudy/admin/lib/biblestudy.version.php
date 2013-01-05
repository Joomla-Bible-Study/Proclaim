<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 *
 * */
// No Direct Access
defined('_JEXEC') or die;

require_once BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.debug.php';

// @todo do we use this file now????
// use default translations if none are available
if (!defined('_BIBLESTUDY_INSTALLED_VERSION'))
{
	DEFINE('_BIBLESTUDY_INSTALLED_VERSION', 'Installed version');
}
if (!defined('_BIBLESTUDY_COPYRIGHT'))
{
	DEFINE('_BIBLESTUDY_COPYRIGHT', 'Copyright');
}
if (!defined('_BIBLESTUDY_LICENSE'))
{
	DEFINE('_BIBLESTUDY_LICENSE', 'License');
}

/**
 * BibleSutdy Version Class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.2
 */
class CBiblestudyVersion
{

	/**
	 * Retrieve Bible Study version from manifest.xml
	 *
	 * @return string version
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
	 * Retrieve installed Biblestudy version as array.
	 *
	 * @return object Contains fields: version, versiondate, build, versionname
	 */
	public function versionObject()
	{
		static $biblestudyversion;
		$db    = JFactory::getDBO();
		$query = 'SELECT * FROM #__extensions WHERE element = "com_biblestudy" LIMIT 1';
		$db->setQuery($query);
		$extension                      = $db->loadObject();
		$manifestvariable               = json_decode($extension->manifest_cache);
		$biblestudyversion->version     = $manifestvariable->version;
		$biblestudyversion->versiondate = $manifestvariable->creationDate;

		return $biblestudyversion;
	}

	/**
	 * Retrieve installed Bible Study version as string.
	 *
	 * @return string "X.Y.Z | YYYY-MM-DD | BUILDNUMBER [versionname]"
	 */
	public function version()
	{
		$version = self::versionObject();

		return '<table><tr><td><strong>' . JText::_('JBS_CMN_JOOMLA_BIBLE_STUDY') . '</strong></td></tr><tr><td>' . JText::_('JBS_CPL_CURRENT_VERSION')
			. ': ' . $version->version . '</td></tr><tr><td>' . JText::_('JBS_CPL_DATE') . ': ' . $version->versiondate . '</td></tr></table>';
	}

	/**
	 * Retrieve MySQL Server version.
	 *
	 * @return string MySQL version
	 */
	public function MySQLVersion()
	{
		static $mysqlversion;

		if (!$mysqlversion)
		{
			$biblestudy_db = & JFactory::getDBO();
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
	 */
	public function PHPVersion()
	{
		return phpversion();
	}

}
