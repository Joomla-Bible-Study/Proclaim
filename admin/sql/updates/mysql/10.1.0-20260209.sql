-- Add bible_version columns to studies table for per-scripture Bible version selection
ALTER TABLE `#__bsms_studies` ADD COLUMN `bible_version` VARCHAR(20) DEFAULT NULL AFTER `verse_end2`;
ALTER TABLE `#__bsms_studies` ADD COLUMN `bible_version2` VARCHAR(20) DEFAULT NULL AFTER `bible_version`;

-- Bible translation table operations now managed by lib_cwmscripture
SELECT 1;
