DROP TABLE IF EXISTS `#__bsms_install`;

-- Dump of table #__bsms_update
--  ------------------------------------------------------------

CREATE TABLE `#__bsms_update` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `version` VARCHAR(255)              DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

INSERT INTO `#__bsms_update` (`id`, `version`)
VALUES
  (1, '9.0.9');

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_admin`
--

CREATE TABLE `#__bsms_admin` (
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

CREATE TABLE `#__bsms_books` (
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

CREATE TABLE `#__bsms_comments` (
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

CREATE TABLE `#__bsms_locations` (
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

CREATE TABLE `#__bsms_mediafiles` (
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

CREATE TABLE `#__bsms_message_type` (
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

CREATE TABLE `#__bsms_podcast` (
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

CREATE TABLE `#__bsms_series` (
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
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_servers`
--

CREATE TABLE `#__bsms_servers` (
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

CREATE TABLE `#__bsms_studies` (
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
  `media_hours`         VARCHAR(2)                                                DEFAULT NULL,
  `media_minutes`       VARCHAR(2)                                                DEFAULT NULL,
  `media_seconds`       VARCHAR(2)                                                DEFAULT NULL,
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

CREATE TABLE `#__bsms_studytopics` (
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

CREATE TABLE `#__bsms_teachers` (
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

CREATE TABLE `#__bsms_templatecode` (
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

CREATE TABLE `#__bsms_templates` (
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

CREATE TABLE `#__bsms_timeset` (
  `timeset` VARCHAR(14) NOT NULL DEFAULT '',
  `backup`  VARCHAR(14)          DEFAULT NULL,
  PRIMARY KEY (`timeset`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_topics`
--

CREATE TABLE `#__bsms_topics` (
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

-- --------------------------------------------------------
