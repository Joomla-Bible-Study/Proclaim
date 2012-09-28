INSERT INTO `#__bsms_update` (id,version) VALUES (7,'7.1.0')
ON DUPLICATE KEY UPDATE version= '7.1.0';

--
-- Admin Table
--
ALTER TABLE `#__bsms_admin` ADD COLUMN `installstate` TEXT;
ALTER TABLE `#__bsms_admin` ADD `debug` TINYINT( 3 ) NOT NULL DEFAULT '0';

--
-- Books
--
ALTER TABLE `#__bsms_books` MODIFY `published` tinyint(3) NOT NULL DEFAULT '1';

--
-- Comments Table
--
ALTER TABLE `#__bsms_comments` ADD COLUMN `language` CHAR( 7 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'The language code for the Comments.';

UPDATE `#__bsms_comments` SET `language` = '*' WHERE `#__bsms_comments`.`language` = '';

--
-- Folders Table
--

--
-- Locations Table
--
ALTER TABLE `#__bsms_locations` ADD COLUMN `landing_show` int(3) DEFAULT '1';

--
-- Media Table
--

--
-- MediaFiles Table
--
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `language` CHAR( 7 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'The language code for the MediaFile.';
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `created_by` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `created_by_alias` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `modified_by` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `#__bsms_mediafiles` ADD INDEX `idx_study_id` ( `study_id` );

UPDATE `#__bsms_mediafiles` SET `language` = '*' WHERE `#__bsms_mediafiles`.`language` = '';

--
-- Message Type Table
--
ALTER TABLE `#__bsms_message_type` ADD COLUMN `landing_show` INT(3) DEFAULT '1';

--
-- MimType Table
--

--
-- Order Table
--
ALTER TABLE `#__bsms_order` ADD COLUMN `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.';

ALTER TABLE `#__bsms_order` ADD COLUMN `access` int(10) unsigned NOT NULL DEFAULT '0';

ALTER TABLE `#__bsms_order` ADD INDEX `idx_access` (`access`);

--
-- Podcast Table
--
ALTER TABLE `#__bsms_podcast` ADD COLUMN `alternatelink` varchar(300) COMMENT 'replaces podcast file link on subscription';
ALTER TABLE `#__bsms_podcast` ADD COLUMN `alternateimage` varchar(150) COMMENT 'alternate image path for podcast';
ALTER TABLE `#__bsms_podcast` ADD COLUMN `podcast_subscribe_show` int(3);
ALTER TABLE `#__bsms_podcast` ADD COLUMN `podcast_image_subscribe` VARCHAR(150) COMMENT 'The image to use for the podcast subscription image';
ALTER TABLE `#__bsms_podcast` ADD COLUMN `podcast_subscribe_desc` VARCHAR(150) COMMENT 'Words to go below podcast subscribe image';
ALTER TABLE `#__bsms_podcast` ADD COLUMN `alternatewords` varchar(20);

--
-- Search Table
--

--
-- Series Table
--

ALTER TABLE `#__bsms_series` ADD COLUMN `language` CHAR( 7 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'The language code for the Series.';

UPDATE `#__bsms_series` SET `language` = '*' WHERE `#__bsms_series`.`language` = '';

ALTER TABLE `#__bsms_series` ADD COLUMN `landing_show` INT(3) DEFAULT '1';

--
-- Servers Table
--
ALTER TABLE `#__bsms_servers` ADD COLUMN `type` tinyint(3) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `ftphost` varchar(100) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `ftpuser` varchar(250) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `ftppassword` varchar(250) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `ftpport` varchar(10) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `aws_key` varchar(100) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `aws_secret` varchar(100) NOT NULL;

--
-- Share Table
-- @todo need to look at a better way to do this sql
--
UPDATE `#__bsms_share` SET `params` = '{"mainlink":"http://www.facebook.com/sharer.php?","item1prefix":"u=","item1":200,"item1custom":"","item2prefix":"t=","item2":5,"item2custom":"","item3prefix":"","item3":6,"item3custom":"","item4prefix":"","item4":8,"item4custom":"","use_bitly":0,"username":"","api":"","shareimage":"media/com_biblestudy/images/facebook.png","shareimageh":"33px","shareimagew":"33px","totalcharacters":"","alttext":"FaceBook"}' WHERE `#__bsms_share`.`id` = 1;

UPDATE `#__bsms_share` SET `params` = '{"mainlink":"http://twitter.com/?","item1prefix":"status=","item1":200,"item1custom":"","item2prefix":"","item2":5,"item2custom":"","item3prefix":"","item3":1,"item3custom":"","item4prefix":"","item4":0,"item4custom":"","use_bitly":0,"username":"","api":"","shareimage":"media/com_biblestudy/images/twitter.png","shareimageh":"33px","shareimagew":"33px","totalcharacters":140,"alttext":"Twitter"}' WHERE `#__bsms_share`.`id` = 2;

UPDATE `#__bsms_share` SET `params` = '{"mainlink":"http://delicious.com/save?","item1prefix":"url=","item1":200,"item1custom":"","item2prefix":"&title=","item2":5,"item2custom":"","item3prefix":"","item3":6,"item3custom":"","item4prefix":"","item4":"","item4custom":"","use_bitly":0,"username":"","api":"","shareimage":"media/com_biblestudy/images/delicious.png","shareimagew":"33px","shareimageh":"33px","totalcharacters":"","alttext":"Delicious"}' WHERE `#__bsms_share`.`id` = 3;

UPDATE `#__bsms_share` SET `params` = '{"mainlink":"http://www.myspace.com/index.cfm?","item1prefix":"fuseaction=postto&t=","item1":5,"item1custom":"","item2prefix":"&c=","item2":6,"item2custom":"","item3prefix":"&u=","item3":200,"item3custom":"","item4prefix":"&l=1","item4":"","item4custom":"","use_bitly":0,"username":"","api":"","shareimage":"media/com_biblestudy/images/myspace.png","shareimagew":"33px","shareimageh":"33px","totalcharacters":"","alttext":"MySpace"}' WHERE `#__bsms_share`.`id` = 4;

--
-- Studies Table
--
ALTER TABLE `#__bsms_studies` ADD COLUMN `language` CHAR( 7 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'The language code for the Studies.';
ALTER TABLE `#__bsms_studies` ADD INDEX `idx_seriesid` ( `series_id` );
ALTER TABLE `#__bsms_studies`ADD INDEX `idx_topicsid` ( `topics_id` );
ALTER TABLE `#__bsms_studies`ADD INDEX `idx_user` ( `user_id` );
UPDATE `#__bsms_studies` SET `language` = '*' WHERE `#__bsms_studies`.`language` = '';

--
-- StudyTopics Table
--
ALTER TABLE `#__bsms_studytopics` ADD INDEX `idx_study` ( `study_id` );
ALTER TABLE `#__bsms_studytopics` ADD INDEX `idx_topic` ( `topic_id` );

--
-- Style Table
--
DROP TABLE IF EXISTS `#__bsms_styles`;
CREATE TABLE IF NOT EXISTS `#__bsms_styles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `filename` text NOT NULL,
  `stylecode` longtext NOT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`)
) DEFAULT CHARSET=utf8;

--
-- Teachers Table
--

ALTER TABLE `#__bsms_teachers` ADD COLUMN `language` CHAR( 7 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'The language code for the Teachers.';
ALTER TABLE `#__bsms_teachers` ADD COLUMN `facebooklink` varchar(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `twitterlink` varchar(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `bloglink` varchar(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `link1` varchar(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `linklabel1` varchar(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `link2` varchar(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `linklabel2` varchar(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `link3` varchar(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `linklabel3` varchar(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `contact` int(11);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `address` mediumtext NOT NULL;
ALTER TABLE `#__bsms_teachers` ADD COLUMN `landing_show` int(3) DEFAULT '1';
ALTER TABLE `#__bsms_teachers` ADD COLUMN `address1` mediumtext NOT NULL;
UPDATE `#__bsms_teachers` SET `language` = '*' WHERE `#__bsms_teachers`.`language` = '';


--
-- TemplateCode Table
--
-- new table for TemplateCode
DROP TABLE IF EXISTS `#__bsms_templatecode`;
CREATE TABLE IF NOT EXISTS `#__bsms_templatecode` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `type` tinyint(3) NOT NULL,
  `filename` text NOT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `templatecode` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Templates Table
--

--
-- Tiemset Table
--

--
-- Topics Table
--
