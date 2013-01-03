<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

JLoader::register('JBSMDbHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');

/**
 * Upgrade class for 6.1.2
 *
 * @package  BibleStudy.Admin
 * @since    7.0.2
 */
class JBS612Install
{

	/**
	 * Upgrade Function
	 *
	 * @return string
	 */
	public function upgrade612()
	{
		$query = JFactory::getDbo()
			->getQuery(true);
		$query
			->update('#__bsms_mediafiles')
			->set('params = ' . $query->q('player=2') . ', internal_viewer = ' . (int) $query->q('0'))
			->where('internal_view = ' . (int) $query->q('1'))
			->where('params IS NULL');

		if (!JBSMDbHelper::performdb($query, "Build 612: "))
		{
			return false;
		}

		return true;
	}

}
