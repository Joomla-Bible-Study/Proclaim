--
-- Cache actual stored size in bible_translations to avoid expensive
-- SUM(LENGTH(text)) scans on the bible_verses table at display time.
-- data_size is set once after each download and cleared on remove.
--

ALTER TABLE `#__bsms_bible_translations`
    ADD COLUMN `data_size` BIGINT UNSIGNED NOT NULL DEFAULT 0
        COMMENT 'Actual stored size in bytes (cached after download)'
        AFTER `estimated_size`;