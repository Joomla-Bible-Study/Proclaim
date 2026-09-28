SET SQL_MODE = '';

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_admin`
--

CREATE TABLE IF NOT EXISTS `#__bsms_admin`
(
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `drop_tables`  INT(3)                    DEFAULT '0',
    `params`       TEXT,
    `asset_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
    `access`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `installstate` TEXT,
    `debug`        TINYINT(3)       NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `idx_access` (`access`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_comments`
--

CREATE TABLE IF NOT EXISTS `#__bsms_comments`
(
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
    `checked_out`      INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out_time` DATETIME                  DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_state` (`published`),
    KEY `idx_access` (`access`),
    KEY `idx_checkout` (`checked_out`),
    KEY `idx_study_published` (`study_id`, `published`, `comment_date`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_locations`
--

CREATE TABLE IF NOT EXISTS `#__bsms_locations`
(
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
    KEY `idx_access` (`access`),
    KEY `idx_checkout` (`checked_out`),
    KEY `idx_published_access` (`published`, `access`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_playlists`
--

CREATE TABLE IF NOT EXISTS `#__bsms_playlists`
(
    `id`                  INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`               VARCHAR(250)              DEFAULT NULL,
    `alias`               VARCHAR(400)     NOT NULL DEFAULT '',
    `description`         MEDIUMTEXT,
    `remote_playlist_id` VARCHAR(64)      NOT NULL DEFAULT '' COMMENT 'Remote playlist ID (e.g. YouTube playlist ID)',
    `server_id`           INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to #__bsms_servers',
    `series_id`           INT(10) UNSIGNED          DEFAULT NULL COMMENT 'Optional FK to #__bsms_series',
    `default_settings`    TEXT COMMENT 'JSON of default settings applied to synced videos',
    `sync_enabled`        TINYINT(1)       NOT NULL DEFAULT '0',
    `writeback_enabled`   TINYINT(1)       NOT NULL DEFAULT '0' COMMENT 'Push local title changes back to the remote platform',
    `last_sync`           DATETIME                  DEFAULT NULL,
    `params`              TEXT             NOT NULL,
    `language`            CHAR(7)          NOT NULL DEFAULT '',
    `checked_out`         INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out_time`    DATETIME                  DEFAULT NULL,
    `created`             DATETIME                  DEFAULT NULL,
    `created_by`          INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `created_by_alias`    VARCHAR(255)     NOT NULL DEFAULT '',
    `modified`            DATETIME                  DEFAULT NULL,
    `modified_by`         INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `published`           TINYINT(3)       NOT NULL DEFAULT '1',
    `asset_id`            INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
    `access`              INT(10) UNSIGNED NOT NULL DEFAULT '1',
    `ordering`            INT(11)          NOT NULL DEFAULT '0',
    -- A remote playlist maps to one local row per server. remote_playlist_id is
    -- NOT NULL DEFAULT '' and the edit form does not require it, so a playlist
    -- created by hand in the admin carries ''. Mapping '' to NULL keeps those
    -- unconstrained, because NULLs are distinct from each other in a unique index.
    `remote_playlist_uk`  VARCHAR(64) GENERATED ALWAYS AS
        (IF(`remote_playlist_id` <> '', `remote_playlist_id`, NULL)) STORED,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_server_remote_playlist` (`server_id`, `remote_playlist_uk`),
    KEY `idx_state` (`published`),
    KEY `idx_access` (`access`),
    KEY `idx_checkout` (`checked_out`),
    KEY `idx_server` (`server_id`),
    KEY `idx_remote_playlist` (`remote_playlist_id`, `server_id`),
    KEY `idx_published_access` (`published`, `access`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_playlist_items`
--

CREATE TABLE IF NOT EXISTS `#__bsms_playlist_items`
(
    `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `playlist_id`      INT(10) UNSIGNED NOT NULL,
    `mediafile_id`     INT(10) UNSIGNED          DEFAULT NULL COMMENT 'FK to #__bsms_mediafiles; NULL when no local media yet',
    `remote_video_id` VARCHAR(32)      NOT NULL DEFAULT '' COMMENT 'Reconciliation key matched against params.filename',
    `title`            VARCHAR(400)     NOT NULL DEFAULT '',
    `position`         INT(11)          NOT NULL DEFAULT '0',
    `source`           VARCHAR(16)      NOT NULL DEFAULT 'remote' COMMENT 'remote = imported/confirmed on platform; manual = assigned via the media-file playlist field, not yet confirmed on the platform',
    `created`          DATETIME                  DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_playlist_video` (`playlist_id`, `remote_video_id`),
    KEY `idx_playlist` (`playlist_id`),
    KEY `idx_mediafile` (`mediafile_id`),
    KEY `idx_video` (`remote_video_id`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_mediafiles`
--

CREATE TABLE IF NOT EXISTS `#__bsms_mediafiles`
(
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
    `podcast_downloads` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Podcast-app downloads counted via the tracking redirect (IAB-style 24h dedupe)',
    `podcast_guid`     VARCHAR(400)              DEFAULT NULL COMMENT 'Frozen RSS <guid> for podcast items; stamped on first feed build',
    `plays`            INT(10)                   DEFAULT '0',
    `content_origin`   TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0=ministry-created, 1=external/third-party',
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
    KEY `idx_createdby` (`created_by`),
    KEY `idx_study_published` (`study_id`, `published`, `createdate`),
    KEY `idx_podcast_published` (`podcast_id`, `published`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_message_type`
--

CREATE TABLE IF NOT EXISTS `#__bsms_message_type`
(
    `id`           INT(10) UNSIGNED                                 NOT NULL AUTO_INCREMENT,
    `message_type` TEXT                                             NOT NULL,
    `alias`        VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
    `published`    TINYINT(3)                                       NOT NULL DEFAULT '1',
    `asset_id`     INT(10) UNSIGNED                                 NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
    `access`       INT(10) UNSIGNED                                 NOT NULL DEFAULT '1',
    `ordering`         INT(11)          NOT NULL DEFAULT '0',
    `landing_show`     INT(3)                    DEFAULT NULL,
    `created`          DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `created_by_alias` VARCHAR(255)     NOT NULL DEFAULT '',
    `modified`         DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out_time` DATETIME                  DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_state` (`published`),
    KEY `idx_access` (`access`),
    KEY `idx_checkout` (`checked_out`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_podcast`
--

CREATE TABLE IF NOT EXISTS `#__bsms_podcast`
(
    `id`                      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`                   VARCHAR(100)              DEFAULT NULL,
    `website`                 VARCHAR(100)              DEFAULT NULL,
    `podcastlink`             VARCHAR(100)              DEFAULT NULL,
    `description`             TEXT,
    `image`                   VARCHAR(255)              DEFAULT NULL,
    `imageh`                  INT(3)                    DEFAULT NULL,
    `imagew`                  INT(3)                    DEFAULT NULL,
    `author`                  VARCHAR(100)              DEFAULT NULL,
    `podcastimage`            VARCHAR(255)              DEFAULT NULL,
    `filename`                VARCHAR(150)              DEFAULT NULL,
    `language`                VARCHAR(10)               DEFAULT 'en-us',
    `editor_name`             VARCHAR(150)              DEFAULT NULL,
    `editor_email`            VARCHAR(150)              DEFAULT NULL,
    `copyright`               VARCHAR(255)              DEFAULT NULL COMMENT 'Rights holder for the feed copyright tag; falls back to the site name',
    `podcastlimit`            INT(5)                    DEFAULT NULL,
    `published`               TINYINT(3)       NOT NULL DEFAULT '1',
    `track_downloads`         TINYINT(1)       NOT NULL DEFAULT 1 COMMENT '1 = rewrite feed enclosures through the download-tracking redirect (on by default)',
    `location_id`             INT(3)                    DEFAULT NULL,
    `episodetitle`            INT(11)                   DEFAULT NULL,
    `custom`                  VARCHAR(200)              DEFAULT NULL,
    `detailstemplateid`       INT(11)                   DEFAULT NULL,
    `asset_id`                INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
    `access`                  INT(10) UNSIGNED NOT NULL DEFAULT '1',
    `platform_links`          TEXT                      DEFAULT NULL,
    `podcast_subscribe_show`  INT(3)                    DEFAULT NULL,
    `podcast_image_subscribe` VARCHAR(255)              DEFAULT NULL COMMENT 'The image to use for the podcast subscription image',
    `podcast_subscribe_desc`  VARCHAR(150)              DEFAULT NULL COMMENT 'Words to go below podcast subscribe image',
    `linktype`                INT(10)          NOT NULL DEFAULT '0',
    `itunes_category`         VARCHAR(100)     NOT NULL DEFAULT 'Religion & Spirituality',
    `itunes_subcategory`      VARCHAR(100)     NOT NULL DEFAULT 'Christianity',
    `itunes_explicit`         VARCHAR(5)       NOT NULL DEFAULT 'false',
    `itunes_type`             VARCHAR(10)      NOT NULL DEFAULT 'episodic',
    `funding_url`             VARCHAR(255)              DEFAULT NULL,
    `funding_text`            VARCHAR(100)              DEFAULT NULL,
    `podcast_license`         VARCHAR(255)              DEFAULT NULL,
    `podcast_license_url`     VARCHAR(255)              DEFAULT NULL,
    `podcast_publisher`       VARCHAR(150)              DEFAULT NULL,
    `podcast_txt_verify`      VARCHAR(255)              DEFAULT NULL,
    `update_frequency`        VARCHAR(20)               DEFAULT NULL,
    `created`                 DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`              INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `created_by_alias`        VARCHAR(255)     NOT NULL DEFAULT '',
    `modified`                DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by`             INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out`             INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out_time`        DATETIME                  DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_state` (`published`),
    KEY `idx_access` (`access`),
    KEY `idx_checkout` (`checked_out`),
    KEY `idx_podcast_location` (`location_id`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_series`
--

CREATE TABLE IF NOT EXISTS `#__bsms_series`
(
    `id`               INT(10) UNSIGNED                                 NOT NULL AUTO_INCREMENT,
    `series_text`      TEXT,
    `alias`            VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
    `teacher`          INT(3)                                                    DEFAULT NULL,
    `location_id`      INT(3)                                                    DEFAULT NULL,
    `description`      TEXT,
    `series_thumbnail` VARCHAR(255)                                              DEFAULT NULL,
    `image`            TEXT                                                       DEFAULT NULL,
    `published`        TINYINT(3)                                       NOT NULL DEFAULT '1',
    `asset_id`         INT(10) UNSIGNED                                 NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
    `ordering`         INT(11)                                          NOT NULL DEFAULT '0',
    `access`           INT(10) UNSIGNED                                 NOT NULL DEFAULT '1',
    `language`         CHAR(7)                                          NOT NULL COMMENT 'The language code for the Series.',
    `landing_show`     INT(3)                                                    DEFAULT NULL,
    `pc_show`          INT(3)                                           NOT NULL DEFAULT '1' COMMENT 'For displaying on
   podcasts page',
    `created`          DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED                                 NOT NULL DEFAULT '0',
    `created_by_alias` VARCHAR(255)                                     NOT NULL DEFAULT '',
    `modified`         DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by`      INT(10) UNSIGNED                                 NOT NULL DEFAULT '0',
    `publish_up`       DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `publish_down`     DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `checked_out`      INT(10) UNSIGNED                                 NOT NULL DEFAULT 0,
    `checked_out_time` DATETIME                                                  DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_state` (`published`),
    KEY `idx_access` (`access`),
    KEY `idx_checkout` (`checked_out`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_published_access` (`published`, `access`),
    KEY `idx_teacher_published` (`teacher`, `published`),
    KEY `idx_published_dates` (`published`, `publish_up`, `publish_down`),
    KEY `idx_series_location` (`location_id`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_servers`
--

CREATE TABLE IF NOT EXISTS `#__bsms_servers`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `server_name` VARCHAR(250)     NOT NULL DEFAULT '',
    `published`   TINYINT(3)       NOT NULL DEFAULT '1',
    `asset_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
    `access`      INT(10) UNSIGNED NOT NULL DEFAULT '1',
    `type`             CHAR(255)        NOT NULL,
    `location_id`      INT(3)                    DEFAULT NULL,
    `params`           TEXT             NOT NULL,
    `media`            TEXT             NOT NULL,
    `stats_synced_at`  DATETIME                  DEFAULT NULL,
    `created`          DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `created_by_alias` VARCHAR(255)     NOT NULL DEFAULT '',
    `modified`         DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out_time` DATETIME                  DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_state` (`published`),
    KEY `idx_access` (`access`),
    KEY `idx_checkout` (`checked_out`),
    KEY `idx_location_published` (`location_id`, `published`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_platform_stats`
--

CREATE TABLE IF NOT EXISTS `#__bsms_platform_stats`
(
    `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `media_id`       INT UNSIGNED  NOT NULL COMMENT 'FK to #__bsms_mediafiles',
    `server_id`      INT UNSIGNED  NOT NULL COMMENT 'FK to #__bsms_servers',
    `platform`       VARCHAR(20)   NOT NULL COMMENT 'Server type: youtube, vimeo, wistia',
    `platform_id`    VARCHAR(100)  NOT NULL COMMENT 'Video ID/hash on the platform',
    `view_count`     INT UNSIGNED  NOT NULL DEFAULT 0,
    `play_count`     INT UNSIGNED  NOT NULL DEFAULT 0,
    `like_count`     INT UNSIGNED           DEFAULT NULL,
    `comment_count`  INT UNSIGNED           DEFAULT NULL,
    `load_count`     INT UNSIGNED           DEFAULT NULL COMMENT 'Page loads (Wistia)',
    `hours_watched`  DECIMAL(10,2)          DEFAULT NULL,
    `engagement`     DECIMAL(5,2)           DEFAULT NULL COMMENT 'Play rate percentage',
    `synced_at`      DATETIME      NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_media_platform` (`media_id`, `platform`),
    KEY `idx_server` (`server_id`),
    KEY `idx_platform` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_podcast_download_log`
-- 24h dedupe log for the podcast download tracking redirect (#1281).
-- One row per (media, client-hash); a client re-counts only after 24h.
--

CREATE TABLE IF NOT EXISTS `#__bsms_podcast_download_log` (
    `media_id`    INT(10) UNSIGNED NOT NULL,
    `client_hash` CHAR(40)         NOT NULL COMMENT 'sha1(ip | user-agent)',
    `logged`      DATETIME         NOT NULL,
    PRIMARY KEY (`media_id`, `client_hash`),
    KEY `idx_logged` (`logged`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_studies`
--

CREATE TABLE IF NOT EXISTS `#__bsms_studies`
(
    `id`                  INT(10) UNSIGNED                                 NOT NULL AUTO_INCREMENT,
    `studydate`           DATETIME                                                  DEFAULT NULL,
    `studynumber`         VARCHAR(100)                                              DEFAULT '',
    `secondary_reference` TEXT,
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
    `transcript`          MEDIUMTEXT                                                DEFAULT NULL,
    `thumbnailm`          TEXT,
    `image`               TEXT                                                      DEFAULT NULL,
    `thumbhm`             INT(11)                                                   DEFAULT NULL,
    `thumbwm`             INT(11)                                                   DEFAULT NULL,
    `params`              TEXT,
    `checked_out`         INT(11) UNSIGNED                                 NOT NULL DEFAULT '0',
    `checked_out_time`    DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `published`           TINYINT(3)                                       NOT NULL DEFAULT '0',
    `publish_up`          DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `publish_down`        DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created`             DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`          INT(10) UNSIGNED                                 NOT NULL DEFAULT '0',
    `created_by_alias`    VARCHAR(255)                                     NOT NULL DEFAULT '',
    `modified`            DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by`         INT(10) UNSIGNED                                 NOT NULL DEFAULT '0',
    `asset_id`            INT(10) UNSIGNED                                 NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
    `access`              INT(10) UNSIGNED                                 NOT NULL DEFAULT '1',
    `ordering`            INT(11)                                          NOT NULL DEFAULT '0',
    `language`            CHAR(7)                                          NOT NULL COMMENT 'The language code for the Studies.',
    `download_id`         INT(10)                                          NOT NULL DEFAULT '0' COMMENT 'Used for link to download of mediafile',
    `studynumber_uk`      VARCHAR(100) GENERATED ALWAYS AS
        (IF(`series_id` > 0 AND `studynumber` <> '', `studynumber`, NULL)) STORED,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_series_studynumber` (`series_id`, `studynumber_uk`),
    KEY `idx_state` (`published`),
    KEY `idx_access` (`access`),
    KEY `idx_seriesid` (`series_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_checkout` (`checked_out`),
    KEY `idx_published_access_series` (`published`, `access`, `series_id`, `studydate`),
    KEY `idx_location_published` (`location_id`, `published`),
    KEY `idx_location_pub_date` (`location_id`, `published`, `studydate`),
    KEY `idx_location_access` (`location_id`, `access`),
    KEY `idx_published_dates` (`published`, `publish_up`, `publish_down`),
    KEY `idx_messagetype_published` (`messagetype`, `published`),
    KEY `idx_language_published` (`language`, `published`),
    FULLTEXT KEY `idx_transcript` (`transcript`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_study_scriptures`
--

CREATE TABLE IF NOT EXISTS `#__bsms_study_scriptures` (
    `id`             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `study_id`       INT(10) UNSIGNED NOT NULL,
    `ordering`       INT(3)           NOT NULL DEFAULT 0,
    `booknumber`     INT(3)           NOT NULL DEFAULT 0,
    `chapter_begin`  INT(3)           NOT NULL DEFAULT 0,
    `verse_begin`    INT(3)           NOT NULL DEFAULT 0,
    `chapter_end`    INT(3)           NOT NULL DEFAULT 0,
    `verse_end`      INT(3)           NOT NULL DEFAULT 0,
    `bible_version`  VARCHAR(20)      NOT NULL DEFAULT '',
    `reference_text` VARCHAR(255)     NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `idx_study_ordering` (`study_id`, `ordering`),
    KEY `idx_booknumber` (`booknumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_study_teachers`
--

CREATE TABLE IF NOT EXISTS `#__bsms_study_teachers` (
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `study_id`   INT(10) UNSIGNED NOT NULL,
    `teacher_id` INT(10) UNSIGNED NOT NULL,
    `ordering`   INT(3)           NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_study_teacher` (`study_id`, `teacher_id`),
    KEY `idx_teacher` (`teacher_id`),
    KEY `idx_study_ordering` (`study_id`, `ordering`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_studytopics`
--

CREATE TABLE IF NOT EXISTS `#__bsms_studytopics`
(
    `id`       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `study_id` INT(3)           NOT NULL DEFAULT '0',
    `topic_id` INT(3)           NOT NULL DEFAULT '0',
    `asset_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
    `access`   INT(10) UNSIGNED NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `idx_access` (`access`),
    KEY `idx_study` (`study_id`),
    KEY `idx_topic` (`topic_id`),
    UNIQUE KEY `uq_study_topic` (`study_id`, `topic_id`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_teachers`
--

CREATE TABLE IF NOT EXISTS `#__bsms_teachers`
(
    `id`                INT(10) UNSIGNED                                 NOT NULL AUTO_INCREMENT,
    `teacher_image`     TEXT,
    `teacher_thumbnail` TEXT,
    `teachername`       VARCHAR(250)                                     NOT NULL DEFAULT '',
    `alias`             VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
    `title`             VARCHAR(250)                                              DEFAULT NULL,
    `org_name`          VARCHAR(255)                                              DEFAULT NULL,
    `phone`             VARCHAR(50)                                               DEFAULT NULL,
    `email`             VARCHAR(100)                                              DEFAULT NULL,
    `website`           TEXT,
    `information`       TEXT,
    `image`             TEXT,
    `short`             TEXT,
    `ordering`          INT(11)                                          NOT NULL DEFAULT '0',
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
    `user_id`           INT(10) UNSIGNED                                          DEFAULT NULL,
    `address`           MEDIUMTEXT,
    `social_links`      TEXT                                                      DEFAULT NULL,
    `landing_show`      INT(3)                                                    DEFAULT NULL,
    `created`           DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`        INT(10) UNSIGNED                                 NOT NULL DEFAULT '0',
    `created_by_alias`  VARCHAR(255)                                     NOT NULL DEFAULT '',
    `modified`          DATETIME                                         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by`       INT(10) UNSIGNED                                 NOT NULL DEFAULT '0',
    `checked_out`       INT(10) UNSIGNED                                 NOT NULL DEFAULT 0,
    `checked_out_time`  DATETIME                                                  DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`alias`),
    KEY `idx_state` (`published`),
    KEY `idx_access` (`access`),
    KEY `idx_checkout` (`checked_out`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_published_access` (`published`, `access`),
    KEY `idx_teacher_user` (`user_id`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_templatecode`
--

CREATE TABLE IF NOT EXISTS `#__bsms_templatecode`
(
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `published`    TINYINT(3)       NOT NULL DEFAULT '1',
    `location_id`  INT(3)                    DEFAULT NULL,
    `type`         TINYINT(3)       NOT NULL,
    `filename`     TEXT             NOT NULL,
    `asset_id`         INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
    `created`          DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `created_by_alias` VARCHAR(255)     NOT NULL DEFAULT '',
    `modified`         DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out_time` DATETIME                  DEFAULT NULL,
    `templatecode`     MEDIUMTEXT       NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_checkout` (`checked_out`),
    KEY `idx_templatecode_location` (`location_id`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_templates`
--

CREATE TABLE IF NOT EXISTS `#__bsms_templates`
(
    `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`             VARCHAR(255)     NOT NULL,
    `published`        TINYINT(3)       NOT NULL DEFAULT '1',
    `params`           LONGTEXT,
    `title`            TEXT,
    `asset_id`         INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
    `created`          DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `created_by_alias` VARCHAR(255)     NOT NULL DEFAULT '',
    `modified`         DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out_time` DATETIME                  DEFAULT NULL,
    `access`           INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `location_id`      INT(3)                    DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_state` (`published`),
    KEY `idx_access` (`access`),
    KEY `idx_checkout` (`checked_out`),
    KEY `idx_template_location` (`location_id`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_timeset`
--

CREATE TABLE IF NOT EXISTS `#__bsms_timeset`
(
    `timeset` VARCHAR(14) NOT NULL DEFAULT '',
    `backup`  VARCHAR(14)          DEFAULT NULL,
    PRIMARY KEY (`timeset`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_topics`
--

CREATE TABLE IF NOT EXISTS `#__bsms_topics`
(
    `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `topic_text`       TEXT,
    `published`        TINYINT(3)       NOT NULL DEFAULT '1',
    `params`           VARCHAR(511)              DEFAULT NULL,
    `created`          DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `created_by_alias` VARCHAR(255)     NOT NULL DEFAULT '',
    `modified`         DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out_time` DATETIME                  DEFAULT NULL,
    `asset_id`         INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
    `language`         CHAR(7)                   DEFAULT '*',
    `access`           INT(10) UNSIGNED NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `idx_state` (`published`),
    KEY `idx_access` (`access`),
    KEY `idx_checkout` (`checked_out`),
    KEY `idx_published_access` (`published`, `access`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- Dump of table #__bsms_admin
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_admin` (`id`, `drop_tables`, `params`, `asset_id`, `access`, `installstate`, `debug`)
VALUES (1, 0,
        '{\"simple_mode\":\"1\",\"simple_mode_template\":\"simple_mode1\",\"metakey\":\"\",\"metadesc\":\"\",\"compat_mode\":\"0\",\"studylistlimit\":\"10\",\"show_location_media\":\"0\",\"popular_limit\":\"\",\"character_filter\":\"1\",\"format_popular\":\"0\",\"default_main_image\":\"\",\"default_series_image\":\"\",\"default_teacher_image\":\"\",\"default_download_image\":\"\",\"default_showHide_image\":\"\",\"thumbnail_teacher_size\":\"150\",\"thumbnail_series_size\":\"150\",\"thumbnail_study_size\":\"150\",\"location_id\":\"-1\",\"teacher_id\":\"-1\",\"series_id\":\"-1\",\"booknumber\":\"-1\",\"messagetype\":\"-1\",\"default_study_image\":\"\",\"download\":\"1\",\"target\":\" \",\"server\":\"1\",\"podcast\":[\"-1\"],\"from\":\"x\",\"to\":\"x\",\"pFrom\":\"x\",\"pTo\":\"x\",\"controls\":\"1\",\"playlist\":\"false\",\"sharing\":\"false\",\"rtmp\":\"Comming Soon\",\"ga\":\"Comming Soon\",\"captions\":\"false\"}',
        7587, 1, NULL, 0);

-- Dump of table #__bsms_locations
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_locations` (`id`, `location_text`, `contact_id`, `address`, `suburb`, `state`, `country`,
                                        `postcode`, `telephone`, `fax`, `misc`, `image`, `email_to`, `default_con`,
                                        `checked_out`, `checked_out_time`, `params`, `user_id`, `mobile`, `webpage`,
                                        `sortname1`, `sortname2`, `sortname3`, `language`, `created`, `created_by`,
                                        `created_by_alias`, `modified`, `modified_by`, `metakey`, `metadesc`,
                                        `metadata`, `featured`, `xreference`, `version`, `hits`, `publish_up`,
                                        `publish_down`, `published`, `asset_id`, `access`, `ordering`, `landing_show`)
VALUES (1, 'My Location', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '0000-00-00 00:00:00',
        '', 0,
        '', '', '', '', '', '', '0000-00-00 00:00:00', 0, '', '0000-00-00 00:00:00', 0, '', '', '', 0, '', 1, 0,
        '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 7480, 1, 1, NULL);

-- Dump of table #__bsms_message_type
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_message_type` (`id`, `message_type`, `alias`, `published`, `asset_id`, `access`, `ordering`,
                                           `landing_show`)
VALUES (1, 'Sunday', 'sunday', 1, 7482, 1, 1, NULL);

-- Dump of table #__bsms_podcast
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_podcast` (`id`, `title`, `website`, `podcastlink`, `description`, `image`, `imageh`, `imagew`, `author`, `podcastimage`, `filename`, `language`, `editor_name`, `editor_email`, `podcastlimit`, `published`, `episodetitle`, `custom`, `detailstemplateid`, `asset_id`, `access`, `podcast_subscribe_show`, `podcast_image_subscribe`, `podcast_subscribe_desc`, `linktype`)
VALUES (1, 'My Podcast', 'www.mywebsite.com', 'www.mywebsite.com/PodcastController.php', 'Podcast Description goes here', 'www.mywebsite.com/myimage.jpg', 30, 30, 'Pastor Billy', 'www.mywebsite.com/myimage.jpg', 'mypodcast.xml', '*', 'Jim Editor', 'jim@mywebsite.com', 50, 1, 0, '', 1, 7483, 1, 0, '', '', 0);

-- Dump of table #__bsms_servers
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_servers` (`id`, `server_name`, `published`, `asset_id`, `access`, `type`, `params`, `media`)
VALUES (1, 'Legacy MP3', 1, 7478, 1, 'legacy',
        '{\"path\":\"\\/\\/www.calvarychapelnewberg.net\\/\",\"protocol\":\"http:\\/\\/\"}',
        '{\"link_type\":\"1\",\"player\":\"7\",\"popup\":\"3\",\"mediacode\":\"\",\"media_image\":\"images\\/biblestudy\\/mp3.png\",\"mime_type\":\"audio\\/mp3\",\"autostart\":\"1\"}'),
       (2, 'Legacy YouTube', 1, 7588, 1, 'legacy', '{\"path\":\"\",\"protocol\":\"http:\\/\\/\"}',
        '{\"link_type\":\"0\",\"player\":\"1\",\"popup\":\"3\",\"mediacode\":\"\",\"media_image\":\"images\\/biblestudy\\/youtube24.png\",\"mime_type\":\"video\\/mp4\",\"autostart\":\"1\"}'),
       (3, 'Legacy PDF', 1, 7589, 1, 'legacy',
        '{\"path\":\"http:\\/\\/calvarynewberg.org\\/\",\"protocol\":\"http:\\/\\/\"}',
        '{\"link_type\":\"1\",\"player\":\"0\",\"popup\":\"2\",\"mediacode\":\"\",\"media_image\":\"images\\/biblestudy\\/pdf16.png\",\"mime_type\":\"application\\/pdf\",\"autostart\":\"1\"}');


-- Dump of table #__bsms_templates
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_templates` (`id`, `type`, `published`, `params`, `title`, `asset_id`,
                                        `access`)
VALUES (1, 'tmplList', 1,
        '{\"useterms\":\"0\",\"terms\":\"\",\"studieslisttemplateid\":\"1\",\"sermonstemplate\":\"0\",\"detailstemplateid\":\"1\",\"sermontemplate\":\"0\",\"teachertemplateid\":\"1\",\"teachertemplate\":\"0\",\"teacherstemplate\":\"0\",\"serieslisttemplateid\":\"1\",\"seriesdisplaystemplate\":\"0\",\"seriesdetailtemplateid\":\"1\",\"seriesdisplaytemplate\":\"0\",\"offset\":\"false\",\"teacher_id\":[\"-1\"],\"series_id\":[\"-1\"],\"booknumber\":[\"-1\"],\"topic_id\":[\"-1\"],\"messagetype\":[\"-1\"],\"locations\":[\"-1\"],\"show_verses\":\"0\",\"stylesheet\":\"\",\"date_format\":\"2\",\"custom_date_format\":\"\",\"duration_type\":\"2\",\"protocol\":\"http:\\/\\/\",\"player\":\"0\",\"popuptype\":\"window\",\"internal_popup\":\"1\",\"special\":\"_blank\",\"autostart\":\"1\",\"playerresposive\":\"1\",\"player_width\":\"400\",\"player_height\":\"300\",\"embedshare\":\"TRUE\",\"backcolor\":\"#287585\",\"frontcolor\":\"white\",\"lightcolor\":\"black\",\"screencolor\":\"black\",\"popuptitle\":\"{{title}}\",\"popupfooter\":\"{{filename}}\",\"popupmargin\":\"50\",\"popupbackground\":\"black\",\"popupimage\":\"media\\/com_proclaim\\/images\\/speaker24.png\",\"show_filesize\":\"1\",\"playerposition\":\"over\",\"playeridlehide\":\"1\",\"default_order\":\"DESC\",\"default_order_secondary\":\"ASC\",\"show_page_title\":\"1\",\"show_page_image\":\"1\",\"list_page_title\":\"Bible Studies\",\"list_title_align\":\"text-align:center\",\"use_headers_list\":\"1\",\"studies_element\":\"1\",\"list_intro\":\"\",\"intro_show\":\"1\",\"list_teacher_show\":\"1\",\"listteachers\":[],\"teacherlink\":\"1\",\"showpodcastsubscribelist\":\"1\",\"subscribeintro\":\"Our Podcasts\",\"details_text\":\"Study Details\",\"show_book_search\":\"1\",\"ddbooks\":\"1\",\"booklist\":\"1\",\"use_go_button\":\"1\",\"ddgobutton\":\"2\",\"show_teacher_search\":\"1\",\"ddteachers\":\"3\",\"show_series_search\":\"1\",\"ddseries\":\"4\",\"show_type_search\":\"1\",\"ddmessagetype\":\"5\",\"show_year_search\":\"1\",\"ddyears\":\"6\",\"show_order_search\":\"1\",\"ddorder\":\"7\",\"show_topic_search\":\"1\",\"ddtopics\":\"8\",\"show_locations_search\":\"1\",\"ddlocations\":\"9\",\"show_popular\":\"1\",\"ddpopular\":\"10\",\"listlanguage\":\"0\",\"ddlanguage\":\"11\",\"show_pagination\":\"1\",\"listcolor1\":\"#8f8fb2\",\"listcolor2\":\"#ccccff\",\"rowspanitem\":\"0\",\"rowspanitemspan\":\"2\",\"rowspanitemimage\":\"img-polaroid\",\"rowspanitempull\":\"float-left\",\"scripture1row\":\"0\",\"scripture1col\":\"3\",\"scripture1colspan\":\"2\",\"scripture1element\":\"1\",\"scripture1custom\":\"\",\"scripture1linktype\":\"0\",\"scripture2row\":\"0\",\"scripture2col\":\"1\",\"scripture2colspan\":\"3\",\"scripture2element\":\"1\",\"scripture2custom\":\"\",\"scripture2linktype\":\"0\",\"secondaryrow\":\"0\",\"secondarycol\":\"1\",\"secondarycolspan\":\"1\",\"secondaryelement\":\"1\",\"secondarycustom\":\"\",\"secondarylinktype\":\"0\",\"jbsmediarow\":\"1\",\"jbsmediacol\":\"4\",\"jbsmediacolspan\":\"4\",\"jbsmediaelement\":\"0\",\"jbsmediacustom\":\"\",\"jbsmedialinktype\":\"2\",\"titlerow\":\"1\",\"titlecol\":\"2\",\"titlecolspan\":\"4\",\"titleelement\":\"1\",\"titlecustom\":\"\",\"titlelinktype\":\"0\",\"daterow\":\"1\",\"datecol\":\"1\",\"datecolspan\":\"2\",\"dateelement\":\"1\",\"datecustom\":\"\",\"datelinktype\":\"0\",\"teacherrow\":\"0\",\"teachercol\":\"1\",\"teachercolspan\":\"1\",\"teacherelement\":\"1\",\"teachercustom\":\"\",\"teacherlinktype\":\"0\",\"teacherimagerow\":\"0\",\"teacherimagecol\":\"1\",\"teacherimagecolspan\":\"1\",\"teacherimageelement\":\"1\",\"teacherimagecustom\":\"\",\"teacher-titlerow\":\"0\",\"teacher-titlecol\":\"1\",\"teacher-titlecolspan\":\"1\",\"teacher-titleelement\":\"1\",\"teacher-titlecustom\":\"\",\"teacher-titlelinktype\":\"0\",\"durationrow\":\"0\",\"durationcol\":\"1\",\"durationcolspan\":\"1\",\"durationelement\":\"1\",\"durationcustom\":\"\",\"durationlinktype\":\"0\",\"studyintrorow\":\"0\",\"studyintrocol\":\"1\",\"studyintrocolspan\":\"12\",\"studyintroelement\":\"1\",\"studyintrocustom\":\"\",\"studyintrolinktype\":\"0\",\"seriesrow\":\"0\",\"seriescol\":\"1\",\"seriescolspan\":\"1\",\"serieselement\":\"1\",\"seriescustom\":\"\",\"serieslinktype\":\"0\",\"seriesthumbnailrow\":\"0\",\"seriesthumbnailcol\":\"1\",\"seriesthumbnailcolspan\":\"1\",\"seriesthumbnailelement\":\"1\",\"seriesthumbnailcustom\":\"\",\"seriesthumbnaillinktype\":\"0\",\"seriesdescriptionrow\":\"0\",\"seriesdescriptioncol\":\"1\",\"seriesdescriptioncolspan\":\"1\",\"seriesdescriptionelement\":\"1\",\"seriesdescriptioncustom\":\"\",\"seriesdescriptionlinktype\":\"0\",\"submittedrow\":\"0\",\"submittedcol\":\"1\",\"submittedcolspan\":\"1\",\"submittedelement\":\"1\",\"submittedcustom\":\"\",\"submittedlinktype\":\"0\",\"hitsrow\":\"0\",\"hitscol\":\"1\",\"hitscolspan\":\"6\",\"hitselement\":\"1\",\"hitscustom\":\"\",\"hitslinktype\":\"0\",\"downloadsrow\":\"0\",\"downloadscol\":\"1\",\"downloadscolspan\":\"1\",\"downloadselement\":\"1\",\"downloadscustom\":\"\",\"downloadslinktype\":\"0\",\"studynumberrow\":\"0\",\"studynumbercol\":\"1\",\"studynumbercolspan\":\"1\",\"studynumberelement\":\"1\",\"studynumbercustom\":\"\",\"studynumberlinktype\":\"0\",\"topicrow\":\"0\",\"topiccol\":\"1\",\"topiccolspan\":\"6\",\"topicelement\":\"1\",\"topiccustom\":\"\",\"topiclinktype\":\"0\",\"locationsrow\":\"0\",\"locationscol\":\"1\",\"locationscolspan\":\"1\",\"locationselement\":\"1\",\"locationscustom\":\"\",\"locationslinktype\":\"0\",\"messagetyperow\":\"0\",\"messagetypecol\":\"1\",\"messagetypecolspan\":\"6\",\"messagetypeelement\":\"1\",\"messagetypecustom\":\"\",\"messagetypelinktype\":\"0\",\"thumbnailrow\":\"0\",\"thumbnailcol\":\"1\",\"thumbnailcolspan\":\"1\",\"thumbnailelement\":\"1\",\"thumbnailcustom\":\"\",\"thumbnaillinktype\":\"0\",\"customrow\":\"0\",\"customcol\":\"1\",\"customcolspan\":\"1\",\"customelement\":\"1\",\"customcustom\":\"\",\"customtext\":\"\",\"show_print_view\":\"1\",\"link_text\":\"Return to Studies List\",\"showrelated\":\"1\",\"showpodcastsubscribedetails\":\"1\",\"show_scripture_link\":\"0\",\"show_passage_view\":\"1\",\"bible_version\":\"51\",\"socialnetworking\":\"1\",\"sharetype\":\"1\",\"sharelabel\":\"Share This\",\"comments_type\":\"0\",\"show_comments\":\"1\",\"link_comments\":\"0\",\"comment_access\":\"1\",\"comment_publish\":\"0\",\"use_captcha\":\"1\",\"public_key\":\"\",\"private_key\":\"\",\"email_comments\":\"1\",\"recipient\":\"\",\"subject\":\"Comments on studies\",\"body\":\"Comments entered.\",\"study_detailtemplate\":\"\",\"teacher_title\":\"Our Teachers\",\"teachers_element\":\"1\",\"tsrowspanitem\":\"0\",\"tsrowspanitemspan\":\"4\",\"tsrowspanitemimage\":\"img-polaroid\",\"tsrowspanitempull\":\"float-left\",\"use_headers_teacher_list\":\"1\",\"tslistcolor1\":\"\",\"tslistcolor2\":\"\",\"tsteacherrow\":\"1\",\"tsteachercol\":\"1\",\"tsteachercolspan\":\"2\",\"tsteacherelement\":\"1\",\"tsteachercustom\":\"\",\"tsteacherlinktype\":\"0\",\"tsteacherimagerow\":\"0\",\"tsteacherimagecol\":\"1\",\"tsteacherimagecolspan\":\"1\",\"tsteacherimageelement\":\"1\",\"tsteacherimagecustom\":\"\",\"tsteacher-titlerow\":\"0\",\"tsteacher-titlecol\":\"1\",\"tsteacher-titlecolspan\":\"1\",\"tsteacher-titleelement\":\"1\",\"tsteacher-titlecustom\":\"\",\"tsteacher-titlelinktype\":\"0\",\"tsteacheremailrow\":\"0\",\"tsteacheremailcol\":\"1\",\"tsteacheremailcolspan\":\"1\",\"tsteacheremailelement\":\"1\",\"tsteacheremailcustom\":\"\",\"tsteacherwebrow\":\"0\",\"tsteacherwebcol\":\"1\",\"tsteacherwebcolspan\":\"1\",\"tsteacherwebelement\":\"1\",\"tsteacherphonerow\":\"0\",\"tsteacherphonecol\":\"1\",\"tsteacherphonecolspan\":\"1\",\"tsteacherphoneelement\":\"1\",\"tsteacherphonecustom\":\"\",\"tsteacherfbrow\":\"0\",\"tsteacherfbcol\":\"1\",\"tsteacherfbcolspan\":\"1\",\"tsteacherfbelement\":\"1\",\"tsteacherfbcustom\":\"\",\"tsteachertwrow\":\"0\",\"tsteachertwcol\":\"1\",\"tsteachertwcolspan\":\"1\",\"tsteachertwelement\":\"1\",\"tsteachertwcustom\":\"\",\"tsteacherblogrow\":\"0\",\"tsteacherblogcol\":\"1\",\"tsteacherblogcolspan\":\"1\",\"tsteacherblogelement\":\"1\",\"tsteacherblogcustom\":\"\",\"tsteachershortrow\":\"0\",\"tsteachershortcol\":\"1\",\"tsteachershortcolspan\":\"1\",\"tsteachershortelement\":\"1\",\"tsteachershortcustom\":\"\",\"tsteachershortlinktype\":\"0\",\"tscustomrow\":\"\",\"tscustomcol\":\"1\",\"tscustomcolspan\":\"1\",\"tscustomelement\":\"1\",\"tscustomcustom\":\"\",\"tscustomtext\":\"\",\"tsteacherallinonerow\":\"0\",\"tsteacherallinonecol\":\"1\",\"tsteacherallinonecolspan\":\"1\",\"tsteacherallinoneelement\":\"1\",\"tsteacherallinonecustom\":\"\",\"teacher_headercode\":\"\",\"teacher_templatecode\":\"           {{teacher}}     {{title}}     {{teacher}}           {{short}}     {{information}}       \",\"teacher_wrapcode\":\"0\",\"show_teacher_studies\":\"0\",\"studies\":\"\",\"label_teacher\":\"Latest Messages\",\"teacherlinkstudies\":\"1\",\"tdrowspanitem\":\"0\",\"tdrowspanitemspan\":\"4\",\"tdrowspanitemimage\":\"img-polaroid\",\"tdrowspanitempull\":\"float-left\",\"use_headers_teacher_details\":\"1\",\"teacherdisplay_color\":\"\",\"tdteacherrow\":\"1\",\"tdteachercol\":\"1\",\"tdteachercolspan\":\"2\",\"tdteacherelement\":\"1\",\"tdteachercustom\":\"\",\"tdteacherimagerow\":\"0\",\"tdteacherimagecol\":\"1\",\"tdteacherimagecolspan\":\"1\",\"tdteacherimageelement\":\"1\",\"tdteacherimagecustom\":\"\",\"tdteacher-titlerow\":\"0\",\"tdteacher-titlecol\":\"1\",\"tdteacher-titlecolspan\":\"1\",\"tdteacher-titleelement\":\"1\",\"tdteacher-titlecustom\":\"\",\"tdteacheremailrow\":\"0\",\"tdteacheremailcol\":\"1\",\"tdteacheremailcolspan\":\"1\",\"tdteacheremailelement\":\"1\",\"tdteacheremailcustom\":\"\",\"tdteacherwebrow\":\"0\",\"tdteacherwebcol\":\"1\",\"tdteacherwebcolspan\":\"1\",\"tdteacherwebelement\":\"1\",\"tdteacherphonerow\":\"0\",\"tdteacherphonecol\":\"1\",\"tdteacherphonecolspan\":\"1\",\"tdteacherphoneelement\":\"1\",\"tdteacherphonecustom\":\"\",\"tdteacherfbrow\":\"0\",\"tdteacherfbcol\":\"1\",\"tdteacherfbcolspan\":\"1\",\"tdteacherfbelement\":\"1\",\"tdteacherfbcustom\":\"\",\"tdteachertwrow\":\"0\",\"tdteachertwcol\":\"1\",\"tdteachertwcolspan\":\"1\",\"tdteachertwelement\":\"1\",\"tdteachertwcustom\":\"\",\"tdteacherblogrow\":\"0\",\"tdteacherblogcol\":\"1\",\"tdteacherblogcolspan\":\"1\",\"tdteacherblogelement\":\"1\",\"tdteacherblogcustom\":\"\",\"tdteachershortrow\":\"0\",\"tdteachershortcol\":\"1\",\"tdteachershortcolspan\":\"1\",\"tdteachershortelement\":\"1\",\"tdteachershortcustom\":\"\",\"tdteacherlongrow\":\"0\",\"tdteacherlongcol\":\"1\",\"tdteacherlongcolspan\":\"1\",\"tdteacherlongelement\":\"1\",\"tdteacherlongcustom\":\"\",\"tdteacheraddressrow\":\"0\",\"tdteacheraddresscol\":\"1\",\"tdteacheraddresscolspan\":\"1\",\"tdteacheraddresselement\":\"1\",\"tdteacheraddresscustom\":\"\",\"tdteacherlink1row\":\"0\",\"tdteacherlink1col\":\"1\",\"tdteacherlink1colspan\":\"1\",\"tdteacherlink1element\":\"1\",\"tdteacherlink1custom\":\"\",\"tdteacherlink2row\":\"0\",\"tdteacherlink2col\":\"1\",\"tdteacherlink2colspan\":\"1\",\"tdteacherlink2element\":\"1\",\"tdteacherlink2custom\":\"\",\"tdteacherlink3row\":\"0\",\"tdteacherlink3col\":\"1\",\"tdteacherlink3colspan\":\"1\",\"tdteacherlink3element\":\"1\",\"tdteacherlink3custom\":\"\",\"tdteacherlargeimagerow\":\"0\",\"tdteacherlargeimagecol\":\"1\",\"tdteacherlargeimagecolspan\":\"1\",\"tdteacherlargeimageelement\":\"1\",\"tdteacherlargeimagecustom\":\"\",\"tdcustomrow\":\"\",\"tdcustomcol\":\"1\",\"tdcustomcolspan\":\"1\",\"tdcustomelement\":\"1\",\"tdcustomcustom\":\"\",\"tdcustomtext\":\"\",\"tdteacherallinonerow\":\"0\",\"tdteacherallinonecol\":\"1\",\"tdteacherallinonecolspan\":\"1\",\"tdteacherallinoneelement\":\"1\",\"tdteacherallinonecustom\":\"\",\"series_title\":\"Our Series\",\"show_series_title\":\"1\",\"show_page_image_series\":\"1\",\"series_element\":\"1\",\"use_headers_series\":\"1\",\"series_show_description\":\"1\",\"series_characters\":\"\",\"search_series\":\"1\",\"series_list_teachers\":\"1\",\"series_list_years\":\"1\",\"series_list_show_pagination\":\"1\",\"series_list_order\":\"ASC\",\"series_order_field\":\"series_text\",\"srowspanitem\":\"0\",\"srowspanitemspan\":\"4\",\"srowspanitemimage\":\"img-polaroid\",\"srowspanitempull\":\"float-left\",\"sseriesrow\":\"2\",\"sseriescol\":\"1\",\"sseriescolspan\":\"6\",\"sserieselement\":\"1\",\"sseriescustom\":\"\",\"sserieslinktype\":\"0\",\"sseriesthumbnailrow\":\"1\",\"sseriesthumbnailcol\":\"2\",\"sseriesthumbnailcolspan\":\"1\",\"sseriesthumbnailelement\":\"1\",\"sseriesthumbnailcustom\":\"\",\"sseriesthumbnaillinktype\":\"0\",\"steacherrow\":\"0\",\"steachercol\":\"1\",\"steachercolspan\":\"1\",\"steacherelement\":\"1\",\"steachercustom\":\"\",\"steacherlinktype\":\"0\",\"steacherimagerow\":\"0\",\"steacherimagecol\":\"1\",\"steacherimagecolspan\":\"1\",\"steacherimageelement\":\"1\",\"steacherimagecustom\":\"\",\"steacher-titlerow\":\"0\",\"steacher-titlecol\":\"1\",\"steacher-titlecolspan\":\"1\",\"steacher-titleelement\":\"1\",\"steacher-titlecustom\":\"\",\"steacher-titlelinktype\":\"0\",\"sdescriptionrow\":\"0\",\"sdescriptioncol\":\"1\",\"sdescriptioncolspan\":\"1\",\"sdescriptionelement\":\"1\",\"sdescriptioncustom\":\"\",\"sdescriptionlinktype\":\"0\",\"sdcustomrow\":\"0\",\"sdcustomcol\":\"1\",\"sdcustomcolspan\":\"1\",\"sdcustomelement\":\"1\",\"sdcustomcustom\":\"\",\"sdcustomtext\":\"\",\"series_detail_sort\":\"studydate\",\"series_detail_order\":\"DESC\",\"series_detail_limit\":\"\",\"series_list_return\":\"1\",\"sdrowspanitem\":\"0\",\"sdrowspanitemspan\":\"4\",\"sdrowspanitemimage\":\"img-polaroid\",\"sdrowspanitempull\":\"float-left\",\"seriesdisplay_color\":\"\",\"use_header_seriesdisplay\":\"0\",\"sdseriesrow\":\"2\",\"sdseriescol\":\"1\",\"sdseriescolspan\":\"6\",\"sdserieselement\":\"1\",\"sdseriescustom\":\"\",\"sdserieslinktype\":\"0\",\"sdseriesthumbnailrow\":\"1\",\"sdseriesthumbnailcol\":\"2\",\"sdseriesthumbnailcolspan\":\"1\",\"sdseriesthumbnailelement\":\"1\",\"sdseriesthumbnailcustom\":\"\",\"sdseriesthumbnaillinktype\":\"0\",\"sdteacherrow\":\"0\",\"sdteachercol\":\"1\",\"sdteachercolspan\":\"1\",\"sdteacherelement\":\"1\",\"sdteachercustom\":\"\",\"sdteacherlinktype\":\"0\",\"sdteacherimagerow\":\"0\",\"sdteacherimagecol\":\"1\",\"sdteacherimagecolspan\":\"1\",\"sdteacherimageelement\":\"1\",\"sdteacherimagecustom\":\"\",\"sdteacher-titlerow\":\"0\",\"sdteacher-titlecol\":\"1\",\"sdteacher-titlecolspan\":\"1\",\"sdteacher-titleelement\":\"1\",\"sdteacher-titlecustom\":\"\",\"sdteacher-titlelinktype\":\"0\",\"sddescriptionrow\":\"0\",\"sddescriptioncol\":\"1\",\"sddescriptioncolspan\":\"1\",\"sddescriptionelement\":\"1\",\"sddescriptioncustom\":\"\",\"sddescriptionlinktype\":\"0\",\"tip_title\":\"Sermon Information\",\"tooltip\":\"1\",\"tip_item1_title\":\"Title\",\"tip_item1\":\"title\",\"tip_item2_title\":\"Details\",\"tip_item2\":\"title\",\"tip_item3_title\":\"Teacher\",\"tip_item3\":\"title\",\"tip_item4_title\":\"Reference\",\"tip_item4\":\"title\",\"tip_item5_title\":\"Date\",\"tip_item5\":\"title\",\"drowspanitem\":\"0\",\"drowspanitemspan\":\"4\",\"drowspanitemimage\":\"img-polaroid\",\"drowspanitempull\":\"float-left\",\"dscripture1row\":\"1\",\"dscripture1col\":\"1\",\"dscripture1colspan\":\"1\",\"dscripture1element\":\"1\",\"dscripture1custom\":\"\",\"dscripture1linktype\":\"0\",\"dscripture2row\":\"0\",\"dscripture2col\":\"1\",\"dscripture2colspan\":\"1\",\"dscripture2element\":\"1\",\"dscripture2custom\":\"\",\"dscripture2linktype\":\"0\",\"dsecondaryrow\":\"0\",\"dsecondarycol\":\"1\",\"dsecondarycolspan\":\"1\",\"dsecondaryelement\":\"1\",\"dsecondarycustom\":\"\",\"dsecondarylinktype\":\"0\",\"djbsmediarow\":\"1\",\"djbsmediacol\":\"3\",\"djbsmediacolspan\":\"1\",\"djbsmediaelement\":\"1\",\"djbsmediacustom\":\"\",\"djbsmedialinktype\":\"0\",\"dcustomrow\":\"0\",\"dcustomcol\":\"1\",\"dcustomcolspan\":\"1\",\"dcustomelement\":\"1\",\"dcustomcustom\":\"\",\"dcustomtext\":\"\",\"dtitlerow\":\"1\",\"dtitlecol\":\"2\",\"dtitlecolspan\":\"3\",\"dtitleelement\":\"1\",\"dtitlecustom\":\"\",\"dtitlelinktype\":\"0\",\"ddaterow\":\"0\",\"ddatecol\":\"1\",\"ddatecolspan\":\"1\",\"ddateelement\":\"1\",\"ddatecustom\":\"\",\"ddatelinktype\":\"0\",\"dteacherrow\":\"0\",\"dteachercol\":\"1\",\"dteachercolspan\":\"1\",\"dteacherelement\":\"1\",\"dteachercustom\":\"\",\"dteacherlinktype\":\"0\",\"dteacherimagerow\":\"0\",\"dteacherimagecol\":\"1\",\"dteacherimagecolspan\":\"1\",\"dteacherimageelement\":\"1\",\"dteacherimagecustom\":\"\",\"dteacher-titlerow\":\"0\",\"dteacher-titlecol\":\"1\",\"dteacher-titlecolspan\":\"1\",\"dteacher-titleelement\":\"1\",\"dteacher-titlecustom\":\"\",\"dteacher-titlelinktype\":\"0\",\"ddurationrow\":\"0\",\"ddurationcol\":\"1\",\"ddurationcolspan\":\"1\",\"ddurationelement\":\"1\",\"ddurationcustom\":\"\",\"ddurationlinktype\":\"0\",\"dstudyintrorow\":\"0\",\"dstudyintrocol\":\"1\",\"dstudyintrocolspan\":\"6\",\"dstudyintroelement\":\"1\",\"dstudyintrocustom\":\"\",\"dstudyintrolinktype\":\"0\",\"dseriesrow\":\"0\",\"dseriescol\":\"1\",\"dseriescolspan\":\"1\",\"dserieselement\":\"1\",\"dseriescustom\":\"\",\"dserieslinktype\":\"0\",\"dseriesthumbnailrow\":\"0\",\"dseriesthumbnailcol\":\"1\",\"dseriesthumbnailcolspan\":\"1\",\"dseriesthumbnailelement\":\"1\",\"dseriesthumbnailcustom\":\"\",\"dseriesthumbnaillinktype\":\"0\",\"dseriesdescriptionrow\":\"0\",\"dseriesdescriptioncol\":\"1\",\"dseriesdescriptioncolspan\":\"1\",\"dseriesdescriptionelement\":\"1\",\"dseriesdescriptioncustom\":\"\",\"dseriesdescriptionlinktype\":\"0\",\"dsubmittedrow\":\"0\",\"dsubmittedcol\":\"1\",\"dsubmittedcolspan\":\"1\",\"dsubmittedelement\":\"1\",\"dsubmittedcustom\":\"\",\"dsubmittedlinktype\":\"0\",\"dhitsrow\":\"0\",\"dhitscol\":\"1\",\"dhitscolspan\":\"6\",\"dhitselement\":\"1\",\"dhitscustom\":\"\",\"dhitslinktype\":\"0\",\"ddownloadsrow\":\"0\",\"ddownloadscol\":\"1\",\"ddownloadscolspan\":\"1\",\"ddownloadselement\":\"1\",\"ddownloadscustom\":\"\",\"ddownloadslinktype\":\"0\",\"dstudynumberrow\":\"0\",\"dstudynumbercol\":\"1\",\"dstudynumbercolspan\":\"1\",\"dstudynumberelement\":\"1\",\"dstudynumbercustom\":\"\",\"dstudynumberlinktype\":\"0\",\"dtopicrow\":\"0\",\"dtopiccol\":\"1\",\"dtopiccolspan\":\"6\",\"dtopicelement\":\"1\",\"dtopiccustom\":\"\",\"dtopiclinktype\":\"0\",\"dlocationsrow\":\"0\",\"dlocationscol\":\"1\",\"dlocationscolspan\":\"1\",\"dlocationselement\":\"1\",\"dlocationscustom\":\"\",\"dlocationslinktype\":\"0\",\"dmessagetyperow\":\"0\",\"dmessagetypecol\":\"1\",\"dmessagetypecolspan\":\"6\",\"dmessagetypeelement\":\"1\",\"dmessagetypecustom\":\"\",\"dmessagetypelinktype\":\"0\",\"dthumbnailrow\":\"0\",\"dthumbnailcol\":\"1\",\"dthumbnailcolspan\":\"1\",\"dthumbnailelement\":\"1\",\"dthumbnailcustom\":\"\",\"dthumbnaillinktype\":\"0\",\"landing_hide\":\"0\",\"landing_default_order\":\"ASC\",\"landing_hidelabel\":\"Show\\/Hide All\",\"headingorder_1\":\"teachers\",\"headingorder_2\":\"series\",\"headingorder_3\":\"books\",\"headingorder_4\":\"topics\",\"headingorder_5\":\"locations\",\"headingorder_6\":\"messagetypes\",\"headingorder_7\":\"years\",\"showteachers\":\"1\",\"landingteachersuselimit\":\"0\",\"landingteacherslimit\":\"\",\"teacherslabel\":\"Speakers\",\"linkto\":\"1\",\"showseries\":\"1\",\"landingseriesuselimit\":\"0\",\"landingserieslimit\":\"\",\"serieslabel\":\"Series\",\"series_linkto\":\"0\",\"showbooks\":\"1\",\"landingbookslimit\":\"\",\"bookslabel\":\"Books\",\"showtopics\":\"1\",\"landingtopicslimit\":\"\",\"topicslabel\":\"Topics\",\"showlocations\":\"1\",\"landinglocationsuselimit\":\"0\",\"landinglocationslimit\":\"\",\"locationslabel\":\"Locations\",\"showmessagetypes\":\"1\",\"landingmessagetypeuselimit\":\"0\",\"landingmessagetypeslimit\":\"\",\"messagetypeslabel\":\"Message Types\",\"showyears\":\"1\",\"landingyearslimit\":\"\",\"yearslabel\":\"Years\",\"series_order\":\"2\",\"books_order\":\"2\",\"teachers_order\":\"2\",\"years_order\":\"1\",\"topics_order\":\"2\",\"locations_order\":\"2\",\"messagetypes_order\":\"2\"}',
        'Default', 7490, 1);

-- Dump of table #__bsms_timeset
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_timeset` (`timeset`, `backup`)
VALUES ('1281646339', '1281646339');

-- Dump of table #__bsms_topics
-- ------------------------------------------------------------

INSERT IGNORE INTO `#__bsms_topics` (`id`, `topic_text`, `published`, `params`, `asset_id`, `language`, `access`)
VALUES (1, 'JBS_TOP_ABORTION', 1, NULL, 7491, '*', 1),
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

--
-- Table structure for table `#__bsms_bible_translations`
--

CREATE TABLE IF NOT EXISTS `#__bsms_bible_translations` (
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `abbreviation` VARCHAR(20)      NOT NULL COMMENT 'Short code e.g. kjv, web, nlt',
    `name`         VARCHAR(255)     NOT NULL COMMENT 'Full name e.g. King James Version',
    `language`     VARCHAR(10)      NOT NULL DEFAULT 'en' COMMENT 'ISO language code',
    `source`       VARCHAR(50)      NOT NULL DEFAULT 'getbible' COMMENT 'Origin: getbible, api_bible, manual',
    `provider_id`  VARCHAR(100)     DEFAULT NULL COMMENT 'Provider-specific Bible ID (e.g. api.bible UUID)',
    `installed`    TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '1 if verses are stored locally',
    `bundled`      TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '1 if shipped with the component',
    `verse_count`  INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of verses stored locally',
    `estimated_size` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Estimated text size in bytes before download',
    `data_size`    BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Actual stored size in bytes (cached after download)',
    `downloaded_at` DATETIME NULL DEFAULT NULL COMMENT 'When the translation was last downloaded/refreshed',
    `copyright`    TEXT COMMENT 'Copyright notice for this translation',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_abbreviation` (`abbreviation`),
    KEY `idx_installed` (`installed`),
    KEY `idx_language` (`language`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_bible_verses`
--

CREATE TABLE IF NOT EXISTS `#__bsms_bible_verses` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `translation` VARCHAR(20)     NOT NULL COMMENT 'FK to bible_translations.abbreviation',
    `book`        TINYINT UNSIGNED NOT NULL COMMENT 'Standard book number 1-66',
    `chapter`     SMALLINT UNSIGNED NOT NULL,
    `verse`       SMALLINT UNSIGNED NOT NULL,
    `text`        TEXT             NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_translation_book_chapter_verse` (`translation`, `book`, `chapter`, `verse`),
    KEY `idx_translation_book` (`translation`, `book`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_analytics_events`
--

CREATE TABLE IF NOT EXISTS `#__bsms_analytics_events` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `study_id`        INT UNSIGNED NULL DEFAULT NULL COMMENT 'FK #__bsms_studies',
    `series_id`       INT UNSIGNED NULL DEFAULT NULL COMMENT 'FK #__bsms_series',
    `media_id`        INT UNSIGNED NULL DEFAULT NULL COMMENT 'FK #__bsms_mediafiles',
    `location_id`     INT UNSIGNED NULL DEFAULT NULL COMMENT 'Campus from content record',
    `event_type`      ENUM('page_view','play','download','outbound_click') NOT NULL,
    `referrer_type`   ENUM('direct','organic','social','email','internal','other') NULL DEFAULT NULL,
    `referrer_url`    VARCHAR(2048) NULL DEFAULT NULL COMMENT 'Consent-required',
    `referrer_domain` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Consent-required',
    `utm_source`      VARCHAR(255) NULL DEFAULT NULL,
    `utm_medium`      VARCHAR(255) NULL DEFAULT NULL,
    `utm_campaign`    VARCHAR(255) NULL DEFAULT NULL,
    `country_code`    CHAR(2) NULL DEFAULT NULL COMMENT 'ISO from GeoLite2; IP never stored',
    `device_type`     ENUM('desktop','mobile','tablet','unknown') NULL DEFAULT NULL,
    `browser`         VARCHAR(50) NULL DEFAULT NULL,
    `os`              VARCHAR(50) NULL DEFAULT NULL,
    `language`        VARCHAR(10) NULL DEFAULT NULL,
    `is_guest`        TINYINT(1) NULL DEFAULT NULL COMMENT '0=logged in, 1=guest',
    `session_hash`    VARCHAR(64) NULL DEFAULT NULL COMMENT 'Keyed hash of session ID, rotated daily; not linkable across days; consent-required',
    `created`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_study_created`    (`study_id`, `created`),
    KEY `idx_media_created`    (`media_id`, `created`),
    KEY `idx_location_created` (`location_id`, `created`),
    KEY `idx_event_created`    (`event_type`, `created`),
    KEY `idx_series_created`   (`series_id`, `created`),
    KEY `idx_country_created`  (`country_code`, `created`),
    KEY `idx_device_created`   (`device_type`, `created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__bsms_analytics_monthly`
--

CREATE TABLE IF NOT EXISTS `#__bsms_analytics_monthly` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `study_id`      INT UNSIGNED NULL DEFAULT NULL,
    `series_id`     INT UNSIGNED NULL DEFAULT NULL,
    `media_id`      INT UNSIGNED NULL DEFAULT NULL,
    `location_id`   INT UNSIGNED NULL DEFAULT NULL,
    `event_type`    ENUM('page_view','play','download','outbound_click') NOT NULL,
    `referrer_type` ENUM('direct','organic','social','email','internal','other') NULL DEFAULT NULL,
    `country_code`  CHAR(2) NULL DEFAULT NULL,
    `device_type`   ENUM('desktop','mobile','tablet','unknown') NULL DEFAULT NULL,
    `year`          SMALLINT UNSIGNED NOT NULL,
    `month`         TINYINT UNSIGNED NOT NULL,
    `count`         INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_aggregate` (
        `series_id`, `study_id`, `media_id`, `location_id`,
        `event_type`, `referrer_type`, `country_code`, `device_type`,
        `year`, `month`
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__bsms_scripture_cache`
--

CREATE TABLE IF NOT EXISTS `#__bsms_scripture_cache` (
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `provider`    VARCHAR(50)      NOT NULL COMMENT 'Provider name: getbible, bolls, etc.',
    `translation` VARCHAR(20)      NOT NULL,
    `reference`   VARCHAR(255)     NOT NULL COMMENT 'Normalized reference string',
    `text`        MEDIUMTEXT       NOT NULL,
    `copyright`   TEXT COMMENT 'Copyright text returned by provider',
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at`  DATETIME         NOT NULL COMMENT 'Cache expiry time',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_provider_translation_reference` (`provider`, `translation`, `reference`),
    KEY `idx_expires` (`expires_at`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

--
-- Table structure for table `#__bsms_import_manifest`
--

CREATE TABLE IF NOT EXISTS `#__bsms_import_manifest` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `import_tag` VARCHAR(64)  NOT NULL COMMENT 'Identifies one import run, e.g. demo-v1',
    `entity_type` ENUM('row','file') NOT NULL,
    `table_name` VARCHAR(64)  NULL DEFAULT NULL COMMENT 'Unprefixed table name, set when entity_type=row',
    `row_id`     INT UNSIGNED NULL DEFAULT NULL COMMENT 'Set when entity_type=row',
    `file_path`  VARCHAR(512) NULL DEFAULT NULL COMMENT 'Site-root-relative path, set when entity_type=file',
    `created`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_tag_row` (`import_tag`, `table_name`, `row_id`),
    KEY `idx_import_tag` (`import_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed common translations (installed=0 means available for download, not yet local)
INSERT IGNORE INTO `#__bsms_bible_translations` (`abbreviation`, `name`, `language`, `source`, `installed`, `bundled`, `estimated_size`)
VALUES
    ('kjv', 'King James Version', 'en', 'getbible', 0, 1, 4000000),
    ('akjv', 'American King James Version', 'en', 'getbible', 0, 0, 4000000),
    ('web', 'World English Bible', 'en', 'getbible', 0, 1, 4300000),
    ('asv', 'American Standard Version', 'en', 'getbible', 0, 0, 4100000),
    ('ylt', 'Young''s Literal Translation', 'en', 'getbible', 0, 0, 4000000),
    ('basicenglish', 'Bible in Basic English', 'en', 'getbible', 0, 0, 3500000),
    ('douayrheims', 'Douay-Rheims Bible', 'en', 'getbible', 0, 0, 4200000),
    ('wb', 'Webster Bible', 'en', 'getbible', 0, 0, 4000000),
    ('darby', 'Darby Translation', 'en', 'getbible', 0, 0, 4000000),
    ('vulgate', 'Vulgata Clementina', 'la', 'getbible', 0, 0, 3800000),
    ('almeida', 'Almeida Atualizada', 'pt', 'getbible', 0, 0, 4000000),
    ('luther1545', 'Luther (1545)', 'de', 'getbible', 0, 0, 4200000),
    ('ls1910', 'Louis Segond 1910', 'fr', 'getbible', 0, 0, 4100000),
    ('synodal', 'Synodal Translation', 'ru', 'getbible', 0, 0, 4500000),
    ('valera', 'Reina Valera (1909)', 'es', 'getbible', 0, 0, 4100000),
    ('karoli', 'Károli Bible', 'hu', 'getbible', 0, 0, 4000000),
    ('giovanni', 'Giovanni Diodati Bible', 'it', 'getbible', 0, 0, 4100000),
    ('cornilescu', 'Cornilescu Bible', 'ro', 'getbible', 0, 0, 3900000),
    ('korean', 'Korean Bible', 'ko', 'getbible', 0, 0, 3800000),
    ('cus', 'Chinese Union Simplified', 'zh', 'getbible', 0, 0, 2500000);

-- Register Proclaim entity types with Joomla Action Logs
INSERT IGNORE INTO `#__action_log_config` (`type_title`, `type_alias`, `id_holder`, `title_holder`, `table_name`, `text_prefix`)
VALUES
    ('message', 'com_proclaim.message', 'id', 'studytitle', '#__bsms_studies', 'COM_PROCLAIM'),
    ('teacher', 'com_proclaim.teacher', 'id', 'teachername', '#__bsms_teachers', 'COM_PROCLAIM'),
    ('server', 'com_proclaim.server', 'id', 'server_name', '#__bsms_servers', 'COM_PROCLAIM'),
    ('podcast', 'com_proclaim.podcast', 'id', 'title', '#__bsms_podcast', 'COM_PROCLAIM'),
    ('template', 'com_proclaim.template', 'id', 'title', '#__bsms_templates', 'COM_PROCLAIM');

-- Register Proclaim extension with Joomla Action Logs
INSERT IGNORE INTO `#__action_logs_extensions` (`extension`) VALUES ('com_proclaim');
