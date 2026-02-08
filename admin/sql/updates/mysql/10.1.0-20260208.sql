--
-- Bible Translation API: New tables for local verse storage, translation tracking, and API cache
--

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_bible_translations`
--

CREATE TABLE IF NOT EXISTS `#__bsms_bible_translations`
(
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `abbreviation` VARCHAR(20)      NOT NULL COMMENT 'Short code e.g. kjv, web, nlt',
    `name`         VARCHAR(255)     NOT NULL COMMENT 'Full name e.g. King James Version',
    `language`     VARCHAR(10)      NOT NULL DEFAULT 'en' COMMENT 'ISO language code',
    `source`       VARCHAR(50)      NOT NULL DEFAULT 'getbible' COMMENT 'Origin: getbible, bolls, manual',
    `installed`    TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '1 if verses are stored locally',
    `bundled`      TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '1 if shipped with the component',
    `verse_count`  INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of verses stored locally',
    `copyright`    TEXT COMMENT 'Copyright notice for this translation',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_abbreviation` (`abbreviation`),
    KEY `idx_installed` (`installed`),
    KEY `idx_language` (`language`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_bible_verses`
--

CREATE TABLE IF NOT EXISTS `#__bsms_bible_verses`
(
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `translation` VARCHAR(20)     NOT NULL COMMENT 'FK to bible_translations.abbreviation',
    `book`        TINYINT UNSIGNED NOT NULL COMMENT 'Standard book number 1-66',
    `chapter`     SMALLINT UNSIGNED NOT NULL,
    `verse`       SMALLINT UNSIGNED NOT NULL,
    `text`        TEXT             NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_translation_book_chapter_verse` (`translation`, `book`, `chapter`, `verse`),
    KEY `idx_translation_book` (`translation`, `book`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_scripture_cache`
--

CREATE TABLE IF NOT EXISTS `#__bsms_scripture_cache`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `provider`    VARCHAR(50)      NOT NULL COMMENT 'Provider name: getbible, bolls, etc.',
    `translation` VARCHAR(20)      NOT NULL,
    `reference`   VARCHAR(255)     NOT NULL COMMENT 'Normalized reference string',
    `text`        MEDIUMTEXT       NOT NULL,
    `copyright`   TEXT COMMENT 'Copyright text returned by provider',
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at`  DATETIME         NOT NULL COMMENT 'Cache expiry time',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_provider_translation_reference` (`provider`, `translation`, `reference`),
    KEY `idx_expires` (`expires_at`)
) ENGINE InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

-- Seed common translations so the picker has options even before import
INSERT IGNORE INTO `#__bsms_bible_translations` (`abbreviation`, `name`, `language`, `source`, `installed`, `bundled`)
VALUES
    ('kjv', 'King James Version', 'en', 'getbible', 0, 1),
    ('web', 'World English Bible', 'en', 'getbible', 0, 1),
    ('asvd', 'American Standard Version', 'en', 'getbible', 0, 0),
    ('ylt', 'Young''s Literal Translation', 'en', 'getbible', 0, 0),
    ('clementine', 'Clementine Vulgate', 'la', 'getbible', 0, 0),
    ('almeida', 'João Ferreira de Almeida', 'pt', 'getbible', 0, 0),
    ('luther1912', 'Luther Bibel 1912', 'de', 'getbible', 0, 0),
    ('ls1910', 'Louis Segond 1910', 'fr', 'getbible', 0, 0),
    ('synodal', 'Synodal Translation', 'ru', 'getbible', 0, 0),
    ('rv1909', 'Reina-Valera 1909', 'es', 'getbible', 0, 0);
