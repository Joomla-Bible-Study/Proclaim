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
 * Update for 6.1.4 class
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class Migration614
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
		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_studytopics` (
				  `id` INT(3) NOT NULL AUTO_INCREMENT,
				  `study_id` INT(3) NOT NULL DEFAULT '0',
				  `topic_id` INT(3) NOT NULL DEFAULT '0',
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `id` (`id`),
				  KEY `id_2` (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

		if (!JBSMDbHelper::performDB($query, "Build 614: "))
		{
			return false;
		}

		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_timeset` (
                `timeset` VARCHAR(14) ,
                KEY `timeset` (`timeset`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		if (!JBSMDbHelper::performDB($query, "Build 614: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_teachers` MODIFY `title` VARCHAR(250)";

		if (!JBSMDbHelper::performDB($query, "Build 614: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_mediafiles` ADD COLUMN downloads INT(10) DEFAULT 0";

		if (!JBSMDbHelper::performDB($query, "Build 614: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_mediafiles` ADD COLUMN plays INT(10) DEFAULT 0";

		if (!JBSMDbHelper::performDB($query, "Build 614: "))
		{
			return false;
		}

		$query = $db->getQuery(true);
		$query->insert('#__bsms_timeset')->set('timeset = ' . 1281646339);

		if (!JBSMDbHelper::performDB($query, "Build 614: "))
		{
			return false;
		}

		// This updates the mediafiles table to reflect the new way of associating files to podcasts
		$query = $db->getQuery(true);
		$query->select('id, params, podcast_id')->from('#__bsms_mediafiles')->where('podcast_id > ' . 0);
		$db->setQuery($query);
		$db->execute(); /* Need this do to the getNumRows dos not execute the Query */
		$num_rows = $db->getNumRows();

		if ($num_rows > 0)
		{
			$results = $db->loadObjectList();

			foreach ($results as $result)
			{
				// Added the \n
				$podcast = 'podcasts=' . $result->podcast_id . '\n';
				$params  = $result->params;
				$update  = $podcast . ' ' . $params;
				$query   = $db->getQuery(true);
				$query->update('#__bsms_mediafiles')->set('params = ' . $db->q($update) . ', podcast_id = ' . 0)->where('id = ' . (int) $result->id);

				if (!JBSMDbHelper::performDB($query, "Build 614: "))
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

		if (!JBSMDbHelper::performDB($query, "Build 614: "))
		{
			return false;
		}

		$query = "INSERT INTO `#__bsms_version` SET `version` = '6.2.0', `installdate`='2010-09-06', " .
			"`build`='614', `versionname`='Deuteronomy', `versiondate`='2010-09-06'";

		if (!JBSMDbHelper::performDB($query, "Build 614: "))
		{
			return false;
		}

		return true;
	}
}
