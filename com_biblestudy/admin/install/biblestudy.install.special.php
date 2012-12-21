<?php

/**
 * Install special Scripts
 * @package BibleStudy.Admin
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Fresh install class
 * @package BibleStudy.Admin
 * @since   7.0.0
 */
class JBSMFreshInstall
{

	/**
	 * Install CSS on Fresh install
	 * @return boolean
	 */
	public static function installCSS()
	{
		$db = JFactory::getDBO();
		$dest = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'biblestudy.css';
		$query = 'SELECT * FROM #__bsms_styles WHERE `filename` = "biblestudy"';
		$db->setQuery($query);
		$result = $db->loadObject();
		$newcss = $result->stylecode;
		if (!$result)
		{
			return false;
		}
		else
		{
			if (!JFile::write($dest, $newcss))
			{
				return false;
			}
		}

		return true;
	}

}
