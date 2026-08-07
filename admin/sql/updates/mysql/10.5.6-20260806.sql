-- #1574: #__bsms_studytopics had only a non-unique KEY on (study_id, topic_id),
-- so concurrent saves of the same message could both delete and both insert,
-- leaving duplicate associations. CwmdbHelper::cleanStudyTopics() exists purely
-- to mop those up after the fact.

-- Collapse existing duplicates first, keeping the earliest row of each pair.
-- The UNIQUE index below cannot be created while any remain.
DELETE t1 FROM `#__bsms_studytopics` t1
    INNER JOIN `#__bsms_studytopics` t2
    ON t1.`study_id` = t2.`study_id`
   AND t1.`topic_id` = t2.`topic_id`
   AND t1.`id` > t2.`id`;

-- Drop the non-unique index it replaces, if present.
ALTER TABLE `#__bsms_studytopics` DROP INDEX `idx_study_topic`;

-- A study may hold a topic once. Enforced by the database rather than by the
-- application remembering to check.
ALTER TABLE `#__bsms_studytopics`
    ADD UNIQUE KEY `uq_study_topic` (`study_id`, `topic_id`);

-- Remove associations whose study no longer exists. Nothing deleted these when
-- the study was deleted, and topic counts and filter dropdowns join against
-- them.
DELETE FROM `#__bsms_studytopics`
WHERE `study_id` NOT IN (SELECT `id` FROM `#__bsms_studies`);

-- #1611: session_hash is now a keyed digest rotated daily, so a visitor cannot
-- be followed across days. Update the column comment to say so.
ALTER TABLE `#__bsms_analytics_events`
    MODIFY `session_hash` VARCHAR(64) NULL DEFAULT NULL
    COMMENT 'Keyed hash of session ID, rotated daily; not linkable across days; consent-required';

-- Re-key the existing history. Rows written before this release hold a bare,
-- unsalted SHA-256 that is stable for all time, so one visitor could be traced
-- across the whole table. Folding each row's own date into the digest gives
-- those rows the same cross-day unlinkability new rows get.
--
-- Rows from the same visitor on the same day still collide, so
-- COUNT(DISTINCT session_hash) is unchanged and no historical Sessions figure
-- moves. NULL stays NULL because CONCAT() propagates it.
UPDATE `#__bsms_analytics_events`
SET `session_hash` = SHA2(CONCAT(`session_hash`, DATE(`created`)), 256)
WHERE `session_hash` IS NOT NULL;
