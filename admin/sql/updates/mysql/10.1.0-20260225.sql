--
-- Analytics: add series_id to raw events and monthly aggregate tables.
-- Enables tracking visits to series pages directly, and attributes all
-- message-level events (plays, downloads, page views) to their series.
--

ALTER TABLE `#__bsms_analytics_events`
    ADD COLUMN `series_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'FK #__bsms_series' AFTER `study_id`,
    ADD KEY `idx_series_created` (`series_id`, `created`);

ALTER TABLE `#__bsms_analytics_monthly`
    ADD COLUMN `series_id` INT UNSIGNED NULL DEFAULT NULL AFTER `study_id`,
    DROP INDEX `uq_aggregate`,
    ADD UNIQUE KEY `uq_aggregate` (
        `series_id`, `study_id`, `media_id`, `location_id`,
        `event_type`, `referrer_type`, `country_code`, `device_type`,
        `year`, `month`
    );
