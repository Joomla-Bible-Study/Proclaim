--
-- Add bible_version columns to studies table for per-scripture Bible version selection
--

ALTER TABLE `#__bsms_studies` ADD COLUMN `bible_version` VARCHAR(20) DEFAULT NULL AFTER `verse_end2`;
ALTER TABLE `#__bsms_studies` ADD COLUMN `bible_version2` VARCHAR(20) DEFAULT NULL AFTER `bible_version`;

--
-- Add estimated_size column for pre-install size display
--

ALTER TABLE `#__bsms_bible_translations` ADD COLUMN `estimated_size` INT(10) UNSIGNED NOT NULL DEFAULT 0
    COMMENT 'Estimated text size in bytes before download' AFTER `verse_count`;

--
-- Seed common Bible translations (available for local download)
--

INSERT IGNORE INTO `#__bsms_bible_translations` (`abbreviation`, `name`, `language`, `source`, `installed`, `bundled`, `estimated_size`)
VALUES
    ('kjv', 'King James Version', 'en', 'getbible', 0, 1, 4000000),
    ('akjv', 'American King James Version', 'en', 'getbible', 0, 0, 4000000),
    ('web', 'World English Bible', 'en', 'getbible', 0, 1, 4300000),
    ('asv', 'American Standard Version', 'en', 'getbible', 0, 0, 4100000),
    ('ylt', 'Young''s Literal Translation', 'en', 'getbible', 0, 0, 4000000),
    ('basicenglish', 'Bible in Basic English', 'en', 'getbible', 0, 0, 3500000),
    ('douayrheims', 'Douay-Rheims Bible', 'en', 'getbible', 0, 0, 4200000),
    ('wb', 'Webster Bible', 'en', 'getbible', 0, 0, 4000000),
    ('darby', 'Darby Translation', 'en', 'getbible', 0, 0, 4000000),
    ('vulgate', 'Vulgata Clementina', 'la', 'getbible', 0, 0, 3800000),
    ('almeida', 'Almeida Atualizada', 'pt', 'getbible', 0, 0, 4000000),
    ('luther1545', 'Luther (1545)', 'de', 'getbible', 0, 0, 4200000),
    ('ls1910', 'Louis Segond 1910', 'fr', 'getbible', 0, 0, 4100000),
    ('synodal', 'Synodal Translation', 'ru', 'getbible', 0, 0, 4500000),
    ('valera', 'Reina Valera (1909)', 'es', 'getbible', 0, 0, 4100000),
    ('karoli', 'Károli Bible', 'hu', 'getbible', 0, 0, 4000000),
    ('giovanni', 'Giovanni Diodati Bible', 'it', 'getbible', 0, 0, 4100000),
    ('cornilescu', 'Cornilescu Bible', 'ro', 'getbible', 0, 0, 3900000),
    ('korean', 'Korean Bible', 'ko', 'getbible', 0, 0, 3800000),
    ('cus', 'Chinese Union Simplified', 'zh', 'getbible', 0, 0, 2500000);

-- Fix incorrect abbreviations from earlier seed data
UPDATE `#__bsms_bible_translations` SET `abbreviation` = 'asv', `name` = 'American Standard Version' WHERE `abbreviation` = 'asvd';
UPDATE `#__bsms_bible_translations` SET `abbreviation` = 'vulgate', `name` = 'Vulgata Clementina' WHERE `abbreviation` = 'clementine';
UPDATE `#__bsms_bible_translations` SET `abbreviation` = 'luther1545', `name` = 'Luther (1545)' WHERE `abbreviation` = 'luther1912';
UPDATE `#__bsms_bible_translations` SET `abbreviation` = 'valera', `name` = 'Reina Valera (1909)' WHERE `abbreviation` = 'rv1909';
UPDATE `#__bsms_bible_translations` SET `abbreviation` = 'cus', `name` = 'Chinese Union Simplified' WHERE `abbreviation` = 'cuvs';
DELETE FROM `#__bsms_bible_translations` WHERE `abbreviation` = 'webbe';

-- Populate estimated_size for existing rows
UPDATE `#__bsms_bible_translations` SET `estimated_size` = 4000000 WHERE `estimated_size` = 0 AND `language` = 'en';
UPDATE `#__bsms_bible_translations` SET `estimated_size` = 4000000 WHERE `estimated_size` = 0 AND `language` != 'en';
