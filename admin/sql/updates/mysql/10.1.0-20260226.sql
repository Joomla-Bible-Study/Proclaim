--
-- Platform stats: external video platform statistics
-- @since 10.1.0
--

CREATE TABLE IF NOT EXISTS `#__bsms_platform_stats` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `media_id`       INT UNSIGNED NOT NULL COMMENT 'FK to #__bsms_mediafiles',
    `server_id`      INT UNSIGNED NOT NULL COMMENT 'FK to #__bsms_servers',
    `platform`       VARCHAR(20)  NOT NULL COMMENT 'Server type: youtube, vimeo, wistia',
    `platform_id`    VARCHAR(100) NOT NULL COMMENT 'Video ID/hash on the platform',
    `view_count`     INT UNSIGNED NOT NULL DEFAULT 0,
    `play_count`     INT UNSIGNED NOT NULL DEFAULT 0,
    `like_count`     INT UNSIGNED NULL DEFAULT NULL,
    `comment_count`  INT UNSIGNED NULL DEFAULT NULL,
    `load_count`     INT UNSIGNED NULL DEFAULT NULL COMMENT 'Page loads (Wistia)',
    `hours_watched`  DECIMAL(10,2) NULL DEFAULT NULL,
    `engagement`     DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Play rate percentage',
    `synced_at`      DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_media_platform` (`media_id`, `platform`),
    KEY `idx_server` (`server_id`),
    KEY `idx_platform` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add stats_synced_at to servers (safe if already present)
ALTER TABLE `#__bsms_servers`
    ADD COLUMN `stats_synced_at` DATETIME NULL DEFAULT NULL;
