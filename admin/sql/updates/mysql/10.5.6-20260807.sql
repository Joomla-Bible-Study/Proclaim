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

-- ⚠️ NOT RE-RUNNABLE. Every other statement in this file is idempotent; this
-- one hashes whatever it finds. If this file is renamed forward again, apply
-- only the newly appended statements to any database that already ran it,
-- rather than replaying the whole file.
--
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

-- #1560: nothing stopped two overlapping playlist imports from both creating a
-- row for the same remote playlist. CwmplaylistSyncHelper::importChannelPlaylists()
-- looks the playlist up and inserts when absent, with no constraint between the
-- two steps, so a scheduled sync overlapping a manual "Import from YouTube" could
-- have both pass the lookup before either had stored.

-- Salvage the losing duplicates' junction rows before the rows go away. Each
-- step runs against every duplicate group at once. `g` names the row each group
-- keeps: joining on "any playlist with a lower id" instead would, in a group of
-- three, let MySQL pick either survivor arbitrarily -- and step 3 could then
-- remap an item onto a playlist that step 5 goes on to delete.

-- 1. Carry a resolved media-file link over to the keeper where the keeper has
--    none. Deleting the loser's row first would throw that link away.
UPDATE `#__bsms_playlist_items` keep
    JOIN `#__bsms_playlists` kp ON kp.`id` = keep.`playlist_id`
    JOIN (SELECT `remote_playlist_id`, `server_id`, MIN(`id`) AS keep_id
          FROM `#__bsms_playlists`
          WHERE `remote_playlist_id` <> ''
          GROUP BY `remote_playlist_id`, `server_id`) g
        ON g.`keep_id` = kp.`id`
    JOIN `#__bsms_playlists` lp
        ON lp.`remote_playlist_id` = g.`remote_playlist_id`
       AND lp.`server_id` = g.`server_id`
       AND lp.`id` <> g.`keep_id`
    JOIN `#__bsms_playlist_items` lose
        ON lose.`playlist_id` = lp.`id`
       AND lose.`remote_video_id` = keep.`remote_video_id`
SET keep.`mediafile_id` = lose.`mediafile_id`
WHERE keep.`mediafile_id` IS NULL
  AND lose.`mediafile_id` IS NOT NULL;

-- 2. Drop the losers' items the keeper already holds. #__bsms_playlist_items has
--    UNIQUE KEY (playlist_id, remote_video_id), so remapping these in step 3
--    would abort the migration on a duplicate key.
DELETE lose FROM `#__bsms_playlist_items` lose
    JOIN `#__bsms_playlists` lp ON lp.`id` = lose.`playlist_id`
    JOIN (SELECT `remote_playlist_id`, `server_id`, MIN(`id`) AS keep_id
          FROM `#__bsms_playlists`
          WHERE `remote_playlist_id` <> ''
          GROUP BY `remote_playlist_id`, `server_id`) g
        ON g.`remote_playlist_id` = lp.`remote_playlist_id`
       AND g.`server_id` = lp.`server_id`
       AND g.`keep_id` <> lp.`id`
    JOIN `#__bsms_playlist_items` keep
        ON keep.`playlist_id` = g.`keep_id`
       AND keep.`remote_video_id` = lose.`remote_video_id`
WHERE lp.`remote_playlist_id` <> '';

-- 3. Move what survives onto the keeper, so a manual playlist assignment made
--    against a duplicate is not silently lost.
UPDATE `#__bsms_playlist_items` lose
    JOIN `#__bsms_playlists` lp ON lp.`id` = lose.`playlist_id`
    JOIN (SELECT `remote_playlist_id`, `server_id`, MIN(`id`) AS keep_id
          FROM `#__bsms_playlists`
          WHERE `remote_playlist_id` <> ''
          GROUP BY `remote_playlist_id`, `server_id`) g
        ON g.`remote_playlist_id` = lp.`remote_playlist_id`
       AND g.`server_id` = lp.`server_id`
       AND g.`keep_id` <> lp.`id`
SET lose.`playlist_id` = g.`keep_id`
WHERE lp.`remote_playlist_id` <> '';

-- 4. Release the losing rows' ACL assets, which nothing else would clean up once
--    the rows are gone.
DELETE a FROM `#__assets` a
    JOIN `#__bsms_playlists` lp
        ON a.`name` = CONCAT('com_proclaim.playlist.', lp.`id`)
    JOIN (SELECT `remote_playlist_id`, `server_id`, MIN(`id`) AS keep_id
          FROM `#__bsms_playlists`
          WHERE `remote_playlist_id` <> ''
          GROUP BY `remote_playlist_id`, `server_id`) g
        ON g.`remote_playlist_id` = lp.`remote_playlist_id`
       AND g.`server_id` = lp.`server_id`
       AND g.`keep_id` <> lp.`id`
WHERE lp.`remote_playlist_id` <> '';

-- 5. Remove the duplicates themselves. The constraint below cannot be created
--    while any remain.
DELETE lp FROM `#__bsms_playlists` lp
    JOIN (SELECT `remote_playlist_id`, `server_id`, MIN(`id`) AS keep_id
          FROM `#__bsms_playlists`
          WHERE `remote_playlist_id` <> ''
          GROUP BY `remote_playlist_id`, `server_id`) g
        ON g.`remote_playlist_id` = lp.`remote_playlist_id`
       AND g.`server_id` = lp.`server_id`
       AND g.`keep_id` <> lp.`id`
WHERE lp.`remote_playlist_id` <> '';

-- A remote playlist maps to one local row per server. remote_playlist_id is
-- NOT NULL DEFAULT '' and the edit form does not require it, so a playlist
-- created by hand in the admin carries ''. A plain unique index would let only
-- one of those exist per server. Mapping '' to NULL leaves them unconstrained,
-- because NULLs are distinct from each other in a unique index -- the same
-- treatment uq_series_studynumber gives an absent episode number above.
ALTER TABLE `#__bsms_playlists`
    ADD COLUMN `remote_playlist_uk` VARCHAR(64)
    GENERATED ALWAYS AS (IF(`remote_playlist_id` <> '', `remote_playlist_id`, NULL)) STORED;

ALTER TABLE `#__bsms_playlists`
    ADD UNIQUE KEY `uq_server_remote_playlist` (`server_id`, `remote_playlist_uk`);

-- The unique index covers the generated column, which no query names. Lookups by
-- (remote_playlist_id, server_id) -- every sync run does one per remote playlist
-- -- would otherwise still fall back to idx_server alone.
ALTER TABLE `#__bsms_playlists`
    ADD KEY `idx_remote_playlist` (`remote_playlist_id`, `server_id`);

-- #1622: CwmteacherModel::canDelete() enqueued a "cannot delete" message and
-- then returned a plain permission check, so the teacher was deleted anyway --
-- and CwmteacherTable::delete() never cleared #__bsms_study_teachers. Both are
-- fixed in code; these clear rows already orphaned by the old behaviour.

-- Junction rows whose teacher is gone. Teacher listings and filters join
-- against these, so they surface as blank entries rather than staying harmless.
DELETE FROM `#__bsms_study_teachers`
WHERE `teacher_id` NOT IN (SELECT `id` FROM `#__bsms_teachers`);

-- The same in the other direction. CwmmessageTable::delete() has always cleared
-- these, so any that exist predate that or came from a path that bypassed it.
DELETE FROM `#__bsms_study_teachers`
WHERE `study_id` NOT IN (SELECT `id` FROM `#__bsms_studies`);

-- #1612: analytics stored the referrer verbatim, query string and all. That
-- string is written by the *referring* site and routinely carries things that
-- are not ours to hold -- search terms, session tokens, addresses in newsletter
-- links, password-reset tokens. Capture now keeps only scheme, host and path;
-- this reduces the rows already written.
--
-- Applied to every row rather than only the leaky-looking ones: a URL with no
-- query string is unchanged by the same expression, so one statement covers it.
-- SUBSTRING_INDEX strips from the first '?' or '#', whichever appears; nothing
-- after either is a page identity. UTM tags are unaffected, being stored
-- separately in utm_source / utm_medium / utm_campaign.
UPDATE `#__bsms_analytics_events`
SET `referrer_url` = SUBSTRING_INDEX(SUBSTRING_INDEX(`referrer_url`, '?', 1), '#', 1)
WHERE `referrer_url` IS NOT NULL
  AND (`referrer_url` LIKE '%?%' OR `referrer_url` LIKE '%#%');
