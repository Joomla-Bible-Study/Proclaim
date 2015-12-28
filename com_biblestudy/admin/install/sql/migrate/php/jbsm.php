<?php
/**
 * BibleStudy Component
 *
 * @package       BibleStudy.Installer
 *
 * @copyright (C) 2008 - 2014 BibleStudy Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          http://www.joomlabiblestudy.org
 **/
defined('_JEXEC') or die ();

/**
 * Class JBSMMigratorJBSM
 */
class JBSMMigratorJBSM
{
	/**
	 * @return \JBSMMigratorJBSM|null
	 */
	public static function getInstance()
	{
		static $instance = null;
		if (!$instance)
		{
			$instance = new JBSMMigratorJBSM();
		}

		return $instance;
	}

	/**
	 * Detect JBSM 1.x version.
	 *
	 * @return  string  JBSM version or null.
	 */
	public function detect()
	{
		return null;
	}
}
