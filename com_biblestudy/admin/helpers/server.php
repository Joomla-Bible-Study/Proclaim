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
		$query = 'select distinct * from #__bsms_servers where id = ' . $serverid;

		$db->setQuery($query);

		$tresult = $db->loadObject();

		return $tresult;
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
		$query = 'select distinct * from #__bsms_folders where id = ' . $folderId;

		$db->setQuery($query);

		$tresult = $db->loadObject();

		return $tresult;
	}

}
