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
