-- Add platform_links column for multi-platform podcast subscription links
ALTER TABLE `#__bsms_podcast`
    ADD COLUMN `platform_links` TEXT NULL DEFAULT NULL AFTER `alternateimage`;
