-- Phase 8: Add location_id to series and podcast tables for multi-campus support
ALTER TABLE `#__bsms_series`
    ADD COLUMN `location_id` INT(3) DEFAULT NULL AFTER `teacher`;

ALTER TABLE `#__bsms_podcast`
    ADD COLUMN `location_id` INT(3) DEFAULT NULL AFTER `published`;

ALTER TABLE `#__bsms_series`
    ADD KEY `idx_series_location` (`location_id`);

ALTER TABLE `#__bsms_podcast`
    ADD KEY `idx_podcast_location` (`location_id`);
