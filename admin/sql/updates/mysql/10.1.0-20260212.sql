--
-- Scripture references junction table for unlimited per-message Bible references
-- Replaces the fixed 2-slot columns on #__bsms_studies with a flexible junction table
--

CREATE TABLE IF NOT EXISTS `#__bsms_study_scriptures` (
    `id`             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `study_id`       INT(10) UNSIGNED NOT NULL,
    `ordering`       INT(3)           NOT NULL DEFAULT 0,
    `booknumber`     INT(3)           NOT NULL DEFAULT 0,
    `chapter_begin`  INT(3)           NOT NULL DEFAULT 0,
    `verse_begin`    INT(3)           NOT NULL DEFAULT 0,
    `chapter_end`    INT(3)           NOT NULL DEFAULT 0,
    `verse_end`      INT(3)           NOT NULL DEFAULT 0,
    `bible_version`  VARCHAR(20)      NOT NULL DEFAULT '',
    `reference_text` VARCHAR(255)     NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `idx_study_ordering` (`study_id`, `ordering`),
    KEY `idx_booknumber` (`booknumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
