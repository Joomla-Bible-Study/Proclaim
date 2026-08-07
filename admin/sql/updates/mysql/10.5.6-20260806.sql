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

-- #1579: nothing stopped two concurrent saves allocating the same episode
-- number in a series. Both read MAX()+1 before either had stored, and both
-- passed the duplicate check for the same reason.

-- Blank the later members of each existing duplicate group, keeping the
-- earliest message's number. The constraint below cannot be created while any
-- remain. Blanking rather than renumbering: the affected messages return to the
-- unnumbered state the UI already handles, and an administrator renumbers them
-- deliberately from the episode audit screen instead of the database inventing
-- an order that a number-sorted podcast feed would then follow.
UPDATE `#__bsms_studies` s
    JOIN (SELECT s2.`id`
          FROM `#__bsms_studies` s2
          JOIN (SELECT `series_id`, `studynumber`, MIN(`id`) AS keep_id
                FROM `#__bsms_studies`
                WHERE `series_id` > 0 AND `studynumber` <> ''
                GROUP BY `series_id`, `studynumber`
                HAVING COUNT(*) > 1) g
            ON s2.`series_id` = g.`series_id`
           AND s2.`studynumber` = g.`studynumber`
           AND s2.`id` <> g.`keep_id`) d
      ON d.`id` = s.`id`
SET s.`studynumber` = '';

-- An episode number only has to be unique inside a real series, and only when
-- one has actually been given. series_id 0 and -1 are the seriesless
-- sentinels, and studynumber defaults to '' -- MySQL treats '' as a value, so a
-- plain unique index would have collided across the 685 unnumbered messages a
-- development database carries. Mapping both cases to NULL leaves them
-- unconstrained, because NULLs are distinct from each other in a unique index.
ALTER TABLE `#__bsms_studies`
    ADD COLUMN `studynumber_uk` VARCHAR(100)
    GENERATED ALWAYS AS (IF(`series_id` > 0 AND `studynumber` <> '', `studynumber`, NULL)) STORED;

ALTER TABLE `#__bsms_studies`
    ADD UNIQUE KEY `uq_series_studynumber` (`series_id`, `studynumber_uk`);
