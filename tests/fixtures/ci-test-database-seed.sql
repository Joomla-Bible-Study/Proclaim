-- Install-time state for CI's PHPUnit database.
--
-- ci.yml builds the test database from Joomla's base.sql and Proclaim's
-- install SQL. A real site also has the rows below, and tests that need them
-- skip without them. Applied after those imports, with #__ replaced by the
-- table prefix.
--
-- Install state only. Tests that need content, such as a number of messages
-- or teachers, create it themselves inside a transaction they roll back.

-- Guided tours. Joomla defines these tables in extensions.sql, which CI does
-- not import; base.sql already registers com_guidedtours and
-- plg_system_guidedtours. Copied from joomla-cms 5.4.3, the version ci.yml pins.
CREATE TABLE IF NOT EXISTS `#__guidedtours` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '' NOT NULL,
  `uid` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `description` text NOT NULL,
  `ordering` int NOT NULL DEFAULT 0,
  `extensions` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int NOT NULL DEFAULT 0,
  `checked_out_time` datetime,
  `checked_out` int unsigned,
  `published` tinyint NOT NULL DEFAULT 0,
  `language` varchar(7) NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `access` int unsigned NOT NULL DEFAULT 0,
  `autostart` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_state` (`published`),
  KEY `idx_language` (`language`),
  KEY `idx_uid` (`uid`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__guidedtour_steps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tour_id` int NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `published` tinyint NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `ordering` int NOT NULL DEFAULT 0,
  `position` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  `type` int NOT NULL,
  `interactive_type` int NOT NULL DEFAULT 1,
  `url` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  `checked_out_time` datetime,
  `checked_out` int unsigned,
  `language` varchar(7) NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `params` text,
  PRIMARY KEY (`id`),
  KEY `idx_tour` (`tour_id`),
  KEY `idx_state` (`published`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Post-installation messages, which Proclaim registers. Joomla defines this
-- table in supports.sql, which CI does not import either. Same 5.4.3 source.
CREATE TABLE IF NOT EXISTS `#__postinstall_messages` (
  `postinstall_message_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `extension_id` bigint NOT NULL DEFAULT 700 COMMENT 'FK to #__extensions',
  `title_key` varchar(255) NOT NULL DEFAULT '' COMMENT 'Lang key for the title',
  `description_key` varchar(255) NOT NULL DEFAULT '' COMMENT 'Lang key for description',
  `action_key` varchar(255) NOT NULL DEFAULT '',
  `language_extension` varchar(255) NOT NULL DEFAULT 'com_postinstall' COMMENT 'Extension holding lang keys',
  `language_client_id` tinyint NOT NULL DEFAULT 1,
  `type` varchar(10) NOT NULL DEFAULT 'link' COMMENT 'Message type - message, link, action',
  `action_file` varchar(255) DEFAULT '' COMMENT 'RAD URI to the PHP file containing action method',
  `action` varchar(255) DEFAULT '' COMMENT 'Action method name or URL',
  `condition_file` varchar(255) DEFAULT NULL COMMENT 'RAD URI to file holding display condition method',
  `condition_method` varchar(255) DEFAULT NULL COMMENT 'Display condition method, must return boolean',
  `version_introduced` varchar(50) NOT NULL DEFAULT '3.2.0' COMMENT 'Version when this message was introduced',
  `enabled` tinyint NOT NULL DEFAULT 1,
  PRIMARY KEY (`postinstall_message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Users. The installer creates a Super User (group 8); a site also has
-- ordinary users, here one in Registered (group 2). No passwords: nothing logs in.
INSERT INTO `#__users` (`id`, `name`, `username`, `email`, `password`, `block`, `sendEmail`, `registerDate`, `params`) VALUES
(42, 'CI Super User', 'ci-admin', 'ci-admin@example.com', '', 0, 0, '2026-01-01 00:00:00', '{}'),
(43, 'CI Registered User', 'ci-user', 'ci-user@example.com', '', 0, 0, '2026-01-01 00:00:00', '{}');

INSERT INTO `#__user_usergroup_map` (`user_id`, `group_id`) VALUES
(42, 8),
(43, 2);

-- Proclaim's own extension rows, as the installer registers them.
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`) VALUES
(0, 'com_proclaim', 'component', 'com_proclaim', '', 1, 1, 0, 0, 0, '', '{}', ''),
(0, 'plg_schemaorg_proclaim', 'plugin', 'proclaim', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '');

-- The component's asset, as Joomla's installer registers it: the last child of
-- root, rules '{}'. It has to exist from the start. Access::getAssetId() caches
-- each name it looks up for the rest of the process, so a lookup made before
-- the row exists answers 0 for good, and com_proclaim's rules are never read.
-- Appended to the nested set from root's current rgt rather than fixed values.
UPDATE `#__assets` SET `rgt` = `rgt` + 2 WHERE `id` = 1;

INSERT INTO `#__assets` (`parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`)
SELECT `id`, `rgt` - 2, `rgt` - 1, 1, 'com_proclaim', 'com_proclaim', '{}' FROM `#__assets` WHERE `id` = 1;
