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

use \Joomla\Registry\Registry;

/**
 * Update for 7.0.0 class
 *
 * @package  BibleStudy.Admin
 * @since    9.0.0
 */
class Migration700
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
		/**
		 * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
		 */
		if (!ini_get('safe_mode'))
		{
			set_time_limit(3000);
		}

		$dbhelper = new JBSMDbHelper;

		// Alter the admin table
		$tables = array(
			array('table' => '#__bsms_admin', 'field' => 'main', 'type' => 'DROP', 'command' => ''),
			array('table' => '#__bsms_admin', 'field' => 'podcast', 'type' => 'DROP', 'command' => ''),
			array('table' => '#__bsms_admin', 'field' => 'series', 'type' => 'DROP', 'command' => ''),
			array('table' => '#__bsms_admin', 'field' => 'study', 'type' => 'DROP', 'command' => ''),
			array('table' => '#__bsms_admin', 'field' => 'teacher', 'type' => 'DROP', 'command' => ''),
			array('table' => '#__bsms_admin', 'field' => 'media', 'type' => 'DROP', 'command' => ''),
			array('table' => '#__bsms_admin', 'field' => 'showhide', 'type' => 'DROP', 'command' => ''),
			array('table' => '#__bsms_admin', 'field' => 'download', 'type' => 'DROP', 'command' => ''), array(
				'table'   => '#__bsms_admin', 'field' => 'drop_tables', 'type' => 'ADD',
				'command' => 'int(3) NULL default "0"'
			), array(
				'table'   => '#__bsms_admin', 'field' => 'id', 'type' => 'MODIFY',
				'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'
			), array(
				'table'   => '#__bsms_comments', 'field' => 'id', 'type' => 'MODIFY',
				'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'
			), array(
				'table'   => '#__bsms_folders', 'field' => 'id', 'type' => 'MODIFY',
				'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'
			), array(
				'table'   => '#__bsms_media', 'field' => 'id', 'type' => 'MODIFY',
				'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'
			), array(
				'table' => '#__bsms_mediafiles', 'field' => 'podcast_id', 'type' => 'MODIFY', 'command' => 'varchar(50)'
			), array(
				'table'   => '#__bsms_mediafiles', 'field' => 'player', 'type' => 'ADD',
				'command' => 'INT(2) NULL DEFAULT NULL'
			), array(
				'table'   => '#__bsms_mediafiles', 'field' => 'popup', 'type' => 'ADD',
				'command' => 'INT(2) NULL DEFAULT NULL'
			), array(
				'table'   => '#__bsms_mediafiles', 'field' => 'id', 'type' => 'MODIFY',
				'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'
			), array(
				'table'   => '#__bsms_series', 'field' => 'id', 'type' => 'MODIFY',
				'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'
			), array(
				'table'   => '#__bsms_servers', 'field' => 'id', 'type' => 'MODIFY',
				'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'
			), array(
				'table'   => '#__bsms_servers', 'field' => 'published', 'type' => 'MODIFY',
				'command' => "tinyint(3) NOT NULL DEFAULT '1'"
			), array(
				'table'   => '#__bsms_comments', 'field' => 'study_id', 'type' => 'MODIFY',
				'command' => "int(11) NOT NULL DEFAULT '0'"
			), array(
				'table'   => '#__bsms_comments', 'field' => 'user_id', 'type' => 'MODIFY',
				'command' => "int(11) NOT NULL DEFAULT '0'"
			), array(
				'table'   => '#__bsms_comments', 'field' => 'full_name', 'type' => 'MODIFY',
				'command' => "varchar(50) NOT NULL DEFAULT ''"
			), array(
				'table'   => '#__bsms_comments', 'field' => 'user_email', 'type' => 'MODIFY',
				'command' => "varchar(100) NOT NULL DEFAULT ''"
			), array(
				'table'   => '#__bsms_comments', 'field' => 'comment_date', 'type' => 'MODIFY',
				'command' => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'"
			), array(
				'table'   => '#__bsms_folders', 'field' => 'foldername', 'type' => 'MODIFY',
				'command' => "varchar(250) NOT NULL DEFAULT ''"
			), array(
				'table'   => '#__bsms_folders', 'field' => 'folderpath', 'type' => 'MODIFY',
				'command' => "varchar(250) NOT NULL DEFAULT ''"
			), array(
				'table'   => '#__bsms_folders', 'field' => 'published', 'type' => 'MODIFY',
				'command' => "tinyint(3) NOT NULL DEFAULT '1'"
			), array(
				'table'   => '#__bsms_locations', 'field' => 'published', 'type' => 'MODIFY',
				'command' => "tinyint(3) NOT NULL DEFAULT '1'"
			), array(
				'table'   => '#__bsms_media', 'field' => 'media_image_name', 'type' => 'MODIFY',
				'command' => "varchar(250) NOT NULL DEFAULT ''"
			), array(
				'table'   => '#__bsms_media', 'field' => 'media_image_path', 'type' => 'MODIFY',
				'command' => "varchar(250) NOT NULL DEFAULT ''"
			), array(
				'table'   => '#__bsms_media', 'field' => 'media_alttext', 'type' => 'MODIFY',
				'command' => "varchar(250) NOT NULL DEFAULT ''"
			), array(
				'table'   => '#__bsms_media', 'field' => 'published', 'type' => 'MODIFY',
				'command' => "tinyint(3) NOT NULL DEFAULT '1'"
			), array('table' => '#__bsms_mediafiles', 'field' => 'mediacode', 'type' => 'MODIFY', 'command' => "text"),
			array(
				'table'   => '#__bsms_mediafiles', 'field' => 'link_type', 'type' => 'MODIFY',
				'command' => "char(1) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_mediafiles', 'field' => 'published', 'type' => 'MODIFY',
				'command' => "tinyint(3) NOT NULL DEFAULT '1'"
			), array(
				'table'   => '#__bsms_mediafiles', 'field' => 'downloads', 'type' => 'MODIFY',
				'command' => "int(10) DEFAULT '0'"
			), array(
				'table'   => '#__bsms_mediafiles', 'field' => 'plays', 'type' => 'MODIFY',
				'command' => "int(10) DEFAULT '0'"
			), array(
				'table'   => '#__bsms_message_type', 'field' => 'message_type', 'type' => 'MODIFY',
				'command' => "text NOT NULL"
			), array(
				'table'   => '#__bsms_message_type', 'field' => 'published', 'type' => 'MODIFY',
				'command' => "tinyint(3) NOT NULL DEFAULT '1'"
			), array(
				'table'   => '#__bsms_mimetype', 'field' => 'published', 'type' => 'MODIFY',
				'command' => "tinyint(3) NOT NULL DEFAULT '1'"
			), array('table' => '#__bsms_podcast', 'field' => 'description', 'type' => 'MODIFY', 'command' => "text"),
			array(
				'table'   => '#__bsms_podcast', 'field' => 'published', 'type' => 'MODIFY',
				'command' => "tinyint(3) NOT NULL DEFAULT '1'"
			), array(
				'table'   => '#__bsms_series', 'field' => 'published', 'type' => 'MODIFY',
				'command' => "tinyint(3) NOT NULL DEFAULT '1'"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'chapter_begin', 'type' => 'MODIFY',
				'command' => "int(3) DEFAULT '1'"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'verse_begin', 'type' => 'MODIFY',
				'command' => "int(3) DEFAULT '1'"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'chapter_end', 'type' => 'MODIFY',
				'command' => "int(3) DEFAULT '1'"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'verse_end', 'type' => 'MODIFY',
				'command' => "int(3) DEFAULT '1'"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'series_id', 'type' => 'MODIFY',
				'command' => "int(3) DEFAULT '0'"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'topics_id', 'type' => 'MODIFY',
				'command' => "int(3) DEFAULT '0'"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'show_level', 'type' => 'MODIFY',
				'command' => "varchar(100) NOT NULL DEFAULT '0'"
			), array(
				'table'   => '#__bsms_teachers', 'field' => 'teachername', 'type' => 'MODIFY',
				'command' => "varchar(250) NOT NULL DEFAULT ''"
			), array('table' => '#__bsms_teachers', 'field' => 'website', 'type' => 'MODIFY', 'command' => "text"),
			array(
				'table'   => '#__bsms_timeset', 'field' => 'backup', 'type' => 'ADD',
				'command' => "varchar(14) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_timeset', 'field' => 'timeset', 'type' => 'MODIFY',
				'command' => "varchar(14) NOT NULL DEFAULT ''"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media1_id', 'type' => 'DROP',
				'command' => "int(11) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media1_server', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media1_path', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media1_special', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			),
			array('table' => '#__bsms_studies', 'field' => 'media1_filename ', 'type' => 'DROP', 'command' => "text"),
			array('table' => '#__bsms_studies', 'field' => 'media1_size', 'type' => 'DROP', 'command' => "text"), array(
				'table'   => '#__bsms_studies', 'field' => 'media1_show', 'type' => 'DROP',
				'command' => "tinyint(1) DEFAULT '0'"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media2_id', 'type' => 'DROP',
				'command' => "int(11) DEFAULT '0'"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media2_server', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media2_path', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media2_special', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array('table' => '#__bsms_studies', 'field' => 'media2_filename', 'type' => 'DROP', 'command' => "text"),
			array('table' => '#__bsms_studies', 'field' => 'media2_size', 'type' => 'DROP', 'command' => "text"), array(
				'table'   => '#__bsms_studies', 'field' => 'media2_show', 'type' => 'DROP',
				'command' => "tinyint(1) DEFAULT '0'"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media3_id', 'type' => 'DROP',
				'command' => "int(11) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media3_server', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media3_path', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media3_special', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array('table' => '#__bsms_studies', 'field' => 'media3_filename', 'type' => 'DROP', 'command' => "text"),
			array('table' => '#__bsms_studies', 'field' => 'media3_size', 'type' => 'DROP', 'command' => "text"), array(
				'table'   => '#__bsms_studies', 'field' => 'media3_show', 'type' => 'DROP',
				'command' => "media3_show tinyint(1) DEFAULT '0'"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media4_id', 'type' => 'DROP',
				'command' => "int(11) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media4_server', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media4_path', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media4_special', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array('table' => '#__bsms_studies', 'field' => 'media4_filename', 'type' => 'DROP', 'command' => "text"),
			array('table' => '#__bsms_studies', 'field' => 'media4_size', 'type' => 'DROP', 'command' => "text"), array(
				'table'   => '#__bsms_studies', 'field' => 'media4_show', 'type' => 'DROP',
				'command' => "tinyint(1) DEFAULT '0'"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media5_id', 'type' => 'DROP',
				'command' => "int(11) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media5_server', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media5_path', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media5_special', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array('table' => '#__bsms_studies', 'field' => 'media5_filename', 'type' => 'DROP', 'command' => "text"),
			array('table' => '#__bsms_studies', 'field' => 'media5_size', 'type' => 'DROP', 'command' => "text"), array(
				'table'   => '#__bsms_studies', 'field' => 'media5_show', 'type' => 'DROP',
				'command' => "tinyint(1) DEFAULT '0'"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media6_id', 'type' => 'DROP',
				'command' => "int(11) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media6_server', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media6_path', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array(
				'table'   => '#__bsms_studies', 'field' => 'media6_special', 'type' => 'DROP',
				'command' => "varchar(250) DEFAULT NULL"
			), array('table' => '#__bsms_studies', 'field' => 'media6_filename', 'type' => 'DROP', 'command' => "text"),
			array('table' => '#__bsms_studies', 'field' => 'media6_size', 'type' => 'DROP', 'command' => "text"), array(
				'table'   => '#__bsms_studies', 'field' => 'media6_show', 'type' => 'DROP',
				'command' => "tinyint(1) DEFAULT '0'"
			),
		);

		if (!$dbhelper->alterDB($tables, "Build 700: "))
		{
			return false;
		}

		/* Start of Adding Assets and Access Columns */
		$table = '#__bsms_admin';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		if ($dbhelper->checkTables($table, 'drop_tables') == 'true')
		{
			$query = 'UPDATE `#__bsms_admin` SET `drop_tables` = 0 WHERE `id` = 1';

			if (!JBSMDbHelper::performdb($query, "Build 700: "))
			{
				return false;
			}
		}

		$table = '#__bsms_comments';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_folders';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_locations';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_media';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_mediafiles';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_message_type';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_mimetype';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_podcast';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_series';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_servers';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_share';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_studies';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_studytopics';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_teachers';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_templates';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}

		$table = '#__bsms_topics';

		if (!$this->addAssetColumn($table))
		{
			return false;
		}
		/* End of Adding Assets and Access Columns */

		/* Fix Mimtype Flash from old plyers */
		$query = "UPDATE `#__bsms_mimetype` SET `id` = '15', `mimetype` = 'video/x-flv .flv', " .
			"`mimetext` = ' Flash Video FLV', `published` = '1', `asset_id` = '3900', `access` = '1' WHERE `#__bsms_mimetype`.`id` = '15'";

		if (!JBSMDbHelper::performdb($query, "Build 700: "))
		{
			return false;
		}

		/* Update Show levels */
		$query = "UPDATE `#__bsms_studies` SET `access` = '1' WHERE `show_level` = '0'";

		if (!JBSMDbHelper::performdb($query, "Build 700: "))
		{
			return false;
		}
		$query = "UPDATE `#__bsms_studies` SET `access` = '2' WHERE `show_level` = '18'";

		if (!JBSMDbHelper::performdb($query, "Build 700: "))
		{
			return false;
		}
		$query = "UPDATE `#__bsms_studies` SET `access` = '2' WHERE `show_level` = '19'";

		if (!JBSMDbHelper::performdb($query, "Build 700: "))
		{
			return false;
		}
		$query = "UPDATE `#__bsms_studies` SET `access` = '2' WHERE `show_level` = '20'";

		if (!JBSMDbHelper::performdb($query, "Build 700: "))
		{
			return false;
		}
		$query = "UPDATE `#__bsms_studies` SET `access` = '3' WHERE `show_level` = '22'";

		if (!JBSMDbHelper::performdb($query, "Build 700: "))
		{
			return false;
		}
		$query = "UPDATE `#__bsms_studies` SET `access` = '3' WHERE `show_level` = '23'";

		if (!JBSMDbHelper::performdb($query, "Build 700: "))
		{
			return false;
		}
		$query = "UPDATE `#__bsms_studies` SET `access` = '3' WHERE `show_level` = '24'";

		if (!JBSMDbHelper::performdb($query, "Build 700: "))
		{
			return false;
		}

		/* Perform Mediafiles players. */
<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php
		$query = 'SELECT `id`, `params` FROM `#__bsms_mediafiles`';
=======
		$query = $db->getQuery(true);
		$query->select('id, params')
			->from('#__bsms_mediafiles');
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php
		$db->setQuery($query);
		$results = $db->loadObjectList();

		if ($results)
		{
			// Now run through all the results, pull out the media player and the popup type and move them to their respective db fields
			foreach ($results AS $result)
			{
				$registry = new Registry;

				// Fix incorrect params string literal
				$params = array();
				foreach (explode('\n', $result->params) as $param)
				{
					$param = explode('=',  str_replace('\n', '', trim($param)));
					$params[$param[0]] = $param[1];
				}

				$registry->loadArray($params);
				$params   = $registry;
				$player   = $params->get('player');

				$popup    = $params->get('internal_popup');
				$podcasts = $params->get('podcasts');

				if ($player)
				{
					if ($player == 2)
					{
						$player = 3;
					}
<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php
					elseif ($player == 100)
					{
						$player = 1;
					}
					$query = "UPDATE `#__bsms_mediafiles` SET `player` = " . $db->quote($player) . " WHERE `id` = " .
						$db->quote($result->id) . " LIMIT 1";
=======
					$query = $db->getQuery(true);
					$query->update('#__bsms_mediafiles')->set($db->qn('player') . ' = ' . $db->q($player))->where('id = ' . (int) $db->quote($result->id));
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php

					if (!JBSMDbHelper::performdb($query, "Build 700: "))
					{
						return false;
					}
				}
				if ($popup)
				{
<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php
					$query = "UPDATE `#__bsms_mediafiles` SET `popup` = " . $db->quote($popup) . " WHERE `id` = " .
						$db->quote($result->id) . " LIMIT 1";
=======
					$query = $db->getQuery(true);
					$query->update('#__bsms_mediafiles')->set($db->qn('popup') . ' = ' . $db->q($popup))->where('id = ' . (int) $db->quote($result->id));
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php

					if (!JBSMDbHelper::performdb($query, "Build 700: "))
					{
						return false;
					}
				}
				if ($podcasts)
				{
					$podcasts = str_replace('|', ',', $podcasts);
<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php
					$query    = "UPDATE `#__bsms_mediafiles` SET `podcast_id` = " . $db->quote($podcasts) . " WHERE `id` = " .
						$db->quote($result->id) . " LIMIT 1";
=======
					$query = $db->getQuery(true);
					$query->update('#__bsms_mediafiles')->set($db->qn('podcast_id') . ' = ' . $db->q($podcasts))->where('id = ' . (int) $db->quote($result->id));
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php

					if (!JBSMDbHelper::performdb($query, "Build 700: "))
					{
						return false;
					}
				}
				// Update the params to json
<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php
				$params2 = json_encode($params->toObject());

				$query = "UPDATE `#__bsms_mediafiles` SET `params` = " . $db->quote($params2) . " WHERE `id` = " .
					$db->quote($result->id) . " LIMIT 1";
=======
				$registry = new JRegistry;
				$registry->loadString($result->params);
				$params  = $registry;
				$params2 = $params->toObject();
				$params2 = json_encode($params2);
				$query = $db->getQuery(true);
				$query->update('#__bsms_mediafiles')->set($db->qn('params') . ' = ' . $db->q($params2))->where('id = ' . (int) $db->quote($result->id));
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php

				if (!JBSMDbHelper::performdb($query, "Build 700: "))
				{
					return false;
				}
			}
		}

<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php
=======
		$query = $db->getQuery(true);
		$query->update('#__bsms_order')->set('text = ' . $db->q('JBS_CMN_DESCENDING'))->where('id = 1');

		if (!JBSMDbHelper::performdb($query, "Build 700: "))
		{
			return false;
		}

		$query = $db->getQuery(true);
		$query->update('#__bsms_order')->set('text = ' . $db->q('JBS_CMN_DESCENDING'))->where('id = 2');

		if (!JBSMDbHelper::performdb($query, "Build 700: "))
		{
			return false;
		}

>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php
		$query = 'DROP TABLE IF EXISTS `#__bsms_books`';

		if (!JBSMDbHelper::performdb($query, "Build 700: "))
		{
			return false;
		}

		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_books` (
					  `id` INT(3) NOT NULL AUTO_INCREMENT,
					  `bookname` VARCHAR(250) DEFAULT NULL,
                                          `booknumber` INT(5) DEFAULT NULL,
					  `published` TINYINT(1) NOT NULL DEFAULT '1',
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

		if (!JBSMDbHelper::performdb($query, "Build 700: "))
		{
			return false;
		}

		$query = "INSERT INTO `#__bsms_books` (`id`, `bookname`, `booknumber`, `published`) VALUES
				 (1, 'JBS_BBK_GENESIS', 101, 1),
				 (2, 'JBS_BBK_EXODUS', 102, 1),
				 (3, 'JBS_BBK_LEVITICUS', 103, 1),
				 (4, 'JBS_BBK_NUMBERS', 104, 1),
				 (5, 'JBS_BBK_DEUTERONOMY', 105, 1) ,
				 (6, 'JBS_BBK_JOSHUA', 106, 1) ,
				 (7, 'JBS_BBK_JUDGES', 107, 1) ,
				 (8, 'JBS_BBK_RUTH', 108, 1) ,
				 (9, 'JBS_BBK_1SAMUEL', 109, 1) ,
				 (10, 'JBS_BBK_2SAMUEL', 110, 1) ,
				 (11, 'JBS_BBK_1KINGS', 111, 1) ,
				 (12, 'JBS_BBK_2KINGS', 112, 1) ,
				 (13, 'JBS_BBK_1CHRONICLES', 113, 1) ,
				 (14, 'JBS_BBK_2CHRONICLES', 114, 1) ,
				 (15, 'JBS_BBK_EZRA', 115, 1) ,
				 (16, 'JBS_BBK_NEHEMIAH', 116, 1) ,
				 (17, 'JBS_BBK_ESTHER', 117, 1) ,
				 (18, 'JBS_BBK_JOB', 118, 1) ,
				 (19, 'JBS_BBK_PSALM', 119, 1) ,
				 (20, 'JBS_BBK_PROVERBS', 120, 1) ,
				 (21, 'JBS_BBK_ECCLESIASTES', 121, 1) ,
				 (22, 'JBS_BBK_SONG_OF_SOLOMON', 122, 1) ,
				 (23, 'JBS_BBK_ISAIAH', 123, 1) ,
				 (24, 'JBS_BBK_JEREMIAH', 124, 1) ,
				 (25, 'JBS_BBK_LAMENTATIONS', 125, 1) ,
				 (26, 'JBS_BBK_EZEKIEL', 126, 1) ,
				 (27, 'JBS_BBK_DANIEL', 127, 1) ,
				 (28, 'JBS_BBK_HOSEA', 128, 1) ,
				 (29, 'JBS_BBK_JOEL', 129, 1) ,
				 (30, 'JBS_BBK_AMOS', 130, 1) ,
				 (31, 'JBS_BBK_OBADIAH', 131, 1) ,
				 (32, 'JBS_BBK_JONAH', 132, 1) ,
				 (33, 'JBS_BBK_MICAH', 133, 1) ,
				 (34, 'JBS_BBK_NAHUM', 134, 1) ,
				 (35, 'JBS_BBK_HABAKKUK', 135, 1) ,
				 (36, 'JBS_BBK_ZEPHANIAH', 136, 1),
				 (37, 'JBS_BBK_HAGGAI', 137, 1),
				 (38, 'JBS_BBK_ZECHARIAH', 138, 1),
				 (39, 'JBS_BBK_MALACHI', 139, 1),
				 (40, 'JBS_BBK_MATTHEW', 140, 1),
				 (41, 'JBS_BBK_MARK', 141, 1),
				 (42, 'JBS_BBK_LUKE', 142, 1),
				 (43, 'JBS_BBK_JOHN', 143, 1),
				 (44, 'JBS_BBK_ACTS', 144, 1),
				 (45, 'JBS_BBK_ROMANS', 145, 1),
				 (46, 'JBS_BBK_1CORINTHIANS', 146, 1),
				 (47, 'JBS_BBK_2CORINTHIANS', 147, 1),
				 (48, 'JBS_BBK_GALATIANS', 148, 1),
				 (49, 'JBS_BBK_EPHESIANS', 149, 1),
				 (50, 'JBS_BBK_PHILIPPIANS', 150, 1),
				 (51, 'JBS_BBK_COLOSSIANS', 151, 1),
				 (52, 'JBS_BBK_1THESSALONIANS', 152, 1),
				 (53, 'JBS_BBK_2THESSALONIANS', 153, 1),
				 (54, 'JBS_BBK_1TIMOTHY', 154, 1),
				 (55, 'JBS_BBK_2TIMOTHY', 155, 1),
				 (56, 'JBS_BBK_TITUS', 156, 1),
				 (57, 'JBS_BBK_PHILEMON', 157, 1),
				 (58, 'JBS_BBK_HEBREWS', 158, 1),
				 (59, 'JBS_BBK_JAMES', 159, 1),
				 (60, 'JBS_BBK_1PETER', 160, 1),
				 (61, 'JBS_BBK_2PETER', 161, 1),
				 (62, 'JBS_BBK_1JOHN', 162, 1),
				 (63, 'JBS_BBK_2JOHN', 163, 1),
				 (64, 'JBS_BBK_3JOHN', 164, 1),
				 (65, 'JBS_BBK_JUDE', 165, 1),
				 (66, 'JBS_BBK_REVELATION', 166, 1),
				 (67, 'JBS_BBK_TOBIT', 167, 1),
				 (68, 'JBS_BBK_JUDITH', 168, 1),
				 (69, 'JBS_BBK_1MACCABEES', 169, 1),
				 (70, 'JBS_BBK_2MACCABEES', 170, 1),
				 (71, 'JBS_BBK_WISDOM', 171, 1),
				 (72, 'JBS_BBK_SIRACH', 172, 1),
				 (73, 'JBS_BBK_BARUCH', 173, 1)";

		if (!JBSMDbHelper::performdb($query, "Build 700: "))
		{
			return false;
		}
		$query = "SHOW INDEX FROM #__bsms_timeset WHERE Key_name = 'idx_state'";
		$db->setQuery($query);

		if ($db->loadResult())
		{
			// Fix timeset primary key
			$query = "ALTER TABLE `#__bsms_timeset` DROP INDEX  `timeset` , ADD PRIMARY KEY (  `timeset` )";

			if (!JBSMDbHelper::performdb($query, "Build 700: "))
			{
				return false;
			}
		}

		// Fix studies params
<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php
		$query = "SELECT `id`, `params` FROM `#__bsms_studies`";
=======
		$query = $db->getQuery(true);
		$query->select('`id`, `params`')->from('#__bsms_studies');
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php
		$db->setQuery($query);
		$results = $db->loadObjectList();

		if ($results)
		{
			foreach ($results AS $result)
			{
				// Update the params to json
				$registry = new Registry;
				$registry->loadString($result->params);
<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php

				$params2 = $registry->toObject();
				$params2 = json_encode($params2);
				$query   = "UPDATE `#__bsms_studies` SET `params` = " . $db->quote($params2) . " WHERE `id` = " .
					(int) $db->quote($result->id) . " LIMIT 1";
=======
				$params = $registry;
				$params2 = $params->toObject();
				$params2 = json_encode($params2);
				$query = $db->getQuery(true);
				$query->update('#__bsms_mediafiles')->set($db->qn('params') . ' = ' . $db->q($params2))->where('id = ' . (int) $db->quote($result->id));
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php

				if (!JBSMDbHelper::performdb($query, "Build 700: "))
				{
					return false;
				}
			}
		}

		// Fix topics text
<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php
		$query = "SELECT `id`, `topic_text` FROM `#__bsms_topics`";
=======
		$query = $db->getQuery(true);
		$query->select('`id`, `topic_text`')->from('#__bsms_topics');
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php
		$db->setQuery($query);
		$results = $db->loadObjectList();

		if ($results)
		{
			foreach ($results AS $result)
			{
				$topic = $result->topic_text;

				// Replace all non a-Z 0-9 by '_'
				$topic = 'JBS_TOP_' . strtoupper(preg_replace('/[^a-z0-9]/i', '_', $topic));
<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php
				$query = "UPDATE `#__bsms_topics` SET `topic_text` = " . $db->quote($topic) . " WHERE `id` = " .
					(int) $db->quote($result->id);
=======
				$query = $db->getQuery(true);
				$query->update('#__bsms_topics')->set($db->qn('topic_text') . ' = ' . $db->q($topic))->where('id = ' . (int) $db->quote($result->id));
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php

				if (!JBSMDbHelper::performdb($query, "Build 700: "))
				{
					return false;
				}
			}
		}

<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php
		// Fix template params
		$query = "SELECT `id`, `params` FROM `#__bsms_templates`";
=======
		// Fix share params
		$query = $db->getQuery(true);
		$query->select('`id`, `params`')->from('#__bsms_share');
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php
		$db->setQuery($query);
		$results = $db->loadObjectList();

		if ($results)
		{
			foreach ($results AS $result)
			{
				// Update the params to json
				$registry = new Registry;

				if ($result->params)
				{
					// Fix incorrect params string literal
					$params = array();
					foreach (explode('\n', $result->params) as $param)
					{
						$param             = explode('=', str_replace('\n', '', trim($param)));
						$params[$param[0]] = $param[1];
					}

					$registry->loadArray($params);
				}

				$params2 = $registry->toObject();
				$params2 = json_encode($params2);
<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php
				$query   = "UPDATE `#__bsms_templates` SET `params` = " . $db->quote($params2) . " WHERE `id` = " .
					(int) $db->quote($result->id) . " LIMIT 1";
=======
				$query = $db->getQuery(true);
				$query->update('#__bsms_share')->set($db->qn('params') . ' = ' . $db->q($params2))->where('id = ' . (int) $db->quote($result->id));
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php

				if (!JBSMDbHelper::performdb($query, "Build 700: "))
				{
					return false;
				}
			}
		}

<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php
		// Fix Admin params
		$query = "SELECT `id`, `params` FROM `#__bsms_admin`";
=======
		// Fix template params
		$query = $db->getQuery(true);
		$query->select('`id`, `params`')->from('#__bsms_templates');
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php
		$db->setQuery($query);
		$results = $db->loadObjectList();

		if ($results)
		{
			foreach ($results AS $result)
			{
				// Update the params to json
				$registry = new Registry;

				if ($result->params)
				{
					// Fix incorrect params string literal
					$params = array();
					foreach (explode('\n', $result->params) as $param)
					{
						$param             = explode('=', str_replace('\n', '', trim($param)));
						$params[$param[0]] = $param[1];
					}

					$registry->loadArray($params);
				}

				$params2 = $registry->toObject();
				$params2 = json_encode($params2);
<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php
				$query   = "UPDATE `#__bsms_admin` SET `params` = " . $db->quote($params2) . " WHERE `id` = " .
					(int) $db->quote($result->id) . " LIMIT 1";
=======
				$query = $db->getQuery(true);
				$query->update('#__bsms_templates')->set($db->qn('params') . ' = ' . $db->q($params2))->where('id = ' . (int) $db->quote($result->id));
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php

				if (!JBSMDbHelper::performdb($query, "Build 700: "))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Add Asset Column
	 *
	 * @param   array  $table  Table name to affect
	 *
	 * @return boolean
	 */
	protected function addAssetColumn($table)
	{
		$dbhelper = new JBSMDbHelper;

		if (!$table)
		{
			return false;
		}
		if (JBSMDbHelper::checkTables($table, 'asset_id') !== true)
		{
			$array = array(
				array(
					'table'   => $table, 'field' => 'asset_id', 'type' => 'ADD',
					'command' => "int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.'"
				)
			);

			if (!$dbhelper->alterDB($array, "Build 700 : "))
			{
				return false;
			}
		}
		if (JBSMDbHelper::checkTables($table, 'access') !== true)
		{
			$array = array(
				array(
					'table'   => $table, 'field' => 'access', 'type' => 'ADD',
					'command' => "int(10) unsigned NOT NULL DEFAULT '1'"
				)
			);

			if (!$dbhelper->alterDB($array, "Build 700 : "))
			{
				return false;
			}
		}

		return true;
	}
<<<<<<< HEAD:com_biblestudy/admin/install/updates/7.0.0.php
=======

	/**
	 * Upgrade function
	 *
	 * @return boolean
	 */
	public function upgrade623()
	{

		$db = JFactory::getDBO();

		// We adjust those rows that have internal_popup set to 0 and we change it to 2
		$query = $db->getQuery(true);
		$query
			->select('id, params')
			->from('#__bsms_mediafiles');
		$db->setQuery($query);
		$db->query();
		$results = $db->loadObjectList();

		if ($results)
		{
			foreach ($results AS $result)
			{
				$isplayertype = substr_count($result->params, 'internal_popup=0');

				if ($isplayertype)
				{
					$oldparams = $result->params;
					$newparams = str_replace('internal_popup=0', 'internal_popup=2', $oldparams);
					$query     = $db->getQuery(true);
					$query
						->update('#__bsms_mediafiles')
						->set($db->qn('params') . ' = ' . $db->q($newparams))
						->where('id = ' . (int) $db->q($result->id));
					$db->setQuery($query);

					if (!$db->execute())
					{
						JFactory::getApplication()
							->enqueueMessage(
								"Build 623: " . JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'warning');

						return false;
					}
				}
			}
		}
		$data              = new stdClass;
		$data->version     = '6.2.3';
		$data->installdate = '2010-11-03';
		$data->build       = '623';
		$data->versionname = '1Samuel';
		$data->versiondate = '2010-11-03';

		if (!$db->insertObject('#__bsms_version', $data))
		{
			return false;
		}

		$data1              = new stdClass;
		$data1->version     = '6.2.4';
		$data1->installdate = '2010-11-09';
		$data1->build       = '623';
		$data1->versionname = '2Samuel';
		$data1->versiondate = '2010-11-09';

		if (!$db->insertObject('#__bsms_version', $data1))
		{
			return false;
		}

		return true;
	}

	/**
	 * Upgrade Function
	 *
	 * @return boolean
	 */
	public function upgrade622()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('`id`, `params`')
			->from('#__bsms_mediafiles')
			->where($db->qn('params') . ' LIKE ' . $db->q('%podcast1%'));
		$db->setQuery($query);
		$results = $db->loadObjectList();

		if ($results)
		{
			foreach ($results AS $result)
			{
				$old_params = $result->params;
				$new_params = str_replace('podcast1', 'podcasts', $old_params);
				$query = $db->getQuery(true);
				$query->update('#__bsms_mediafiles')->set($db->qn('params') . ' = ' . $db->q($new_params))->where('id = ' . (int) $db->quote($result->id));

				if (!JBSMDbHelper::performdb($query, "Build 622: "))
				{
					return false;
				}
			}
		}

		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_order` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `value` varchar(15) DEFAULT '',
				  `text` varchar(15) DEFAULT '',
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		if (!JBSMDbHelper::performdb($query, "Build 622: "))
		{
			return false;
		}

		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__bsms_order')
			->where($db->qn('id') . ' = ' . $db->q('1'));
		$db->setQuery($query);
		$results = $db->loadObject();
		if (!$results)
		{
			$query = "INSERT INTO `#__bsms_order` VALUES (1, 'ASC', 'Ascending'),(2, 'DESC', 'Descending')";
			if (!JBSMDbHelper::performdb($query, "Build 622: "))
			{
				return false;
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
		if (!JBSMDbHelper::performdb($query, "Build 622: "))
		{
			return false;
		}

		$query = "INSERT INTO #__bsms_version SET `version` = '6.2.2', `installdate`='2010-10-25', `build`='622', " .
			"`versionname`='Judges', `versiondate`='2010-10-25'";

		if (!JBSMDbHelper::performdb($query, "Build 622: "))
		{
			return false;
		}

		return true;
	}

	/**
	 * Upgrade function
	 *
	 * @return boolean
	 */
	public function upgrade614()
	{
		$db    = JFactory::getDBO();
		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_studytopics` (
				  `id` int(3) NOT NULL AUTO_INCREMENT,
				  `study_id` int(3) NOT NULL DEFAULT '0',
				  `topic_id` int(3) NOT NULL DEFAULT '0',
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `id` (`id`),
				  KEY `id_2` (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}

		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_timeset` (
                `timeset` VARCHAR(14) ,
                KEY `timeset` (`timeset`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}
		$query = "ALTER TABLE `#__bsms_teachers` MODIFY `title` varchar(250)";

		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}
		$query = "ALTER TABLE `#__bsms_mediafiles` ADD COLUMN downloads int(10) DEFAULT 0";

		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}
		$query = "ALTER TABLE `#__bsms_mediafiles` ADD COLUMN plays int(10) DEFAULT 0";

		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}
		$query = $db->getQuery(true);
		$query->insert('#__bsms_timeset')->set('timeset = ' . 1281646339);

		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}

		// This updates the mediafiles table to reflect the new way of associating files to podcasts
		$query = $db->getQuery(true);
		$query->select('id, params, podcast_id')->from('#__bsms_mediafiles')->where('podcast_id > ' . 0);
		$db->setQuery($query);
		$db->execute(); // Need this do to the getNumRows dos not execute the Query
		$num_rows = $db->getNumRows();

		if ($num_rows > 0)
		{
			$results = $db->loadObjectList();

			foreach ($results as $result)
			{
				$registry = new JRegistry;
				$params  = $result->params;
				$registry->loadString($params);
				$registry->def('podcasts', $result->podcast_id);
				$update  = $registry->toString();
				$query   = $db->getQuery(true);
				$query->update('#__bsms_mediafiles')->set('`params` = ' . $db->q($update) . ', `podcast_id` = ' . 0)->where('id = ' . (int) $result->id);

				if (!JBSMDbHelper::performdb($query, "Build 614: "))
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

		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}

		$query = "INSERT INTO #__bsms_version SET `version` = '6.2.0', `installdate`='2010-09-06', " .
			"`build`='614', `versionname`='Deuteronomy', `versiondate`='2010-09-06'";
		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}
		return true;
	}

	/**
	 * Upgrade function
	 *
	 * @return boolean Messages of progress.
	 */
	public function upgrade613()
	{
		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_admin` (
					  `id` int(11) NOT NULL,
					  `podcast` text,
					  `series` text,
					  `study` text,
					  `teacher` text,
					  `media` text,
					  `download` text,
					  `main` text,
					  `showhide` char(255) DEFAULT NULL,
					  `params` text,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_share` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(250) DEFAULT NULL,
				  `params` text,
				  `published` tinyint(1) NOT NULL DEFAULT '1',
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_templates` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `type` varchar(255) NOT NULL,
				  `tmpl` longtext NOT NULL,
				  `published` int(1) NOT NULL DEFAULT '1',
				  `params` longtext,
				  `title` text,
				  `text` text,
				  `pdf` text,
				  PRIMARY KEY (`id`)
				  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "INSERT INTO `#__bsms_admin` VALUES (1, '', '', '', '', 'speaker24.png', " .
			"'download.png', 'openbible.png', '0', 'compat_mode=0 drop_tables=0 admin_store=1 studylistlimit=10 " .
			"popular_limit=1 series_imagefolder= media_imagefolder= teachers_imagefolder= study_images= podcast_imagefolder= " .
			"location_id= teacher_id= series_id= booknumber= topic_id= messagetype= avr=0 download= target= server= path= " .
			"podcast=0 mime=0 allow_entry_study=0 entry_access=23 study_publish=0 socialnetworking=1')";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "INSERT INTO `#__bsms_share` (`id`, `name`, `params`, `published`) VALUES" .
			"(NULL, 'FaceBook', 'mainlink=//www.facebook.com/sharer.php? item1prefix=u= item1=200 item1custom= item2prefix=t= item2=5 item2custom= " .
			"item3prefix= item3=6 item3custom= item4prefix= item4=8 item4custom= use_bitly=0 username= api= " .
			"shareimage=../media/com_biblestudy/images/facebook.png " .
			"shareimageh=33px shareimagew=33px totalcharacters= alttext=FaceBook  ', 1)," .
			"(null, 'Twitter', 'mainlink=//twitter.com/home? item1prefix=status= item1=200 item1custom= item2prefix= " .
			"item2=5 item2custom= item3prefix= item3=1 item3custom=" .
			"item4prefix= item4= item4custom= use_bitly=0 username= api= shareimage=../media/com_biblestudy/images/twitter.png " .
			"shareimagew=33px shareimageh=33px totalcharacters=140 alttext=Twitter', 1)," .
			"(null, 'Delicious', 'mainlink=//delicious.com/save? item1prefix=url= item1=200 item1custom= " .
			"item2prefix=&amp;title= item2=5 item2custom= item3prefix= item3=6 item3custom= item4prefix= item4= item4custom= " .
			"use_bitly=0 username= api= shareimage=../media/com_biblestudy/images/delicious.png shareimagew=33px " .
			"shareimageh=33px totalcharacters= alttext=Delicious', 1), " .
			"(null, 'MySpace', 'mainlink=//www.myspace.com/index.cfm? item1prefix=fuseaction=postto&amp;t= item1=5 item1custom= " .
			"item2prefix=&amp;c= item2=6 item2custom= item3prefix=&amp;u= item3=200 item3custom= item4prefix=&amp;l=1 item4= item4custom= " .
			"use_bitly=0 username= api= " .
			"shareimage=../media/com_biblestudy/images/myspace.png\nshareimagew=33px\nshareimageh=33px\ntotalcharacters=\nalttext=MySpace', 1)";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "INSERT INTO `#__bsms_templates` VALUES(1, 'tmplList', '', 1," .
			" 'itemslimit=10\n compatibilityMode=0\n studieslisttemplateid=1\n " .
			"detailstemplateid=1\n teachertemplateid=1\n serieslisttemplateid=1\n seriesdetailtemplateid=1\n teacher_id=\n " .
			"show_teacher_list=0\n series_id=0\n booknumber=0\n topic_id=0\n messagetype=0\n locations=0\n default_order=DESC\n " .
			"show_page_image=1\n tooltip=1\n show_verses=0\n stylesheet=\n date_format=2\n duration_type=1\n useavr=0\n popuptype=window\n " .
			"media_player=0\n player_width=290\n show_filesize=1\n store_page=flypage.tpl\n show_page_title=1\n page_title=Bible\n " .
			"Studies\n use_headers_list=1\n list_intro=\n intro_show=1\n listteachers=1\n teacherlink=1\n details_text=Study\n Details\n " .
			"show_book_search=1\n show_teacher_search=1\n show_series_search=1\n show_type_search=1\n show_year_search=1\n " .
			"show_order_search=1\n show_topic_search=1\n show_locations_search=1\n show_popular=1\n tip_title=Sermon\n Information\n " .
			"tip_item1_title=Title\n tip_item1=5\n tip_item2_title=Details\n tip_item2=6\n tip_item3_title=Teacher\n tip_item3=7\n " .
			"tip_item4_title=Reference\n tip_item4=1\n tip_item5_title=Date\n tip_item5=10\n row1col1=18\n r1c1custom=\n r1c1span=1\n " .
			"rowspanr1c1=1\n linkr1c1=0\n row1col2=5\n r1c2custom=\n r1c2span=1\n rowspanr1c2=1\n linkr1c2=1\n row1col3=1\n r1c3custom=\n " .
			"r1c3span=1\n rowspanr1c3=1\n linkr1c3=0\n row1col4=20\n r1c4custom=\n rowspanr1c4=1\n linkr1c4=0\n row2col1=6\n r2c1custom=\n " .
			"r2c1span=4\n rowspanr2c1=1\n linkr2c1=0\n row2col2=0\n r2c2custom=\n r2c2span=1\n rowspanr2c2=1\n linkr2c2=0\n row2col3=0\n " .
			"r2c3custom=\n r2c3span=1\n rowspanr2c3=1\n linkr2c3=0\n row2col4=0\n r2c4custom=\n rowspanr2c4=1\n linkr2c4=0\n row3col1=0\n " .
			"r3c1custom=\n r3c1span=1\n rowspanr3c1=1\n linkr3c1=0\n row3col2=0\n r3c2custom=\n r3c2span=1\n linkr3c2=0\n row3col3=0\n " .
			"r3c3custom=\n r3c3span=1\n rowspanr3c3=1\n linkr3c3=0\n row3col4=0\n r3c4custom=\n rowspanr3c4=1\n linkr3c4=0\n row4col1=0\n " .
			"r4c1custom=\n r4c1span=1\n rowspanr4c1=1\n linkr4c1=0\n row4col2=0\n r4c2custom=\n r4c2span=1\n rowspanr4c2=1\n linkr4c2=0\n " .
			"row4col3=0\n r4c3custom=\n r4c3span=1\n rowspanr4c3=1\n linkr4c3=0\n row4col4=0\n r4c4custom=\n rowspanr4c4=1\n linkr4c4=0\n " .
			"show_print_view=1\n show_pdf_view=1\n show_teacher_view=1\n show_passage_view=1\n use_headers_view=1\n list_items_view=0\n " .
			"title_line_1=1\n customtitle1=\n title_line_2=4\n customtitle2=\n view_link=1\n link_text=Return\n to\n Studies\n List\n " .
			"show_scripture_link=1\n show_comments=0\n comment_access=1\n comment_publish=0\n use_captcha=1\n email_comments=1\n recipient=\n " .
			"subject=Comments\n on\n studies\n body=Comments\n entered.\n moduleitems=3\n teacher_title=Our\n Teachers\n show_teacher_studies=1\n " .
			"studies=5\n label_teacher=Latest\n Messages\n teacherlink=1\n series_title=Our\n Series\n show_series_title=1\n show_page_image_series=1\n " .
			"series_show_description=1\n series_characters=\n search_series=1\n series_limit=5\n serieselement1=1\n seriesislink1=1\n serieselement2=1\n " .
			"seriesislink2=1\n serieselement3=1\n seriesislink3=1\n serieselement4=1\n seriesislink4=1\n series_detail_sort=1\n series_detail_order=DESC\n " .
			"series_detail_show_link=1\n series_detail_limit=\n series_list_return=1\n series_detail_1=5\n series_detail_islink1=1\n series_detail_2=7\n " .
			"series_detail_islink2=0\n series_detail_3=10\n series_detail_islink3=0\n series_detail_4=20\n series_detail_islink4=0', " .
			"'Default', 'textfile24.png', 'pdf24.png')";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_studies` ADD COLUMN thumbnailm TEXT NULL AFTER studytext";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_studies` ADD COLUMN thumbhm INT NULL AFTER thumbnailm";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_studies` ADD COLUMN thumbwm INT NULL AFTER thumbhm";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_studies` ADD COLUMN params TEXT NULL AFTER thumbwm";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_podcast` ADD COLUMN episodetitle INT NULL AFTER published";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_podcast` ADD COLUMN custom VARCHAR( 200 ) NULL AFTER episodetitle";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_podcast` ADD COLUMN detailstemplateid INT NULL AFTER custom";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_series` ADD COLUMN series_thumbnail VARCHAR(150) NULL AFTER series_text";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_series` ADD COLUMN description TEXT NULL AFTER series_text";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_series` ADD COLUMN teacher INT(3) NULL AFTER series_text";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_media` ADD COLUMN path2 VARCHAR(150) NOT NULL AFTER media_image_path";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "INSERT INTO #__bsms_media SET `media_text` = 'Article',`media_image_name` = 'Article',`path2` = " .
			"'textfile24.png', `media_alttext` = 'Article',`published` = '1'";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "INSERT INTO #__bsms_media SET `media_text` = 'Download',`media_image_name` = 'Download',`path2` = " .
			"'download.png', `media_alttext` = 'Download',`published` = '1'";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_teachers` ADD COLUMN teacher_thumbnail TEXT NULL AFTER id";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_teachers` ADD COLUMN teacher_image TEXT NULL AFTER id";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_mediafiles` ADD COLUMN docMan_id INT NULL AFTER published";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_mediafiles` ADD COLUMN article_id INT NULL AFTER docMan_id";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_mediafiles` ADD COLUMN comment TEXT NULL AFTER article_id";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_mediafiles` ADD COLUMN virtueMart_id INT NULL AFTER comment";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_mediafiles` ADD COLUMN params TEXT NULL AFTER virtueMart_id";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "UPDATE `#__bsms_mediafiles` SET params = 'player=2', internal_viewer = '0' WHERE internal_viewer = '1' AND params IS NULL";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = 'DROP TABLE IF EXISTS `#__bsms_schemaVersion`;';

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
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

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		$query = "INSERT INTO #__bsms_version SET `version` = '', `installdate`='2009-11-30', " .
			"`build`='613', `versionname`='Numbers', `versiondate`='2009-11-30'";

		if (!JBSMDbHelper::performdb($query, "Build 613: "))
		{
			return false;
		}

		return true;
	}

	/**
	 * Upgrade Function
	 *
	 * @return boolean
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

	/**
	 * Upgrade function
	 *
	 * @return boolean
	 */
	public function upgrade611()
	{
		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_locations` (
					`id` INT NOT NULL AUTO_INCREMENT,
					`location_text` VARCHAR(250) NULL,
					`published` TINYINT(1) NOT NULL DEFAULT '1',
					PRIMARY KEY (`id`) ) ENGINE=InnoDB CHARACTER SET `utf8`";

		if (!JBSMDbHelper::performdb($query, "Build 611: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_studies` ADD COLUMN show_level varchar(100) NOT NULL default '0' AFTER user_name";

		if (!JBSMDbHelper::performdb($query, "Build 611: "))
		{
			return false;
		}

		$query = "ALTER TABLE `#__bsms_studies` ADD COLUMN location_id INT(3) NULL AFTER show_level";

		if (!JBSMDbHelper::performdb($query, "Build 611: "))
		{
			return false;
		}

		$query = "INSERT INTO #__bsms_version SET `version` = '6.0.11', `installdate`='2008-10-22', `build`='611'," .
			"`versionname`='Leviticus', `versiondate`='2008-10-22'";

		if (!JBSMDbHelper::performdb($query, "Build 611: "))
		{
			return false;
		}

		return true;
	}

	/**
	 * Update for 7.1.0
	 *
	 * @return boolean
	 */
	public function upgrade710()
	{
		JLoader::register('JBS710Update', BIBLESTUDY_PATH_ADMIN . '/install/updates/update710.php');
		$migrate = new JBS710Update;

		if (!$migrate->update710())
		{
			return false;
		}

		return true;
	}

	/**
	 * Update for 7.1.0
	 *
	 * @return boolean
	 */
	public function upgrade701()
	{
		JLoader::register('JBS701Update', BIBLESTUDY_PATH_ADMIN . '/install/updates/update701.php');
		$migrate = new JBS701Update;

		if (!$migrate->do701update())
		{
			return false;
		}

		return true;
	}

	/**
	 * Update for 8.0.0
	 *
	 * @return boolean
	 */
	public function upgrade800()
	{
		JLoader::register('JBS800Update', BIBLESTUDY_PATH_ADMIN . '/install/updates/8.0.0.php');
		$migrate = new JBS800Update;

		if (!$migrate->update800())
		{
			return false;
		}

		return true;
	}

>>>>>>> Joomla-Bible-Study/master:com_biblestudy/admin/migration/updateALL.php
}
