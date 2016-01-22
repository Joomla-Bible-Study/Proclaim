<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;


/**
 * Fresh install class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class JBSMFreshInstall
{
	/**
	 * Install CSS on Fresh install
	 *
	 * @return boolean
	 */
	public static function installCSS()
	{
		$db    = JFactory::getDBO();
		$dest  = JPATH_SITE . '/media/com_biblestudy/css/site/biblestudy.css';
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__bsms_styles')
			->where($db->qn('filename') . '=' . $db->q('biblestudy'));
		$db->setQuery($query);
		$result = $db->loadObject();
		$newcss = $result->stylecode;

		if ($result)
		{
			if (JFile::write($dest, $newcss))
			{
				return true;
			}
		}

		return false;
	}

}
