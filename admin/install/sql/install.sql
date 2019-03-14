DROP TABLE IF EXISTS `#__bsms_install`;

-- Dump of table #__bsms_update
--  ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__bsms_update` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `version` VARCHAR(255)              DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

INSERT IGNORE INTO `#__bsms_update` (`id`, `version`)
VALUES
  (1, '9.2.0');

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_admin`
--

CREATE TABLE IF NOT EXISTS `#__bsms_admin` (
  `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `drop_tables`  INT(3)                    DEFAULT '0',
  `params`       TEXT,
  `asset_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `installstate` TEXT,
  `debug`        TINYINT(3)       NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_books`
--

CREATE TABLE IF NOT EXISTS `#__bsms_books` (
  `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `bookname`   VARCHAR(250)              DEFAULT NULL,
  `booknumber` INT(5)                    DEFAULT NULL,
  `published`  TINYINT(3)       NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

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
  `asset_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `language`     CHAR(7)          NOT NULL COMMENT 'The language code for the Comments.',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_locations`
--

CREATE TABLE IF NOT EXISTS `#__bsms_locations` (
  `id`               INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `location_text`    VARCHAR(250)                 DEFAULT NULL,
  `contact_id`       INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT 'Used to link to com_contact',
  `address`          TEXT,
  `suburb`           VARCHAR(100)                 DEFAULT NULL,
  `state`            VARCHAR(100)                 DEFAULT NULL,
  `country`          VARCHAR(100)                 DEFAULT NULL,
  `postcode`         VARCHAR(100)                 DEFAULT NULL,
  `telephone`        VARCHAR(255)                 DEFAULT NULL,
  `fax`              VARCHAR(255)                 DEFAULT NULL,
  `misc`             MEDIUMTEXT,
  `image`            VARCHAR(255)                 DEFAULT NULL,
  `email_to`         VARCHAR(255)                 DEFAULT NULL,
  `default_con`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `checked_out`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params`           TEXT                NOT NULL,
  `user_id`          INT(11)             NOT NULL DEFAULT '0',
  `mobile`           VARCHAR(255)        NOT NULL DEFAULT '',
  `webpage`          VARCHAR(255)        NOT NULL DEFAULT '',
  `sortname1`        VARCHAR(255)        NOT NULL,
  `sortname2`        VARCHAR(255)        NOT NULL,
  `sortname3`        VARCHAR(255)        NOT NULL,
  `language`         CHAR(7)             NOT NULL,
  `created`          DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by`       INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `created_by_alias` VARCHAR(255)        NOT NULL DEFAULT '',
  `modified`         DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `metakey`          TEXT                NOT NULL,
  `metadesc`         TEXT                NOT NULL,
  `metadata`         TEXT                NOT NULL,
  `featured`         TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Set if article is featured.',
  `xreference`       VARCHAR(50)         NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  `version`          INT(10) UNSIGNED    NOT NULL DEFAULT '1',
  `hits`             INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `publish_up`       DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down`     DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published`        TINYINT(3)          NOT NULL DEFAULT '1',
  `asset_id`         INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access`           INT(10) UNSIGNED    NOT NULL DEFAULT '1',
  `ordering`         INT(11)             NOT NULL DEFAULT '0',
  `landing_show`     INT(3)                       DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_mediafiles`
--

CREATE TABLE IF NOT EXISTS `#__bsms_mediafiles` (
  `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `study_id`         INT(5)                    DEFAULT NULL,
  `server_id`        INT(5)                    DEFAULT NULL,
  `podcast_id`       VARCHAR(50)               DEFAULT NULL,
  `metadata`         TEXT             NOT NULL,
  `ordering`         INT(11)          NOT NULL DEFAULT '0',
  `createdate`       DATETIME                  DEFAULT NULL,
  `hits`             INT(10)                   DEFAULT '0',
  `published`        TINYINT(3)       NOT NULL DEFAULT '1',
  `comment`          TEXT,
  `downloads`        INT(10)                   DEFAULT '0',
  `plays`            INT(10)                   DEFAULT '0',
  `params`           TEXT,
  `asset_id`         INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access`           INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `language`         CHAR(7)          NOT NULL COMMENT 'The language code for the MediaFile.',
  `created_by`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `created_by_alias` VARCHAR(255)     NOT NULL DEFAULT '',
  `modified`         DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `checked_out`      INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_study_id` (`study_id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_createdby` (`created_by`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_message_type`
--

CREATE TABLE IF NOT EXISTS `#__bsms_message_type` (
  `id`           INT(10) UNSIGNED                                 NOT NULL AUTO_INCREMENT,
  `message_type` TEXT                                             NOT NULL,
  `alias`        VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `published`    TINYINT(3)                                       NOT NULL DEFAULT '1',
  `asset_id`     INT(10) UNSIGNED                                 NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access`       INT(10) UNSIGNED                                 NOT NULL DEFAULT '1',
  `ordering`     INT(11)                                          NOT NULL DEFAULT '0',
  `landing_show` INT(3)                                                    DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_podcast`
--

CREATE TABLE IF NOT EXISTS `#__bsms_podcast` (
  `id`                      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`                   VARCHAR(100)              DEFAULT NULL,
  `website`                 VARCHAR(100)              DEFAULT NULL,
  `description`             TEXT,
  `image`                   VARCHAR(130)              DEFAULT NULL,
  `imageh`                  INT(3)                    DEFAULT NULL,
  `imagew`                  INT(3)                    DEFAULT NULL,
  `author`                  VARCHAR(100)              DEFAULT NULL,
  `podcastimage`            VARCHAR(130)              DEFAULT NULL,
  `podcastsearch`           VARCHAR(255)              DEFAULT NULL,
  `filename`                VARCHAR(150)              DEFAULT NULL,
  `language`                VARCHAR(10)               DEFAULT 'en-us',
  `editor_name`             VARCHAR(150)              DEFAULT NULL,
  `editor_email`            VARCHAR(150)              DEFAULT NULL,
  `podcastlimit`            INT(5)                    DEFAULT NULL,
  `published`               TINYINT(3)       NOT NULL DEFAULT '1',
  `episodetitle`            INT(11)                   DEFAULT NULL,
  `custom`                  VARCHAR(200)              DEFAULT NULL,
  `detailstemplateid`       INT(11)                   DEFAULT NULL,
  `asset_id`                INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access`                  INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `alternatelink`           VARCHAR(300)              DEFAULT NULL COMMENT 'replaces podcast file link on subscription',
  `alternateimage`          VARCHAR(150)              DEFAULT NULL COMMENT 'alternate image path for podcast',
  `podcast_subscribe_show`  INT(3)                    DEFAULT NULL,
  `podcast_image_subscribe` VARCHAR(150)              DEFAULT NULL COMMENT 'The image to use for the podcast subscription image',
  `podcast_subscribe_desc`  VARCHAR(150)              DEFAULT NULL COMMENT 'Words to go below podcast subscribe image',
  `alternatewords`          VARCHAR(20)               DEFAULT NULL,
  `episodesubtitle`         INT(11)                   DEFAULT NULL,
  `customsubtitle`          VARCHAR(200)              DEFAULT NULL,
  `linktype`                INT(10)          NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_series`
--

CREATE TABLE IF NOT EXISTS `#__bsms_series` (
  `id`               INT(10) UNSIGNED                                 NOT NULL AUTO_INCREMENT,
  `series_text`      TEXT,
  `alias`            VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `teacher`          INT(3)                                                    DEFAULT NULL,
  `description`      TEXT,
  `series_thumbnail` VARCHAR(150)                                              DEFAULT NULL,
  `published`        TINYINT(3)                                       NOT NULL DEFAULT '1',
  `asset_id`         INT(10) UNSIGNED                                 NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `ordering`         INT(11)                                          NOT NULL DEFAULT '0',
  `access`           INT(10) UNSIGNED                                 NOT NULL DEFAULT '1',
  `language`         CHAR(7)                                          NOT NULL COMMENT 'The language code for the Series.',
  `landing_show`     INT(3)                                                    DEFAULT NULL,
  `pc_show`          INT(3)                                           NOT NULL DEFAULT '1' COMMENT 'For displaying on
   podcasts page',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_servers`
--

CREATE TABLE IF NOT EXISTS `#__bsms_servers` (
  `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `server_name` VARCHAR(250)     NOT NULL DEFAULT '',
  `published`   TINYINT(3)       NOT NULL DEFAULT '1',
  `asset_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access`      INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `type`        CHAR(255)        NOT NULL,
  `params`      TEXT             NOT NULL,
  `media`       TEXT             NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_studies`
--

CREATE TABLE IF NOT EXISTS `#__bsms_studies` (
  `id`                  INT(10) UNSIGNED                                 NOT NULL AUTO_INCREMENT,
  `studydate`           DATETIME                                                  DEFAULT NULL,
  `teacher_id`          INT(11)                                                   DEFAULT '1',
  `studynumber`         VARCHAR(100)                                              DEFAULT '',
  `booknumber`          INT(3)                                                    DEFAULT '101',
  `chapter_begin`       INT(3)                                                    DEFAULT '1',
  `verse_begin`         INT(3)                                                    DEFAULT '1',
  `chapter_end`         INT(3)                                                    DEFAULT '1',
  `verse_end`           INT(3)                                                    DEFAULT '1',
  `secondary_reference` TEXT,
  `booknumber2`         VARCHAR(4)                                                DEFAULT NULL,
  `chapter_begin2`      VARCHAR(4)                                                DEFAULT NULL,
  `verse_begin2`        VARCHAR(4)                                                DEFAULT NULL,
  `chapter_end2`        VARCHAR(4)                                                DEFAULT NULL,
  `verse_end2`          VARCHAR(4)                                                DEFAULT NULL,
  `prod_dvd`            VARCHAR(100)                                              DEFAULT NULL,
  `prod_cd`             VARCHAR(100)                                              DEFAULT NULL,
  `server_cd`           VARCHAR(10)                                               DEFAULT NULL,
  `server_dvd`          VARCHAR(10)                                               DEFAULT NULL,
  `image_cd`            VARCHAR(10)                                               DEFAULT NULL,
  `image_dvd`           VARCHAR(10)                                               DEFAULT '0',
  `studytext2`          TEXT,
  `comments`            TINYINT(1)                                                DEFAULT '1',
  `hits`                INT(10)                                          NOT NULL DEFAULT '0',
  `user_id`             INT(10)                                                   DEFAULT NULL,
  `user_name`           VARCHAR(50)                                               DEFAULT NULL,
  `show_level`          VARCHAR(100)                                     NOT NULL DEFAULT '0',
  `location_id`         INT(3)                                                    DEFAULT NULL,
  `studytitle`          TEXT,
  `alias`               VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `studyintro`          TEXT,
  `messagetype`         VARCHAR(100)                                              DEFAULT '1',
  `series_id`           INT(3)                                                    DEFAULT '0',
  `studytext`           TEXT,
  `thumbnailm`          TEXT,
  `thumbhm`             INT(11)                                                   DEFAULT NULL,
  `thumbwm`             INT(11)                                                   DEFAULT NULL,
  `params`              TEXT,
  `checked_out`         INT(11) UNSIGNED                                 NOT NULL DEFAULT '0',
  `checked_out_time`    DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published`           TINYINT(3)                                       NOT NULL DEFAULT '0',
  `publish_up`          DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down`        DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified`            DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`         INT(10) UNSIGNED                                 NOT NULL DEFAULT '0',
  `asset_id`            INT(10) UNSIGNED                                 NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access`              INT(10) UNSIGNED                                 NOT NULL DEFAULT '1',
  `ordering`            INT(11)                                          NOT NULL DEFAULT '0',
  `language`            CHAR(7)                                          NOT NULL COMMENT 'The language code for the Studies.',
  `download_id`         INT(10)                                          NOT NULL DEFAULT '0' COMMENT 'Used for link to download of mediafile',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`),
  KEY `idx_seriesid` (`series_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_createdby` (`user_id`),
  KEY `idx_checkout` (`checked_out`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_studytopics`
--

CREATE TABLE IF NOT EXISTS `#__bsms_studytopics` (
  `id`       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `study_id` INT(3)           NOT NULL DEFAULT '0',
  `topic_id` INT(3)           NOT NULL DEFAULT '0',
  `asset_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access`   INT(10) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_study` (`study_id`),
  KEY `idx_topic` (`topic_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;


-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_teachers`
--

CREATE TABLE IF NOT EXISTS `#__bsms_teachers` (
  `id`                INT(10) UNSIGNED                                 NOT NULL AUTO_INCREMENT,
  `teacher_image`     TEXT,
  `teacher_thumbnail` TEXT,
  `teachername`       VARCHAR(250)                                     NOT NULL DEFAULT '',
  `alias`             VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `title`             VARCHAR(250)                                              DEFAULT NULL,
  `phone`             VARCHAR(50)                                               DEFAULT NULL,
  `email`             VARCHAR(100)                                              DEFAULT NULL,
  `website`           TEXT,
  `information`       TEXT,
  `image`             TEXT,
  `imageh`            TEXT,
  `imagew`            TEXT,
  `thumb`             TEXT,
  `thumbw`            TEXT,
  `thumbh`            TEXT,
  `short`             TEXT,
  `ordering`          INT(11)                                          NOT NULL DEFAULT '0',
  `catid`             INT(3)                                                    DEFAULT '1',
  `list_show`         TINYINT(1)                                       NOT NULL DEFAULT '1',
  `published`         TINYINT(3)                                       NOT NULL DEFAULT '1',
  `asset_id`          INT(10) UNSIGNED                                 NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access`            INT(10) UNSIGNED                                 NOT NULL DEFAULT '1',
  `language`          CHAR(7)                                          NOT NULL COMMENT 'The language code for the Teachers.',
  `facebooklink`      VARCHAR(150)                                              DEFAULT NULL,
  `twitterlink`       VARCHAR(150)                                              DEFAULT NULL,
  `bloglink`          VARCHAR(150)                                              DEFAULT NULL,
  `link1`             VARCHAR(150)                                              DEFAULT NULL,
  `linklabel1`        VARCHAR(150)                                              DEFAULT NULL,
  `link2`             VARCHAR(150)                                              DEFAULT NULL,
  `linklabel2`        VARCHAR(150)                                              DEFAULT NULL,
  `link3`             VARCHAR(150)                                              DEFAULT NULL,
  `linklabel3`        VARCHAR(150)                                              DEFAULT NULL,
  `contact`           INT(11)                                                   DEFAULT NULL,
  `address`           MEDIUMTEXT                                       NOT NULL,
  `landing_show`      INT(3)                                                    DEFAULT NULL,
  `address1`          MEDIUMTEXT                                       NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_templatecode`
--

CREATE TABLE IF NOT EXISTS `#__bsms_templatecode` (
  `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `published`    TINYINT(3)       NOT NULL DEFAULT '1',
  `type`         TINYINT(3)       NOT NULL,
  `filename`     TEXT             NOT NULL,
  `asset_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `templatecode` MEDIUMTEXT       NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

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
  `asset_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access`    INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_timeset`
--

CREATE TABLE IF NOT EXISTS `#__bsms_timeset` (
  `timeset` VARCHAR(14) NOT NULL DEFAULT '',
  `backup`  VARCHAR(14)          DEFAULT NULL,
  PRIMARY KEY (`timeset`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_topics`
--

CREATE TABLE IF NOT EXISTS `#__bsms_topics` (
  `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `topic_text` TEXT,
  `published`  TINYINT(3)       NOT NULL DEFAULT '1',
  `params`     VARCHAR(511)              DEFAULT NULL,
  `asset_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `language`   CHAR(7)                   DEFAULT '*',
  `access`     INT(10) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- Dump of table #__bsms_admin
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_admin` (`id`, `drop_tables`, `params`, `asset_id`, `access`, `installstate`, `debug`)
VALUES
(1, 0,
 '{\"simple_mode\":\"1\",\"metakey\":\"\",\"metadesc\":\"\",\"compat_mode\":\"0\",\"studylistlimit\":\"10\",\"show_location_media\":\"0\",\"popular_limit\":\"\",\"character_filter\":\"1\",\"format_popular\":\"0\",\"default_main_image\":\"\",\"default_series_image\":\"\",\"default_teacher_image\":\"\",\"default_download_image\":\"\",\"default_showHide_image\":\"\",\"thumbnail_teacher_size\":\"150\",\"thumbnail_series_size\":\"150\",\"thumbnail_study_size\":\"150\",\"location_id\":\"-1\",\"teacher_id\":\"1\",\"series_id\":\"-1\",\"booknumber\":\"-1\",\"messagetype\":\"-1\",\"default_study_image\":\"\",\"download\":\"1\",\"target\":\" \",\"server\":\"1\",\"podcast\":[\"-1\"],\"from\":\"x\",\"to\":\"x\",\"pFrom\":\"x\",\"pTo\":\"x\",\"controls\":\"1\",\"jwplayer_pro\":\"0\",\"jwplayer_key\":\"\",\"jwplayer_cdn\":\"\",\"jwplayer_skin\":\"\",\"jwplayer_autostart\":\"false\",\"jwplayer_fallback\":\"true\",\"jwplayer_mute\":\"false\",\"jwplayer_stagevideo\":\"false\",\"jwplayer_primary\":\"html5\",\"playlist\":\"false\",\"jwplayer_listbar\":\"false\",\"jwplayer_logo\":\"\",\"sharing\":\"false\",\"jwplayer_related\":\"false\",\"jwplayer_advertising\":\"\",\"rtmp\":\"Comming Soon\",\"ga\":\"Comming Soon\",\"jwplayer_sitecatalyst\":\"Comming Soon\",\"captions\":\"false\"}',
 7587, 1, NULL, 0);


-- Dump of table #__bsms_books
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_books` (`id`, `bookname`, `booknumber`, `published`)
VALUES
(1, 'JBS_BBK_GENESIS', 101, 1),
(2, 'JBS_BBK_EXODUS', 102, 1),
(3, 'JBS_BBK_LEVITICUS', 103, 1),
(4, 'JBS_BBK_NUMBERS', 104, 1),
(5, 'JBS_BBK_DEUTERONOMY', 105, 1),
(6, 'JBS_BBK_JOSHUA', 106, 1),
(7, 'JBS_BBK_JUDGES', 107, 1),
(8, 'JBS_BBK_RUTH', 108, 1),
(9, 'JBS_BBK_1SAMUEL', 109, 1),
(10, 'JBS_BBK_2SAMUEL', 110, 1),
(11, 'JBS_BBK_1KINGS', 111, 1),
(12, 'JBS_BBK_2KINGS', 112, 1),
(13, 'JBS_BBK_1CHRONICLES', 113, 1),
(14, 'JBS_BBK_2CHRONICLES', 114, 1),
(15, 'JBS_BBK_EZRA', 115, 1),
(16, 'JBS_BBK_NEHEMIAH', 116, 1),
(17, 'JBS_BBK_ESTHER', 117, 1),
(18, 'JBS_BBK_JOB', 118, 1),
(19, 'JBS_BBK_PSALM', 119, 1),
(20, 'JBS_BBK_PROVERBS', 120, 1),
(21, 'JBS_BBK_ECCLESIASTES', 121, 1),
(22, 'JBS_BBK_SONG_OF_SOLOMON', 122, 1),
(23, 'JBS_BBK_ISAIAH', 123, 1),
(24, 'JBS_BBK_JEREMIAH', 124, 1),
(25, 'JBS_BBK_LAMENTATIONS', 125, 1),
(26, 'JBS_BBK_EZEKIEL', 126, 1),
(27, 'JBS_BBK_DANIEL', 127, 1),
(28, 'JBS_BBK_HOSEA', 128, 1),
(29, 'JBS_BBK_JOEL', 129, 1),
(30, 'JBS_BBK_AMOS', 130, 1),
(31, 'JBS_BBK_OBADIAH', 131, 1),
(32, 'JBS_BBK_JONAH', 132, 1),
(33, 'JBS_BBK_MICAH', 133, 1),
(34, 'JBS_BBK_NAHUM', 134, 1),
(35, 'JBS_BBK_HABAKKUK', 135, 1),
(36, 'JBS_BBK_ZEPHANIAH', 136, 1),
(37, 'JBS_BBK_HAGGAI', 137, 1),
(38, 'JBS_BBK_ZECHARIAH', 138, 1),
(39, 'JBS_BBK_MALACHI', 139, 1),
(40, 'JBS_BBK_MATTHEW', 140, 1),
(41, 'JBS_BBK_MARK', 141, 1),
(42, 'JBS_BBK_LUKE', 142, 1),
(43, 'JBS_BBK_JOHN', 143, 1),
(44, 'JBS_BBK_ACTS', 144, 1),
(45, 'JBS_BBK_ROMANS', 145, 1),
(46, 'JBS_BBK_1CORINTHIANS', 146, 1),
(47, 'JBS_BBK_2CORINTHIANS', 147, 1),
(48, 'JBS_BBK_GALATIANS', 148, 1),
(49, 'JBS_BBK_EPHESIANS', 149, 1),
(50, 'JBS_BBK_PHILIPPIANS', 150, 1),
(51, 'JBS_BBK_COLOSSIANS', 151, 1),
(52, 'JBS_BBK_1THESSALONIANS', 152, 1),
(53, 'JBS_BBK_2THESSALONIANS', 153, 1),
(54, 'JBS_BBK_1TIMOTHY', 154, 1),
(55, 'JBS_BBK_2TIMOTHY', 155, 1),
(56, 'JBS_BBK_TITUS', 156, 1),
(57, 'JBS_BBK_PHILEMON', 157, 1),
(58, 'JBS_BBK_HEBREWS', 158, 1),
(59, 'JBS_BBK_JAMES', 159, 1),
(60, 'JBS_BBK_1PETER', 160, 1),
(61, 'JBS_BBK_2PETER', 161, 1),
(62, 'JBS_BBK_1JOHN', 162, 1),
(63, 'JBS_BBK_2JOHN', 163, 1),
(64, 'JBS_BBK_3JOHN', 164, 1),
(65, 'JBS_BBK_JUDE', 165, 1),
(66, 'JBS_BBK_REVELATION', 166, 1),
(67, 'JBS_BBK_TOBIT', 167, 1),
(68, 'JBS_BBK_JUDITH', 168, 1),
(69, 'JBS_BBK_1MACCABEES', 169, 1),
(70, 'JBS_BBK_2MACCABEES', 170, 1),
(71, 'JBS_BBK_WISDOM', 171, 1),
(72, 'JBS_BBK_SIRACH', 172, 1),
(73, 'JBS_BBK_BARUCH', 173, 1);

-- Dump of table #__bsms_locations
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_locations` (`id`, `location_text`, `contact_id`, `address`, `suburb`, `state`, `country`, `postcode`, `telephone`, `fax`, `misc`, `image`, `email_to`, `default_con`, `checked_out`, `checked_out_time`, `params`, `user_id`, `mobile`, `webpage`, `sortname1`, `sortname2`, `sortname3`, `language`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `metakey`, `metadesc`, `metadata`, `featured`, `xreference`, `version`, `hits`, `publish_up`, `publish_down`, `published`, `asset_id`, `access`, `ordering`, `landing_show`)
VALUES
(1, 'My Location', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '0000-00-00 00:00:00', '', 0,
 '', '', '', '', '', '', '0000-00-00 00:00:00', 0, '', '0000-00-00 00:00:00', 0, '', '', '', 0, '', 1, 0,
 '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 7480, 1, 1, NULL);

-- Dump of table #__bsms_mediafiles
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_mediafiles` (`id`, `study_id`, `server_id`, `podcast_id`, `metadata`, `ordering`, `createdate`, `hits`, `published`, `comment`, `downloads`, `plays`, `params`, `asset_id`, `access`, `language`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `checked_out`, `checked_out_time`)
VALUES
(1, 1, 1, '1', '{\"plays\":\"0\", \"downloads\":\"0\"}', 0, '2009-09-13 00:10:00', 0, 1, 'Sample Media file', 0, 10,
 '{\"link_type\":\"\",\"docMan_id\":\"0\",\"article_id\":\"-1\",\"virtueMart_id\":\"0\",\"player\":\"7\",\"popup\":\"3\",\"mediacode\":\"\",\"filename\":\"\\/MediaFiles\\/2015\\/2015-008.mp3\",\"size\":false,\"special\":\"\",\"media_image\":\"images\\/biblestudy\\/speaker24.png\",\"mime_type\":\"audio\\/mp3\",\"playerwidth\":\"\",\"playerheight\":\"\",\"itempopuptitle\":\"\",\"itempopupfooter\":\"\",\"popupmargin\":\"50\",\"autostart\":\"false\"}',
 7481, 1, '*', 1, 'admin', '0000-00-00 00:00:00', 1, 0, '0000-00-00 00:00:00'),
(2, 1, 3, '', '', 0, '2010-03-12 18:10:00', 0, 1, '', 0, 6,
 '{\"link_type\":\"0\",\"docMan_id\":\"0\",\"article_id\":\"-1\",\"virtueMart_id\":\"0\",\"player\":\"0\",\"popup\":\"2\",\"mediacode\":\"\",\"filename\":\"\\/images\\/growthgroupquestions\\/Colossians3_5-11Questions.pdf\",\"size\":0,\"special\":\"\",\"media_image\":\"images\\/biblestudy\\/pdf16.png\",\"mime_type\":\"application\\/pdf\",\"playerwidth\":\"\",\"playerheight\":\"\",\"itempopuptitle\":\"\",\"itempopupfooter\":\"\",\"popupmargin\":\"50\",\"autostart\":\"\"}',
 7593, 1, '*', 0, '', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00'),
(3, 1, 2, '', '', 0, '2015-07-28 19:18:07', 0, 1, '', 0, 0,
 '{\"link_type\":\"0\",\"docMan_id\":\"0\",\"article_id\":\"-1\",\"virtueMart_id\":\"0\",\"player\":\"1\",\"popup\":\"3\",\"mediacode\":\"\",\"filename\":\"https:\\/\\/youtu.be\\/PsFo6MhAB9o\",\"size\":0,\"special\":\"\",\"media_image\":\"images\\/biblestudy\\/youtube24.png\",\"mime_type\":\"video\\/mp4\",\"playerwidth\":\"\",\"playerheight\":\"\",\"itempopuptitle\":\"\",\"itempopupfooter\":\"\",\"popupmargin\":\"50\",\"autostart\":\"\"}',
 7596, 1, '*', 0, '', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00');

-- Dump of table #__bsms_message_type
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_message_type` (`id`, `message_type`, `alias`, `published`, `asset_id`, `access`, `ordering`, `landing_show`)
VALUES
(1, 'Sunday', X'73756E646179', 1, 7482, 1, 1, NULL);

-- Dump of table #__bsms_podcast
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_podcast` (`id`, `title`, `website`, `description`, `image`, `imageh`, `imagew`, `author`, `podcastimage`, `podcastsearch`, `filename`, `language`, `editor_name`, `editor_email`, `podcastlimit`, `published`, `episodetitle`, `custom`, `detailstemplateid`, `asset_id`, `access`, `alternatelink`, `alternateimage`, `podcast_subscribe_show`, `podcast_image_subscribe`, `podcast_subscribe_desc`, `alternatewords`, `episodesubtitle`, `customsubtitle`, `linktype`)
VALUES
(1, 'My Podcast', 'www.mywebsite.com', 'Podcast Description goes here', 'www.mywebsite.com/myimage.jpg', 30, 30,
 'Pastor Billy', 'www.mywebsite.com/myimage.jpg', 'jesus', 'mypodcast.xml', '*', 'Jim Editor', 'jim@mywebsite.com',
 50, 1, 0, '', 1, 7483, 1, '', '', 0, '', '', '', 0, '', 0);

-- Dump of table #__bsms_series
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_series` (`id`, `series_text`, `alias`, `teacher`, `description`, `series_thumbnail`, `published`, `asset_id`, `ordering`, `access`, `language`, `landing_show`, `pc_show`)
VALUES
(1, 'Worship Series', X'776F72736869702D736572696573', -1, '', '', 1, 7484, 1, 1, '*', NULL, 1);

-- Dump of table #__bsms_servers
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_servers` (`id`, `server_name`, `published`, `asset_id`, `access`, `type`, `params`, `media`)
VALUES
(1, 'Legacy MP3', 1, 7478, 1, 'legacy',
 '{\"path\":\"\\/\\/www.calvarychapelnewberg.net\\/\",\"protocol\":\"http:\\/\\/\"}',
 '{\"link_type\":\"1\",\"player\":\"7\",\"popup\":\"3\",\"mediacode\":\"\",\"media_image\":\"images\\/biblestudy\\/mp3.png\",\"mime_type\":\"audio\\/mp3\",\"autostart\":\"1\"}'),
(2, 'Legacy YouTube', 1, 7588, 1, 'legacy', '{\"path\":\"\",\"protocol\":\"http:\\/\\/\"}',
 '{\"link_type\":\"0\",\"player\":\"1\",\"popup\":\"3\",\"mediacode\":\"\",\"media_image\":\"images\\/biblestudy\\/youtube24.png\",\"mime_type\":\"video\\/mp4\",\"autostart\":\"1\"}'),
(3, 'Legacy PDF', 1, 7589, 1, 'legacy',
 '{\"path\":\"http:\\/\\/calvarynewberg.org\\/\",\"protocol\":\"http:\\/\\/\"}',
 '{\"link_type\":\"1\",\"player\":\"0\",\"popup\":\"2\",\"mediacode\":\"\",\"media_image\":\"images\\/biblestudy\\/pdf16.png\",\"mime_type\":\"application\\/pdf\",\"autostart\":\"1\"}');


-- Dump of table #__bsms_studies
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_studies` (`id`, `studydate`, `teacher_id`, `studynumber`, `booknumber`, `chapter_begin`, `verse_begin`, `chapter_end`, `verse_end`, `secondary_reference`, `booknumber2`, `chapter_begin2`, `verse_begin2`, `chapter_end2`, `verse_end2`, `prod_dvd`, `prod_cd`, `server_cd`, `server_dvd`, `image_cd`, `image_dvd`, `studytext2`, `comments`, `hits`, `user_id`, `user_name`, `show_level`, `location_id`, `studytitle`, `alias`, `studyintro`, `messagetype`, `series_id`, `studytext`, `thumbnailm`, `thumbhm`, `thumbwm`, `params`, `checked_out`, `checked_out_time`, `published`, `publish_up`, `publish_down`, `modified`, `modified_by`, `asset_id`, `access`, `ordering`, `language`, `download_id`)
VALUES
(1, '2010-03-13 00:10:00', 1, '2015-01', 151, 3, 5, 3, 11, '', '-1', '', '', '', '', NULL, NULL, NULL, NULL, NULL,
 '0', NULL, 1, 0, 0, '', '0', 1, 'Four Steps to Defeating the Flesh',
 X'666F75722D73746570732D746F2D646566656174696E672D7468652D666C657368',
 'If you’ve lived around Oregon very long you know what Oregon mud is like. The soils in the Willamette Valley contain a fair amount of clay. I remember trying to put in a sprinkler system when we first built our house. Foolishly, I thought I could beat the winter rains and get the system put in. Such was not the case. Towards the end I remember slogging around the yard—with each step I took it got harder and harder to walk as more and more mud clung to my shoes.',
 '1', 1,
 'If you’ve lived around Oregon very long you know what Oregon mud is like. The soils in the Willamette Valley contain a fair amount of clay. I remember trying to put in a sprinkler system when we first built our house. Foolishly, I thought I could beat the winter rains and get the system put in. Such was not the case. Towards the end I remember slogging around the yard—with each step I took it got harder and harder to walk as more and more mud clung to my shoes. I use that analogy because the goal of becoming a Christian is to become like God in our character. We realize the old ways of sin just aren’t working for us and we come to Christ who forgives us and gives us eternal life. As Paul says: “old things have passed away, and look new things have come” (2 Cor 5:17). So far so good. The trouble starts when we begin walking around in this life. Instead of feeling light and free and pure—we feel the “old man” or “the flesh” sticking to our character like clay sticking to our shoes. The old ways of thinking, speaking, and acting are still with us—ready to take over at the slightest provocation or temptation. It makes it hard to walk with Christ effectively and smoothly. Paul says it well in: Romans 7:18 “For I know that nothing good lives in me, that is, in my flesh. For the desire to do what is good is with me, but there is no ability to do it. 19 For I do not do the good that I want to do, but I practice the evil that I do not want to do. 20 Now if I do what I do not want, I am no longer the one doing it, but it is the sin that lives in me.” So what’s a Christian to do? The goal is to be like Christ but the flesh seems to be so successful in holding us back. That’s what Colossians 3 and much of Chapter 4 is about. Paul outlines for us what the old character looks like, and what the new nature looks like by contrast. He describes the process of change as something as simple as changing your clothes. It’s simple in theory, difficult in practice, but totally worth the effort. He describes this change away from the old character with four actions: “put to death” (verse 5), “put away” (verse 8) and “put off” (verse 9), then we are to “put on” the new character of Christ (verse 10). The character traits that we are to kill, put away, and put off are: improper or unchecked desire, anger, and lying. From these three spring most of the things we associate with “the flesh”. 5 Paul starts off with “therefore”. In light of the fact that we are risen people, we need to start thinking like citizens of heaven (the new age), not like residents of earth (this age). Paul says for us to “put to death what belongs to your earthly nature.” The word “put to death” means: “to make dead.” How does that happen? Paul gives us a clue in Romans 8:13: “But if by the Spirit you put to death the deeds of the body, you will live.” So there is this cooperation that takes place between God’s Spirit and us. In reality, you will not be free from the presence and temptation to act in the old ways until you are given you new body. But as James 1:14 says: “But each person is tempted when he is drawn away and enticed by his own evil desires. 15 Then after desire has conceived, it gives birth to sin, and when sin is fully grown, it gives birth to death.   Evil thoughts will occur; it is what we do with them that matters. When they come we can, by the power of the Spirit, tell them they are dead and have no place in us anymore. Do it strongly; do it often. Picture those thoughts on a paper, and then nail that paper to the cross. Jesus died to rid you of acting on those thought patterns. The word “put to death” can mean: “to deprive of power.” Cut the supply lines to the old nature and it will become weak nd starve. So here is the big question: what in your life is still feeding the flesh? It’s different for everyone, mind you. Starve the flesh and it will go a long way to killing it. This is part 1: “kill the old ways, one thought at a time.” Next, Paul lists five manifestations of the flesh that we need to watch out for. Make no mistake—these are powerful forces in the human mind and are not easily dissuaded or killed. Sexual immorality. It means any sexual intimacy outside of the marriage between a man and a woman. Impurity means the character infected by immoral behavior. Lust refers to any unbridled passion but here likely refers to sexual desire that is out of bounds and out of control. Evil desire is probably better translated as lust—but could refer to the thoughts that precede lust. Remember again, it isn’t the temptation but what we do with it that matters. We sin when we focus on and even encourage impure thoughts that lead to impure actions. Greed, which is idolatry. Greed here is a general term for unchecked physical pleasure. Paul calls it greed because the worship of pleasure – the worship of anything for that matter – takes first place in front of God, and that is idolatry. In Paul’s day, literal idol worship was rampant, as was sexual immorality with temple prostitutes as part of that idolatry. And because sex is such a powerful thing, these actions led to impure thoughts, desires, and lusts. 6 God’s wrath is the natural outpouring against anyone who is not pure. Doing something that is sinful is disobeying God because He said “Be holy, even as I am holy” (Lev. 11:45). Some people say that a loving God cannot be a wrathful God. But you cannot have love without wrath against evil. Love means protecting the innocent against evil. It means coming against evil and destroying it. Just ask any parent who has lost a child to horrible violence—and you begin to understand the love and wrath of God. Before we get too smug in the opinion that we aren’t like those disobedient sinners—look at the next verse. 7 Before we knew Jesus we all disobeyed and were guilty as charged. “All have sinned and fall short of the glory of God” (Romans 3:23). We acted out these things (“walked”) because we were steeped in them. We could translate this verse as: “when your life consisted of such wretched things as these.” As fallen people we couldn’t help but act in these ways, but not anymore. Now we are steeped in the Messiah’s forgiving and cleansing love. We don’t have to act out the deeds of the flesh any more. And Paul goes onto to step 2 of how to do that next. 8 Step One is to “Kill kill, kill them all” – all the thoughts that come from the flesh and urge us to think and do things that are not like God. We should nail those thoughts to the cross. Step Two is to “put off” or “put away” the deeds of the flesh. The flesh should feel like a foreign intruder. We don’t just ignore it; we actively push it away. The Greek here is the picture of taking off your clothes. The flesh is like an old set of worn out clothes that, through the Spirit, we can literally take off. Paul helps us understand this by being very specific in another major area of the old nature: anger. He brings in five ways anger destroys us. Anger and wrath are related. Anger is Greek word for a plant that is bursting with juice. What a great picture of when we get red-hot with anger and our face literally flushes red with blood. Wrath means: “to rush in” and is what we do when we are angry. How often do we get angry and let our anger vent with hurtful words that later we wish we could take back. Malice is when we mean to hurt someone—often the result of anger. Slander is what we say about them to hurt them. It’s the Greek word: blasphema. In this case it is hurtful words about another, rather than God. Finally “filthy language” refers to the words used—words which hurt both the speaker and the hearer. Why do we get angry? There are many answers to that question—but I want to bring in four main ones: Frustration – when a goal is thwarted; like getting to work on time thwarted by heavy traffic. Hurt – when the words or actions of another wounds us. Loss – when something that we hold dear is taken away, like a job or a spouse. Victim thinking – when I don’t get what I think I deserve. Put it to death by declaring the sin nature dead on the cross. Romans 6:6 “For we know that our old self was crucified with Him in order that sin’s dominion over the body may be abolished, so that we may no longer be enslaved to sin”. Put it away by separating yourself from that old character, like you’d change out of an old set of clothes. Put it off, remove its power, by thinking of the flesh as something foreign to you, like a limb that is no longer a part of your body—gangrenous and in need of amputation. Then put on the character of Christ like putting on a costume in a play. The more you wear it the more comfortable you will be in it and the more you will own that new character Each one of us struggles with different parts of our old nature. You can be a Christian for many years and still struggle with areas of the flesh. I think that often what happens is a trigger occurs—perhaps a word or phrase that reminds our unconscious mind of an abuse from childhood. We don’t think about but that trigger dis-regulates us, our pre-frontal cortex goes offline and our fleshly nature takes over. The trick is to begin to notice it—even physically in our body—and then work with the Holy Spirit to put you back into your new-self state of mind. 9 – 10 In verse 9, Paul moves to the third major category of the flesh—our interactions with others in relation to the truth. Lying is a default human behavior that we learn at a very young age. We bend facts and manipulate the truth in order to get a result we feel we can’t get by telling the truth. We do it in big ways and in small ways all the time. But lying is not a character trait of heaven—and lying is one of the sins specifically mentioned in Revelation 21:15 as being excluded from heaven. It’s part of the old set of clothes that we need to “put off”. It means: “to wholly strip off of oneself” or “to disarm”. Does it mean we must then be completely transparently honest with everything we think at all times? Of course not. Ephesians 4:15 says: “Speaking the truth in love let us grow in every way into Him.” Be honest always, and always speak those truths in a way that will build up and encourage another’s relationship with Christ. One way we disarm lying is to think: what can I say that will benefit this person, truly. Sometimes that is saying a difficult thing, but not in a hurtful way, but a helpful one. We move now from “putting off” the old character to “putting on” a new one—which is the character of Christ. The words literally mean to put on a change of clothes. Paul uses the same Greek word in Rom. 13:14: “But put on the Lord Jesus Christ, and make no plans to satisfy the fleshly desires.” The word can also mean: “to sink into”. Believe it or not, you have the power to put on or sink into the character of Jesus—forcing out the old flesh, which is always making plans to satisfy itself. At first it might feel very foreign, like wearing a costume while playing a part in a play. But you know, that’s okay. Play the part of a person who thinks, acts, and speaks like Jesus in love, joy, peace, patience, kindness, gentleness and the like. It might feel a little hypocritical at first but the more you practice the part the more comfortable you get with it and the more it becomes part of you. All you are doing really is cooperating with the force of the Holy Spirit in you that already wants to make these changes. It is part of that “renewal” process Paul talks about here. The more we know about Jesus and His character, and the more we work towards mirroring that character, the more into His “image” we become. You’ll find in the end that this new character is the “real” new you that God has been making all along. 11 Verse 11 brings up what is often the source of the fleshly desires that run counter to the character of Christ—divisions along racial, economic, social, and religious differences. After Alexander the Great conquered much of the known world, he spread the Greek culture far and wide. So the Greeks felt very self-important culturally. The Jews also felt very superior religiously as they had the Torah and a covenant with Yahweh. This division was never so strident when it came to who was in the covenant and who was out—via circumcision. Barbarians were any non-Greek or non-Jews and Scythians were a little known race of people from the far-northern part of the Middle East who were thought to be no better than animals. Slavery was well known in that culture and separated people along socio-economic lines. Often times it is these things that cause the flesh to flare up. But Paul says Christ is in all peoples (how that could be rendered). Jesus is the great equalizer of religious, racial, cultural and socio-economic status. Also, as Paul says in Galatians 3:28, the great equalizer of gender inequalities. In Christ we should never let those things separate us. That’s the old way, not the new. So we’re talked about three ways to help rid ourselves of the old nature and one way to help nurture the new character. Try it on with something you are struggling with, like anger. Picture what it would be like if something or someone makes you angry. How would you normally react and how can these new steps intervene? What are the qualities in the new character that replace unchecked desire, anger, and dishonesty? Fidelity (commitment in your relationships with others and with God) Security (trusting that God will supply your needs) Love (self-sacrificing, other-centered affection that looks out for the good of others always) We’ll talk more about these as they relate to everyday life in the coming verses.',
 '', NULL, NULL,
 '{\"metakey\":\"Rain, Flesh, \\\"Sexual immorality\\\", Impurity, Lust, \\\"Evil desire\\\", Greed, Frustration, Hurt, Loss, \\\"Victim thinking\\\", \\\"Put it to death\\\", \\\"Put it away\\\",\\\"Put it off\\\"\",\"metadesc\":\"\"}',
 0, '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2015-07-28 23:46:05', 627, 7479, 1, 1,
 '*', -1);

-- Dump of table #__bsms_studytopics
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_studytopics` (`id`, `study_id`, `topic_id`, `asset_id`, `access`)
VALUES
(3, 1, 114, 7594, 1),
(4, 1, 114, 7595, 1);


-- Dump of table #__bsms_teachers
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_teachers` (`id`, `teacher_image`, `teacher_thumbnail`, `teachername`, `alias`, `title`, `phone`, `email`, `website`, `information`, `image`, `imageh`, `imagew`, `thumb`, `thumbw`, `thumbh`, `short`, `ordering`, `catid`, `list_show`, `published`, `asset_id`, `access`, `language`, `facebooklink`, `twitterlink`, `bloglink`, `link1`, `linklabel1`, `link2`, `linklabel2`, `link3`, `linklabel3`, `contact`, `address`, `landing_show`, `address1`)
VALUES
(1, '', '', 'Billy Sunday', X'62696C6C792D73756E646179', 'Pastor', '555-555-5555', 'billy@sunday.com',
 'http://billysunday.com',
 'William Ashley Sunday was an American athlete who after being a popular outfielder in baseballs National League during the 1880s became the most celebrated and influential American evangelist during the first two decades of the 20th century. ',
 'media/com_biblestudy/images/billy_sunday11.jpg', '276', '197', 'media/com_biblestudy/images/images.jpg', '101',
 '141', 'Billy Sunday: 1862-1935', 0, 1, 1, 1, 7489, 1, '*', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
 NULL, '', NULL, '');

-- Dump of table #__bsms_templatatecode

INSERT IGNORE INTO `#__bsms_templatecode` (`id`, `published`, `type`, `filename`, `asset_id`, `templatecode`) VALUES
(1, 1, 1, 'easy', 188, '<?php\r\n\r\n/**\r\n * Helper for Template Code\r\n *\r\n * @package    Proclaim.Admin\r\n * @copyright  2007 - 2019 (C) CWM Team All rights reserved\r\n * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL\r\n * @link       https://www.christianwebministries.org\r\n * */\r\n// No Direct Access\r\ndefined(\'_JEXEC\') or die;\r\n\r\n// Do not remove\r\n// this is here to make sure that security of the site is maintained. It should be placed in every template file\r\nJHtml::addIncludePath(JPATH_COMPONENT . \'/helpers/html\');\r\n\r\nJHtml::_(\'bootstrap.tooltip\');\r\nJHtml::_(\'dropdown.init\');\r\nJHtml::_(\'behavior.multiselect\');\r\nJHtml::_(\'formbehavior.chosen\', \'select\');\r\n\r\n$app       = JFactory::getApplication();\r\n$user      = JFactory::getUser();\r\n$userId    = $user->get(\'id\');\r\n$listOrder = $this->escape($this->state->get(\'list.ordering\'));\r\n$listDirn  = $this->escape($this->state->get(\'list.direction\'));\r\n$archived  = $this->state->get(\'filter.published\') == 2 ? true : false;\r\n$trashed   = $this->state->get(\'filter.published\') == -2 ? true : false;\r\n$saveOrder = $listOrder == \'study.ordering\';\r\n$columns   = 12;\r\n\r\n\r\n\r\n?>\r\n<style>img{border-radius:4px;}</style>\r\n\r\n\r\n  <div class=\"row-fluid span12\">\r\n    <h2>\r\n      Teachings\r\n    </h2>\r\n  </div>\r\n\r\n\r\n\r\n  <div class=\"row-fluid span12 dropdowns\" style=\"background-color:#A9A9A9; margin:0 -5px; padding:8px 8px; border:1px solid #C5C1BE; position:relative; -webkit-border-radius:10px;\">\r\n\r\n    <?php\r\n    echo $this->page->books;\r\n    echo $this->page->teachers;\r\n    echo $this->page->series;\r\n    $oddeven = \'\';\r\n	$class1 = \'#d3d3d3\';\r\n    $class2 = \'\';?>\r\n</div>\r\n<?php foreach ($this->items as $study)\r\n{\r\n\r\n	$oddeven = ($oddeven == $class1) ? $class2 : $class1;\r\n	?>\r\n	<div style=\"width:100%;\">\r\n		<div class=\"span3\"><div style=\"padding:12px 8px;line-height:22px;height:200px;\">\r\n				<?php if ($study->study_thumbnail) {echo \'<span style=\"max-width:250px; height:auto;\">\'.$study->study_thumbnail .\'</span>\'; echo \'<br />\';} ?>\r\n				<strong><?php echo $study->studytitle;?></strong><br />\r\n				<span style=\"color:#9b9b9b;\"><?php echo $study->scripture1;?> | <?php echo $study->studydate;?></span><br />\r\n				<div style=\"font-size:85%;margin-bottom:-17px;max-height:122px;overflow:hidden;\"><?php echo $study->teachername;?></div><br /><div style=\"background: rgba(0, 0, 0, 0) linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, white 100%) repeat scroll 0 0;bottom: 0;height: 32px;margin-top: -32px; position: relative;width: 100%;\"></div>\r\n				<?php echo $study->media; ?>\r\n			</div></div>\r\n\r\n\r\n	</div>\r\n<?php }?>\r\n<div class=\"row-fluid span12 pagination pagelinks\" style=\"background-color: #A9A9A9;\r\n	margin: 0 -5px;\r\n	padding: 8px 8px;\r\n	border: 1px solid #C5C1BE;\r\n	position: relative;\r\n	-webkit-border-radius: 9px;\">\r\n	<?php echo $this->pagination->getPageslinks();?>\r\n</div>\r\n');

-- Dump of table #__bsms_templates
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_templates` (`id`, `type`, `tmpl`, `published`, `params`, `title`, `text`, `pdf`, `asset_id`, `access`)
VALUES
(1, 'tmplList', '', 1,
 '{\"useterms\":\"0\",\"terms\":\"\",\"css\":\"biblestudy.css\",\"studieslisttemplateid\":\"1\",\"sermonstemplate\":\"0\",\"detailstemplateid\":\"1\",\"sermontemplate\":\"0\",\"teachertemplateid\":\"1\",\"teachertemplate\":\"0\",\"teacherstemplate\":\"0\",\"serieslisttemplateid\":\"1\",\"seriesdisplaystemplate\":\"0\",\"seriesdetailtemplateid\":\"1\",\"seriesdisplaytemplate\":\"0\",\"offset\":\"false\",\"teacher_id\":[\"-1\"],\"series_id\":[\"-1\"],\"booknumber\":[\"-1\"],\"topic_id\":[\"-1\"],\"messagetype\":[\"-1\"],\"locations\":[\"-1\"],\"show_verses\":\"0\",\"stylesheet\":\"\",\"date_format\":\"2\",\"custom_date_format\":\"\",\"duration_type\":\"2\",\"protocol\":\"http:\\/\\/\",\"player\":\"0\",\"popuptype\":\"window\",\"internal_popup\":\"1\",\"special\":\"_blank\",\"autostart\":\"1\",\"playerresposive\":\"1\",\"player_width\":\"400\",\"player_height\":\"300\",\"embedshare\":\"TRUE\",\"backcolor\":\"0x287585\",\"frontcolor\":\"0xFFFFFF\",\"lightcolor\":\"0x000000\",\"screencolor\":\"0x000000\",\"popuptitle\":\"{{title}}\",\"popupfooter\":\"{{filename}}\",\"popupmargin\":\"50\",\"popupbackground\":\"black\",\"popupimage\":\"media\\/com_biblestudy\\/images\\/speaker24.png\",\"show_filesize\":\"1\",\"playerposition\":\"over\",\"playeridlehide\":\"1\",\"default_order\":\"DESC\",\"default_order_secondary\":\"ASC\",\"show_page_title\":\"1\",\"show_page_image\":\"1\",\"list_page_title\":\"Bible Studies\",\"list_title_align\":\"text-align:center\",\"use_headers_list\":\"1\",\"studies_element\":\"1\",\"list_intro\":\"\",\"intro_show\":\"1\",\"list_teacher_show\":\"1\",\"listteachers\":[],\"teacherlink\":\"1\",\"showpodcastsubscribelist\":\"1\",\"subscribeintro\":\"Our Podcasts\",\"details_text\":\"Study Details\",\"show_book_search\":\"1\",\"ddbooks\":\"1\",\"booklist\":\"1\",\"use_go_button\":\"1\",\"ddgobutton\":\"2\",\"show_teacher_search\":\"1\",\"ddteachers\":\"3\",\"show_series_search\":\"1\",\"ddseries\":\"4\",\"show_type_search\":\"1\",\"ddmessagetype\":\"5\",\"show_year_search\":\"1\",\"ddyears\":\"6\",\"show_order_search\":\"1\",\"ddorder\":\"7\",\"show_topic_search\":\"1\",\"ddtopics\":\"8\",\"show_locations_search\":\"1\",\"ddlocations\":\"9\",\"show_popular\":\"1\",\"ddpopular\":\"10\",\"listlanguage\":\"0\",\"ddlanguage\":\"11\",\"show_pagination\":\"1\",\"listcolor1\":\"#8f8fb2\",\"listcolor2\":\"#ccccff\",\"rowspanitem\":\"1\",\"rowspanitemspan\":\"2\",\"rowspanitemimage\":\"img-polaroid\",\"rowspanitempull\":\"pull-left\",\"scripture1row\":\"0\",\"scripture1col\":\"3\",\"scripture1colspan\":\"2\",\"scripture1element\":\"1\",\"scripture1custom\":\"\",\"scripture1linktype\":\"0\",\"scripture2row\":\"0\",\"scripture2col\":\"1\",\"scripture2colspan\":\"3\",\"scripture2element\":\"1\",\"scripture2custom\":\"\",\"scripture2linktype\":\"0\",\"secondaryrow\":\"0\",\"secondarycol\":\"1\",\"secondarycolspan\":\"1\",\"secondaryelement\":\"1\",\"secondarycustom\":\"\",\"secondarylinktype\":\"0\",\"jbsmediarow\":\"1\",\"jbsmediacol\":\"4\",\"jbsmediacolspan\":\"4\",\"jbsmediaelement\":\"0\",\"jbsmediacustom\":\"\",\"jbsmedialinktype\":\"2\",\"titlerow\":\"1\",\"titlecol\":\"2\",\"titlecolspan\":\"4\",\"titleelement\":\"1\",\"titlecustom\":\"\",\"titlelinktype\":\"0\",\"daterow\":\"1\",\"datecol\":\"1\",\"datecolspan\":\"2\",\"dateelement\":\"1\",\"datecustom\":\"\",\"datelinktype\":\"0\",\"teacherrow\":\"0\",\"teachercol\":\"1\",\"teachercolspan\":\"1\",\"teacherelement\":\"1\",\"teachercustom\":\"\",\"teacherlinktype\":\"0\",\"teacherimagerrow\":\"0\",\"teacherimagecol\":\"1\",\"teacherimagecolspan\":\"1\",\"teacherimageelement\":\"1\",\"teacherimagecustom\":\"\",\"teacher-titlerow\":\"0\",\"teacher-titlecol\":\"1\",\"teacher-titlecolspan\":\"1\",\"teacher-titleelement\":\"1\",\"teacher-titlecustom\":\"\",\"teacher-titlelinktype\":\"0\",\"durationrow\":\"0\",\"durationcol\":\"1\",\"durationcolspan\":\"1\",\"durationelement\":\"1\",\"durationcustom\":\"\",\"durationlinktype\":\"0\",\"studyintrorow\":\"0\",\"studyintrocol\":\"1\",\"studyintrocolspan\":\"12\",\"studyintroelement\":\"1\",\"studyintrocustom\":\"\",\"studyintrolinktype\":\"0\",\"seriesrow\":\"0\",\"seriescol\":\"1\",\"seriescolspan\":\"1\",\"serieselement\":\"1\",\"seriescustom\":\"\",\"serieslinktype\":\"0\",\"seriesthumbnailrow\":\"0\",\"seriesthumbnailcol\":\"1\",\"seriesthumbnailcolspan\":\"1\",\"seriesthumbnailelement\":\"1\",\"seriesthumbnailcustom\":\"\",\"seriesthumbnaillinktype\":\"0\",\"seriesdescriptionrow\":\"0\",\"seriesdescriptioncol\":\"1\",\"seriesdescriptioncolspan\":\"1\",\"seriesdescriptionelement\":\"1\",\"seriesdescriptioncustom\":\"\",\"seriesdescriptionlinktype\":\"0\",\"submittedrow\":\"0\",\"submittedcol\":\"1\",\"submittedcolspan\":\"1\",\"submittedelement\":\"1\",\"submittedcustom\":\"\",\"submittedlinktype\":\"0\",\"hitsrow\":\"0\",\"hitscol\":\"1\",\"hitscolspan\":\"6\",\"hitselement\":\"1\",\"hitscustom\":\"\",\"hitslinktype\":\"0\",\"downloadsrow\":\"0\",\"downloadscol\":\"1\",\"downloadscolspan\":\"1\",\"downloadselement\":\"1\",\"downloadscustom\":\"\",\"downloadslinktype\":\"0\",\"studynumberrow\":\"0\",\"studynumbercol\":\"1\",\"studynumbercolspan\":\"1\",\"studynumberelement\":\"1\",\"studynumbercustom\":\"\",\"studynumberlinktype\":\"0\",\"topicrow\":\"0\",\"topiccol\":\"1\",\"topiccolspan\":\"6\",\"topicelement\":\"1\",\"topiccustom\":\"\",\"topiclinktype\":\"0\",\"locationsrow\":\"0\",\"locationscol\":\"1\",\"locationscolspan\":\"1\",\"locationselement\":\"1\",\"locationscustom\":\"\",\"locationslinktype\":\"0\",\"messagetyperow\":\"0\",\"messagetypecol\":\"1\",\"messagetypecolspan\":\"6\",\"messagetypeelement\":\"1\",\"messagetypecustom\":\"\",\"messagetypelinktype\":\"0\",\"thumbnailrow\":\"0\",\"thumbnailcol\":\"1\",\"thumbnailcolspan\":\"1\",\"thumbnailelement\":\"1\",\"thumbnailcustom\":\"\",\"thumbnaillinktype\":\"0\",\"customrow\":\"0\",\"customcol\":\"1\",\"customcolspan\":\"1\",\"customelement\":\"1\",\"customcustom\":\"\",\"customtext\":\"\",\"show_print_view\":\"1\",\"link_text\":\"Return to Studies List\",\"showrelated\":\"1\",\"showpodcastsubscribedetails\":\"1\",\"show_scripture_link\":\"0\",\"show_passage_view\":\"1\",\"bible_version\":\"51\",\"socialnetworking\":\"1\",\"sharetype\":\"1\",\"sharelabel\":\"Share This\",\"comments_type\":\"0\",\"show_comments\":\"1\",\"link_comments\":\"0\",\"comment_access\":\"1\",\"comment_publish\":\"0\",\"use_captcha\":\"1\",\"public_key\":\"\",\"private_key\":\"\",\"email_comments\":\"1\",\"recipient\":\"\",\"subject\":\"Comments on studies\",\"body\":\"Comments entered.\",\"study_detailtemplate\":\"\",\"teacher_title\":\"Our Teachers\",\"teachers_element\":\"1\",\"tsrowspanitem\":\"0\",\"tsrowspanitemspan\":\"4\",\"tsrowspanitemimage\":\"img-polaroid\",\"tsrowspanitempull\":\"pull-left\",\"use_headers_teacher_list\":\"1\",\"tslistcolor1\":\"\",\"tslistcolor2\":\"\",\"tsteacherrow\":\"1\",\"tsteachercol\":\"1\",\"tsteachercolspan\":\"2\",\"tsteacherelement\":\"1\",\"tsteachercustom\":\"\",\"tsteacherlinktype\":\"0\",\"tsteacherimagerrow\":\"0\",\"tsteacherimagecol\":\"1\",\"tsteacherimagecolspan\":\"1\",\"tsteacherimageelement\":\"1\",\"tsteacherimagecustom\":\"\",\"tsteacher-titlerow\":\"0\",\"tsteacher-titlecol\":\"1\",\"tsteacher-titlecolspan\":\"1\",\"tsteacher-titleelement\":\"1\",\"tsteacher-titlecustom\":\"\",\"tsteacher-titlelinktype\":\"0\",\"tsteacheremailrow\":\"0\",\"tsteacheremailcol\":\"1\",\"tsteacheremailcolspan\":\"1\",\"tsteacheremailelement\":\"1\",\"tsteacheremailcustom\":\"\",\"tsteacherwebrow\":\"0\",\"tsteacherwebcol\":\"1\",\"tsteacherwebcolspan\":\"1\",\"tsteacherwebelement\":\"1\",\"tsteacherphonerow\":\"0\",\"tsteacherphonecol\":\"1\",\"tsteacherphonecolspan\":\"1\",\"tsteacherphoneelement\":\"1\",\"tsteacherphonecustom\":\"\",\"tsteacherfbrow\":\"0\",\"tsteacherfbcol\":\"1\",\"tsteacherfbcolspan\":\"1\",\"tsteacherfbelement\":\"1\",\"tsteacherfbcustom\":\"\",\"tsteachertwrow\":\"0\",\"tsteachertwcol\":\"1\",\"tsteachertwcolspan\":\"1\",\"tsteachertwelement\":\"1\",\"tsteachertwcustom\":\"\",\"tsteacherblogrow\":\"0\",\"tsteacherblogcol\":\"1\",\"tsteacherblogcolspan\":\"1\",\"tsteacherblogelement\":\"1\",\"tsteacherblogcustom\":\"\",\"tsteachershortrow\":\"0\",\"tsteachershortcol\":\"1\",\"tsteachershortcolspan\":\"1\",\"tsteachershortelement\":\"1\",\"tsteachershortcustom\":\"\",\"tsteachershortlinktype\":\"0\",\"tscustomrow\":\"\",\"tscustomcol\":\"1\",\"tscustomcolspan\":\"1\",\"tscustomelement\":\"1\",\"tscustomcustom\":\"\",\"tscustomtext\":\"\",\"tsteacherallinonerow\":\"0\",\"tsteacherallinonecol\":\"1\",\"tsteacherallinonecolspan\":\"1\",\"tsteacherallinoneelement\":\"1\",\"tsteacherallinonecustom\":\"\",\"teacher_headercode\":\"\",\"teacher_templatecode\":\"           {{teacher}}     {{title}}     {{teacher}}           {{short}}     {{information}}       \",\"teacher_wrapcode\":\"0\",\"show_teacher_studies\":\"0\",\"studies\":\"\",\"label_teacher\":\"Latest Messages\",\"teacherlinkstudies\":\"1\",\"tdrowspanitem\":\"0\",\"tdrowspanitemspan\":\"4\",\"tdrowspanitemimage\":\"img-polaroid\",\"tdrowspanitempull\":\"pull-left\",\"use_headers_teacher_details\":\"1\",\"teacherdisplay_color\":\"\",\"tdteacherrow\":\"1\",\"tdteachercol\":\"1\",\"tdteachercolspan\":\"2\",\"tdteacherelement\":\"1\",\"tdteachercustom\":\"\",\"tdteacherimagerrow\":\"0\",\"tdteacherimagecol\":\"1\",\"tdteacherimagecolspan\":\"1\",\"tdteacherimageelement\":\"1\",\"tdteacherimagecustom\":\"\",\"tdteacher-titlerow\":\"0\",\"tdteacher-titlecol\":\"1\",\"tdteacher-titlecolspan\":\"1\",\"tdteacher-titleelement\":\"1\",\"tdteacher-titlecustom\":\"\",\"tdteacheremailrow\":\"0\",\"tdteacheremailcol\":\"1\",\"tdteacheremailcolspan\":\"1\",\"tdteacheremailelement\":\"1\",\"tdteacheremailcustom\":\"\",\"tdteacherwebrow\":\"0\",\"tdteacherwebcol\":\"1\",\"tdteacherwebcolspan\":\"1\",\"tdteacherwebelement\":\"1\",\"tdteacherphonerow\":\"0\",\"tdteacherphonecol\":\"1\",\"tdteacherphonecolspan\":\"1\",\"tdteacherphoneelement\":\"1\",\"tdteacherphonecustom\":\"\",\"tdteacherfbrow\":\"0\",\"tdteacherfbcol\":\"1\",\"tdteacherfbcolspan\":\"1\",\"tdteacherfbelement\":\"1\",\"tdteacherfbcustom\":\"\",\"tdteachertwrow\":\"0\",\"tdteachertwcol\":\"1\",\"tdteachertwcolspan\":\"1\",\"tdteachertwelement\":\"1\",\"tdteachertwcustom\":\"\",\"tdteacherblogrow\":\"0\",\"tdteacherblogcol\":\"1\",\"tdteacherblogcolspan\":\"1\",\"tdteacherblogelement\":\"1\",\"tdteacherblogcustom\":\"\",\"tdteachershortrow\":\"0\",\"tdteachershortcol\":\"1\",\"tdteachershortcolspan\":\"1\",\"tdteachershortelement\":\"1\",\"tdteachershortcustom\":\"\",\"tdteacherlongrow\":\"0\",\"tdteacherlongcol\":\"1\",\"tdteacherlongcolspan\":\"1\",\"tdteacherlongelement\":\"1\",\"tdteacherlongcustom\":\"\",\"tdteacheraddressrow\":\"0\",\"tdteacheraddresscol\":\"1\",\"tdteacheraddresscolspan\":\"1\",\"tdteacheraddresselement\":\"1\",\"tdteacheraddresscustom\":\"\",\"tdteacherlink1row\":\"0\",\"tdteacherlink1col\":\"1\",\"tdteacherlink1colspan\":\"1\",\"tdteacherlink1element\":\"1\",\"tdteacherlink1custom\":\"\",\"tdteacherlink2row\":\"0\",\"tdteacherlink2col\":\"1\",\"tdteacherlink2colspan\":\"1\",\"tdteacherlink2element\":\"1\",\"tdteacherlink2custom\":\"\",\"tdteacherlink3row\":\"0\",\"tdteacherlink3col\":\"1\",\"tdteacherlink3colspan\":\"1\",\"tdteacherlink3element\":\"1\",\"tdteacherlink3custom\":\"\",\"tdteacherlargeimagerow\":\"0\",\"tdteacherlargeimagecol\":\"1\",\"tdteacherlargeimagecolspan\":\"1\",\"tdteacherlargeimageelement\":\"1\",\"tdteacherlargeimagecustom\":\"\",\"tdcustomrow\":\"\",\"tdcustomcol\":\"1\",\"tdcustomcolspan\":\"1\",\"tdcustomelement\":\"1\",\"tdcustomcustom\":\"\",\"tdcustomtext\":\"\",\"tdteacherallinonerow\":\"0\",\"tdteacherallinonecol\":\"1\",\"tdteacherallinonecolspan\":\"1\",\"tdteacherallinoneelement\":\"1\",\"tdteacherallinonecustom\":\"\",\"series_title\":\"Our Series\",\"show_series_title\":\"1\",\"show_page_image_series\":\"1\",\"series_element\":\"1\",\"use_headers_series\":\"1\",\"series_show_description\":\"1\",\"series_characters\":\"\",\"search_series\":\"1\",\"series_list_teachers\":\"1\",\"series_list_years\":\"1\",\"series_list_show_pagination\":\"1\",\"series_list_order\":\"ASC\",\"series_order_field\":\"series_text\",\"srowspanitem\":\"0\",\"srowspanitemspan\":\"4\",\"srowspanitemimage\":\"img-polaroid\",\"srowspanitempull\":\"pull-left\",\"sseriesrow\":\"2\",\"sseriescol\":\"1\",\"sseriescolspan\":\"6\",\"sserieselement\":\"1\",\"sseriescustom\":\"\",\"sserieslinktype\":\"0\",\"sseriesthumbnailrow\":\"1\",\"sseriesthumbnailcol\":\"2\",\"sseriesthumbnailcolspan\":\"1\",\"sseriesthumbnailelement\":\"1\",\"sseriesthumbnailcustom\":\"\",\"sseriesthumbnaillinktype\":\"0\",\"steacherrow\":\"0\",\"steachercol\":\"1\",\"steachercolspan\":\"1\",\"steacherelement\":\"1\",\"steachercustom\":\"\",\"steacherlinktype\":\"0\",\"steacherimagerow\":\"0\",\"steacherimagecol\":\"1\",\"steacherimagecolspan\":\"1\",\"steacherimageelement\":\"1\",\"steacherimagecustom\":\"\",\"steacher-titlerow\":\"0\",\"steacher-titlecol\":\"1\",\"steacher-titlecolspan\":\"1\",\"steacher-titleelement\":\"1\",\"steacher-titlecustom\":\"\",\"steacher-titlelinktype\":\"0\",\"sdescriptionrow\":\"0\",\"sdescriptioncol\":\"1\",\"sdescriptioncolspan\":\"1\",\"sdescriptionelement\":\"1\",\"sdescriptioncustom\":\"\",\"sdescriptionlinktype\":\"0\",\"sdcustomrow\":\"0\",\"sdcustomcol\":\"1\",\"sdcustomcolspan\":\"1\",\"sdcustomelement\":\"1\",\"sdcustomcustom\":\"\",\"sdcustomtext\":\"\",\"series_detail_sort\":\"studydate\",\"series_detail_order\":\"DESC\",\"series_detail_limit\":\"\",\"series_list_return\":\"1\",\"sdrowspanitem\":\"0\",\"sdrowspanitemspan\":\"4\",\"sdrowspanitemimage\":\"img-polaroid\",\"sdrowspanitempull\":\"pull-left\",\"seriesdisplay_color\":\"\",\"use_header_seriesdisplay\":\"0\",\"sdseriesrow\":\"2\",\"sdseriescol\":\"1\",\"sdseriescolspan\":\"6\",\"sdserieselement\":\"1\",\"sdseriescustom\":\"\",\"sdserieslinktype\":\"0\",\"sdseriesthumbnailrow\":\"1\",\"sdseriesthumbnailcol\":\"2\",\"sdseriesthumbnailcolspan\":\"1\",\"sdseriesthumbnailelement\":\"1\",\"sdseriesthumbnailcustom\":\"\",\"sdseriesthumbnaillinktype\":\"0\",\"sdteacherrow\":\"0\",\"sdteachercol\":\"1\",\"sdteachercolspan\":\"1\",\"sdteacherelement\":\"1\",\"sdteachercustom\":\"\",\"sdteacherlinktype\":\"0\",\"sdteacherimagerow\":\"0\",\"sdteacherimagecol\":\"1\",\"sdteacherimagecolspan\":\"1\",\"sdteacherimageelement\":\"1\",\"sdteacherimagecustom\":\"\",\"sdteacher-titlerow\":\"0\",\"sdteacher-titlecol\":\"1\",\"sdteacher-titlecolspan\":\"1\",\"sdteacher-titleelement\":\"1\",\"sdteacher-titlecustom\":\"\",\"sdteacher-titlelinktype\":\"0\",\"sddescriptionrow\":\"0\",\"sddescriptioncol\":\"1\",\"sddescriptioncolspan\":\"1\",\"sddescriptionelement\":\"1\",\"sddescriptioncustom\":\"\",\"sddescriptionlinktype\":\"0\",\"tip_title\":\"Sermon Information\",\"tooltip\":\"1\",\"tip_item1_title\":\"Title\",\"tip_item1\":\"title\",\"tip_item2_title\":\"Details\",\"tip_item2\":\"title\",\"tip_item3_title\":\"Teacher\",\"tip_item3\":\"title\",\"tip_item4_title\":\"Reference\",\"tip_item4\":\"title\",\"tip_item5_title\":\"Date\",\"tip_item5\":\"title\",\"drowspanitem\":\"0\",\"drowspanitemspan\":\"4\",\"drowspanitemimage\":\"img-polaroid\",\"drowspanitempull\":\"pull-left\",\"dscripture1row\":\"1\",\"dscripture1col\":\"1\",\"dscripture1colspan\":\"1\",\"dscripture1element\":\"1\",\"dscripture1custom\":\"\",\"dscripture1linktype\":\"0\",\"dscripture2row\":\"0\",\"dscripture2col\":\"1\",\"dscripture2colspan\":\"1\",\"dscripture2element\":\"1\",\"dscripture2custom\":\"\",\"dscripture2linktype\":\"0\",\"dsecondaryrow\":\"0\",\"dsecondarycol\":\"1\",\"dsecondarycolspan\":\"1\",\"dsecondaryelement\":\"1\",\"dsecondarycustom\":\"\",\"dsecondarylinktype\":\"0\",\"djbsmediarow\":\"1\",\"djbsmediacol\":\"3\",\"djbsmediacolspan\":\"1\",\"djbsmediaelement\":\"1\",\"djbsmediacustom\":\"\",\"djbsmedialinktype\":\"0\",\"dcustomrow\":\"0\",\"dcustomcol\":\"1\",\"dcustomcolspan\":\"1\",\"dcustomelement\":\"1\",\"dcustomcustom\":\"\",\"dcustomtext\":\"\",\"dtitlerow\":\"1\",\"dtitlecol\":\"2\",\"dtitlecolspan\":\"3\",\"dtitleelement\":\"1\",\"dtitlecustom\":\"\",\"dtitlelinktype\":\"0\",\"ddaterow\":\"0\",\"ddatecol\":\"1\",\"ddatecolspan\":\"1\",\"ddateelement\":\"1\",\"ddatecustom\":\"\",\"ddatelinktype\":\"0\",\"dteacherrow\":\"0\",\"dteachercol\":\"1\",\"dteachercolspan\":\"1\",\"dteacherelement\":\"1\",\"dteachercustom\":\"\",\"dteacherlinktype\":\"0\",\"dteacherimagerrow\":\"0\",\"dteacherimagecol\":\"1\",\"dteacherimagecolspan\":\"1\",\"dteacherimageelement\":\"1\",\"dteacherimagecustom\":\"\",\"dteacher-titlerow\":\"0\",\"dteacher-titlecol\":\"1\",\"dteacher-titlecolspan\":\"1\",\"dteacher-titleelement\":\"1\",\"dteacher-titlecustom\":\"\",\"dteacher-titlelinktype\":\"0\",\"ddurationrow\":\"0\",\"ddurationcol\":\"1\",\"ddurationcolspan\":\"1\",\"ddurationelement\":\"1\",\"ddurationcustom\":\"\",\"ddurationlinktype\":\"0\",\"dstudyintrorow\":\"0\",\"dstudyintrocol\":\"1\",\"dstudyintrocolspan\":\"6\",\"dstudyintroelement\":\"1\",\"dstudyintrocustom\":\"\",\"dstudyintrolinktype\":\"0\",\"dseriesrow\":\"0\",\"dseriescol\":\"1\",\"dseriescolspan\":\"1\",\"dserieselement\":\"1\",\"dseriescustom\":\"\",\"dserieslinktype\":\"0\",\"dseriesthumbnailrow\":\"0\",\"dseriesthumbnailcol\":\"1\",\"dseriesthumbnailcolspan\":\"1\",\"dseriesthumbnailelement\":\"1\",\"dseriesthumbnailcustom\":\"\",\"dseriesthumbnaillinktype\":\"0\",\"dseriesdescriptionrow\":\"0\",\"dseriesdescriptioncol\":\"1\",\"dseriesdescriptioncolspan\":\"1\",\"dseriesdescriptionelement\":\"1\",\"dseriesdescriptioncustom\":\"\",\"dseriesdescriptionlinktype\":\"0\",\"dsubmittedrow\":\"0\",\"dsubmittedcol\":\"1\",\"dsubmittedcolspan\":\"1\",\"dsubmittedelement\":\"1\",\"dsubmittedcustom\":\"\",\"dsubmittedlinktype\":\"0\",\"dhitsrow\":\"0\",\"dhitscol\":\"1\",\"dhitscolspan\":\"6\",\"dhitselement\":\"1\",\"dhitscustom\":\"\",\"dhitslinktype\":\"0\",\"ddownloadsrow\":\"0\",\"ddownloadscol\":\"1\",\"ddownloadscolspan\":\"1\",\"ddownloadselement\":\"1\",\"ddownloadscustom\":\"\",\"ddownloadslinktype\":\"0\",\"dstudynumberrow\":\"0\",\"dstudynumbercol\":\"1\",\"dstudynumbercolspan\":\"1\",\"dstudynumberelement\":\"1\",\"dstudynumbercustom\":\"\",\"dstudynumberlinktype\":\"0\",\"dtopicrow\":\"0\",\"dtopiccol\":\"1\",\"dtopiccolspan\":\"6\",\"dtopicelement\":\"1\",\"dtopiccustom\":\"\",\"dtopiclinktype\":\"0\",\"dlocationsrow\":\"0\",\"dlocationscol\":\"1\",\"dlocationscolspan\":\"1\",\"dlocationselement\":\"1\",\"dlocationscustom\":\"\",\"dlocationslinktype\":\"0\",\"dmessagetyperow\":\"0\",\"dmessagetypecol\":\"1\",\"dmessagetypecolspan\":\"6\",\"dmessagetypeelement\":\"1\",\"dmessagetypecustom\":\"\",\"dmessagetypelinktype\":\"0\",\"dthumbnailrow\":\"0\",\"dthumbnailcol\":\"1\",\"dthumbnailcolspan\":\"1\",\"dthumbnailelement\":\"1\",\"dthumbnailcustom\":\"\",\"dthumbnaillinktype\":\"0\",\"landing_hide\":\"0\",\"landing_default_order\":\"ASC\",\"landing_hidelabel\":\"Show\\/Hide All\",\"headingorder_1\":\"teachers\",\"headingorder_2\":\"series\",\"headingorder_3\":\"books\",\"headingorder_4\":\"topics\",\"headingorder_5\":\"locations\",\"headingorder_6\":\"messagetypes\",\"headingorder_7\":\"years\",\"showteachers\":\"1\",\"landingteachersuselimit\":\"0\",\"landingteacherslimit\":\"\",\"teacherslabel\":\"Speakers\",\"linkto\":\"1\",\"showseries\":\"1\",\"landingseriesuselimit\":\"0\",\"landingserieslimit\":\"\",\"serieslabel\":\"Series\",\"series_linkto\":\"0\",\"showbooks\":\"1\",\"landingbookslimit\":\"\",\"bookslabel\":\"Books\",\"showtopics\":\"1\",\"landingtopicslimit\":\"\",\"topicslabel\":\"Topics\",\"showlocations\":\"1\",\"landinglocationsuselimit\":\"0\",\"landinglocationslimit\":\"\",\"locationslabel\":\"Locations\",\"showmessagetypes\":\"1\",\"landingmessagetypeuselimit\":\"0\",\"landingmessagetypeslimit\":\"\",\"messagetypeslabel\":\"Message Types\",\"showyears\":\"1\",\"landingyearslimit\":\"\",\"yearslabel\":\"Years\",\"series_order\":\"2\",\"books_order\":\"2\",\"teachers_order\":\"2\",\"years_order\":\"1\",\"topics_order\":\"2\",\"locations_order\":\"2\",\"messagetypes_order\":\"2\"}',
 'Default', 'textfile24.png', 'pdf24.png', 7490, 1);

-- Dump of table #__bsms_timeset
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_timeset` (`timeset`, `backup`)
VALUES
('1281646339', '1281646339');

-- Dump of table #__bsms_topics
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_topics` (`id`, `topic_text`, `published`, `params`, `asset_id`, `language`, `access`)
VALUES
(1, 'JBS_TOP_ABORTION', 1, NULL, 7491, '*', 1),
(2, 'JBS_TOP_GODS_ACTIVITY', 1, NULL, 7492, '*', 1),
(3, 'JBS_TOP_ADDICTION', 1, NULL, 7493, '*', 1),
(4, 'JBS_TOP_AFTERLIFE', 1, NULL, 7494, '*', 1),
(5, 'JBS_TOP_APOLOGETICS', 1, NULL, 7495, '*', 1),
(6, 'JBS_TOP_GODS_ATTRIBUTES', 1, NULL, 7496, '*', 1),
(7, 'JBS_TOP_BAPTISM', 1, NULL, 7497, '*', 1),
(8, 'JBS_TOP_BASICS_OF_CHRISTIANITY', 1, NULL, 7498, '*', 1),
(9, 'JBS_TOP_BECOMING_A_CHRISTIAN', 1, NULL, 7499, '*', 1),
(10, 'JBS_TOP_BIBLE', 1, NULL, 7500, '*', 1),
(11, 'JBS_TOP_JESUS_BIRTH', 1, NULL, 7501, '*', 1),
(12, 'JBS_TOP_CHILDREN', 1, NULL, 7502, '*', 1),
(13, 'JBS_TOP_CHRIST', 1, NULL, 7503, '*', 1),
(14, 'JBS_TOP_CHRISTIAN_CHARACTER_FRUITS', 1, NULL, 7504, '*', 1),
(15, 'JBS_TOP_CHRISTIAN_VALUES', 1, NULL, 7505, '*', 1),
(16, 'JBS_TOP_CHRISTMAS_SEASON', 1, NULL, 7506, '*', 1),
(17, 'JBS_TOP_CHURCH', 1, NULL, 7507, '*', 1),
(18, 'JBS_TOP_COMMUNICATION', 1, NULL, 7508, '*', 1),
(19, 'JBS_TOP_COMMUNION___LORDS_SUPPER', 1, NULL, 7509, '*', 1),
(21, 'JBS_TOP_CREATION', 1, NULL, 7510, '*', 1),
(22, 'JBS_TOP_JESUS_CROSS_FINAL_WEEK', 1, NULL, 7511, '*', 1),
(23, 'JBS_TOP_CULTS', 1, NULL, 7512, '*', 1),
(24, 'JBS_TOP_DEATH', 1, NULL, 7513, '*', 1),
(26, 'JBS_TOP_DESCRIPTIONS_OF_GOD', 1, NULL, 7514, '*', 1),
(27, 'JBS_TOP_DISCIPLES', 1, NULL, 7515, '*', 1),
(28, 'JBS_TOP_DISCIPLESHIP', 1, NULL, 7516, '*', 1),
(29, 'JBS_TOP_JESUS_DIVINITY', 1, NULL, 7517, '*', 1),
(30, 'JBS_TOP_DIVORCE', 1, NULL, 7518, '*', 1),
(32, 'JBS_TOP_EASTER_SEASON', 1, NULL, 7519, '*', 1),
(33, 'JBS_TOP_EMOTIONS', 1, NULL, 7520, '*', 1),
(34, 'JBS_TOP_ENTERTAINMENT', 1, NULL, 7521, '*', 1),
(35, 'JBS_TOP_EVANGELISM', 1, NULL, 7522, '*', 1),
(36, 'JBS_TOP_FAITH', 1, NULL, 7523, '*', 1),
(37, 'JBS_TOP_BLENDED_FAMILY_RELATIONSHIPS', 1, NULL, 7524, '*', 1),
(39, 'JBS_TOP_FORGIVING_OTHERS', 1, NULL, 7525, '*', 1),
(40, 'JBS_TOP_GODS_FORGIVENESS', 1, NULL, 7526, '*', 1),
(41, 'JBS_TOP_FRIENDSHIP', 1, NULL, 7527, '*', 1),
(42, 'JBS_TOP_FULFILLMENT_IN_LIFE', 1, NULL, 7528, '*', 1),
(43, 'JBS_TOP_FUND_RAISING_RALLY', 1, NULL, 7529, '*', 1),
(44, 'JBS_TOP_FUNERALS', 1, NULL, 7530, '*', 1),
(45, 'JBS_TOP_GIVING', 1, NULL, 7531, '*', 1),
(46, 'JBS_TOP_GODS_WILL', 1, NULL, 7532, '*', 1),
(47, 'JBS_TOP_HARDSHIP_OF_LIFE', 1, NULL, 7533, '*', 1),
(48, 'JBS_TOP_HOLY_SPIRIT', 1, NULL, 7534, '*', 1),
(50, 'JBS_TOP_JESUS_HUMANITY', 1, NULL, 7535, '*', 1),
(52, 'JBS_TOP_KINGDOM_OF_GOD', 1, NULL, 7536, '*', 1),
(55, 'JBS_TOP_LEADERSHIP_ESSENTIALS', 1, NULL, 7537, '*', 1),
(56, 'JBS_TOP_JESUS_LIFE', 1, NULL, 7538, '*', 1),
(57, 'JBS_TOP_LOVE', 1, NULL, 7539, '*', 1),
(58, 'JBS_TOP_GODS_LOVE', 1, NULL, 7540, '*', 1),
(59, 'JBS_TOP_MARRIAGE', 1, NULL, 7541, '*', 1),
(61, 'JBS_TOP_JESUS_MIRACLES', 1, NULL, 7542, '*', 1),
(62, 'JBS_TOP_MISCONCEPTIONS_OF_CHRISTIANITY', 1, NULL, 7543, '*', 1),
(63, 'JBS_TOP_MONEY', 1, NULL, 7544, '*', 1),
(65, 'JBS_TOP_GODS_NATURE', 1, NULL, 7545, '*', 1),
(66, 'JBS_TOP_OUR_NEED_FOR_GOD', 1, NULL, 7546, '*', 1),
(69, 'JBS_TOP_PARABLES', 1, NULL, 7547, '*', 1),
(70, 'JBS_TOP_PARANORMAL', 1, NULL, 7548, '*', 1),
(71, 'JBS_TOP_PARENTING', 1, NULL, 7549, '*', 1),
(73, 'JBS_TOP_POVERTY', 1, NULL, 7550, '*', 1),
(74, 'JBS_TOP_PRAYER', 1, NULL, 7551, '*', 1),
(76, 'JBS_TOP_PROMINENT_N_T__MEN', 1, NULL, 7552, '*', 1),
(77, 'JBS_TOP_PROMINENT_N_T__WOMEN', 1, NULL, 7553, '*', 1),
(78, 'JBS_TOP_PROMINENT_O_T__MEN', 1, NULL, 7554, '*', 1),
(79, 'JBS_TOP_PROMINENT_O_T__WOMEN', 1, NULL, 7555, '*', 1),
(82, 'JBS_TOP_MESSIANIC_PROPHECIES', 1, NULL, 7556, '*', 1),
(83, 'JBS_TOP_RACISM', 1, NULL, 7557, '*', 1),
(84, 'JBS_TOP_JESUS_RESURRECTION', 1, NULL, 7558, '*', 1),
(85, 'JBS_TOP_SECOND_COMING', 1, NULL, 7559, '*', 1),
(86, 'JBS_TOP_SEXUALITY', 1, NULL, 7560, '*', 1),
(87, 'JBS_TOP_SIN', 1, NULL, 7561, '*', 1),
(88, 'JBS_TOP_SINGLENESS', 1, NULL, 7562, '*', 1),
(89, 'JBS_TOP_SMALL_GROUPS', 1, NULL, 7563, '*', 1),
(90, 'JBS_TOP_SPIRITUAL_DISCIPLINES', 1, NULL, 7564, '*', 1),
(91, 'JBS_TOP_SPIRITUAL_GIFTS', 1, NULL, 7565, '*', 1),
(92, 'JBS_TOP_SUPERNATURAL', 1, NULL, 7566, '*', 1),
(93, 'JBS_TOP_JESUS_TEACHING', 1, NULL, 7567, '*', 1),
(94, 'JBS_TOP_TEMPTATION', 1, NULL, 7568, '*', 1),
(95, 'JBS_TOP_TEN_COMMANDMENTS', 1, NULL, 7569, '*', 1),
(97, 'JBS_TOP_TRUTH', 1, NULL, 7570, '*', 1),
(98, 'JBS_TOP_TWELVE_APOSTLES', 1, NULL, 7571, '*', 1),
(100, 'JBS_TOP_WEDDINGS', 1, NULL, 7572, '*', 1),
(101, 'JBS_TOP_WORKPLACE_ISSUES', 1, NULL, 7573, '*', 1),
(102, 'JBS_TOP_WORLD_RELIGIONS', 1, NULL, 7574, '*', 1),
(103, 'JBS_TOP_FAMILY', 1, NULL, 7575, '*', 1),
(104, 'JBS_TOP_FREEDOM', 1, NULL, 7576, '*', 1),
(105, 'JBS_TOP_STEWARDSHIP', 1, NULL, 7577, '*', 1),
(106, 'JBS_TOP_WORSHIP', 1, NULL, 7578, '*', 1),
(107, 'JBS_TOP_HOLIDAYS', 1, NULL, 7579, '*', 1),
(108, 'JBS_TOP_SPECIAL_SERVICES', 1, NULL, 7580, '*', 1),
(109, 'JBS_TOP_MEN', 1, NULL, 7581, '*', 1),
(110, 'JBS_TOP_WOMEN', 1, NULL, 7582, '*', 1),
(111, 'JBS_TOP_HOT_TOPICS', 1, NULL, 7583, '*', 1),
(112, 'JBS_TOP_NARNIA', 1, NULL, 7584, '*', 1),
(113, 'JBS_TOP_DA_VINCI_CODE', 1, NULL, 7585, '*', 1),
(114, 'JBS_TOP_RAIN', 1, NULL, 7590, '*', 1);

-- --------------------------------------------------------
