--
-- Add checked_out and checked_out_time columns for check-in/check-out support
--

ALTER TABLE `#__bsms_teachers`
    ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`,
    ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`,
    ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_series`
    ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`,
    ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`,
    ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_topics`
    ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`,
    ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`,
    ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_message_type`
    ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`,
    ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`,
    ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_servers`
    ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`,
    ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`,
    ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_podcast`
    ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`,
    ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`,
    ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_templates`
    ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`,
    ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`,
    ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_templatecode`
    ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_by`,
    ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`,
    ADD KEY `idx_checkout` (`checked_out`);

ALTER TABLE `#__bsms_comments`
    ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `language`,
    ADD COLUMN `checked_out_time` DATETIME DEFAULT NULL AFTER `checked_out`,
    ADD KEY `idx_checkout` (`checked_out`);

-- Add missing index to locations (columns already exist)
ALTER TABLE `#__bsms_locations`
    ADD KEY `idx_checkout` (`checked_out`);
