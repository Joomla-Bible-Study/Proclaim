CREATE TABLE IF NOT EXISTS `#__bsms_studies`(
`id` INT(11) NOT NULL AUTO_INCREMENT,
`studydate` datetime default NULL,
`teacher_id` INT(11) NULL default '1',
`studynumber` VARCHAR(100) NULL default '',
`booknumber` INT(3) NULL default '101',
`chapter_begin` INT(3) NULL,
`verse_begin` INT(3) NULL,
`chapter_end` INT(3) NULL, 
`verse_end` INT(3) NULL,
`studytitle` text NULL default '',
`studyintro` text NULL default '', 
`messagetype` VARCHAR(100) NULL default '1',
`series_id` INT(3) NULL,
`topics_id` INT(3) NULL,
`studytext` TEXT NULL default '',
`media1_id` INT(11) NULL,
`media1_server` VARCHAR(250) NULL,
`media1_path` VARCHAR(250) NULL,
`media1_special` VARCHAR(250) NULL,
`media1_filename` TEXT NULL,
`media1_size` TEXT NULL,
`media1_show` TINYINT(1) NULL default '0',
`media2_id` INT(11) NULL default '0',
`media2_server` VARCHAR(250) NULL,
`media2_path` VARCHAR(250) NULL,
`media2_special` VARCHAR(250) NULL,
`media2_filename` TEXT NULL,
`media2_size` TEXT NULL,
`media2_show` TINYINT(1) NULL default '0',
`media3_id` INT(11) NULL,
`media3_server` VARCHAR(250) NULL,
`media3_path` VARCHAR(250) NULL,
`media3_special` VARCHAR(250) NULL,
`media3_filename` TEXT NULL,
`media3_size` TEXT NULL,
`media3_show` TINYINT(1) NULL default '0',
`media4_id` INT(11) NULL,
`media4_server` VARCHAR(250) NULL,
`media4_path` VARCHAR(250) NULL,
`media4_special` VARCHAR(250) NULL,
`media4_filename` TEXT NULL,
`media4_size` TEXT NULL,
`media4_show` TINYINT(1) NULL default '0',
`media5_id` INT(11) NULL,
`media5_server` VARCHAR(250) NULL,
`media5_path` VARCHAR(250) NULL,
`media5_special` VARCHAR(250) NULL,
`media5_filename` TEXT NULL,
`media5_size` TEXT NULL,
`media5_show` TINYINT(1) NULL default '0',
`media6_id` INT(11) NULL,
`media6_server` VARCHAR(250) NULL,
`media6_path` VARCHAR(250) NULL,
`media6_special` VARCHAR(250) NULL,
`media6_filename` TEXT NULL,
`media6_size` TEXT NULL,
`media6_show` TINYINT(1) NULL default '0',
`published` TINYINT(1) NOT NULL default '1',
PRIMARY KEY (`id`) )
TYPE=MyISAM CHARACTER SET `utf8`;
--
CREATE TABLE IF NOT EXISTS `#__bsms_series` (
`id` INT(3) NOT NULL AUTO_INCREMENT,
`series_text` text NULL default '',
`published` TINYINT(1) NOT NULL default '1',
PRIMARY KEY (`id`) )
TYPE=MyISAM CHARACTER SET `utf8`;
--
CREATE TABLE IF NOT EXISTS `#__bsms_servers` (
`id` INT(3) NOT NULL AUTO_INCREMENT,
`server_name` VARCHAR(250) NULL,
`server_path` VARCHAR(250) NULL, 
`published` TINYINT(1) NOT NULL default '1', 
PRIMARY KEY (`id`) )
TYPE=MyISAM CHARACTER SET `utf8`;
--
CREATE TABLE IF NOT EXISTS `#__bsms_teachers` (
`id` INT NOT NULL AUTO_INCREMENT,
`teachername` VARCHAR(250) NULL,
`published` TINYINT(1) NULL default '1',
PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`;
--
CREATE TABLE IF NOT EXISTS `#__bsms_message_type` (
`id` INT NOT NULL AUTO_INCREMENT,
`message_type` TEXT NULL,
`published` TINYINT(1) NOT NULL default '1',
PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`;
--
CREATE TABLE IF NOT EXISTS `#__bsms_folders` (
`id` INT NOT NULL AUTO_INCREMENT,
`foldername` VARCHAR(250) NULL,
`folderpath` VARCHAR(250) NULL,
`published` TINYINT(1) NOT NULL DEFAULT '1',
PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`;
--
CREATE TABLE IF NOT EXISTS `#__bsms_order` (
`id` INT NOT NULL AUTO_INCREMENT,
`value` VARCHAR(15) NULL,
`text` VARCHAR(15) NULL,
PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`;
--
CREATE TABLE IF NOT EXISTS `#__bsms_topics` (
`id` INT NOT NULL AUTO_INCREMENT,
`topic_text` TEXT NULL,
`published` TINYINT(1) NOT NULL DEFAULT '1',
PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`;
--
INSERT IGNORE INTO `#__bsms_order` VALUES (1, 'ASC', 'Ascending');
INSERT IGNORE INTO `#__bsms_order` VALUES (2, 'DESC', 'Descending');
CREATE TABLE IF NOT EXISTS `#__bsms_search` (
`id` INT NOT NULL AUTO_INCREMENT,
`value` VARCHAR(15) NULL,
`text` VARCHAR(15) NULL,
PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`;
--
INSERT IGNORE INTO `#__bsms_search` VALUES (1, 'studytitle', 'Title');
INSERT IGNORE INTO `#__bsms_search` VALUES (2, 'studytext', 'Details');
INSERT IGNORE INTO `#__bsms_search` VALUES (3, 'studyintro', 'Description');
-- 
-- Table structure for table `#__bsms_books`
-- 
CREATE TABLE IF NOT EXISTS `#__bsms_books` (
  `id` int(3) NOT NULL auto_increment,
  `bookname` varchar(250) NULL,
  `booknumber` int(5) NULL,
  `published` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=`utf8` AUTO_INCREMENT=69 ;
--
-- Creating the schemaVersion Table
--
CREATE TABLE IF NOT EXISTS `#__bsms_schemaVersion`(
`id` int(3) NOT NULL auto_increment,
`schemaVersion` int(10),
PRIMARY KEY (`id`)
) TYPE=MyISAM CHARACTER SET `utf8`;
INSERT IGNORE INTO `#__bsms_schemaVersion` VALUES (1, 502);
--
CREATE TABLE IF NOT EXISTS `#__bsms_media` (
`id` INT(3) NOT NULL AUTO_INCREMENT,
`media_text` text NULL default '',
`media_image_name` VARCHAR(250) NULL,
`media_image_path` VARCHAR(250) NULL, 
`media_alttext` VARCHAR(250) NULL,
`published` TINYINT(1) NOT NULL default '1',
PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`;
--
-- Adding Podcast Table
--
CREATE TABLE IF NOT EXISTS `#__bsms_podcast` (
`id` INT(3) NOT NULL AUTO_INCREMENT,
`title` VARCHAR(100) NULL,
`website` VARCHAR(100) NULL,
`description` TEXT NULL,
`image` VARCHAR(130) NULL,
`imageh` INT(3) NULL,
`imagew` INT(3) NULL,
`author` VARCHAR(100) NULL,
`podcastimage` VARCHAR(130) NULL,
`podcastsearch` VARCHAR(250) NULL,
`filename` VARCHAR(150) NULL,
`language` VARCHAR(10) NULL default 'en-us',
`editor_name` VARCHAR(150) NULL,
`editor_email` VARCHAR(150) NULL,
`podcastlimit` INT(5) NULL,
`published` TINYINT(1) NOT NULL default '1',
PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`;
--
-- Adding Mime Type Table
--
CREATE TABLE IF NOT EXISTS `#__bsms_mimetype` (
`id` INT(3) NOT NULL AUTO_INCREMENT,
`mimetype` VARCHAR(50) NULL,
`mimetext` VARCHAR(50) NULL,
`published` TINYINT(1) NOT NULL default '1',
PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`;
--
-- Adding Media Table
--
CREATE TABLE IF NOT EXISTS `#__bsms_mediafiles` (
`id` INT(3) NOT NULL AUTO_INCREMENT,
`study_id` INT(5) NULL,
`media_image` INT(3) NULL,
`server` VARCHAR(250) NULL,
`path` VARCHAR(250) NULL,
`special` VARCHAR(250) NULL default '_self',
`filename` TEXT NULL,
`size` VARCHAR(50) NULL,
`mime_type` INT(3) NULL,
`podcast_id` INT(3) NULL,
`internal_viewer` TINYINT(1) NULL default '0',
`mediacode` VARCHAR(250) NULL,
`ordering` INT(11) NOT NULL default '0',
`createdate` DATETIME default NULL,
`published` TINYINT(1) NOT NULL default '1',
PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`;
--
-- Adding a Comments table
--
CREATE TABLE IF NOT EXISTS `#__bsms_comments` (
`id` INT(3) NOT NULL AUTO_INCREMENT,
`published` TINYINT(1) NOT NULL default '0',
`study_id` INT(11) NOT NULL,
`user_id` INT(11) NOT NULL,
`full_name` VARCHAR(50) NOT NULL,
`user_email` VARCHAR(100) NOT NULL,
`comment_date` DATETIME NOT NULL,
`comment_text` TEXT NOT NULL,
PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`;
-- 
-- Dumping data for table `#__bsms_books`
-- 

INSERT IGNORE INTO `#__bsms_books` VALUES (1, 'Genesis', 101, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (2, 'Exodus', 102, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (3, 'Leviticus', 103, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (4, 'Numbers', 104, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (5, 'Deuteronomy', 105, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (6, 'Joshua', 106, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (7, 'Judges', 107, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (8, 'Ruth', 108, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (9, '1Samuel', 109, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (10, '2Samuel', 110, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (11, '1Kings', 111, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (12, '2Kings', 112, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (13, '1Chronicles', 113, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (14, '2Chronicles', 114, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (15, 'Ezra', 115, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (16, 'Nehemiah', 116, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (17, 'Esther', 117, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (18, 'Job', 118, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (19, 'Psalm', 119, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (20, 'Proverbs', 120, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (21, 'Ecclesiastes', 121, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (22, 'Song of Solomon', 122, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (23, 'Isaiah', 123, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (24, 'Jeremiah', 124, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (25, 'Lamentations', 125, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (26, 'Ezekiel', 126, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (27, 'Daniel', 127, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (28, 'Hosea', 128, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (29, 'Joel', 129, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (30, 'Amos', 130, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (31, 'Obadiah', 131, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (32, 'Jonah', 132, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (33, 'Micah', 133, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (34, 'Nahum', 134, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (35, 'Habakkuk', 135, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (36, 'Zephaniah', 136, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (37, 'Haggai', 137, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (38, 'Zechariah', 138, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (39, 'Malachi', 139, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (40, 'Matthew', 140, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (41, 'Mark', 141, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (42, 'Luke', 142, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (43, 'John', 143, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (44, 'Acts', 144, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (45, 'Romans', 145, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (46, '1Corinthians', 146, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (47, '2Corinthians', 147, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (48, 'Galatians', 148, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (49, 'Ephesians', 149, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (50, 'Philippians', 150, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (51, 'Colossians', 151, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (52, '1Thessalonians', 152, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (53, '2Thessalonians', 153, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (54, '1Timothy', 154, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (55, '2Timothy', 155, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (56, 'Titus', 156, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (57, 'Philemon', 157, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (58, 'Hebrews', 158, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (59, 'James', 159, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (60, '1Peter', 160, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (61, '2Peter', 161, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (62, '1John', 162, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (63, '2John', 163, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (64, '3John', 164, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (65, 'Jude', 165, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (66, 'Revelation', 166, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (67, 'Topical', 167, 1);
INSERT IGNORE INTO `#__bsms_books` VALUES (68, 'Holiday', 168, 1);
INSERT IGNORE INTO `#__bsms_topics` VALUES (1,'Abortion',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (3,'Addiction',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (4,'Afterlife',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (5,'Apologetics',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (7,'Baptism',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (8,'Basics of Christianity',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (9,'Becoming a Christian',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (10,'Bible',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (37,'Blended Family Relationships',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (12,'Children',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (13,'Christ',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (14,'Christian Character/Fruits',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (15,'Christian Values',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (16,'Christmas Season',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (17,'Church',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (18,'Communication',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (19,'Communion / Lords Supper',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (21,'Creation',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (23,'Cults',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (113,'Da Vinci Code',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (24,'Death',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (26,'Descriptions of God',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (27,'Disciples',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (28,'Discipleship',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (30,'Divorce',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (32,'Easter Season',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (33,'Emotions',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (34,'Entertainment',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (35,'Evangelism',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (36,'Faith',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (103,'Family',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (39,'Forgiving Others',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (104,'Freedom',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (41,'Friendship',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (42,'Fulfillment in Life',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (43,'Fund-raising Rally',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (44,'Funerals',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (45,'Giving',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (2,'Gods Activity',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (6,'Gods Attributes',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (40,'Gods Forgiveness',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (58,'Gods Love',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (65,'Gods Nature',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (46,'Gods Will',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (47,'Hardship of Life',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (107,'Holidays',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (48,'Holy Spirit',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (111,'Hot Topics',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (11,'Jesus Birth',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (22,'Jesus Cross/Final Week',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (29,'Jesus Divinity',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (50,'Jesus Humanity',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (56,'Jesus Life',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (61,'Jesus Miracles',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (84,'Jesus Resurrection',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (93,'Jesus Teaching',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (52,'Kingdom of God',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (55,'Leadership Essentials',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (57,'Love',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (59,'Marriage',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (109,'Men',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (82,'Messianic Prophecies',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (62,'Misconceptions of Christianity',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (63,'Money',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (112,'Narnia',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (66,'Our Need for God',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (69,'Parables',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (70,'Paranormal',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (71,'Parenting',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (73,'Poverty',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (74,'Prayer',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (76,'Prominent N.T. Men',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (77,'Prominent N.T. Women',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (78,'Prominent O.T. Men',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (79,'Prominent O.T. Women',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (83,'Racism',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (85,'Second Coming',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (86,'Sexuality',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (87,'Sin',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (88,'Singleness',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (89,'Small Groups',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (108,'Special Services',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (90,'Spiritual Disciplines',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (91,'Spiritual Gifts',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (105,'Stewardship',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (92,'Supernatural',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (94,'Temptation',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (95,'Ten Commandments',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (97,'Truth',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (98,'Twelve Apostles',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (100,'Weddings',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (110,'Women',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (101,'Workplace Issues',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (102,'World Religions',1);
  INSERT IGNORE INTO `#__bsms_topics` VALUES (106,'Worship',1);
-- 
-- Dumping data for table `#__bsms_media`
-- 
DELETE FROM #__bsms_media WHERE id = 2 LIMIT 1;
DELETE FROM #__bsms_media WHERE id = 3 LIMIT 1;
DELETE FROM #__bsms_media WHERE id = 4 LIMIT 1;
DELETE FROM #__bsms_media WHERE id = 6 LIMIT 1;
DELETE FROM #__bsms_media WHERE id = 7 LIMIT 1;
DELETE FROM #__bsms_media WHERE id = 8 LIMIT 1;
DELETE FROM #__bsms_media WHERE id = 9 LIMIT 1;
DELETE FROM #__bsms_media WHERE id = 10 LIMIT 1;
DELETE FROM #__bsms_media WHERE id = 11 LIMIT 1;
DELETE FROM #__bsms_media WHERE id = 12 LIMIT 1;
INSERT IGNORE INTO `#__bsms_media` VALUES (2, 'mp3 compressed audio file', 'mp3', 'components/com_biblestudy/images/speaker24.png', 'mp3 audio file', 1);
INSERT IGNORE INTO `#__bsms_media` VALUES (3, 'Video', 'Video File', 'components/com_biblestudy/images/video24.png', 'Video File', 1);
INSERT IGNORE INTO `#__bsms_media` VALUES (4, 'm4v', 'Video Podcast', 'components/com_biblestudy/images/podcast-video24.png', 'Video Podcast', 1);
INSERT IGNORE INTO `#__bsms_media` VALUES (6, 'Streaming Audio', 'Streaming Audio', 'components/com_biblestudy/images/streamingaudio24.png', 'Streaming Audio', 1);
INSERT IGNORE INTO `#__bsms_media` VALUES (7, 'Streaming Video', 'Streaming Video', 'components/com_biblestudy/images/streamingvideo24.png', 'Streaming Video', 1);
INSERT IGNORE INTO `#__bsms_media` VALUES (8, 'Real Audio', 'Real Audio', 'components/com_biblestudy/images/realplayer24.png', 'Real Audio', 1);
INSERT IGNORE INTO `#__bsms_media` VALUES (9, 'Windows Media Audio', 'Windows Media Audio', 'components/com_biblestudy/images/windows-media24.png', 'Windows Media File', 1);
INSERT IGNORE INTO `#__bsms_media` VALUES (10, 'Podcast Audio', 'Podcast Audio', 'components/com_biblestudy/images/podcast-audio24.png', 'Podcast Audio', 1);
INSERT IGNORE INTO `#__bsms_media` VALUES (11, 'CD', 'CD', 'components/com_biblestudy/image/cd.png', 'CD', 1);
INSERT IGNORE INTO `#__bsms_media` VALUES (12, 'DVD', 'DVD', 'components/com_biblestudy/image/dvd.png', 'DVD', 1);
--
-- Dumping Data into Mime Type
--
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (1,'audio/mpeg3','MP3 Audio',1);
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (2,'audio/x-pn-realaudio','Real Audio',1);
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (3,'video/x-m4v','Podcast Video m4v',1);
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (4,'application/vnd.rn-realmedia','Real Media rm',1);
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (5,'audio/x-ms-wma','Windows Media Audio WMA',1);
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (6,'text/html','Text',1);
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (7,'audio/x-wav','Windows wav File',1);
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (8,'audio/x-pn-realaudio-plugin',' Real audio Plugin.rpm',1);
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (9,'audio/x-pn-realaudio','Real Media File .rm',1);
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (10,'audio/x-realaudio','Rea Audio File .ra',1);
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (11,'audio/x-pn-realaudio','Read Audio File.ram',1);
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (12,'video/mpeg',' Mpeg video .mpg',1);
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (13,'audio/mpeg','Video .mp2 File',1);
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (14,'video/x-msvideo',' Video .avi File',1);
INSERT IGNORE INTO `#__bsms_mimetype` VALUES (15,'video/x-flv .flv',' Flash Video FLV',1);



