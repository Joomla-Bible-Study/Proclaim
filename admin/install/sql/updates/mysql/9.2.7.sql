CREATE TABLE IF NOT EXISTS `#__bsms_update` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  version VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

INSERT INTO `#__bsms_update` (id, version) VALUES ('37', '9.2.7')
ON DUPLICATE KEY UPDATE version = '9.2.7';

ALTER TABLE `#__bsms_podcast` ADD `podcastlink`  VARCHAR(100) NOT NULL AFTER `website`;
ALTER TABLE `#__bsms_podcast` ADD `subtitle`  TEXT NOT NULL AFTER `description`;