DROP TABLE IF EXISTS `#__bsms_install`;

DROP TABLE IF EXISTS `#__bsms_update`;

CREATE TABLE IF NOT EXISTS `#__bsms_update` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  version VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

INSERT INTO `#__bsms_update` (id,version) VALUES
(1,'7.0.0'),
(2,'7.0.1'),
(3,'7.0.1.1'),
(4,'7.0.2'),
(5,'7.0.3'),
(6,'7.0.4'),
(7,'7.1.0'),
(8,'7.1.1'),
(9,'7.1.2');

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_admin`
--

CREATE TABLE IF NOT EXISTS `#__bsms_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `drop_tables` int(3) DEFAULT '0',
  `params` text,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `installstate` text,
  `debug` TINYINT( 3 ) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_books`
--

CREATE TABLE IF NOT EXISTS `#__bsms_books` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bookname` varchar(250) DEFAULT NULL,
  `booknumber` int(5) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_comments`
--

CREATE TABLE IF NOT EXISTS `#__bsms_comments` (
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
  `language` char(7) NOT NULL COMMENT 'The language code for the Comments.',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_folders`
--

CREATE TABLE IF NOT EXISTS `#__bsms_folders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `foldername` varchar(250) NOT NULL DEFAULT '',
  `folderpath` varchar(250) NOT NULL DEFAULT '',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_locations`
--

CREATE TABLE IF NOT EXISTS `#__bsms_locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location_text` varchar(250) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `landing_show` int(3),
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_media`
--

CREATE TABLE IF NOT EXISTS `#__bsms_media` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `media_text` text,
  `media_image_name` varchar(250) NOT NULL DEFAULT '',
  `media_image_path` varchar(250) NOT NULL DEFAULT '',
  `path2` varchar(150) NOT NULL,
  `media_alttext` varchar(250) NOT NULL DEFAULT '',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_mediafiles`
--

CREATE TABLE IF NOT EXISTS `#__bsms_mediafiles` (
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
  `downloads` int(10) DEFAULT '0',
  `plays` int(10) DEFAULT '0',
  `params` text,
  `player` int(2) DEFAULT NULL,
  `popup` int(2) DEFAULT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  `language` char(7) NOT NULL COMMENT 'The language code for the MediaFile.',
  `created_by` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
  `created_by_alias` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_study_id` (`study_id`),
  KEY `idx_access` (`access`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_message_type`
--

CREATE TABLE IF NOT EXISTS `#__bsms_message_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `message_type` text NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `landing_show` int(3),
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_mimetype`
--

CREATE TABLE IF NOT EXISTS `#__bsms_mimetype` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mimetype` varchar(50) DEFAULT NULL,
  `mimetext` varchar(50) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_order`
--

CREATE TABLE IF NOT EXISTS `#__bsms_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(15) DEFAULT '',
  `text` varchar(50) DEFAULT '',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_podcast`
--

CREATE TABLE IF NOT EXISTS `#__bsms_podcast` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `description` text,
  `image` varchar(130) DEFAULT NULL,
  `imageh` int(3) DEFAULT NULL,
  `imagew` int(3) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `podcastimage` varchar(130) DEFAULT NULL,
  `podcastsearch` varchar(255) DEFAULT NULL,
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
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  `alternatelink` varchar(300) COMMENT 'replaces podcast file link on subscription',
  `alternateimage` varchar(150) COMMENT 'alternate image path for podcast',
  `podcast_subscribe_show` int(3),
  `podcast_image_subscribe` VARCHAR(150) COMMENT 'The image to use for the podcast subscription image',
  `podcast_subscribe_desc` VARCHAR(150) COMMENT 'Words to go below podcast subscribe image',
  `alternatewords` varchar(20),
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_search`
--

CREATE TABLE IF NOT EXISTS `#__bsms_search` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(15) DEFAULT '',
  `text` varchar(15) DEFAULT '',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_series`
--

CREATE TABLE IF NOT EXISTS `#__bsms_series` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `series_text` text,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `teacher` int(3) DEFAULT NULL,
  `description` text,
  `series_thumbnail` varchar(150) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  `language` char(7) NOT NULL COMMENT 'The language code for the Series.',
  `landing_show` int(3),
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_servers`
--

CREATE TABLE IF NOT EXISTS `#__bsms_servers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `server_name` varchar(250) NOT NULL DEFAULT '',
  `server_path` varchar(250) NOT NULL DEFAULT '',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `server_type` char(5) NOT NULL DEFAULT 'local',
  `ftp_username` char(255) NOT NULL,
  `ftp_password` char(255) NOT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
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
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_share`
--

CREATE TABLE IF NOT EXISTS `#__bsms_share` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `params` text,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_studies`
--

CREATE TABLE IF NOT EXISTS `#__bsms_studies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `studydate` datetime DEFAULT NULL,
  `teacher_id` int(11) DEFAULT '1',
  `studynumber` varchar(100) DEFAULT '',
  `booknumber` int(3) DEFAULT '101',
  `chapter_begin` int(3) DEFAULT '1',
  `verse_begin` int(3) DEFAULT '1',
  `chapter_end` int(3) DEFAULT '1',
  `verse_end` int(3) DEFAULT '1',
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
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL COMMENT 'The language code for the Studies.',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`),
  KEY `idx_seriesid` (`series_id`),
  KEY `idx_topicsid` (`topics_id`),
  KEY `idx_user` (`user_id`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_studytopics`
--

CREATE TABLE IF NOT EXISTS `#__bsms_studytopics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `study_id` int(3) NOT NULL DEFAULT '0',
  `topic_id` int(3) NOT NULL DEFAULT '0',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_study` (`study_id`),
  KEY `idx_topic` (`topic_id`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_styles`
--

CREATE TABLE IF NOT EXISTS `#__bsms_styles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `filename` text NOT NULL,
  `stylecode` longtext NOT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_teachers`
--

CREATE TABLE IF NOT EXISTS `#__bsms_teachers` (
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
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  `language` char(7) NOT NULL COMMENT 'The language code for the Teachers.',
  `facebooklink` varchar(150),
  `twitterlink` varchar(150),
  `bloglink` varchar(150),
  `link1` varchar(150),
  `linklabel1` varchar(150),
  `link2` varchar(150),
  `linklabel2` varchar(150),
  `link3` varchar(150),
  `linklabel3` varchar(150),
  `contact` int(11),
  `address` mediumtext NOT NULL,
  `landing_show` int(3),
  `address1` MEDIUMTEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_templatecode`
--

CREATE TABLE IF NOT EXISTS `#__bsms_templatecode` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `type` tinyint(3) NOT NULL,
  `filename` text NOT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `templatecode` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_templates`
--

CREATE TABLE IF NOT EXISTS `#__bsms_templates` (
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
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_timeset`
--

CREATE TABLE IF NOT EXISTS `#__bsms_timeset` (
  `timeset` varchar(14) NOT NULL DEFAULT '',
  `backup` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`timeset`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_topics`
--

CREATE TABLE IF NOT EXISTS `#__bsms_topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic_text` text,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `params` varchar(511) DEFAULT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) DEFAULT CHARSET=utf8;
