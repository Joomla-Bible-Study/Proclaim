<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;

/**
 * Update for 6.2.2 class
 *
 * @package  BibleStudy.Admin
 * @since    8.1.0
 */
class Migration622
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
		$query = "SELECT `id`, `params` FROM `#__bsms_mediafiles` WHERE `params` LIKE '%podcast1%'";
		$db->setQuery($query);
		$results = $db->loadObjectList();

		if ($results)
		{
			foreach ($results AS $result)
			{
				$old_params = $result->params;
				$new_params = str_replace('podcast1', 'podcasts', $old_params);
				$query      = "UPDATE #__bsms_mediafiles SET `params` = " . $db->quote($new_params) . " WHERE `id` = " .
					(int) $db->quote($result->id);

				if (!JBSMDbHelper::performdb($query, "Build 622: "))
				{
					return false;
				}
			}
		}
		$query = "INSERT INTO #__bsms_version SET `version` = '6.2.2', `installdate`='2010-10-25', `build`='622', " .
			"`versionname`='Judges', `versiondate`='2010-10-25'";

		if (!JBSMDbHelper::performdb($query, "Build 622: "))
		{
			return false;
		}

		return true;
	}
}
