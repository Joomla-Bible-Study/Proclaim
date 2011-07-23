CREATE TABLE IF NOT EXISTS #__jbspodcast_install ( fielda int(10) );
CREATE TABLE IF NOT EXISTS `#__jbspodcast_timeset` (
	`timeset` varchar(14) NOT NULL DEFAULT '',
	`backup` varchar(14) DEFAULT NULL,
	PRIMARY KEY (`timeset`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `#__jbspodcast_timeset`
--

INSERT INTO `#__jbspodcast_timeset` (`timeset`, `backup`) VALUES 
	( '1281646339', '1281646339');