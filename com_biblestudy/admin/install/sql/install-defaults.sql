SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Dumping data for table `#__bsms_admin`
--

INSERT INTO `#__bsms_admin` (`id`, `drop_tables`, `params`, `asset_id`, `access`, `installstate`, `debug`) VALUES
(1, 0, '{"metakey":"","metadesc":"","compat_mode":"0","jbsmigrationshow":"0","studylistlimit":"10","show_location_media":"0","popular_limit":"","character_filter":"1","format_popular":"0","uploadtype":"1","socialnetworking":"1","sharetype":"1","default_main_image":"","default_series_image":"","default_teacher_image":"","default_download_image":"","default_showHide_image":"","location_id":"-1","teacher_id":"1","series_id":"-1","booknumber":"-1","messagetype":"-1","default_study_image":"","download":"1","target":" ","server":"1","path":"-1","podcast":["-1"],"mime":"1","from":"x","to":"x","pFrom":"x","pTo":"x","debug":"0"}', 0, 1, NULL, 0);

--
-- Dumping data for table `#__bsms_books`
--

INSERT INTO `#__bsms_books` (`id`, `bookname`, `booknumber`, `published`) VALUES
(1, 'JBS_BBK_GENESIS', 101, 1),
(2, 'JBS_BBK_EXODUS', 102, 1),
(3, 'JBS_BBK_LEVITICUS', 103, 1),
(4, 'JBS_BBK_NUMBERS', 104, 1),
(5, 'JBS_BBK_DEUTERONOMY', 105, 1),
(6, 'JBS_BBK_JOSHUA', 106, 1),
(7, 'JBS_BBK_JUDGES', 107, 1),
(8, 'JBS_BBK_RUTH', 108, 1),
(9, 'JBS_BBK_1SAMUEL', 109, 1),
(10, 'JBS_BBK_2SAMUEL', 110, 1),
(11, 'JBS_BBK_1KINGS', 111, 1),
(12, 'JBS_BBK_2KINGS', 112, 1),
(13, 'JBS_BBK_1CHRONICLES', 113, 1),
(14, 'JBS_BBK_2CHRONICLES', 114, 1),
(15, 'JBS_BBK_EZRA', 115, 1),
(16, 'JBS_BBK_NEHEMIAH', 116, 1),
(17, 'JBS_BBK_ESTHER', 117, 1),
(18, 'JBS_BBK_JOB', 118, 1),
(19, 'JBS_BBK_PSALM', 119, 1),
(20, 'JBS_BBK_PROVERBS', 120, 1),
(21, 'JBS_BBK_ECCLESIASTES', 121, 1),
(22, 'JBS_BBK_SONG_OF_SOLOMON', 122, 1),
(23, 'JBS_BBK_ISAIAH', 123, 1),
(24, 'JBS_BBK_JEREMIAH', 124, 1),
(25, 'JBS_BBK_LAMENTATIONS', 125, 1),
(26, 'JBS_BBK_EZEKIEL', 126, 1),
(27, 'JBS_BBK_DANIEL', 127, 1),
(28, 'JBS_BBK_HOSEA', 128, 1),
(29, 'JBS_BBK_JOEL', 129, 1),
(30, 'JBS_BBK_AMOS', 130, 1),
(31, 'JBS_BBK_OBADIAH', 131, 1),
(32, 'JBS_BBK_JONAH', 132, 1),
(33, 'JBS_BBK_MICAH', 133, 1),
(34, 'JBS_BBK_NAHUM', 134, 1),
(35, 'JBS_BBK_HABAKKUK', 135, 1),
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

--
-- Dumping data for table `#__bsms_folders`
--

INSERT INTO `#__bsms_folders` (`id`, `foldername`, `folderpath`, `published`, `asset_id`, `access`) VALUES
(1, 'My Folder Name', '/media/', 1, 0, 1);

--
-- Dumping data for table `#__bsms_locations`
--

INSERT INTO `#__bsms_locations` (`id`, `location_text`, `published`, `asset_id`, `access`, `ordering`) VALUES
(1, 'My Location', 1, 0, 1, 1);

--
-- Dumping data for table `#__bsms_media`
--

INSERT INTO `#__bsms_media` (`id`, `media_text`, `media_image_name`, `media_image_path`, `path2`, `media_alttext`, `published`, `asset_id`, `access`, `ordering`) VALUES
(1, 'mp3 compressed audio file', 'mp3', '', 'speaker24.png', 'mp3 audio file', 1, 0, 1, 1),
(2, 'Video', 'Video File', '', 'video24.png', 'Video File', 1, 0, 1, 2),
(3, 'm4v', 'Video Podcast', '', 'podcast-video24.png', 'Video Podcast', 1, 0, 1, 3),
(4, 'Streaming Audio', 'Streaming Audio', '', 'streamingaudio24.png', 'Streaming Audio', 1, 0, 1, 4),
(5, 'Streaming Video', 'Streaming Video', '', 'streamingvideo24.png', 'Streaming Video', 1, 0, 1, 5),
(6, 'Real Audio', 'Real Audio', '', 'realplayer24.png', 'Real Audio', 1, 0, 1, 6),
(7, 'Windows Media Audio', 'Windows Media Audio', '', 'windows-media24.png', 'Windows Media File', 1, 0, 1, 7),
(8, 'Podcast Audio', 'Podcast Audio', '', 'podcast-audio24.png', 'Podcast Audio', 1, 0, 1, 8),
(9, 'CD', 'CD', '', 'cd.png', 'CD', 1, 0, 1, 9),
(10, 'DVD', 'DVD', '', 'dvd.png', 'DVD', 1, 0, 1, 10),
(11, 'Download', 'Download', '', 'download.png', 'Download', 1, 0, 1, 11),
(12, 'Article', 'Article', '', 'textfile24.png', 'Article', 1, 0, 1, 12),
(13, 'You Tube', 'You Tube', '', 'youtube24.png', 'You Tube Video', 1, 0, 1, 13);

--
-- Dumping data for table `#__bsms_mediafiles`
--

INSERT INTO `#__bsms_mediafiles` (`id`, `study_id`, `media_image`, `server`, `path`, `special`, `filename`, `size`, `mime_type`, `podcast_id`, `internal_viewer`, `mediacode`, `ordering`, `createdate`, `link_type`, `hits`, `published`, `docMan_id`, `article_id`, `comment`, `virtueMart_id`, `downloads`, `plays`, `params`, `player`, `popup`, `asset_id`, `access`, `language`) VALUES
(1, 1, 2, '1', '1', '', 'myfile.mp3', '12332', 1, '1', 0, '', 0, '2009-09-13 00:10:00', '1', 0, 1, 0, -1, '', 0, 0, 0, '{"playerwidth":"","playerheight":"","itempopuptitle":"","itempopupfooter":"","popupmargin":"50"}', 1, 1, 0, 1, '*');

--
-- Dumping data for table `#__bsms_message_type`
--

INSERT INTO `#__bsms_message_type` (`id`, `message_type`, `alias`, `published`, `asset_id`, `access`, `ordering`) VALUES
(1, 'Sunday', 'sunday', 1, 0, 1, 1);

--
-- Dumping data for table `#__bsms_mimetype`
--

INSERT INTO `#__bsms_mimetype` (`id`, `mimetype`, `mimetext`, `published`, `asset_id`, `access`, `ordering`) VALUES
(1, 'audio/mpeg3', 'MP3 Audio', 1, 0, 1, 1),
(2, 'audio/x-pn-realaudio', 'Real Audio', 1, 0, 1, 2),
(3, 'video/x-m4v', 'Podcast Video m4v', 1, 0, 1, 3),
(4, 'application/vnd.rn-realmedia', 'Real Media rm', 1, 0, 1, 4),
(5, 'audio/x-ms-wma', 'Windows Media Audio WMA', 1, 0, 1, 5),
(6, 'text/html', 'Text', 1, 0, 1, 6),
(7, 'audio/x-wav', 'Windows wav File', 1, 0, 1, 7),
(8, 'audio/x-pn-realaudio-plugin', ' Real audio Plugin.rpm', 1, 0, 1, 8),
(9, 'audio/x-pn-realaudio', 'Real Media File .rm', 1, 0, 1, 9),
(10, 'audio/x-realaudio', 'Rea Audio File .ra', 1, 0, 1, 10),
(11, 'audio/x-pn-realaudio', 'Read Audio File.ram', 1, 0, 1, 11),
(12, 'video/mpeg', ' Mpeg video .mpg', 1, 0, 1, 12),
(13, 'audio/mpeg', 'Video .mp2 File', 1, 0, 1, 13),
(14, 'video/x-msvideo', ' Video .avi File', 1, 0, 1, 14),
(15, 'video/x-flv', ' Flash Video FLV', 1, 0, 1, 15);

--
-- Dumping data for table `#__bsms_order`
--

INSERT INTO `#__bsms_order` (`id`, `value`, `text`) VALUES
(1, 'ASC', 'JBS_CMN_ASCENDING'),
(2, 'DESC', 'JBS_CMN_DESCENDING');

--
-- Dumping data for table `#__bsms_podcast`
--

INSERT INTO `#__bsms_podcast` (`id`, `title`, `website`, `description`, `image`, `imageh`, `imagew`, `author`, `podcastimage`, `podcastsearch`, `filename`, `language`, `editor_name`, `editor_email`, `podcastlimit`, `published`, `episodetitle`, `custom`, `detailstemplateid`, `asset_id`, `access`) VALUES
(1, 'My Podcast', 'www.mywebsite.com', 'Podcast Description goes here', 'www.mywebsite.com/myimage.jpg', 30, 30, 'Pastor Billy', 'www.mywebsite.com/myimage.jpg', 'jesus', 'mypodcast.xml', '*', 'Jim Editor', 'jim@mywebsite.com', 50, 1, 0, '', 1, 0, 1);

--
-- Dumping data for table `#__bsms_series`
--

INSERT INTO `#__bsms_series` (`id`, `series_text`, `alias`, `teacher`, `description`, `series_thumbnail`, `published`, `asset_id`, `ordering`, `access`, `language`) VALUES
(1, 'Worship Series', 'worship-series', -1, '', '', 1, 0, 1, 1, '*');

--
-- Dumping data for table `#__bsms_servers`
--

INSERT INTO `#__bsms_servers` (`id`, `server_name`, `server_path`, `published`, `server_type`, `ftp_username`, `ftp_password`, `asset_id`, `access`, `type`, `ftphost`, `ftpuser`, `ftppassword`, `ftpport`, `aws_key`, `aws_secret`) VALUES
(1, 'Your Server Name', 'www.mywebsite.com', 1, 'local', '', '', 0, 1, 0, '', '', '', '', '', '');

--
-- Dumping data for table `#__bsms_share`
--

INSERT INTO `#__bsms_share` (`id`, `name`, `params`, `published`, `asset_id`, `access`, `ordering`) VALUES
(1, 'FaceBook', '{"mainlink":"http://www.facebook.com/sharer.php?","item1prefix":"u=","item1":200,"item1custom":"","item2prefix":"t=","item2":5,"item2custom":"","item3prefix":"","item3":6,"item3custom":"","item4prefix":"","item4":8,"item4custom":"","use_bitly":0,"username":"","api":"","shareimage":"media/com_biblestudy/images/facebook.png","shareimageh":"33px","shareimagew":"33px","totalcharacters":"","alttext":"FaceBook"}', 1, 0, 1, 1),
(2, 'Twitter', '{"mainlink":"http://twitter.com/nfsda?","item1prefix":"status=","item1":200,"item1custom":"","item2prefix":"","item2":5,"item2custom":"","item3prefix":"","item3":1,"item3custom":"","item4prefix":"","item4":0,"item4custom":"","use_bitly":0,"username":"","api":"","shareimage":"media/com_biblestudy/images/twitter.png","shareimageh":"33px","shareimagew":"33px","totalcharacters":140,"alttext":"Twitter"}', 1, 0, 1, 2),
(3, 'Delicious', '{"mainlink":"http://delicious.com/save?","item1prefix":"url=","item1":200,"item1custom":"","item2prefix":"&title=","item2":5,"item2custom":"","item3prefix":"","item3":6,"item3custom":"","item4prefix":"","item4":"","item4custom":"","use_bitly":0,"username":"","api":"","shareimage":"media/com_biblestudy/images/delicious.png","shareimagew":"33px","shareimageh":"33px","totalcharacters":"","alttext":"Delicious"}', 1, 0, 1, 3),
(4, 'MySpace', '{"mainlink":"http://www.myspace.com/index.cfm?","item1prefix":"fuseaction=postto&t=","item1":5,"item1custom":"","item2prefix":"&c=","item2":6,"item2custom":"","item3prefix":"&u=","item3":200,"item3custom":"","item4prefix":"&l=1","item4":"","item4custom":"","use_bitly":0,"username":"","api":"","shareimage":"media/com_biblestudy/images/myspace.png","shareimagew":"33px","shareimageh":"33px","totalcharacters":"","alttext":"MySpace"}', 1, 0, 1, 4);

--
-- Dumping data for table `#__bsms_studies`
--

INSERT INTO `#__bsms_studies` (`id`, `studydate`, `teacher_id`, `studynumber`, `booknumber`, `chapter_begin`, `verse_begin`, `chapter_end`, `verse_end`, `secondary_reference`, `booknumber2`, `chapter_begin2`, `verse_begin2`, `chapter_end2`, `verse_end2`, `prod_dvd`, `prod_cd`, `server_cd`, `server_dvd`, `image_cd`, `image_dvd`, `studytext2`, `comments`, `hits`, `user_id`, `user_name`, `show_level`, `location_id`, `studytitle`, `alias`, `studyintro`, `media_hours`, `media_minutes`, `media_seconds`, `messagetype`, `series_id`, `topics_id`, `studytext`, `thumbnailm`, `thumbhm`, `thumbwm`, `params`, `published`, `asset_id`, `access`, `ordering`, `language`) VALUES
(1, '2010-03-13 00:10:00', 1, '2010-001', 101, 1, 1, 1, 31, '', '-1', '', '', '', '', NULL, NULL, NULL, NULL, NULL, '0', NULL, 1, 0, 0, NULL, '0', -1, 'Sample Study Title', 'sample-study-title', 'Sample text you can use as an introduction to your study', '', '', '', '1', -1, 0, 'This is where you would put study notes or other information. This could be the full text of your study as well. If you install the scripture links plugin you will have all verses as links to BibleGateway.com', '', NULL, NULL, '{"metakey":"","metadesc":""}', 1, 0, 1, 1, '*');

--
-- Dumping data for table `#__bsms_styles`
--

INSERT INTO `#__bsms_styles` (`id`, `published`, `filename`, `stylecode`, `asset_id`) VALUES
(1, 1, 'biblestudy', '/* New Teacher Codes */\r\n#bsm_teachertable_list .bsm_teachername\r\n{\r\n    font-weight: bold;\r\n    font-size: 14px;\r\n    color: #000000;\r\n}\r\n#bsm_teachertable_list\r\n{\r\n    margin: 0;\r\n    border-collapse:separate;\r\n}\r\n#bsm_teachertable_list td {\r\n    text-align:left;\r\n    padding:0 5px 0 5px;\r\n    border:none;\r\n}\r\n#bsm_teachertable_list .titlerow\r\n{\r\n    border-bottom: thick;\r\n}\r\n#bsm_teachertable_list .title\r\n{\r\n    font-size:18px;\r\n    font-weight:bold;\r\n    border-bottom: 3px solid #999999;\r\n    padding: 4px 0px 4px 4px;\r\n}\r\n#bsm_teachertable_list .bsm_separator\r\n{\r\n    border-bottom: 1px solid #999999;\r\n}\r\n\r\n.bsm_teacherthumbnail_list\r\n{\r\n\r\n}\r\n#bsm_teachertable_list .bsm_teacheremail\r\n{\r\n    font-weight:normal;\r\n    font-size: 11px;\r\n}\r\n#bsm_teachertable_list .bsm_teacherwebsite\r\n{\r\n    font-weight:normal;\r\n    font-size: 11px;\r\n}\r\n#bsm_teachertable_list .bsm_teacherphone\r\n{\r\n    font-weight:normal;\r\n    font-size: 11px;\r\n}\r\n#bsm_teachertable_list .bsm_short\r\n{\r\n    padding: 8px 4px 4px;\r\n}\r\n#bsm_teachertable .bsm_studiestitlerow {\r\n    background-color: #666;\r\n}\r\n#bsm_teachertable_list .bsm_titletitle\r\n{\r\n    font-weight:bold;\r\n    color:#FFFFFF;\r\n}\r\n#bsm_teachertable_list .bsm_titlescripture\r\n{\r\n    font-weight:bold;\r\n    color:#FFFFFF;\r\n}\r\n#bsm_teachertable_list .bsm_titledate\r\n{\r\n    font-weight:bold;\r\n    color:#FFFFFF;\r\n}\r\n#bsm_teachertable_list .bsm_teacherlong\r\n{\r\n    padding: 8px 4px 4px;\r\n    border-bottom: 1px solid #999999;\r\n}\r\n#bsm_teachertable_list tr.bsodd {\r\n    background-color:#FFFFFF;\r\n    border-bottom: 1px solid #999999;\r\n}\r\n#bsm_teachertable_list tr.bseven {\r\n    background-color:#FFFFF0;\r\n    border-bottom: 1px solid #999999;\r\n}\r\n\r\n#bsm_teachertable_list .lastrow td {\r\n    border-bottom:1px solid grey;\r\n    padding-bottom:7px;\r\n    padding-top:7px;\r\n}\r\n#bsm_teachertable_list .bsm_teacherfooter\r\n{\r\n    border-top: 1px solid #999999;\r\n    padding: 4px 1px 1px 4px;\r\n}\r\n/* New Teacher Details Codes */\r\n\r\n#bsm_teachertable .teacheraddress{\r\n    text-align:left;\r\n}\r\n\r\n#bsm_teachertable .teacherwebsite{\r\n    text-align:left;}\r\n\r\n#bsm_teachertable .teacherfacebook{\r\n    text-align:left;\r\n}\r\n\r\n#bsm_teachertable .bsm_teachertwitter{\r\n    text-align:left;\r\n}\r\n\r\n#bsm_teachertable .bsm_teacherblog{\r\n    text-align:left;\r\n}\r\n\r\n#bsm_teachertable .bsm_teacherlink1{\r\n    text-align:left;\r\n}\r\n\r\n\r\n/* New Landing Page CSS */\r\n\r\n.landingtable {\r\n    clear:both;\r\n    width:auto;\r\n    display:table;\r\n\r\n}\r\n\r\n.landingrow {\r\n    display:inline;\r\n    padding: 1em;\r\n}\r\n.landingcell {\r\n    display:table-cell;\r\n}\r\n\r\n.landinglink a{\r\n    display:inline;\r\n}\r\n\r\n/* Terms of use or donate display settings */\r\n.termstext {\r\n}\r\n\r\n.termslink{\r\n}\r\n/* Podcast Subscription Display Settings */\r\n\r\n.podcastsubscribe{\r\n    clear:both;\r\n    display:table;\r\n    width:100%;\r\n    background-color:#eee;\r\n    border-radius: 15px 15px 15px 15px;\r\n    border: 1px solid grey;\r\n    padding: 1em;\r\n}\r\n.podcastsubscribe .image {\r\n    float: left;\r\n    padding-right: 5px;\r\n    display: inline;\r\n}\r\n.podcastsubscribe .image .text {\r\n    display:inline;\r\n    position:relative;\r\n    right:50px;\r\n    bottom:-10px;\r\n}\r\n.podcastsubscribe .prow {\r\n    display: table-row;\r\n    width:auto;\r\n    clear:both;\r\n}\r\n.podcastsubscribe .pcell {\r\n    display: table-cell;\r\n    float:left;\r\n    background-color:#e3e2e2;\r\n    border-radius: 15px 15px 15px 15px;\r\n    border: 1px solid grey;\r\n    padding: 1em;\r\n    margin-right: 5px;\r\n}\r\n.podcastheader h3{\r\n    display:table-header;\r\n    text-align:center;\r\n}\r\n\r\n.podcastheader{\r\n    font-weight: bold;\r\n}\r\n\r\n.podcastlinks{\r\n    display: inline;\r\n\r\n}\r\n\r\n.fltlft {\r\n  float:left;\r\n  padding-right: 5px;\r\n}\r\n\r\n/* Listing Page Items */\r\n#subscribelinks {\r\n\r\n}\r\n\r\ndiv.listingfooter ul li {\r\n    list-style: none outside none;\r\n}\r\n\r\n/* Listing Page Items */\r\n#listintro p, #listintro td {\r\n    margin: 0;\r\n    font-weight: bold;\r\n    color: black;\r\n}\r\n\r\n#listingfooter li, #listingfooter ul\r\n{\r\n    display: inline;\r\n}\r\n#main ul, #main li\r\n{\r\n    display: inline;\r\n}\r\n#bsdropdownmenu {\r\n    margin-bottom: 10px;\r\n}\r\n.bslisttable {\r\n    margin: 0;\r\n    border-collapse:separate;\r\n}\r\n.bslisttable th, .bslisttable td {\r\n    text-align:left;\r\n    padding:0 5px 0 5px;\r\n    border:none;\r\n}\r\n.bslisttable .row1col1,\r\n.bslisttable .row2col1,\r\n.bslisttable .row3col1,\r\n.bslisttable .row4col1 {\r\n    border-left: grey 2px solid;\r\n}\r\n.bslisttable .lastcol {\r\n    border-right: grey 2px solid;\r\n}\r\n.bslisttable .lastrow td {\r\n    border-bottom:2px solid grey;\r\n    padding-bottom:7px;\r\n}\r\n.bslisttable th {\r\n    background-color:#707070;\r\n    font-weight:bold;\r\n    color:white;\r\n\r\n}\r\n.bslisttable th.row1col1,\r\n.bslisttable th.row1col2,\r\n.bslisttable th.row1col3,\r\n.bslisttable th.row1col4 {\r\n    border-top: grey 2px solid;\r\n    padding-top:3px;\r\n}\r\n.bslisttable th.firstrow {\r\n    border-bottom: grey 2px solid;\r\n}\r\n.bslisttable tr.lastrow th {\r\n    border-bottom:2px solid grey;\r\n    padding-bottom:3px;\r\n}\r\n\r\n.bslisttable tr.bsodd td {\r\n    background-color:#FFFFFF;\r\n}\r\n.bslisttable tr.bseven td {\r\n    background-color:#FFFFF0;\r\n}\r\n\r\n.bslisttable .date {\r\n    white-space:nowrap;\r\n    font-size:1.2em;\r\n    color:grey;\r\n    font-weight:bold;\r\n}\r\n.bslisttable .scripture1 {\r\n    white-space:nowrap;\r\n    color:#c02121;\r\n    font-weight:bold;\r\n}\r\n.bslisttable .scripture2 {\r\n    white-space:nowrap;\r\n    color:#c02121;\r\n    font-weight:bold;\r\n}\r\n.bslisttable .title {\r\n    font-size:1.2em;\r\n    color:#707070;\r\n    font-weight:bold;\r\n}\r\n.bslisttable .series_text {\r\n    white-space:nowrap;\r\n    color:grey;\r\n}\r\n.bslisttable .duration {\r\n    white-space:nowrap;\r\n    font-style:italic;\r\n}\r\n.bslisttable .studyintro {\r\n\r\n}\r\n.bslisttable .teacher {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .location_text {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .topic_text {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .message_type {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .jbsmedia {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .store {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .details-text {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .details-pdf {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .details-text-pdf {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .detailstable td {\r\n    border: none;\r\n    padding: 0 2px 0 0;\r\n}\r\n.bslisttable .secondary_reference {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .teacher-title-name {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .submitted {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .hits {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .studynumber {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .filesize {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .custom {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .commentshead {\r\n    font-size: 2em;\r\n    font-weight:bold;\r\n}\r\n.bslisttable .thumbnail {\r\n    white-space:nowrap;\r\n}\r\n.bslisttable .mediatable td {\r\n    border: none;\r\n    padding: 0 6px 0 0;\r\n}\r\n.bslisttable .mediatable span.bsfilesize {\r\n    font-size:0.6em;\r\n    position:relative; bottom: 7px;\r\n}\r\n\r\n.component-content ul\r\n{\r\n    text-align: center;\r\n}\r\n\r\n.component-content li\r\n{\r\n    display: inline;\r\n}\r\n\r\n.pagenav\r\n{\r\n    margin-left: 10px;\r\n    margin-right: 10px;\r\n}\r\n\r\n/* Study Details CSS */\r\n\r\n#bsmsdetailstable {\r\n    margin: 0;\r\n    border-collapse:separate;\r\n}\r\n#bsmsdetailstable th, #bsmsdetailstable td {\r\n    text-align:left;\r\n    padding:0 5px 0 5px;\r\n    border:none;\r\n}\r\n#bsmsdetailstable .row1col1,\r\n#bsmsdetailstable .row2col1,\r\n#bsmsdetailstable .row3col1,\r\n#bsmsdetailstable .row4col1 {\r\n    border-left: grey 2px solid;\r\n}\r\n#bsmsdetailstable .lastcol {\r\n    border-right: grey 2px solid;\r\n}\r\n#bsmsdetailstable .lastrow td {\r\n    border-bottom:2px solid grey;\r\n    padding-bottom:7px;\r\n}\r\n#bsmsdetailstable th {\r\n    background-color:#707070;\r\n    font-weight:bold;\r\n    color:white;\r\n\r\n}\r\n#bsmsdetailstable th.row1col1,\r\n#bsmsdetailstable th.row1col2,\r\n#bsmsdetailstable th.row1col3,\r\n#bsmsdetailstable th.row1col4 {\r\n    border-top: grey 2px solid;\r\n    padding-top:3px;\r\n}\r\n#bsmsdetailstable tr.lastrow th {\r\n    border-bottom:2px solid grey;\r\n    padding-bottom:3px;\r\n}\r\n#bsmsdetailstable th.firstrow {\r\n    border-bottom: grey 2px solid;\r\n}\r\n#bsmsdetailstable tr.bsodd td {\r\n    background-color:#FFFFFF;\r\n}\r\n#bsmsdetailstable tr.bseven td {\r\n    background-color:#FFFFF0;\r\n}\r\n\r\n#bsmsdetailstable .date {\r\n    white-space:nowrap;\r\n    font-size:1.2em;\r\n    color:grey;\r\n    font-weight:bold;\r\n}\r\n#bsmsdetailstable .scripture1 {\r\n    white-space:nowrap;\r\n    color:#c02121;\r\n    font-weight:bold;\r\n}\r\n#bsmsdetailstable .scripture2 {\r\n    white-space:nowrap;\r\n    color:#c02121;\r\n    font-weight:bold;\r\n}\r\n#bsmsdetailstable .title {\r\n    font-size:1.2em;\r\n    color:#707070;\r\n    font-weight:bold;\r\n}\r\n#bsmsdetailstable .series_text {\r\n    white-space:nowrap;\r\n    color:grey;\r\n}\r\n#bsmsdetailstable .duration {\r\n    white-space:nowrap;\r\n    font-style:italic;\r\n}\r\n#bsmsdetailstable .studyintro {\r\n\r\n}\r\n#bsmsdetailstable .teacher {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .location_text {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .topic_text {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .message_type {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .jbsmedia {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .store {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .details-text {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .details-pdf {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .details-text-pdf {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .detailstable td {\r\n    border: none;\r\n    padding: 0 2px 0 0;\r\n}\r\n#bsmsdetailstable .secondary_reference {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .teacher-title-name {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .submitted {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .hits {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .studynumber {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .filesize {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .custom {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .commentshead {\r\n    font-size: 2em;\r\n    font-weight:bold;\r\n}\r\n#bsmsdetailstable .thumbnail {\r\n    white-space:nowrap;\r\n}\r\n#bsmsdetailstable .mediatable td {\r\n    border: none;\r\n    padding: 0 6px 0 0;\r\n}\r\n#bsmsdetailstable .mediatable span.bsfilesize {\r\n    font-size:0.6em;\r\n    position:relative; bottom: 7px;\r\n}\r\n#bsdetailstable th, #bsdetailstable td {\r\n    text-align:left;\r\n    padding:0 5px 0 5px;\r\n    border:none;\r\n}\r\n#bsdetailstable .studydetailstext td {\r\n    font-size:1.2em;\r\n    color:#707070;\r\n    font-family:Verdana, Geneva, sans-serif;\r\n}\r\n#titletable {\r\n    margin: 0;\r\n    border-collapse:separate;\r\n}\r\n#titletable th, #titletable td {\r\n    text-align:left;\r\n    padding:0 5px 0 5px;\r\n    border:none;\r\n}\r\n\r\n#titletable .titlesecondline {\r\n    font-weight: bold;\r\n}\r\n#titletable .titlefirstline {\r\n    font-size:20px;\r\n    font-weight:bold;\r\n}\r\n\r\n#recaptcha_widget_div {\r\n    position:static !important;\r\n}\r\n/* Module Style Settings */\r\n\r\n#bsmsmoduletable {\r\n    margin: 0;\r\n    border-collapse:separate;\r\n}\r\n#bsmsmoduletable th, #bsmsmoduletable td {\r\n    text-align:left;\r\n    padding:0 5px 0 5px;\r\n    border:none;\r\n}\r\n#bsmsmoduletable .row1col1,\r\n#bsmsmoduletable .row2col1,\r\n#bsmsmoduletable .row3col1,\r\n#bsmsmoduletable .row4col1 {\r\n    border-left: grey 2px solid;\r\n}\r\n#bsmsmoduletable .lastcol {\r\n    border-right: grey 2px solid;\r\n}\r\n#bsmsmoduletable .lastrow td {\r\n    border-bottom:2px solid grey;\r\n    padding-bottom:7px;\r\n}\r\n#bsmsmoduletable th {\r\n    background-color:#707070;\r\n    font-weight:bold;\r\n    color:white;\r\n\r\n}\r\n#bsmsmoduletable th.row1col1,\r\n#bsmsmoduletable th.row1col2,\r\n#bsmsmoduletable th.row1col3,\r\n#bsmsmoduletable th.row1col4 {\r\n    border-top: grey 2px solid;\r\n    padding-top:3px;\r\n}\r\n#bsmsmoduletable th.firstrow {\r\n    border-bottom: grey 2px solid;\r\n}\r\n#bsmsmoduletable tr.lastrow th {\r\n    border-bottom:2px solid grey;\r\n    padding-bottom:3px;\r\n}\r\n\r\n#bsmsmoduletable tr.bsodd td {\r\n    background-color:#FFFFFF;\r\n}\r\n#bsmsmoduletable tr.bseven td {\r\n    background-color:#FFFFF0;\r\n}\r\n\r\n#bsmsmoduletable .date {\r\n    white-space:nowrap;\r\n    font-size:1.2em;\r\n    color:grey;\r\n    font-weight:bold;\r\n}\r\n#bsmsmoduletable .scripture1 {\r\n    white-space:nowrap;\r\n    color:#c02121;\r\n    font-weight:bold;\r\n}\r\n#bsmsmoduletable .scripture2 {\r\n    white-space:nowrap;\r\n    color:#c02121;\r\n    font-weight:bold;\r\n}\r\n#bsmsmoduletable .title {\r\n    font-size:1.2em;\r\n    color:#707070;\r\n    font-weight:bold;\r\n}\r\n#bsmsmoduletable .series_text {\r\n    white-space:nowrap;\r\n    color:grey;\r\n}\r\n#bsmsmoduletable .duration {\r\n    white-space:nowrap;\r\n    font-style:italic;\r\n}\r\n#bsmsmoduletable .studyintro {\r\n\r\n}\r\n#bsmsmoduletable .teacher {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .location_text {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .topic_text {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .message_type {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .jbsmedia {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .store {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .details-text {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .details-pdf {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .details-text-pdf {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .detailstable td {\r\n    border: none;\r\n    padding: 0 2px 0 0;\r\n}\r\n#bsmsmoduletable .secondary_reference {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .teacher-title-name {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .submitted {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .hits {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .studynumber {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .filesize {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .custom {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .commentshead {\r\n    font-size: 2em;\r\n    font-weight:bold;\r\n}\r\n#bsmsmoduletable .thumbnail {\r\n    white-space:nowrap;\r\n}\r\n#bsmsmoduletable .mediatable td {\r\n    border: none;\r\n    padding: 0 6px 0 0;\r\n}\r\n#bsmsmoduletable .mediatable span.bsfilesize {\r\n    font-size:0.6em;\r\n    position:relative; bottom: 7px;\r\n}\r\n/* Series List-Details Items */\r\n#seriestable {\r\n    margin: 0;\r\n    border-collapse:separate;\r\n}\r\n#seriestable th, #seriestable td {\r\n    text-align:left;\r\n    padding: 3px 3px 3px 3px;\r\n    border:none;\r\n}\r\n#seriestable .firstrow td {\r\n    border-top: grey 2px solid;\r\n}\r\n#seriestable .firstcol {\r\n    border-left: grey 2px solid;\r\n}\r\n#seriestable .lastcol {\r\n    border-right: grey 2px solid;\r\n}\r\n#seriestable .lastrow td {\r\n    border-bottom:2px solid grey;\r\n    border-left: 2px solid grey;\r\n    border-right: 2px solid grey;\r\n    padding-bottom:3px;\r\n}\r\n#seriesttable tr.bsodd td {\r\n    background-color:#FFFFFF;\r\n}\r\n#seriestable tr.bseven td {\r\n    background-color:#FFFFF0;\r\n}\r\n#seriestable tr.onlyrow td {\r\n    border-bottom: 2px solid grey;\r\n    border-top:  grey 2px solid;\r\n}\r\n#seriestable .thumbnail img {\r\n    border: 1px solid grey;\r\n}\r\n#seriestable .teacher img {\r\n    border: 1px solid grey;\r\n}\r\n#seriestable .title {\r\n    font-weight: bold;\r\n    font-size: larger;\r\n}\r\n#seriestable tr.noborder td{\r\n    border: none;\r\n}\r\n#seriestable .description p{\r\n    width:500px;\r\n}\r\n#seriestable .teacher {\r\n    font-weight: bold;\r\n}\r\n/* Series Detail Study Links Items */\r\n#seriesstudytable {\r\n    margin: 0;\r\n    border-collapse:separate;\r\n}\r\n#seriesstudytable th, #seriesstudytable td {\r\n    text-align:left;\r\n    padding:0 5px 0 5px;\r\n    border:none;\r\n}\r\n#seriesstudytable .row1col1,\r\n#seriesstudytable .row2col1,\r\n#seriesstudytable .row3col1,\r\n#seriesstudytable .row4col1 {\r\n    border-left: grey 2px solid;\r\n}\r\n#seriesstudytable .lastcol {\r\n    border-right: grey 2px solid;\r\n}\r\n#seriesstudytable .lastrow td {\r\n    border-bottom:2px solid grey;\r\n    padding-bottom:7px;\r\n}\r\n#seriesstudytable th {\r\n    background-color:#707070;\r\n    font-weight:bold;\r\n    color:white;\r\n\r\n}\r\n#seriesstudytable th.row1col1,\r\n#seriesstudytable th.row1col2,\r\n#seriesstudytable th.row1col3,\r\n#seriesstudytable th.row1col4 {\r\n    border-top: grey 2px solid;\r\n    padding-top:3px;\r\n}\r\n#seriesstudytable th.firstrow {\r\n    border-bottom: grey 2px solid;\r\n}\r\n#seriesstudytable tr.lastrow th {\r\n    border-bottom:2px solid grey;\r\n    padding-bottom:3px;\r\n}\r\n\r\n#seriesstudytable tr.bsodd td {\r\n    background-color:#FFFFFF;\r\n}\r\n#seriesstudytable tr.bseven td {\r\n    background-color:#FFFFF0;\r\n}\r\n\r\n#seriesstudytable .date {\r\n    white-space:nowrap;\r\n    font-size:1.2em;\r\n    color:grey;\r\n    font-weight:bold;\r\n}\r\n#seriesstudytable .scripture1 {\r\n    white-space:nowrap;\r\n    color:#c02121;\r\n    font-weight:bold;\r\n}\r\n#seriesstudytable .scripture2 {\r\n    white-space:nowrap;\r\n    color:#c02121;\r\n    font-weight:bold;\r\n}\r\n#seriesstudytable .title {\r\n    font-size:1.2em;\r\n    color:#707070;\r\n    font-weight:bold;\r\n    font-style:italic;\r\n}\r\n#seriesstudytable .series_text {\r\n    white-space:nowrap;\r\n    color:grey;\r\n}\r\n#seriesstudytable .duration {\r\n    white-space:nowrap;\r\n    font-style:italic;\r\n}\r\n#seriesstudytable .studyintro {\r\n\r\n}\r\n#seriesstudytable .teacher {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .location_text {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .topic_text {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .message_type {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .jbsmedia {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .store {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .details-text {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .details-pdf {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .details-text-pdf {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .detailstable td {\r\n    border: none;\r\n    padding: 0 2px 0 0;\r\n}\r\n#seriesstudytable .secondary_reference {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .teacher-title-name {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .submitted {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .hits {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .studynumber {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .filesize {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .custom {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .commentshead {\r\n    font-size: 2em;\r\n    font-weight:bold;\r\n}\r\n#seriesstudytable .thumbnail {\r\n    white-space:nowrap;\r\n}\r\n#seriesstudytable .mediatable td {\r\n    border: none;\r\n    padding: 0 6px 0 0;\r\n}\r\n#seriesstudytable .mediatable span.bsfilesize {\r\n    font-size:0.6em;\r\n    position:relative; bottom: 7px;\r\n}\r\n#seriesstudytable .studyrow {\r\n\r\n}\r\n.tool-tip {\r\n    color: #fff;\r\n    width: 300px;\r\n    z-index: 13000;\r\n}\r\n/* Tooltip Styles */\r\n/* @todo need to find these files */\r\n.tool-title {\r\n    font-weight: bold;\r\n    font-size: 11px;\r\n    margin: 0;\r\n    color: #9FD4FF;\r\n    padding: 8px 8px 4px;\r\n    background: url(/images/tooltip/bubble.gif) top left;\r\n}\r\n.tool-text {\r\n    font-size: 11px;\r\n    padding: 4px 8px 8px;\r\n    background: url(/images/tooltip/bubble_filler.gif) bottom right;\r\n}\r\n.custom-tip {\r\n    color: #000;\r\n    width: 300px;\r\n    z-index: 13000;\r\n    border: 2px solid #666666;\r\n    background-color: white;\r\n}\r\n\r\n.custom-title {\r\n    font-weight: bold;\r\n    font-size: 11px;\r\n    margin: 0;\r\n    color: #000000;\r\n    padding: 8px 8px 4px;\r\n    background: #666666;\r\n    border-bottom: 1px solid #999999;\r\n}\r\n\r\n.custom-text {\r\n    font-size: 11px;\r\n    padding: 4px 8px 8px;\r\n    background: #999999;\r\n}\r\n/* Teacher List Styles */\r\n#bsm_teachertable\r\n{\r\n    margin: 0;\r\n    border-collapse:separate;\r\n}\r\n#bsm_teachertable td {\r\n    text-align:left;\r\n    padding:0 5px 0 5px;\r\n    border:none;\r\n}\r\n#bsm_teachertable .titlerow\r\n{\r\n    border-bottom: thick;\r\n}\r\n#bsm_teachertable .title\r\n{\r\n    font-size:18px;\r\n    font-weight:bold;\r\n    border-bottom: 3px solid #999999;\r\n    padding: 4px 0px 4px 4px;\r\n}\r\n#bsm_teachertable .bsm_separator\r\n{\r\n    border-bottom: 1px solid #999999;\r\n}\r\n\r\n.bsm_teacherthumbnail\r\n{\r\n\r\n}\r\n#bsm_teachertable .bsm_teachername\r\n{\r\n    font-weight: bold;\r\n    font-size: 14px;\r\n    color: #000000;\r\n    white-space:nowrap;\r\n\r\n}\r\n#bsm_teachertable .bsm_teacheremail\r\n{\r\n    font-weight:normal;\r\n    font-size: 11px;\r\n}\r\n#bsm_teachertable .bsm_teacherwebsite\r\n{\r\n    font-weight:normal;\r\n    font-size: 11px;\r\n}\r\n#bsm_teachertable .bsm_teacherphone\r\n{\r\n    font-weight:normal;\r\n    font-size: 11px;\r\n}\r\n#bsm_teachertable .bsm_short\r\n{\r\n    padding: 8px 4px 4px;\r\n}\r\n#bsm_teachertable .bsm_studiestitlerow {\r\n    background-color: #666;\r\n}\r\n#bsm_teachertable .bsm_titletitle\r\n{\r\n    font-weight:bold;\r\n    color:#FFFFFF;\r\n}\r\n#bsm_teachertable .bsm_titlescripture\r\n{\r\n    font-weight:bold;\r\n    color:#FFFFFF;\r\n}\r\n#bsm_teachertable .bsm_titledate\r\n{\r\n    font-weight:bold;\r\n    color:#FFFFFF;\r\n}\r\n#bsm_teachertable .bsm_teacherlong\r\n{\r\n    padding: 8px 4px 4px;\r\n    border-bottom: 1px solid #999999;\r\n}\r\n#bsm_teachertable tr.bsodd {\r\n    background-color:#FFFFFF;\r\n    border-bottom: 1px solid #999999;\r\n}\r\n#bsm_teachertable tr.bseven {\r\n    background-color:#FFFFF0;\r\n    border-bottom: 1px solid #999999;\r\n}\r\n\r\n#bsm_teachertable .lastrow td {\r\n    border-bottom:1px solid grey;\r\n    padding-bottom:7px;\r\n    padding-top:7px;\r\n}\r\n#bsm_teachertable .bsm_teacherfooter\r\n{\r\n    border-top: 1px solid #999999;\r\n    padding: 4px 1px 1px 4px;\r\n}\r\n/*Study Edit CSS */\r\n\r\n.bsmbutton\r\n{\r\n    background-color:white;\r\n\r\n}\r\n#toolbar td.white {\r\n    background-color:#FFFFFF;\r\n}\r\n#toolbar a hover visited{\r\n    color:#0B55C4;\r\n}\r\n\r\n/*Social Networking Items */\r\n#bsmsshare {\r\n    margin: 0;\r\n    border-collapse:separate;\r\n    float:right;\r\n    border: 1px solid #CFCFCF;\r\n    background-color: #F5F5F5;\r\n}\r\n#bsmsshare th, #bsmsshare td {\r\n    text-align:center;\r\n    padding:0 0 0 0;\r\n    border:none;\r\n}\r\n#bsmsshare th {\r\n    color:#0b55c4;\r\n    font-weight:bold;\r\n}\r\n/* Landing Page Items */\r\n.landinglist {\r\n\r\n}\r\n#landing_label {\r\n\r\n}\r\n.landing_item {\r\n\r\n}\r\n.landing_title {\r\n    font-family:arial;\r\n    font-size:16px;\r\n    font-weight:bold;\r\n\r\n}\r\n#biblestudy_landing {\r\n\r\n}\r\n#showhide {\r\n    font-family:arial;\r\n    font-size:12px;\r\n    font-weight:bold;\r\n    text-decoration:none;\r\n}\r\n\r\n#showhide .showhideheadingbutton img {\r\n    vertical-align:bottom;\r\n}\r\n\r\n.landing_table {\r\n\r\n}\r\n\r\n#landing_td {\r\n    width: 33%;\r\n}\r\n\r\n.landing_separator {\r\n    height:15px;\r\n}\r\n/* Popup Window Items */\r\n.popupwindow\r\n{\r\n    margin: 5px;\r\n    text-align:center;\r\n}\r\np.popuptitle {\r\n    font-weight: bold;\r\n    color: black;\r\n}\r\n\r\n.popupfooter\r\n{\r\n    margin: 5px;\r\n    text-align:center;\r\n}\r\np.popupfooter {\r\n    font-weight: bold;\r\n    color: grey;\r\n}\r\n#main ul, #main li\r\n{\r\n    display: inline;\r\n}\r\n\r\n.component-content ul\r\n{\r\n    text-align: center;\r\n}\r\n\r\n.component-content li\r\n{\r\n    display: inline;\r\n}\r\n\r\n.pagenav\r\n{\r\n    margin-left: 10px;\r\n    margin-right: 10px;\r\n}\r\n\r\n#recaptcha_widget_div {\r\n    position:static !important;}', 0);

--
-- Dumping data for table `#__bsms_teachers`
--

INSERT INTO `#__bsms_teachers` (`id`, `teacher_image`, `teacher_thumbnail`, `teachername`, `alias`, `title`, `phone`, `email`, `website`, `information`, `image`, `imageh`, `imagew`, `thumb`, `thumbw`, `thumbh`, `short`, `ordering`, `catid`, `list_show`, `published`, `asset_id`, `access`, `language`) VALUES
(1, '', '', 'Billy Sunday', 'billy-sunday', 'Pastor', '555-555-5555', 'billy@sunday.com', 'http://billysunday.com', 'William Ashley Sunday was an American athlete who after being a popular outfielder in baseballs National League during the 1880s became the most celebrated and influential American evangelist during the first two decades of the 20th century. ', 'media/com_biblestudy/images/billy_sunday11.jpg', '276', '197', 'media/com_biblestudy/images/images.jpg', '101', '141', 'Billy Sunday: 1862-1935', 0, 1, 1, 1, 0, 1, '*');

--
-- Dumping data for table `#__bsms_templates`
--

INSERT INTO `#__bsms_templates` (`id`, `type`, `tmpl`, `published`, `params`, `title`, `text`, `pdf`, `asset_id`, `access`) VALUES
(1, 'tmplList', '', 1, '{"useterms":"0","terms":"","css":"biblestudy.css","studieslisttemplateid":"1","sermonstemplate":"0","detailstemplateid":"1","sermontemplate":"0","teachertemplateid":"1","teachertemplate":"0","teacherstemplate":"0","serieslisttemplateid":"1","seriesdisplaystemplate":"0","seriesdetailtemplateid":"1","seriesdisplaytemplate":"0","teacher_id":["-1"],"series_id":["-1"],"booknumber":["-1"],"topic_id":["-1"],"messagetype":["-1"],"locations":["-1"],"show_verses":"0","stylesheet":"","date_format":"2","custom_date_format":"","duration_type":"2","protocol":"http:\\/\\/","media_player":"0","popuptype":"window","internal_popup":"1","autostart":"1","player_width":"400","player_height":"300","embedshare":"TRUE","backcolor":"0x287585","frontcolor":"0xFFFFFF","lightcolor":"0x000000","screencolor":"0x000000","popuptitle":"{{title}}","popupfooter":"{{filename}}","popupmargin":"50","popupbackground":"black","popupimage":"components\\/com_biblestudy\\/images\\/speaker24.png","show_filesize":"1","useexpert_list":"0","headercode":"","templatecode":"                                   {{teacher}}             {{title}}             {{date}}                                   {{studyintro}}             {{scripture}}                               ","wrapcode":"0","listlanguage":"0","default_order":"DESC","show_page_title":"1","show_page_image":"1","page_title":"Bible Studies","use_headers_list":"1","list_intro":"","intro_show":"1","list_teacher_show":"1","listteachers":"","teacherlink":"1","showpodcastsubscribelist":"1","subscribeintro":"Our Podcasts","details_text":"Study Details","show_book_search":"1","use_go_button":"1","booklist":"1","show_teacher_search":"1","show_series_search":"1","show_type_search":"1","show_year_search":"1","show_order_search":"1","show_topic_search":"1","show_locations_search":"1","show_popular":"1","show_pagination":"1","row1col1":"10","r1c1custom":"","r1c1customlabel":"","r1c1span":"1","linkr1c1":"0","row1col2":"5","r1c2custom":"","r1c2customlabel":"","r1c2span":"2","linkr1c2":"0","row1col3":"0","r1c3custom":"","r1c3customlabel":"","r1c3span":"1","linkr1c3":"0","row1col4":"20","r1c4custom":"","r1c4customlabel":"","linkr1c4":"0","row2col1":"9","r2c1custom":"","r2c1customlabel":"","r2c1span":"1","linkr2c1":"0","row2col2":"7","r2c2custom":"","r2c2customlabel":"","r2c2span":"1","linkr2c2":"0","row2col3":"1","r2c3custom":"","r2c3customlabel":"","r2c3span":"1","linkr2c3":"0","row2col4":"2","r2c4custom":"","r2c4customlabel":"","linkr2c4":"0","row3col1":"6","r3c1custom":"","r3c1customlabel":"","r3c1span":"4","linkr3c1":"0","row3col2":"0","r3c2custom":"","r3c2customlabel":"","r3c2span":"1","linkr3c2":"0","row3col3":"0","r3c3custom":"","r3c3customlabel":"","r3c3span":"1","linkr3c3":"0","row3col4":"0","r3c4custom":"","r3c4customlabel":"","linkr3c4":"0","row4col1":"0","r4c1custom":"","r4c1customlabel":"","r4c1span":"1","linkr4c1":"0","row4col2":"0","r4c2custom":"","r4c2customlabel":"","r4c2span":"1","linkr4c2":"0","row4col3":"0","r4c3custom":"","r4c3customlabel":"","r4c3span":"1","linkr4c3":"0","row4col4":"0","r4c4custom":"","r4c4customlabel":"","linkr4c4":"0","show_print_view":"1","show_teacher_view":"0","use_headers_view":"1","list_items_view":"0","title_line_1":"1","customtitle1":"","title_line_2":"4","customtitle2":"","view_link":"1","link_text":"Return to Studies List","showrelated":"1","showpodcastsubscribedetails":"1","show_scripture_link":"0","show_passage_view":"1","bible_version":"51","show_comments":"1","link_comments":"0","comment_access":"1","comment_publish":"0","use_captcha":"1","public_key":"","private_key":"","email_comments":"1","recipient":"","subject":"Comments on studies","body":"Comments entered.","useexpert_details":"0","study_detailtemplate":"","teacher_title":"Our Teachers","show_teacher_studies":"1","studies":"","label_teacher":"Latest Messages","useexpert_teacherlist":"0","teacher_headercode":"","teacher_templatecode":"           {{teacher}}     {{title}}     {{teacher}}           {{short}}     {{information}}       ","teacher_wrapcode":"0","useexpert_teacherdetail":"0","teacher_detailtemplate":"           {{teacher}}     {{title}}     {{teacher}}           {{short}}     {{information}}       ","series_title":"Our Series","show_series_title":"1","show_page_image_series":"1","series_show_description":"1","series_characters":"","search_series":"1","series_limit":"5","series_list_order":"ASC","series_order_field":"series_text","serieselement1":"1","seriesislink1":"1","serieselement2":"6","seriesislink2":"1","serieselement3":"0","seriesislink3":"1","serieselement4":"0","seriesislink4":"1","useexpert_serieslist":"0","series_headercode":"","series_templatecode":"","series_wrapcode":"0","series_detail_sort":"studydate","series_detail_order":"DESC","series_detail_limit":"","series_list_return":"1","series_detail_listtype":"0","series_detail_1":"5","series_detail_islink1":"1","series_detail_2":"7","series_detail_islink2":"0","series_detail_3":"10","series_detail_islink3":"0","series_detail_4":"20","series_detail_islink4":"0","useexpert_seriesdetail":"0","series_detailcode":"","tip_title":"Sermon Information","tooltip":"1","tip_item1_title":"Title","tip_item1":"5","tip_item2_title":"Details","tip_item2":"6","tip_item3_title":"Teacher","tip_item3":"7","tip_item4_title":"Reference","tip_item4":"1","tip_item5_title":"Date","tip_item5":"10","drow1col1":"5","dr1c1custom":"","dr1c1customlabel":"","dr1c1span":"2","dlinkr1c1":"0","drow1col2":"0","dr1c2custom":"","dr1c2customlabel":"","dr1c2span":"1","dlinkr1c2":"0","drow1col3":"8","dr1c3custom":"","dr1c3customlabel":"","dr1c3span":"2","dlinkr1c3":"0","drow1col4":"0","dr1c4custom":"","dr1c4customlabel":"","dlinkr1c4":"0","drow2col1":"1","dr2c1custom":"","dr2c1customlabel":"","dr2c1span":"1","dlinkr2c1":"0","drow2col2":"2","dr2c2custom":"","dr2c2customlabel":"","dr2c2span":"1","dlinkr2c2":"0","drow2col3":"3","dr2c3custom":"","dr2c3customlabel":"","dr2c3span":"2","dlinkr2c3":"0","drow2col4":"0","dr2c4custom":"","dr2c4customlabel":"","dlinkr2c4":"0","drow3col1":"10","dr3c1custom":"","dr3c1customlabel":"","dr3c1span":"1","dlinkr3c1":"0","drow3col2":"9","dr3c2custom":"","dr3c2customlabel":"","dr3c2span":"1","dlinkr3c2":"0","drow3col3":"20","dr3c3custom":"","dr3c3customlabel":"","dr3c3span":"2","dlinkr3c3":"0","drow3col4":"0","dr3c4custom":"","dr3c4customlabel":"","dlinkr3c4":"0","drow4col1":"6","dr4c1custom":"","dr4c1customlabel":"","dr4c1span":"4","dlinkr4c1":"0","drow4col2":"0","dr4c2custom":"","dr4c2customlabel":"","dr4c2span":"1","dlinkr4c2":"0","drow4col3":"0","dr4c3custom":"","dr4c3customlabel":"","dr4c3span":"1","dlinkr4c3":"0","drow4col4":"0","dr4c4custom":"","dr4c4customlabel":"","dlinkr4c4":"0","landing_hide":"0","landing_default_order":"ASC","landing_hidelabel":"Show\\/Hide All","headingorder_1":"teachers","headingorder_2":"series","headingorder_3":"books","headingorder_4":"topics","headingorder_5":"locations","headingorder_6":"messagetypes","headingorder_7":"years","showteachers":"1","landingteachersuselimit":"0","landingteacherslimit":"","teacherslabel":"Speakers","linkto":"1","showseries":"1","landingseriesuselimit":"0","landingserieslimit":"","serieslabel":"Series","series_linkto":"0","showbooks":"1","landingbookslimit":"","bookslabel":"Books","showtopics":"1","landingtopicslimit":"","topicslabel":"Topics","showlocations":"1","landinglocationsuselimit":"0","landinglocationslimit":"","locationslabel":"Locations","showmessagetypes":"1","landingmessagetypeuselimit":"0","landingmessagetypeslimit":"","messagetypeslabel":"Message Types","showyears":"1","landingyearslimit":"","yearslabel":"Years"}', 'Default', 'textfile24.png', 'pdf24.png', 0, 1);

--
-- Dumping data for table `#__bsms_timeset`
--

INSERT INTO `#__bsms_timeset` (`timeset`, `backup`) VALUES
('1281646339', '1281646339');

--
-- Dumping data for table `#__bsms_topics`
--

INSERT INTO `#__bsms_topics` (`id`, `topic_text`, `published`, `params`, `asset_id`, `access`) VALUES
(1, 'JBS_TOP_ABORTION', 1, NULL, 0, 1),
(3, 'JBS_TOP_ADDICTION', 1, NULL, 0, 1),
(4, 'JBS_TOP_AFTERLIFE', 1, NULL, 0, 1),
(5, 'JBS_TOP_APOLOGETICS', 1, NULL, 0, 1),
(7, 'JBS_TOP_BAPTISM', 1, NULL, 0, 1),
(8, 'JBS_TOP_BASICS_OF_CHRISTIANITY', 1, NULL, 0, 1),
(9, 'JBS_TOP_BECOMING_A_CHRISTIAN', 1, NULL, 0, 1),
(10, 'JBS_TOP_BIBLE', 1, NULL, 0, 1),
(37, 'JBS_TOP_BLENDED_FAMILY_RELATIONSHIPS', 1, NULL, 0, 1),
(12, 'JBS_TOP_CHILDREN', 1, NULL, 0, 1),
(13, 'JBS_TOP_CHRIST', 1, NULL, 0, 1),
(14, 'JBS_TOP_CHRISTIAN_CHARACTER_FRUITS', 1, NULL, 0, 1),
(15, 'JBS_TOP_CHRISTIAN_VALUES', 1, NULL, 0, 1),
(16, 'JBS_TOP_CHRISTMAS_SEASON', 1, NULL, 0, 1),
(17, 'JBS_TOP_CHURCH', 1, NULL, 0, 1),
(18, 'JBS_TOP_COMMUNICATION', 1, NULL, 0, 1),
(19, 'JBS_TOP_COMMUNION___LORDS_SUPPER', 1, NULL, 0, 1),
(21, 'JBS_TOP_CREATION', 1, NULL, 0, 1),
(23, 'JBS_TOP_CULTS', 1, NULL, 0, 1),
(113, 'JBS_TOP_DA_VINCI_CODE', 1, NULL, 0, 1),
(24, 'JBS_TOP_DEATH', 1, NULL, 0, 1),
(26, 'JBS_TOP_DESCRIPTIONS_OF_GOD', 1, NULL, 0, 1),
(27, 'JBS_TOP_DISCIPLES', 1, NULL, 0, 1),
(28, 'JBS_TOP_DISCIPLESHIP', 1, NULL, 0, 1),
(30, 'JBS_TOP_DIVORCE', 1, NULL, 0, 1),
(32, 'JBS_TOP_EASTER_SEASON', 1, NULL, 0, 1),
(33, 'JBS_TOP_EMOTIONS', 1, NULL, 0, 1),
(34, 'JBS_TOP_ENTERTAINMENT', 1, NULL, 0, 1),
(35, 'JBS_TOP_EVANGELISM', 1, NULL, 0, 1),
(36, 'JBS_TOP_FAITH', 1, NULL, 0, 1),
(103, 'JBS_TOP_FAMILY', 1, NULL, 0, 1),
(39, 'JBS_TOP_FORGIVING_OTHERS', 1, NULL, 0, 1),
(104, 'JBS_TOP_FREEDOM', 1, NULL, 0, 1),
(41, 'JBS_TOP_FRIENDSHIP', 1, NULL, 0, 1),
(42, 'JBS_TOP_FULFILLMENT_IN_LIFE', 1, NULL, 0, 1),
(43, 'JBS_TOP_FUND_RAISING_RALLY', 1, NULL, 0, 1),
(44, 'JBS_TOP_FUNERALS', 1, NULL, 0, 1),
(45, 'JBS_TOP_GIVING', 1, NULL, 0, 1),
(2, 'JBS_TOP_GODS_ACTIVITY', 1, NULL, 0, 1),
(6, 'JBS_TOP_GODS_ATTRIBUTES', 1, NULL, 0, 1),
(40, 'JBS_TOP_GODS_FORGIVENESS', 1, NULL, 0, 1),
(58, 'JBS_TOP_GODS_LOVE', 1, NULL, 0, 1),
(65, 'JBS_TOP_GODS_NATURE', 1, NULL, 0, 1),
(46, 'JBS_TOP_GODS_WILL', 1, NULL, 0, 1),
(47, 'JBS_TOP_HARDSHIP_OF_LIFE', 1, NULL, 0, 1),
(107, 'JBS_TOP_HOLIDAYS', 1, NULL, 0, 1),
(48, 'JBS_TOP_HOLY_SPIRIT', 1, NULL, 0, 1),
(111, 'JBS_TOP_HOT_TOPICS', 1, NULL, 0, 1),
(11, 'JBS_TOP_JESUS_BIRTH', 1, NULL, 0, 1),
(22, 'JBS_TOP_JESUS_CROSS_FINAL_WEEK', 1, NULL, 0, 1),
(29, 'JBS_TOP_JESUS_DIVINITY', 1, NULL, 0, 1),
(50, 'JBS_TOP_JESUS_HUMANITY', 1, NULL, 0, 1),
(56, 'JBS_TOP_JESUS_LIFE', 1, NULL, 0, 1),
(61, 'JBS_TOP_JESUS_MIRACLES', 1, NULL, 0, 1),
(84, 'JBS_TOP_JESUS_RESURRECTION', 1, NULL, 0, 1),
(93, 'JBS_TOP_JESUS_TEACHING', 1, NULL, 0, 1),
(52, 'JBS_TOP_KINGDOM_OF_GOD', 1, NULL, 0, 1),
(55, 'JBS_TOP_LEADERSHIP_ESSENTIALS', 1, NULL, 0, 1),
(57, 'JBS_TOP_LOVE', 1, NULL, 0, 1),
(59, 'JBS_TOP_MARRIAGE', 1, NULL, 0, 1),
(109, 'JBS_TOP_MEN', 1, NULL, 0, 1),
(82, 'JBS_TOP_MESSIANIC_PROPHECIES', 1, NULL, 0, 1),
(62, 'JBS_TOP_MISCONCEPTIONS_OF_CHRISTIANITY', 1, NULL, 0, 1),
(63, 'JBS_TOP_MONEY', 1, NULL, 0, 1),
(112, 'JBS_TOP_NARNIA', 1, NULL, 0, 1),
(66, 'JBS_TOP_OUR_NEED_FOR_GOD', 1, NULL, 0, 1),
(69, 'JBS_TOP_PARABLES', 1, NULL, 0, 1),
(70, 'JBS_TOP_PARANORMAL', 1, NULL, 0, 1),
(71, 'JBS_TOP_PARENTING', 1, NULL, 0, 1),
(73, 'JBS_TOP_POVERTY', 1, NULL, 0, 1),
(74, 'JBS_TOP_PRAYER', 1, NULL, 0, 1),
(76, 'JBS_TOP_PROMINENT_N_T__MEN', 1, NULL, 0, 1),
(77, 'JBS_TOP_PROMINENT_N_T__WOMEN', 1, NULL, 0, 1),
(78, 'JBS_TOP_PROMINENT_O_T__MEN', 1, NULL, 0, 1),
(79, 'JBS_TOP_PROMINENT_O_T__WOMEN', 1, NULL, 0, 1),
(83, 'JBS_TOP_RACISM', 1, NULL, 0, 1),
(85, 'JBS_TOP_SECOND_COMING', 1, NULL, 0, 1),
(86, 'JBS_TOP_SEXUALITY', 1, NULL, 0, 1),
(87, 'JBS_TOP_SIN', 1, NULL, 0, 1),
(88, 'JBS_TOP_SINGLENESS', 1, NULL, 0, 1),
(89, 'JBS_TOP_SMALL_GROUPS', 1, NULL, 0, 1),
(108, 'JBS_TOP_SPECIAL_SERVICES', 1, NULL, 0, 1),
(90, 'JBS_TOP_SPIRITUAL_DISCIPLINES', 1, NULL, 0, 1),
(91, 'JBS_TOP_SPIRITUAL_GIFTS', 1, NULL, 0, 1),
(105, 'JBS_TOP_STEWARDSHIP', 1, NULL, 0, 1),
(92, 'JBS_TOP_SUPERNATURAL', 1, NULL, 0, 1),
(94, 'JBS_TOP_TEMPTATION', 1, NULL, 0, 1),
(95, 'JBS_TOP_TEN_COMMANDMENTS', 1, NULL, 0, 1),
(97, 'JBS_TOP_TRUTH', 1, NULL, 0, 1),
(98, 'JBS_TOP_TWELVE_APOSTLES', 1, NULL, 0, 1),
(100, 'JBS_TOP_WEDDINGS', 1, NULL, 0, 1),
(110, 'JBS_TOP_WOMEN', 1, NULL, 0, 1),
(101, 'JBS_TOP_WORKPLACE_ISSUES', 1, NULL, 0, 1),
(102, 'JBS_TOP_WORLD_RELIGIONS', 1, NULL, 0, 1),
(106, 'JBS_TOP_WORSHIP', 1, NULL, 0, 1);