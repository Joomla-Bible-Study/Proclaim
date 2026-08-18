-- #1281 Podcast download tracking (tracking-redirect prefix).
-- Podcast directories expose no download analytics API, and the feed's
-- <enclosure> points straight at the live media, so podcast pulls never reach
-- Proclaim. This adds an opt-in tracking-redirect (cwmpodcast.track) that counts
-- a download, then 302-redirects to the live media URL (local OR external host).

-- Per-episode counter, incremented by the tracking-redirect endpoint.
ALTER TABLE `#__bsms_mediafiles`
    ADD COLUMN `podcast_downloads` INT(10) UNSIGNED NOT NULL DEFAULT 0
    COMMENT 'Podcast-app downloads counted via the tracking redirect (IAB-style 24h dedupe)' AFTER `downloads` /** CAN FAIL **/;

-- Frozen, URL-independent RSS <guid> for podcast items. Stamped once on the first
-- feed build with the item's then-current value (identical to what shipped before,
-- so existing subscribers never re-download), then never recomputed — a later media
-- URL or enclosure change can no longer move an episode's identity.
ALTER TABLE `#__bsms_mediafiles`
    ADD COLUMN `podcast_guid` VARCHAR(400) DEFAULT NULL
    COMMENT 'Frozen RSS <guid> for podcast items; stamped on first feed build' AFTER `podcast_downloads` /** CAN FAIL **/;

-- Per-podcast switch: when 1, the feed rewrites <enclosure> URLs through the redirect.
-- Default 1 (on) — download tracking is valuable to every ministry and safe now that
-- episode GUIDs are frozen (enclosure changes never move item identity, so no
-- re-download). Existing podcasts get 1 when this column is added; a ministry can
-- opt out per podcast (e.g. if they already run a prefix like Podtrac).
ALTER TABLE `#__bsms_podcast`
    ADD COLUMN `track_downloads` TINYINT(1) NOT NULL DEFAULT 1
    COMMENT '1 = rewrite feed enclosures through the download-tracking redirect' AFTER `published` /** CAN FAIL **/;

-- 24h dedupe log: one row per (media, client-hash). A client is counted again
-- only after 24h (IAB rolling window). Stale rows are pruned per-media on write.
CREATE TABLE IF NOT EXISTS `#__bsms_podcast_download_log` (
    `media_id`    INT(10) UNSIGNED NOT NULL,
    `client_hash` CHAR(40)         NOT NULL COMMENT 'sha1(ip | user-agent)',
    `logged`      DATETIME         NOT NULL,
    PRIMARY KEY (`media_id`, `client_hash`),
    KEY `idx_logged` (`logged`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;
