DROP TABLE IF EXISTS `#__bsms_install`;

DROP TABLE IF EXISTS `#__bsms_update`;

CREATE TABLE IF NOT EXISTS `#__bsms_update` (
  id      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  version VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

INSERT INTO `#__bsms_update` (id, version) VALUES
(1, '7.0.0'),
(2, '7.0.1'),
(3, '7.0.1.1'),
(4, '7.0.2'),
(5, '7.0.3'),
(6, '7.0.4'),
(7, '7.1.0'),
(8, '7.1.1'),
(9, '7.1.2'),
(10, '7.1.3'),
(11, '8.0.0'),
(12, '8.0.1'),
(13, '8.0.2'),
(14, '8.0.3'),
(15, '8.1.0');

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_admin`
--

CREATE TABLE IF NOT EXISTS `#__bsms_admin` (
  `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `drop_tables`  INT(3) DEFAULT '0',
  `params`       TEXT,
  `asset_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `access`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `installstate` TEXT,
  `debug`        TINYINT(3)       NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_books`
--

CREATE TABLE IF NOT EXISTS `#__bsms_books` (
  `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `bookname`   VARCHAR(250) DEFAULT NULL,
  `booknumber` INT(5) DEFAULT NULL,
  `published`  TINYINT(3)       NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_comments`
--

CREATE TABLE IF NOT EXISTS `#__bsms_comments` (
  `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `published`    TINYINT(3)       NOT NULL DEFAULT '0',
  `study_id`     INT(11)          NOT NULL DEFAULT '0',
  `user_id`      INT(11)          NOT NULL DEFAULT '0',
  `full_name`    VARCHAR(50)      NOT NULL DEFAULT '',
  `user_email`   VARCHAR(100)     NOT NULL DEFAULT '',
  `comment_date` DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_text` TEXT             NOT NULL,
  `asset_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `access`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `language`     CHAR(7)          NOT NULL
  COMMENT 'The language code for the Comments.',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_folders`
--

CREATE TABLE IF NOT EXISTS `#__bsms_folders` (
  `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `server_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `foldername` VARCHAR(250)     NOT NULL DEFAULT '',
  `folderpath` VARCHAR(250)     NOT NULL DEFAULT '',
  `published`  TINYINT(3)       NOT NULL DEFAULT '1',
  `asset_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `access`     INT(10) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_locations`
--

CREATE TABLE IF NOT EXISTS `#__bsms_locations` (
  `id`            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `location_text` VARCHAR(250) DEFAULT NULL,
  `published`     TINYINT(3)       NOT NULL DEFAULT '1',
  `asset_id`      INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `access`        INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `ordering`      INT(11)          NOT NULL DEFAULT '0',
  `landing_show`  INT(3),
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_media`
--

CREATE TABLE IF NOT EXISTS `#__bsms_media` (
  `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `media_text`       TEXT,
  `media_image_name` VARCHAR(250)     NOT NULL DEFAULT '',
  `media_image_path` VARCHAR(250)     NOT NULL DEFAULT '',
  `path2`            VARCHAR(150)     NOT NULL,
  `media_alttext`    VARCHAR(250)     NOT NULL DEFAULT '',
  `published`        TINYINT(3)       NOT NULL DEFAULT '1',
  `asset_id`         INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `access`           INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `ordering`         INT(11)          NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_mediafiles`
--

CREATE TABLE IF NOT EXISTS `#__bsms_mediafiles` (
  `id`                INT(10) unsigned NOT NULL AUTO_INCREMENT,
  `study_id`          INT(5) DEFAULT NULL,
  `server_id`         INT(5) DEFAULT NULL,
  `podcast_id`        VARCHAR(50) DEFAULT NULL,
  `params`            TEXT,
  `metadata`          TEXT,
  `ordering`          INT(11) NOT NULL DEFAULT '0',
  `createdate`        DATETIME DEFAULT NULL,
  `published`         TINYINT(3) NOT NULL DEFAULT '1',
  `comment`          TEXT,
  `asset_id`          INT(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access`            INT(10) unsigned NOT NULL DEFAULT '1',
  `language`          CHAR(7) NOT NULL COMMENT 'The language code for the MediaFile.',
  `created_by`        INT(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias`  VARCHAR(255) NOT NULL DEFAULT '',
  `modified`          DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`       INT(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_study_id` (`study_id`),
  KEY `idx_access` (`access`)
)
  ENGINE=InnoDB
  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_message_type`
--

CREATE TABLE IF NOT EXISTS `#__bsms_message_type` (
  `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `message_type` TEXT             NOT NULL,
  `alias`        VARCHAR(255)
                 CHARACTER SET utf8
                 COLLATE utf8_bin NOT NULL DEFAULT '',
  `published`    TINYINT(3)       NOT NULL DEFAULT '1',
  `asset_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `access`       INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `ordering`     INT(11)          NOT NULL DEFAULT '0',
  `landing_show` INT(3),
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_mimetype`
--

CREATE TABLE IF NOT EXISTS `#__bsms_mimetype` (
  `id`        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mimetype`  VARCHAR(50) DEFAULT NULL,
  `mimetext`  VARCHAR(50) DEFAULT NULL,
  `published` TINYINT(3)       NOT NULL DEFAULT '1',
  `asset_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `access`    INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `ordering`  INT(11)          NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

--
-- Table structure for table `#__bsms_podcast`
--

CREATE TABLE IF NOT EXISTS `#__bsms_podcast` (
  `id`                      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`                   VARCHAR(100) DEFAULT NULL,
  `website`                 VARCHAR(100) DEFAULT NULL,
  `description`             TEXT,
  `image`                   VARCHAR(130) DEFAULT NULL,
  `imageh`                  INT(3) DEFAULT NULL,
  `imagew`                  INT(3) DEFAULT NULL,
  `author`                  VARCHAR(100) DEFAULT NULL,
  `podcastimage`            VARCHAR(130) DEFAULT NULL,
  `podcastsearch`           VARCHAR(255) DEFAULT NULL,
  `filename`                VARCHAR(150) DEFAULT NULL,
  `language`                VARCHAR(10) DEFAULT 'en-us',
  `editor_name`             VARCHAR(150) DEFAULT NULL,
  `editor_email`            VARCHAR(150) DEFAULT NULL,
  `podcastlimit`            INT(5) DEFAULT NULL,
  `published`               TINYINT(3)       NOT NULL DEFAULT '1',
  `episodetitle`            INT(11) DEFAULT NULL,
  `custom`                  VARCHAR(200) DEFAULT NULL,
  `detailstemplateid`       INT(11) DEFAULT NULL,
  `asset_id`                INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `access`                  INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `alternatelink`           VARCHAR(300) COMMENT 'replaces podcast file link on subscription',
  `alternateimage`          VARCHAR(150) COMMENT 'alternate image path for podcast',
  `podcast_subscribe_show`  INT(3),
  `podcast_image_subscribe` VARCHAR(150) COMMENT 'The image to use for the podcast subscription image',
  `podcast_subscribe_desc`  VARCHAR(150) COMMENT 'Words to go below podcast subscribe image',
  `alternatewords`          VARCHAR(20),
  `episodesubtitle`         INT(11) DEFAULT NULL,
  `customsubtitle`          VARCHAR(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_series`
--

CREATE TABLE IF NOT EXISTS `#__bsms_series` (
  `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `series_text`      TEXT,
  `alias`            VARCHAR(255)
                     CHARACTER SET utf8
                     COLLATE utf8_bin NOT NULL DEFAULT '',
  `teacher`          INT(3) DEFAULT NULL,
  `description`      TEXT,
  `series_thumbnail` VARCHAR(150) DEFAULT NULL,
  `published`        TINYINT(3)       NOT NULL DEFAULT '1',
  `asset_id`         INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `ordering`         INT(11)          NOT NULL DEFAULT '0',
  `access`           INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `language`         CHAR(7)          NOT NULL
  COMMENT 'The language code for the Series.',
  `landing_show`     INT(3),
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_servers`
--

CREATE TABLE IF NOT EXISTS `#__bsms_servers` (
  `id`           INT(10) unsigned NOT NULL AUTO_INCREMENT,
  `server_name`  VARCHAR(250) NOT NULL DEFAULT '',
  `type`         CHAR(255) NOT NULL,
  `published`    TINYINT(3) NOT NULL DEFAULT '1',
  `asset_id`     INT(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access`       INT(10) unsigned NOT NULL DEFAULT '1',
  `params`       TEXT NOT NULL,
  `media`        TEXT,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
)
  ENGINE=InnoDB
  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_share`
--

CREATE TABLE IF NOT EXISTS `#__bsms_share` (
  `id`        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`      VARCHAR(250) DEFAULT NULL,
  `params`    TEXT,
  `published` TINYINT(3)       NOT NULL DEFAULT '1',
  `asset_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `access`    INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `ordering`  INT(11)          NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_studies`
--

CREATE TABLE IF NOT EXISTS `#__bsms_studies` (
  `id`                  INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `studydate`           DATETIME DEFAULT NULL,
  `teacher_id`          INT(11) DEFAULT '1',
  `studynumber`         VARCHAR(100) DEFAULT '',
  `booknumber`          INT(3) DEFAULT '101',
  `chapter_begin`       INT(3) DEFAULT '1',
  `verse_begin`         INT(3) DEFAULT '1',
  `chapter_end`         INT(3) DEFAULT '1',
  `verse_end`           INT(3) DEFAULT '1',
  `secondary_reference` TEXT,
  `booknumber2`         VARCHAR(4) DEFAULT NULL,
  `chapter_begin2`      VARCHAR(4) DEFAULT NULL,
  `verse_begin2`        VARCHAR(4) DEFAULT NULL,
  `chapter_end2`        VARCHAR(4) DEFAULT NULL,
  `verse_end2`          VARCHAR(4) DEFAULT NULL,
  `prod_dvd`            VARCHAR(100) DEFAULT NULL,
  `prod_cd`             VARCHAR(100) DEFAULT NULL,
  `server_cd`           VARCHAR(10) DEFAULT NULL,
  `server_dvd`          VARCHAR(10) DEFAULT NULL,
  `image_cd`            VARCHAR(10) DEFAULT NULL,
  `image_dvd`           VARCHAR(10) DEFAULT '0',
  `studytext2`          TEXT,
  `comments`            TINYINT(1) DEFAULT '1',
  `hits`                INT(10)          NOT NULL DEFAULT '0',
  `user_id`             INT(10) DEFAULT NULL,
  `user_name`           VARCHAR(50) DEFAULT NULL,
  `show_level`          VARCHAR(100)     NOT NULL DEFAULT '0',
  `location_id`         INT(3) DEFAULT NULL,
  `studytitle`          TEXT,
  `alias`               VARCHAR(255)
                        CHARACTER SET utf8
                        COLLATE utf8_bin NOT NULL DEFAULT '',
  `studyintro`          TEXT,
  `media_hours`         VARCHAR(2) DEFAULT NULL,
  `media_minutes`       VARCHAR(2) DEFAULT NULL,
  `media_seconds`       VARCHAR(2) DEFAULT NULL,
  `messagetype`         VARCHAR(100) DEFAULT '1',
  `series_id`           INT(3) DEFAULT '0',
  `studytext`           TEXT,
  `thumbnailm`          TEXT,
  `thumbhm`             INT(11) DEFAULT NULL,
  `thumbwm`             INT(11) DEFAULT NULL,
  `params`              TEXT,
  `published`           TINYINT(3)       NOT NULL DEFAULT '0',
  `asset_id`            INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `access`              INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `ordering`            INT(11)          NOT NULL DEFAULT '0',
  `language`            CHAR(7)          NOT NULL
  COMMENT 'The language code for the Studies.',
  `download_id`         INT(10)          NOT NULL DEFAULT '0'
  COMMENT 'Used for link to download of mediafile',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`),
  KEY `idx_seriesid` (`series_id`),
  KEY `idx_user` (`user_id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_studytopics`
--

CREATE TABLE IF NOT EXISTS `#__bsms_studytopics` (
  `id`       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `study_id` INT(3)           NOT NULL DEFAULT '0',
  `topic_id` INT(3)           NOT NULL DEFAULT '0',
  `asset_id` INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `access`   INT(10) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_study` (`study_id`),
  KEY `idx_topic` (`topic_id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_styles`
--

CREATE TABLE IF NOT EXISTS `#__bsms_styles` (
  `id`        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `published` TINYINT(3)       NOT NULL DEFAULT '1',
  `filename`  TEXT             NOT NULL,
  `stylecode` LONGTEXT         NOT NULL,
  `asset_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_teachers`
--

CREATE TABLE IF NOT EXISTS `#__bsms_teachers` (
  `id`                INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `teacher_image`     TEXT,
  `teacher_thumbnail` TEXT,
  `teachername`       VARCHAR(250)     NOT NULL DEFAULT '',
  `alias`             VARCHAR(255)
                      CHARACTER SET utf8
                      COLLATE utf8_bin NOT NULL DEFAULT '',
  `title`             VARCHAR(250) DEFAULT NULL,
  `phone`             VARCHAR(50) DEFAULT NULL,
  `email`             VARCHAR(100) DEFAULT NULL,
  `website`           TEXT,
  `information`       TEXT,
  `image`             TEXT,
  `imageh`            TEXT,
  `imagew`            TEXT,
  `thumb`             TEXT,
  `thumbw`            TEXT,
  `thumbh`            TEXT,
  `short`             TEXT,
  `ordering`          INT(11)          NOT NULL DEFAULT '0',
  `catid`             INT(3) DEFAULT '1',
  `list_show`         TINYINT(1)       NOT NULL DEFAULT '1',
  `published`         TINYINT(3)       NOT NULL DEFAULT '1',
  `asset_id`          INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `access`            INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `language`          CHAR(7)          NOT NULL
  COMMENT 'The language code for the Teachers.',
  `facebooklink`      VARCHAR(150),
  `twitterlink`       VARCHAR(150),
  `bloglink`          VARCHAR(150),
  `link1`             VARCHAR(150),
  `linklabel1`        VARCHAR(150),
  `link2`             VARCHAR(150),
  `linklabel2`        VARCHAR(150),
  `link3`             VARCHAR(150),
  `linklabel3`        VARCHAR(150),
  `contact`           INT(11),
  `address`           MEDIUMTEXT       NOT NULL,
  `landing_show`      INT(3),
  `address1`          MEDIUMTEXT       NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_templatecode`
--

CREATE TABLE IF NOT EXISTS `#__bsms_templatecode` (
  `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `published`    TINYINT(3)       NOT NULL DEFAULT '1',
  `type`         TINYINT(3)       NOT NULL,
  `filename`     TEXT             NOT NULL,
  `asset_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `templatecode` MEDIUMTEXT       NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_templates`
--

CREATE TABLE IF NOT EXISTS `#__bsms_templates` (
  `id`        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`      VARCHAR(255)     NOT NULL,
  `tmpl`      LONGTEXT         NOT NULL,
  `published` TINYINT(3)       NOT NULL DEFAULT '1',
  `params`    LONGTEXT,
  `title`     TEXT,
  `text`      TEXT,
  `pdf`       TEXT,
  `asset_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `access`    INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_timeset`
--

CREATE TABLE IF NOT EXISTS `#__bsms_timeset` (
  `timeset` VARCHAR(14) NOT NULL DEFAULT '',
  `backup`  VARCHAR(14) DEFAULT NULL,
  PRIMARY KEY (`timeset`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_topics`
--

CREATE TABLE IF NOT EXISTS `#__bsms_topics` (
  `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `topic_text` TEXT,
  `published`  TINYINT(3)       NOT NULL DEFAULT '1',
  `params`     VARCHAR(511) DEFAULT NULL,
  `asset_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'FK to the #__assets table.',
  `language`   CHAR(7) DEFAULT '*',
  `access`     INT(10) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;
