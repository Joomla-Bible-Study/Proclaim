<?php

/**
 * Migration for 7.0.0
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

require_once ( JPATH_ROOT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'joomla' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'parameter.php' );
require_once(BIBLESTUDY_PATH_ADMIN_HELPERS . DIRECTORY_SEPARATOR . 'dbhelper.php');

/**
 * Upgrade class for 7.0.0
 * @package BibleStudy.Admin
 * @since 7.0.2
 */
class jbs700Install {

    /**
     * Upgrade function
     * @return array
     */
    function upgrade700() {
        $db = JFactory::getDBO();
        $messages = array();

        //Alter some tables
        $msg = '';

        @set_time_limit(300);

        $dbhelper = new jbsDBhelper();
        //alter the admin table

        $tables = array(
            array('table' => '#__bsms_admin', 'field' => 'main', 'type' => 'DROP', 'command' => ''),
            array('table' => '#__bsms_admin', 'field' => 'podcast', 'type' => 'DROP', 'command' => ''),
            array('table' => '#__bsms_admin', 'field' => 'series', 'type' => 'DROP', 'command' => ''),
            array('table' => '#__bsms_admin', 'field' => 'study', 'type' => 'DROP', 'command' => ''),
            array('table' => '#__bsms_admin', 'field' => 'teacher', 'type' => 'DROP', 'command' => ''),
            array('table' => '#__bsms_admin', 'field' => 'media', 'type' => 'DROP', 'command' => ''),
            array('table' => '#__bsms_admin', 'field' => 'showhide', 'type' => 'DROP', 'command' => ''),
            array('table' => '#__bsms_admin', 'field' => 'download', 'type' => 'DROP', 'command' => ''),
            array('table' => '#__bsms_admin', 'field' => 'drop_tables', 'type' => 'ADD', 'command' => 'int(3) NULL default "0"'),
            array('table' => '#__bsms_admin', 'field' => 'id', 'type' => 'MODIFY', 'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'),
            array('table' => '#__bsms_comments', 'field' => 'id', 'type' => 'MODIFY', 'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'),
            array('table' => '#__bsms_admin', 'field' => 'id', 'type' => 'MODIFY', 'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'),
            array('table' => '#__bsms_folders', 'field' => 'id', 'type' => 'MODIFY', 'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'),
            array('table' => '#__bsms_media', 'field' => 'id', 'type' => 'MODIFY', 'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'),
            array('table' => '#__bsms_mediafiles', 'field' => 'podcast_id', 'type' => 'MODIFY', 'command' => 'VARCHAR(50)'),
            array('table' => '#__bsms_mediafiles', 'field' => 'player', 'type' => 'ADD', 'command' => 'INT( 2 ) NULL DEFAULT NULL'),
            array('table' => '#__bsms_mediafiles', 'field' => 'popup', 'type' => 'ADD', 'command' => 'INT( 2 ) NULL DEFAULT NULL'),
            array('table' => '#__bsms_mediafiles', 'field' => 'id', 'type' => 'MODIFY', 'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'),
            array('table' => '#__bsms_order', 'field' => 'id', 'type' => 'MODIFY', 'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'),
            array('table' => '#__bsms_order', 'field' => 'text', 'type' => 'MODIFY', 'command' => 'VARCHAR(50) DEFAULT NULL'),
            array('table' => '#__bsms_search', 'field' => 'id', 'type' => 'MODIFY', 'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'),
            array('table' => '#__bsms_series', 'field' => 'id', 'type' => 'MODIFY', 'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'),
            array('table' => '#__bsms_servers', 'field' => 'id', 'type' => 'MODIFY', 'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'),
            array('table' => '#__bsms_share', 'field' => 'id', 'type' => 'MODIFY', 'command' => 'int(3) UNSIGNED NOT NULL AUTO_INCREMENT'),
            array('table' => '#__bsms_comments', 'field' => 'show_level', 'type' => 'ADD', 'command' => "varchar(100) NOT NULL DEFAULT '0'"),
            array('table' => '#__bsms_comments', 'field' => 'study_id', 'type' => 'MODIFY', 'command' => "int(11) NOT NULL DEFAULT '0'"),
            array('table' => '#__bsms_comments', 'field' => 'user_id', 'type' => 'MODIFY', 'command' => "int(11) NOT NULL DEFAULT '0'"),
            array('table' => '#__bsms_comments', 'field' => 'full_name', 'type' => 'MODIFY', 'command' => "varchar(50) NOT NULL DEFAULT ''"),
            array('table' => '#__bsms_comments', 'field' => 'user_email', 'type' => 'MODIFY', 'command' => "varchar(100) NOT NULL DEFAULT ''"),
            array('table' => '#__bsms_comments', 'field' => 'comment_date', 'type' => 'MODIFY', 'command' => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'"),
            array('table' => '#__bsms_folders', 'field' => 'foldername', 'type' => 'MODIFY', 'command' => "varchar(250) NOT NULL DEFAULT ''"),
            array('table' => '#__bsms_folders', 'field' => 'folderpath', 'type' => 'MODIFY', 'command' => "varchar(250) NOT NULL DEFAULT ''"),
            array('table' => '#__bsms_folders', 'field' => 'published', 'type' => 'MODIFY', 'command' => "tinyint(3) NOT NULL DEFAULT '1'"),
            array('table' => '#__bsms_locations', 'field' => 'published', 'type' => 'MODIFY', 'command' => "tinyint(3) NOT NULL DEFAULT '1'"),
            array('table' => '#__bsms_media', 'field' => 'media_image_name', 'type' => 'MODIFY', 'command' => "varchar(250) NOT NULL DEFAULT ''"),
            array('table' => '#__bsms_media', 'field' => 'media_image_path', 'type' => 'MODIFY', 'command' => "varchar(250) NOT NULL DEFAULT ''"),
            array('table' => '#__bsms_media', 'field' => 'media_alttext', 'type' => 'MODIFY', 'command' => "varchar(250) NOT NULL DEFAULT ''"),
            array('table' => '#__bsms_media', 'field' => 'published', 'type' => 'MODIFY', 'command' => "tinyint(3) NOT NULL DEFAULT '1'"),
            array('table' => '#__bsms_mediafiles', 'field' => 'mediacode', 'type' => 'MODIFY', 'command' => "text"),
            array('table' => '#__bsms_mediafiles', 'field' => 'link_type', 'type' => 'MODIFY', 'command' => "char(1) DEFAULT NULL"),
            array('table' => '#__bsms_mediafiles', 'field' => 'published', 'type' => 'MODIFY', 'command' => "tinyint(3) NOT NULL DEFAULT '1'"),
            array('table' => '#__bsms_mediafiles', 'field' => 'downloads', 'type' => 'MODIFY', 'command' => "int(10) DEFAULT '0'"),
            array('table' => '#__bsms_mediafiles', 'field' => 'plays', 'type' => 'MODIFY', 'command' => "int(10) DEFAULT '0'"),
            array('table' => '#__bsms_message_type', 'field' => 'message_type', 'type' => 'MODIFY', 'command' => "text NOT NULL"),
            array('table' => '#__bsms_message_type', 'field' => 'published', 'type' => 'MODIFY', 'command' => "tinyint(3) NOT NULL DEFAULT '1'"),
            array('table' => '#__bsms_mimetype', 'field' => 'published', 'type' => 'MODIFY', 'command' => "tinyint(3) NOT NULL DEFAULT '1'"),
            array('table' => '#__bsms_podcast', 'field' => 'description', 'type' => 'MODIFY', 'command' => "text"),
            array('table' => '#__bsms_podcast', 'field' => 'published', 'type' => 'MODIFY', 'command' => "tinyint(3) NOT NULL DEFAULT '1'"),
            array('table' => '#__bsms_search', 'field' => 'value', 'type' => 'MODIFY', 'command' => "varchar(15) DEFAULT ''"),
            array('table' => '#__bsms_search', 'field' => 'text', 'type' => 'MODIFY', 'command' => "varchar(15) DEFAULT ''"),
            array('table' => '#__bsms_series', 'field' => 'published', 'type' => 'MODIFY', 'command' => "tinyint(3) NOT NULL DEFAULT '1'"),
            array('table' => '#__bsms_servers', 'field' => 'ftp_username', 'type' => 'ADD', 'command' => "char(255) NOT NULL"),
            array('table' => '#__bsms_servers', 'field' => 'ftp_password', 'type' => 'ADD', 'command' => "char(255) NOT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'chapter_begin', 'type' => 'MODIFY', 'command' => "int(3) DEFAULT '1'"),
            array('table' => '#__bsms_studies', 'field' => 'verse_begin', 'type' => 'MODIFY', 'command' => "int(3) DEFAULT '1'"),
            array('table' => '#__bsms_studies', 'field' => 'chapter_end', 'type' => 'MODIFY', 'command' => "int(3) DEFAULT '1'"),
            array('table' => '#__bsms_studies', 'field' => 'verse_end', 'type' => 'MODIFY', 'command' => "int(3) DEFAULT '1'"),
            array('table' => '#__bsms_studies', 'field' => 'series_id', 'type' => 'MODIFY', 'command' => "int(3) DEFAULT '0'"),
            array('table' => '#__bsms_studies', 'field' => 'topics_id', 'type' => 'MODIFY', 'command' => "int(3) DEFAULT '0'"),
            array('table' => '#__bsms_studies', 'field' => 'show_level', 'type' => 'MODIFY', 'command' => "varchar(100) NOT NULL DEFAULT '0'"),
            array('table' => '#__bsms_teachers', 'field' => 'teachername', 'type' => 'MODIFY', 'command' => "varchar(250) NOT NULL DEFAULT ''"),
            array('table' => '#__bsms_teachers', 'field' => 'website', 'type' => 'MODIFY', 'command' => "text"),
            array('table' => '#__bsms_timeset', 'field' => 'backup', 'type' => 'ADD', 'command' => "varchar(14) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media1_id', 'type' => 'DROP', 'command' => "int(11) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media1_server', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media1_path', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media1_special', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media1_filename ', 'type' => 'DROP', 'command' => "text"),
            array('table' => '#__bsms_studies', 'field' => 'media1_size', 'type' => 'DROP', 'command' => "text"),
            array('table' => '#__bsms_studies', 'field' => 'media1_show', 'type' => 'DROP', 'command' => "tinyint(1) DEFAULT '0'"),
            array('table' => '#__bsms_studies', 'field' => 'media2_id', 'type' => 'DROP', 'command' => "int(11) DEFAULT '0'"),
            array('table' => '#__bsms_studies', 'field' => 'media2_server', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media2_path', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media2_special', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media2_filename', 'type' => 'DROP', 'command' => "text"),
            array('table' => '#__bsms_studies', 'field' => 'media2_size', 'type' => 'DROP', 'command' => "text"),
            array('table' => '#__bsms_studies', 'field' => 'media2_show', 'type' => 'DROP', 'command' => "tinyint(1) DEFAULT '0'"),
            array('table' => '#__bsms_studies', 'field' => 'media3_id', 'type' => 'DROP', 'command' => "int(11) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media3_server', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media3_path', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media3_special', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media3_filename', 'type' => 'DROP', 'command' => "text"),
            array('table' => '#__bsms_studies', 'field' => 'media3_size', 'type' => 'DROP', 'command' => "text"),
            array('table' => '#__bsms_studies', 'field' => 'media3_show', 'type' => 'DROP', 'command' => "media3_show tinyint(1) DEFAULT '0'"),
            array('table' => '#__bsms_studies', 'field' => 'media4_id', 'type' => 'DROP', 'command' => "int(11) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media4_server', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media4_path', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media4_special', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media4_filename', 'type' => 'DROP', 'command' => "text"),
            array('table' => '#__bsms_studies', 'field' => 'media4_size', 'type' => 'DROP', 'command' => "text"),
            array('table' => '#__bsms_studies', 'field' => 'media4_show', 'type' => 'DROP', 'command' => "tinyint(1) DEFAULT '0'"),
            array('table' => '#__bsms_studies', 'field' => 'media5_id', 'type' => 'DROP', 'command' => "int(11) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media5_server', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media5_path', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media5_special', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media5_filename', 'type' => 'DROP', 'command' => "text"),
            array('table' => '#__bsms_studies', 'field' => 'media5_size', 'type' => 'DROP', 'command' => "text"),
            array('table' => '#__bsms_studies', 'field' => 'media5_show', 'type' => 'DROP', 'command' => "tinyint(1) DEFAULT '0'"),
            array('table' => '#__bsms_studies', 'field' => 'media6_id', 'type' => 'DROP', 'command' => "int(11) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media6_server', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media6_path', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media6_special', 'type' => 'DROP', 'command' => "varchar(250) DEFAULT NULL"),
            array('table' => '#__bsms_studies', 'field' => 'media6_filename', 'type' => 'DROP', 'command' => "text"),
            array('table' => '#__bsms_studies', 'field' => 'media6_size', 'type' => 'DROP', 'command' => "text"),
            array('table' => '#__bsms_studies', 'field' => 'media6_show', 'type' => 'DROP', 'command' => "tinyint(1) DEFAULT '0'"),
        );

        $admin = $dbhelper->alterDB($tables);
        if (!empty($admin)) {
            $messages[] = $admin;
        }

        /* Start of Adding Assets and Access Columns */
        $table = '#__bsms_admin';
        $msg[] = $this->addAssetColumn($table);
        if ($dbhelper->checkTables($table, 'drop_tables') == 'true') {
            $query = 'UPDATE `#__bsms_admin` SET `drop_tables` = 0 WHERE id = 1';
            $messages[] = $this->performdb($query);
        }

        $table = '#__bsms_comments';
        $messages[] = $this->addAssetColumn($table);

        $table = '#__bsms_folders';
        $messages[] = $this->addAssetColumn($table);

        $table = '#__bsms_locations';
        $messages[] = $this->addAssetColumn($table);

        $table = '#__bsms_media';
        $messages[] = $this->addAssetColumn($table);

        $table = '#__bsms_mediafiles';
        $messages[] = $this->addAssetColumn($table);
        $table = '#__bsms_message_type';
        $messages[] = $this->addAssetColumn($table);

        $table = '#__bsms_mimetype';
        $messages[] = $this->addAssetColumn($table);

        // was not in 7.0.2 will be added in sql 7.1.0.sql under allupdate.php (#__bsms_order)

        $table = '#__bsms_podcast';
        $messages[] = $this->addAssetColumn($table);

        $table = '#__bsms_series';
        $messages[] = $this->addAssetColumn($table);

        $table = '#__bsms_servers';
        $messages[] = $this->addAssetColumn($table);

        $table = '#__bsms_share';
        $messages[] = $this->addAssetColumn($table);

        $table = '#__bsms_studies';
        $messages[] = $this->addAssetColumn($table);

        $table = '#__bsms_studytopics';
        $messages[] = $this->addAssetColumn($table);

        $table = '#__bsms_teachers';
        $messages[] = $this->addAssetColumn($table);

        $table = '#__bsms_templates';
        $messages[] = $this->addAssetColumn($table);

        $table = '#__bsms_topics';
        $messages[] = $this->addAssetColumn($table);
        /* End of Adding Assets and Access Columns */

        /* Fix Mimtype Flash from old plyers */
        $query = "UPDATE `#__bsms_mimetype` SET `id` = '15', `mimetype` = 'video/x-flv .flv', `mimetext` = ' Flash Video FLV', `published` = '1', `asset_id` = '3900', `access` = '0' WHERE `#__bsms_mimetype`.`id` = '15'";
        $messages[] = $this->performdb($query);

        /* Update Show levels */
        $query = "UPDATE `#__bsms_studies` SET `access` = '1' WHERE `show_level` = '0'";
        $messages[] = $this->performdb($query);
        $query = "UPDATE `#__bsms_studies` SET `access` = '2' WHERE `show_level` = '18'";
        $messages[] = $this->performdb($query);
        $query = "UPDATE `#__bsms_studies` SET `access` = '2' WHERE `show_level` = '19'";
        $messages[] = $this->performdb($query);
        $query = "UPDATE `#__bsms_studies` SET `access` = '2' WHERE `show_level` = '20'";
        $messages[] = $this->performdb($query);
        $query = "UPDATE `#__bsms_studies` SET `access` = '3' WHERE `show_level` = '22'";
        $messages[] = $this->performdb($query);
        $query = "UPDATE `#__bsms_studies` SET `access` = '3' WHERE `show_level` = '23'";
        $messages[] = $this->performdb($query);
        $query = "UPDATE `#__bsms_studies` SET `access` = '3' WHERE `show_level` = '24'";
        $messages[] = $this->performdb($query);

        /* Perform Mediafiles players. */
        $query = 'SELECT `id`, `params` FROM #__bsms_mediafiles';
        $db->setQuery($query);
        $db->query();
        $results = $db->loadObjectList();
        if ($results) {
            //Now run through all the results, pull out the media player and the popup type and move them to their respective db fields
            foreach ($results AS $result) {
                $registry = new JRegistry;
                $registry->loadJSON($result->params);
                $params = $registry;
                $player = $params->get('player');
                $popup = $params->get('internal_popup');
                $podcasts = $params->get('podcasts');
                if ($player) {
                    if ($player == 2) {
                        $player = 3;
                    }
                    $query = "UPDATE #__bsms_mediafiles SET `player` = '$player' WHERE `id` = $result->id LIMIT 1";
                    $messages[] = $this->performdb($query);
                }
                if ($popup) {
                    $query = "UPDATE #__bsms_mediafiles SET `popup` = '$popup' WHERE `id` = $result->id LIMIT 1";
                    $messages[] = $this->performdb($query);
                }
                if ($podcasts) {
                    $podcasts = str_replace('|', ',', $podcasts);
                    $query = "UPDATE #__bsms_mediafiles SET `podcast_id` = '$podcasts' WHERE `id` = $result->id LIMIT 1";
                    $messages[] = $this->performdb($query);
                }
                //Update the params to json
                $registry = new JRegistry;
                $registry->loadJSON($result->params);
                $params = $registry;
                $params2 = $params->toObject();
                $params2 = json_encode($params2);


                $query = "UPDATE #__bsms_mediafiles SET `params` = '$params2' WHERE `id` = $result->id LIMIT 1";
                $messages[] = $this->performdb($query);
            }
        }


//Get all the study records


        $query = 'DROP TABLE #__bsms_books';
        $msg = $this->performdb($query);
        if (!$msg) {
            $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
        } else {
            $messages[] = $msg;
        }

        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_books` (
					  `id` int(3) NOT NULL AUTO_INCREMENT,
					  `bookname` varchar(250) DEFAULT NULL,
                                          `booknumber` int(5) DEFAULT NULL,
					  `published` tinyint(1) NOT NULL DEFAULT '1',
					  PRIMARY KEY (`id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg = $this->performdb($query);
        if (!$msg) {
            $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
        } else {
            $messages[] = $msg;
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
        $msg = $this->performdb($query);
        if (!$msg) {
            $messages[] = '<font color="green">' . JText::_('JBS_IBM_QUERY_SUCCESS') . ': ' . $query . ' </font><br /><br />';
        } else {
            $messages[] = $msg;
        }

//Fix studies params
        $query = "SELECT `id`, `params` FROM #__bsms_studies";
        $db->setQuery($query);
        $db->query();
        $results = $db->loadObjectList();
        if ($results) {
            foreach ($results AS $result) {
                //Update the params to json
                $registry = new JRegistry;
                $registry->loadJSON($result->params);
                $params = $registry;

                $params2 = $params->toObject();
                $params2 = json_encode($params2);
                $query = "UPDATE #__bsms_studies SET `params` = '$params2' WHERE `id` = $result->id LIMIT 1";
                $msg = $this->performdb($query);
                if ($msg) {
                    $messages[] = $msg;
                }
            }
        }

//Fix topics text
        $query = "SELECT `id`, `topic_text` FROM #__bsms_topics";
        $db->setQuery($query);
        $db->query();
        $results = $db->loadObjectList();
        if ($results) {
            foreach ($results AS $result) {
                $topic = $result->topic_text;
                $topic = 'JBS_TOP_' . strtoupper(preg_replace('/[^a-z0-9]/i', '_', $topic));  // replace all non a-Z 0-9 by '_'
                $query = "UPDATE #__bsms_topics SET `topic_text` = '$topic' WHERE `id` = $result->id";
                $msg = $this->performdb($query);
                if ($msg) {
                    $messages[] = $msg;
                }
            }
        }

//Fix share params
        $query = "SELECT `id`, `params` FROM #__bsms_share";
        $db->setQuery($query);
        $db->query();
        $results = $db->loadObjectList();
        if ($results) {
            foreach ($results AS $result) {
                //Update the params to json
                $registry = new JRegistry;
                $registry->loadJSON($result->params);
                $params = $registry;

                $params2 = $params->toObject();
                $params2 = json_encode($params2);
                $query = "UPDATE #__bsms_share SET `params` = '$params2' WHERE `id` = $result->id LIMIT 1";
                $msg = $this->performdb($query);
                if ($msg) {
                    $messages[] = $msg;
                }
            }
        }


//Fix template params
        $query = "SELECT `id`, `params` FROM #__bsms_templates";
        $db->setQuery($query);
        $db->query();
        $results = $db->loadObjectList();
        if ($results) {
            foreach ($results AS $result) {
                //Update the params to json
                $registry = new JRegistry;
                $registry->loadJSON($result->params);
                $params = $registry;

                $params2 = $params->toObject();
                $params2 = json_encode($params2);
                $query = "UPDATE #__bsms_templates SET `params` = '$params2' WHERE `id` = $result->id LIMIT 1";
                $msg = $this->performdb($query);
                if ($msg) {
                    $messages[] = $msg;
                }
            }
        }
        $fresult = array('build' => '700', 'messages' => $messages);

        return $fresult;
    }

    /**
     * Perform DB Query
     * @param string $query
     * @return string|boolean
     */
    protected function performdb($query) {
        $db = JFactory::getDBO();
        $results = false;
        $db->setQuery($query);
        $db->query();
        if ($db->getErrorNum() != 0) {
            $results = JText::_('JBS_IBM_DB_ERROR') . ': ' . $db->getErrorNum() . "<br /><font color=\"red\">";
            $results .= $db->stderr(true);
            $results .= "</font>";
            return $results;
        } else {
            $results = false;
            return $results;
        }
    }

    /**
     * Add Asset Column
     * @param array $table
     * @return objects
     */
    protected function addAssetColumn($table) {
        $msg = array();
        $dbhelper = new jbsDBhelper();
        if (jbsDBhelper::checkTables($table, 'asset_id') !== TRUE) {
            $array = array(array('table' => $table, 'field' => 'asset_id', 'type' => 'ADD', 'command' => "int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.'"));
            $alteradmin = $dbhelper->alterDB($array);
            if ($alteradmin !== TRUE) {
                $msg[] = $alteradmin;
            }
        }
        if (jbsDBhelper::checkTables($table, 'access') !== TRUE) {
            $array = array(array('table' => $table, 'field' => 'access', 'type' => 'ADD', 'command' => "int(10) unsigned NOT NULL DEFAULT '0'"));
            $alteradmin = $dbhelper->alterDB($array);
            if ($alteradmin !== TRUE) {
                $msg[] = $alteradmin;
            }
        }
        return $msg;
    }

}
