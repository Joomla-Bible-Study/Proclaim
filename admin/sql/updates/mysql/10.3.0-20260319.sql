-- Migrate landing_ordering data into ordering column, then drop landing_ordering
UPDATE `#__bsms_teachers`
SET `ordering` = `landing_ordering`
WHERE `landing_ordering` > 0 AND (`ordering` = 0 OR `ordering` = '' OR `ordering` IS NULL);

-- Set alphabetical ordering for any teachers that still have no ordering value
SET @row_number = 0;
UPDATE `#__bsms_teachers` AS t
    INNER JOIN (
        SELECT `id`, (@row_number := @row_number + 1) AS new_ordering
        FROM `#__bsms_teachers`
        WHERE `ordering` = 0 OR `ordering` = '' OR `ordering` IS NULL
        ORDER BY `teachername` ASC
    ) AS ranked ON t.`id` = ranked.`id`
SET t.`ordering` = ranked.`new_ordering`;

ALTER TABLE `#__bsms_teachers` DROP COLUMN IF EXISTS `landing_ordering`;

-- Add organization name field to teachers for Schema.org worksFor override
ALTER TABLE `#__bsms_teachers` ADD COLUMN `org_name` VARCHAR(255) DEFAULT NULL AFTER `title`;

-- Add Joomla user account linkage for teacher auto-access
ALTER TABLE `#__bsms_teachers`
    ADD COLUMN `user_id` INT(10) UNSIGNED DEFAULT NULL AFTER `contact`,
    ADD KEY `idx_teacher_user` (`user_id`);

-- Podcasting 2.0: optional channel-level fields
ALTER TABLE `#__bsms_podcast`
    ADD COLUMN `funding_url` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN `funding_text` VARCHAR(100) DEFAULT NULL,
    ADD COLUMN `podcast_license` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN `podcast_license_url` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN `podcast_publisher` VARCHAR(150) DEFAULT NULL,
    ADD COLUMN `podcast_txt_verify` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN `update_frequency` VARCHAR(20) DEFAULT NULL;
