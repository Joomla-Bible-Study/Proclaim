--
-- Add bible_version columns to studies table for per-scripture Bible version selection
--

ALTER TABLE `#__bsms_studies` ADD COLUMN `bible_version` VARCHAR(20) DEFAULT NULL AFTER `verse_end2`;
ALTER TABLE `#__bsms_studies` ADD COLUMN `bible_version2` VARCHAR(20) DEFAULT NULL AFTER `bible_version`;

--
-- Seed common Bible translations (available for local download)
--

INSERT IGNORE INTO `#__bsms_bible_translations` (`abbreviation`, `name`, `language`, `source`, `installed`, `bundled`)
VALUES
    ('kjv', 'King James Version', 'en', 'getbible', 0, 1),
    ('akjv', 'American King James Version', 'en', 'getbible', 0, 0),
    ('web', 'World English Bible', 'en', 'getbible', 0, 1),
    ('webbe', 'World English Bible British Edition', 'en', 'getbible', 0, 0),
    ('asvd', 'American Standard Version', 'en', 'getbible', 0, 0),
    ('ylt', 'Young''s Literal Translation', 'en', 'getbible', 0, 0),
    ('basicenglish', 'Bible in Basic English', 'en', 'getbible', 0, 0),
    ('douayrheims', 'Douay-Rheims Bible', 'en', 'getbible', 0, 0),
    ('wb', 'Webster Bible', 'en', 'getbible', 0, 0),
    ('darby', 'Darby Translation', 'en', 'getbible', 0, 0),
    ('clementine', 'Clementine Vulgate', 'la', 'getbible', 0, 0),
    ('almeida', 'João Ferreira de Almeida', 'pt', 'getbible', 0, 0),
    ('luther1912', 'Luther Bibel 1912', 'de', 'getbible', 0, 0),
    ('ls1910', 'Louis Segond 1910', 'fr', 'getbible', 0, 0),
    ('synodal', 'Synodal Translation', 'ru', 'getbible', 0, 0),
    ('rv1909', 'Reina-Valera 1909', 'es', 'getbible', 0, 0),
    ('karoli', 'Károli Bible', 'hu', 'getbible', 0, 0),
    ('giovanni', 'Giovanni Diodati Bible', 'it', 'getbible', 0, 0),
    ('cornilescu', 'Cornilescu Bible', 'ro', 'getbible', 0, 0),
    ('korean', 'Korean Bible', 'ko', 'getbible', 0, 0),
    ('cuvs', 'Chinese Union Version Simplified', 'zh', 'getbible', 0, 0);
