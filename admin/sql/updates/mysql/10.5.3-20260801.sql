-- Add columns that PR #1189 and PR #1188 shipped in install.mysql.utf8.sql
-- but never gave a matching ALTER TABLE for existing sites.
--
-- Both PRs merged 2026-03-19 and only edited the CREATE TABLE definitions, so
-- every site that already existed at that point never got these columns.
-- Fresh installs since then have them (they come from install SQL), which is
-- why the Podcasting 2.0 tab silently drops these fields on upgraded sites
-- but works fine on a from-scratch dev install: Joomla's Table::bind() only
-- binds columns the database actually has, so posting a field the table
-- lacks is a silent no-op, not an error. (#1416)
--
-- No `AFTER` clause: a drifted site may be missing the column being anchored
-- to, which would fail the whole statement. Appending is always safe.
-- One ALTER per column, not combined, so a partially-migrated site (already
-- has some of these) doesn't lose the rest to one failing statement.
--
-- Named 10.5.3, not 10.5.2: 10.5.2 already shipped today, and Joomla skips
-- any update file whose version is <= the version already recorded for the
-- extension. A file named 10.5.2-*.sql would silently never run on any site
-- already at 10.5.2 — which is exactly why System > Maintenance > Database
-- showed no pending fix on the reporting site even with this file deployed.

-- #1189 — Podcasting 2.0 funding/license/publisher/verification/update-frequency fields
ALTER TABLE `#__bsms_podcast` ADD COLUMN `funding_url` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `#__bsms_podcast` ADD COLUMN `funding_text` VARCHAR(100) DEFAULT NULL;
ALTER TABLE `#__bsms_podcast` ADD COLUMN `podcast_license` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `#__bsms_podcast` ADD COLUMN `podcast_license_url` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `#__bsms_podcast` ADD COLUMN `podcast_publisher` VARCHAR(150) DEFAULT NULL;
ALTER TABLE `#__bsms_podcast` ADD COLUMN `podcast_txt_verify` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `#__bsms_podcast` ADD COLUMN `update_frequency` VARCHAR(20) DEFAULT NULL;

-- #1188 — link a teacher to a Joomla user account for auto-access
ALTER TABLE `#__bsms_teachers` ADD COLUMN `user_id` INT(10) UNSIGNED DEFAULT NULL;