-- Give podcasts a copyright holder.
--
-- The feed's <copyright> tag was hardcoded as "© (2026) All rights reserved."
-- — stray parentheses, and naming nobody, which is the one thing a copyright
-- notice exists to say. There was no field behind it (#1412).
--
-- Left NULL here rather than back-filled: the generator falls back to the site
-- name when this is empty, so existing feeds gain a real holder without anyone
-- editing a record, and a site that wants something else can say so.
--
-- Plain ADD COLUMN, matching every other migration here. `IF NOT EXISTS` is
-- MariaDB syntax that MySQL rejects outright, and Joomla runs each update file
-- once against the schema version it records, so the guard bought nothing.

ALTER TABLE `#__bsms_podcast`
    ADD COLUMN `copyright` VARCHAR(255) DEFAULT NULL
        COMMENT 'Rights holder for the feed copyright tag; falls back to the site name'
        AFTER `editor_email`;
