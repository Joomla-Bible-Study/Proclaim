--
-- Multiple teachers per sermon: junction table.
-- Data migration (INSERT IGNORE from legacy teacher_id) is handled by the
-- populateStudyTeachers() PHP finish step because Joomla ChangeSet skips
-- INSERT/UPDATE/DELETE statements.
--

-- Create study_teachers junction table
CREATE TABLE IF NOT EXISTS `#__bsms_study_teachers` (
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `study_id`   INT(10) UNSIGNED NOT NULL,
    `teacher_id` INT(10) UNSIGNED NOT NULL,
    `ordering`   INT(3)           NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_study_teacher` (`study_id`, `teacher_id`),
    KEY `idx_teacher` (`teacher_id`),
    KEY `idx_study_ordering` (`study_id`, `ordering`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
