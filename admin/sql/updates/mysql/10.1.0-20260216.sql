--
-- Add full-size image column to series table.
-- Stores the path to the original uploaded image (not the series_thumbnail).
-- Existing records: back-fill from series_thumbnail by stripping the thumb_ prefix.
--

ALTER TABLE `#__bsms_series`
    ADD COLUMN `image` TEXT DEFAULT NULL AFTER `series_thumbnail`;

-- Back-fill image from series_thumbnail for existing records that have thumb_ prefix
UPDATE `#__bsms_series`
SET `image` = CONCAT(
    SUBSTRING_INDEX(`series_thumbnail`, '/', CHAR_LENGTH(`series_thumbnail`) - CHAR_LENGTH(REPLACE(`series_thumbnail`, '/', ''))),
    '/',
    SUBSTRING(SUBSTRING_INDEX(`series_thumbnail`, '/', -1), 7)
)
WHERE `series_thumbnail` IS NOT NULL
  AND `series_thumbnail` != ''
  AND SUBSTRING_INDEX(`series_thumbnail`, '/', -1) LIKE 'thumb_%';

-- For records where series_thumbnail does NOT have thumb_ prefix, copy as-is
UPDATE `#__bsms_series`
SET `image` = `series_thumbnail`
WHERE `series_thumbnail` IS NOT NULL
  AND `series_thumbnail` != ''
  AND `image` IS NULL
  AND SUBSTRING_INDEX(`series_thumbnail`, '/', -1) NOT LIKE 'thumb_%';
