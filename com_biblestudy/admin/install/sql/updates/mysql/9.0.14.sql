CREATE TABLE IF NOT EXISTS `#__bsms_update` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  version VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

INSERT INTO `#__bsms_update` (id, version) VALUES ('26', '9.0.14')
ON DUPLICATE KEY UPDATE version = '9.0.14';