-- Add downloaded_at timestamp to track when translations were last refreshed
-- @since 10.1.0

ALTER TABLE `#__bsms_bible_translations`
    ADD COLUMN `downloaded_at` DATETIME NULL DEFAULT NULL
        COMMENT 'When the translation was last downloaded/refreshed'
        AFTER `data_size`;
