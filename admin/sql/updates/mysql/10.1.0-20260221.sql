-- Proclaim 10.1.0 - Location System Phase 1: composite indexes on #__bsms_studies
-- These indexes support the location-based filtering queries added by CwmlocationHelper.

-- Index for filtering messages by location + publish status + date (most common list query pattern)
ALTER TABLE `#__bsms_studies`
    ADD INDEX `idx_location_pub_date` (`location_id`, `published`, `studydate`);

-- Index for the hybrid security filter (location + Joomla access level)
ALTER TABLE `#__bsms_studies`
    ADD INDEX `idx_location_access` (`location_id`, `access`);
