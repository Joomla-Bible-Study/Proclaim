<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */

defined('_JEXEC') or die;

/**
 * Update for 6.1.1 class
 *
 * @package  BibleStudy.Admin
 * @since    9.0.0
 */
class Migration611
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
		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_locations` (
				`id` INT NOT NULL AUTO_INCREMENT,
				`location_text` VARCHAR(250) NULL,
				`published` TINYINT(1) NOT NULL DEFAULT '1',
				PRIMARY KEY (`id`) ) ENGINE=InnoDB CHARACTER SET `utf8`";

		if (!JBSMDbHelper::performDB($query, "Build 611: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_studies` ADD COLUMN show_level VARCHAR(100) NOT NULL DEFAULT '0' AFTER user_name";

		if (!JBSMDbHelper::performDB($query, "Build 611: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_studies` ADD COLUMN location_id INT(3) NULL AFTER show_level";

		if (!JBSMDbHelper::performDB($query, "Build 611: "))
		{
			return false;
		}

		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_version`
								(`id` INTEGER NOT NULL AUTO_INCREMENT,
								`version` VARCHAR(20) NOT NULL,
								`versiondate` DATE NOT NULL,
								`installdate` DATE NOT NULL,
								`build` VARCHAR(20) NOT NULL,
								`versionname` VARCHAR(40) NULL,
								PRIMARY KEY(`id`)) DEFAULT CHARSET=utf8;";

		if (!JBSMDbHelper::performDB($query, "Build 611: "))
		{
			return false;
		}

		$query = "INSERT INTO `#__bsms_version` SET `version` = '6.0.11', `installdate`='2008-10-22', `build`='611'," .
			"`versionname`='Leviticus', `versiondate`='2008-10-22'";

		if (!JBSMDbHelper::performDB($query, "Build 611: "))
		{
			return false;
		}

		return true;
	}
}
