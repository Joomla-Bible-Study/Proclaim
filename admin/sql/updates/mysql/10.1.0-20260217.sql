--
-- Teacher table cleanup: merge duplicate teachers, deduplicate aliases, add UNIQUE KEY, drop legacy columns.
--

-- Step 1: Ensure every teacher has an alias (generate from teachername if missing)
UPDATE `#__bsms_teachers`
SET `alias` = LOWER(REPLACE(REPLACE(REPLACE(TRIM(`teachername`), ' ', '-'), '''', ''), '"', ''))
WHERE `alias` = '' OR `alias` IS NULL;

-- Step 2: Merge duplicate teachers (same name, case-insensitive)
-- For each group of duplicates, keep the lowest ID and reassign sermons from the rest.
CREATE TEMPORARY TABLE `#__bsms_teachers_merge` AS
SELECT t1.`id` AS dup_id,
       (SELECT MIN(t2.`id`) FROM `#__bsms_teachers` t2
        WHERE LOWER(t2.`teachername`) = LOWER(t1.`teachername`)) AS keeper_id
FROM `#__bsms_teachers` t1
WHERE t1.`id` > (
    SELECT MIN(t3.`id`) FROM `#__bsms_teachers` t3
    WHERE LOWER(t3.`teachername`) = LOWER(t1.`teachername`)
);

-- Reassign sermons from duplicates to their keeper
UPDATE `#__bsms_studies` s
INNER JOIN `#__bsms_teachers_merge` m ON s.`teacher_id` = m.`dup_id`
SET s.`teacher_id` = m.`keeper_id`;

-- Reassign series from duplicates to their keeper
UPDATE `#__bsms_series` sr
INNER JOIN `#__bsms_teachers_merge` m ON sr.`teacher` = m.`dup_id`
SET sr.`teacher` = m.`keeper_id`;

-- Remove asset entries for duplicate teachers
DELETE FROM `#__assets`
WHERE `name` IN (
    SELECT CONCAT('com_proclaim.teacher.', `dup_id`) FROM `#__bsms_teachers_merge`
);

-- Delete duplicate teacher records
DELETE FROM `#__bsms_teachers`
WHERE `id` IN (SELECT `dup_id` FROM `#__bsms_teachers_merge`);

DROP TEMPORARY TABLE `#__bsms_teachers_merge`;

-- Step 3: Deduplicate aliases by appending -ID to collisions
-- (handles different teachers whose names generate the same alias)
CREATE TEMPORARY TABLE `#__bsms_teachers_dup_ids` AS
SELECT t2.`id`
FROM `#__bsms_teachers` t2
WHERE EXISTS (
    SELECT 1 FROM `#__bsms_teachers` t3
    WHERE LOWER(t3.`alias`) = LOWER(t2.`alias`)
      AND t3.`id` < t2.`id`
);

UPDATE `#__bsms_teachers`
SET `alias` = CONCAT(`alias`, '-', `id`)
WHERE `id` IN (SELECT `id` FROM `#__bsms_teachers_dup_ids`);

DROP TEMPORARY TABLE `#__bsms_teachers_dup_ids`;

-- Step 4: Add UNIQUE KEY on alias column
ALTER TABLE `#__bsms_teachers`
    ADD UNIQUE KEY `idx_alias` (`alias`);

-- Step 5: Drop legacy image/dimension columns superseded by teacher_thumbnail system
ALTER TABLE `#__bsms_teachers` DROP COLUMN `imageh`;
ALTER TABLE `#__bsms_teachers` DROP COLUMN `imagew`;
ALTER TABLE `#__bsms_teachers` DROP COLUMN `thumb`;
ALTER TABLE `#__bsms_teachers` DROP COLUMN `thumbw`;
ALTER TABLE `#__bsms_teachers` DROP COLUMN `thumbh`;
ALTER TABLE `#__bsms_teachers` DROP COLUMN `address1`;
ALTER TABLE `#__bsms_teachers` DROP COLUMN `catid`;
