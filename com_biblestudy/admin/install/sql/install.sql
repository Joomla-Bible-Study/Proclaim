# Dump of table #__bsms_update
# ------------------------------------------------------------

DROP TABLE IF EXISTS `#__bsms_update`;

CREATE TABLE `#__bsms_update` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `#__bsms_update` WRITE;
/*!40000 ALTER TABLE `#__bsms_update` DISABLE KEYS */;

INSERT INTO `#__bsms_update` (`id`, `version`)
VALUES
(1,'7.0.0'),
(2,'7.0.1'),
(3,'7.0.1.1'),
(4,'7.0.2'),
(5,'7.0.3'),
(6,'7.0.4'),
(7,'7.1.0'),
(8,'7.1.1'),
(9,'7.1.2'),
(10,'7.1.3'),
(11,'8.0.0'),
(12,'8.0.1'),
(13,'8.0.2'),
(14,'8.0.3'),
(15,'8.0.4'),
(16,'8.0.5');

/*!40000 ALTER TABLE `#__bsms_update` ENABLE KEYS */;
UNLOCK TABLES;

# Dump of table #__bsms_admin
# ------------------------------------------------------------

CREATE TABLE `#__bsms_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `params` text,
  `drop_tables` int(3) DEFAULT '0',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `installstate` text,
  `debug` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_books
# ------------------------------------------------------------

CREATE TABLE `#__bsms_books` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bookname` varchar(250) DEFAULT NULL,
  `booknumber` int(5) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_comments
# ------------------------------------------------------------

CREATE TABLE `#__bsms_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `study_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `full_name` varchar(50) NOT NULL DEFAULT '',
  `user_email` varchar(100) NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_text` text NOT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `language` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_folders
# ------------------------------------------------------------

CREATE TABLE `#__bsms_folders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `foldername` varchar(250) NOT NULL DEFAULT '',
  `folderpath` varchar(250) NOT NULL DEFAULT '',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_locations
# ------------------------------------------------------------

CREATE TABLE `#__bsms_locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location_text` varchar(250) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `landing_show` int(3) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_media
# ------------------------------------------------------------

CREATE TABLE `#__bsms_media` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `media_text` text,
  `media_image_name` varchar(250) NOT NULL DEFAULT '',
  `media_image_path` varchar(250) NOT NULL DEFAULT '',
  `path2` varchar(150) NOT NULL,
  `media_alttext` varchar(250) NOT NULL DEFAULT '',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_mediafiles
# ------------------------------------------------------------

CREATE TABLE `#__bsms_mediafiles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `study_id` int(5) DEFAULT NULL,
  `media_image` int(3) DEFAULT NULL,
  `server` varchar(250) DEFAULT NULL,
  `path` varchar(250) DEFAULT NULL,
  `special` varchar(250) DEFAULT '_self',
  `filename` text,
  `size` varchar(50) DEFAULT NULL,
  `mime_type` int(3) DEFAULT NULL,
  `podcast_id` varchar(50) DEFAULT NULL,
  `internal_viewer` tinyint(1) DEFAULT '0',
  `mediacode` text,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `createdate` datetime DEFAULT NULL,
  `link_type` char(1) DEFAULT NULL,
  `hits` int(10) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `docMan_id` int(11) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `comment` text,
  `virtueMart_id` int(11) DEFAULT NULL,
  `params` text,
  `downloads` int(10) DEFAULT '0',
  `plays` int(10) DEFAULT '0',
  `player` int(2) DEFAULT NULL,
  `popup` int(2) DEFAULT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `language` char(3) NOT NULL DEFAULT '',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`),
  KEY `idx_study_id` (`study_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_message_type
# ------------------------------------------------------------

CREATE TABLE `#__bsms_message_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `message_type` text NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `landing_show` int(3) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_mimetype
# ------------------------------------------------------------

CREATE TABLE `#__bsms_mimetype` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mimetype` varchar(50) DEFAULT NULL,
  `mimetext` varchar(50) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_order
# ------------------------------------------------------------

CREATE TABLE `#__bsms_order` (
  `id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(15) DEFAULT NULL,
  `text` varchar(50) DEFAULT '',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_podcast
# ------------------------------------------------------------

CREATE TABLE `#__bsms_podcast` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `description` text,
  `image` varchar(130) DEFAULT NULL,
  `imageh` int(3) DEFAULT NULL,
  `imagew` int(3) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `podcastimage` varchar(130) DEFAULT NULL,
  `podcastsearch` varchar(250) DEFAULT NULL,
  `filename` varchar(150) DEFAULT NULL,
  `language` varchar(10) DEFAULT 'en-us',
  `editor_name` varchar(150) DEFAULT NULL,
  `editor_email` varchar(150) DEFAULT NULL,
  `podcastlimit` int(5) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `episodetitle` int(11) DEFAULT NULL,
  `custom` varchar(200) DEFAULT NULL,
  `detailstemplateid` int(11) DEFAULT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `alternatelink` varchar(300) DEFAULT NULL COMMENT 'replaces podcast file link on subscription',
  `alternateimage` varchar(150) DEFAULT NULL COMMENT 'alternate image path for podcast',
  `podcast_subscribe_show` int(3) DEFAULT NULL,
  `podcast_image_subscribe` varchar(150) DEFAULT NULL COMMENT 'The image to use for the podcast subscription image',
  `podcast_subscribe_desc` varchar(150) DEFAULT NULL COMMENT 'Words to go below podcast subscribe image',
  `alternatewords` varchar(20) DEFAULT NULL,
  `episodesubtitle` int(11) DEFAULT NULL,
  `customsubtitle` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_schemaVersion
# ------------------------------------------------------------

CREATE TABLE `#__bsms_schemaVersion` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `schemaVersion` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# Dump of table #__bsms_series
# ------------------------------------------------------------

CREATE TABLE `#__bsms_series` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `series_text` text,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `teacher` int(3) DEFAULT NULL,
  `description` text,
  `series_thumbnail` varchar(150) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `language` char(3) NOT NULL DEFAULT '',
  `landing_show` int(3) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_servers
# ------------------------------------------------------------

CREATE TABLE `#__bsms_servers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `server_name` varchar(250) DEFAULT NULL,
  `server_path` varchar(250) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(3) NOT NULL,
  `ftphost` varchar(100) NOT NULL,
  `ftpuser` varchar(250) NOT NULL,
  `ftppassword` varchar(250) NOT NULL,
  `ftpport` varchar(10) NOT NULL,
  `aws_key` varchar(100) NOT NULL,
  `aws_secret` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_share
# ------------------------------------------------------------

CREATE TABLE `#__bsms_share` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `params` text,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_studies
# ------------------------------------------------------------

CREATE TABLE `#__bsms_studies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `studydate` datetime DEFAULT NULL,
  `teacher_id` int(11) DEFAULT '1',
  `studynumber` varchar(100) DEFAULT '',
  `booknumber` int(3) DEFAULT '101',
  `chapter_begin` int(3) DEFAULT '1',
  `verse_begin` int(3) DEFAULT '1',
  `chapter_end` int(3) DEFAULT '1',
  `secondary_reference` text,
  `booknumber2` varchar(4) DEFAULT NULL,
  `chapter_begin2` varchar(4) DEFAULT NULL,
  `verse_begin2` varchar(4) DEFAULT NULL,
  `chapter_end2` varchar(4) DEFAULT NULL,
  `verse_end2` varchar(4) DEFAULT NULL,
  `prod_dvd` varchar(100) DEFAULT NULL,
  `prod_cd` varchar(100) DEFAULT NULL,
  `server_cd` varchar(10) DEFAULT NULL,
  `server_dvd` varchar(10) DEFAULT NULL,
  `image_cd` varchar(10) DEFAULT NULL,
  `image_dvd` varchar(10) DEFAULT '0',
  `studytext2` text,
  `comments` tinyint(1) DEFAULT '1',
  `hits` int(10) NOT NULL DEFAULT '0',
  `user_id` int(10) DEFAULT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `show_level` varchar(100) NOT NULL DEFAULT '0',
  `location_id` int(3) DEFAULT NULL,
  `verse_end` int(3) DEFAULT '1',
  `studytitle` text,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `studyintro` text,
  `media_hours` varchar(2) DEFAULT NULL,
  `media_minutes` varchar(2) DEFAULT NULL,
  `media_seconds` varchar(2) DEFAULT NULL,
  `messagetype` varchar(100) DEFAULT '1',
  `series_id` int(3) DEFAULT '0',
  `topics_id` int(3) DEFAULT '0',
  `studytext` text,
  `thumbnailm` text,
  `thumbhm` int(11) DEFAULT NULL,
  `thumbwm` int(11) DEFAULT NULL,
  `params` text,
  `media1_filename` text,
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `language` char(3) NOT NULL DEFAULT '',
  `download_id` int(10) NOT NULL DEFAULT '0' COMMENT 'Used for link to download of mediafile',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`),
  KEY `idx_seriesid` (`series_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_studytopics
# ------------------------------------------------------------

CREATE TABLE `#__bsms_studytopics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `study_id` int(3) NOT NULL DEFAULT '0',
  `topic_id` int(3) NOT NULL DEFAULT '0',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_study` (`study_id`),
  KEY `idx_topic` (`topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_styles
# ------------------------------------------------------------

CREATE TABLE `#__bsms_styles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `filename` text NOT NULL,
  `stylecode` longtext NOT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_teachers
# ------------------------------------------------------------

CREATE TABLE `#__bsms_teachers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_image` text,
  `teacher_thumbnail` text,
  `teachername` varchar(250) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `title` varchar(250) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` text,
  `information` text,
  `image` text,
  `imageh` text,
  `imagew` text,
  `thumb` text,
  `thumbw` text,
  `thumbh` text,
  `short` text,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `catid` int(3) DEFAULT '1',
  `list_show` tinyint(1) NOT NULL DEFAULT '1',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `language` char(3) NOT NULL DEFAULT '',
  `facebooklink` varchar(150) DEFAULT NULL,
  `twitterlink` varchar(150) DEFAULT NULL,
  `bloglink` varchar(150) DEFAULT NULL,
  `link1` varchar(150) DEFAULT NULL,
  `linklabel1` varchar(150) DEFAULT NULL,
  `link2` varchar(150) DEFAULT NULL,
  `linklabel2` varchar(150) DEFAULT NULL,
  `link3` varchar(150) DEFAULT NULL,
  `linklabel3` varchar(150) DEFAULT NULL,
  `contact` int(11) DEFAULT NULL,
  `address` mediumtext NOT NULL,
  `landing_show` int(3) DEFAULT '1',
  `address1` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_templatecode
# ------------------------------------------------------------

CREATE TABLE `#__bsms_templatecode` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `type` tinyint(3) NOT NULL,
  `filename` text NOT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `templatecode` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_templates
# ------------------------------------------------------------

CREATE TABLE `#__bsms_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `tmpl` longtext NOT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `params` longtext,
  `title` text,
  `text` text,
  `pdf` text,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_timeset
# ------------------------------------------------------------

CREATE TABLE `#__bsms_timeset` (
  `timeset` varchar(14) NOT NULL DEFAULT '',
  `backup` varchar(14) DEFAULT NULL,
  KEY `timeset` (`timeset`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table #__bsms_topics
# ------------------------------------------------------------

CREATE TABLE `#__bsms_topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic_text` text,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `params` varchar(511) DEFAULT NULL,
  `language` char(7) DEFAULT '*',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
