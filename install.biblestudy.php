<?php defined('_JEXEC') or die('Restricted access'); 
@set_time_limit(300);
$kn_maxTime = @ini_get('max_execution_time');

$maxMem = trim(@ini_get('memory_limit'));
if ($maxMem) {
	$unit = strtolower($maxMem{strlen($maxMem) - 1});
	switch($unit) {
		case 'g':
			$maxMem	*=	1024;
		case 'm':
			$maxMem	*=	1024;
		case 'k':
			$maxMem	*=	1024;
	}
	if ($maxMem < 16000000) {
		@ini_set('memory_limit', '16M');
	}
	if ($maxMem < 32000000) {
		@ini_set('memory_limit', '32M');
	}
	if ($maxMem < 48000000) {
		@ini_set('memory_limit', '48M');
	}
}
ignore_user_abort(true);

function com_install()
{
?>
<table width = "100%" border = "0" cellpadding = "0" cellspacing = "0">
            <tr>
            	<td width = "100%" valign = "top" style = "padding:10px;">
                
<?php 
$installresults = array();
$database	= & JFactory::getDBO();

//Change the admin menu icon
$database->setQuery("SELECT id FROM #__components WHERE admin_menu_link = 'option=com_biblestudy'");
$id = $database->loadResult();
//add new admin menu images
$database->setQuery("UPDATE #__components " . "SET admin_menu_img  = '../components/com_biblestudy/images/biblemenu.png'" . ",   admin_menu_link = 'option=com_biblestudy' " . "WHERE id='$id'");
$database->query();

//First we check to see if the database has been installed at all
$tables = $database->getTableList();
	$tn = '#__bsms_studies';
	$fields = $database->getTableFields( array( $tn ) );
	$bsms = false;
	$bsms	= isset( $fields[$tn]['id'] );
	$isdb = '';
	if ($bsms) { $isdb =  'Database is installed. <br />'; }
	else { 
		$isdb = 'Database not installed. Upgrade halted. Please uninstall and check MySQL.<br />'; 
		$installresults[] = 'Database not installed. Upgrade halted. Please uninstall and check MySQL.';
		}
if ($bsms) { //this is the beginninng of the install block. It won't go if the database isn't installed at all

// Sample Data install

//Added to the version 6.1.0 install due to changes in the #__bsms_media table, insert ignore caused problems, so test and if fresh install, then put in media data
	


		
	$database->setQuery ("SELECT id FROM #__bsms_studies");
	$database->query();
	$isitnew = $database->loadResult();
	if (!$isitnew){
	$database->setQuery ("INSERT INTO #__bsms_studies VALUES (1, '2009-09-13 00:10:00', 1, '2009-001', 101, 01, 01, 01, 31, 'Sample Study Title', 'Sample text you can use as an introduction to your study', '1', 4, 1, 'This is where you would put study notes or other information. This could be the full text of your study as well. If you install the scripture links plugin you will have all verses as links to BibleGateway.com', 1)");
	$database->query();
	$database->setQuery ("INSERT INTO #__bsms_servers VALUES (1, 'Your Server Name', 'www.mywebsite.com', 1)");
	$database->query();
	$database->setQuery ("INSERT INTO #__bsms_series VALUES (1, 'Worship Series', 1)");
	$database->query();
	$database->setQuery ("INSERT INTO #__bsms_message_type VALUES (1, 'Sunday', 1)");
	$database->query();
	$database->setQuery ("INSERT INTO #__bsms_folders VALUES (1, 'My Folder Name', '/media/', 1)");
	$database->query();
	$database->setQuery ("INSERT INTO #__bsms_podcast VALUES (1, 'My Podcast', 'www.mywebsite.com', 'Podcast Description goes here', 'www.mywebsite.com/myimage.jpg', 30, 30, 'Pastor Billy', 'www.mywebsite.com/myimage.jpg', 'jesus', 'mypodcast.xml', 'en-us', 'Jim Editor', 'jim@mywebsite.com', 50, 1)");
	$database->query();
	$database->setQuery ("INSERT INTO #__bsms_locations VALUES (1, 'My Location', 1)");
	$database->query();
	//this is inserting default values into the #__bsms_media table
	$database->setQuery("INSERT  INTO `#__bsms_media` VALUES (2, 'mp3 compressed audio file', 'mp3', '','speaker24.png', 'mp3 audio file', 1);");
	$database->query();	
	$database->setQuery("INSERT  INTO `#__bsms_media` VALUES (3, 'Video', 'Video File', '','video24.png', 'Video File', 1)");
	$database->query();
	$database->setQuery("INSERT  INTO `#__bsms_media` VALUES (4, 'm4v', 'Video Podcast', '','podcast-video24.png', 'Video Podcast', 1;)");
	$database->query();
	$database->setQuery("INSERT  INTO `#__bsms_media` VALUES (6, 'Streaming Audio', 'Streaming Audio', '','streamingaudio24.png', 'Streaming Audio', 1);");
	$database->query();
	$database->setQuery("INSERT  INTO `#__bsms_media` VALUES (7, 'Streaming Video', 'Streaming Video', '','streamingvideo24.png', 'Streaming Video', 1);");
	$database->query();
	$database->setQuery("INSERT  INTO `#__bsms_media` VALUES (8, 'Real Audio', 'Real Audio', '','realplayer24.png', 'Real Audio', 1;");
	$database->query();
	$database->setQuery("INSERT  INTO `#__bsms_media` VALUES (9, 'Windows Media Audio', 'Windows Media Audio', '','windows-media24.png', 'Windows Media File', 1);");
	$database->query();
	$database->setQuery("INSERT  INTO `#__bsms_media` VALUES (10, 'Podcast Audio', 'Podcast Audio', '','podcast-audio24.png', 'Podcast Audio', 1);");
	$database->query();
	$database->setQuery("INSERT  INTO `#__bsms_media` VALUES (11, 'CD', 'CD', '','cd.png', 'CD', 1);");
	$database->query();
	$database->setQuery("INSERT  INTO `#__bsms_media` VALUES (12, 'DVD', 'DVD', '','dvd.png', 'DVD', 1);");
	$database->query();
	$database->setQuery("INSERT INTO #__bsms_media VALUES (13,'Download','Download', '', 'download.png', 'Download', '1');");
	$database->query();
	}
	
	//end sample data
	
// Check schema version
	$database->setQuery ("SELECT schemaVersion FROM #__bsms_schemaVersion");
	$schema = $database->loadResult();
	if (!$schema) {
		$database->setQuery ("CREATE TABLE IF NOT EXISTS #__bsms_schemaVersion(id int(3) NOT NULL auto_increment, schemaVersion int(10), PRIMARY KEY (id) ) TYPE=MyISAM CHARACTER SET utf8");
		$database->query();
		$database->setQuery ("INSERT IGNORE INTO #__bsms_schemaVersion VALUES (1, 502)");
		$database->query();
	}
	$tn = '#__bsms_studies';
	$fields = $database->getTableFields( array( $tn ) );
	$hours = false;
	$hours	= isset( $fields[$tn]['media_hours'] );
	$check = $hours;	
			if (!$hours) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN media_hours VARCHAR(2) NULL AFTER studyintro;");
			$database->query();}
	$hours = false;
	$hours	= isset( $fields[$tn]['media_minutes'] );	
			if (!$hours) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN media_minutes VARCHAR(2) NULL AFTER media_hours;");
			$database->query();}
	$hours = false;
	$hours	= isset( $fields[$tn]['media_seconds'] );	
			if (!$hours) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN media_seconds VARCHAR(2) NULL AFTER media_minutes");
			$database->query();}
	$hours = false;
	$hours	= isset( $fields[$tn]['secondary_reference'] );	
			if (!$hours) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN secondary_reference TEXT NULL AFTER chapter_end");
			$database->query();}
	
	$tn = '#__bsms_teachers';
	$fields = $database->getTableFields( array( $tn ) );
	$title = false;
	$title	= isset( $fields[$tn]['title'] );
	//$check = $title;
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN title VARCHAR(50) NULL AFTER teachername");	$database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['phone'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN phone VARCHAR(50) NULL AFTER title"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['email'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN email VARCHAR(100) NULL AFTER phone"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['website'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN website VARCHAR(250) NULL AFTER email"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['information'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN information TEXT NULL AFTER website"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['image'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN image TEXT NULL AFTER information"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['imageh'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN imageh TEXT NULL AFTER image"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['imagew'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN imagew TEXT NULL AFTER imageh"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['thumb'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN thumb TEXT NULL AFTER imagew"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['thumbw'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN thumbw TEXT NULL AFTER thumb"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['thumbh'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN thumbh TEXT NULL AFTER thumbw"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['short'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN short TEXT NULL AFTER thumbh"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['ordering'] );	
			if (!$title ) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN ordering INT(3) NULL AFTER short"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['catid'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN catid INT(3) NULL default '1' AFTER ordering"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['list_show'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN list_show TINYINT(1) NOT NULL default '1' AFTER catid"); $database->query();}
	//$teachers	= isset( $fields[$tn]['list_show'] );
			//if ($check != $teachers) { echo 'Success in upgrading teachers table <br />'; }
			
			

//Begin installation for version 6.0.08

//if ($schema_version3 == 600) {

	$tn = '#__bsms_studies';
	$fields = $database->getTableFields( array( $tn ) );
	$fieldcheck = false;
	$fieldcheck	= isset( $fields[$tn]['booknumber2'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN booknumber2 VARCHAR(4) NULL AFTER secondary_reference;");
		$database->query();}
	$fieldcheck = false;
	$fieldcheck	= isset( $fields[$tn]['chapter_begin2'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN chapter_begin2 VARCHAR(4) NULL AFTER booknumber2;");
		$database->query();}
	$fieldcheck = false;
	$fieldcheck	= isset( $fields[$tn]['verse_begin2'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN verse_begin2 VARCHAR(4) NULL AFTER chapter_begin2;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['chapter_end2'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN chapter_end2 VARCHAR(4) NULL AFTER verse_begin2;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['verse_end2'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN verse_end2 VARCHAR(4) NULL AFTER chapter_end2;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['prod_dvd'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN prod_dvd VARCHAR(100) NULL AFTER verse_end2;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['prod_cd'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN prod_cd VARCHAR(100) NULL AFTER prod_dvd;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['server_cd'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN server_cd VARCHAR(10) NULL AFTER prod_cd;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['server_dvd'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN server_dvd VARCHAR(10) NULL AFTER server_cd;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['image_cd'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN image_cd VARCHAR(10) NULL AFTER server_dvd;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['image_dvd'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN image_dvd VARCHAR(10) NULL default '0' AFTER image_cd;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['studytext2'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN studytext2 TEXT NULL AFTER image_dvd;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['comments'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN comments TINYINT(1) NULL default '1' AFTER studytext2;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['hits'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN hits INT(10) NOT NULL default '0' AFTER comments;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['user_id'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN user_id INT(10) NULL AFTER hits;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['user_name'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN user_name VARCHAR(50) NULL AFTER user_id;");
		$database->query();}
	
	$tn = '#__bsms_mediafiles';
	$fields = $database->getTableFields( array( $tn ) );
	$fieldcheck = false;
	$fieldcheck	= isset( $fields[$tn]['link_type'] );	
	if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_mediafiles ADD COLUMN link_type VARCHAR(1) NULL AFTER createdate;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['hits'] );	
	if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_mediafiles ADD COLUMN hits INT(10) NULL AFTER link_type;");
		$database->query();}
	$database->setQuery ("DELETE FROM #__bsms_schemaVersion WHERE id = 1 LIMIT 1");
		$database->query();
	$database->setQuery ("INSERT IGNORE INTO #__bsms_schemaVersion VALUES (1, 608)");
		$database->query();
		$database->setQuery ("SELECT schemaVersion FROM #__bsms_schemaVersion");
		$db608 = $database->loadResult();
		$dbmessage =  'The current database schema for Bible Study is: '.$db608.'<br>';
		$installresults[] = 'Database version 608 installed';
//} //End check of $schema_version == 600	
// Begin $schema_version 611
$tn = '#__bsms_studies';
	$fields = $database->getTableFields( array( $tn ) );
	$fieldcheck = false;
$fieldcheck	= isset( $fields[$tn]['show_level'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN show_level INT(2) NOT NULL default '0' AFTER user_name;");
		$database->query();}
$fieldcheck	= isset( $fields[$tn]['location_id'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN location_id INT(3) NULL AFTER show_level;");
		$database->query();}
$tn = '#__bsms_studies';
	$db611 = true;
	$fields = $database->getTableFields( array( $tn ) );
	$fieldcheck	= isset( $fields[$tn]['show_level'] );
		if (!$fieldcheck) { $show_level_message = 'Problem creating field show_level. Check permissions on your MySQL database'; $db611 = false;}
	$fieldcheck	= isset( $fields[$tn]['location_id'] );
		if (!$fieldcheck) { $location_id_message = 'Problem creating field location_id. Check permissions on your MySQL database'; $db611 = false;}
	if (!$db611) { $dbmessage = 'There was a problem with the installation of this version as follows: <br>' . $location_id_message.'<br>'.$show_level_message;}
	else {
		$database->setQuery ("DELETE FROM #__bsms_schemaVersion WHERE id = 1 LIMIT 1");
		$database->query();
	$database->setQuery ("INSERT IGNORE INTO #__bsms_schemaVersion VALUES (1, 611)");
		$database->query();
		$database->setQuery ("SELECT schemaVersion FROM #__bsms_schemaVersion");
		$db611 = $database->loadResult();
		$dbmessage =  'The current database schema for Bible Study is: '.$db611.'<br>';
		$installresults[] = 'Database version 611 installed';
		}
// End version 611 upgrade
// Begin version 612 upgrade


$database->setQuery ("SELECT id FROM #__bsms_templates");
	$database->query();
	$isitnew = $database->loadResult();
	if (!$isitnew)
	{
	$database->setQuery  
	("
	INSERT INTO `#__bsms_templates` (`id`, `type`, `tmpl`, `published`, `params`, `title`, `text`, `pdf`) VALUES
(1, 'tmplList', '', 1, 'itemslimit=10
compatibilityMode=0
studieslisttemplateid=1
detailstemplateid=1
teachertemplateid=1
serieslisttemplateid=1
seriesdetailtemplateid=1
teacher_id=
show_teacher_list=0
mult_teachers=
series_id=0
mult_series=
booknumber=0
mult_books=
topic_id=0
mult_topics=
messagetype=0
mult_messagetype=
locations=0
mult_locations=
default_order=DESC
show_page_image=1
tooltip=1
show_verses=0
stylesheet=
date_format=2
duration_type=1
useavr=0
popuptype=window
media_player=0
player_width=290
show_filesize=1
store_page=flypage.tpl
show_page_title=1
page_title=Bible Studies
use_headers_list=1
list_intro=
intro_show=1
listteachers=1
teacherlink=1
details_text=Study Details
show_book_search=1
show_teacher_search=1
show_series_search=1
show_type_search=1
show_year_search=1
show_order_search=1
show_topic_search=1
show_locations_search=1
tip_title=Sermon Information
tip_item1_title=Title
tip_item1=5
tip_item2_title=Details
tip_item2=6
tip_item3_title=Teacher
tip_item3=7
tip_item4_title=Reference
tip_item4=1
tip_item5_title=Date
tip_item5=10
row1col1=18
r1c1custom=
r1c1span=1
rowspanr1c1=1
linkr1c1=0
row1col2=5
r1c2custom=
r1c2span=1
rowspanr1c2=1
linkr1c2=1
row1col3=1
r1c3custom=
r1c3span=1
rowspanr1c3=1
linkr1c3=0
row1col4=20
r1c4custom=
rowspanr1c4=1
linkr1c4=0
row2col1=6
r2c1custom=
r2c1span=4
rowspanr2c1=1
linkr2c1=0
row2col2=0
r2c2custom=
r2c2span=1
rowspanr2c2=1
linkr2c2=0
row2col3=0
r2c3custom=
r2c3span=1
rowspanr2c3=1
linkr2c3=0
row2col4=0
r2c4custom=
rowspanr2c4=1
linkr2c4=0
row3col1=0
r3c1custom=
r3c1span=1
rowspanr3c1=1
linkr3c1=0
row3col2=0
r3c2custom=
r3c2span=1
linkr3c2=0
row3col3=0
r3c3custom=
r3c3span=1
rowspanr3c3=1
linkr3c3=0
row3col4=0
r3c4custom=
rowspanr3c4=1
linkr3c4=0
row4col1=0
r4c1custom=
r4c1span=1
rowspanr4c1=1
linkr4c1=0
row4col2=0
r4c2custom=
r4c2span=1
rowspanr4c2=1
linkr4c2=0
row4col3=0
r4c3custom=
r4c3span=1
rowspanr4c3=1
linkr4c3=0
row4col4=0
r4c4custom=
rowspanr4c4=1
linkr4c4=0
show_print_view=1
show_pdf_view=1
show_teacher_view=1
show_passage_view=1
use_headers_view=1
list_items_view=0
title_line_1=1
customtitle1=
title_line_2=4
customtitle2=
view_link=1
link_text=Return to Studies List
show_scripture_link=1
show_comments=0
comment_access=1
comment_publish=0
use_captcha=1
email_comments=1
recipient=
subject=Comments on studies
body=Comments entered.
moduleitems=3
teacher_title=Our Teachers
show_teacher_studies=1
studies=5
label_teacher=Latest Messages
series_title=Our Series
show_series_title=1
show_page_image_series=1
series_show_description=1
series_characters=
search_series=1
series_limit=5
serieselement1=1
seriesislink1=1
serieselement2=1
seriesislink2=1
serieselement3=1
seriesislink3=1
serieselement4=1
seriesislink4=1
series_detail_sort=1
series_detail_order=DESC
series_detail_show_link=1
series_detail_limit=
series_list_return=1
series_detail_1=5
series_detail_islink1=1
series_detail_2=7
series_detail_islink2=0
series_detail_3=10
series_detail_islink3=0
series_detail_4=20
series_detail_islink4=0

', 'Default','textfile24.png','pdf24.png');
	");	
	$database->query();
	$installresults[] = 'Default template data installed';
	}
$tn = '#__bsms_studies';
	$fields = $database->getTableFields( array( $tn ) );
	$fieldcheck = false;
$fieldcheck	= isset( $fields[$tn]['thumbnailm'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN thumbnailm TEXT NULL AFTER studytext;");
		$database->query();}
$fieldcheck	= isset( $fields[$tn]['thumbhm'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN thumbhm INT NULL AFTER thumbnailm;");
		$database->query();}
$fieldcheck	= isset( $fields[$tn]['thumbwm'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN thumbwm INT NULL AFTER thumbhm;");
		$database->query();}
$fieldcheck	= isset( $fields[$tn]['params'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN params TEXT NULL AFTER thumbwm;");
		$database->query();}
		
$tn = '#__bsms_podcast';
	$fields = $database->getTableFields( array( $tn ) );
	$fieldcheck = false;	
$fieldcheck	= isset( $fields[$tn]['episodetitle'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_podcast ADD COLUMN episodetitle INT NULL AFTER published;");
		$database->query();}
$fieldcheck	= isset( $fields[$tn]['custom'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_podcast ADD COLUMN custom VARCHAR( 200 ) NULL AFTER episodetitle;");
		$database->query();}
$fieldcheck = false;	
$fieldcheck	= isset( $fields[$tn]['detailstemplateid'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_podcast ADD COLUMN detailstemplateid INT NULL AFTER custom;");
		$database->query();}


$tn = '#__bsms_series';
	$fields = $database->getTableFields( array( $tn ) );
	$fieldcheck = false;	
$fieldcheck	= isset( $fields[$tn]['series_thumbnail'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_series ADD COLUMN series_thumbnail VARCHAR(150) NULL AFTER series_text;");
		$database->query();}
	$fieldcheck = false;
	$fieldcheck = isset($fields[$tn]['description']);
		if	 (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_series ADD COLUMN description TEXT NULL AFTER series_text;");
		$database->query();}
	$fieldcheck = false;
	$fieldcheck = isset($fields[$tn]['teacher']);
		if	 (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_series ADD COLUMN teacher INT(3) NULL AFTER series_text;");
		$database->query();}		


$tn = '#__bsms_media';
$fields = $database->getTableFields( array( $tn ) );
	$fieldcheck = false;	
$fieldcheck	= isset( $fields[$tn]['path2'] );
		if (!$fieldcheck) 

		{
			$database->setQuery ("ALTER TABLE #__bsms_media ADD COLUMN path2 VARCHAR(150) NOT NULL AFTER media_image_path;");
			$database->query();
		}
$database->setQuery("SELECT id FROM #__bsms_media WHERE path2 LIKE 'textfile24.png';");
$database->query();
$numrows = $database->getNumRows();
if (!$numrows)
	{
		$database->setQuery("INSERT INTO #__bsms_media (media_text, media_image_name, media_image_path, path2, media_alttext, published) VALUES ('Article','Article', '', 'textfile24.png', 'Article', '1');");
		$database->query();
	}

$database->setQuery("SELECT id FROM #__bsms_media WHERE path2 LIKE '%download.png%';");
$database->query();
$numrows = $database->getNumRows();
if (!$numrows)
	{
		$query = "INSERT INTO #__bsms_media (media_text, media_image_name, media_image_path, path2, media_alttext, published) VALUES ('Download','Download', '', 'download.png', 'Download', '1');";
		$database->setQuery = ($query);
		$database->query();
	}
$tn = '#__bsms_teachers';
$fields = $database->getTableFields( array( $tn ) );
	$fieldcheck = false;	
$fieldcheck = isset($fields[$tn]['teacher_thumbnail']);
		if	 (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN teacher_thumbnail TEXT NULL AFTER id;");
		$database->query();}	
$fieldcheck = false;	
$fieldcheck = isset($fields[$tn]['teacher_image']);
		if	 (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN teacher_image TEXT NULL AFTER id;");
		$database->query();}
		
$tn = '#__bsms_mediafiles';
$fields = $database->getTableFields( array( $tn ) );
	$fieldcheck = false;	
$fieldcheck = isset($fields[$tn]['docMan_id']);
		if	 (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_mediafiles ADD COLUMN docMan_id INT NULL AFTER published;");
		$database->query();}
$fieldcheck = isset($fields[$tn]['article_id']);
		if	 (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_mediafiles ADD COLUMN article_id INT NULL AFTER docMan_id;");
		$database->query();}
$fieldcheck = isset($fields[$tn]['comment']);		
		if	 (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_mediafiles ADD COLUMN comment TEXT NULL AFTER article_id;");
		$database->query();}
$fieldcheck = isset($fields[$tn]['virtueMart_id']);		
		if	 (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_mediafiles ADD COLUMN virtueMart_id INT NULL AFTER comment;");
		$database->query();}		
$fieldcheck = isset($fields[$tn]['params']);		
		if	 (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_mediafiles ADD COLUMN params TEXT NULL AFTER virtueMart_id;");
		$database->query();}	
		
		$database->setQuery ("DELETE FROM #__bsms_schemaVersion WHERE id = 1 LIMIT 1");
		$database->query();
	$database->setQuery ("INSERT IGNORE INTO #__bsms_schemaVersion VALUES (1, 612)");
		$database->query();
		$database->setQuery ("SELECT schemaVersion FROM #__bsms_schemaVersion");
		$db612 = $database->loadResult();
		$dbmessage =  'The current database schema for Bible Study is: '.$db612.'<br>';
		$installresults[] = 'Database version 612 installed';
		
//We insert a teacher row into a fresh database
$database->setQuery ("SELECT id FROM #__bsms_teachers");
	$database->query();
	$isitnew = $database->loadResult();
	if (!$isitnew){
$database->setQuery ("INSERT INTO #__bsms_teachers VALUES (1,'','', 'Billy Sunday','Pastor','555-555-5555','billy@sunday.com','http://billysunday.com','William Ashley Sunday (November 19 1862–November 6 1935) was an American athlete who after being a popular outfielder in baseballs National League during the 1880s became the most celebrated and influential American evangelist during the first two decades of the 20th century. ','components/com_biblestudy/images/billy_sunday11.jpg','276','197','components/com_biblestudy/images/images.jpg','101','141','Billy Sunday: 1862-1935',0,1,1,1)");
$database->query();
$installresults[] = 'Teacher sample data installed';
	}
//We insert a mediafile row into a fresh database
$database->setQuery ("SELECT id FROM #__bsms_mediafiles");
	$database->query();
	$isitnew = $database->loadResult();
	if (!$isitnew){
	$database->setQuery ("INSERT INTO #__bsms_mediafiles VALUES (1, 1, 2, 1, 1, '','myfile.mp3', 12332, 1, 1, 0, '', 0, '2009-09-13 00:10:00', 1,'',1,0,0,'',0,'player=0')");
	$database->query();
	}
//Check to see if the admin row exists

$database->setQuery ("SELECT id FROM #__bsms_admin");
	$database->query();
	$isitnew = $database->loadResult();
	if (!$isitnew)
	{
		$database->setQuery ("INSERT INTO #__bsms_admin VALUES 	(1, '', '', '', '', 'speaker24.png', 'download.png', 'openbible.png', 'compat_mode=0
drop_tables=0
admin_store=1
studylistlimit=10
series_imagefolder=
media_imagefolder=
teachers_imagefolder=
study_images=
podcast_imagefolder=
location_id=
teacher_id=
series_id=
booknumber=
topic_id=
messagetype=
avr=0
download=
target=
server=
path=
podcast=0
mime=0
allow_entry_study=0
entry_access=23
study_publish=0')");
		$database->query();
		$installresults[] = 'Administration data installed';
	}
//Check to see if the css file exists. If it does, don't do anything. If not, install the css file
jimport('joomla.filesystem.file');
$src = JPATH_SITE.DS.'components/com_biblestudy/assets/css/biblestudy.css.dist';
$dest = JPATH_SITE.DS.'components/com_biblestudy/assets/css/biblestudy.css';
$cssexists = JFile::exists($dest);
if (!$cssexists)
	{
		JFile::copy($src, $dest);
		$installresults[] = 'CSS data installed';
	}

//End version 612 upgrade

//Begin version 613 upgrade
$database->setQuery ("SELECT id FROM #__bsms_share");
	$database->query();
	$isitnew = $database->loadResult();
	if (!$isitnew)
	{
		$database->setQuery ("INSERT INTO `#__bsms_share` (`id`, `name`, `params`, `published`) VALUES
(1, 'FaceBook', 'mainlink=http://www.facebook.com/sharer.php?\nitem1prefix=u=\nitem1=200\nitem1custom=\nitem2prefix=t=\nitem2=5\nitem2custom=\nitem3prefix=\nitem3=6\nitem3custom=\nitem4prefix=\nitem4=8\nitem4custom=\nuse_bitly=0\nusername=\napi=\nshareimage=components/com_biblestudy/images/facebook.png\nshareimageh=33px\nshareimagew=33px\ntotalcharacters=\nalttext=FaceBook\n\n', 1),
(2, 'Twitter', 'mainlink=http://twitter.com/home?\r\nitem1prefix=status=\r\nitem1=200\r\nitem1custom=\r\nitem2prefix=\r\nitem2=5\r\nitem2custom=\r\nitem3prefix=\r\nitem3=1\r\nitem3custom=\r\nitem4prefix=\r\nitem4=\r\nitem4custom=\r\nuse_bitly=0\r\nusername=\r\napi=\r\nshareimage=components/com_biblestudy/images/twitter.png\r\nshareimagew=33px\r\nshareimageh=33px\r\ntotalcharacters=140\r\nalttext=Twitter', 1),
(3, 'Delicious', 'mainlink=http://delicious.com/save?\r\nitem1prefix=url=\r\nitem1=200\r\nitem1custom=\r\nitem2prefix=&title=\r\nitem2=5\r\nitem2custom=\r\nitem3prefix=\r\nitem3=6\r\nitem3custom=\r\nitem4prefix=\r\nitem4=\r\nitem4custom=\r\nuse_bitly=0\r\nusername=\r\napi=\r\nshareimage=components/com_biblestudy/images/delicious.png\r\nshareimagew=33px\r\nshareimageh=33px\r\ntotalcharacters=\r\nalttext=Delicious', 1),
(4, 'MySpace', 'mainlink=http://www.myspace.com/index.cfm?\r\nitem1prefix=fuseaction=postto&t=\r\nitem1=5\r\nitem1custom=\r\nitem2prefix=&c=\r\nitem2=6\r\nitem2custom=\r\nitem3prefix=&u=\r\nitem3=200\r\nitem3custom=\r\nitem4prefix=&l=1\r\nitem4=\r\nitem4custom=\r\nuse_bitly=0\r\nusername=\r\napi=\r\nshareimage=components/com_biblestudy/images/myspace.png\r\nshareimagew=33px\r\nshareimageh=33px\r\ntotalcharacters=\r\nalttext=MySpace', 1)");
$database->query();
$installresults[] = 'Social Networking data installed';
	}

//Read current css file, add share information if not already there, write and close
$cssread = JFile::read($dest);
$shareexists = 0;
$shareexists = substr_count($cssread,'#bsmsshare');
if ($shareexists < 1)
{
	
	$cssshared = '
/*Social Networking Items */
#bsmsshare {
  margin: 0;
  border-collapse:separate;
  float:right;
  border: 1px solid #CFCFCF;
  background-color: #F5F5F5;
}
#bsmsshare th, #bsmsshare td {
  text-align:center;
  padding:0 0 0 0;
  border:none;
}
#bsmsshare th {
	color:#0b55c4;
	font-weight:bold;
}';
	$cssread = $cssread.$cssshared;
	$errcss = '';
	if (!JFile::write($dest, $cssread))
	{$errcss = 'There was a problem writing to the css file. Please contact customer support on JoomlaBibleStudy.org';
	$installresults[] = 'There was a problem writing to the css file. Please contact customer support on JoomlaBibleStudy.org';}
}
$database->setQuery ("DELETE FROM #__bsms_schemaVersion WHERE id = 1 LIMIT 1");
		$database->query();
	$database->setQuery ("INSERT IGNORE INTO #__bsms_schemaVersion VALUES (1, 613)");
		$database->query();
		$database->setQuery ("SELECT schemaVersion FROM #__bsms_schemaVersion");
		$db613 = $database->loadResult();
		$dbmessage613 =  'Upgraded database to version: '.$db613.'<br>';
		$installresults[] = 'Database version 613 installed';
//End of upgrade to databse version 613
?><p>
<?php echo '<strong>Messages: </strong><br>';
foreach($installresults as $key => $value)
{
	echo $value.'<br>';
}
?></p>
<div class="header"><?php 
global $mainframe; ?>

<img src = "<?php echo $mainframe->getCfg("live_site"); ?>/components/com_biblestudy/images/openbible.png" alt = "" border = "0">
Congratulations, Bible Study Message Manager has been installed successfully. </div>
<p>

<p>
Welcome to the Bible Study Message System. Please note if there are any error messages above. This component is designed to help your church communicate the gospel and teachings in the Word of God. com_biblestudy allows you to enter detailed information about the studies given and links to multimedia content you have uploaded to your server. You can also display full text or notes. All this is searchable in many different ways and you have a lot of control over how much information is displayed on the front end. </p>
<p>
It is very important that you do a couple of things when you first install the component. </p>
<p>
 1. Go to Components | Bible Study Manager. There should be a sample study there. Now click on the Administration tab. There you will find a few settings you can use. Next click on Templates. There should be a default template listed. This is where the display settings are kept. Take some time to look through the drop downs and see how they affect the various views you set up. You can create new templates if you like. </p>
 <p>
 2. Go back to Components | Bible Study. Click on the Servers and add your server, then go to the Folders link and add a folder under that server. Now you are set to add your first real study (be sure to delete the samples once you are familiar with how the component works). </p>
 <p>
 3. Click on Studies. Just put in some text to add a new study. Then go to the Media Files tab and enter a media file associated with that study. </p>
 <?php } // end of if ($bsms) for whole db update block	?>
 <p><a href="http://www.joomlaoregon.org/index.php?option=com_fireboard" target="_blank">Visit our forum with your questions</a></p>
 <p><a href="http://www.joomlaoregon.org" target="_blank">Get more help and information at JoomlaOregon.org</a></p>
 </td>
 </tr>
 </table>
 <?php } // end of com_install?>