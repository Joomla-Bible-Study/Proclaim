-- Servers
ALTER TABLE `#__bsms_servers` ADD `params` TEXT NOT NULL;
ALTER TABLE `#__bsms_servers` ADD `media` TEXT NOT NULL;
ALTER TABLE `#__bsms_servers` MODIFY `type` CHAR(255) NOT NULL;

-- -- MediaFiles
ALTER TABLE `#__bsms_mediafiles` ADD `server_id` int(5) NULL AFTER `study_id`;
ALTER TABLE `#__bsms_mediafiles` ADD `metadata` TEXT NOT NULL AFTER `params`;

-- -- Remove Bad topic_text save
DELETE FROM `#__bsms_topics` WHERE `topic_text` = 'A';
