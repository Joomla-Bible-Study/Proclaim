CREATE TABLE IF NOT EXISTS `#__bsms_update` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  version VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

INSERT INTO `#__bsms_update` (id, version) VALUES (13, '7.1.0')
ON DUPLICATE KEY UPDATE version= '7.1.0';
--
-- Admin Table
--
ALTER TABLE `#__bsms_admin` ADD COLUMN `installstate` TEXT;
ALTER TABLE `#__bsms_admin` ADD COLUMN `debug` TINYINT(3) NOT NULL DEFAULT '0';
--
-- Books
--
ALTER TABLE `#__bsms_books` MODIFY `published` TINYINT(3) NOT NULL DEFAULT '1';
--
-- Comments Table
--
ALTER TABLE `#__bsms_comments` ADD COLUMN `language` CHAR(3) NOT NULL DEFAULT '';
UPDATE `#__bsms_comments` SET `language` = '*' WHERE `#__bsms_comments`.`language` = '';
--
-- Folders Table
--
--
-- Locations Table
--
ALTER TABLE `#__bsms_locations` ADD COLUMN `landing_show` INT(3) DEFAULT '1';
--
-- Media Table
--
--
-- MediaFiles Table

ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `language` CHAR(3) NOT NULL DEFAULT '';
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `created_by_alias` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `#__bsms_mediafiles` ADD INDEX `idx_study_id` (`study_id`);
UPDATE `#__bsms_mediafiles` SET `language` = '*' WHERE `#__bsms_mediafiles`.`language` = '';
--
-- Message Type Table
--
ALTER TABLE `#__bsms_message_type` ADD COLUMN `landing_show` INT(3) DEFAULT '1';
--
-- MimType Table
--
--
-- Podcast Table
--
ALTER TABLE `#__bsms_podcast` ADD COLUMN `alternatelink` VARCHAR(300) COMMENT 'replaces podcast file link on subscription';
ALTER TABLE `#__bsms_podcast` ADD COLUMN `alternateimage` VARCHAR(150) COMMENT 'alternate image path for podcast';
ALTER TABLE `#__bsms_podcast` ADD COLUMN `podcast_subscribe_show` INT(3);
ALTER TABLE `#__bsms_podcast` ADD COLUMN `podcast_image_subscribe` VARCHAR(150) COMMENT 'The image to use for the podcast subscription image';
ALTER TABLE `#__bsms_podcast` ADD COLUMN `podcast_subscribe_desc` VARCHAR(150) COMMENT 'Words to go below podcast subscribe image';
ALTER TABLE `#__bsms_podcast` ADD COLUMN `alternatewords` VARCHAR(20);

--
-- Search Table
--

--
-- Series Table
--

ALTER TABLE `#__bsms_series` ADD COLUMN `language` CHAR(3) NOT NULL DEFAULT '';

UPDATE `#__bsms_series` SET `language` = '*' WHERE `#__bsms_series`.`language` = '';

ALTER TABLE `#__bsms_series` ADD COLUMN `landing_show` INT(3) DEFAULT '1';

--
-- Servers Table
--
ALTER TABLE `#__bsms_servers` ADD COLUMN `type` TINYINT(3) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `ftphost` VARCHAR(100) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `ftpuser` VARCHAR(250) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `ftppassword` VARCHAR(250) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `ftpport` VARCHAR(10) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `aws_key` VARCHAR(100) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `aws_secret` VARCHAR(100) NOT NULL;

--
-- Studies Table
--
ALTER TABLE `#__bsms_studies` ADD COLUMN `language` CHAR(3) NOT NULL DEFAULT '';
ALTER TABLE `#__bsms_studies` ADD INDEX `idx_seriesid` (`series_id`);
ALTER TABLE `#__bsms_studies` ADD INDEX `idx_user` (`user_id`);
UPDATE `#__bsms_studies` SET `language` = '*' WHERE `#__bsms_studies`.`language` = '';

--
-- StudyTopics Table
--
ALTER TABLE `#__bsms_studytopics` ADD INDEX `idx_study` (`study_id`);
ALTER TABLE `#__bsms_studytopics` ADD INDEX `idx_topic` (`topic_id`);

--
-- Teachers Table
--

ALTER TABLE `#__bsms_teachers` ADD COLUMN `language` CHAR(3) NOT NULL DEFAULT '';
ALTER TABLE `#__bsms_teachers` ADD COLUMN `facebooklink` VARCHAR(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `twitterlink` VARCHAR(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `bloglink` VARCHAR(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `link1` VARCHAR(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `linklabel1` VARCHAR(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `link2` VARCHAR(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `linklabel2` VARCHAR(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `link3` VARCHAR(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `linklabel3` VARCHAR(150);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `contact` INT(11);
ALTER TABLE `#__bsms_teachers` ADD COLUMN `address` MEDIUMTEXT NOT NULL;
ALTER TABLE `#__bsms_teachers` ADD COLUMN `landing_show` INT(3) DEFAULT '1';
ALTER TABLE `#__bsms_teachers` ADD COLUMN `address1` MEDIUMTEXT NOT NULL;
UPDATE `#__bsms_teachers` SET `language` = '*' WHERE `#__bsms_teachers`.`language` = '';

--
-- TemplateCode Table
--
-- new table for TemplateCode
DROP TABLE IF EXISTS `#__bsms_templatecode`;
CREATE TABLE IF NOT EXISTS `#__bsms_templatecode` (
  `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `published`    TINYINT(3)       NOT NULL DEFAULT '1',
  `type`         TINYINT(3)       NOT NULL,
  `filename`     TEXT             NOT NULL,
  `asset_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `templatecode` MEDIUMTEXT       NOT NULL,
  PRIMARY KEY (`id`)
)
  DEFAULT CHARSET =utf8;

--
-- Templates Table
--

--
-- Time set Table
--

--
-- Topics Table
--
