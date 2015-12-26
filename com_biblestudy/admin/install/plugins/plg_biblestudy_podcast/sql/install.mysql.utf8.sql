DROP TABLE IF EXISTS `#__jbspodcast_install`;

DROP TABLE IF EXISTS `#__jbspodcast_update`;

CREATE TABLE IF NOT EXISTS `#__jbspodcast_update` (
  id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  version VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
)
  ENGINE=InnoDB
  DEFAULT CHARSET = utf8;

INSERT INTO `#__jbspodcast_update` (id, version) VALUES
(1, '7.0.0'),
(2, '7.0.1'),
(3, '7.0.2'),
(4, '7.0.3'),
(5, '7.1.0.a2'),
(6, '7.1.1'),
(7, '8.0.0');

CREATE TABLE IF NOT EXISTS `#__jbspodcast_timeset` (
  `timeset` VARCHAR(14) NOT NULL DEFAULT '',
  `backup`  VARCHAR(14) DEFAULT NULL,
  PRIMARY KEY (`timeset`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

--
-- Dumping data for table `#__jbspodcast_timeset`
--

INSERT INTO `#__jbspodcast_timeset` (`timeset`, `backup`) VALUES
('1281646339', '1281646339');
