-- Add transcript field to messages (#__bsms_studies) for issue #1225
-- (epic #1210 step 4): untimed plain-text transcripts get a proper home
-- separate from timed caption files. MEDIUMTEXT (16 MB) chosen over TEXT
-- (64 KB) — long sermons can produce 100k-word transcripts. FULLTEXT key
-- supports the indexed-for-search acceptance criterion.

ALTER TABLE `#__bsms_studies`
    ADD COLUMN `transcript` MEDIUMTEXT DEFAULT NULL AFTER `studytext`;

ALTER TABLE `#__bsms_studies`
    ADD FULLTEXT KEY `idx_transcript` (`transcript`);

-- Playlist entity (#1273): a first-class playlist (e.g. a YouTube playlist),
-- distinct from a Series. May optionally back a Series via series_id, but also
-- exists for cross-cutting groupings (e.g. "All Church Services").

CREATE TABLE IF NOT EXISTS `#__bsms_playlists`
(
    `id`                  INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`               VARCHAR(250)              DEFAULT NULL,
    `alias`               VARCHAR(400)     NOT NULL DEFAULT '',
    `description`         MEDIUMTEXT,
    `remote_playlist_id` VARCHAR(64)      NOT NULL DEFAULT '',
    `server_id`           INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `series_id`           INT(10) UNSIGNED          DEFAULT NULL,
    `default_settings`    TEXT,
    `sync_enabled`        TINYINT(1)       NOT NULL DEFAULT '0',
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
    `asset_id`            INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `access`              INT(10) UNSIGNED NOT NULL DEFAULT '1',
    `ordering`            INT(11)          NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `idx_state` (`published`),
    KEY `idx_access` (`access`),
    KEY `idx_checkout` (`checked_out`),
    KEY `idx_server` (`server_id`),
    KEY `idx_published_access` (`published`, `access`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- Playlist items junction (#1273 phase 2): maps a playlist to the Proclaim
-- media files it contains. remote_video_id is the reconciliation key — bulk
-- import matches it against existing #__bsms_mediafiles (params.filename) so we
-- link rather than duplicate. mediafile_id is nullable: a playlist video with no
-- local media yet is still recorded so the membership/order is preserved.

CREATE TABLE IF NOT EXISTS `#__bsms_playlist_items`
(
    `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `playlist_id`      INT(10) UNSIGNED NOT NULL,
    `mediafile_id`     INT(10) UNSIGNED          DEFAULT NULL,
    `remote_video_id` VARCHAR(32)      NOT NULL DEFAULT '',
    `title`            VARCHAR(400)     NOT NULL DEFAULT '',
    `position`         INT(11)          NOT NULL DEFAULT '0',
    `created`          DATETIME                  DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_playlist_video` (`playlist_id`, `remote_video_id`),
    KEY `idx_playlist` (`playlist_id`),
    KEY `idx_mediafile` (`mediafile_id`),
    KEY `idx_video` (`remote_video_id`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- #1273 phase 6 (OAuth write-back): per-playlist opt-in for pushing local title
-- changes back to the remote platform (e.g. YouTube playlists.update). Default 0
-- so write-back is strictly opt-in; the conflict gate keeps prior read-sync behaviour.
ALTER TABLE `#__bsms_playlists` ADD COLUMN `writeback_enabled` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Push local title changes back to the remote platform' AFTER `sync_enabled`;
