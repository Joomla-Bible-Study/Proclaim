<?php

/**
 * @author Tom Fuller - Joomla Bible Study
 * @copyright 2011
 */

defined( '_JEXEC' ) or die('Restricted access');
require_once ( JPATH_ROOT .DS.'libraries'.DS.'joomla'.DS.'html'.DS.'parameter.php' );
require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');

class JBSUpgrade
{

    function version()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__bsms_version';
        $db->setQuery($query);
        $db->query();
        $versions = $db->loadObjectList();
        if (!$versions)
        {
            //check to be sure a really early version is not installed 1 = older version 2 = no version 3 = correct version
            $query = "SELECT * FROM #__bsms_studies";
            $db->setQuery($query);
            $db->query();
            $oldversion = $db->loadObjectList();
            if ($oldversion) {$ver = 1; }
            if (!$oldversion){$ver = 2; }
        }
        else
        {
            foreach ($versions AS $version)
            {
                $build = $version->build;
                $ver = 1; 
                if ($build == '614')
                {
                    $ver = 3; 
                }
                if ($build > 614)
                {
                    $ver = 4;
                }
            }
        }
        switch ($ver)
        {
            case 1:
            $message = false;
            break;
            
            case 2:
        //    $message = $this->fresh();
            $message = 'fresh';
            break;
            
            case 3:
        //    $message = $this->upgrade();
            $message = 'upgrade';
            break;
            
            case 4:
            $message = '7.0.0 already installed. Refreshing install.';
            break;
        }
        return $message;
    }
    function upgrade()
    {
        $db = JFactory::getDBO();
        $msg = array();
        
        //Alter some tables
        $query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN `player` int(2) NULL";
        $msg[] = $this->performdb($query);
        $query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN `popup` int(2) NULL";
        $msg[] = $this->performdb($query);
        $query = "DROP TABLE #__bsms_timeset";
        $msg[] = $this->performdb($query);
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_timeset` (
                    `timeset` VARCHAR(14) ,
                    `backup` VARCHAR(14) ,
                    KEY `timeset` (`timeset`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
         $msg[] = $this->performdb($query);
        
        $query = "INSERT INTO `#__bsms_timeset` SET `timeset`='1281646339', `backup` = '1281646339'";
        $msg[] = $this->performdb($query);
        
        $query = "INSERT  INTO `#__bsms_media` VALUES (15,'You Tube','You Tube','','youtube24.png','You Tube Video', 1)";
        $msg[] = $this->performdb($query);
        
        //Run the 700 upgrade php file
        require(JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
        
        
        $query = "INSERT INTO #__bsms_version SET `version` = '7.0.0', `installdate`='2011-02-12', `build`='1390', `versionname`='1Kings', `versiondate`='2011-02-15'";
        $msg[] = $this->performdb($query);
        
        $res = '<table><tr><td>Upgrade Joomla Bible Study to version 7.0.0</td></tr>';  //santon 2010-12-28 convert to phrase
        if (count($msg) < 1){$res .= JText::_('JBS_INS_NO_ERROR');
		}
        else
        {
            
            $r = 'Queries or Errors: <br />';
            foreach ($msg AS $m)
            {
                $r .= $m.'<br />';
            }
        }
        $result_table = '<tr>
        		<td>
        			'.$res.$r.'
        		</td>
        	
        	</tr>';
        
        
        $result_table .= '</td></tr></table>';
        return $result_table;
    }
    
       
    function fresh()
    {
        $msg = array();
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_admin` (
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
					) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $msg[] = $this->performdb($query);
        
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_books` (
					  `id` int(3) NOT NULL AUTO_INCREMENT,
					  `bookname` varchar(250) DEFAULT NULL,
					  `booknumber` int(5) DEFAULT NULL,
					  `published` tinyint(1) NOT NULL DEFAULT '1',
					  PRIMARY KEY (`id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);
        
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_comments` (
					  `id` int(3) NOT NULL AUTO_INCREMENT,
					  `published` tinyint(1) NOT NULL DEFAULT '0',
					  `study_id` int(11) NOT NULL DEFAULT '0',
					  `user_id` int(11) NOT NULL DEFAULT '0',
					  `full_name` varchar(50) NOT NULL DEFAULT '',
					  `user_email` varchar(100) NOT NULL DEFAULT '',
					  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					  `comment_text` text NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);
        
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_folders` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `foldername` varchar(250) NOT NULL DEFAULT '',
				  `folderpath` varchar(250) NOT NULL DEFAULT '',
				  `published` tinyint(1) NOT NULL DEFAULT '1',
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);        
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_locations` (
    				  `id` int(11) NOT NULL AUTO_INCREMENT,
    				  `location_text` varchar(250) DEFAULT NULL,
    				  `published` tinyint(1) NOT NULL DEFAULT '1',
    				  PRIMARY KEY (`id`)
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_media` (
    				  `id` int(3) NOT NULL AUTO_INCREMENT,
    				  `media_text` text,
    				  `media_image_name` varchar(250) NOT NULL DEFAULT '',
    				  `media_image_path` varchar(250) NOT NULL DEFAULT '',
    				  `path2` varchar(150) NOT NULL,
    				  `media_alttext` varchar(250) NOT NULL DEFAULT '',
    				  `published` tinyint(1) NOT NULL DEFAULT '1',
    				  PRIMARY KEY (`id`)
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_mediafiles` (
    				  `id` int(3) NOT NULL AUTO_INCREMENT,
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
    				  `downloads` int(10) DEFAULT 0,
    				  `plays` int(10) DEFAULT 0,
    				  `params` text,
                      `player` int(2) NULL,
                      `popup` int(2) NULL,
    				  PRIMARY KEY (`id`)
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_message_type` (
    				  `id` int(11) NOT NULL AUTO_INCREMENT,
    				  `message_type` text NOT NULL,
    				  `published` tinyint(1) NOT NULL DEFAULT '1',
    				  PRIMARY KEY (`id`)
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_mimetype` (
    				  `id` int(3) NOT NULL AUTO_INCREMENT,
    				  `mimetype` varchar(50) DEFAULT NULL,
    				  `mimetext` varchar(50) DEFAULT NULL,
    				  `published` tinyint(1) NOT NULL DEFAULT '1',
    				  PRIMARY KEY (`id`)
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_order` (
    				  `id` int(11) NOT NULL AUTO_INCREMENT,
    				  `value` varchar(15) DEFAULT '',
    				  `text` varchar(20) DEFAULT '',
    				  PRIMARY KEY (`id`)
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_podcast` (
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
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_search` (
    				  `id` int(11) NOT NULL AUTO_INCREMENT,
    				  `value` varchar(15) DEFAULT '',
    				  `text` varchar(15) DEFAULT '',
    				  PRIMARY KEY (`id`)
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_series` (
    				  `id` int(3) NOT NULL AUTO_INCREMENT,
    				  `series_text` text,
    				  `teacher` int(3) DEFAULT NULL,
    				  `description` text,
    				  `series_thumbnail` varchar(150) DEFAULT NULL,
    				  `published` tinyint(1) NOT NULL DEFAULT '1',
    				  PRIMARY KEY (`id`)
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_servers` (
    				  `id` int(3) NOT NULL AUTO_INCREMENT,
    				  `server_name` varchar(250) NOT NULL DEFAULT '',
    				  `server_path` varchar(250) NOT NULL DEFAULT '',
    				  `published` tinyint(1) NOT NULL DEFAULT '1',
    				  `server_type` char(5) NOT NULL DEFAULT 'local',
    				  `ftp_username` char(255) NOT NULL,
    				  `ftp_password` char(255) NOT NULL,
    				  PRIMARY KEY (`id`)
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_share` (
    				  `id` int(11) NOT NULL AUTO_INCREMENT,
    				  `name` varchar(250) DEFAULT NULL,
    				  `params` text,
    				  `published` tinyint(1) NOT NULL DEFAULT '1',
    				  PRIMARY KEY (`id`)
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_studies` (
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
    				  PRIMARY KEY (`id`)
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_studytopics` (
    				  `id` int(3) NOT NULL AUTO_INCREMENT,
    				  `study_id` int(3) NOT NULL DEFAULT '0',
    				  `topic_id` int(3) NOT NULL DEFAULT '0',
    				  PRIMARY KEY (`id`),
    				  UNIQUE KEY `id` (`id`),
    				  KEY `id_2` (`id`)
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_teachers` (
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
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_templates` (
    				  `id` int(11) NOT NULL AUTO_INCREMENT,
    				  `type` varchar(255) NOT NULL,
    				  `tmpl` longtext NOT NULL,
    				  `published` int(1) NOT NULL DEFAULT '1',
    				  `params` longtext,
    				  `title` text,
    				  `text` text,
    				  `pdf` text,
    				  PRIMARY KEY (`id`)
    				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_topics` (
    				`id` INT NOT NULL AUTO_INCREMENT,
    				`topic_text` TEXT NULL,
    				`published` TINYINT(1) NOT NULL DEFAULT '1',
    				PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`";
        $msg[] = $this->performdb($query);            
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_timeset` (
                    `timeset` VARCHAR(14) ,
                    `backup` VARCHAR(14) ,
                    KEY `timeset` (`timeset`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
         $msg[] = $this->performdb($query);
         
         //Load up the default values
         
         $query = "INSERT INTO `#__bsms_timeset` SET `timeset`='1281646339', `backup` = '1281646339'";
         $msg[] = $this->performdb($query);
                
               
         $query = "INSERT INTO `#__bsms_studies` SET `studydate`='2010-03-13 00:10:00', `teacher_id`=1, `studynumber`='2010-001', `booknumber`='101', `chapter_begin`='01', `verse_begin`='01', `chapter_end`='01', `verse_end`='31', `studytitle`='Sample Study Title', `studyintro`='Sample text you can use as an introduction to your study', `studytext`='This is where you would put study notes or other information. This could be the full text of your study as well. If you install the scripture links plugin you will have all verses as links to BibleGateway.com'";
         $msg[] = $this->performdb($query);
         
         $query = "INSERT INTO `#__bsms_servers` SET `id`='1', `server_name`='Your Server Name', `server_path`='www.mywebsite.com', `published`='1'";
         $msg[] = $this->performdb($query);
         
         $query = "INSERT INTO `#__bsms_series` SET `id`=NULL, `series_text`='Worship Series', `published`='1'";
         $msg[] = $this->performdb($query);
         
         $query = "INSERT INTO `#__bsms_message_type` VALUES (NULL, 'Sunday', 1)";
         $msg[] = $this->performdb($query);
         
         $query = "INSERT INTO `#__bsms_folders` VALUES (NULL, 'My Folder Name', '/media/', 1)";
         $msg[] = $this->performdb($query);
         
         $query = "INSERT INTO `#__bsms_podcast` SET `id`=NULL, `title`='My Podcast', `website`='www.mywebsite.com', `description`='Podcast Description goes here', `image`='www.mywebsite.com/myimage.jpg', `imageh`='30', `imagew`='30', `author`='Pastor Billy', `podcastimage`='www.mywebsite.com/myimage.jpg', `podcastsearch`='jesus', `filename`='mypodcast.xml', `language`='en-us', `editor_name`='Jim Editor', `editor_email`='jim@mywebsite.com', `podcastlimit`=50, `published`='1'";
         $msg[] = $this->performdb($query);
         
         $query = "INSERT INTO `#__bsms_topics` VALUES (1,'Abortion',1) , (3,'Addiction',1) , (4,'Afterlife',1) , (5,'Apologetics',1) ,(7,'Baptism',1) ,(8,'Basics of Christianity',1) ,(9,'Becoming a Christian',1) ,(10,'Bible',1) ,(37,'Blended Family Relationships',1) ,(12,'Children',1) ,(13,'Christ',1) ,(14,'Christian Character/Fruits',1) ,(15,'Christian Values',1) ,(16,'Christmas Season',1) ,(17,'Church',1) ,(18,'Communication',1) ,(19,'Communion / Lords Supper',1) ,(21,'Creation',1) ,(23,'Cults',1) ,(113,'Da Vinci Code',1) ,(24,'Death',1) ,(26,'Descriptions of God',1) ,(27,'Disciples',1) ,(28,'Discipleship',1) ,(30,'Divorce',1) ,(32,'Easter Season',1) ,(33,'Emotions',1) ,(34,'Entertainment',1) ,(35,'Evangelism',1) ,(36,'Faith',1) ,(103,'Family',1) ,(39,'Forgiving Others',1) ,(104,'Freedom',1) ,(41,'Friendship',1) ,	(42,'Fulfillment in Life',1) ,(43,'Fund-raising rally',1) ,(44,'Funerals',1) ,(45,'Giving',1) ,(2,'Gods Activity',1) ,(6,'Gods Attributes',1) ,(40,'Gods Forgiveness',1) ,(58,'Gods Love',1) ,(65,'Gods Nature',1) ,(46,'Gods Will',1) ,(47,'Hardship of Life',1) ,(107,'Holidays',1) ,(48,'Holy Spirit',1) ,(111,'Hot Topics',1) ,(11,'Jesus Birth',1) ,(22,'Jesus Cross/Final Week',1) ,(29,'Jesus Divinity',1) ,(50,'Jesus Humanity',1) ,(56,'Jesus Life',1) ,(61,'Jesus Miracles',1) ,(84,'Jesus Resurrection',1) ,(93,'Jesus Teaching',1) ,	(52,'Kingdom of God',1) ,(55,'Leadership Essentials',1) ,(57,'Love',1) ,(59,'Marriage',1) ,(109,'Men',1) ,(82,'Messianic Prophecies',1) ,	(62,'Misconceptions of Christianity',1) ,(63,'Money',1) ,(112,'Narnia',1) ,(66,'Our Need for God',1) ,(69,'Parables',1) ,(70,'Paranormal',1) ,(71,'Parenting',1) ,(73,'Poverty',1) ,	(74,'Prayer',1) ,(76,'Prominent N.T. Men',1) ,(77,'Prominent N.T. Women',1) ,(78,'Prominent O.T. Men',1) ,(79,'Prominent O.T. Women',1) ,(83,'Racism',1) ,(85,'Second Coming',1) ,(86,'Sexuality',1) ,(87,'Sin',1) ,(88,'Singleness',1) ,(89,'Small Groups',1) ,(108,'Special Services',1) ,(90,'Spiritual Disciplines',1) ,(91,'Spiritual Gifts',1) ,(105,'Stewardship',1) ,(92,'Supernatural',1) ,(94,'Temptation',1) ,(95,'Ten Commandments',1) ,	(97,'Truth',1) ,(98,'Twelve Apostles',1) ,(100,'Weddings',1) ,(110,'Women',1) ,(101,'Workplace Issues',1) ,(102,'World Religions',1) ,(106,'Worship',1)";
         $msg[] = $this->performdb($query);
         
         $query = "INSERT INTO `#__bsms_locations` SET `location_text`='My Location', `published`=1";
         $msg[] = $this->performdb($query);
         
          $query = "INSERT  INTO `#__bsms_media` VALUES (2, 'mp3 compressed audio file', 'mp3', '','speaker24.png', 'mp3 audio file', 1),(3, 'Video', 'Video File', '','video24.png', 'Video File', 1),(4, 'm4v', 'Video Podcast', '','podcast-video24.png', 'Video Podcast', 1),(6, 'Streaming Audio', 'Streaming Audio', '','streamingaudio24.png', 'Streaming Audio', 1),(7, 'Streaming Video', 'Streaming Video', '','streamingvideo24.png', 'Streaming Video', 1),(8, 'Real Audio', 'Real Audio', '','realplayer24.png', 'Real Audio', 1),(9, 'Windows Media Audio', 'Windows Media Audio', '','windows-media24.png', 'Windows Media File', 1),(10, 'Podcast Audio', 'Podcast Audio', '','podcast-audio24.png', 'Podcast Audio', 1),(11, 'CD', 'CD', '','cd.png', 'CD', 1),(12, 'DVD', 'DVD', '','dvd.png', 'DVD', 1), (13,'Download','Download', '', 'download.png', 'Download', '1'),(14,'Article','Article', '', 'textfile24.png', 'Article', '1'),(15,'You Tube','You Tube','','youtube24.png','You Tube Video', 1)";
         $msg[] = $this->performdb($query);
         
         $query = "INSERT INTO `#__bsms_books`  VALUES (1, 'JBS_BBK_GENESIS', 101, 1),(2, 'JBS_BBK_EXODUS', 102, 1),(3, 'JBS_BBK_LEVITICUS', 103, 1),(4, 'JBS_BBK_NUMBERS', 104, 1),(5, 'JBS_BBK_DEUTERONOMY', 105, 1) ,(6, 'JBS_BBK_JOSHUA', 106, 1) ,(7, 'JBS_BBK_JUDGES', 107, 1) ,(8, 'JBS_BBK_RUTH', 108, 1) ,(9, 'JBS_BBK_1SAMUEL', 109, 1) ,(10, 'JBS_BBK_2SAMUEL', 110, 1) ,(11, 'JBS_BBK_1KINGS', 111, 1) ,(12, 'JBS_BBK_2KINGS', 112, 1) ,(13, 'JBS_BBK_1CHRONICLES', 113, 1) ,(14, 'JBS_BBK_2CHRONICLES', 114, 1) ,(15, 'JBS_BBK_EZRA', 115, 1) ,(16, 'JBS_BBK_NEHEMIAH', 116, 1) ,(17, 'JBS_BBK_ESTHER', 117, 1) ,(18, 'JBS_BBK_JOB', 118, 1) ,(19, 'JBS_BBK_PSALM', 119, 1) ,(20, 'JBS_BBK_PROVERBS', 120, 1) ,(21, 'JBS_BBK_ECCLESIASTES', 121, 1) ,(22, 'JBS_BBK_SONG_OF_SOLOMON', 122, 1) ,(23, 'JBS_BBK_ISAIAH', 123, 1) ,(24, 'JBS_BBK_JEREMIAH', 124, 1) ,(25, 'JBS_BBK_LAMENTATIONS', 125, 1) ,(26, 'JBS_BBK_EZEKIEL', 126, 1) ,(27, 'JBS_BBK_DANIEL', 127, 1) ,(28, 'JBS_BBK_HOSEA', 128, 1) ,(29, 'JBS_BBK_JOEL', 129, 1) ,(30, 'JBS_BBK_AMOS', 130, 1) ,(31, 'JBS_BBK_OBADIAH', 131, 1) ,(32, 'JBS_BBK_JONAH', 132, 1) ,(33, 'JBS_BBK_MICAH', 133, 1) ,(34, 'JBS_BBK_NAHUM', 134, 1) ,(35, 'JBS_BBK_HABAKKUK', 135, 1) ,(36, 'JBS_BBK_ZEPHANIAH', 136, 1),(37, 'JBS_BBK_HAGGAI', 137, 1),(38, 'JBS_BBK_ZECHARIAH', 138, 1),(39, 'JBS_BBK_MALACHI', 139, 1),(40, 'JBS_BBK_MATTHEW', 140, 1),(41, 'JBS_BBK_MARK', 141, 1),(42, 'JBS_BBK_LUKE', 142, 1),(43, 'JBS_BBK_JOHN', 143, 1),(44, 'JBS_BBK_ACTS', 144, 1),(45, 'JBS_BBK_ROMANS', 145, 1),(46, 'JBS_BBK_1CORINTHIANS', 146, 1),(47, 'JBS_BBK_2CORINTHIANS', 147, 1),(48, 'JBS_BBK_GALATIANS', 148, 1),(49, 'JBS_BBK_EPHESIANS', 149, 1),(50, 'JBS_BBK_PHILIPPIANS', 150, 1),(51, 'JBS_BBK_COLOSSIANS', 151, 1),(52, 'JBS_BBK_1THESSALONIANS', 152, 1),(53, 'JBS_BBK_2THESSALONIANS', 153, 1),(54, 'JBS_BBK_1TIMOTHY', 154, 1),(55, 'JBS_BBK_2TIMOTHY', 155, 1),(56, 'JBS_BBK_TITUS', 156, 1),(57, 'JBS_BBK_PHILEMON', 157, 1),(58, 'JBS_BBK_HEBREWS', 158, 1),(59, 'JBS_BBK_JAMES', 159, 1),(60, 'JBS_BBK_1PETER', 160, 1),(61, 'JBS_BBK_2PETER', 161, 1),(62, 'JBS_BBK_1JOHN', 162, 1),(63, 'JBS_BBK_2JOHN', 163, 1),(64, 'JBS_BBK_3JOHN', 164, 1),(65, 'JBS_BBK_JUDE', 165, 1),(66, 'JBS_BBK_REVELATION', 166, 1),(67, 'JBS_BBK_TOBIT', 167, 1),(68, 'JBS_BBK_JUDITH', 168, 1),(69, 'JBS_BBK_1MACCABEES', 169, 1),(70, 'JBS_BBK_2MACCABEES', 170, 1),(71, 'JBS_BBK_WISDOM', 171, 1),(72, 'JBS_BBK_SIRACH', 172, 1),(73, 'JBS_BBK_BARUCH', 173, 1)";
         $msg[] = $this->performdb($query);
         
         $query = "INSERT INTO `#__bsms_order` VALUES (1, 'ASC', 'JBS_CMN_ASCENDING'),(2, 'DESC', 'JBS_CMN_DESCENDING')";
         $msg[] = $this->performdb($query);
         
          $query = "INSERT INTO `#__bsms_mimetype` VALUES (1,'audio/mpeg3','MP3 Audio',1), (2,'audio/x-pn-realaudio','Real Audio',1),(3,'video/x-m4v','Podcast Video m4v',1),(4,'application/vnd.rn-realmedia','Real Media rm',1),(5,'audio/x-ms-wma','Windows Media Audio WMA',1),(6,'text/html','Text',1),(7,'audio/x-wav','Windows wav File',1),(8,'audio/x-pn-realaudio-plugin',' Real audio Plugin.rpm',1),(9,'audio/x-pn-realaudio','Real Media File .rm',1),(10,'audio/x-realaudio','Rea Audio File .ra',1),(11,'audio/x-pn-realaudio','Read Audio File.ram',1),(12,'video/mpeg',' Mpeg video .mpg',1),(13,'audio/mpeg','Video .mp2 File',1),(14,'video/x-msvideo',' Video .avi File',1),(15,'video/x-flv',' Flash Video FLV',1)";
         $msg[] = $this->performdb($query);
         
         $query = "INSERT INTO `#__bsms_mediafiles` VALUES (NULL, 1, 2, 1, 1, '','myfile.mp3', 12332, 1, 1, 0, '', 0, '2009-09-13 00:10:00', 1,'',1,0,0,'',0,0,0,'',1,1)";
         $msg[] = $this->performdb($query);
         
         $query = "INSERT INTO `#__bsms_admin` VALUES (1, '', '', '', '', 'speaker24.png', 'download.png', 'openbible.png', '0', 'compat_mode=0 drop_tables=0 admin_store=1 studylistlimit=10 popular_limit=1 series_imagefolder= media_imagefolder= teachers_imagefolder= study_images= podcast_imagefolder= location_id= teacher_id= series_id= booknumber= topic_id= messagetype= avr=0 download= target= server= path= podcast=0 mime=0 allow_entry_study=0 entry_access=23 study_publish=0 socialnetworking=1')";
         $msg[] = $this->performdb($query);
         
         $query = "INSERT INTO `#__bsms_teachers` VALUES (NULL,'','', 'Billy Sunday','Pastor','555-555-5555','billy@sunday.com','http://billysunday.com','William Ashley Sunday was an American athlete who after being a popular outfielder in baseballs National League during the 1880s became the most celebrated and influential American evangelist during the first two decades of the 20th century. ','components/com_biblestudy/images/billy_sunday11.jpg','276','197','components/com_biblestudy/images/images.jpg','101','141','Billy Sunday: 1862-1935',0,1,1,1)";
         $msg[] = $this->performdb($query);
         
          $query = "INSERT INTO `#__bsms_share` (`id`, `name`, `params`, `published`) VALUES 
	(NULL, 'FaceBook', 'mainlink=http://www.facebook.com/sharer.php? item1prefix=u= item1=200 item1custom= item2prefix=t= item2=5 item2custom= item3prefix= item3=6 item3custom= item4prefix= item4=8 item4custom= use_bitly=0 username= api= shareimage=components/com_biblestudy/images/facebook.png shareimageh=33px shareimagew=33px totalcharacters= alttext=FaceBook  ', 1), 
	(NULL, 'Twitter', 'mainlink=http://twitter.com/home? item1prefix=status= item1=200 item1custom= item2prefix= item2=5 item2custom= item3prefix= item3=1 item3custom= item4prefix= item4= item4custom= use_bitly=0 username= api= shareimage=components/com_biblestudy/images/twitter.png shareimagew=33px shareimageh=33px totalcharacters=140 alttext=Twitter', 1), 
	(NULL, 'Delicious', 'mainlink=http://delicious.com/save? item1prefix=url= item1=200 item1custom= item2prefix=&amp;title= item2=5 item2custom= item3prefix= item3=6 item3custom= item4prefix= item4= item4custom= use_bitly=0 username= api= shareimage=components/com_biblestudy/images/delicious.png shareimagew=33px shareimageh=33px totalcharacters= alttext=Delicious', 1),
	(NULL, 'MySpace', 'mainlink=http://www.myspace.com/index.cfm? item1prefix=fuseaction=postto&amp;t= item1=5 item1custom= item2prefix=&amp;c= item2=6 item2custom= item3prefix=&amp;u= item3=200 item3custom= item4prefix=&amp;l=1 item4= item4custom= use_bitly=0 username= api= shareimage=components/com_biblestudy/images/myspace.png\nshareimagew=33px\nshareimageh=33px\ntotalcharacters=\nalttext=MySpace', 1)";
         $msg[] = $this->performdb($query);
        
    $query = 'INSERT INTO `#__bsms_templates` (`id`, `type`, `tmpl`, `published`, `params`, `title`, `text`, `pdf`) VALUES (1, "tmplList", "", 1, "{"studieslisttemplateid":"1","detailstemplateid":"1","teachertemplateid":"1","serieslisttemplateid":"1","seriesdetailtemplateid":"1","teacher_id":["-1"],"series_id":["-1"],"booknumber":["-1"],"topic_id":["-1"],"messagetype":["-1"],"locations":["-1"],"show_verses":"0","stylesheet":"","date_format":"2","custom_date_format":"","duration_type":"2","protocol":"http:\\/\\/","media_player":"0","popuptype":"window","internal_popup":"1","player_width":"290","player_height":"23","embedshare":"TRUE","backcolor":"0","frontcolor":"0","lightcolor":"0","screencolor":"0","popuptitle":"Calvary Chapel Newberg - {{title}}","popupfooter":"{{filename}}","popupmargin":"50","popupbackground":"black","popupimage":"components\\/com_biblestudy\\/images\\/speaker24.png","show_filesize":"1","store_page":"flypage.tpl","useexpert_list":"0","headercode":"","templatecode":"\\\\n  \\\\n  \\\\n    {{teacher}}\\\\n    {{title}}\\\\n    {{media}}\\\\n  \\\\n  \\\\n    {{studyintro}}\\\\n    {{scripture}}\\\\n  \\\\n  \\\\n","wrapcode":"0","itemslimit":"10","default_order":"DESC","show_page_title":"1","page_title":"Bible Studies","use_headers_list":"1","list_intro":"","intro_show":"1","list_teacher_show":"0","listteachers":"1","teacherlink":"1","details_text":"Study Details","show_book_search":"1","use_go_button":"1","booklist":"0","show_teacher_search":"2","show_series_search":"2","show_type_search":"2","show_year_search":"1","show_order_search":"1","show_topic_search":"2","show_locations_search":"2","show_popular":"1","row1col1":"1","r1c1custom":"","r1c1span":"1","linkr1c1":"7","row1col2":"5","r1c2custom":"","r1c2span":"1","linkr1c2":"1","row1col3":"20","r1c3custom":"","r1c3span":"1","linkr1c3":"0","row1col4":"0","r1c4custom":"","linkr1c4":"0","row2col1":"6","r2c1custom":"","r2c1span":"4","linkr2c1":"0","row2col2":"0","r2c2custom":"","r2c2span":"1","linkr2c2":"0","row2col3":"0","r2c3custom":"","r2c3span":"1","linkr2c3":"0","row2col4":"0","r2c4custom":"","linkr2c4":"0","row3col1":"0","r3c1custom":"","r3c1span":"1","linkr3c1":"0","row3col2":"0","r3c2custom":"","r3c2span":"1","linkr3c2":"0","row3col3":"0","r3c3custom":"","r3c3span":"1","linkr3c3":"0","row3col4":"0","r3c4custom":"","linkr3c4":"0","row4col1":"0","r4c1custom":"","r4c1span":"1","linkr4c1":"0","row4col2":"0","r4c2custom":"","r4c2span":"1","linkr4c2":"0","row4col3":"0","r4c3custom":"","r4c3span":"1","linkr4c3":"0","row4col4":"0","r4c4custom":"","linkr4c4":"0","show_print_view":"1","show_teacher_view":"1","show_passage_view":"1","use_headers_view":"0","list_items_view":"1","title_line_1":"1","customtitle1":"","title_line_2":"4","customtitle2":"","view_link":"1","link_text":"Return to Studies List","show_scripture_link":"1","show_comments":["1"],"link_comments":"1","comment_access":["1"],"comment_publish":"1","use_captcha":"0","public_key":"6Ldut8ASAAAAAOeTkhVNyDGTFUlKXV3ynfKi3fBJ ","private_key":"6Ldut8ASAAAAAPL8rWoqqK-Cwk5nTtrDaJCgZZwB ","email_comments":"1","recipient":"tomfuller2@gmail.com","subject":"Comments on studies","body":"Comments entered.","useexpert_details":"0","study_detailtemplate":"{{title}}","teacher_title":"Our Teachers","show_teacher_studies":"1","studies":"10","label_teacher":"Latest Messages","useexpert_teacherlist":"0","teacher_headercode":"Teacher Header","teacher_templatecode":"\\\\n  \\\\n  \\\\n    {{teacher}}\\\\n    {{title}}\\\\n    {{teacher}}\\\\n  \\\\n  \\\\n    {{short}}\\\\n    {{information}}\\\\n  \\\\n  \\\\n","teacher_wrapcode":"0","useexpert_teacherdetail":"0","teacher_detailtemplate":"\\\\n  \\\\n  \\\\n    {{teacher}}\\\\n    {{title}}\\\\n    {{teacher}}\\\\n  \\\\n  \\\\n    {{short}}\\\\n    {{information}}\\\\n  \\\\n  \\\\n","series_title":"Our Series","show_series_title":"1","show_page_image_series":"1","series_show_description":"1","series_characters":"","search_series":"1","series_limit":"5","series_list_order":"ASC","series_order_field":"series_text","serieselement1":"1","seriesislink1":"1","serieselement2":"2","seriesislink2":"1","serieselement3":"3","seriesislink3":"1","serieselement4":"4","seriesislink4":"1","useexpert_serieslist":"0","series_headercode":"","series_templatecode":"","series_wrapcode":"0","series_detail_sort":"studydate","series_detail_order":"DESC","series_detail_limit":"","series_list_return":"1","series_detail_listtype":"0","series_detail_1":"5","series_detail_islink1":"1","series_detail_2":"7","series_detail_islink2":"0","series_detail_3":"10","series_detail_islink3":"0","series_detail_4":"20","series_detail_islink4":"0","useexpert_seriesdetail":"0","series_detailcode":"{{title}}","tip_title":"Sermon Information","tip_item1_title":"Title","tip_item1":"5","tip_item2_title":"Details","tip_item2":"6","tip_item3_title":"Teacher","tip_item3":"7","tip_item4_title":"Reference","tip_item4":"1","tip_item5_title":"Date","tip_item5":"10","drow1col1":"6","dr1c1custom":"","dr1c1span":"4","dlinkr1c1":"0","drow1col2":"0","dr1c2custom":"","dr1c2span":"1","dlinkr1c2":"0","drow1col3":"0","dr1c3custom":"","dr1c3span":"1","dlinkr1c3":"0","drow1col4":"0","dr1c4custom":"","dlinkr1c4":"0","drow2col1":"0","dr2c1custom":"","dr2c1span":"1","dlinkr2c1":"0","drow2col2":"0","dr2c2custom":"","dr2c2span":"1","dlinkr2c2":"0","drow2col3":"0","dr2c3custom":"","dr2c3span":"1","dlinkr2c3":"0","drow2col4":"0","dr2c4custom":"","dlinkr2c4":"0","drow3col1":"0","dr3c1custom":"","dr3c1span":"1","dlinkr3c1":"0","drow3col2":"0","dr3c2custom":"","dr3c2span":"1","dlinkr3c2":"0","drow3col3":"0","dr3c3custom":"","dr3c3span":"1","dlinkr3c3":"0","drow3col4":"0","dr3c4custom":"","dlinkr3c4":"0","drow4col1":"0","dr4c1custom":"","dr4c1span":"1","dlinkr4c1":"0","drow4col2":"0","dr4c2custom":"","dr4c2span":"1","dlinkr4c2":"0","drow4col3":"0","dr4c3custom":"","dr4c3span":"1","dlinkr4c3":"0","drow4col4":"0","dr4c4custom":"","dlinkr4c4":"0","landing_hide":"0","landing_hidelabel":"Show\\/Hide All","headingorder_1":"teachers","headingorder_2":"series","headingorder_3":"teachers","headingorder_4":"topics","headingorder_5":"locations","headingorder_6":"teachers","headingorder_7":"years","showteachers":"1","landingteacherslimit":"","teacherslabel":"Speakers","linkto":"1","showseries":"1","landingserieslimit":"","serieslabel":"Series","series_linkto":"0","showbooks":"1","landingbookslimit":"","bookslabel":"Books","showtopics":"1","landingtopicslimit":"","topicslabel":"Topics","showlocations":"1","landinglocationslimit":"","locationslabel":"Locations","showmessagetypes":"1","landingmessagetypeslimit":"","messagetypeslabel":"Message Types","showyears":"1","landingyearslimit":"","yearslabel":"Years"}", "Default", "textfile24.png", "pdf24.png")';
        
         $msg[] = $this->performdb($query);
          
     $query = "CREATE TABLE IF NOT EXISTS `jos_bsms_version` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `version` varchar(20) NOT NULL,
      `versiondate` date NOT NULL,
      `installdate` date NOT NULL,
      `build` varchar(20) NOT NULL,
      `versionname` varchar(40) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ";
    $msg[] = $this->performdb($query);

    $query = "INSERT INTO #__bsms_version SET `version` = '7.0.0', `installdate`='2011-2-12', `build`='1390', `versionname`='1Kings', `versiondate`='2011-02-15'";
        $msg[] = $this->performdb($query);
                
         require(JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.install.special.php');
        ob_start();
        $msg[] = ob_get_contents();
        ob_end_clean();
         
          $res = '<table><tr><td>Upgrade Joomla Bible Study to version 7.0.0</td></tr>';  //santon 2010-12-28 convert to phrase
        if (count($msg) < 1){$res .= JText::_('JBS_INS_NO_ERROR');}
        else
        {
            
            $r .= 'Results: <br />';
            foreach ($msg AS $m)
            {
                $r .= $m.'<br />';
            }
        }
        $result_table .= '<tr>
        		<td>
        			'.$res.$r.'
        		</td>
        	
        	</tr>';
        
        
        $result_table .= '</td></tr></table>';
        return $result_table;
    }
    
    function performdb($query=null)
    {
        $db = JFactory::getDBO();
        $results = '';
        if (!$query){$results = "Error. No query found"; return $results;}
        //$db = &$this->database;
        $db->setQuery($query);
        $db->query();
        if ($db->getErrorNum() > 0)
        				{
        					$error = $db->getErrorMsg();
                            $results = JText::_('JBS_ADM_ERROR_OCCURED').' '.$error;
                        }
                        else
                        {$results = $query;}
        print $results.'<br /><br />';
        return $results;
    }
}
?>