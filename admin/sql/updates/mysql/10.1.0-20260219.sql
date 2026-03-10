--
-- Scheduled publishing for series: add publish_up / publish_down columns
-- and a composite index for the scheduled-publish task query.
--

ALTER TABLE `#__bsms_series` ADD COLUMN `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `modified_by`;
ALTER TABLE `#__bsms_series` ADD COLUMN `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `publish_up`;
ALTER TABLE `#__bsms_series` ADD KEY `idx_published_dates` (`published`, `publish_up`, `publish_down`);
