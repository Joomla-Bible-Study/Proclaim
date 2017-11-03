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
 * Inserts some css code to fix pagination problem and add a tag for the captcha of comments
 *
 * @package  Proclaim.Admin
 * @since    7.0.2
 */
class Migration702
{
	/**
	 * Update CSS for 7.0.2
	 *
	 * @param   JDatabaseDriver  $db  Joomla Data bass driver
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	public function up($db)
	{
		$db->setQuery("ALTER TABLE `#__bsms_folders` ADD INDEX `idx_state` ( `published` );
ALTER TABLE `#__bsms_folders` ADD INDEX `idx_access` ( `access` ),
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '1',
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;");

		$db->execute();

		$db->setQuery("ALTER TABLE `#__bsms_media` ADD INDEX `idx_state` ( `published` );
ALTER TABLE `#__bsms_media` ADD INDEX `idx_access` ( `access` ),
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.';
ALTER TABLE `#__bsms_media` ADD COLUMN `ordering` INT( 11 ) NOT NULL DEFAULT '0',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '1',
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;");

		$db->execute();

		$db->setQuery("ALTER TABLE `#__bsms_mimetype` ADD INDEX `idx_state` ( `published` );
ALTER TABLE `#__bsms_mimetype` ADD INDEX `idx_access` ( `access` );
ALTER TABLE `#__bsms_mimetype` ADD COLUMN `ordering` INT( 11 ) NOT NULL DEFAULT '0',
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '1',
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;");

		$db->execute();

		return true;
	}
}
