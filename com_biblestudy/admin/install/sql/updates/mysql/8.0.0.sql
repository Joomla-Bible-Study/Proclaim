CREATE TABLE IF NOT EXISTS `#__bsms_update` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  version VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

INSERT INTO `#__bsms_update` (id, version) VALUES (17, '8.0.0')
ON DUPLICATE KEY UPDATE version= '8.0.0';

-- Moving DB to InnoDB
ALTER TABLE `#__bsms_studies` ENGINE = INNODB;
ALTER TABLE `#__bsms_teachers` ENGINE = INNODB;
ALTER TABLE `#__bsms_topics` ENGINE = INNODB;
ALTER TABLE `#__bsms_servers` ENGINE = INNODB;
ALTER TABLE `#__bsms_series` ENGINE = INNODB;
ALTER TABLE `#__bsms_message_type` ENGINE = INNODB;
ALTER TABLE `#__bsms_folders` ENGINE = INNODB;
ALTER TABLE `#__bsms_media` ENGINE = INNODB;
ALTER TABLE `#__bsms_books` ENGINE = INNODB;
ALTER TABLE `#__bsms_podcast` ENGINE = INNODB;
ALTER TABLE `#__bsms_mimetype` ENGINE = INNODB;
ALTER TABLE `#__bsms_mediafiles` ENGINE = INNODB;
ALTER TABLE `#__bsms_templates` ENGINE = INNODB;
ALTER TABLE `#__bsms_templatecode` ENGINE = INNODB;
ALTER TABLE `#__bsms_comments` ENGINE = INNODB;
ALTER TABLE `#__bsms_admin` ENGINE = INNODB;
ALTER TABLE `#__bsms_studytopics` ENGINE = INNODB;
ALTER TABLE `#__bsms_locations` ENGINE = INNODB;
ALTER TABLE `#__bsms_timeset` ENGINE = INNODB;
ALTER TABLE `#__bsms_update` ENGINE = INNODB;

ALTER TABLE `#__bsms_podcast` ADD COLUMN `episodesubtitle` INT(11) DEFAULT NULL;
ALTER TABLE `#__bsms_podcast` ADD COLUMN `customsubtitle` VARCHAR(200) DEFAULT NULL;
ALTER TABLE `#__bsms_topics` ADD COLUMN `language` CHAR(7) DEFAULT '*';

ALTER TABLE `#__bsms_studies` DROP `topics_id` ;

ALTER TABLE `#__bsms_studies` ADD COLUMN `download_id` INT(10) NOT NULL DEFAULT '0' COMMENT 'Used for link to download of mediafile';
