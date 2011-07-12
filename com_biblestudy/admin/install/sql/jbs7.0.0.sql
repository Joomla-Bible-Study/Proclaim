-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 08, 2010 at 07:34 AM
-- Server version: 5.1.37
-- PHP Version: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_admin`
--

CREATE TABLE IF NOT EXISTS `#__bsms_admin` (
	`id` int(3) NOT NULL,
	`drop_tables` int(3) DEFAULT '0',
	`params` text,
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	 PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `#__bsms_admin`
--

INSERT INTO `#__bsms_admin` (`id`, `drop_tables`, `params`, asset_id, access) VALUES
	(1, 0, '{"compat_mode":"0","admin_store":"1","studylistlimit":"10","show_location_media":"0","popular_limit":"","character_filter":"1","format_popular":"0","socialnetworking":"1","sharetype":"1","default_main_image":"","default_series_image":"","default_teacher_image":"","default_download_image":"","default_showHide_image":"","location_id":"-1","teacher_id":"1","series_id":"-1","booknumber":"-1","topic_id":"-1","messagetype":"-1","default_study_image":"","download":"1","target":" ","server":"1","path":"-1","podcast":"-1","mime":"1","from":"x","to":"x","pFrom":"x","pTo":"x"}', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_books`
--

CREATE TABLE IF NOT EXISTS `#__bsms_books` (
	`id` int(3) NOT NULL AUTO_INCREMENT,
	`bookname` varchar(250) DEFAULT NULL,
	`booknumber` int(5) DEFAULT NULL,
	`published` tinyint(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=74 ;

--
-- Dumping data for table `#__bsms_books`
--

INSERT INTO `#__bsms_books` (`id`, `bookname`, `booknumber`, `published`) VALUES 
	(1, 'JBS_BBK_GENESIS', 101, 1),
	(2, 'JBS_BBK_EXODUS', 102, 1),
	(3, 'JBS_BBK_LEVITICUS', 103, 1),
	(4, 'JBS_BBK_NUMBERS', 104, 1),
	(5, 'JBS_BBK_DEUTERONOMY', 105, 1) ,
	(6, 'JBS_BBK_JOSHUA', 106, 1) ,
	(7, 'JBS_BBK_JUDGES', 107, 1) ,
	(8, 'JBS_BBK_RUTH', 108, 1) ,
	(9, 'JBS_BBK_1SAMUEL', 109, 1) ,
	(10, 'JBS_BBK_2SAMUEL', 110, 1) ,
	(11, 'JBS_BBK_1KINGS', 111, 1) ,
	(12, 'JBS_BBK_2KINGS', 112, 1) ,
	(13, 'JBS_BBK_1CHRONICLES', 113, 1) ,
	(14, 'JBS_BBK_2CHRONICLES', 114, 1) ,
	(15, 'JBS_BBK_EZRA', 115, 1) ,
	(16, 'JBS_BBK_NEHEMIAH', 116, 1) ,
	(17, 'JBS_BBK_ESTHER', 117, 1) ,
	(18, 'JBS_BBK_JOB', 118, 1) ,
	(19, 'JBS_BBK_PSALM', 119, 1) ,
	(20, 'JBS_BBK_PROVERBS', 120, 1) ,
	(21, 'JBS_BBK_ECCLESIASTES', 121, 1) ,
	(22, 'JBS_BBK_SONG_OF_SOLOMON', 122, 1) ,
	(23, 'JBS_BBK_ISAIAH', 123, 1) ,
	(24, 'JBS_BBK_JEREMIAH', 124, 1) ,
	(25, 'JBS_BBK_LAMENTATIONS', 125, 1) ,
	(26, 'JBS_BBK_EZEKIEL', 126, 1) ,
	(27, 'JBS_BBK_DANIEL', 127, 1) ,
	(28, 'JBS_BBK_HOSEA', 128, 1) ,
	(29, 'JBS_BBK_JOEL', 129, 1) ,
	(30, 'JBS_BBK_AMOS', 130, 1) ,
	(31, 'JBS_BBK_OBADIAH', 131, 1) ,
	(32, 'JBS_BBK_JONAH', 132, 1) ,
	(33, 'JBS_BBK_MICAH', 133, 1) ,
	(34, 'JBS_BBK_NAHUM', 134, 1) ,
	(35, 'JBS_BBK_HABAKKUK', 135, 1) ,
	(36, 'JBS_BBK_ZEPHANIAH', 136, 1),
	(37, 'JBS_BBK_HAGGAI', 137, 1),
	(38, 'JBS_BBK_ZECHARIAH', 138, 1),
	(39, 'JBS_BBK_MALACHI', 139, 1),
	(40, 'JBS_BBK_MATTHEW', 140, 1),
	(41, 'JBS_BBK_MARK', 141, 1),
	(42, 'JBS_BBK_LUKE', 142, 1),
	(43, 'JBS_BBK_JOHN', 143, 1),
	(44, 'JBS_BBK_ACTS', 144, 1),
	(45, 'JBS_BBK_ROMANS', 145, 1),
	(46, 'JBS_BBK_1CORINTHIANS', 146, 1),
	(47, 'JBS_BBK_2CORINTHIANS', 147, 1),
	(48, 'JBS_BBK_GALATIANS', 148, 1),
	(49, 'JBS_BBK_EPHESIANS', 149, 1),
	(50, 'JBS_BBK_PHILIPPIANS', 150, 1),
	(51, 'JBS_BBK_COLOSSIANS', 151, 1),
	(52, 'JBS_BBK_1THESSALONIANS', 152, 1),
	(53, 'JBS_BBK_2THESSALONIANS', 153, 1),
	(54, 'JBS_BBK_1TIMOTHY', 154, 1),
	(55, 'JBS_BBK_2TIMOTHY', 155, 1),
	(56, 'JBS_BBK_TITUS', 156, 1),
	(57, 'JBS_BBK_PHILEMON', 157, 1),
	(58, 'JBS_BBK_HEBREWS', 158, 1),
	(59, 'JBS_BBK_JAMES', 159, 1),
	(60, 'JBS_BBK_1PETER', 160, 1),
	(61, 'JBS_BBK_2PETER', 161, 1),
	(62, 'JBS_BBK_1JOHN', 162, 1),
	(63, 'JBS_BBK_2JOHN', 163, 1),
	(64, 'JBS_BBK_3JOHN', 164, 1),
	(65, 'JBS_BBK_JUDE', 165, 1),
	(66, 'JBS_BBK_REVELATION', 166, 1),
	(67, 'JBS_BBK_TOBIT', 167, 1),
	(68, 'JBS_BBK_JUDITH', 168, 1),
	(69, 'JBS_BBK_1MACCABEES', 169, 1),
	(70, 'JBS_BBK_2MACCABEES', 170, 1),
	(71, 'JBS_BBK_WISDOM', 171, 1),
	(72, 'JBS_BBK_SIRACH', 172, 1),
	(73, 'JBS_BBK_BARUCH', 173, 1);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_comments`
--

CREATE TABLE IF NOT EXISTS `#__bsms_comments` (
	`id` int(3) NOT NULL AUTO_INCREMENT,
	`published` tinyint(1) NOT NULL DEFAULT '0',
	`study_id` int(11) NOT NULL DEFAULT '0',
	`user_id` int(11) NOT NULL DEFAULT '0',
	`full_name` varchar(50) NOT NULL DEFAULT '',
	`user_email` varchar(100) NOT NULL DEFAULT '',
	`comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`comment_text` text NOT NULL,
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `#__bsms_comments`
--


-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_folders`
--

CREATE TABLE IF NOT EXISTS `#__bsms_folders` (
	`id` int(3) NOT NULL AUTO_INCREMENT,
	`foldername` varchar(250) NOT NULL DEFAULT '',
	`folderpath` varchar(250) NOT NULL DEFAULT '',
	`published` tinyint(1) NOT NULL DEFAULT '1',
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_folders`
--

INSERT INTO `#__bsms_folders` (`id`, `foldername`, `folderpath`, `published`, `asset_id`, `access`) VALUES
	(1, 'My Folder Name', '/media/', 1, 0,0);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_locations`
--

CREATE TABLE IF NOT EXISTS `#__bsms_locations` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`location_text` varchar(250) DEFAULT NULL,
	`published` tinyint(1) NOT NULL DEFAULT '1',
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_locations`
--

INSERT INTO `#__bsms_locations` (`id`, `location_text`, `published`, `asset_id`, `access`) VALUES 
	(1, 'My Location', 1,0,0);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_media`
--

CREATE TABLE IF NOT EXISTS `#__bsms_media` (
	`id` int(3) NOT NULL AUTO_INCREMENT,
	`media_text` text,
	`media_image_name` varchar(250) NOT NULL DEFAULT '',
	`media_image_path` varchar(250) NOT NULL DEFAULT '',
	`path2` varchar(150) NOT NULL,
	`media_alttext` varchar(250) NOT NULL DEFAULT '',
	`published` tinyint(1) NOT NULL DEFAULT '1',
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `#__bsms_media`
--

INSERT  INTO `#__bsms_media` (`id`, `media_text`, `media_image_name`, `media_image_path`, `path2`, `media_alttext`, `published`, `asset_id`, `access`) VALUES 
	(1, 'mp3 compressed audio file', 'mp3', '','speaker24.png', 'mp3 audio file', 1,0,0),
	(2, 'Video', 'Video File', '','video24.png', 'Video File', 1,0,0),
	(3, 'm4v', 'Video Podcast', '','podcast-video24.png', 'Video Podcast', 1,0,0),
	(4, 'Streaming Audio', 'Streaming Audio', '','streamingaudio24.png', 'Streaming Audio', 1,0,0),
	(5, 'Streaming Video', 'Streaming Video', '','streamingvideo24.png', 'Streaming Video', 1,0,0),
	(6, 'Real Audio', 'Real Audio', '','realplayer24.png', 'Real Audio', 1,0,0),
	(7, 'Windows Media Audio', 'Windows Media Audio', '','windows-media24.png', 'Windows Media File', 1,0,0),
	(8, 'Podcast Audio', 'Podcast Audio', '','podcast-audio24.png', 'Podcast Audio', 1,0,0),
	(9, 'CD', 'CD', '','cd.png', 'CD', 1,0,0),
	(10, 'DVD', 'DVD', '','dvd.png', 'DVD', 1,0,0), 
	(11,'Download','Download', '', 'download.png', 'Download', '1',0,0),
	(12,'Article','Article', '', 'textfile24.png', 'Article', '1',0,0),
	(13,'You Tube','You Tube','','youtube24.png','You Tube Video', 1,0,0);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_mediafiles`
--

CREATE TABLE IF NOT EXISTS `#__bsms_mediafiles` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`study_id` int(5) DEFAULT NULL,
	`media_image` int(3) DEFAULT NULL,
	`server` varchar(250) DEFAULT NULL,
	`path` varchar(250) DEFAULT NULL,
	`special` varchar(250) DEFAULT '_self',
	`filename` text,
	`size` varchar(50) DEFAULT NULL,
	`mime_type` int(3) DEFAULT NULL,
	`podcast_id` varchar(50) DEFAULT NULL,
	`internal_viewer` tinyint(1) DEFAULT '0',
	`mediacode` text,
	`ordering` int(11) NOT NULL DEFAULT '0',
	`createdate` datetime DEFAULT NULL,
	`link_type` char(1) DEFAULT NULL,
	`hits` int(10) DEFAULT NULL,
	`published` tinyint(1) NOT NULL DEFAULT '1',
	`docMan_id` int(11) DEFAULT NULL,
	`article_id` int(11) DEFAULT NULL,
	`comment` text,
	`virtueMart_id` int(11) DEFAULT NULL,
	`downloads` int(10) DEFAULT '0',
	`plays` int(10) DEFAULT '0',
	`params` text,
	`player` int(2) DEFAULT NULL,
	`popup` int(2) DEFAULT NULL,
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_mediafiles`
--

INSERT INTO `#__bsms_mediafiles` (`id`, `study_id`, `media_image`, `server`, `path`, `special`, `filename`, `size`, `mime_type`, `podcast_id`, `internal_viewer`, `mediacode`, `ordering`, `createdate`, `link_type`, `hits`, `published`, `docMan_id`, `article_id`, `comment`, `virtueMart_id`, `downloads`, `plays`, `params`, `player`, `popup`, `asset_id`, `access`) VALUES
	(1, 1, 2, '1', '1', '', 'myfile.mp3', '12332', 1, '1', 0, '', 0, '2009-09-13 00:10:00', '1',0,1,0,-1,'',0,0,0,'{"playerwidth":"","playerheight":"","itempopuptitle":"","itempopupfooter":"","popupmargin":"50"}',1,1,NULL,NULL);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_message_type`
--

CREATE TABLE IF NOT EXISTS `#__bsms_message_type` (
	`id` int(3) NOT NULL AUTO_INCREMENT,
	`message_type` text NOT NULL,
	`published` tinyint(1) NOT NULL DEFAULT '1',
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_message_type`
--

INSERT INTO `#__bsms_message_type` (`id`, `message_type`, `published`, `asset_id`, `access`) VALUES
	(1, 'Sunday', 1,0,0);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_mimetype`
--

CREATE TABLE IF NOT EXISTS `#__bsms_mimetype` (
	`id` int(3) NOT NULL AUTO_INCREMENT,
	`mimetype` varchar(50) DEFAULT NULL,
	`mimetext` varchar(50) DEFAULT NULL,
	`published` tinyint(1) NOT NULL DEFAULT '1',
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `#__bsms_mimetype`
--

INSERT INTO `#__bsms_mimetype` (`id`, `mimetype`, `mimetext`, `published`, `asset_id`, `access`) VALUES 
	(1,'audio/mpeg3','MP3 Audio',1,0,0), 
	(2,'audio/x-pn-realaudio','Real Audio',1,0,0),
	(3,'video/x-m4v','Podcast Video m4v',1,0,0),
	(4,'application/vnd.rn-realmedia','Real Media rm',1,0,0),
	(5,'audio/x-ms-wma','Windows Media Audio WMA',1,0,0),
	(6,'text/html','Text',1,0,0),
	(7,'audio/x-wav','Windows wav File',1,0,0),
	(8,'audio/x-pn-realaudio-plugin',' Real audio Plugin.rpm',1,0,0),
	(9,'audio/x-pn-realaudio','Real Media File .rm',1,0,0),
	(10,'audio/x-realaudio','Rea Audio File .ra',1,0,0),
	(11,'audio/x-pn-realaudio','Read Audio File.ram',1,0,0),
	(12,'video/mpeg',' Mpeg video .mpg',1,0,0),
	(13,'audio/mpeg','Video .mp2 File',1,0,0),
	(14,'video/x-msvideo',' Video .avi File',1,0,0),
	(15,'video/x-flv',' Flash Video FLV',1,0,0);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_order`
--

CREATE TABLE IF NOT EXISTS `#__bsms_order` (
	`id` int(3) NOT NULL AUTO_INCREMENT,
	`value` varchar(15) DEFAULT '',
	`text` varchar(50) DEFAULT '',
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `#__bsms_order`
--

INSERT INTO `#__bsms_order` (`id`, `value`, `text`) VALUES 
	(1, 'ASC', 'JBS_CMN_ASCENDING'),
	(2, 'DESC', 'JBS_CMN_DESCENDING');

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_podcast`
--

CREATE TABLE IF NOT EXISTS `#__bsms_podcast` (
	`id` int(3) NOT NULL AUTO_INCREMENT,
	`title` varchar(100) DEFAULT NULL,
	`website` varchar(100) DEFAULT NULL,
	`description` text,
	`image` varchar(130) DEFAULT NULL,
	`imageh` int(3) DEFAULT NULL,
	`imagew` int(3) DEFAULT NULL,
	`author` varchar(100) DEFAULT NULL,
	`podcastimage` varchar(130) DEFAULT NULL,
	`podcastsearch` varchar(255) DEFAULT NULL,
	`filename` varchar(150) DEFAULT NULL,
	`language` varchar(10) DEFAULT 'en-us',
	`editor_name` varchar(150) DEFAULT NULL,
	`editor_email` varchar(150) DEFAULT NULL,
	`podcastlimit` int(5) DEFAULT NULL,
	`published` tinyint(1) NOT NULL DEFAULT '1',
	`episodetitle` int(11) DEFAULT NULL,
	`custom` varchar(200) DEFAULT NULL,
	`detailstemplateid` int(11) DEFAULT NULL,
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_podcast`
--

INSERT INTO `#__bsms_podcast` (`id`, `title`, `website`, `description`, `image`, `imageh`, `imagew`, `author`, `podcastimage`, `podcastsearch`, `filename`, `language`, `editor_name`, `editor_email`, `podcastlimit`, `published`, `episodetitle`, `custom`, `detailstemplateid`, `asset_id`, `access`) VALUES 
	(1, 'My Podcast', 'www.mywebsite.com', 'Podcast Description goes here', 'www.mywebsite.com/myimage.jpg', 30, 30, 'Pastor Billy', 'www.mywebsite.com/myimage.jpg', 'jesus', 'mypodcast.xml', 'en-us', 'Jim Editor', 'jim@mywebsite.com', 50, 1, NULL, '', 1,NULL,NULL);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_search`
--

CREATE TABLE IF NOT EXISTS `#__bsms_search` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`value` varchar(15) DEFAULT '',
	`text` varchar(15) DEFAULT '',
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `#__bsms_search`
--


-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_series`
--

CREATE TABLE IF NOT EXISTS `#__bsms_series` (
	`id` int(3) NOT NULL AUTO_INCREMENT,
	`series_text` text,
	`teacher` int(3) DEFAULT NULL,
	`description` text,
	`series_thumbnail` varchar(150) DEFAULT NULL,
	`published` tinyint(1) NOT NULL DEFAULT '1',
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_series`
--

INSERT INTO `#__bsms_series` (`id`, `series_text`, `teacher`, `description`, `series_thumbnail`, `published`, `asset_id`, `access`) VALUES
	(1, 'Worship Series', -1, '', '', 1,0,0);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_servers`
--

CREATE TABLE IF NOT EXISTS `#__bsms_servers` (
	`id` int(3) NOT NULL AUTO_INCREMENT,
	`server_name` varchar(250) NOT NULL DEFAULT '',
	`server_path` varchar(250) NOT NULL DEFAULT '',
	`published` tinyint(1) NOT NULL DEFAULT '1',
	`server_type` char(5) NOT NULL DEFAULT 'local',
	`ftp_username` char(255) NOT NULL,
	`ftp_password` char(255) NOT NULL,
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_servers`
--

INSERT INTO `#__bsms_servers` (`id`, `server_name`, `server_path`, `published`, `server_type`, `ftp_username`, `ftp_password`, `asset_id`, `access`) VALUES
	(1, 'Your Server Name', 'www.mywebsite.com', 1, 'local', '', '',0,0);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_share`
--

CREATE TABLE IF NOT EXISTS `#__bsms_share` (
	`id` int(3) NOT NULL AUTO_INCREMENT,
	`name` varchar(250) DEFAULT NULL,
	`params` text,
	`published` tinyint(1) NOT NULL DEFAULT '1',
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `#__bsms_share`
--

INSERT INTO `#__bsms_share` (`id`, `name`, `params`, `published`, `asset_id`, `access`) VALUES
	(1, 'FaceBook', '{"mainlink":"http://www.facebook.com/sharer.php?","item1prefix":"u=","item1":200,"item1custom":"","item2prefix":"t=","item2":5,"item2custom":"","item3prefix":"","item3":6,"item3custom":"","item4prefix":"","item4":8,"item4custom":"","use_bitly":0,"username":"","api":"","shareimage":"components/com_biblestudy/images/facebook.png","shareimageh":"33px","shareimagew":"33px","totalcharacters":"","alttext":"FaceBook"}', 1,0,0),
	(2, 'Twitter', '{"mainlink":"http://twitter.com/home?","item1prefix":"status=","item1":200,"item1custom":"","item2prefix":"","item2":5,"item2custom":"","item3prefix":"","item3":1,"item3custom":"","item4prefix":"","item4":"","item4custom":"","use_bitly":0,"username":"","api":"","shareimage":"components/com_biblestudy/images/twitter.png","shareimagew":"33px","shareimageh":"33px","totalcharacters":140,"alttext":"Twitter"}', 1,0,0),
	(3, 'Delicious', '{"mainlink":"http://delicious.com/save?","item1prefix":"url=","item1":200,"item1custom":"","item2prefix":"&title=","item2":5,"item2custom":"","item3prefix":"","item3":6,"item3custom":"","item4prefix":"","item4":"","item4custom":"","use_bitly":0,"username":"","api":"","shareimage":"components/com_biblestudy/images/delicious.png","shareimagew":"33px","shareimageh":"33px","totalcharacters":"","alttext":"Delicious"}', 1,0,0),
	(4, 'MySpace', '{"mainlink":"http://www.myspace.com/index.cfm?","item1prefix":"fuseaction=postto&t=","item1":5,"item1custom":"","item2prefix":"&c=","item2":6,"item2custom":"","item3prefix":"&u=","item3":200,"item3custom":"","item4prefix":"&l=1","item4":"","item4custom":"","use_bitly":0,"username":"","api":"","shareimage":"components/com_biblestudy/images/myspace.png","shareimagew":"33px","shareimageh":"33px","totalcharacters":"","alttext":"MySpace"}', 1,0,0);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_studies`
--

CREATE TABLE IF NOT EXISTS `#__bsms_studies` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`studydate` datetime DEFAULT NULL,
	`teacher_id` int(11) DEFAULT '1',
	`studynumber` varchar(100) DEFAULT '',
	`booknumber` int(3) DEFAULT '101',
	`chapter_begin` int(3) DEFAULT '1',
	`verse_begin` int(3) DEFAULT '1',
	`chapter_end` int(3) DEFAULT '1',
	`verse_end` int(3) DEFAULT '1',
	`secondary_reference` text,
	`booknumber2` varchar(4) DEFAULT NULL,
	`chapter_begin2` varchar(4) DEFAULT NULL,
	`verse_begin2` varchar(4) DEFAULT NULL,
	`chapter_end2` varchar(4) DEFAULT NULL,
	`verse_end2` varchar(4) DEFAULT NULL,
	`prod_dvd` varchar(100) DEFAULT NULL,
	`prod_cd` varchar(100) DEFAULT NULL,
	`server_cd` varchar(10) DEFAULT NULL,
	`server_dvd` varchar(10) DEFAULT NULL,
	`image_cd` varchar(10) DEFAULT NULL,
	`image_dvd` varchar(10) DEFAULT '0',
	`studytext2` text,
	`comments` tinyint(1) DEFAULT '1',
	`hits` int(10) NOT NULL DEFAULT '0',
	`user_id` int(10) DEFAULT NULL,
	`user_name` varchar(50) DEFAULT NULL,
	`show_level` varchar(100) NOT NULL DEFAULT '0',
	`location_id` int(3) DEFAULT NULL,
	`studytitle` text,
	`studyintro` text,
	`media_hours` varchar(2) DEFAULT NULL,
	`media_minutes` varchar(2) DEFAULT NULL,
	`media_seconds` varchar(2) DEFAULT NULL,
	`messagetype` varchar(100) DEFAULT '1',
	`series_id` int(3) DEFAULT '0',
	`topics_id` int(3) DEFAULT '0',
	`studytext` text,
	`thumbnailm` text,
	`thumbhm` int(11) DEFAULT NULL,
	`thumbwm` int(11) DEFAULT NULL,
	`params` text,
	`published` tinyint(1) NOT NULL DEFAULT '1',
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_studies`
--

INSERT INTO `#__bsms_studies` (`id`, `studydate`, `teacher_id`, `studynumber`, `booknumber`, `chapter_begin`, `verse_begin`, `chapter_end`, `verse_end`, `secondary_reference`, `booknumber2`, `chapter_begin2`, `verse_begin2`, `chapter_end2`, `verse_end2`, `prod_dvd`, `prod_cd`, `server_cd`, `server_dvd`, `image_cd`, `image_dvd`, `studytext2`, `comments`, `hits`, `user_id`, `user_name`, `show_level`, `location_id`, `studytitle`, `studyintro`, `media_hours`, `media_minutes`, `media_seconds`, `messagetype`, `series_id`, `topics_id`, `studytext`, `thumbnailm`, `thumbhm`, `thumbwm`, `params`, `published`, `asset_id`, `access`) VALUES
	(1, '2010-03-13 00:10:00', 1, '2010-001', 101, 1, 1, 1, 31, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, 1, 0, NULL, NULL, 0, NULL, 'Sample Study Title', 'Sample text you can use as an introduction to your study', NULL, NULL, NULL, '1', 0, 0, 'This is where you would put study notes or other information. This could be the full text of your study as well. If you install the scripture links plugin you will have all verses as links to BibleGateway.com', NULL, NULL, NULL, NULL, 1,NULL,NULL);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_studytopics`
--

CREATE TABLE IF NOT EXISTS `#__bsms_studytopics` (
	`id` int(3) NOT NULL AUTO_INCREMENT,
	`study_id` int(3) NOT NULL DEFAULT '0',
	`topic_id` int(3) NOT NULL DEFAULT '0',
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `id` (`id`),
	KEY `id_2` (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `#__bsms_studytopics`
--


-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_teachers`
--

CREATE TABLE IF NOT EXISTS `#__bsms_teachers` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`teacher_image` text,
	`teacher_thumbnail` text,
	`teachername` varchar(250) NOT NULL DEFAULT '',
	`title` varchar(250) DEFAULT NULL,
	`phone` varchar(50) DEFAULT NULL,
	`email` varchar(100) DEFAULT NULL,
	`website` text,
	`information` text,
	`image` text,
	`imageh` text,
	`imagew` text,
	`thumb` text,
	`thumbw` text,
	`thumbh` text,
	`short` text,
	`ordering` int(3) DEFAULT NULL,
	`catid` int(3) DEFAULT '1',
	`list_show` tinyint(1) NOT NULL DEFAULT '1',
	`published` tinyint(1) NOT NULL DEFAULT '1',
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_teachers`
--

INSERT INTO `#__bsms_teachers` (`id`, `teacher_image`, `teacher_thumbnail`, `teachername`, `title`, `phone`, `email`, `website`, `information`, `image`, `imageh`, `imagew`, `thumb`, `thumbw`, `thumbh`, `short`, `ordering`, `catid`, `list_show`, `published`, `asset_id`, `access`) VALUES
	(1, '', '', 'Billy Sunday', 'Pastor', '555-555-5555', 'billy@sunday.com', 'http://billysunday.com', 'William Ashley Sunday was an American athlete who after being a popular outfielder in baseballs National League during the 1880s became the most celebrated and influential American evangelist during the first two decades of the 20th century. ', 'components/com_biblestudy/images/billy_sunday11.jpg', '276', '197', 'components/com_biblestudy/images/images.jpg', '101', '141', 'Billy Sunday: 1862-1935', 0, 1, 1, 1,NULL,NULL);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_templates`
--

CREATE TABLE IF NOT EXISTS `#__bsms_templates` (
	`id` int(3) NOT NULL AUTO_INCREMENT,
	`type` varchar(255) NOT NULL,
	`tmpl` longtext NOT NULL,
	`published` int(1) NOT NULL DEFAULT '1',
	`params` longtext,
	`title` text,
	`text` text,
	`pdf` text,
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

--
-- Dumping data for table `#__bsms_templates`
--

INSERT INTO `#__bsms_templates` (`id`, `type`, `tmpl`, `published`, `params`, `title`, `text`, `pdf`, `asset_id`, `access`) VALUES
(1, 'tmplList', '', 1, '{"studieslisttemplateid":"1","detailstemplateid":"1","teachertemplateid":"1","serieslisttemplateid":"1","seriesdetailtemplateid":"1","teacher_id":["-1"],"series_id":["-1"],"booknumber":["-1"],"topic_id":["-1"],"messagetype":["-1"],"locations":["-1"],"show_verses":"0","stylesheet":"","date_format":"2","custom_date_format":"","duration_type":"2","protocol":"http:\\/\\/","media_player":"0","popuptype":"window","internal_popup":"1","player_width":"400","player_height":"300","embedshare":"TRUE","backcolor":"0x287585","frontcolor":"0xFFFFFF","lightcolor":"0x000000","screencolor":"0x000000","popuptitle":"{{title}}","popupfooter":"{{filename}}","popupmargin":"50","popupbackground":"black","popupimage":"components\\/com_biblestudy\\/images\\/speaker24.png","show_filesize":"1","store_page":"flypage.tpl","useexpert_list":"0","headercode":"","templatecode":"                                   {{teacher}}             {{title}}             {{date}}                                   {{studyintro}}             {{scripture}}                               ","wrapcode":"0","itemslimit":"20","default_order":"DESC","show_page_title":"1","show_page_image":"1","page_title":"Bible Studies","use_headers_list":"1","list_intro":"","intro_show":"1","list_teacher_show":"1","listteachers":"","teacherlink":"1","details_text":"Study Details","show_book_search":"1","use_go_button":"1","booklist":"1","show_teacher_search":"1","show_series_search":"1","show_type_search":"1","show_year_search":"1","show_order_search":"1","show_topic_search":"1","show_locations_search":"1","show_popular":"1","row1col1":"10","r1c1custom":"","r1c1span":"1","linkr1c1":"0","row1col2":"5","r1c2custom":"","r1c2span":"2","linkr1c2":"0","row1col3":"0","r1c3custom":"","r1c3span":"1","linkr1c3":"0","row1col4":"20","r1c4custom":"","linkr1c4":"0","row2col1":"9","r2c1custom":"","r2c1span":"1","linkr2c1":"0","row2col2":"7","r2c2custom":"","r2c2span":"1","linkr2c2":"0","row2col3":"1","r2c3custom":"","r2c3span":"1","linkr2c3":"0","row2col4":"2","r2c4custom":"","linkr2c4":"0","row3col1":"6","r3c1custom":"","r3c1span":"4","linkr3c1":"0","row3col2":"0","r3c2custom":"","r3c2span":"1","linkr3c2":"0","row3col3":"0","r3c3custom":"","r3c3span":"1","linkr3c3":"0","row3col4":"0","r3c4custom":"","linkr3c4":"0","row4col1":"0","r4c1custom":"","r4c1span":"1","linkr4c1":"0","row4col2":"0","r4c2custom":"","r4c2span":"1","linkr4c2":"0","row4col3":"0","r4c3custom":"","r4c3span":"1","linkr4c3":"0","row4col4":"0","r4c4custom":"","linkr4c4":"0","show_print_view":"1","show_teacher_view":"0","show_passage_view":"1","use_headers_view":"1","list_items_view":"0","title_line_1":"1","customtitle1":"","title_line_2":"4","customtitle2":"","view_link":"1","link_text":"Return to Studies List","show_scripture_link":"0","show_comments":"1","link_comments":"0","comment_access":"1","comment_publish":"0","use_captcha":"1","public_key":"","private_key":"","email_comments":"1","recipient":"","subject":"Comments on studies","body":"Comments entered.","useexpert_details":"0","study_detailtemplate":"","teacher_title":"Our Teachers","show_teacher_studies":"1","studies":"","label_teacher":"Latest Messages","useexpert_teacherlist":"0","teacher_headercode":"","teacher_templatecode":"           {{teacher}}     {{title}}     {{teacher}}           {{short}}     {{information}}       ","teacher_wrapcode":"0","useexpert_teacherdetail":"0","teacher_detailtemplate":"           {{teacher}}     {{title}}     {{teacher}}           {{short}}     {{information}}       ","series_title":"Our Series","show_series_title":"1","show_page_image_series":"1","series_show_description":"1","series_characters":"","search_series":"1","series_limit":"5","series_list_order":"ASC","series_order_field":"series_text","serieselement1":"1","seriesislink1":"1","serieselement2":"6","seriesislink2":"1","serieselement3":"0","seriesislink3":"1","serieselement4":"0","seriesislink4":"1","useexpert_serieslist":"0","series_headercode":"","series_templatecode":"","series_wrapcode":"0","series_detail_sort":"studydate","series_detail_order":"DESC","series_detail_limit":"","series_list_return":"1","series_detail_listtype":"0","series_detail_1":"5","series_detail_islink1":"1","series_detail_2":"7","series_detail_islink2":"0","series_detail_3":"10","series_detail_islink3":"0","series_detail_4":"20","series_detail_islink4":"0","useexpert_seriesdetail":"0","series_detailcode":"","tip_title":"Sermon Information","tooltip":"1","tip_item1_title":"Title","tip_item1":"5","tip_item2_title":"Details","tip_item2":"6","tip_item3_title":"Teacher","tip_item3":"7","tip_item4_title":"Reference","tip_item4":"1","tip_item5_title":"Date","tip_item5":"10","drow1col1":"5","dr1c1custom":"","dr1c1span":"2","dlinkr1c1":"0","drow1col2":"0","dr1c2custom":"","dr1c2span":"1","dlinkr1c2":"0","drow1col3":"8","dr1c3custom":"","dr1c3span":"2","dlinkr1c3":"0","drow1col4":"0","dr1c4custom":"","dlinkr1c4":"0","drow2col1":"1","dr2c1custom":"","dr2c1span":"1","dlinkr2c1":"0","drow2col2":"2","dr2c2custom":"","dr2c2span":"1","dlinkr2c2":"0","drow2col3":"3","dr2c3custom":"","dr2c3span":"2","dlinkr2c3":"0","drow2col4":"0","dr2c4custom":"","dlinkr2c4":"0","drow3col1":"10","dr3c1custom":"","dr3c1span":"1","dlinkr3c1":"0","drow3col2":"9","dr3c2custom":"","dr3c2span":"1","dlinkr3c2":"0","drow3col3":"20","dr3c3custom":"","dr3c3span":"2","dlinkr3c3":"0","drow3col4":"0","dr3c4custom":"","dlinkr3c4":"0","drow4col1":"6","dr4c1custom":"","dr4c1span":"4","dlinkr4c1":"0","drow4col2":"0","dr4c2custom":"","dr4c2span":"1","dlinkr4c2":"0","drow4col3":"0","dr4c3custom":"","dr4c3span":"1","dlinkr4c3":"0","drow4col4":"0","dr4c4custom":"","dlinkr4c4":"0","landing_hide":"0","landing_hidelabel":"Show\\/Hide All","headingorder_1":"teachers","headingorder_2":"series","headingorder_3":"books","headingorder_4":"topics","headingorder_5":"locations","headingorder_6":"messagetypes","headingorder_7":"years","showteachers":"1","landingteacherslimit":"","teacherslabel":"Speakers","linkto":"1","showseries":"1","landingserieslimit":"","serieslabel":"Series","series_linkto":"0","showbooks":"1","landingbookslimit":"","bookslabel":"Books","showtopics":"1","landingtopicslimit":"","topicslabel":"Topics","showlocations":"1","landinglocationslimit":"","locationslabel":"Locations","showmessagetypes":"1","landingmessagetypeslimit":"","messagetypeslabel":"Message Types","showyears":"1","landingyearslimit":"","yearslabel":"Years"}', 'Default', 'textfile24.png', 'pdf24.png', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_timeset`
--

CREATE TABLE IF NOT EXISTS `#__bsms_timeset` (
	`timeset` varchar(14) NOT NULL DEFAULT '',
	`backup` varchar(14) DEFAULT NULL,
	PRIMARY KEY (`timeset`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `#__bsms_timeset`
--

INSERT INTO `#__bsms_timeset` (`timeset`, `backup`) VALUES 
	( '1281646339', '1281646339');

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_topics`
--

CREATE TABLE IF NOT EXISTS `#__bsms_topics` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`topic_text` text,
	`published` tinyint(1) NOT NULL DEFAULT '1',
	`params` varchar(511) DEFAULT NULL,
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=114 ;

--
-- Dumping data for table `#__bsms_topics`
--

INSERT INTO `#__bsms_topics` (`id`, `topic_text`, `published`, `languages`, `asset_id`, `access`) VALUES 
	(1,'JBS_TOP_ABORTION',1, NULL, NULL, NULL) ,
	(3,'JBS_TOP_ADDICTION',1, NULL, NULL, NULL) ,
	(4,'JBS_TOP_AFTERLIFE',1, NULL, NULL, NULL) ,
	(5,'JBS_TOP_APOLOGETICS',1, NULL, NULL, NULL) ,
	(7,'JBS_TOP_BAPTISM',1, NULL, NULL, NULL) ,
	(8,'JBS_TOP_BASICS_OF_CHRISTIANITY',1, NULL, NULL, NULL) ,
	(9,'JBS_TOP_BECOMING_A_CHRISTIAN',1, NULL, NULL, NULL) ,
	(10,'JBS_TOP_BIBLE',1, NULL, NULL, NULL) ,
	(37,'JBS_TOP_BLENDED_FAMILY_RELATIONSHIPS',1, NULL, NULL, NULL) ,
	(12,'JBS_TOP_CHILDREN',1, NULL, NULL, NULL) ,
	(13,'JBS_TOP_CHRIST',1, NULL, NULL, NULL) ,
	(14,'JBS_TOP_CHRISTIAN_CHARACTER_FRUITS',1, NULL, NULL, NULL) ,
	(15,'JBS_TOP_CHRISTIAN_VALUES',1, NULL, NULL, NULL) ,
	(16,'JBS_TOP_CHRISTMAS_SEASON',1, NULL, NULL, NULL) ,
	(17,'JBS_TOP_CHURCH',1, NULL, NULL, NULL) ,
	(18,'JBS_TOP_COMMUNICATION',1, NULL, NULL, NULL) ,
	(19,'JBS_TOP_COMMUNION___LORDS_SUPPER',1, NULL, NULL, NULL) ,
	(21,'JBS_TOP_CREATION',1, NULL, NULL, NULL) ,
	(23,'JBS_TOP_CULTS',1, NULL, NULL, NULL) ,
	(113,'JBS_TOP_DA_VINCI_CODE',1, NULL, NULL, NULL) ,
	(24,'JBS_TOP_DEATH',1, NULL, NULL, NULL) ,
	(26,'JBS_TOP_DESCRIPTIONS_OF_GOD',1, NULL, NULL, NULL) ,
	(27,'JBS_TOP_DISCIPLES',1, NULL, NULL, NULL) ,
	(28,'JBS_TOP_DISCIPLESHIP',1, NULL, NULL, NULL) ,
	(30,'JBS_TOP_DIVORCE',1, NULL, NULL, NULL) ,
	(32,'JBS_TOP_EASTER_SEASON',1, NULL, NULL, NULL) ,
	(33,'JBS_TOP_EMOTIONS',1, NULL, NULL, NULL) ,
	(34,'JBS_TOP_ENTERTAINMENT',1, NULL, NULL, NULL) ,
	(35,'JBS_TOP_EVANGELISM',1, NULL, NULL, NULL) ,
	(36,'JBS_TOP_FAITH',1, NULL, NULL, NULL) ,
	(103,'JBS_TOP_FAMILY',1, NULL, NULL, NULL) ,
	(39,'JBS_TOP_FORGIVING_OTHERS',1, NULL, NULL, NULL) ,
	(104,'JBS_TOP_FREEDOM',1, NULL, NULL, NULL) ,
	(41,'JBS_TOP_FRIENDSHIP',1, NULL, NULL, NULL) ,
	(42,'JBS_TOP_FULFILLMENT_IN_LIFE',1, NULL, NULL, NULL) ,
	(43,'JBS_TOP_FUND_RAISING_RALLY',1, NULL, NULL, NULL) ,
	(44,'JBS_TOP_FUNERALS',1, NULL, NULL, NULL) ,
	(45,'JBS_TOP_GIVING',1, NULL, NULL, NULL) ,
	(2,'JBS_TOP_GODS_ACTIVITY',1, NULL, NULL, NULL) ,
	(6,'JBS_TOP_GODS_ATTRIBUTES',1, NULL, NULL, NULL) ,
	(40,'JBS_TOP_GODS_FORGIVENESS',1, NULL, NULL, NULL) ,
	(58,'JBS_TOP_GODS_LOVE',1, NULL, NULL, NULL) ,
	(65,'JBS_TOP_GODS_NATURE',1, NULL, NULL, NULL) ,
	(46,'JBS_TOP_GODS_WILL',1, NULL, NULL, NULL) ,
	(47,'JBS_TOP_HARDSHIP_OF_LIFE',1, NULL, NULL, NULL) ,
	(107,'JBS_TOP_HOLIDAYS',1, NULL, NULL, NULL) ,
	(48,'JBS_TOP_HOLY_SPIRIT',1, NULL, NULL, NULL) ,
	(111,'JBS_TOP_HOT_TOPICS',1, NULL, NULL, NULL) ,
	(11,'JBS_TOP_JESUS_BIRTH',1, NULL, NULL, NULL) ,
	(22,'JBS_TOP_JESUS_CROSS_FINAL_WEEK',1, NULL, NULL, NULL) ,
	(29,'JBS_TOP_JESUS_DIVINITY',1, NULL, NULL, NULL) ,
	(50,'JBS_TOP_JESUS_HUMANITY',1, NULL, NULL, NULL) ,
	(56,'JBS_TOP_JESUS_LIFE',1, NULL, NULL, NULL) ,
	(61,'JBS_TOP_JESUS_MIRACLES',1, NULL, NULL, NULL) ,
	(84,'JBS_TOP_JESUS_RESURRECTION',1, NULL, NULL, NULL) ,
	(93,'JBS_TOP_JESUS_TEACHING',1, NULL, NULL, NULL) ,
	(52,'JBS_TOP_KINGDOM_OF_GOD',1, NULL, NULL, NULL) ,
	(55,'JBS_TOP_LEADERSHIP_ESSENTIALS',1, NULL, NULL, NULL) ,
	(57,'JBS_TOP_LOVE',1, NULL, NULL, NULL) ,
	(59,'JBS_TOP_MARRIAGE',1, NULL, NULL, NULL) ,
	(109,'JBS_TOP_MEN',1, NULL, NULL, NULL) ,
	(82,'JBS_TOP_MESSIANIC_PROPHECIES',1, NULL, NULL, NULL) ,
	(62,'JBS_TOP_MISCONCEPTIONS_OF_CHRISTIANITY',1, NULL, NULL, NULL) ,
	(63,'JBS_TOP_MONEY',1, NULL, NULL, NULL) ,
	(112,'JBS_TOP_NARNIA',1, NULL, NULL, NULL) ,
	(66,'JBS_TOP_OUR_NEED_FOR_GOD',1, NULL, NULL, NULL) ,
	(69,'JBS_TOP_PARABLES',1, NULL, NULL, NULL) ,
	(70,'JBS_TOP_PARANORMAL',1, NULL, NULL, NULL) ,
	(71,'JBS_TOP_PARENTING',1, NULL, NULL, NULL) ,
	(73,'JBS_TOP_POVERTY',1, NULL, NULL, NULL) ,
	(74,'JBS_TOP_PRAYER',1, NULL, NULL, NULL) ,
	(76,'JBS_TOP_PROMINENT_N_T__MEN',1, NULL, NULL, NULL) ,
	(77,'JBS_TOP_PROMINENT_N_T__WOMEN',1, NULL, NULL, NULL) ,
	(78,'JBS_TOP_PROMINENT_O_T__MEN',1, NULL, NULL, NULL) ,
	(79,'JBS_TOP_PROMINENT_O_T__WOMEN',1, NULL, NULL, NULL) ,
	(83,'JBS_TOP_RACISM',1, NULL, NULL, NULL) ,
	(85,'JBS_TOP_SECOND_COMING',1, NULL, NULL, NULL) ,
	(86,'JBS_TOP_SEXUALITY',1, NULL, NULL, NULL) ,
	(87,'JBS_TOP_SIN',1, NULL, NULL, NULL) ,
	(88,'JBS_TOP_SINGLENESS',1, NULL, NULL, NULL) ,
	(89,'JBS_TOP_SMALL_GROUPS',1, NULL, NULL, NULL) ,
	(108,'JBS_TOP_SPECIAL_SERVICES',1, NULL, NULL, NULL) ,
	(90,'JBS_TOP_SPIRITUAL_DISCIPLINES',1, NULL, NULL, NULL) ,
	(91,'JBS_TOP_SPIRITUAL_GIFTS',1, NULL, NULL, NULL) ,
	(105,'JBS_TOP_STEWARDSHIP',1, NULL, NULL, NULL) ,
	(92,'JBS_TOP_SUPERNATURAL',1, NULL, NULL, NULL) ,
	(94,'JBS_TOP_TEMPTATION',1, NULL, NULL, NULL) ,
	(95,'JBS_TOP_TEN_COMMANDMENTS',1, NULL, NULL, NULL) ,
	(97,'JBS_TOP_TRUTH',1, NULL, NULL, NULL) ,
	(98,'JBS_TOP_TWELVE_APOSTLES',1, NULL, NULL, NULL) ,
	(100,'JBS_TOP_WEDDINGS',1, NULL, NULL, NULL) ,
	(110,'JBS_TOP_WOMEN',1, NULL, NULL, NULL) ,
	(101,'JBS_TOP_WORKPLACE_ISSUES',1, NULL, NULL, NULL) ,
	(102,'JBS_TOP_WORLD_RELIGIONS',1, NULL, NULL, NULL) ,
	(106,'JBS_TOP_WORSHIP',1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_version`
--

CREATE TABLE IF NOT EXISTS `#__bsms_version` (
	`id` int(3) NOT NULL AUTO_INCREMENT,
	`version` varchar(20) NOT NULL,
	`versiondate` date NOT NULL,
	`installdate` date NOT NULL,
	`build` varchar(20) NOT NULL,
	`versionname` varchar(40) DEFAULT NULL,
	`asset_id` int(10) DEFAULT NULL,
	`access` int(10) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `#__bsms_version`
--

INSERT INTO `#__bsms_version` (`id`, `version`, `versiondate`, `installdate`, `build`, `versionname`, `asset_id`, `access`) VALUES
	(1, '7.0.0', '2011-02-15', '2011-02-12', '700', '1kings',0,0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
