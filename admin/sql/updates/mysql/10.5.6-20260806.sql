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
