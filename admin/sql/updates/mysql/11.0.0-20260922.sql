-- Retire four dead podcast columns from #__bsms_podcast (#2157).
--
-- itunes:subtitle and itunes:keywords were deprecated by Apple in 2018. None
-- of these four is read anywhere: `subtitle`, `episodesubtitle` and
-- `customsubtitle` existed only as CwmpodcastTable properties, `podcastsearch`
-- was written only by the 9.x importer. Nothing to preserve, so they drop here
-- rather than in a postflight (contrast the scripture columns in #1623, which
-- needed a backfill first).
--
-- ⚠️ The legacy `image` column is dead too but is NOT dropped here. It carries
-- a historical `MODIFY image` (10.0.0-20220921.sql), and Joomla's ChangeSet
-- checks every past statement independently -- so dropping the column makes
-- that old MODIFY fail its check for ever (Database Maintenance shows a
-- permanent error). Retiring `image` needs that handled separately; left in
-- place for now.
--
-- ⚠️ One statement per column, deliberately. Joomla's MysqlChangeItem reads
-- only words 3 and 4 of a statement, so a compound
-- `ALTER TABLE x DROP COLUMN a, DROP COLUMN b` registers as a single
-- "DROP COLUMN a" -- every clause after the first is neither checked by
-- Database Maintenance nor repairable from it. Same trap as #1664/#1690.

ALTER TABLE `#__bsms_podcast`
    DROP COLUMN `subtitle`;

ALTER TABLE `#__bsms_podcast`
    DROP COLUMN `podcastsearch`;

ALTER TABLE `#__bsms_podcast`
    DROP COLUMN `episodesubtitle`;

ALTER TABLE `#__bsms_podcast`
    DROP COLUMN `customsubtitle`;

-- Import-set manifest (#2172): records every row/file a tracked import (e.g.
-- the opt-in demo content set, #2145) created, so it can be found again and
-- cleanly removed without touching content the site owner created themselves.
CREATE TABLE IF NOT EXISTS `#__bsms_import_manifest` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `import_tag` VARCHAR(64)  NOT NULL COMMENT 'Identifies one import run, e.g. demo-v1',
    `entity_type` ENUM('row','file') NOT NULL,
    `table_name` VARCHAR(64)  NULL DEFAULT NULL COMMENT 'Unprefixed table name, set when entity_type=row',
    `row_id`     INT UNSIGNED NULL DEFAULT NULL COMMENT 'Set when entity_type=row',
    `file_path`  VARCHAR(512) NULL DEFAULT NULL COMMENT 'Site-root-relative path, set when entity_type=file',
    `created`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_tag_row` (`import_tag`, `table_name`, `row_id`),
    KEY `idx_import_tag` (`import_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
