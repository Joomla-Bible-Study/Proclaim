-- Joomla core tables that com_proclaim's migrations write to.
--
-- The first half of the cwm-schema-replay baseline. An extension's own install
-- SQL is not a site: four of the 37 migrations in admin/sql/updates/mysql touch
-- tables that no Proclaim manifest creates, and replaying onto the #__bsms_*
-- tables alone fails on the first of them for a reason that says nothing about
-- the migration.
--
-- | table                    | what a migration does with it                |
-- |--------------------------|----------------------------------------------|
-- | #__assets                | DELETE, de-duplicating playlist asset rows    |
-- | #__schemas               | referenced by the 10.1.0 version-tracking work|
-- | #__action_log_config     | INSERT, registering com_proclaim log types    |
-- | #__action_logs_extensions| INSERT IGNORE, registering the extension      |
--
-- ## Why only four
--
-- This is the set the migrations actually use, not a whole Joomla schema.
-- Vendoring all 61 core tables would put ~175 KB of upstream SQL in this repo
-- to make four of them reachable, and it would go stale against whichever
-- Joomla release it was copied from.
--
-- The failure mode of a short list is loud and self-describing: a migration
-- touching a fifth core table fails with "Table ... doesn't exist" naming
-- exactly what to add here. That is a better trade than carrying the whole
-- schema to avoid an error that tells you what to do.
--
-- ## Where these came from
--
-- Copied verbatim from Joomla's own installation SQL -- a real core schema,
-- not a dump of somebody's dev site, so it is reviewable as "this is what
-- Joomla creates":
--
--   installation/sql/mysql/base.sql        -- #__assets, #__schemas
--   installation/sql/mysql/extensions.sql  -- the two action-log tables
--
-- Taken from joomla-cms 6.1.3. These four definitions have been stable across
-- 4.x, 5.x and 6.x; if one ever changes in a way that matters, re-copy it from
-- the same two files.
--
-- No data rows. The migrations INSERT their own and the one DELETE joins only
-- #__bsms_playlists, so a site's default asset tree and usergroups are not
-- needed to make any of them behave as they do in the field.

-- from installation/sql/mysql/base.sql
CREATE TABLE IF NOT EXISTS `#__assets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `parent_id` int NOT NULL DEFAULT 0 COMMENT 'Nested set parent.',
  `lft` int NOT NULL DEFAULT 0 COMMENT 'Nested set lft.',
  `rgt` int NOT NULL DEFAULT 0 COMMENT 'Nested set rgt.',
  `level` int unsigned NOT NULL COMMENT 'The cached level in the nested tree.',
  `name` varchar(50) NOT NULL COMMENT 'The unique name for the asset.',
  `title` varchar(100) NOT NULL COMMENT 'The descriptive title for the asset.',
  `rules` varchar(5120) NOT NULL COMMENT 'JSON encoded access control.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_asset_name` (`name`),
  KEY `idx_lft_rgt` (`lft`,`rgt`),
  KEY `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- from installation/sql/mysql/base.sql
CREATE TABLE IF NOT EXISTS `#__schemas` (
  `extension_id` int NOT NULL,
  `version_id` varchar(20) NOT NULL,
  PRIMARY KEY (`extension_id`,`version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- from installation/sql/mysql/extensions.sql
CREATE TABLE IF NOT EXISTS `#__action_log_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type_title` varchar(255) NOT NULL DEFAULT '',
  `type_alias` varchar(255) NOT NULL DEFAULT '',
  `id_holder` varchar(255),
  `title_holder` varchar(255),
  `table_name` varchar(255),
  `text_prefix` varchar(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- from installation/sql/mysql/extensions.sql
CREATE TABLE IF NOT EXISTS `#__action_logs_extensions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `extension` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

