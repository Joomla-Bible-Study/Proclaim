--
-- Add checked_out and checked_out_time columns for check-in/check-out support
-- (Each ALTER must be a separate statement for Joomla ChangeSet parser compatibility)
--

ALTER TABLE `#__bsms_teachers` ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`;
ALTER TABLE `#__bsms_teachers` ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`;
ALTER TABLE `#__bsms_teachers` ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_series` ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`;
ALTER TABLE `#__bsms_series` ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`;
ALTER TABLE `#__bsms_series` ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_topics` ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`;
ALTER TABLE `#__bsms_topics` ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`;
ALTER TABLE `#__bsms_topics` ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_message_type` ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`;
ALTER TABLE `#__bsms_message_type` ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`;
ALTER TABLE `#__bsms_message_type` ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_servers` ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`;
ALTER TABLE `#__bsms_servers` ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`;
ALTER TABLE `#__bsms_servers` ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_podcast` ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`;
ALTER TABLE `#__bsms_podcast` ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`;
ALTER TABLE `#__bsms_podcast` ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_templates` ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`;
ALTER TABLE `#__bsms_templates` ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`;
ALTER TABLE `#__bsms_templates` ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_templatecode` ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`;
ALTER TABLE `#__bsms_templatecode` ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`;
ALTER TABLE `#__bsms_templatecode` ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_comments` ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `language`;
ALTER TABLE `#__bsms_comments` ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`;
ALTER TABLE `#__bsms_comments` ADD KEY `idx_checkout` (`checked_out`);

-- Add missing index to locations (columns already exist)
ALTER TABLE `#__bsms_locations` ADD KEY `idx_checkout` (`checked_out`);