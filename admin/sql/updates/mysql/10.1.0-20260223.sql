-- Phase 9: Add location_id to servers table for shared server pattern
-- NULL = shared server visible to all campuses
-- Specific ID = campus-restricted server
ALTER TABLE `#__bsms_servers`
    ADD COLUMN `location_id` INT(3) DEFAULT NULL AFTER `type`;

ALTER TABLE `#__bsms_servers`
    ADD KEY `idx_location_published` (`location_id`, `published`);
