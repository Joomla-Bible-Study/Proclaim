CREATE TABLE IF NOT EXISTS `#__bsms_update` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  version VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

INSERT INTO `#__bsms_update` (id, version) VALUES (10, '7.0.2')
ON DUPLICATE KEY UPDATE version= '7.0.2';
--
-- Old Tales No longer used.
--
DROP TABLE IF EXISTS `#__bsms_install`;
DROP TABLE IF EXISTS `#__bsms_version`;

--
-- Menu Icon Corrections
--
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` ='jbscmncombiblestudy';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` ='jbsmnucontrolpanel';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-mediaimages.png' WHERE `#__menu`.`alias` ='jbsmnumediaimages';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-templates.png' WHERE `#__menu`.`alias` ='jbsmnutemplatedisplay';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-social.png' WHERE `#__menu`.`alias` ='jbsmnusocialnetworklinks';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-podcast.png' WHERE `#__menu`.`alias` ='jbsmnupodcasts';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-folder.png' WHERE `#__menu`.`alias` ='jbsmnuserverfolders';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-servers.png' WHERE `#__menu`.`alias` ='jbsmnuservers';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-comments.png' WHERE `#__menu`.`alias` ='jbsmnustudycomments';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-topics.png' WHERE `#__menu`.`alias` ='jbsmnutopics';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-locations.png' WHERE `#__menu`.`alias` ='jbsmnulocations';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-messagetype.png' WHERE `#__menu`.`alias` ='jbsmnumessagetypes';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-series.png' WHERE `#__menu`.`alias` ='jbsmnuseries';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-teachers.png' WHERE `#__menu`.`alias` ='jbsmnuteachers';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-mp3.png' WHERE `#__menu`.`alias` ='jbsmnumediafiles';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-studies.png' WHERE `#__menu`.`alias` ='jbsmnustudies';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-administration.png' WHERE `#__menu`.`alias` ='jbsmnuadministration';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-css.png' WHERE `#__menu`.`alias` ='jbsmnucssedit';
UPDATE `#__menu` SET `img` = '../media/com_biblestudy/images/menu/icon-16-mimetype.png' WHERE `#__menu`.`alias` ='jbsmnumimetypes';


--
-- Table Index Additions
--
--
-- Admin
--
ALTER TABLE `#__bsms_admin` ADD INDEX `idx_access` ( `access` ),
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Books
--
ALTER TABLE `#__bsms_books` ADD INDEX `idx_state` ( `published` ),
MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '1',
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Comments
--
ALTER TABLE `#__bsms_comments` ADD INDEX `idx_state` ( `published` );
ALTER TABLE `#__bsms_comments` ADD INDEX `idx_access` ( `access` ),
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '0',
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Folders
--
-- Moved to PHP builder

--
-- Locations
--
ALTER TABLE `#__bsms_locations` ADD INDEX `idx_state` ( `published` );
ALTER TABLE `#__bsms_locations` ADD INDEX `idx_access` ( `access` );
ALTER TABLE `#__bsms_locations` ADD COLUMN `ordering` INT( 11 ) NOT NULL DEFAULT '0',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '1',
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Media
--
-- Moved to PHP Builder


--
-- Mediafiles
--
ALTER TABLE `#__bsms_mediafiles` ADD INDEX `idx_state` ( `published` );
ALTER TABLE `#__bsms_mediafiles` ADD INDEX `idx_access` ( `access` ),
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '1',
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Message Type
--
ALTER TABLE `#__bsms_message_type` ADD INDEX `idx_state` ( `published` );
ALTER TABLE `#__bsms_message_type` ADD INDEX `idx_access` ( `access` );
ALTER TABLE `#__bsms_message_type` ADD COLUMN `ordering` INT( 11 ) NOT NULL DEFAULT '0',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.';
ALTER TABLE `#__bsms_message_type` ADD COLUMN `alias` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `message_type`,
MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '1',
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- MIME Type
--
-- Moved to PHP Builder


--
--
--
-- Podcast
--
ALTER TABLE `#__bsms_podcast` ADD INDEX `idx_state` ( `published` );
ALTER TABLE `#__bsms_podcast` ADD INDEX `idx_access` ( `access` ),
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '1',
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
--
--
-- Series
--
ALTER TABLE `#__bsms_series` ADD INDEX `idx_state` ( `published` );
ALTER TABLE `#__bsms_series` ADD INDEX `idx_access` ( `access` );
ALTER TABLE `#__bsms_series` ADD COLUMN `alias` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `series_text`;
ALTER TABLE `#__bsms_series` ADD COLUMN `ordering` INT( 11 ) NOT NULL DEFAULT '0',
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '1',
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Servers
--
ALTER TABLE `#__bsms_servers` ADD INDEX `idx_state` ( `published` );
ALTER TABLE `#__bsms_servers` ADD INDEX `idx_access` ( `access` ),
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '1',
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Studies
--
ALTER TABLE `#__bsms_studies` ADD INDEX `idx_state` ( `published` );
ALTER TABLE `#__bsms_studies` ADD INDEX `idx_access` ( `access` );
ALTER TABLE `#__bsms_studies` ADD COLUMN `ordering` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `#__bsms_studies` MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '0',
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
ADD COLUMN `alias` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `studytitle`,
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Studytopics
--
ALTER TABLE `#__bsms_studytopics` ADD INDEX `idx_access` ( `access` ),
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
-- The drop of Keys are in the update.701.php file
--	UNIQUE KEY `id` (`id`),
--	KEY `id_2` (`id`)
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Teachers
--
ALTER TABLE `#__bsms_teachers` ADD INDEX `idx_state` ( `published` );
ALTER TABLE `#__bsms_teachers` ADD INDEX `idx_access` ( `access` );
ALTER TABLE `#__bsms_teachers` MODIFY `ordering` INT( 11 ) NOT NULL DEFAULT '0',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '1';
ALTER TABLE `#__bsms_teachers` ADD COLUMN `alias` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `teachername`,
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Templates
--
ALTER TABLE `#__bsms_templates` ADD INDEX `idx_state` ( `published` );
ALTER TABLE `#__bsms_templates` ADD INDEX `idx_access` ( `access` ),
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '1',
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Topics
--
ALTER TABLE `#__bsms_topics` ADD INDEX `idx_state` ( `published` );
ALTER TABLE `#__bsms_topics` ADD INDEX `idx_access` ( `access` ),
MODIFY `asset_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
MODIFY `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
MODIFY `published` TINYINT( 3 ) NOT NULL DEFAULT '1',
MODIFY `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Drop `#__bsms_version`
--
DROP TABLE IF EXISTS `#__bsms_version`;
