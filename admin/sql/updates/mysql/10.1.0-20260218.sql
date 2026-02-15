--
-- Multiple teachers per sermon: junction table and data migration.
--

-- Step 1: Create study_teachers junction table
CREATE TABLE IF NOT EXISTS `#__bsms_study_teachers` (
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `study_id`   INT(10) UNSIGNED NOT NULL,
    `teacher_id` INT(10) UNSIGNED NOT NULL,
    `ordering`   INT(3)           NOT NULL DEFAULT 0,
    `role`       VARCHAR(50)      NOT NULL DEFAULT 'speaker',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_study_teacher` (`study_id`, `teacher_id`),
    KEY `idx_teacher` (`teacher_id`),
    KEY `idx_study_ordering` (`study_id`, `ordering`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Step 2: Migrate existing teacher_id data into junction table
INSERT IGNORE INTO `#__bsms_study_teachers` (`study_id`, `teacher_id`, `ordering`, `role`)
SELECT `id`, `teacher_id`, 0, 'speaker'
FROM `#__bsms_studies`
WHERE `teacher_id` IS NOT NULL AND `teacher_id` > 0;
