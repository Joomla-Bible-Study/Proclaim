-- Add created_by and modified_by fields to tables that are missing them

-- bsms_message_type
ALTER TABLE `#__bsms_message_type`
    ADD COLUMN `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `landing_show`,
    ADD COLUMN `created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `created`,
    ADD COLUMN `created_by_alias` VARCHAR(255) NOT NULL DEFAULT '' AFTER `created_by`,
    ADD COLUMN `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by_alias`,
    ADD COLUMN `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `modified`;

-- bsms_podcast
ALTER TABLE `#__bsms_podcast`
    ADD COLUMN `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `linktype`,
    ADD COLUMN `created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `created`,
    ADD COLUMN `created_by_alias` VARCHAR(255) NOT NULL DEFAULT '' AFTER `created_by`,
    ADD COLUMN `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by_alias`,
    ADD COLUMN `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `modified`;

-- bsms_series
ALTER TABLE `#__bsms_series`
    ADD COLUMN `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `landing_show`,
    ADD COLUMN `created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `created`,
    ADD COLUMN `created_by_alias` VARCHAR(255) NOT NULL DEFAULT '' AFTER `created_by`,
    ADD COLUMN `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by_alias`,
    ADD COLUMN `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `modified`;

-- bsms_servers
ALTER TABLE `#__bsms_servers`
    ADD COLUMN `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `params`,
    ADD COLUMN `created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `created`,
    ADD COLUMN `created_by_alias` VARCHAR(255) NOT NULL DEFAULT '' AFTER `created_by`,
    ADD COLUMN `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by_alias`,
    ADD COLUMN `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `modified`;

-- bsms_studies (messages/sermons)
ALTER TABLE `#__bsms_studies`
    ADD COLUMN `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `landing_show`,
    ADD COLUMN `created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `created`,
    ADD COLUMN `created_by_alias` VARCHAR(255) NOT NULL DEFAULT '' AFTER `created_by`,
    ADD COLUMN `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by_alias`,
    ADD COLUMN `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `modified`;

-- bsms_teachers
ALTER TABLE `#__bsms_teachers`
    ADD COLUMN `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `landing_show`,
    ADD COLUMN `created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `created`,
    ADD COLUMN `created_by_alias` VARCHAR(255) NOT NULL DEFAULT '' AFTER `created_by`,
    ADD COLUMN `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by_alias`,
    ADD COLUMN `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `modified`;

-- bsms_templatecode
ALTER TABLE `#__bsms_templatecode`
    ADD COLUMN `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `asset_id`,
    ADD COLUMN `created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `created`,
    ADD COLUMN `created_by_alias` VARCHAR(255) NOT NULL DEFAULT '' AFTER `created_by`,
    ADD COLUMN `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by_alias`,
    ADD COLUMN `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `modified`;

-- bsms_templates
ALTER TABLE `#__bsms_templates`
    ADD COLUMN `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `asset_id`,
    ADD COLUMN `created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `created`,
    ADD COLUMN `created_by_alias` VARCHAR(255) NOT NULL DEFAULT '' AFTER `created_by`,
    ADD COLUMN `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by_alias`,
    ADD COLUMN `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `modified`;

-- bsms_topics
ALTER TABLE `#__bsms_topics`
    ADD COLUMN `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `params`,
    ADD COLUMN `created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `created`,
    ADD COLUMN `created_by_alias` VARCHAR(255) NOT NULL DEFAULT '' AFTER `created_by`,
    ADD COLUMN `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by_alias`,
    ADD COLUMN `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `modified`;

-- Add indexes for created_by where useful
ALTER TABLE `#__bsms_studies` ADD KEY `idx_createdby` (`created_by`);
ALTER TABLE `#__bsms_series` ADD KEY `idx_createdby` (`created_by`);
ALTER TABLE `#__bsms_teachers` ADD KEY `idx_createdby` (`created_by`);