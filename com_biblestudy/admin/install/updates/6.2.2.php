<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

defined('_JEXEC') or die;

/**
 * Update for 6.2.2 class
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class Migration622
{
	/**
	 * Start of upgrade
	 *
	 * @param   JDatabaseDriver  $db  Data bass driver
	 *
	 * @return bool
	 *
	 * @since 9.0.0
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
				$query = "UPDATE `#__bsms_mediafiles` SET `params` = " . $db->quote($new_params) . " WHERE `id` = " .
					(int) $db->quote($result->id);

				if (!JBSMDbHelper::performDB($query, "Build 622: "))
				{
					return false;
				}
			}
		}

		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_version`
								(`id` INTEGER NOT NULL AUTO_INCREMENT,
								`version` VARCHAR(20) NOT NULL,
								`versiondate` DATE NOT NULL,
								`installdate` DATE NOT NULL,
								`build` VARCHAR(20) NOT NULL,
								`versionname` VARCHAR(40) NULL,
								PRIMARY KEY(`id`)) DEFAULT CHARSET=utf8;";

		if (!JBSMDbHelper::performDB($query, "Build 622: "))
		{
			return false;
		}

		$query = "INSERT INTO `#__bsms_version` SET `version` = '6.2.2', `installdate`='2010-10-25', `build`='622', " .
			"`versionname`='Judges', `versiondate`='2010-10-25'";

		if (!JBSMDbHelper::performDB($query, "Build 622: "))
		{
			return false;
		}

		return true;
	}
}
