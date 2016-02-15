INSERT INTO `#__bsms_update` (id, version) VALUES (19, '9.0.0')
ON DUPLICATE KEY UPDATE version = '9.0.0';

-- Servers
ALTER TABLE `#__bsms_servers` ADD COLUMN `params` TEXT NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `media` TEXT NOT NULL;
ALTER TABLE `#__bsms_servers` MODIFY COLUMN `type` CHAR(255) NOT NULL;

-- -- MediaFiles
ALTER TABLE `#__bsms_mediafiles` MODIFY COLUMN `hits` INT (10) DEFAULT '0';
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `server_id` INT(5) NULL AFTER `study_id`;
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `metadata` TEXT NOT NULL AFTER `podcast_id`;
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `checked_out` INT(11) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__bsms_mediafiles` ADD INDEX `idx_checkout` (`checked_out`);
ALTER TABLE `#__bsms_mediafiles` ADD INDEX `idx_createdby` (`created_by`);

-- -- Remove Bad topic_text save
DELETE FROM `#__bsms_topics` WHERE `topic_text` = 'A';

-- -- Remove Old tables
DROP TABLE IF EXISTS `#__bsms_order`;
DROP TABLE IF EXISTS `#__bsms_search`;
DROP TABLE IF EXISTS `#__bsms_styles`;

-- -- Locations
ALTER TABLE `#__bsms_locations` ADD COLUMN `contact_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Used to link to com_contact' AFTER `location_text`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `address` TEXT AFTER `contact_id`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `suburb` VARCHAR(100) DEFAULT NULL AFTER `address`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `state` VARCHAR(100) DEFAULT NULL AFTER `suburb`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `country` VARCHAR(100) DEFAULT NULL AFTER `state`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `postcode` VARCHAR(100) DEFAULT NULL AFTER `country`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `telephone` VARCHAR(255) DEFAULT NULL AFTER `postcode`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `fax` VARCHAR(255) DEFAULT NULL AFTER `telephone`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `misc` MEDIUMTEXT AFTER `fax`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `image` VARCHAR(255) DEFAULT NULL AFTER `misc`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `email_to` VARCHAR(255) DEFAULT NULL AFTER `image`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `default_con` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `email_to`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `default_con`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `checked_out`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `params` TEXT NOT NULL AFTER `checked_out_time`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `user_id` INT(11) NOT NULL DEFAULT '0' AFTER `params`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `mobile` VARCHAR(255) NOT NULL DEFAULT '' AFTER `user_id`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `webpage` VARCHAR(255) NOT NULL DEFAULT '' AFTER `mobile`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `sortname1` VARCHAR(255) NOT NULL AFTER `webpage`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `sortname2` VARCHAR(255) NOT NULL AFTER `sortname1`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `sortname3` VARCHAR(255) NOT NULL AFTER `sortname2`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `language` CHAR(7) NOT NULL AFTER `sortname3`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `language`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `created`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `created_by_alias` VARCHAR(255) NOT NULL DEFAULT '' AFTER `created_by`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by_alias`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `modified`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `metakey` TEXT NOT NULL AFTER `modified_by`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `metadesc` TEXT NOT NULL AFTER `metakey`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `metadata` TEXT NOT NULL AFTER `metadesc`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `featured` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Set if article is featured.' AFTER `metadata`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `xreference` VARCHAR(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.' AFTER `featured`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `version` INT(10) UNSIGNED NOT NULL DEFAULT '1' AFTER `xreference`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `hits` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `version`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `hits`;
ALTER TABLE `#__bsms_locations` ADD COLUMN `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `publish_up`;

-- -- Studies
ALTER TABLE `#__bsms_studies` ADD COLUMN `checked_out` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `params`;
ALTER TABLE `#__bsms_studies` ADD COLUMN `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `checked_out`;
ALTER TABLE `#__bsms_studies` ADD COLUMN `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `published`;
ALTER TABLE `#__bsms_studies` ADD COLUMN `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `publish_up`;
ALTER TABLE `#__bsms_studies` ADD COLUMN `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `publish_down`;
ALTER TABLE `#__bsms_studies` ADD COLUMN `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `modified`;
ALTER TABLE `#__bsms_studies` ADD INDEX `idx_createdby` (`user_id`);
ALTER TABLE `#__bsms_studies` ADD INDEX `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_podcast` ADD COLUMN `linktype` INT(10) NOT NULL  DEFAULT '0' AFTER `customsubtitle`;
-- drop all asset ides in php referring to folder media and memtype;
