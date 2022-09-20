CREATE TABLE IF NOT EXISTS `#__bsms_update` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  version VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

INSERT INTO `#__bsms_update` (id, version) VALUES ('25', '9.0.12')
ON DUPLICATE KEY UPDATE version = '9.0.12';


ALTER TABLE `#__bsms_series` ADD COLUMN `pc_show` INT(3) NOT NULL DEFAULT '1' COMMENT 'For displaying on podcasts page' AFTER `landing_show`;
