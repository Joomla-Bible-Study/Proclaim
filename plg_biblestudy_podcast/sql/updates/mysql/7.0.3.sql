DROP TABLE IF EXISTS `#__jbspodcast_update`;

CREATE TABLE IF NOT EXISTS `#__jbspodcast_update` (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  version VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

INSERT INTO `#__jbspodcast_update` (id,version) VALUES
(1,'7.0.0'),
(2,'7.0.1'),
(3,'7.0.2'),
(4,'7.0.3');