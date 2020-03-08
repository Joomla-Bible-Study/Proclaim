CREATE TABLE IF NOT EXISTS `#__bsms_update` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  version VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

INSERT INTO `#__bsms_update` (id, version) VALUES (16, '7.1.3')
ON DUPLICATE KEY UPDATE version= '7.1.3';

-- need to make a update media table where auto start params where 0 need to be empty
