<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
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
		/**
		 * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
		 */
		if (!ini_get('safe_mode'))
		{
			set_time_limit(3000);
		}

		$dbhelper = new JBSMDbHelper;

		$tables = [
			['table' => '#__bsms_folders', 'field' => 'idx_state', 'type' => 'INDEX', 'command' => '( `published` )'],
			['table' => '#__bsms_folders', 'field' => 'idx_access', 'type' => 'INDEX', 'command' => '( `access` )'],
			['table' => '#__bsms_folders', 'field' => 'asset_id', 'type' => 'MODIFY', 'command' => 'INT( 10 ) UNSIGNED NOT NULL DEFAULT \'0\''],
			['table' => '#__bsms_folders', 'field' => 'access', 'type' => 'MODIFY', 'command' => 'INT( 10 ) UNSIGNED NOT NULL DEFAULT \'0\''],
			['table' => '#__bsms_folders', 'field' => 'published', 'type' => 'MODIFY', 'command' => 'TINYINT( 3 ) NOT NULL DEFAULT \'1\''],
			['table' => '#__bsms_folders', 'field' => 'id', 'type' => 'MODIFY', 'command' => 'INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT'],
			['table' => '#__bsms_folders', 'field' => 'published', 'type' => 'MODIFY', 'command' => 'TINYINT( 3 ) NOT NULL DEFAULT \'1\''],
			['table' => '#__bsms_folders', 'field' => '', 'type' => 'MODIFY', 'command' => 'TINYINT( 3 ) NOT NULL DEFAULT \'1\''],
			['table' => '#__bsms_folders', 'field' => 'published', 'type' => 'MODIFY', 'command' => 'TINYINT( 3 ) NOT NULL DEFAULT \'1\''],
			['table' => '#__bsms_media', 'field' => 'idx_state', 'type' => 'INDEX', 'command' => '( `published` )'],
			['table' => '#__bsms_media', 'field' => 'idx_access', 'type' => 'INDEX', 'command' => '( `access` )'],
			['table' => '#__bsms_media', 'field' => 'asset_id', 'type' => 'MODIFY', 'command' => 'INT( 10 ) UNSIGNED NOT NULL DEFAULT \'0\'' .
				' COMMENT \'FK to the #__assets table.\''],
			['table' => '#__bsms_media', 'field' => 'ordering', 'type' => 'COLUMN', 'command' => 'INT( 11 ) NOT NULL DEFAULT \'0\''],
			['table' => '#__bsms_media', 'field' => 'access', 'type' => 'MODIFY', 'command' => 'INT( 10 ) UNSIGNED NOT NULL DEFAULT \'0\''],
			['table' => '#__bsms_media', 'field' => 'published', 'type' => 'MODIFY', 'command' => 'TINYINT( 3 ) NOT NULL DEFAULT \'1\''],
			['table' => '#__bsms_media', 'field' => 'id', 'type' => 'MODIFY', 'command' => 'INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT'],
			['table' => '#__bsms_mimetype', 'field' => 'idx_state', 'type' => 'INDEX', 'command' => '( `published` )'],
			['table' => '#__bsms_mimetype', 'field' => 'idx_access', 'type' => 'INDEX', 'command' => '( `access` )'],
			['table' => '#__bsms_mimetype', 'field' => 'ordering', 'type' => 'COLUMN', 'command' => 'INT( 11 ) NOT NULL DEFAULT \'0\''],
			['table' => '#__bsms_mimetype', 'field' => 'asset_id', 'type' => 'MODIFY', 'command' => 'INT( 10 ) UNSIGNED NOT NULL DEFAULT \'0\'' .
				' COMMENT \'FK to the #__assets table.\''],
			['table' => '#__bsms_mimetype', 'field' => 'access', 'type' => 'MODIFY', 'command' => 'INT( 10 ) UNSIGNED NOT NULL DEFAULT \'0\''],
			['table' => '#__bsms_mimetype', 'field' => 'published', 'type' => 'MODIFY', 'command' => 'TINYINT( 3 ) NOT NULL DEFAULT \'1\''],
			['table' => '#__bsms_mimetype', 'field' => 'id', 'type' => 'MODIFY', 'command' => 'INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT'],
		];

		if (!$dbhelper->alterDB($tables, "Build 700: "))
		{
			return false;
		}

		return true;
	}
}
