-- Add content_origin column to mediafiles for ministry vs external content tracking
ALTER TABLE `#__bsms_mediafiles`
    ADD COLUMN `content_origin` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
    COMMENT '0=ministry-created, 1=external/third-party'
    AFTER `plays`;

-- Remove legacy version tracking table (replaced by #__schemas)
DROP TABLE IF EXISTS `#__bsms_update`;
