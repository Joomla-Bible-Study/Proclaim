-- Servers
ALTER TABLE `#__bsms_servers` ADD `params` TEXT NOT NULL;
ALTER TABLE `#__bsms_servers` ADD `media` TEXT NOT NULL;
ALTER TABLE `#__bsms_servers` MODIFY `type` CHAR(255) NOT NULL;

-- -- MediaFiles
ALTER TABLE `#__bsms_mediafiles` ADD `server_id` INT(5) NULL AFTER `study_id`;
ALTER TABLE `#__bsms_mediafiles` ADD `metadata` TEXT NOT NULL AFTER `params`;

-- -- Remove Bad topic_text save
DELETE FROM `#__bsms_topics`
WHERE `topic_text` = 'A';

-- -- Locations
ALTER TABLE `#__bsms_locations` ADD `contact_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Used to link to com_contact' AFTER `location_text`;
ALTER TABLE `#__bsms_locations` ADD `address` text AFTER `contact_id`;
ALTER TABLE `#__bsms_locations` ADD `suburb` varchar(100) DEFAULT NULL AFTER `address`;
ALTER TABLE `#__bsms_locations` ADD `state` varchar(100) DEFAULT NULL AFTER `suburb`;
ALTER TABLE `#__bsms_locations` ADD `country` varchar(100) DEFAULT NULL AFTER `state`;
ALTER TABLE `#__bsms_locations` ADD `postcode` varchar(100) DEFAULT NULL AFTER `country`;
ALTER TABLE `#__bsms_locations` ADD `telephone` varchar(255) DEFAULT NULL AFTER `postcode`;
ALTER TABLE `#__bsms_locations` ADD `fax` varchar(255) DEFAULT NULL AFTER `telephone`;
ALTER TABLE `#__bsms_locations` ADD `misc` mediumtext AFTER `fax`;
ALTER TABLE `#__bsms_locations` ADD `image` varchar(255) DEFAULT NULL AFTER `misc`;
ALTER TABLE `#__bsms_locations` ADD `email_to` varchar(255) DEFAULT NULL AFTER `image`;
ALTER TABLE `#__bsms_locations` ADD `default_con` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `email_to`;
ALTER TABLE `#__bsms_locations` ADD `checked_out` int(10) unsigned NOT NULL DEFAULT '0' AFTER `default_con`;
ALTER TABLE `#__bsms_locations` ADD `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `checked_out`;
ALTER TABLE `#__bsms_locations` ADD `params` text NOT NULL AFTER `checked_out_time`;
ALTER TABLE `#__bsms_locations` ADD `user_id` int(11) NOT NULL DEFAULT '0' AFTER `params`;
ALTER TABLE `#__bsms_locations` ADD `mobile` varchar(255) NOT NULL DEFAULT '' AFTER `user_id`;
ALTER TABLE `#__bsms_locations` ADD `webpage` varchar(255) NOT NULL DEFAULT '' AFTER `mobile`;
ALTER TABLE `#__bsms_locations` ADD `sortname1` varchar(255) NOT NULL AFTER `webpage`;
ALTER TABLE `#__bsms_locations` ADD `sortname2` varchar(255) NOT NULL AFTER `sortname1`;
ALTER TABLE `#__bsms_locations` ADD `sortname3` varchar(255) NOT NULL AFTER `sortname2`;
ALTER TABLE `#__bsms_locations` ADD `language` char(7) NOT NULL AFTER `sortname3`;
ALTER TABLE `#__bsms_locations` ADD `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `language`;
ALTER TABLE `#__bsms_locations` ADD `created_by` int(10) unsigned NOT NULL DEFAULT '0' AFTER `created`;
ALTER TABLE `#__bsms_locations` ADD `created_by_alias` varchar(255) NOT NULL DEFAULT '' AFTER `created_by`;
ALTER TABLE `#__bsms_locations` ADD `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by_alias`;
ALTER TABLE `#__bsms_locations` ADD `modified_by` int(10) unsigned NOT NULL DEFAULT '0' AFTER `modified`;
ALTER TABLE `#__bsms_locations` ADD `metakey` text NOT NULL AFTER `modified_by`;
ALTER TABLE `#__bsms_locations` ADD `metadesc` text NOT NULL AFTER `medakey`;
ALTER TABLE `#__bsms_locations` ADD `metadata` text NOT NULL AFTER `medadesc`;
ALTER TABLE `#__bsms_locations` ADD `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if article is featured.' AFTER `metadata`;
ALTER TABLE `#__bsms_locations` ADD `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.' AFTER `featured`;
ALTER TABLE `#__bsms_locations` ADD `version` int(10) unsigned NOT NULL DEFAULT '1' AFTER `xreference`;
ALTER TABLE `#__bsms_locations` ADD `hits` int(10) unsigned NOT NULL DEFAULT '0' AFTER `version`;
ALTER TABLE `#__bsms_locations` ADD `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `hits`;
ALTER TABLE `#__bsms_locations` ADD `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `publish_up`;
