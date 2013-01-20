<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Class Server Helper
 *
 * @package  BibleStudy.Admin
 * @since    8.0.0
 *
 * @todo Looks like this could go into the dbhelper.php file. TOM - what is this even for. I don't think I wrote this.
 */
class JBSMServer
{
	/**
	 * @var string
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Get Server
	 *
	 * @param   int  $serverid  Server ID
	 *
	 * @return object
	 */
	public static function getServer($serverid)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('distinct *')
			->from('#__bsms_servers')
			->where('id = ' . (int) $serverid);
		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	/**
	 * Get Folder
	 *
	 * @param   int  $folderId  Folder ID
	 *
	 * @return object
	 */
	public static function getFolder($folderId)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('distinct *')
			->from('#__bsms_folders')
			->where('id = ' . (int) $folderId);
		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

}
