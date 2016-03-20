<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;

/**
 * Update for 6.1.2 class
 *
 * @package  BibleStudy.Admin
 * @since    9.0.0
 */
class Migration612
{
	/**
	 * Start of upgrade
	 *
	 * @param   JDatabaseDriver  $db  Data bass driver
	 *
	 * @return bool
	 */
	public function up($db)
	{
		$query = $db->getQuery(true);
		$query
			->update('#__bsms_mediafiles')
			->set('params = ' . $query->q('player=2') . ', internal_viewer = ' . (int) $query->q('0'))
			->where('internal_view = ' . (int) $query->q('1'))
			->where('params IS NULL');

		if (!JBSMDbHelper::performDB($query, "Build 612: "))
		{
			return false;
		}

		return true;
	}
}
