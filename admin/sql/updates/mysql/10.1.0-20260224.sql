-- Analytics: raw event log (Tier 1) — purged after configurable retention
CREATE TABLE IF NOT EXISTS `#__bsms_analytics_events` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `study_id`       INT UNSIGNED NULL DEFAULT NULL COMMENT 'FK #__bsms_studies',
    `media_id`       INT UNSIGNED NULL DEFAULT NULL COMMENT 'FK #__bsms_mediafiles',
    `location_id`    INT UNSIGNED NULL DEFAULT NULL COMMENT 'Campus from content record',
    `event_type`     ENUM('page_view','play','download','outbound_click') NOT NULL,
    `referrer_type`  ENUM('direct','organic','social','email','internal','other') NULL DEFAULT NULL,
    `referrer_url`   VARCHAR(2048) NULL DEFAULT NULL COMMENT 'Consent-required',
    `referrer_domain` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Consent-required',
    `utm_source`     VARCHAR(255) NULL DEFAULT NULL,
    `utm_medium`     VARCHAR(255) NULL DEFAULT NULL,
    `utm_campaign`   VARCHAR(255) NULL DEFAULT NULL,
    `country_code`   CHAR(2) NULL DEFAULT NULL COMMENT 'ISO from GeoLite2; IP never stored',
    `device_type`    ENUM('desktop','mobile','tablet','unknown') NULL DEFAULT NULL,
    `browser`        VARCHAR(50) NULL DEFAULT NULL,
    `os`             VARCHAR(50) NULL DEFAULT NULL,
    `language`       VARCHAR(10) NULL DEFAULT NULL,
    `is_guest`       TINYINT(1) NULL DEFAULT NULL COMMENT '0=logged in, 1=guest',
    `session_hash`   VARCHAR(64) NULL DEFAULT NULL COMMENT 'SHA-256 of session ID; consent-required',
    `created`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_study_created`    (`study_id`, `created`),
    KEY `idx_media_created`    (`media_id`, `created`),
    KEY `idx_location_created` (`location_id`, `created`),
    KEY `idx_event_created`    (`event_type`, `created`),
    KEY `idx_country_created`  (`country_code`, `created`),
    KEY `idx_device_created`   (`device_type`, `created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics: permanent monthly aggregates (Tier 2) — kept forever
CREATE TABLE IF NOT EXISTS `#__bsms_analytics_monthly` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `study_id`      INT UNSIGNED NULL DEFAULT NULL,
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
        `study_id`, `media_id`, `location_id`,
        `event_type`, `referrer_type`, `country_code`, `device_type`,
        `year`, `month`
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
