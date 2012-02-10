-- Start File
CREATE TABLE IF NOT EXISTS `#__jbsbackup_update` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  version VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

INSERT INTO `#__jbspodcast_update` (id,version) VALUES(1,7.0.4);
INSERT INTO `#__jbspodcast_update` (id,version) VALUES(2,7.0.4);