-- Servers
ALTER TABLE `#__bsms_servers` ADD COLUMN `params` TEXT NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `media` TEXT NOT NULL;
ALTER TABLE `#__bsms_servers` MODIFY `type` CHAR(255) NOT NULL;

-- -- MediaFiles
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `server_id` INT(5) NULL AFTER `study_id`;
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `metadata` TEXT NOT NULL AFTER `params`;
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `checked_out` INT(11) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `#__bsms_mediafiles` ADD COLUMN `created_by_alias` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `#__bsms_mediafiles` ADD INDEX `Idx_checkout` (`checked_out`);
ALTER TABLE `#__bsms_mediafiles` ADD INDEX `idx_createdby` (`created_by`);

-- -- Remove Bad topic_text save
DELETE FROM `#__bsms_topics` WHERE `topic_text` = 'A';

-- -- Studies
ALTER TABLE `#__bsms_studies` ADD COLUMN `created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `#__bsms_studies` ADD COLUMN `created_by_alias` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `#__bsms_studies` ADD COLUMN `checked_out` INT(11) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `#__bsms_studies` ADD COLUMN `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__bsms_studies` ADD COLUMN `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__bsms_studies` ADD COLUMN `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__bsms_studies` ADD INDEX `idx_createdby` (`created_by`);
ALTER TABLE `#__bsms_studies` ADD INDEX `Idx_checkout` (`checked_out`);
