--
-- Add bible_version column to studies table for per-message Bible version selection
--

ALTER TABLE `#__bsms_studies` ADD COLUMN `bible_version` VARCHAR(20) DEFAULT NULL AFTER `verse_end2`;
