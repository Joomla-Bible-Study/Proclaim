
--
-- Table structure for table `#__bsms_admin`
--

DROP TABLE IF EXISTS `#__bsms_admin`;
CREATE TABLE `#__bsms_admin` (
  `id` int(11) NOT NULL,
  `podcast` text,
  `series` text,
  `study` text,
  `teacher` text,
  `media` text,
  `download` text,
  `main` text,
  `showhide` char(255) DEFAULT NULL,
  `params` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;


--
-- Dumping data for table `#__bsms_admin`
--

INSERT INTO #__bsms_admin SET `id`='1',`podcast`='',`series`='- No Image -',`study`='- No Image -',`teacher`='- No Image -',`media`='speaker24.png',`download`='download.png',`main`='openbible.png',`showhide`='- Default Image -',`params`='compat_mode=0\ndrop_tables=0\nadmin_store=0\nstudylistlimit=\nshow_location_media=0\npopular_limit=\ncharacter_filter=1\nformat_popular=1\nsocialnetworking=0\nsharetype=1\nseries_imagefolder=\nmedia_imagefolder=\nteachers_imagefolder=\nstudy_images=\nlocation_id=0\nteacher_id=1\nseries_id=0\nbooknumber=140\ntopic_id=0\nmessagetype=0\ndownload=1\ntarget=0\nserver=1\npath=1\npodcast=0\nmime=1\nallow_entry_study=0\nentry_access=23\nstudy_publish=0\nitemidlinktype=0\nitemidlinkview=studieslist\nitemidlinknumber=\n\n';

-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_books`
--

DROP TABLE IF EXISTS `#__bsms_books`;
CREATE TABLE `#__bsms_books` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `bookname` varchar(250) DEFAULT NULL,
  `booknumber` int(5) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=69;


--
-- Dumping data for table `#__bsms_books`
--

INSERT INTO #__bsms_books SET `id`='1',`bookname`='Genesis',`booknumber`='101',`published`='1';
INSERT INTO #__bsms_books SET `id`='2',`bookname`='Exodus',`booknumber`='102',`published`='1';
INSERT INTO #__bsms_books SET `id`='3',`bookname`='Leviticus',`booknumber`='103',`published`='1';
INSERT INTO #__bsms_books SET `id`='4',`bookname`='Numbers',`booknumber`='104',`published`='1';
INSERT INTO #__bsms_books SET `id`='5',`bookname`='Deuteronomy',`booknumber`='105',`published`='1';
INSERT INTO #__bsms_books SET `id`='6',`bookname`='Joshua',`booknumber`='106',`published`='1';
INSERT INTO #__bsms_books SET `id`='7',`bookname`='Judges',`booknumber`='107',`published`='1';
INSERT INTO #__bsms_books SET `id`='8',`bookname`='Ruth',`booknumber`='108',`published`='1';
INSERT INTO #__bsms_books SET `id`='9',`bookname`='1Samuel',`booknumber`='109',`published`='1';
INSERT INTO #__bsms_books SET `id`='10',`bookname`='2Samuel',`booknumber`='110',`published`='1';
INSERT INTO #__bsms_books SET `id`='11',`bookname`='1Kings',`booknumber`='111',`published`='1';
INSERT INTO #__bsms_books SET `id`='12',`bookname`='2Kings',`booknumber`='112',`published`='1';
INSERT INTO #__bsms_books SET `id`='13',`bookname`='1Chronicles',`booknumber`='113',`published`='1';
INSERT INTO #__bsms_books SET `id`='14',`bookname`='2Chronicles',`booknumber`='114',`published`='1';
INSERT INTO #__bsms_books SET `id`='15',`bookname`='Ezra',`booknumber`='115',`published`='1';
INSERT INTO #__bsms_books SET `id`='16',`bookname`='Nehemiah',`booknumber`='116',`published`='1';
INSERT INTO #__bsms_books SET `id`='17',`bookname`='Esther',`booknumber`='117',`published`='1';
INSERT INTO #__bsms_books SET `id`='18',`bookname`='Job',`booknumber`='118',`published`='1';
INSERT INTO #__bsms_books SET `id`='19',`bookname`='Psalm',`booknumber`='119',`published`='1';
INSERT INTO #__bsms_books SET `id`='20',`bookname`='Proverbs',`booknumber`='120',`published`='1';
INSERT INTO #__bsms_books SET `id`='21',`bookname`='Ecclesiastes',`booknumber`='121',`published`='1';
INSERT INTO #__bsms_books SET `id`='22',`bookname`='Song of Solomon',`booknumber`='122',`published`='1';
INSERT INTO #__bsms_books SET `id`='23',`bookname`='Isaiah',`booknumber`='123',`published`='1';
INSERT INTO #__bsms_books SET `id`='24',`bookname`='Jeremiah',`booknumber`='124',`published`='1';
INSERT INTO #__bsms_books SET `id`='25',`bookname`='Lamentations',`booknumber`='125',`published`='1';
INSERT INTO #__bsms_books SET `id`='26',`bookname`='Ezekiel',`booknumber`='126',`published`='1';
INSERT INTO #__bsms_books SET `id`='27',`bookname`='Daniel',`booknumber`='127',`published`='1';
INSERT INTO #__bsms_books SET `id`='28',`bookname`='Hosea',`booknumber`='128',`published`='1';
INSERT INTO #__bsms_books SET `id`='29',`bookname`='Joel',`booknumber`='129',`published`='1';
INSERT INTO #__bsms_books SET `id`='30',`bookname`='Amos',`booknumber`='130',`published`='1';
INSERT INTO #__bsms_books SET `id`='31',`bookname`='Obadiah',`booknumber`='131',`published`='1';
INSERT INTO #__bsms_books SET `id`='32',`bookname`='Jonah',`booknumber`='132',`published`='1';
INSERT INTO #__bsms_books SET `id`='33',`bookname`='Micah',`booknumber`='133',`published`='1';
INSERT INTO #__bsms_books SET `id`='34',`bookname`='Nahum',`booknumber`='134',`published`='1';
INSERT INTO #__bsms_books SET `id`='35',`bookname`='Habakkuk',`booknumber`='135',`published`='1';
INSERT INTO #__bsms_books SET `id`='36',`bookname`='Zephaniah',`booknumber`='136',`published`='1';
INSERT INTO #__bsms_books SET `id`='37',`bookname`='Haggai',`booknumber`='137',`published`='1';
INSERT INTO #__bsms_books SET `id`='38',`bookname`='Zechariah',`booknumber`='138',`published`='1';
INSERT INTO #__bsms_books SET `id`='39',`bookname`='Malachi',`booknumber`='139',`published`='1';
INSERT INTO #__bsms_books SET `id`='40',`bookname`='Matthew',`booknumber`='140',`published`='1';
INSERT INTO #__bsms_books SET `id`='41',`bookname`='Mark',`booknumber`='141',`published`='1';
INSERT INTO #__bsms_books SET `id`='42',`bookname`='Luke',`booknumber`='142',`published`='1';
INSERT INTO #__bsms_books SET `id`='43',`bookname`='John',`booknumber`='143',`published`='1';
INSERT INTO #__bsms_books SET `id`='44',`bookname`='Acts',`booknumber`='144',`published`='1';
INSERT INTO #__bsms_books SET `id`='45',`bookname`='Romans',`booknumber`='145',`published`='1';
INSERT INTO #__bsms_books SET `id`='46',`bookname`='1Corinthians',`booknumber`='146',`published`='1';
INSERT INTO #__bsms_books SET `id`='47',`bookname`='2Corinthians',`booknumber`='147',`published`='1';
INSERT INTO #__bsms_books SET `id`='48',`bookname`='Galatians',`booknumber`='148',`published`='1';
INSERT INTO #__bsms_books SET `id`='49',`bookname`='Ephesians',`booknumber`='149',`published`='1';
INSERT INTO #__bsms_books SET `id`='50',`bookname`='Philippians',`booknumber`='150',`published`='1';
INSERT INTO #__bsms_books SET `id`='51',`bookname`='Colossians',`booknumber`='151',`published`='1';
INSERT INTO #__bsms_books SET `id`='52',`bookname`='1Thessalonians',`booknumber`='152',`published`='1';
INSERT INTO #__bsms_books SET `id`='53',`bookname`='2Thessalonians',`booknumber`='153',`published`='1';
INSERT INTO #__bsms_books SET `id`='54',`bookname`='1Timothy',`booknumber`='154',`published`='1';
INSERT INTO #__bsms_books SET `id`='55',`bookname`='2Timothy',`booknumber`='155',`published`='1';
INSERT INTO #__bsms_books SET `id`='56',`bookname`='Titus',`booknumber`='156',`published`='1';
INSERT INTO #__bsms_books SET `id`='57',`bookname`='Philemon',`booknumber`='157',`published`='1';
INSERT INTO #__bsms_books SET `id`='58',`bookname`='Hebrews',`booknumber`='158',`published`='1';
INSERT INTO #__bsms_books SET `id`='59',`bookname`='James',`booknumber`='159',`published`='1';
INSERT INTO #__bsms_books SET `id`='60',`bookname`='1Peter',`booknumber`='160',`published`='1';
INSERT INTO #__bsms_books SET `id`='61',`bookname`='2Peter',`booknumber`='161',`published`='1';
INSERT INTO #__bsms_books SET `id`='62',`bookname`='1John',`booknumber`='162',`published`='1';
INSERT INTO #__bsms_books SET `id`='63',`bookname`='2John',`booknumber`='163',`published`='1';
INSERT INTO #__bsms_books SET `id`='64',`bookname`='3John',`booknumber`='164',`published`='1';
INSERT INTO #__bsms_books SET `id`='65',`bookname`='Jude',`booknumber`='165',`published`='1';
INSERT INTO #__bsms_books SET `id`='66',`bookname`='Revelation',`booknumber`='166',`published`='1';
INSERT INTO #__bsms_books SET `id`='67',`bookname`='Topical',`booknumber`='167',`published`='1';
INSERT INTO #__bsms_books SET `id`='68',`bookname`='Holiday',`booknumber`='168',`published`='1';

-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_comments`
--

DROP TABLE IF EXISTS `#__bsms_comments`;
CREATE TABLE `#__bsms_comments` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `study_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `full_name` varchar(50) NOT NULL DEFAULT '',
  `user_email` varchar(100) NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;


--
-- Dumping data for table `#__bsms_comments`
--


-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_folders`
--

DROP TABLE IF EXISTS `#__bsms_folders`;
CREATE TABLE `#__bsms_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foldername` varchar(250) NOT NULL DEFAULT '',
  `folderpath` varchar(250) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11;


--
-- Dumping data for table `#__bsms_folders`
--

INSERT INTO #__bsms_folders SET `id`='1',`foldername`='/media/matthew/',`folderpath`='/media/matthew/',`published`='1';
INSERT INTO #__bsms_folders SET `id`='2',`foldername`='/media/deuteronomy/',`folderpath`='/media/deuteronomy/',`published`='1';
INSERT INTO #__bsms_folders SET `id`='8',`foldername`='/media/John/',`folderpath`='/media/John/',`published`='1';
INSERT INTO #__bsms_folders SET `id`='4',`foldername`='/media/Mark/',`folderpath`='/media/Mark/',`published`='1';
INSERT INTO #__bsms_folders SET `id`='10',`foldername`='Topical',`folderpath`='/media/Topical/',`published`='1';
INSERT INTO #__bsms_folders SET `id`='9',`foldername`='/media/Guest-Speakers/',`folderpath`='/media/Guest-Speakers/',`published`='1';

-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_locations`
--

DROP TABLE IF EXISTS `#__bsms_locations`;
CREATE TABLE `#__bsms_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_text` varchar(250) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2;


--
-- Dumping data for table `#__bsms_locations`
--


-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_media`
--

DROP TABLE IF EXISTS `#__bsms_media`;
CREATE TABLE `#__bsms_media` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `media_text` text,
  `media_image_name` varchar(250) NOT NULL DEFAULT '',
  `media_image_path` varchar(250) NOT NULL DEFAULT '',
  `path2` varchar(150) NOT NULL,
  `media_alttext` varchar(250) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15;


--
-- Dumping data for table `#__bsms_media`
--

INSERT INTO #__bsms_media SET `id`='2',`media_text`='mp3 compressed audio file',`media_image_name`='mp3',`media_image_path`='',`path2`='speaker24.png',`media_alttext`='mp3 audio file',`published`='1';
INSERT INTO #__bsms_media SET `id`='3',`media_text`='Video',`media_image_name`='Video File',`media_image_path`='',`path2`='video24.png',`media_alttext`='Video File',`published`='0';
INSERT INTO #__bsms_media SET `id`='4',`media_text`='m4v',`media_image_name`='Video Podcast',`media_image_path`='',`path2`='podcast-video24.png',`media_alttext`='Video Podcast',`published`='0';
INSERT INTO #__bsms_media SET `id`='6',`media_text`='Streaming Audio',`media_image_name`='Streaming Audio',`media_image_path`='',`path2`='streamingaudio24.png',`media_alttext`='Streaming Audio',`published`='0';
INSERT INTO #__bsms_media SET `id`='7',`media_text`='Streaming Video',`media_image_name`='Streaming Video',`media_image_path`='',`path2`='streamingvideo24.png',`media_alttext`='Streaming Video',`published`='0';
INSERT INTO #__bsms_media SET `id`='8',`media_text`='Real Audio',`media_image_name`='Real Audio',`media_image_path`='',`path2`='realplayer24.png',`media_alttext`='Real Audio',`published`='0';
INSERT INTO #__bsms_media SET `id`='9',`media_text`='Windows Media Audio',`media_image_name`='Windows Media Audio',`media_image_path`='',`path2`='windows-media24.png',`media_alttext`='Windows Media File',`published`='0';
INSERT INTO #__bsms_media SET `id`='10',`media_text`='Podcast Audio',`media_image_name`='Podcast Audio',`media_image_path`='',`path2`='podcast-audio24.png',`media_alttext`='Podcast Audio',`published`='0';
INSERT INTO #__bsms_media SET `id`='11',`media_text`='CD',`media_image_name`='CD',`media_image_path`='',`path2`='cd.png',`media_alttext`='CD',`published`='0';
INSERT INTO #__bsms_media SET `id`='12',`media_text`='DVD',`media_image_name`='DVD',`media_image_path`='',`path2`='dvd.png',`media_alttext`='DVD',`published`='0';
INSERT INTO #__bsms_media SET `id`='13',`media_text`='Download',`media_image_name`='Download',`media_image_path`='',`path2`='download.png',`media_alttext`='Download',`published`='1';
INSERT INTO #__bsms_media SET `id`='14',`media_text`='Article',`media_image_name`='Article',`media_image_path`='',`path2`='textfile24.png',`media_alttext`='Article',`published`='0';

-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_mediafiles`
--

DROP TABLE IF EXISTS `#__bsms_mediafiles`;
CREATE TABLE `#__bsms_mediafiles` (
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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=51;


--
-- Dumping data for table `#__bsms_mediafiles`
--

INSERT INTO #__bsms_mediafiles SET `id`='42',`study_id`='41',`media_image`='2',`server`='1',`path`='10',`special`='0',`filename`='MarriageSeminarPt3.mp3',`size`='28000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-09-29 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='125',`plays`='28',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='43',`study_id`='42',`media_image`='2',`server`='1',`path`='4',`special`='0',`filename`='LoveFromtheInsideOut.mp3',`size`='20000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-10-06 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='120',`plays`='24',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='44',`study_id`='43',`media_image`='2',`server`='1',`path`='4',`special`='0',`filename`='LookingForFaith.mp3',`size`='21000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-10-13 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='146',`plays`='20',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='45',`study_id`='44',`media_image`='2',`server`='1',`path`='2',`special`='0',`filename`='YoureNotCursed.mp3',`size`='22000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-10-16 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='143',`plays`='35',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='41',`study_id`='40',`media_image`='2',`server`='1',`path`='2',`special`='0',`filename`='Deuteronomy21.mp3',`size`='22000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-09-25 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='75',`plays`='8',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='40',`study_id`='39',`media_image`='2',`server`='1',`path`='10',`special`='0',`filename`='AWorkingMarriagePt2.mp3',`size`='31000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-09-25 11:58:21',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='78',`plays`='20',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='39',`study_id`='38',`media_image`='2',`server`='1',`path`='9',`special`='0',`filename`='PastorTony.mp3',`size`='26000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-09-11 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='65',`plays`='7',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='12',`study_id`='13',`media_image`='2',`server`='1',`path`='9',`special`='0',`filename`='Ken_Graves.mp3',`size`='34000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-05-04 00:00:00',`link_type`='1',`hits`=NULL,`published`='0',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='53',`plays`='12',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='13',`study_id`='12',`media_image`='2',`server`='1',`path`='9',`special`='0',`filename`='Samy_Tanagho.mp3',`size`='',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-04-28 00:00:00',`link_type`='1',`hits`=NULL,`published`='0',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='33',`plays`='10',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='14',`study_id`='14',`media_image`='2',`server`='1',`path`='9',`special`='0',`filename`='Andre_Bribiesca.mp3',`size`='29000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-05-06 00:00:00',`link_type`='1',`hits`=NULL,`published`='0',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='47',`plays`='7',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='38',`study_id`='37',`media_image`='2',`server`='1',`path`='10',`special`='0',`filename`='Working_Marriage.mp3',`size`='25000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-09-08 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='127',`plays`='24',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='37',`study_id`='36',`media_image`='2',`server`='1',`path`='10',`special`='0',`filename`='Outreach.mp3',`size`='4000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-08-30 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='377',`plays`='39',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='36',`study_id`='35',`media_image`='2',`server`='1',`path`='2',`special`='0',`filename`='OldSchoolConsideration.mp3',`size`='22000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-08-28 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='108',`plays`='14',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='35',`study_id`='34',`media_image`='2',`server`='1',`path`='4',`special`='0',`filename`='PrayerIgnitesLove.mp3',`size`='20000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-08-25 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='126',`plays`='26',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='19',`study_id`='19',`media_image`='2',`server`='1',`path`='4',`special`='0',`filename`='Hear_and_Heed.mp3',`size`='28000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-06-09 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='180',`plays`='26',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='21',`study_id`='21',`media_image`='2',`server`='1',`path`='2',`special`='0',`filename`='Remember_Your_Deliverance.mp3.mp3',`size`='44669337',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-07-09 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='141',`plays`='23',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='25',`study_id`='24',`media_image`='2',`server`='1',`path`='4',`special`='0',`filename`='Breaking_Out.mp3',`size`='53162803',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-07-14 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='136',`plays`='18',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='23',`study_id`='22',`media_image`='2',`server`='1',`path`='4',`special`='0',`filename`='Active_Faith.mp3',`size`='47185920',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-07-09 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='144',`plays`='21',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='24',`study_id`='23',`media_image`='2',`server`='1',`path`='2',`special`='0',`filename`='Right_Judgement.mp3',`size`='38168166',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-07-10 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='147',`plays`='15',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='26',`study_id`='25',`media_image`='2',`server`='1',`path`='2',`special`='0',`filename`='Giving_Your_Best.mp3',`size`='44774195',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-07-17 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='135',`plays`='14',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='27',`study_id`='26',`media_image`='2',`server`='1',`path`='4',`special`='0',`filename`='Dying_For_Jesus_Christ.mp3',`size`='43935334',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-07-21 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='155',`plays`='17',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='28',`study_id`='27',`media_image`='2',`server`='1',`path`='2',`special`='0',`filename`='Drop_The_World.mp3',`size`='54735667',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-07-24 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='147',`plays`='13',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='29',`study_id`='28',`media_image`='2',`server`='1',`path`='4',`special`='0',`filename`='Kingdom_Provisions.mp3',`size`='51275366',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-07-28 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='145',`plays`='17',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='30',`study_id`='29',`media_image`='2',`server`='1',`path`='2',`special`='0',`filename`='KeepItRunningKeepItTrue.mp3',`size`='44000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-07-31 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='139',`plays`='19',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='31',`study_id`='30',`media_image`='2',`server`='1',`path`='9',`special`='0',`filename`='David_Diaz.mp3',`size`='36000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-08-07 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='96',`plays`='24',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='32',`study_id`='31',`media_image`='2',`server`='1',`path`='10',`special`='0',`filename`='Singles_Seminar.mp3',`size`='42000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-08-09 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='107',`plays`='15',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='33',`study_id`='32',`media_image`='2',`server`='1',`path`='4',`special`='0',`filename`='PrayerFaithHealing.mp3',`size`='42000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-08-18 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='131',`plays`='14',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='34',`study_id`='33',`media_image`='2',`server`='1',`path`='2',`special`='0',`filename`='GodisourRefuge_pt2.mp3',`size`='36000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-08-21 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='127',`plays`='22',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='47',`study_id`='46',`media_image`='2',`server`='1',`path`='2',`special`='0',`filename`='IsCleanlinessNextToGodliness.mp3',`size`='19000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-10-23 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='177',`plays`='43',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='48',`study_id`='47',`media_image`='2',`server`='1',`path`='2',`special`='0',`filename`='InResponseLove.mp3',`size`='27000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-11-20 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='152',`plays`='47',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';
INSERT INTO #__bsms_mediafiles SET `id`='50',`study_id`='48',`media_image`='2',`server`='1',`path`='4',`special`='0',`filename`='WhoDoYouSayThatIAm.mp3',`size`='22000000',`mime_type`='1',`podcast_id`=NULL,`internal_viewer`='0',`mediacode`='',`ordering`='0',`createdate`='2013-11-17 00:00:00',`link_type`='1',`hits`=NULL,`published`='1',`docMan_id`='0',`article_id`='0',`comment`='',`virtueMart_id`='0',`downloads`='150',`plays`='26',`params`='player=100\ninternal_popup=3\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n\n';

-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_message_type`
--

DROP TABLE IF EXISTS `#__bsms_message_type`;
CREATE TABLE `#__bsms_message_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_type` text NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2;


--
-- Dumping data for table `#__bsms_message_type`
--


-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_mimetype`
--

DROP TABLE IF EXISTS `#__bsms_mimetype`;
CREATE TABLE `#__bsms_mimetype` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `mimetype` varchar(50) DEFAULT NULL,
  `mimetext` varchar(50) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16;


--
-- Dumping data for table `#__bsms_mimetype`
--

INSERT INTO #__bsms_mimetype SET `id`='1',`mimetype`='audio/mpeg3',`mimetext`='MP3 Audio',`published`='1';
INSERT INTO #__bsms_mimetype SET `id`='3',`mimetype`='video/x-m4v',`mimetext`='Podcast Video m4v',`published`='0';
INSERT INTO #__bsms_mimetype SET `id`='5',`mimetype`='audio/x-ms-wma',`mimetext`='Windows Media Audio WMA',`published`='0';
INSERT INTO #__bsms_mimetype SET `id`='6',`mimetype`='text/html',`mimetext`='Text',`published`='0';
INSERT INTO #__bsms_mimetype SET `id`='7',`mimetype`='audio/x-wav',`mimetext`='Windows wav File',`published`='0';
INSERT INTO #__bsms_mimetype SET `id`='12',`mimetype`='video/mpeg',`mimetext`=' Mpeg video .mpg',`published`='0';
INSERT INTO #__bsms_mimetype SET `id`='13',`mimetype`='audio/mpeg',`mimetext`='Video .mp2 File',`published`='0';
INSERT INTO #__bsms_mimetype SET `id`='14',`mimetype`='video/x-msvideo',`mimetext`=' Video .avi File',`published`='0';
INSERT INTO #__bsms_mimetype SET `id`='15',`mimetype`='video/x-flv',`mimetext`=' Flash Video FLV',`published`='0';

-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_order`
--

DROP TABLE IF EXISTS `#__bsms_order`;
CREATE TABLE `#__bsms_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(15) DEFAULT '',
  `text` varchar(15) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3;


--
-- Dumping data for table `#__bsms_order`
--

INSERT INTO #__bsms_order SET `id`='1',`value`='ASC',`text`='Ascending';
INSERT INTO #__bsms_order SET `id`='2',`value`='DESC',`text`='Descending';

-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_podcast`
--

DROP TABLE IF EXISTS `#__bsms_podcast`;
CREATE TABLE `#__bsms_podcast` (
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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2;


--
-- Dumping data for table `#__bsms_podcast`
--

INSERT INTO #__bsms_podcast SET `id`='1',`title`='My Podcast',`website`='www.mywebsite.com',`description`='Podcast Description goes here',`image`='www.mywebsite.com/myimage.jpg',`imageh`='30',`imagew`='30',`author`='Pastor Billy',`podcastimage`='www.mywebsite.com/myimage.jpg',`podcastsearch`='jesus',`filename`='mypodcast.xml',`language`='en-us',`editor_name`='Jim Editor',`editor_email`='jim@mywebsite.com',`podcastlimit`='50',`published`='1',`episodetitle`=NULL,`custom`=NULL,`detailstemplateid`=NULL;

-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_search`
--

DROP TABLE IF EXISTS `#__bsms_search`;
CREATE TABLE `#__bsms_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(15) DEFAULT '',
  `text` varchar(15) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;


--
-- Dumping data for table `#__bsms_search`
--


-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_series`
--

DROP TABLE IF EXISTS `#__bsms_series`;
CREATE TABLE `#__bsms_series` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `series_text` text,
  `teacher` int(3) DEFAULT NULL,
  `description` text,
  `series_thumbnail` varchar(150) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2;


--
-- Dumping data for table `#__bsms_series`
--


-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_servers`
--

DROP TABLE IF EXISTS `#__bsms_servers`;
CREATE TABLE `#__bsms_servers` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `server_name` varchar(250) NOT NULL DEFAULT '',
  `server_path` varchar(250) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `server_type` char(5) NOT NULL DEFAULT 'local',
  `ftp_username` char(255) NOT NULL,
  `ftp_password` char(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2;


--
-- Dumping data for table `#__bsms_servers`
--

INSERT INTO #__bsms_servers SET `id`='1',`server_name`='media.ccsweethills.org',`server_path`='media.ccsweethills.org',`published`='1',`server_type`='local',`ftp_username`='',`ftp_password`='';

-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_share`
--

DROP TABLE IF EXISTS `#__bsms_share`;
CREATE TABLE `#__bsms_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `params` text,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5;


--
-- Dumping data for table `#__bsms_share`
--

INSERT INTO #__bsms_share SET `id`='1',`name`='FaceBook',`params`='mainlink=http://www.facebook.com/sharer.php? item1prefix=u= item1=200 item1custom= item2prefix=t= item2=5 item2custom= item3prefix= item3=6 item3custom= item4prefix= item4=8 item4custom= use_bitly=0 username= api= shareimage=components/com_biblestudy/images/facebook.png shareimageh=33px shareimagew=33px totalcharacters= alttext=FaceBook  ',`published`='1';
INSERT INTO #__bsms_share SET `id`='2',`name`='Twitter',`params`='mainlink=http://twitter.com/home? item1prefix=status= item1=200 item1custom= item2prefix= item2=5 item2custom= item3prefix= item3=1 item3custom= item4prefix= item4= item4custom= use_bitly=0 username= api= shareimage=components/com_biblestudy/images/twitter.png shareimagew=33px shareimageh=33px totalcharacters=140 alttext=Twitter',`published`='1';
INSERT INTO #__bsms_share SET `id`='3',`name`='Delicious',`params`='mainlink=http://delicious.com/save? item1prefix=url= item1=200 item1custom= item2prefix=&title= item2=5 item2custom= item3prefix= item3=6 item3custom= item4prefix= item4= item4custom= use_bitly=0 username= api= shareimage=components/com_biblestudy/images/delicious.png shareimagew=33px shareimageh=33px totalcharacters= alttext=Delicious',`published`='1';
INSERT INTO #__bsms_share SET `id`='4',`name`='MySpace',`params`='mainlink=http://www.myspace.com/index.cfm? item1prefix=fuseaction=postto&t= item1=5 item1custom= item2prefix=&c= item2=6 item2custom= item3prefix=&u= item3=200 item3custom= item4prefix=&l=1 item4= item4custom= use_bitly=0 username= api= shareimage=components/com_biblestudy/images/myspace.png\nshareimagew=33px\nshareimageh=33px\ntotalcharacters=\nalttext=MySpace',`published`='1';

-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_studies`
--

DROP TABLE IF EXISTS `#__bsms_studies`;
CREATE TABLE `#__bsms_studies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `show_level` int(2) NOT NULL DEFAULT '0',
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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=49;


--
-- Dumping data for table `#__bsms_studies`
--

INSERT INTO #__bsms_studies SET `id`='40',`studydate`='2013-09-25 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='105',`chapter_begin`='21',`verse_begin`='0',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Deuteronomy 21',`studyintro`='',`media_hours`='',`media_minutes`='44',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='41',`studydate`='2013-09-29 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='140',`chapter_begin`='7',`verse_begin`='24',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Marriage Seminar ( A Working Marriage Pt 3)',`studyintro`='',`media_hours`='',`media_minutes`='56',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='42',`studydate`='2013-10-06 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='141',`chapter_begin`='7',`verse_begin`='0',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Love From The Inside Out',`studyintro`='',`media_hours`='',`media_minutes`='38',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='39',`studydate`='2013-09-22 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='149',`chapter_begin`='5',`verse_begin`='21',`chapter_end`='0',`verse_end`='33',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Marriage Seminar (A Working Marriage Pt 2)',`studyintro`='',`media_hours`='1',`media_minutes`='3',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='38',`studydate`='2013-09-11 00:00:00',`teacher_id`='2',`studynumber`='',`booknumber`='165',`chapter_begin`='1',`verse_begin`='20',`chapter_end`='0',`verse_end`='21',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Tony Rodriguez (Guest Speaker)',`studyintro`='',`media_hours`='',`media_minutes`='51',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='12',`studydate`='2013-04-28 00:00:00',`teacher_id`='2',`studynumber`='',`booknumber`='167',`chapter_begin`='0',`verse_begin`='0',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Samy Tanagho (Guest Speaker) Salvation for Muslims',`studyintro`='',`media_hours`='1',`media_minutes`='16',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='0';
INSERT INTO #__bsms_studies SET `id`='13',`studydate`='2013-05-06 00:00:00',`teacher_id`='2',`studynumber`='',`booknumber`='101',`chapter_begin`='28',`verse_begin`='0',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Ken Graves (Guest Speaker) recorded 6/3/12',`studyintro`='',`media_hours`='1',`media_minutes`='9',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='0';
INSERT INTO #__bsms_studies SET `id`='14',`studydate`='2013-04-14 00:00:00',`teacher_id`='2',`studynumber`='',`booknumber`='101',`chapter_begin`='3',`verse_begin`='0',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Andre Bribiesca (Guest Speaker)',`studyintro`='',`media_hours`='',`media_minutes`='58',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='0';
INSERT INTO #__bsms_studies SET `id`='37',`studydate`='2013-09-08 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='149',`chapter_begin`='5',`verse_begin`='22',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Marriage Seminar ( A Working Marriage)',`studyintro`='',`media_hours`='',`media_minutes`='50',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='36',`studydate`='2013-08-30 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='167',`chapter_begin`='0',`verse_begin`='0',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Jesus Loves You',`studyintro`='',`media_hours`='',`media_minutes`='3',`media_seconds`='30',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='35',`studydate`='2013-08-28 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='105',`chapter_begin`='19',`verse_begin`='14',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Old School Consideration',`studyintro`='',`media_hours`='',`media_minutes`='42',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='34',`studydate`='2013-08-25 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='141',`chapter_begin`='6',`verse_begin`='53',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Prayer Ignites Love',`studyintro`='',`media_hours`='',`media_minutes`='40',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='19',`studydate`='2013-06-09 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='141',`chapter_begin`='4',`verse_begin`='21',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Hear and Heed',`studyintro`='',`media_hours`='',`media_minutes`='55',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='21',`studydate`='2013-07-09 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='105',`chapter_begin`='16',`verse_begin`='1',`chapter_end`='0',`verse_end`='17',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Remember Your Deliverance',`studyintro`='',`media_hours`='',`media_minutes`='47',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='22',`studydate`='2013-07-09 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='141',`chapter_begin`='5',`verse_begin`='0',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Active Faith',`studyintro`='',`media_hours`='',`media_minutes`='50',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='23',`studydate`='2013-07-10 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='105',`chapter_begin`='16',`verse_begin`='18',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Right Judgement',`studyintro`='',`media_hours`='',`media_minutes`='40',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='24',`studydate`='2013-07-14 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='141',`chapter_begin`='6',`verse_begin`='1',`chapter_end`='0',`verse_end`='13',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Breaking Out',`studyintro`='',`media_hours`='',`media_minutes`='56',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='25',`studydate`='2013-07-17 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='105',`chapter_begin`='17',`verse_begin`='1',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Giving Your Best',`studyintro`='',`media_hours`='',`media_minutes`='47',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='26',`studydate`='2013-07-21 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='141',`chapter_begin`='6',`verse_begin`='14',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Dying For Jesus Christ',`studyintro`='',`media_hours`='',`media_minutes`='46',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='27',`studydate`='2013-07-24 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='105',`chapter_begin`='17',`verse_begin`='2',`chapter_end`='0',`verse_end`='20',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Drop The World',`studyintro`='',`media_hours`='',`media_minutes`='58',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='28',`studydate`='2013-07-28 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='141',`chapter_begin`='6',`verse_begin`='30',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Kingdom Provisions',`studyintro`='',`media_hours`='',`media_minutes`='54',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='29',`studydate`='2013-07-31 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='105',`chapter_begin`='18',`verse_begin`='1',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Keep It Running, Keep It True',`studyintro`='',`media_hours`='',`media_minutes`='45',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='30',`studydate`='2013-08-07 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='144',`chapter_begin`='16',`verse_begin`='6',`chapter_end`='0',`verse_end`='34',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='David Diaz (Guest Speaker)',`studyintro`='',`media_hours`='',`media_minutes`='36',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='31',`studydate`='2013-08-09 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='167',`chapter_begin`='0',`verse_begin`='0',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Single\'s Seminar',`studyintro`='',`media_hours`='',`media_minutes`='43',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='32',`studydate`='2013-08-18 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='141',`chapter_begin`='6',`verse_begin`='45',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Prayer, Faith, Healing',`studyintro`='',`media_hours`='',`media_minutes`='43',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='33',`studydate`='2013-08-21 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='105',`chapter_begin`='19',`verse_begin`='11',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='God is Our Refuge pt.2',`studyintro`='',`media_hours`='',`media_minutes`='37',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='43',`studydate`='2013-10-13 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='141',`chapter_begin`='7',`verse_begin`='24',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Looking For Faith',`studyintro`='',`media_hours`='',`media_minutes`='41',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='44',`studydate`='2013-10-16 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='105',`chapter_begin`='23',`verse_begin`='0',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='You\'re Not Cursed',`studyintro`='',`media_hours`='',`media_minutes`='43',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='46',`studydate`='2013-10-23 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='105',`chapter_begin`='23',`verse_begin`='9',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Is Cleanliness Next To Godliness?',`studyintro`='',`media_hours`='',`media_minutes`='38',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='47',`studydate`='2013-11-20 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='105',`chapter_begin`='24',`verse_begin`='0',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='In Response, Love',`studyintro`='',`media_hours`='',`media_minutes`='53',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';
INSERT INTO #__bsms_studies SET `id`='48',`studydate`='2013-11-17 00:00:00',`teacher_id`='1',`studynumber`='',`booknumber`='141',`chapter_begin`='8',`verse_begin`='27',`chapter_end`='0',`verse_end`='0',`secondary_reference`='',`booknumber2`='0',`chapter_begin2`='',`verse_begin2`='',`chapter_end2`='',`verse_end2`='',`prod_dvd`='',`prod_cd`='',`server_cd`='0',`server_dvd`='0',`image_cd`='0',`image_dvd`='0',`studytext2`=NULL,`comments`='1',`hits`='0',`user_id`='66',`user_name`='david diaz',`show_level`='0',`location_id`='0',`studytitle`='Who Do You Say That I Am?',`studyintro`='',`media_hours`='',`media_minutes`='42',`media_seconds`='',`messagetype`='0',`series_id`='0',`topics_id`='0',`studytext`='',`thumbnailm`='0',`thumbhm`=NULL,`thumbwm`=NULL,`params`=NULL,`published`='1';

-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_studytopics`
--

DROP TABLE IF EXISTS `#__bsms_studytopics`;
CREATE TABLE `#__bsms_studytopics` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `study_id` int(3) NOT NULL DEFAULT '0',
  `topic_id` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`)
) ENGINE=MyISAM;


--
-- Dumping data for table `#__bsms_studytopics`
--


-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_teachers`
--

DROP TABLE IF EXISTS `#__bsms_teachers`;
CREATE TABLE `#__bsms_teachers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3;


--
-- Dumping data for table `#__bsms_teachers`
--

INSERT INTO #__bsms_teachers SET `id`='1',`teacher_image`='0',`teacher_thumbnail`='0',`teachername`='Pastor Ryan Houssein',`title`='',`phone`='',`email`='',`website`='',`information`='',`image`='',`imageh`='',`imagew`='',`thumb`='',`thumbw`='',`thumbh`='',`short`='',`ordering`='0',`catid`='1',`list_show`='1',`published`='1';
INSERT INTO #__bsms_teachers SET `id`='2',`teacher_image`='0',`teacher_thumbnail`='0',`teachername`='Guest Speaker',`title`='',`phone`='',`email`='',`website`='',`information`='',`image`='',`imageh`='',`imagew`='',`thumb`='',`thumbw`='',`thumbh`='',`short`='',`ordering`='0',`catid`='1',`list_show`='1',`published`='1';

-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_templates`
--

DROP TABLE IF EXISTS `#__bsms_templates`;
CREATE TABLE `#__bsms_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `tmpl` longtext NOT NULL,
  `published` int(1) NOT NULL DEFAULT '1',
  `params` longtext,
  `title` text,
  `text` text,
  `pdf` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20;


--
-- Dumping data for table `#__bsms_templates`
--

INSERT INTO #__bsms_templates SET `id`='1',`type`='tmplList',`tmpl`='',`published`='1',`params`='itemslimit=30\nstudieslisttemplateid=1\ndetailstemplateid=1\nteachertemplateid=1\nserieslisttemplateid=1\nseriesdetailtemplateid=1\nteacher_id=-1\nseries_id=-1\nbooknumber=-1\ntopic_id=-1\nmessagetype=-1\nlocations=-1\ndefault_order=DESC\nshow_page_image=1\ntooltip=1\nshow_verses=1\nstylesheet=\ndate_format=2\ncustom_date_format=\nduration_type=1\nprotocol=http://\nmedia_player=1\npopuptype=window\ninternal_popup=1\nplayer_width=290\nplayer_height=23\nembedshare=TRUE\nbackcolor=0x287585\nfrontcolor=0xFFFFFF\nlightcolor=0x000000\nscreencolor=0x000000\npopuptitle={{title}}\npopupfooter={{filename}}\npopupmargin=50\npopupbackground=black\npopupimage=components/com_biblestudy/images/speaker24.png\nshow_filesize=1\nstore_page=flypage.tpl\nshow_page_title=1\npage_title=Bible Studies by Pastor Ryan Houssein\nuse_headers_list=1\nlist_intro=\nintro_show=1\nlist_teacher_show=0\nlistteachers=1\nteacherlink=1\ndetails_text=Study\nshow_book_search=2\nuse_go_button=0\nbooklist=1\nshow_teacher_search=0\nshow_series_search=0\nshow_type_search=0\nshow_year_search=0\nshow_order_search=0\nshow_topic_search=0\nshow_locations_search=0\nshow_popular=0\ntip_title=Sermon\ntip_item1_title=Title\ntip_item1=5\ntip_item2_title=Details\ntip_item2=6\ntip_item3_title=Teacher\ntip_item3=7\ntip_item4_title=Reference\ntip_item4=1\ntip_item5_title=Date\ntip_item5=10\nrow1col1=1\nr1c1custom=\nr1c1span=1\nlinkr1c1=0\nrow1col2=5\nr1c2custom=\nr1c2span=1\nlinkr1c2=0\nrow1col3=10\nr1c3custom=\nr1c3span=1\nlinkr1c3=0\nrow1col4=20\nr1c4custom=\nlinkr1c4=0\nrow2col1=0\nr2c1custom=\nr2c1span=1\nlinkr2c1=0\nrow2col2=0\nr2c2custom=\nr2c2span=1\nlinkr2c2=0\nrow2col3=0\nr2c3custom=\nr2c3span=1\nlinkr2c3=0\nrow2col4=0\nr2c4custom=\nlinkr2c4=0\nrow3col1=0\nr3c1custom=\nr3c1span=1\nlinkr3c1=0\nrow3col2=0\nr3c2custom=\nr3c2span=1\nlinkr3c2=0\nrow3col3=0\nr3c3custom=\nr3c3span=1\nlinkr3c3=0\nrow3col4=0\nr3c4custom=\nlinkr3c4=0\nrow4col1=0\nr4c1custom=\nr4c1span=1\nlinkr4c1=0\nrow4col2=0\nr4c2custom=\nr4c2span=1\nlinkr4c2=0\nrow4col3=0\nr4c3custom=\nr4c3span=1\nlinkr4c3=0\nrow4col4=0\nr4c4custom=\nlinkr4c4=0\nuseexpert_list=0\nheadercode=\ntemplatecode=<table width=\"100%\" border=\"0\" style=\"margin-bottom:3px;\">   <tbody>   <tr>     <td width=\"33%\" valign=\"top\">{{teacher}}</td>     <td width=\"33%\" valign=\"top\">{{title}}</td>     <td width=\"33%\" valign=\"top\">{{date}}</td>   </tr>   <tr>     <td colspan=\"2\" valign=\"top\">{{studyintro}}</td>     <td valign=\"top\">{{scripture}}</td>   </tr>   </tbody> </table>\nwrapcode=0\nshow_print_view=1\nshow_pdf_view=1\nshow_teacher_view=1\nshow_passage_view=1\nuse_headers_view=1\nlist_items_view=0\ntitle_line_1=1\ncustomtitle1=\ntitle_line_2=4\ncustomtitle2=\nview_link=1\nlink_text=Return\nshow_scripture_link=1\nshow_comments=0\nlink_comments=0\ncomment_access=1\ncomment_publish=0\nuse_captcha=1\nemail_comments=1\nrecipient=\nsubject=Comments\nbody=Comments\nuseexpert_details=0\nstudy_detailtemplate=\ndrow1col1=1\ndr1c1custom=\ndr1c1span=1\ndlinkr1c1=0\ndrow1col2=5\ndr1c2custom=\ndr1c2span=1\ndlinkr1c2=0\ndrow1col3=0\ndr1c3custom=\ndr1c3span=1\ndlinkr1c3=0\ndrow1col4=0\ndr1c4custom=\ndlinkr1c4=0\ndrow2col1=0\ndr2c1custom=\ndr2c1span=1\ndlinkr2c1=0\ndrow2col2=0\ndr2c2custom=\ndr2c2span=1\ndlinkr2c2=0\ndrow2col3=0\ndr2c3custom=\ndr2c3span=1\ndlinkr2c3=0\ndrow2col4=0\ndr2c4custom=\ndrowspanr2c4=1\ndlinkr2c4=0\ndrow3col1=0\ndr3c1custom=\ndr3c1span=1\ndlinkr3c1=0\ndrow3col2=0\ndr3c2custom=\ndr3c2span=1\ndlinkr3c2=0\ndrow3col3=0\ndr3c3custom=\ndr3c3span=1\ndlinkr3c3=0\ndrow3col4=0\ndr3c4custom=\ndlinkr3c4=0\ndrow4col1=0\ndr4c1custom=\ndr4c1span=1\ndlinkr4c1=0\ndrow4col2=0\ndr4c2custom=\ndr4c2span=1\ndlinkr4c2=0\ndrow4col3=0\ndr4c3custom=\ndr4c3span=1\ndlinkr4c3=0\ndrow4col4=0\ndr4c4custom=\ndlinkr4c4=0\nteacher_title=Our\nshow_teacher_studies=1\nstudies=5\nlabel_teacher=Latest\nuseexpert_teacherlist=0\nteacher_headercode=\nteacher_templatecode=<table width=\"100%\" border=\"0\" style=\"margin-bottom:3px;\">   <tbody>   <tr>     <td width=\"33%\" valign=\"top\">{{teacher}}</td>     <td width=\"33%\" valign=\"top\">{{title}}</td>     <td width=\"33%\" valign=\"top\">{{teacher}}</td>   </tr>   <tr>     <td colspan=\"2\" valign=\"top\">{{short}}</td>     <td valign=\"top\">{{information}}</td>   </tr>   </tbody> </table>\nteacher_wrapcode=0\nuseexpert_teacherdetail=0\nteacher_detailtemplate=<table width=\"100%\" border=\"0\" style=\"margin-bottom:3px;\">   <tbody>   <tr>     <td width=\"33%\" valign=\"top\">{{teacher}}</td>     <td width=\"33%\" valign=\"top\">{{title}}</td>     <td width=\"33%\" valign=\"top\">{{teacher}}</td>   </tr>   <tr>     <td colspan=\"2\" valign=\"top\">{{short}}</td>     <td valign=\"top\">{{information}}</td>   </tr>   </tbody> </table>\nseries_title=Our\nshow_series_title=1\nshow_page_image_series=1\nseries_show_description=1\nseries_characters=\nsearch_series=1\nseries_limit=5\nseries_list_order=ASC\nseries_order_field=series_text\nserieselement1=1\nseriesislink1=1\nserieselement2=1\nseriesislink2=1\nserieselement3=1\nseriesislink3=1\nserieselement4=1\nseriesislink4=1\nuseexpert_serieslist=0\nseries_headercode=\nseries_templatecode=\nseries_wrapcode=0\nseries_detail_sort=studydate\nseries_detail_order=DESC\nseries_detail_limit=\nseries_list_return=1\nseries_detail_listtype=0\nseries_detail_1=5\nseries_detail_islink1=1\nseries_detail_2=7\nseries_detail_islink2=0\nseries_detail_3=10\nseries_detail_islink3=0\nseries_detail_4=20\nseries_detail_islink4=0\nuseexpert_seriesdetail=0\nseries_detailcode=\nlanding_hide=0\nlanding_hidelabel=Show/Hide All\nheadingorder_1=teachers\nheadingorder_2=series\nheadingorder_3=books\nheadingorder_4=topics\nheadingorder_5=locations\nheadingorder_6=messagetypes\nheadingorder_7=years\nshowteachers=1\nlandingteacherslimit=\nteacherslabel=Speakers\nlinkto=1\nshowseries=1\nlandingserieslimit=\nserieslabel=Series\nseries_linkto=0\nshowbooks=1\nlandingbookslimit=\nbookslabel=Books\nshowtopics=1\nlandingtopicslimit=\ntopicslabel=Topics\nshowlocations=1\nlandinglocationslimit=\nlocationslabel=Locations\nshowmessagetypes=1\nlandingmessagetypeslimit=\nmessagetypeslabel=Message Types\nshowyears=1\nlandingyearslimit=\nyearslabel=Years\n\n',`title`='Default',`text`='textfile24.png',`pdf`='pdf24.png';

-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_timeset`
--

DROP TABLE IF EXISTS `#__bsms_timeset`;
CREATE TABLE `#__bsms_timeset` (
  `timeset` varchar(14) DEFAULT NULL,
  KEY `timeset` (`timeset`)
) ENGINE=MyISAM;


--
-- Dumping data for table `#__bsms_timeset`
--

INSERT INTO #__bsms_timeset SET `timeset`='1281646339';

-- --------------------------------------------------------

 
--
-- Table structure for table `#__bsms_topics`
--

DROP TABLE IF EXISTS `#__bsms_topics`;
CREATE TABLE `#__bsms_topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_text` text,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=114;


--
-- Dumping data for table `#__bsms_topics`
--

INSERT INTO #__bsms_topics SET `id`='1',`topic_text`='Abortion',`published`='1';
INSERT INTO #__bsms_topics SET `id`='3',`topic_text`='Addiction',`published`='1';
INSERT INTO #__bsms_topics SET `id`='4',`topic_text`='Afterlife',`published`='1';
INSERT INTO #__bsms_topics SET `id`='5',`topic_text`='Apologetics',`published`='1';
INSERT INTO #__bsms_topics SET `id`='7',`topic_text`='Baptism',`published`='1';
INSERT INTO #__bsms_topics SET `id`='8',`topic_text`='Basics of Christianity',`published`='1';
INSERT INTO #__bsms_topics SET `id`='9',`topic_text`='Becoming a Christian',`published`='1';
INSERT INTO #__bsms_topics SET `id`='10',`topic_text`='Bible',`published`='1';
INSERT INTO #__bsms_topics SET `id`='37',`topic_text`='Blended Family Relationships',`published`='1';
INSERT INTO #__bsms_topics SET `id`='12',`topic_text`='Children',`published`='1';
INSERT INTO #__bsms_topics SET `id`='13',`topic_text`='Christ',`published`='1';
INSERT INTO #__bsms_topics SET `id`='14',`topic_text`='Christian Character/Fruits',`published`='1';
INSERT INTO #__bsms_topics SET `id`='15',`topic_text`='Christian Values',`published`='1';
INSERT INTO #__bsms_topics SET `id`='16',`topic_text`='Christmas Season',`published`='1';
INSERT INTO #__bsms_topics SET `id`='17',`topic_text`='Church',`published`='1';
INSERT INTO #__bsms_topics SET `id`='18',`topic_text`='Communication',`published`='1';
INSERT INTO #__bsms_topics SET `id`='19',`topic_text`='Communion / Lords Supper',`published`='1';
INSERT INTO #__bsms_topics SET `id`='21',`topic_text`='Creation',`published`='1';
INSERT INTO #__bsms_topics SET `id`='23',`topic_text`='Cults',`published`='1';
INSERT INTO #__bsms_topics SET `id`='113',`topic_text`='Da Vinci Code',`published`='1';
INSERT INTO #__bsms_topics SET `id`='24',`topic_text`='Death',`published`='1';
INSERT INTO #__bsms_topics SET `id`='26',`topic_text`='Descriptions of God',`published`='1';
INSERT INTO #__bsms_topics SET `id`='27',`topic_text`='Disciples',`published`='1';
INSERT INTO #__bsms_topics SET `id`='28',`topic_text`='Discipleship',`published`='1';
INSERT INTO #__bsms_topics SET `id`='30',`topic_text`='Divorce',`published`='1';
INSERT INTO #__bsms_topics SET `id`='32',`topic_text`='Easter Season',`published`='1';
INSERT INTO #__bsms_topics SET `id`='33',`topic_text`='Emotions',`published`='1';
INSERT INTO #__bsms_topics SET `id`='34',`topic_text`='Entertainment',`published`='1';
INSERT INTO #__bsms_topics SET `id`='35',`topic_text`='Evangelism',`published`='1';
INSERT INTO #__bsms_topics SET `id`='36',`topic_text`='Faith',`published`='1';
INSERT INTO #__bsms_topics SET `id`='103',`topic_text`='Family',`published`='1';
INSERT INTO #__bsms_topics SET `id`='39',`topic_text`='Forgiving Others',`published`='1';
INSERT INTO #__bsms_topics SET `id`='104',`topic_text`='Freedom',`published`='1';
INSERT INTO #__bsms_topics SET `id`='41',`topic_text`='Friendship',`published`='1';
INSERT INTO #__bsms_topics SET `id`='42',`topic_text`='Fulfillment in Life',`published`='1';
INSERT INTO #__bsms_topics SET `id`='43',`topic_text`='Fund-raising rally',`published`='1';
INSERT INTO #__bsms_topics SET `id`='44',`topic_text`='Funerals',`published`='1';
INSERT INTO #__bsms_topics SET `id`='45',`topic_text`='Giving',`published`='1';
INSERT INTO #__bsms_topics SET `id`='2',`topic_text`='Gods Activity',`published`='1';
INSERT INTO #__bsms_topics SET `id`='6',`topic_text`='Gods Attributes',`published`='1';
INSERT INTO #__bsms_topics SET `id`='40',`topic_text`='Gods Forgiveness',`published`='1';
INSERT INTO #__bsms_topics SET `id`='58',`topic_text`='Gods Love',`published`='1';
INSERT INTO #__bsms_topics SET `id`='65',`topic_text`='Gods Nature',`published`='1';
INSERT INTO #__bsms_topics SET `id`='46',`topic_text`='Gods Will',`published`='1';
INSERT INTO #__bsms_topics SET `id`='47',`topic_text`='Hardship of Life',`published`='1';
INSERT INTO #__bsms_topics SET `id`='107',`topic_text`='Holidays',`published`='1';
INSERT INTO #__bsms_topics SET `id`='48',`topic_text`='Holy Spirit',`published`='1';
INSERT INTO #__bsms_topics SET `id`='111',`topic_text`='Hot Topics',`published`='1';
INSERT INTO #__bsms_topics SET `id`='11',`topic_text`='Jesus Birth',`published`='1';
INSERT INTO #__bsms_topics SET `id`='22',`topic_text`='Jesus Cross/Final Week',`published`='1';
INSERT INTO #__bsms_topics SET `id`='29',`topic_text`='Jesus Divinity',`published`='1';
INSERT INTO #__bsms_topics SET `id`='50',`topic_text`='Jesus Humanity',`published`='1';
INSERT INTO #__bsms_topics SET `id`='56',`topic_text`='Jesus Life',`published`='1';
INSERT INTO #__bsms_topics SET `id`='61',`topic_text`='Jesus Miracles',`published`='1';
INSERT INTO #__bsms_topics SET `id`='84',`topic_text`='Jesus Resurrection',`published`='1';
INSERT INTO #__bsms_topics SET `id`='93',`topic_text`='Jesus Teaching',`published`='1';
INSERT INTO #__bsms_topics SET `id`='52',`topic_text`='Kingdom of God',`published`='1';
INSERT INTO #__bsms_topics SET `id`='55',`topic_text`='Leadership Essentials',`published`='1';
INSERT INTO #__bsms_topics SET `id`='57',`topic_text`='Love',`published`='1';
INSERT INTO #__bsms_topics SET `id`='59',`topic_text`='Marriage',`published`='1';
INSERT INTO #__bsms_topics SET `id`='109',`topic_text`='Men',`published`='1';
INSERT INTO #__bsms_topics SET `id`='82',`topic_text`='Messianic Prophecies',`published`='1';
INSERT INTO #__bsms_topics SET `id`='62',`topic_text`='Misconceptions of Christianity',`published`='1';
INSERT INTO #__bsms_topics SET `id`='63',`topic_text`='Money',`published`='1';
INSERT INTO #__bsms_topics SET `id`='112',`topic_text`='Narnia',`published`='1';
INSERT INTO #__bsms_topics SET `id`='66',`topic_text`='Our Need for God',`published`='1';
INSERT INTO #__bsms_topics SET `id`='69',`topic_text`='Parables',`published`='1';
INSERT INTO #__bsms_topics SET `id`='70',`topic_text`='Paranormal',`published`='1';
INSERT INTO #__bsms_topics SET `id`='71',`topic_text`='Parenting',`published`='1';
INSERT INTO #__bsms_topics SET `id`='73',`topic_text`='Poverty',`published`='1';
INSERT INTO #__bsms_topics SET `id`='74',`topic_text`='Prayer',`published`='1';
INSERT INTO #__bsms_topics SET `id`='76',`topic_text`='Prominent N.T. Men',`published`='1';
INSERT INTO #__bsms_topics SET `id`='77',`topic_text`='Prominent N.T. Women',`published`='1';
INSERT INTO #__bsms_topics SET `id`='78',`topic_text`='Prominent O.T. Men',`published`='1';
INSERT INTO #__bsms_topics SET `id`='79',`topic_text`='Prominent O.T. Women',`published`='1';
INSERT INTO #__bsms_topics SET `id`='83',`topic_text`='Racism',`published`='1';
INSERT INTO #__bsms_topics SET `id`='85',`topic_text`='Second Coming',`published`='1';
INSERT INTO #__bsms_topics SET `id`='86',`topic_text`='Sexuality',`published`='1';
INSERT INTO #__bsms_topics SET `id`='87',`topic_text`='Sin',`published`='1';
INSERT INTO #__bsms_topics SET `id`='88',`topic_text`='Singleness',`published`='1';
INSERT INTO #__bsms_topics SET `id`='89',`topic_text`='Small Groups',`published`='1';
INSERT INTO #__bsms_topics SET `id`='108',`topic_text`='Special Services',`published`='1';
INSERT INTO #__bsms_topics SET `id`='90',`topic_text`='Spiritual Disciplines',`published`='1';
INSERT INTO #__bsms_topics SET `id`='91',`topic_text`='Spiritual Gifts',`published`='1';
INSERT INTO #__bsms_topics SET `id`='105',`topic_text`='Stewardship',`published`='1';
INSERT INTO #__bsms_topics SET `id`='92',`topic_text`='Supernatural',`published`='1';
INSERT INTO #__bsms_topics SET `id`='94',`topic_text`='Temptation',`published`='1';
INSERT INTO #__bsms_topics SET `id`='95',`topic_text`='Ten Commandments',`published`='1';
INSERT INTO #__bsms_topics SET `id`='97',`topic_text`='Truth',`published`='1';
INSERT INTO #__bsms_topics SET `id`='98',`topic_text`='Twelve Apostles',`published`='1';
INSERT INTO #__bsms_topics SET `id`='100',`topic_text`='Weddings',`published`='1';
INSERT INTO #__bsms_topics SET `id`='110',`topic_text`='Women',`published`='1';
INSERT INTO #__bsms_topics SET `id`='101',`topic_text`='Workplace Issues',`published`='1';
INSERT INTO #__bsms_topics SET `id`='102',`topic_text`='World Religions',`published`='1';
INSERT INTO #__bsms_topics SET `id`='106',`topic_text`='Worship',`published`='1';

-- --------------------------------------------------------



--
-- Table structure for table `#__bsms_version`
--

DROP TABLE IF EXISTS `#__bsms_version`;
CREATE TABLE `#__bsms_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(20) NOT NULL,
  `versiondate` date NOT NULL,
  `installdate` date NOT NULL,
  `build` varchar(20) NOT NULL,
  `versionname` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2;


--
-- Dumping data for table `#__bsms_version`
--

INSERT INTO #__bsms_version SET `id`='1',`version`='6.2.4',`versiondate`='2010-11-09',`installdate`='2011-06-10',`build`='624',`versionname`='1Samuel';

-- --------------------------------------------------------

