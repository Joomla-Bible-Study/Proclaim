-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 08, 2011 at 11:41 AM
-- Server version: 5.1.37
-- PHP Version: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `jbs61`
--

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_admin`
--

DROP TABLE IF EXISTS `#__bsms_admin`;
CREATE TABLE IF NOT EXISTS `#__bsms_admin` (
  `id` int(11) NOT NULL,
  `podcast` text,
  `series` text,
  `study` text,
  `teacher` text,
  `media` text,
  `download` text,
  `main` text,
  `params` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `#__bsms_admin`
--

INSERT INTO `#__bsms_admin` (`id`, `podcast`, `series`, `study`, `teacher`, `media`, `download`, `main`, `params`) VALUES
(1, '', '', '', '', 'speaker24.png', 'download.png', 'openbible.png', 'compat_mode=0\r\ndrop_tables=0\r\nadmin_store=1\r\nstudylistlimit=10\r\nseries_imagefolder=\r\nmedia_imagefolder=\r\nteachers_imagefolder=\r\nstudy_images=\r\npodcast_imagefolder=\r\nlocation_id=\r\nteacher_id=\r\nseries_id=\r\nbooknumber=\r\ntopic_id=\r\nmessagetype=\r\navr=0\r\ndownload=\r\ntarget=\r\nserver=\r\npath=\r\npodcast=0\r\nmime=0\r\nallow_entry_study=0\r\nentry_access=23\r\nstudy_publish=0');

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_books`
--

DROP TABLE IF EXISTS `#__bsms_books`;
CREATE TABLE IF NOT EXISTS `#__bsms_books` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `bookname` varchar(250) DEFAULT NULL,
  `booknumber` int(5) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=69 ;

--
-- Dumping data for table `#__bsms_books`
--

INSERT INTO `#__bsms_books` (`id`, `bookname`, `booknumber`, `published`) VALUES
(1, 'Genesis', 101, 1),
(2, 'Exodus', 102, 1),
(3, 'Leviticus', 103, 1),
(4, 'Numbers', 104, 1),
(5, 'Deuteronomy', 105, 1),
(6, 'Joshua', 106, 1),
(7, 'Judges', 107, 1),
(8, 'Ruth', 108, 1),
(9, '1Samuel', 109, 1),
(10, '2Samuel', 110, 1),
(11, '1Kings', 111, 1),
(12, '2Kings', 112, 1),
(13, '1Chronicles', 113, 1),
(14, '2Chronicles', 114, 1),
(15, 'Ezra', 115, 1),
(16, 'Nehemiah', 116, 1),
(17, 'Esther', 117, 1),
(18, 'Job', 118, 1),
(19, 'Psalm', 119, 1),
(20, 'Proverbs', 120, 1),
(21, 'Ecclesiastes', 121, 1),
(22, 'Song of Solomon', 122, 1),
(23, 'Isaiah', 123, 1),
(24, 'Jeremiah', 124, 1),
(25, 'Lamentations', 125, 1),
(26, 'Ezekiel', 126, 1),
(27, 'Daniel', 127, 1),
(28, 'Hosea', 128, 1),
(29, 'Joel', 129, 1),
(30, 'Amos', 130, 1),
(31, 'Obadiah', 131, 1),
(32, 'Jonah', 132, 1),
(33, 'Micah', 133, 1),
(34, 'Nahum', 134, 1),
(35, 'Habakkuk', 135, 1),
(36, 'Zephaniah', 136, 1),
(37, 'Haggai', 137, 1),
(38, 'Zechariah', 138, 1),
(39, 'Malachi', 139, 1),
(40, 'Matthew', 140, 1),
(41, 'Mark', 141, 1),
(42, 'Luke', 142, 1),
(43, 'John', 143, 1),
(44, 'Acts', 144, 1),
(45, 'Romans', 145, 1),
(46, '1Corinthians', 146, 1),
(47, '2Corinthians', 147, 1),
(48, 'Galatians', 148, 1),
(49, 'Ephesians', 149, 1),
(50, 'Philippians', 150, 1),
(51, 'Colossians', 151, 1),
(52, '1Thessalonians', 152, 1),
(53, '2Thessalonians', 153, 1),
(54, '1Timothy', 154, 1),
(55, '2Timothy', 155, 1),
(56, 'Titus', 156, 1),
(57, 'Philemon', 157, 1),
(58, 'Hebrews', 158, 1),
(59, 'James', 159, 1),
(60, '1Peter', 160, 1),
(61, '2Peter', 161, 1),
(62, '1John', 162, 1),
(63, '2John', 163, 1),
(64, '3John', 164, 1),
(65, 'Jude', 165, 1),
(66, 'Revelation', 166, 1),
(67, 'Topical', 167, 1),
(68, 'Holiday', 168, 1);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_comments`
--

DROP TABLE IF EXISTS `#__bsms_comments`;
CREATE TABLE IF NOT EXISTS `#__bsms_comments` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `study_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `comment_date` datetime NOT NULL,
  `comment_text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `#__bsms_comments`
--


-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_folders`
--

DROP TABLE IF EXISTS `#__bsms_folders`;
CREATE TABLE IF NOT EXISTS `#__bsms_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foldername` varchar(250) DEFAULT NULL,
  `folderpath` varchar(250) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_folders`
--

INSERT INTO `#__bsms_folders` (`id`, `foldername`, `folderpath`, `published`) VALUES
(1, '2011', '/MediaFiles/2011/', 1);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_locations`
--

DROP TABLE IF EXISTS `#__bsms_locations`;
CREATE TABLE IF NOT EXISTS `#__bsms_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_text` varchar(250) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `#__bsms_locations`
--


-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_media`
--

DROP TABLE IF EXISTS `#__bsms_media`;
CREATE TABLE IF NOT EXISTS `#__bsms_media` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `media_text` text,
  `media_image_name` varchar(250) DEFAULT NULL,
  `media_image_path` varchar(250) DEFAULT NULL,
  `path2` varchar(150) NOT NULL,
  `media_alttext` varchar(250) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `#__bsms_media`
--

INSERT INTO `#__bsms_media` (`id`, `media_text`, `media_image_name`, `media_image_path`, `path2`, `media_alttext`, `published`) VALUES
(12, 'DVD', 'DVD', 'components/com_biblestudy/images/dvd.png', '', 'DVD', 1),
(11, 'CD', 'CD', 'components/com_biblestudy/images/cd.png', '', 'CD', 1),
(10, 'Podcast Audio', 'Podcast Audio', 'components/com_biblestudy/images/podcast-audio24.png', '', 'Podcast Audio', 1),
(9, 'Windows Media Audio', 'Windows Media Audio', 'components/com_biblestudy/images/windows-media24.png', '', 'Windows Media File', 1),
(8, 'Real Audio', 'Real Audio', 'components/com_biblestudy/images/realplayer24.png', '', 'Real Audio', 1),
(7, 'Streaming Video', 'Streaming Video', 'components/com_biblestudy/images/streamingvideo24.png', '', 'Streaming Video', 1),
(6, 'Streaming Audio', 'Streaming Audio', 'components/com_biblestudy/images/streamingaudio24.png', '', 'Streaming Audio', 1),
(4, 'm4v', 'Video Podcast', 'components/com_biblestudy/images/podcast-video24.png', '', 'Video Podcast', 1),
(2, 'mp3 compressed audio file', 'mp3', 'components/com_biblestudy/images/speaker24.png', '', 'mp3 audio file', 1),
(3, 'Video', 'Video File', 'components/com_biblestudy/images/video24.png', '', 'Video File', 1),
(13, 'Article', 'Article', '', 'textfile24.png', 'Article', 1);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_mediafiles`
--

DROP TABLE IF EXISTS `#__bsms_mediafiles`;
CREATE TABLE IF NOT EXISTS `#__bsms_mediafiles` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `study_id` int(5) DEFAULT NULL,
  `media_image` int(3) DEFAULT NULL,
  `server` varchar(250) DEFAULT NULL,
  `path` varchar(250) DEFAULT NULL,
  `special` varchar(250) DEFAULT '_self',
  `filename` text,
  `size` varchar(50) DEFAULT NULL,
  `mime_type` int(3) DEFAULT NULL,
  `podcast_id` int(3) DEFAULT NULL,
  `internal_viewer` tinyint(1) DEFAULT '0',
  `mediacode` varchar(300) DEFAULT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `createdate` datetime DEFAULT NULL,
  `link_type` varchar(1) DEFAULT NULL,
  `hits` int(10) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `docMan_id` int(11) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `comment` text,
  `virtueMart_id` int(11) DEFAULT NULL,
  `params` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_mediafiles`
--

INSERT INTO `#__bsms_mediafiles` (`id`, `study_id`, `media_image`, `server`, `path`, `special`, `filename`, `size`, `mime_type`, `podcast_id`, `internal_viewer`, `mediacode`, `ordering`, `createdate`, `link_type`, `hits`, `published`, `docMan_id`, `article_id`, `comment`, `virtueMart_id`, `params`) VALUES
(1, 1, 2, '1', '1', '', '2011-001.mp3', '23445', 0, 0, 0, '', 0, '2011-02-01 00:00:00', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_message_type`
--

DROP TABLE IF EXISTS `#__bsms_message_type`;
CREATE TABLE IF NOT EXISTS `#__bsms_message_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_type` text,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `#__bsms_message_type`
--


-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_mimetype`
--

DROP TABLE IF EXISTS `#__bsms_mimetype`;
CREATE TABLE IF NOT EXISTS `#__bsms_mimetype` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `mimetype` varchar(50) DEFAULT NULL,
  `mimetext` varchar(50) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `#__bsms_mimetype`
--

INSERT INTO `#__bsms_mimetype` (`id`, `mimetype`, `mimetext`, `published`) VALUES
(1, 'audio/mpeg3', 'MP3 Audio', 1),
(2, 'audio/x-pn-realaudio', 'Real Audio', 1),
(3, 'video/x-m4v', 'Podcast Video m4v', 1),
(4, 'application/vnd.rn-realmedia', 'Real Media rm', 1),
(5, 'audio/x-ms-wma', 'Windows Media Audio WMA', 1),
(6, 'text/html', 'Text', 1),
(7, 'audio/x-wav', 'Windows wav File', 1),
(8, 'audio/x-pn-realaudio-plugin', ' Real audio Plugin.rpm', 1),
(9, 'audio/x-pn-realaudio', 'Real Media File .rm', 1),
(10, 'audio/x-realaudio', 'Rea Audio File .ra', 1),
(11, 'audio/x-pn-realaudio', 'Read Audio File.ram', 1),
(12, 'video/mpeg', ' Mpeg video .mpg', 1),
(13, 'audio/mpeg', 'Video .mp2 File', 1),
(14, 'video/x-msvideo', ' Video .avi File', 1),
(15, 'video/x-flv .flv', ' Flash Video FLV', 1);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_order`
--

DROP TABLE IF EXISTS `#__bsms_order`;
CREATE TABLE IF NOT EXISTS `#__bsms_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(15) DEFAULT NULL,
  `text` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `#__bsms_order`
--

INSERT INTO `#__bsms_order` (`id`, `value`, `text`) VALUES
(1, 'ASC', 'Ascending'),
(2, 'DESC', 'Descending');

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_podcast`
--

DROP TABLE IF EXISTS `#__bsms_podcast`;
CREATE TABLE IF NOT EXISTS `#__bsms_podcast` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `#__bsms_podcast`
--


-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_schemaversion`
--

DROP TABLE IF EXISTS `#__bsms_schemaversion`;
CREATE TABLE IF NOT EXISTS `#__bsms_schemaversion` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `schemaVersion` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_schemaversion`
--

INSERT INTO `#__bsms_schemaversion` (`id`, `schemaVersion`) VALUES
(1, 613);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_search`
--

DROP TABLE IF EXISTS `#__bsms_search`;
CREATE TABLE IF NOT EXISTS `#__bsms_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(15) DEFAULT NULL,
  `text` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `#__bsms_search`
--

INSERT INTO `#__bsms_search` (`id`, `value`, `text`) VALUES
(1, 'studytitle', 'Title'),
(2, 'studytext', 'Details'),
(3, 'studyintro', 'Description');

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_series`
--

DROP TABLE IF EXISTS `#__bsms_series`;
CREATE TABLE IF NOT EXISTS `#__bsms_series` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `series_text` text,
  `teacher` int(3) DEFAULT NULL,
  `description` text,
  `series_thumbnail` varchar(150) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_series`
--

INSERT INTO `#__bsms_series` (`id`, `series_text`, `teacher`, `description`, `series_thumbnail`, `published`) VALUES
(1, 'Worship', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_servers`
--

DROP TABLE IF EXISTS `#__bsms_servers`;
CREATE TABLE IF NOT EXISTS `#__bsms_servers` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `server_name` varchar(250) DEFAULT NULL,
  `server_path` varchar(250) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_servers`
--

INSERT INTO `#__bsms_servers` (`id`, `server_name`, `server_path`, `published`) VALUES
(1, 'www.calvarychapelnewberg.net', 'www.calvarychapelnewberg.net', 1);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_share`
--

DROP TABLE IF EXISTS `#__bsms_share`;
CREATE TABLE IF NOT EXISTS `#__bsms_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `params` text,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `#__bsms_share`
--

INSERT INTO `#__bsms_share` (`id`, `name`, `params`, `published`) VALUES
(1, 'FaceBook', 'mainlink=http://www.facebook.com/sharer.php?\nitem1prefix=u=\nitem1=200\nitem1custom=\nitem2prefix=t=\nitem2=5\nitem2custom=\nitem3prefix=\nitem3=6\nitem3custom=\nitem4prefix=\nitem4=8\nitem4custom=\nuse_bitly=0\nusername=\napi=\nshareimage=components/com_biblestudy/images/facebook.png\nshareimageh=33px\nshareimagew=33px\ntotalcharacters=\nalttext=FaceBook\n\n', 1),
(2, 'Twitter', 'mainlink=http://twitter.com/home?\r\nitem1prefix=status=\r\nitem1=200\r\nitem1custom=\r\nitem2prefix=\r\nitem2=5\r\nitem2custom=\r\nitem3prefix=\r\nitem3=1\r\nitem3custom=\r\nitem4prefix=\r\nitem4=\r\nitem4custom=\r\nuse_bitly=0\r\nusername=\r\napi=\r\nshareimage=components/com_biblestudy/images/twitter.png\r\nshareimagew=33px\r\nshareimageh=33px\r\ntotalcharacters=140\r\nalttext=Twitter', 1),
(3, 'Delicious', 'mainlink=http://delicious.com/save?\r\nitem1prefix=url=\r\nitem1=200\r\nitem1custom=\r\nitem2prefix=&title=\r\nitem2=5\r\nitem2custom=\r\nitem3prefix=\r\nitem3=6\r\nitem3custom=\r\nitem4prefix=\r\nitem4=\r\nitem4custom=\r\nuse_bitly=0\r\nusername=\r\napi=\r\nshareimage=components/com_biblestudy/images/delicious.png\r\nshareimagew=33px\r\nshareimageh=33px\r\ntotalcharacters=\r\nalttext=Delicious', 1),
(4, 'MySpace', 'mainlink=http://www.myspace.com/index.cfm?\r\nitem1prefix=fuseaction=postto&t=\r\nitem1=5\r\nitem1custom=\r\nitem2prefix=&c=\r\nitem2=6\r\nitem2custom=\r\nitem3prefix=&u=\r\nitem3=200\r\nitem3custom=\r\nitem4prefix=&l=1\r\nitem4=\r\nitem4custom=\r\nuse_bitly=0\r\nusername=\r\napi=\r\nshareimage=components/com_biblestudy/images/myspace.png\r\nshareimagew=33px\r\nshareimageh=33px\r\ntotalcharacters=\r\nalttext=MySpace', 1);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_studies`
--

DROP TABLE IF EXISTS `#__bsms_studies`;
CREATE TABLE IF NOT EXISTS `#__bsms_studies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studydate` datetime DEFAULT NULL,
  `teacher_id` int(11) DEFAULT '1',
  `studynumber` varchar(100) DEFAULT '',
  `booknumber` int(3) DEFAULT '101',
  `chapter_begin` int(3) DEFAULT NULL,
  `verse_begin` int(3) DEFAULT NULL,
  `chapter_end` int(3) DEFAULT NULL,
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
  `show_level` int(2) NOT NULL DEFAULT '0',
  `location_id` int(3) DEFAULT NULL,
  `verse_end` int(3) DEFAULT NULL,
  `studytitle` text,
  `studyintro` text,
  `media_hours` varchar(2) DEFAULT NULL,
  `media_minutes` varchar(2) DEFAULT NULL,
  `media_seconds` varchar(2) DEFAULT NULL,
  `messagetype` varchar(100) DEFAULT '1',
  `series_id` int(3) DEFAULT NULL,
  `topics_id` int(3) DEFAULT NULL,
  `studytext` text,
  `thumbnailm` text,
  `thumbhm` int(11) DEFAULT NULL,
  `thumbwm` int(11) DEFAULT NULL,
  `params` text,
  `media1_id` int(11) DEFAULT NULL,
  `media1_server` varchar(250) DEFAULT NULL,
  `media1_path` varchar(250) DEFAULT NULL,
  `media1_special` varchar(250) DEFAULT NULL,
  `media1_filename` text,
  `media1_size` text,
  `media1_show` tinyint(1) DEFAULT '0',
  `media2_id` int(11) DEFAULT '0',
  `media2_server` varchar(250) DEFAULT NULL,
  `media2_path` varchar(250) DEFAULT NULL,
  `media2_special` varchar(250) DEFAULT NULL,
  `media2_filename` text,
  `media2_size` text,
  `media2_show` tinyint(1) DEFAULT '0',
  `media3_id` int(11) DEFAULT NULL,
  `media3_server` varchar(250) DEFAULT NULL,
  `media3_path` varchar(250) DEFAULT NULL,
  `media3_special` varchar(250) DEFAULT NULL,
  `media3_filename` text,
  `media3_size` text,
  `media3_show` tinyint(1) DEFAULT '0',
  `media4_id` int(11) DEFAULT NULL,
  `media4_server` varchar(250) DEFAULT NULL,
  `media4_path` varchar(250) DEFAULT NULL,
  `media4_special` varchar(250) DEFAULT NULL,
  `media4_filename` text,
  `media4_size` text,
  `media4_show` tinyint(1) DEFAULT '0',
  `media5_id` int(11) DEFAULT NULL,
  `media5_server` varchar(250) DEFAULT NULL,
  `media5_path` varchar(250) DEFAULT NULL,
  `media5_special` varchar(250) DEFAULT NULL,
  `media5_filename` text,
  `media5_size` text,
  `media5_show` tinyint(1) DEFAULT '0',
  `media6_id` int(11) DEFAULT NULL,
  `media6_server` varchar(250) DEFAULT NULL,
  `media6_path` varchar(250) DEFAULT NULL,
  `media6_special` varchar(250) DEFAULT NULL,
  `media6_filename` text,
  `media6_size` text,
  `media6_show` tinyint(1) DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_studies`
--

INSERT INTO `#__bsms_studies` (`id`, `studydate`, `teacher_id`, `studynumber`, `booknumber`, `chapter_begin`, `verse_begin`, `chapter_end`, `secondary_reference`, `booknumber2`, `chapter_begin2`, `verse_begin2`, `chapter_end2`, `verse_end2`, `prod_dvd`, `prod_cd`, `server_cd`, `server_dvd`, `image_cd`, `image_dvd`, `studytext2`, `comments`, `hits`, `user_id`, `user_name`, `show_level`, `location_id`, `verse_end`, `studytitle`, `studyintro`, `media_hours`, `media_minutes`, `media_seconds`, `messagetype`, `series_id`, `topics_id`, `studytext`, `thumbnailm`, `thumbhm`, `thumbwm`, `params`, `media1_id`, `media1_server`, `media1_path`, `media1_special`, `media1_filename`, `media1_size`, `media1_show`, `media2_id`, `media2_server`, `media2_path`, `media2_special`, `media2_filename`, `media2_size`, `media2_show`, `media3_id`, `media3_server`, `media3_path`, `media3_special`, `media3_filename`, `media3_size`, `media3_show`, `media4_id`, `media4_server`, `media4_path`, `media4_special`, `media4_filename`, `media4_size`, `media4_show`, `media5_id`, `media5_server`, `media5_path`, `media5_special`, `media5_filename`, `media5_size`, `media5_show`, `media6_id`, `media6_server`, `media6_path`, `media6_special`, `media6_filename`, `media6_size`, `media6_show`, `published`) VALUES
(1, '2011-02-01 00:00:00', 1, '2010-094', 102, 2, 3, 2, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, 1, 0, NULL, NULL, 0, NULL, 15, 'Birth of a Lamb', 'The description', '', '23', '15', '0', 1, 23, '<p>Some study notes here.</p>', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_teachers`
--

DROP TABLE IF EXISTS `#__bsms_teachers`;
CREATE TABLE IF NOT EXISTS `#__bsms_teachers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_image` text,
  `teacher_thumbnail` text,
  `teachername` varchar(250) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(300) DEFAULT NULL,
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
  `published` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__bsms_teachers`
--

INSERT INTO `#__bsms_teachers` (`id`, `teacher_image`, `teacher_thumbnail`, `teachername`, `title`, `phone`, `email`, `website`, `information`, `image`, `imageh`, `imagew`, `thumb`, `thumbw`, `thumbh`, `short`, `ordering`, `catid`, `list_show`, `published`) VALUES
(1, NULL, NULL, 'Tom Fuller', 'Pastor', '', '', 'www.CalvaryChapelNewberg.org', '<p>Tom is the senior pastor</p>', '', '', '', '', '', '', 'Tom is the senior pastor', 0, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_templates`
--

DROP TABLE IF EXISTS `#__bsms_templates`;
CREATE TABLE IF NOT EXISTS `#__bsms_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `tmpl` longtext NOT NULL,
  `published` int(1) NOT NULL DEFAULT '1',
  `params` longtext,
  `title` text,
  `text` text,
  `pdf` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

--
-- Dumping data for table `#__bsms_templates`
--

INSERT INTO `#__bsms_templates` (`id`, `type`, `tmpl`, `published`, `params`, `title`, `text`, `pdf`) VALUES
(1, 'tmplList', '', 1, 'itemslimit=10\r\ncompatibilityMode=0\r\nstudieslisttemplateid=1\r\ndetailstemplateid=1\r\nteachertemplateid=1\r\nserieslisttemplateid=1\r\nseriesdetailtemplateid=1\r\nteacher_id=\r\nshow_teacher_list=0\r\nmult_teachers=\r\nseries_id=0\r\nmult_series=\r\nbooknumber=0\r\nmult_books=\r\ntopic_id=0\r\nmult_topics=\r\nmessagetype=0\r\nmult_messagetype=\r\nlocations=0\r\nmult_locations=\r\ndefault_order=DESC\r\nshow_page_image=1\r\ntooltip=1\r\nshow_verses=0\r\nstylesheet=\r\ndate_format=2\r\nduration_type=1\r\nuseavr=0\r\npopuptype=window\r\nmedia_player=0\r\nplayer_width=290\r\nshow_filesize=1\r\nstore_page=flypage.tpl\r\nshow_page_title=1\r\npage_title=Bible Studies\r\nuse_headers_list=1\r\nlist_intro=\r\nintro_show=1\r\nlistteachers=1\r\nteacherlink=1\r\ndetails_text=Study Details\r\nshow_book_search=1\r\nshow_teacher_search=1\r\nshow_series_search=1\r\nshow_type_search=1\r\nshow_year_search=1\r\nshow_order_search=1\r\nshow_topic_search=1\r\nshow_locations_search=1\r\ntip_title=Sermon Information\r\ntip_item1_title=Title\r\ntip_item1=5\r\ntip_item2_title=Details\r\ntip_item2=6\r\ntip_item3_title=Teacher\r\ntip_item3=7\r\ntip_item4_title=Reference\r\ntip_item4=1\r\ntip_item5_title=Date\r\ntip_item5=10\r\nrow1col1=18\r\nr1c1custom=\r\nr1c1span=1\r\nrowspanr1c1=1\r\nlinkr1c1=0\r\nrow1col2=5\r\nr1c2custom=\r\nr1c2span=1\r\nrowspanr1c2=1\r\nlinkr1c2=1\r\nrow1col3=1\r\nr1c3custom=\r\nr1c3span=1\r\nrowspanr1c3=1\r\nlinkr1c3=0\r\nrow1col4=20\r\nr1c4custom=\r\nrowspanr1c4=1\r\nlinkr1c4=0\r\nrow2col1=6\r\nr2c1custom=\r\nr2c1span=4\r\nrowspanr2c1=1\r\nlinkr2c1=0\r\nrow2col2=0\r\nr2c2custom=\r\nr2c2span=1\r\nrowspanr2c2=1\r\nlinkr2c2=0\r\nrow2col3=0\r\nr2c3custom=\r\nr2c3span=1\r\nrowspanr2c3=1\r\nlinkr2c3=0\r\nrow2col4=0\r\nr2c4custom=\r\nrowspanr2c4=1\r\nlinkr2c4=0\r\nrow3col1=0\r\nr3c1custom=\r\nr3c1span=1\r\nrowspanr3c1=1\r\nlinkr3c1=0\r\nrow3col2=0\r\nr3c2custom=\r\nr3c2span=1\r\nlinkr3c2=0\r\nrow3col3=0\r\nr3c3custom=\r\nr3c3span=1\r\nrowspanr3c3=1\r\nlinkr3c3=0\r\nrow3col4=0\r\nr3c4custom=\r\nrowspanr3c4=1\r\nlinkr3c4=0\r\nrow4col1=0\r\nr4c1custom=\r\nr4c1span=1\r\nrowspanr4c1=1\r\nlinkr4c1=0\r\nrow4col2=0\r\nr4c2custom=\r\nr4c2span=1\r\nrowspanr4c2=1\r\nlinkr4c2=0\r\nrow4col3=0\r\nr4c3custom=\r\nr4c3span=1\r\nrowspanr4c3=1\r\nlinkr4c3=0\r\nrow4col4=0\r\nr4c4custom=\r\nrowspanr4c4=1\r\nlinkr4c4=0\r\nshow_print_view=1\r\nshow_pdf_view=1\r\nshow_teacher_view=1\r\nshow_passage_view=1\r\nuse_headers_view=1\r\nlist_items_view=0\r\ntitle_line_1=1\r\ncustomtitle1=\r\ntitle_line_2=4\r\ncustomtitle2=\r\nview_link=1\r\nlink_text=Return to Studies List\r\nshow_scripture_link=1\r\nshow_comments=0\r\ncomment_access=1\r\ncomment_publish=0\r\nuse_captcha=1\r\nemail_comments=1\r\nrecipient=\r\nsubject=Comments on studies\r\nbody=Comments entered.\r\nmoduleitems=3\r\nteacher_title=Our Teachers\r\nshow_teacher_studies=1\r\nstudies=5\r\nlabel_teacher=Latest Messages\r\nseries_title=Our Series\r\nshow_series_title=1\r\nshow_page_image_series=1\r\nseries_show_description=1\r\nseries_characters=\r\nsearch_series=1\r\nseries_limit=5\r\nserieselement1=1\r\nseriesislink1=1\r\nserieselement2=1\r\nseriesislink2=1\r\nserieselement3=1\r\nseriesislink3=1\r\nserieselement4=1\r\nseriesislink4=1\r\nseries_detail_sort=1\r\nseries_detail_order=DESC\r\nseries_detail_show_link=1\r\nseries_detail_limit=\r\nseries_list_return=1\r\nseries_detail_1=5\r\nseries_detail_islink1=1\r\nseries_detail_2=7\r\nseries_detail_islink2=0\r\nseries_detail_3=10\r\nseries_detail_islink3=0\r\nseries_detail_4=20\r\nseries_detail_islink4=0\r\n\r\n', 'Default', 'textfile24.png', 'pdf24.png');

-- --------------------------------------------------------

--
-- Table structure for table `#__bsms_topics`
--

DROP TABLE IF EXISTS `#__bsms_topics`;
CREATE TABLE IF NOT EXISTS `#__bsms_topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_text` text,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=114 ;

--
-- Dumping data for table `#__bsms_topics`
--

INSERT INTO `#__bsms_topics` (`id`, `topic_text`, `published`) VALUES
(1, 'Abortion', 1),
(3, 'Addiction', 1),
(4, 'Afterlife', 1),
(5, 'Apologetics', 1),
(7, 'Baptism', 1),
(8, 'Basics of Christianity', 1),
(9, 'Becoming a Christian', 1),
(10, 'Bible', 1),
(37, 'Blended Family Relationships', 1),
(12, 'Children', 1),
(13, 'Christ', 1),
(14, 'Christian Character/Fruits', 1),
(15, 'Christian Values', 1),
(16, 'Christmas Season', 1),
(17, 'Church', 1),
(18, 'Communication', 1),
(19, 'Communion / Lords Supper', 1),
(21, 'Creation', 1),
(23, 'Cults', 1),
(113, 'Da Vinci Code', 1),
(24, 'Death', 1),
(26, 'Descriptions of God', 1),
(27, 'Disciples', 1),
(28, 'Discipleship', 1),
(30, 'Divorce', 1),
(32, 'Easter Season', 1),
(33, 'Emotions', 1),
(34, 'Entertainment', 1),
(35, 'Evangelism', 1),
(36, 'Faith', 1),
(103, 'Family', 1),
(39, 'Forgiving Others', 1),
(104, 'Freedom', 1),
(41, 'Friendship', 1),
(42, 'Fulfillment in Life', 1),
(43, 'Fund-raising Rally', 1),
(44, 'Funerals', 1),
(45, 'Giving', 1),
(2, 'Gods Activity', 1),
(6, 'Gods Attributes', 1),
(40, 'Gods Forgiveness', 1),
(58, 'Gods Love', 1),
(65, 'Gods Nature', 1),
(46, 'Gods Will', 1),
(47, 'Hardship of Life', 1),
(107, 'Holidays', 1),
(48, 'Holy Spirit', 1),
(111, 'Hot Topics', 1),
(11, 'Jesus Birth', 1),
(22, 'Jesus Cross/Final Week', 1),
(29, 'Jesus Divinity', 1),
(50, 'Jesus Humanity', 1),
(56, 'Jesus Life', 1),
(61, 'Jesus Miracles', 1),
(84, 'Jesus Resurrection', 1),
(93, 'Jesus Teaching', 1),
(52, 'Kingdom of God', 1),
(55, 'Leadership Essentials', 1),
(57, 'Love', 1),
(59, 'Marriage', 1),
(109, 'Men', 1),
(82, 'Messianic Prophecies', 1),
(62, 'Misconceptions of Christianity', 1),
(63, 'Money', 1),
(112, 'Narnia', 1),
(66, 'Our Need for God', 1),
(69, 'Parables', 1),
(70, 'Paranormal', 1),
(71, 'Parenting', 1),
(73, 'Poverty', 1),
(74, 'Prayer', 1),
(76, 'Prominent N.T. Men', 1),
(77, 'Prominent N.T. Women', 1),
(78, 'Prominent O.T. Men', 1),
(79, 'Prominent O.T. Women', 1),
(83, 'Racism', 1),
(85, 'Second Coming', 1),
(86, 'Sexuality', 1),
(87, 'Sin', 1),
(88, 'Singleness', 1),
(89, 'Small Groups', 1),
(108, 'Special Services', 1),
(90, 'Spiritual Disciplines', 1),
(91, 'Spiritual Gifts', 1),
(105, 'Stewardship', 1),
(92, 'Supernatural', 1),
(94, 'Temptation', 1),
(95, 'Ten Commandments', 1),
(97, 'Truth', 1),
(98, 'Twelve Apostles', 1),
(100, 'Weddings', 1),
(110, 'Women', 1),
(101, 'Workplace Issues', 1),
(102, 'World Religions', 1),
(106, 'Worship', 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
