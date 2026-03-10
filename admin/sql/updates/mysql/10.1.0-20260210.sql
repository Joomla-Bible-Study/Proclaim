--
-- Add provider_id column for API.Bible provider-specific Bible IDs
--

ALTER TABLE `#__bsms_bible_translations`
    ADD COLUMN `provider_id` VARCHAR(100) DEFAULT NULL
    COMMENT 'Provider-specific Bible ID (e.g. api.bible UUID)' AFTER `source`;
