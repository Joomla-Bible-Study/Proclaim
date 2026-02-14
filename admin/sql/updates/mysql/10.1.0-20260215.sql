--
-- Add full-size image column to studies table.
-- Stores the path to the original uploaded image (not the thumb_ thumbnail).
-- Existing records: back-fill from thumbnailm by stripping the thumb_ prefix.
--

ALTER TABLE `#__bsms_studies`
    ADD COLUMN `image` TEXT DEFAULT NULL AFTER `thumbnailm`;

-- Back-fill image from thumbnailm for existing records that have thumb_ prefix
UPDATE `#__bsms_studies`
SET `image` = CONCAT(
    SUBSTRING_INDEX(`thumbnailm`, '/', CHAR_LENGTH(`thumbnailm`) - CHAR_LENGTH(REPLACE(`thumbnailm`, '/', ''))),
    '/',
    SUBSTRING(SUBSTRING_INDEX(`thumbnailm`, '/', -1), 7)
)
WHERE `thumbnailm` IS NOT NULL
  AND `thumbnailm` != ''
  AND SUBSTRING_INDEX(`thumbnailm`, '/', -1) LIKE 'thumb_%';
