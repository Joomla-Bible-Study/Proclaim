<?php
/**
* @version $Id: biblestudy.version.php 1339 2011-01-07 04:42:20Z bcordis $
* Bible Studey Component
* @package BibleStudy
*
* @Copyright (C) 2008 - 2010 Joomla Bible Study Team All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.JoomlaBibleStudy.org
*
* Install Based on Kunena Component
* 
**/

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (BIBLESTUDY_PATH_ADMIN_LIB . DS . 'biblestudy.debug.php');

// use default translations if none are available
if (!defined('_BIBLESTUDY_INSTALLED_VERSION')) DEFINE('_BIBLESTUDY_INSTALLED_VERSION', 'Installed version');
if (!defined('_BIBLESTUDY_COPYRIGHT')) DEFINE('_BIBLESTUDY_COPYRIGHT', 'Copyright');
if (!defined('_BIBLESTUDY_LICENSE')) DEFINE('_BIBLESTUDY_LICENSE', 'License');

class CBiblestudyVersion {
	/**
	* Retrieve Bible Study version from manifest.xml
	*
	* @return string version
	*/	
	function versionXML()
	{
		if ($data = JApplicationHelper::parseXMLInstallFile(BIBLESTUDY_FILE_INSTALL)) {
			return $data['version'];
		}
		return 'ERROR';
	}
	
	/**
	* Retrieve installed Biblestudy version as array.
	*
	* @return array Contains fields: version, versiondate, build, versionname
	*/
	function versionArray()
	{
		static $biblestudyversion;

		if (!$biblestudyversion)
		{
			$biblestudy_db = &JFactory::getDBO();
			$versionTable = '#__bsms_version';
			$biblestudy_db->setQuery("SELECT version, versiondate, installdate, build, versionname FROM `{$versionTable}` ORDER BY id DESC", 0, 1);
			$biblestudyversion = $biblestudy_db->loadObject();
			if(!$biblestudyversion) {
				$biblestudyversion = new StdClass();
				$biblestudyversion->version = CBiblestudyVersion::versionXML();
				$biblestudyversion->versiondate = JText::_('JBS_CMN_UNKNOWN');
				$biblestudyversion->installdate = '0000-00-00';
				$biblestudyversion->build = '0000';
				$biblestudyversion->versionname = JText::_('JBS_CMN_NOT_INSTALLED');
			}
			$xmlversion = CBiblestudyVersion::versionXML();
			if($biblestudyversion->version != $xmlversion) {
				$biblestudyversion->version = CBiblestudyVersion::versionXML();
				$biblestudyversion->versionname = JText::_('JBS_CMN_NOT_UPGRADED');
			}
			$biblestudyversion->version = strtoupper($biblestudyversion->version);
		}
		return $biblestudyversion;
	}

	/** 
	* Retrieve installed Bible Study version as string.
	*
	* @return string "X.Y.Z | YYYY-MM-DD | BUILDNUMBER [versionname]"
	*/
	function version()
	{
		$version = CBiblestudyVersion::versionArray();
		return '<table><tr><td><strong>'.JText::_('JBS_CMN_JOOMLA_BIBLE_STUDY').'</strong></td></tr><tr><td>'.JText::_('JBS_CPL_CURRENT_VERSION').': '.$version->version.'</td></tr><tr><td>'.JText::_('JBS_CPL_DATE').': '.$version->versiondate.'</td></tr><tr><td>'.JText::_('JBS_CPL_BUILD').': '.$version->build.'</td></tr><tr><td>'.JText::_('JBS_CPL_VERSION_NAME').': '.$version->versionname.'</td></tr></table>';
	}

	/** 
	* Retrieve MySQL Server version.
	*
	* @return string MySQL version
	*/
	function MySQLVersion()
	{
		static $mysqlversion;
		if (!$mysqlversion)
		{
			$biblestudy_db = &JFactory::getDBO();
			$biblestudy_db->setQuery("SELECT VERSION() AS mysql_version");
			$mysqlversion = $biblestudy_db->loadResult();
			if (!$mysqlversion) $mysqlversion = 'unknown';
		}
		return $mysqlversion;
	}

	/**
	* Retrieve PHP Server version.
	*
	* @return string PHP version
	*/
	function PHPVersion()
	{
		return phpversion();
	}
}
?>
